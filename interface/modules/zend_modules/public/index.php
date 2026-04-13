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

use OpenEMR\Core\OEGlobalsBag;

require_once(__DIR__ . "/../../../globals.php");
require_once(__DIR__ . "/../../../../library/forms.inc.php");
require_once(__DIR__ . "/../../../../library/options.inc.php");

chdir(dirname(__DIR__));

// Run the application!
/** @var OpenEMR/Core/ModulesApplication
 * Defined in globals.php
*/
if (!empty(OEGlobalsBag::getInstance()->get('modules_application'))) {
    // $time_start = microtime(true);
    // run the request lifecycle.  The application has already inited in the globals.php
    OEGlobalsBag::getInstance()->get('modules_application')->run();
    // $time_end = microtime(true);
    // echo "App runtime: " . ($time_end - $time_start) . "<br />";
} else {
    die("global modules_application is not defined.  Cannot run zend module request");
}
