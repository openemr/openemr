<?php

namespace OpenEMR\Core;

class SystemConfigurationContext
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new SystemConfigurationContext();
        }
        return self::$instance;
    }
}
