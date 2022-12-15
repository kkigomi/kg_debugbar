<?php
namespace Kkigomi\Plugin\Debugbar;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use DebugBar\DebugBar as BaseDebugbar;
use DebugBar\StandardDebugBar;
use DebugBar\Storage\PdoStorage;
use Kkigomi\Plugin\Debugbar\DataCollector;
use Kkigomi\Plugin\Debugbar\Logger;

class Debugbar implements LoggerAwareInterface
{
    use Traits\SingletonTrait;
    use Traits\TableTrait;

    private static $instance;
    private static bool $booted = false;
    private array $config = [];
    private BaseDebugbar $debugbar;
    private JavascriptRenderer $debugbarRenderer;
    private LoggerInterface $logger;
    private bool $rendered = false;
    private bool $stacked = false;

    public function rendered(): bool
    {
        return $this->rendered;
    }

    private function singletonInstanceInit()
    {
        // 업데이트 체크 및 업데이트 실행
        if ($this->needMigration()) {
            $this->migration();
        }
    }

    public static function booted(): bool
    {
        return self::$booted;
    }

    private function needMigration(): bool
    {
        $tables = self::pluginTables();

        if (
            !empty($_SESSION['ss_mb_id'])
            && \is_admin($_SESSION['ss_mb_id']) === 'super'
        ) {
            return false;
        }

        if (isset($_SERVER['kg_debugbar_updated'])) {
            return false;
        }

        // cache에서 업데이트 기록이 있으면 패스
        $cacheData = g5_get_cache('kg-debugbar-updated') ?: 0;
        if ($cacheData >= \KG_DEBUGBAR_VERSION_ID) {
            return false;
        }

        // 테이블 없으면 설치
        if (!sql_query("DESC {$tables['stack']}")) {
            return true;
        }

        return false;
    }

    public function boot($options)
    {
        $tables = $this->pluginTables();

        self::$booted = true;

        // 에러 핸들러
        set_error_handler(function () {
            $this->exception_error_handler(...func_get_args());
        });

        // AJAX 및 html_process::end()가 실행되지않는 세션을 캡쳐하기 위해 PHP Debugbar의 OpenHandler 사용
        register_shutdown_function(function () {
            $this->shutdownHandler();
        });

        $this->setOptions($options);

        $this->debugbar = new StandardDebugBar();
        $this->setLogger(new Logger\DebugbarLogger($this->debugbar['messages']));

        $this->debugbarRenderer = new JavascriptRenderer(
                $this->debugbar,
                $this->config['pluginUrl'] . '/assets',
                $this->config['pluginPath'] . '/assets'
        );

        // AJAX 및 html_process::end()가 호출되지 않는 요청의 캡쳐하기 위해 PHP Debugbar의 OpenHandler 사용
        $dsn = 'mysql:dbname=' . \G5_MYSQL_DB . ';host=' . \G5_MYSQL_HOST;
        $pdo = new \PDO($dsn, \G5_MYSQL_USER, \G5_MYSQL_PASSWORD);
        $this->debugbar->setStorage(new PdoStorage($pdo, $tables['stack']));
        $this->debugbarRenderer->setOpenHandlerUrl(\KG_DEBUGBAR_URL . '/phpdebugbar_handle.php');

        // 커스텀 콜렉터
        $this->debugbar->addCollector(new DataCollector\QueryCollector());
        $this->debugbar->addCollector(new DataCollector\HookCollector());
        $this->debugbar->addCollector(new DataCollector\G5Collector());

        foreach ($this->debugbar->getCollectors() as $collector) {
            if (method_exists($collector, 'useHtmlVarDumper')) {
                $collector->useHtmlVarDumper();
            }
        }
    }

    private function setOptions(array $options): void
    {
        $this->config['useStack'] = isset($options['useStack']) ? (bool) $options['useStack'] : true;
        $this->config['limitStack'] = isset($options['limitStack']) ? (int) $options['limitStack'] : 100;
        $this->config['pluginUrl'] = isset($options['pluginUrl']) ? (string) $options['pluginUrl'] : \KG_DEBUGBAR_URL;
        $this->config['pluginPath'] = isset($options['pluginPath']) ? (string) $options['pluginPath'] : \KG_DEBUGBAR_PATH;
    }

    public function getDebugbar()
    {
        return $this->debugbar;
    }

