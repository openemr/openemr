<?php
require_once ("interface/globals.php");
Use OpenEMR\Modules;

$compAuto = require_once ($GLOBALS['vendor_dir']."/composer/autoload_classmap.php");
// mdsupport - Needs fix
// $compAutoCls = preg_grep("/\\Modules\\OeModule/", array_keys($compAuto));

foreach ($compAuto as $compAutoCls => $compAutoDir) {
    if (!strpos($compAutoCls, "\\Modules\\OeModule")) {
        unset($compAuto[$compAutoCls]);
    } else {
        $objModule = new $compAutoCls();
        // Make sure the module is published.
        printf('%s is %s', $objModule->getProp('Name'), ($objModule->isActive() ? 'registered' : 'available'));
        // Test of various actions
        $objModule->actionInstall();
        printf(
            '<p>Configure %s module using %s to get admin input.</p>',
            $objModule->getProp('Name'), $objModule->actionConfig()
        );
        $objModule->getConfig();
        $objModule->actionEnable();
        $objModule->actionDisable();
    }
}

