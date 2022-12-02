<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

// PHP 버전 제한. 오류 방지
if (PHP_VERSION_ID >= 70400) {
    /**
     * @return void|Psr\Log\LoggerInterface
     * @since 0.2.0
     */
    function logger()
    {
        $logger = Kkigomi\Plugin\PHPDebugBar\PHPDebugBar::getInstance()->getLogger();

        if (\KG_DEBUGBAR_ENABLE === true && func_num_args()) {
            $logger->debug(...func_get_args());
            return;
        }

        return $logger;
    }

    if (\KG_DEBUGBAR_ENABLE === true) {
        $kgDebugbarCollectorEnabled_1ff9dfc8ced345a2abb4ad98b85b297f = (
            isset($GLOBALS['g5_debug'])
            && is_array($GLOBALS['g5_debug']['sql'])
            && count($GLOBALS['g5_debug']['sql'])
        );

        $kgDebugbarInstance_1ff9dfc8ced345a2abb4ad98b85b297f = Kkigomi\Plugin\PHPDebugBar\PHPDebugBar::getInstance();
        $kgDebugbarInstance_1ff9dfc8ced345a2abb4ad98b85b297f->boot([
            'pluginUrl' => \KG_DEBUGBAR_URL,
            'pluginPath' => \KG_DEBUGBAR_PATH,
            'collectQuery' => $kgDebugbarCollectorEnabled_1ff9dfc8ced345a2abb4ad98b85b297f,
            'queries' => &$GLOBALS['g5_debug']['sql']
        ]);

        add_event('tail_sub', 'phpdebugbarEnable_1ff9dfc8ced345a2abb4ad98b85b297f', 900);

        if (!$kgDebugbarCollectorEnabled_1ff9dfc8ced345a2abb4ad98b85b297f) {
            add_event('sql_query_after', [
                Kkigomi\Plugin\PHPDebugBar\PHPDebugBar::class,
                'hookSqlQueryAfter'
            ], \G5_HOOK_DEFAULT_PRIORITY, 4);
        }

        function phpdebugbarEnable_1ff9dfc8ced345a2abb4ad98b85b297f()
        {
            global $is_admin;

            if ($is_admin !== 'super' && !in_array($_SERVER['REMOTE_ADDR'], \KG_DEBUGBAR_ENABLE_IP)) {
                return;
            }

            $debugbarInstance = Kkigomi\Plugin\PHPDebugBar\PHPDebugBar::getInstance();
            $debugbarInstance->collectQuery($GLOBALS['g5_debug']['sql']);
            $debugbarInstance->render();
        }
    }
}
