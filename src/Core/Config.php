<?php

namespace Lima\Core;

class Config
{
    public static function get($key)
    {
        return $GLOBALS['config'][$key] ?? null;
    }

    public static function set($key, $value)
    {
        $GLOBALS['config'][$key] = $value;
    }
}