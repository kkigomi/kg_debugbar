<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

// PHP 버전 제한. 오류 방지
if (PHP_VERSION_ID < 70400) {
    return;
}

define('KG_DEBUGBAR_VERSION', '0.3.0');
define('KG_DEBUGBAR_VERSION_ID', 300);

if (\KG_DEBUGBAR_ENABLE !== true) {
    return;
}

Kkigomi\Plugin\Debugbar\Debugbar::getInstance()->boot([
    'pluginUrl' => \KG_DEBUGBAR_URL,
    'pluginPath' => \KG_DEBUGBAR_PATH
]);
Kkigomi\Plugin\Debugbar\HookListener::getInstance();

