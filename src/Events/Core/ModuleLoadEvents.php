<?php

namespace OpenEMR\Events\Core;

use Symfony\Contracts\EventDispatcher\Event;

class ModuleLoadEvents extends Event
{
    public const MODULES_LOADED = 'modules.loaded';
    private array $modules;
    public function __construct($modules, $bootstrapFailures = [])
    {
        $modules = array_merge($modules, $bootstrapFailures);
        $this->modules = $modules;
    }

    public function getModulesLoadStatus(): array
    {
        return $this->modules;
    }
}
