<?php

/**
 * Config Module.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Core;

use Symfony\Contracts\EventDispatcher\Event;

class ModuleLoadEvents extends Event
{
    /**
     * The modules.loaded event is dispatched after all active modules have been loaded.
     * getters for the modules loaded and any that failed to load.
     */
    public const MODULES_LOADED = 'modules.loaded';
    private array $modules;
    public function __construct($modules, $bootstrapFailures = [])
    {
        $modules = array_merge($modules, $bootstrapFailures);
        $this->modules = $modules;
    }

    /**
     * @return array
     */
    public function getModulesLoadStatus(): array
    {
        return $this->modules;
    }
}
