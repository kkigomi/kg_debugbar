<?php

namespace Kkigomi\Plugin\Debugbar\Traits;

trait SingletonTrait
{
    protected static $singletonInstance = null;

    final public static function getInstance()
    {
        if (static::$singletonInstance === null) {
            static::$singletonInstance = new static ();
        }

        return static::$singletonInstance;
    }

    final private function __construct()
    {
        $this->singletonInstanceInit();
    }

    protected function singletonInstanceInit()
    {
    }

    final function __wakeup()
    {
    }

    final function __clone()
    {
    }
}