    public function getCollector(string $name)
    {
        return $this->debugbar->getCollector($name);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    // public function startMeasure()
    // {
    //     $collector = $this->debugbar->getCollector('time');
    //     $collector->startMeasure(...func_get_args());
    // }

    // public function stopMeasure()
    // {
    //     $collector = $this->debugbar->getCollector('time');
    //     $collector->stopMeasure(...func_get_args());
    // }

    private function collectQuery(&$queries): void
    {
        if (!self::$booted || !is_array($queries) || !count($queries)) {
            return;
        }

        $queryCollector = $this->debugbar->getCollector('dbquery');

        foreach ($queries as $query) {
            $duration = $query['end_time'] - $query['start_time'];
            $rowCount = (!empty($query['error']) && !$query['error']['error_code'] && is_object($query['result'])) ? sql_num_rows($query['result']) ?? 0 : 0;

            $queryCollector->addQuery(
                $query['sql'],
                $duration,
                $rowCount,
                [
                    'error_code' => $query['error_code'],
                    'error_message' => $query['error_message']
                ],
                $query['source']
            );
        }
    }

    public function getRenderCode(&$queries)
    {
        global $is_admin;

        if (!self::$booted || $this->rendered) {
            return;
        }

        $this->rendered = true;

        $this->collectQuery($queries);

        $output = [];

        if ($is_admin === 'super' && \KG_DEBUGBAR_ENABLE_IP && count(\KG_DEBUGBAR_ENABLE_IP)) {
            $output[] = "<script>";
            $output[] = "window.kgDebugBarIPEnabled_1ff9dfc8ced345a2abb4ad98b85b297f = true;";
            $output[] = 'window.kgDebugBarIPList_1ff9dfc8ced345a2abb4ad98b85b297f = ' . var_export(json_encode(KG_DEBUGBAR_ENABLE_IP), true) . ';';
            $output[] = "</script>";
        }

        $output[] = $this->debugbarRenderer->renderHead();
        $output[] = $this->debugbarRenderer->render();

        $output = implode("\n", $output);

        return $output;
    }

    public function stackData(&$queries, $header = true)
    {
        $tables = $this->pluginTables();

        if (!$this->config['useStack'] || $this->rendered || $this->stacked) {
            return;
        }

        $this->stacked = true;

        $this->collectQuery($queries);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            $this->debugbar->stackData();
        }

        if ($header) {
            $this->debugbar->sendDataInHeaders(true);
        }

        // 오래된 데이터 제거
        sql_query("DELETE FROM `{$tables['stack']}` WHERE `id` NOT IN (SELECT `id` FROM (SELECT `id` FROM `{$tables['stack']}` ORDER BY `no` DESC LIMIT {$this->config['limitStack']}) T)");
    }

    /**
     * Exception을 debugbar에 기록
     */
    private function exception_error_handler(int $code, string $message, string $filename = null, int $line = null, array $context = null): void
    {
        $exceptionsCollector = $this->getCollector('exceptions');
        $exceptionsCollector->addThrowable(new \ErrorException($message, $code, \E_ERROR, $filename, $line));
    }

    private function shutdownHandler()
    {
        if (strpos($_SERVER['SCRIPT_FILENAME'], \KG_DEBUGBAR_PATH) === 0) {
            return;
        }

        // `tail_sub` Hook이 호출되지 않았다면 stack
        $this->stackData($GLOBALS['g5_debug']['sql']);
    }

    private function migration()
    {
        $tables = $this->pluginTables();

        $_SERVER['kg_debugbar_updated'] = true;

        $stackTableDesc = sql_query("DESC {$tables['stack']}");
        if (!$stackTableDesc) {
            sql_query("CREATE TABLE IF NOT EXISTS `{$tables['stack']}` (
                `no` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id` varchar(33) NOT NULL,
                `data` longtext,
                `meta_utime` double DEFAULT NULL,
                `meta_datetime` datetime DEFAULT NULL,
                `meta_uri` text,
                `meta_ip` varchar(45) DEFAULT NULL,
                `meta_method` varchar(10) DEFAULT NULL,
                PRIMARY KEY (`no`),
                UNIQUE KEY `unique_id` (`id`)
            )");
        }

        \g5_set_cache('kg-debugbar-updated', \KG_DEBUGBAR_VERSION_ID);

        return true;
    }
}
