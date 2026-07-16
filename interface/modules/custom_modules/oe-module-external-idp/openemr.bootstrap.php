<?php

/**
 * Bootstrap for the External IdP module.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ExternalIdp\Bootstrap;

$projectDir = OEGlobalsBag::getInstance()->getProjectDir();
$classLoader = new ModulesClassLoader($projectDir);
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\ExternalIdp\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

$bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
$bootstrap->subscribeToEvents();
