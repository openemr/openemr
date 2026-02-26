<?php

use OpenEMR\Core\AbstractModuleActionListener;

class ModuleManagerListener extends AbstractModuleActionListener
{
    public function __construct()
    {
        parent::__construct();
    }

    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        if (method_exists(self::class, $methodName)) {
            return self::$methodName($modId, $currentActionStatus);
        }
        return $currentActionStatus;
    }

    public static function getModuleNamespace(): string
    {
        return 'OpenEMR\\Modules\\SafetySentinel\\';
    }

    public static function initListenerSelf(): self
    {
        return new self();
    }

    private function enable($modId, string $currentActionStatus): string
    {
        return $currentActionStatus;
    }

    private function disable($modId, string $currentActionStatus): string
    {
        return $currentActionStatus;
    }

    private function unregister($modId, string $currentActionStatus): string
    {
        return $currentActionStatus;
    }
}
