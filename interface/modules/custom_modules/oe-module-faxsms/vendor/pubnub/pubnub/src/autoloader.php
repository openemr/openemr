<?php

define('PUBNUB_LIB_BASE_DIR', __DIR__);

function pubnubAutoloader($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    $fileName = PUBNUB_LIB_BASE_DIR . DIRECTORY_SEPARATOR . $fileName;
    if (file_exists($fileName)) {
        require_once $fileName;
    }
}
spl_autoload_register('pubnubAutoloader', true, true);
