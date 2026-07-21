<?php

/**
 * openemr/interface/modules/zend_modules/public/index.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Shalini Balakrishnan <shalini@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\ModulesApplication;
use OpenEMR\Core\OEEnvBag;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\Routing\ZendModuleApplication;
use OpenEMR\Core\Routing\ZendModuleRouteLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . "/../../../globals.php");
require_once(__DIR__ . "/../../../../library/forms.inc.php");
require_once(__DIR__ . "/../../../../library/options.inc.php");

chdir(dirname(__DIR__));

// Run the application! ModulesApplication is created in globals.php and stashed
// in the globals bag, which is untyped, so narrow it here.
$modulesApplication = OEGlobalsBag::getInstance()->get('modules_application');
if (!$modulesApplication instanceof ModulesApplication) {
    die("global modules_application is not defined.  Cannot run zend module request");
}

// Strangler seam (PHP 8.5 migration off laminas-mvc): when the feature flag is
// on and the request is on the seam's canary allowlist, serve it through the new
// Symfony-routing + OEHttpKernel runtime instead of the legacy Laminas runtime.
// Default is off; even when on, only proven canary routes divert — every other
// route falls through to the legacy runtime, because the resolver shim does not
// yet set up the Laminas MVC controller context that most controllers need.
// See ZendModuleApplication::CANARY_PATHS.
//
// The seam is experimental and opt-in, so any failure during its setup or
// dispatch is treated as non-fatal: log it and fall through to the legacy
// runtime rather than aborting the request. handle() builds the full Response
// before sending, so a throw cannot leave a partially-sent response behind.
if (OEEnvBag::getInstance()->getBoolean('OPENEMR__ZEND_SYMFONY_SEAM')) {
    try {
        $eventDispatcher = $modulesApplication->getServiceManager()->get(EventDispatcherInterface::class);
        if (!$eventDispatcher instanceof EventDispatcherInterface) {
            throw new \RuntimeException('Module ServiceManager did not provide an EventDispatcher');
        }
        $seam = new ZendModuleApplication(
            $modulesApplication->getServiceManager(),
            $eventDispatcher,
            new ZendModuleRouteLoader(__DIR__ . '/..'),
        );

        $request = Request::createFromGlobals();
        if ($seam->matches($request->getPathInfo())) {
            $seam->handle($request)->send();
            return;
        }
    } catch (\RuntimeException $seamException) {
        // Recoverable seam failures fall back to the legacy runtime. The seam's
        // own guards plus the Symfony routing / HttpKernel exceptions all extend
        // \RuntimeException; \Error and \ErrorException are intentionally NOT
        // caught — those are programmer bugs that should propagate to the global
        // handler, not be masked by a fallback.
        ServiceContainer::getLogger()->error(
            'zend_modules Symfony seam failed; falling back to legacy runtime',
            ['exception' => $seamException],
        );
    }
}

// $time_start = microtime(true);
// run the request lifecycle.  The application has already inited in the globals.php
$modulesApplication->run();
// $time_end = microtime(true);
// echo "App runtime: " . ($time_end - $time_start) . "<br />";
