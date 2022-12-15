<?php
namespace Kkigomi\Plugin\Debugbar\Logger;

use DebugBar\DataCollector\MessagesCollector;
use Psr\Log\AbstractLogger;

class DebugbarLogger extends AbstractLogger
{
    private $messageCollector;

    public function __construct(MessagesCollector $messageCollector)
    {
        $this->messageCollector = $messageCollector;
    }

    public function log($level, $message, array $context = []): void
    {
        if (is_string($message)) {
            $message = $this->messageCollector->interpolate($message, $context);
        }

        $this->messageCollector->addMessage($message, $level);
    }
}
