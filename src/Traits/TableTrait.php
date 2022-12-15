<?php

namespace Kkigomi\Plugin\Debugbar\Traits;

trait TableTrait
{
    public static function pluginTables(): array
    {
        $prefix = self::g5TablePrefix() . 'kg_debugbar_';
        return [
            'stack' => $prefix . 'stack'
        ];
    }

    public static function g5TablePrefix(): string
    {
        return \G5_TABLE_PREFIX;
    }

    public static function g5Tables(): array
    {
        global $g5;

        return array_filter($g5, function ($key) {
            if (function_exists('\str_ends_with')) {
                return \str_ends_with($key, '_table');
            }

            return substr_compare($key, '_table', -6) === 0;
        }, \ARRAY_FILTER_USE_KEY);
    }
}
