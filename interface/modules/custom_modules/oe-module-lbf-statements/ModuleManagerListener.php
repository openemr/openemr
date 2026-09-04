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

    public static function getModuleNamespace(): string
    {
        return 'OpenEMR\\Modules\\LbfStatements\\';
    }

    public static function initListenerSelf(): ModuleManagerListener
    {
        return new self();
    }

    private static function install(mixed $modId, string $currentActionStatus): string
    {
        unset($modId);
        return $currentActionStatus;
    }

    private static function enable(mixed $modId, string $currentActionStatus): string
    {
        unset($modId);
        return $currentActionStatus;
    }

    private static function disable(mixed $modId, string $currentActionStatus): string
    {
        unset($modId);
        return $currentActionStatus;
    }

    private static function unregister(mixed $modId, string $currentActionStatus): string
    {
        unset($modId);
        return $currentActionStatus;
    }
}
