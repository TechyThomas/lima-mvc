<?php

namespace Lima\Core;

class System
{
    private static function GetOverride($class)
    {
        $overrideFile = LIMA_ROOT . '/system/overrides.php';

        if (!file_exists($overrideFile)) {
            return null;
        }

        include_once($overrideFile);

        if (empty($overrides)) {
            return null;
        }

        return $overrides[$class];
    }

    public static function GetView(array $data = [])
    {
        $viewClass = self::GetOverride('View');

        if (empty($viewClass)) {
            return new View($data);
        }

        return new $viewClass($data);
    }
}