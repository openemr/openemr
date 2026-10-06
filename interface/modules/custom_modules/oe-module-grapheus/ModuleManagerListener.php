<?php

/**
 * Module Manager hooks for Grapheus.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\AbstractModuleActionListener;

class ModuleManagerListener extends AbstractModuleActionListener
{
    public function __construct()
    {
        parent::__construct();
    }

    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        if ($methodName === 'unregister') {
            return $this->unregister($currentActionStatus);
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

    /** Unregistering removes the connected keys (useless without the module); keeps the record of what was applied. */
    private function unregister(string $currentActionStatus): string
    {
        QueryUtils::sqlStatementThrowException("DROP TABLE IF EXISTS `grapheus_keys`", []);
        return $currentActionStatus;
    }
}
