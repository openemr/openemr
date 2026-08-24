<?php

/**
 * Register the LBF Form Statements namespace and boot the module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;

/**
 * @var ModulesClassLoader $classLoader
 */
$classLoader->registerNamespaceIfNotExists(
    "OpenEMR\\Modules\\LbfStatements\\",
    __DIR__ . DIRECTORY_SEPARATOR . "src"
);

$bootstrap = Bootstrap::instantiate(
    OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher(),
    OEGlobalsBag::getInstance()->getKernel()
);
