<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

// PHP 버전 제한. 오류 방지
if (PHP_VERSION_ID < 70400) {
    return;
}

/**
 * @deprecated 0.3.0
 * @param mixed? $message
 * @param array? $context
 * @return void|Psr\Log\LoggerInterface
 */
function logger($message = null, $context = [])
{
    $logger = Kkigomi\Plugin\Debugbar\Debugbar::getInstance()->getLogger();

    if (\KG_DEBUGBAR_ENABLE === true && func_num_args()) {
        $logger->debug(...func_get_args());
        return;
    }

    return $logger;
}

// function startTimeline(string $name, ?string $label = null, ?string $collector = null)
// {
//     if (Kkigomi\Plugin\Debugbar\Debugbar::booted()) {
//         $instance = Kkigomi\Plugin\Debugbar\Debugbar::getInstance();
//         $instance->startMeasure($name, $label, $collector);
//     }
// }

// function endTimeline(string $name, ?array $params = [])
// {
//     if (Kkigomi\Plugin\Debugbar\Debugbar::booted()) {
//         $instance = Kkigomi\Plugin\Debugbar\Debugbar::getInstance();
//         $instance->stopMeasure($name, $params);
//     }
// }
