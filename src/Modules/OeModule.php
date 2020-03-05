<?php

/**
 * OeModule class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    MD Support <mdsupport@users.sourceforge.net>
 * @copyright Copyright (c) 2020 MD Support <mdsupport@users.sourceforge.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules;

abstract class OeModule
{
    protected $modProp = [
        'Name' => '',
        'boolActive' => false
    ];

    // Force Extending class to define these methods
    abstract protected function getProp($strProp);
    abstract protected function setProp($strProp, $vProp);

    // Every module must be able to install, config, enable and disable itself.
    // Uninstall actions are outside the scope of this class.
    abstract protected function actionInstall();
    abstract protected function actionConfig();
    abstract protected function actionEnable();
    abstract protected function actionDisable();

    // Every module must be able to report self configuration details
    abstract protected function getConfig();

    // Common method
    public function isActive() {
        return $this->getProp('boolActive');
    }
}
