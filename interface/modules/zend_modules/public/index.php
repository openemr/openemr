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


//fetching controller name and action name from the SOAP request
$urlArray = explode('/', $_SERVER['REQUEST_URI']);
$countUrlArray = count($urlArray);
preg_match('/\/(\w*)\?/', $_SERVER['REQUEST_URI'], $matches);
$actionName = isset($matches[1]) ? $matches[1] : '';
$controllerName = isset($urlArray[$countUrlArray-2]) ? $urlArray[$countUrlArray-2] : '';

//skipping OpenEMR authentication if the controller is SOAP and action is INDEX
//SOAP authentication is done in the contoller EncounterccdadispatchController
if ($_REQUEST['recipient'] === 'patient' && $_REQUEST['site'] && $controllerName) {
    session_id($_REQUEST['me']);
    session_start();
    $ignoreAuth_onsite_portal_two = false; // eval'ed in globals but why not...
    if ($_SESSION['pid'] && $_SESSION['sessionUser']=='-patient-' && $_SESSION['portal_init']) {
        // Onsite portal was validated and patient authorized and re-validated via forwarded session.
        $ignoreAuth_onsite_portal_two = true;
    }
}

require_once(dirname(__FILE__)."/../../../globals.php");
require_once(dirname(__FILE__)."/../../../../library/forms.inc");
require_once(dirname(__FILE__)."/../../../../library/options.inc.php");
require_once(dirname(__FILE__)."/../../../../library/acl.inc");

chdir(dirname(__DIR__));

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
