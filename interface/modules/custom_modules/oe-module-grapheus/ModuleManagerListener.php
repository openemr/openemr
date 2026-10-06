<?php

/**
 * Module Manager hooks for Grapheus.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
        return 'Exetazo\\Grapheus\\';
    }

    public static function initListenerSelf(): ModuleManagerListener
    {
        return new self();
    }

    private function disable($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }

    /** Unregistering removes the connected keys (they would be useless without the module), keeps the audit of what was applied. */
    private function unregister($modId, $currentActionStatus)
    {
        sqlStatement("DROP TABLE IF EXISTS `grapheus_keys`");
        return $currentActionStatus;
    }
}
