<?php
/**
 * 이 플러그인의 설정은 /config.custom.php 파일을 참조합니다.
 * 이 플러그인의 설정을 변경하기위해 이 파일을 직접 수정하지 마세요.
 *
 * @var string \KG_DEBUGBAR_DIR 플러그인 폴더명
 * @var bool \KG_DEBUGBAR_ENABLE PHP DebugBar 활성화. 최고관리자에게만 활성화 됨
 * @var array \KG_DEBUGBAR_ENABLE_IP 일치하는 IP에 대해 PHP DebugBar 활성화
 *                                   관리자 여부 상관 없음
 */
if (!defined('_GNUBOARD_')) {
    exit;
}
if (PHP_VERSION_ID < 70400) {
    return;
}

if (file_exists(G5_PATH . '/config.custom.php')) {
    include_once G5_PATH . '/config.custom.php';
}

if (!defined('KG_DEBUGBAR_DIR')) {
    /**
     * @var string
     */
    define('KG_DEBUGBAR_DIR', 'kg_phpdebugbar');
}

if (!defined('KG_DEBUGBAR_ENABLE_IP')) {
    /**
     * @var array
     */
    define('KG_DEBUGBAR_ENABLE_IP', []);
}

if (!defined('KG_DEBUGBAR_ENABLE')) {
    $enable = false;
    if (in_array($_SERVER['REMOTE_ADDR'], KG_DEBUGBAR_ENABLE_IP)) {
        $enable = true;
    }

    /**
     * PHP Debugbar 활성화
     * array `PHPDEBUGBAR_ENABLE_IP` IP 목록에 접속자의 IP가 있으면 활성화 함
     */
    define('KG_DEBUGBAR_ENABLE', $enable);
}

define('KG_DEBUGBAR_PATH', G5_PLUGIN_PATH . '/' . KG_DEBUGBAR_DIR);
define('KG_DEBUGBAR_URL', G5_PLUGIN_URL . '/' . KG_DEBUGBAR_DIR);

include_once KG_DEBUGBAR_PATH . '/vendor/autoload.php';
