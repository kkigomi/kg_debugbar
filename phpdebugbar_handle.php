<?php
include '../../common.php';

if (\KG_DEBUGBAR_ENABLE === true) {
    $debugbarInstance = Kkigomi\Plugin\Debugbar\Debugbar::getInstance();
    $openHandler = new \DebugBar\OpenHandler($debugbarInstance->getDebugbar());
    $openHandler->handle();
}
