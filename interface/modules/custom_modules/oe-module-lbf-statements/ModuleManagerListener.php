<?php

/**
 * Module Manager action hooks.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Core\AbstractModuleActionListener;

class ModuleManagerListener extends AbstractModuleActionListener
{
    /**
     * Required by AbstractModuleActionListener.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $methodName
     * @param string $modId
     */
    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        return match ($methodName) {
            'install' => self::install($modId, $currentActionStatus),
            'enable' => self::enable($modId, $currentActionStatus),
            'disable' => self::disable($modId, $currentActionStatus),
            'unregister' => self::unregister($modId, $currentActionStatus),
            default => $currentActionStatus,
        };
    }

    /**
     * PSR-4 namespace for this module's classes.
     */
    public static function getModuleNamespace(): string
    {
        return 'OpenEMR\\Modules\\LbfStatements\\';
    }

    /**
     * Build a listener for Module Manager.
     */
    public static function initListenerSelf(): ModuleManagerListener
    {
        return new self();
    }

    /**
     * Install is a no-op; table.sql is applied by Module Manager.
     */
    private static function install(mixed $modId, string $currentActionStatus): string
    {
        unset($modId);
        return $currentActionStatus;
    }

    /**
     * Enable is a no-op beyond the core module state.
     */
    private static function enable(mixed $modId, string $currentActionStatus): string
    {
        unset($modId);
        return $currentActionStatus;
    }

    /**
     * Disable is a no-op beyond the core module state.
     */
    private static function disable(mixed $modId, string $currentActionStatus): string
    {
        unset($modId);
        return $currentActionStatus;
    }

    /**
     * Unregister is a no-op; cleanup.sql is applied by Module Manager.
     */
    private static function unregister(mixed $modId, string $currentActionStatus): string
    {
        unset($modId);
        return $currentActionStatus;
    }
}
