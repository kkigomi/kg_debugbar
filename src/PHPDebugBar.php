<?php
namespace Kkigomi\Plugin\PHPDebugBar;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use DebugBar\StandardDebugBar;
use Kkigomi\Plugin\PHPDebugBar\DataCollector;
use Kkigomi\Plugin\PHPDebugBar\Logger;
use ReflectionFunction;

class PHPDebugBar implements LoggerAwareInterface
{
    /** @var self $instance */
    private static $instance;
    private static bool $booted = false;
    private StandardDebugBar $debugbar;
    private DataCollector\QueryCollector $queryCollector;
    private DataCollector\HookCollector $hookCollector;
    private $debugbarRenderer;

    private array $config = [];
    private LoggerInterface $logger;

    private function __construct()
    {
        $this->setLogger(new Logger\DummyLogger());
    }

    public static function getInstance(): self
    {
        return self::$instance ?: self::$instance = new self();
    }

    public function boot($options)
    {
        self::$booted = true;

        $this->config = $options;

        $this->debugbar = new StandardDebugBar();
        $this->setLogger(new Logger\DebugbarLogger($this->debugbar['messages']));

        $this->debugbarRenderer = new JavascriptRenderer($this->debugbar);
        $this->debugbarRenderer->setOptions([
            'base_url' => $this->config['pluginUrl'] . '/assets',
            'base_path' => $this->config['pluginPath'] . '/assets',
        ]);

        $this->queryCollector = new DataCollector\QueryCollector();
        $this->hookCollector = new DataCollector\HookCollector();

        $this->debugbar->addCollector($this->queryCollector);
        $this->debugbar->addCollector($this->hookCollector);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function collectQuery(array &$queries): void
    {
        if (!is_array($queries) || !count($queries)) {
            return;
        }

        foreach ($queries as $query) {
            $duration = $query['end_time'] - $query['start_time'];
            $rowCount = (!$query['error']['error_code'] && is_object($query['result'])) ? sql_num_rows($query['result']) ?? 0 : 0;

            $this->queryCollector->addQuery(
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

    /**
     * `sql_query_after` Hook에서 실행된 DB 쿼리 목록 수집
     * 단, 그누보드 Hook 특성상 처음과 끝의 일부가 누락 됨
     */
    public function hookSqlQueryAfter($result = null, $sql = null, $start_time = 0, $end_time = 0): void
    {
        global $g5;

        $duration = $end_time - $start_time;
        $error = [
            'error_code' => mysqli_errno($g5['connect_db']),
            'error_message' => mysqli_error($g5['connect_db']),
        ];
        $rowCount = (!$error['error_code'] && is_object($result)) ? sql_num_rows($result) ?? 0 : 0;

        $source = [];
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $found = false;

        foreach ($stack as $index => $trace) {
            if ($trace['function'] === 'sql_query') {
                $found = true;
            }
            if (isset($stack[$index + 1]) && $stack[$index + 1]['function'] === 'sql_fetch') {
                continue;
            }

            if ($found) {
                $trace['file'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $trace['file']);
                $source['file'] = $trace['file'];
                $source['line'] = $trace['line'];

                $parent = (isset($stack[$index + 1])) ? $stack[$index + 1] : [];
                if (isset($parent['function'])) {
                    if (
                        in_array($trace['function'], ['sql_query', 'sql_fetch'])
                        && (
                            isset($parent['function'])
                            && !in_array($parent['function'], ['sql_fetch', 'include', 'include_once', 'require', 'require_once'])
                        )
                    ) {
                        if (isset($parent['class']) && $parent['class']) {
                            $source['class'] = $parent['class'];
                            $source['type'] = $parent['type'];
                            $source['function'] = $parent['function'];
                        } else {
                            $source['function'] = $parent['function'];
                        }
                    }
                }
                break;
            }
        }

        $this->queryCollector->addQuery($sql, $duration, $rowCount, $error, $source);
    }

    public function render(): void
    {
        echo $this->debugbarRenderer->renderHead();
        echo $this->debugbarRenderer->render();
    }
}
