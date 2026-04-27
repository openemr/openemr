<?php

/**
 * GCIP module configuration entry point for Module Manager.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

require_once dirname(__FILE__, 4) . '/globals.php';

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;

$classLoader = new ModulesClassLoader(OEGlobalsBag::getInstance()->getString('fileroot'));
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\GcipAuth\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src',
);

$module_config = 1;

require_once __DIR__ . '/public/admin.php';
exit;
