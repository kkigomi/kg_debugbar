<?php

if (\KG_DEBUGBAR_ENABLE === true) {
    Kkigomi\Plugin\PHPDebugBar\PHPDebugBar::setConfig([
        'pluginUrl' => \KG_DEBUGBAR_URL
    ]);

    add_event('tail_sub', 'kgEnablePhpdebugbar');
    add_event('sql_query_after', [Kkigomi\Plugin\PHPDebugBar\PHPDebugBar::class, 'hookSqlQueryAfter'], null, 4);

    /**
     * @global string $is_admin
     */
    function kgEnablePhpdebugbar()
    {
        global $is_admin;

        if ($is_admin !== 'super' && !in_array($_SERVER['REMOTE_ADDR'], \KG_DEBUGBAR_ENABLE_IP)) {
            return;
        }

        $kgDebugBarInstance = Kkigomi\Plugin\PHPDebugBar\PHPDebugBar::getInstance();
        $kgDebugBarInstance->render();
    }
}
