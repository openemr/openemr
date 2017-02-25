<?php
/**
 * @package Portal
 *
 * APPLICATION-WIDE CONFIGURATION SETTINGS
 *
 * This file contains application-wide configuration settings.  The settings
 * here will be the same regardless of the machine on which the app is running.
 *
 * This configuration should be added to version control.
 *
 * No settings should be added to this file that would need to be changed
 * on a per-machine basic (ie local, staging or production).  Any
 * machine-specific settings should be added to _machine_config.php
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */

/**
 * APPLICATION ROOT DIRECTORY
 * If the application doesn't detect this correctly then it can be set explicitly
 */
if (! GlobalConfig::$APP_ROOT)
	GlobalConfig::$APP_ROOT = realpath ( "./" );

/**
 * check is needed to ensure asp_tags is not enabled
 */
if (ini_get ( 'asp_tags' ))
	die ( '<h3>Server Configuration Problem: asp_tags is enabled, but is not compatible with Savant.</h3>' . '<p>You can disable asp_tags in .htaccess, php.ini or generate your app with another template engine such as Smarty.</p>' );

/**
 * INCLUDE PATH
 * Adjust the include path as necessary so PHP can locate required libraries
 */
set_include_path ( GlobalConfig::$APP_ROOT . '/libs/' . PATH_SEPARATOR . GlobalConfig::$APP_ROOT . '/fwk/libs' . PATH_SEPARATOR . get_include_path () );

/**
 * COMPOSER AUTOLOADER
 * Uncomment if Composer is being used to manage dependencies
 */
// $loader = require 'vendor/autoload.php';
// $loader->setUseIncludePath(true);

/**
 * SESSION CLASSES
 * Any classes that will be stored in the session can be added here
 * and will be pre-loaded on every page
 */
require_once "App/SecureApp.php";

/**
 * RENDER ENGINE
 * You can use any template system that implements
 * IRenderEngine for the view layer.
 * Phreeze provides pre-built
 * implementations for Smarty, Savant and plain PHP.
 */
require_once 'verysimple/Phreeze/SavantRenderEngine.php';
GlobalConfig::$TEMPLATE_ENGINE = 'SavantRenderEngine';
GlobalConfig::$TEMPLATE_PATH = GlobalConfig::$APP_ROOT . '/templates/';

/**
 * ROUTE MAP
 * The route map connects URLs to Controller+Method and additionally maps the
 * wildcards to a named parameter so that they are accessible inside the
 * Controller without having to parse the URL for parameters such as IDs
 */
