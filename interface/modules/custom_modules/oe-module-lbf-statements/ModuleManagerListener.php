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
     * @param mixed  $methodName
     * @param mixed  $modId
     */
    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        if (is_string($methodName) && method_exists(self::class, $methodName)) {
            $result = self::$methodName($modId, $currentActionStatus);
            return is_string($result) ? $result : $currentActionStatus;
        }
        return $currentActionStatus;
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
