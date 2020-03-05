<?php

/**
 * OeModuleRxCUI class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    MD Support <mdsupport@users.sourceforge.net>
 * @copyright Copyright (c) 2020 MD Support <mdsupport@users.sourceforge.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules;

class OeModuleRxCUI extends OeModule
{

    function __construct() {
        $this->setProp('Name', 'RxCUI');
    }

    // Required methods
    public function getProp($strProp) {
        return $this->modProp[$strProp];
    }

    protected function setProp($strProp, $vProp) {
        return $this->modProp[$strProp] = $vProp;
    }

    // Required actions to install, config, enable and disable this module.
    // Uninstall actions are outside the scope of this class.
    public function actionInstall() {
        print('<p>Installing RxCUI module related database tables and settings.</p>');
        return true;
    }

    public function actionConfig() {
        // Each module should provide iframe src
        // Until issues with zend realpath are resolved, send realpath
        // $fnCfg = explode(realpath('.'), __DIR__ . '/moduleConfig.php')[1];
        return __DIR__ . '/moduleConfig.php';
    }

    public function actionEnable() {
        print('<p>Enabling RxCUI module for productive use.</p>');
        $this->modProp['boolActive'] = true;
        return true;
    }

    public function actionDisable() {
        print('<p>Disabling RxCUI module.</p>');
        $this->modProp['boolActive'] = false;
        return true;
    }

    // Every module must be able to report self configuration details
    public function getConfig() {
        print('<p>Configuration data for RxCUI module.</p>');
        return true;
    }
}