GlobalConfig::$ROUTE_MAP = array (
		
		// default controller when no route specified
		// 'GET:' => array('route' => 'Default.Home'),
		'GET:' => array (
				'route' => 'Provider.Home' 
		),
		'GET:provider' => array (
				'route' => 'Provider.Home' 
		),
		
		// authentication routes
		'GET:loginform' => array (
				'route' => 'SecureApp.LoginForm' 
		),
		'POST:login' => array (
				'route' => 'SecureApp.Login' 
		),
		'GET:secureuser' => array (
				'route' => 'SecureApp.UserPage' 
		),
		'GET:secureadmin' => array (
				'route' => 'SecureApp.AdminPage' 
		),
		'GET:logout' => array (
				'route' => 'SecureApp.Logout' 
		),
		
		// Patient
		'GET:patientdata' => array (
				'route' => 'Patient.ListView' 
		),
		'GET:patient/(:num)' => array (
				'route' => 'Patient.SingleView',
				'params' => array (
						'id' => 1 
				) 
		),
		'GET:api/patientdata' => array (
				'route' => 'Patient.Query' 
		),
		'POST:api/patient' => array (
				'route' => 'Patient.Create' 
		),
		'GET:api/patient/(:num)' => array (
				'route' => 'Patient.Read',
				'params' => array (
						'id' => 2 
				) 
		),
		'PUT:api/patient/(:num)' => array (
				'route' => 'Patient.Update',
				'params' => array (
						'id' => 2 
				) 
		),
		'DELETE:api/patient/(:num)' => array (
				'route' => 'Patient.Delete',
				'params' => array (
						'id' => 2 
				) 
		),
		'PUT:api/portalpatient/(:num)' => array (
				'route' => 'PortalPatient.Update',
				'params' => array (
						'id' => 2 
				) 
		),
		'GET:api/portalpatient/(:num)' => array (
				'route' => 'PortalPatient.Read',
				'params' => array (
						'id' => 2 
				) 
		),
		
		// OnsiteDocument
		'GET:onsitedocuments' => array (
				'route' => 'OnsiteDocument.ListView' 
		),
		'GET:onsitedocument/(:num)' => array (
				'route' => 'OnsiteDocument.SingleView',
				'params' => array (
						'id' => 1 
				) 
		),
		'GET:api/onsitedocuments' => array (
				'route' => 'OnsiteDocument.Query' 
		),
		'POST:api/onsitedocument' => array (
				'route' => 'OnsiteDocument.Create' 
		),
		'GET:api/onsitedocument/(:num)' => array (
				'route' => 'OnsiteDocument.Read',
				'params' => array (
						'id' => 2 
				) 
		),
		'PUT:api/onsitedocument/(:num)' => array (
				'route' => 'OnsiteDocument.Update',
				'params' => array (
						'id' => 2 
				) 
		),
		'DELETE:api/onsitedocument/(:num)' => array (
				'route' => 'OnsiteDocument.Delete',
				'params' => array (
						'id' => 2 
				) 
		),
		
		// OnsitePortalActivity
		'GET:onsiteportalactivities' => array (
				'route' => 'OnsitePortalActivity.ListView' 
		),
		'GET:onsiteportalactivity/(:num)' => array (
				'route' => 'OnsitePortalActivity.SingleView',
				'params' => array (
						'id' => 1 
				) 
		),
		'GET:api/onsiteportalactivities' => array (
				'route' => 'OnsitePortalActivity.Query' 
		),
		'POST:api/onsiteportalactivity' => array (
				'route' => 'OnsitePortalActivity.Create' 
		),
		'GET:api/onsiteportalactivity/(:num)' => array (
				'route' => 'OnsitePortalActivity.Read',
				'params' => array (
						'id' => 2 
				) 
		),
		'PUT:api/onsiteportalactivity/(:num)' => array (
				'route' => 'OnsitePortalActivity.Update',
				'params' => array (
						'id' => 2 
				) 
		),
		'DELETE:api/onsiteportalactivity/(:num)' => array (
				'route' => 'OnsitePortalActivity.Delete',
				'params' => array (
						'id' => 2 
				) 
		),
		// OnsiteActivityView
		'GET:onsiteactivityviews' => array (
				'route' => 'OnsiteActivityView.ListView' 
		),
		'GET:onsiteactivityview/(:any)' => array (
				'route' => 'OnsiteActivityView.SingleView',
				'params' => array (
						'id' => 1 
				) 
		),
		'GET:api/onsiteactivityviews' => array (
				'route' => 'OnsiteActivityView.Query' 
		),
		'POST:api/onsiteactivityview' => array (
				'route' => 'OnsiteActivityView.Create' 
		),
		'GET:api/onsiteactivityview/(:any)' => array (
				'route' => 'OnsiteActivityView.Read',
				'params' => array (
						'id' => 2 
				) 
		),
		'PUT:api/onsiteactivityview/(:any)' => array (
				'route' => 'OnsiteActivityView.Update',
				'params' => array (
						'id' => 2 
				) 
		),
		'DELETE:api/onsiteactivityview/(:any)' => array (
				'route' => 'OnsiteActivityView.Delete',
				'params' => array (
						'id' => 2 
				) 
		),
		
		// User
		'GET:users' => array (
				'route' => 'User.ListView' 
		),
		'GET:user/(:num)' => array (
				'route' => 'User.SingleView',
				'params' => array (
						'id' => 1 
				) 
		),
		'GET:api/users' => array (
				'route' => 'User.Query' 
		),
		'POST:api/user' => array (
				'route' => 'User.Create' 
		),
		'GET:api/user/(:num)' => array (
				'route' => 'User.Read',
				'params' => array (
						'id' => 2 
				) 
		),
		'PUT:api/user/(:num)' => array (
				'route' => 'User.Update',
				'params' => array (
						'id' => 2 
				) 
		),
		'DELETE:api/user/(:num)' => array (
				'route' => 'User.Delete',
				'params' => array (
						'id' => 2 
				) 
		),
		
		// UsersFacility
		'GET:usersfacilities' => array (
				'route' => 'UsersFacility.ListView' 
		),
		'GET:usersfacility/(:any)' => array (
				'route' => 'UsersFacility.SingleView',
				'params' => array (
						'tablename' => 1 
				) 
		),
		'GET:api/usersfacilities' => array (
				'route' => 'UsersFacility.Query' 
		),
		'POST:api/usersfacility' => array (
				'route' => 'UsersFacility.Create' 
		),
		'GET:api/usersfacility/(:any)' => array (
				'route' => 'UsersFacility.Read',
				'params' => array (
						'tablename' => 2 
				) 
		),
		'PUT:api/usersfacility/(:any)' => array (
				'route' => 'UsersFacility.Update',
				'params' => array (
						'tablename' => 2 
				) 
		),
		'DELETE:api/usersfacility/(:any)' => array (
				'route' => 'UsersFacility.Delete',
				'params' => array (
						'tablename' => 2 
				) 
		),
		
		// catch any broken API urls
		'GET:api/(:any)' => array (
				'route' => 'Provider.ErrorApi404' 
		),
		'PUT:api/(:any)' => array (
				'route' => 'Provider.ErrorApi404' 
		),
		'POST:api/(:any)' => array (
				'route' => 'Provider.ErrorApi404' 
		),
		'DELETE:api/(:any)' => array (
				'route' => 'Provider.ErrorApi404' 
		) 
);

?>