<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Jacob T.Paul <jacob@zhservices.com>
*    @author  Shalini Balakrishnan <shalini@zhservices.com>
* +------------------------------------------------------------------------------+
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
if (strtolower($controllerName) == 'soap' && strtolower($actionName) == 'index') {
    $ignoreAuth_offsite_portal = true;
} elseif ($_REQUEST['recipient'] === 'patient' && $_REQUEST['site'] && $controllerName) {
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
require_once(dirname(__FILE__)."/../../../../library/log.inc");

chdir(dirname(__DIR__));

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
