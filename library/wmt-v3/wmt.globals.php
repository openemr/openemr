<?php
/** **************************************************************************
 *	WMT.GLOBALS.PHP
 *
 *	Copyright (c)2017 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage utilities
 *  @version 2.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new functions are defined in the WMT namespace
 */
namespace wmt;

/**
 * Define constants used by the WMT applications;
 */
define('ACCESS_ACL',1);
define('CREATE_ACL',2);
define('UPDATE_ACL',3);
define('DELETE_ACL',4);
define('SUPER_ACL',9);

/**
 * Auto class loader function for all WMT applications. The class name passed to the
 * function must contain exactly two parts; a "prefix" such as "wmt" and a "name" which
 * must start with a capital letter. The "prefix" will be prepended to the "name" to 
 * create a file in the form of "prefixName.class.php". This class file will be loaded
 * from the "~/library/wmt-v3/classes" directory.
 * 
 * @version 2.0.0
 * @since 2017-05-14
 * @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 * @param 	String $class consisting of "prefix\name"
 * @throws 	Exception class file not found
 * 			Exception class not loadable
 */
if (!function_exists('wmt\ClassLoader')) {
	function ClassLoader($class) {
		$parts = explode('\\', $class); // break into components
		if (reset($parts) != 'wmt') return; // not a wmt class
		
		if (strpos(end($parts), 'Module') === false) { // loading a class
			$class_file = $GLOBALS['srcdir']."/wmt-v3/classes/wmt". end($parts) .".class.php";
			if (file_exists($class_file)) {
				require_once($class_file);
				if (!class_exists($class))
					throw new \Exception("Class [$class] could not be loaded");
			} 
			else {
				throw new \Exception("Class [$class] not found in WMT class library");
			}
		}
		else { // loading a module
			$file_name = str_replace('Module', '', end($parts));
			$module_file = $GLOBALS['srcdir']."/wmt-v3/modules/wmt". $file_name .".module.php";
			if (file_exists($module_file)) {
				require_once($module_file);
				if (!class_exists($class))
					throw new \Exception("Module [$class] could not be loaded");
			}				
			else {
				throw new \Exception("Module [$class] not found in WMT module library");
			}
		}
	}

	// Make sure the class loader funtion is on the spl_autoload queue
	$splList = spl_autoload_functions();
	if (!$splList || !$splList['wmt\ClassLoader']) {
		spl_autoload_register('wmt\ClassLoader');
	}
};

/**
 * This function retrieves the security level for the currently signed in user
 * and compares it to the minimum level required. If user is authorized, their
 * user level is returned; otherwise, no acl level is provided.
 * 
 * @version 1.0.0
 * @since 2017-01-01
 * @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 * @param 	String $class consisting of "prefix\name"
 */
if (!function_exists('wmt\SecurityCheck')) {
	function SecurityCheck($realm,$level) {
		$user_acl = false;
		
		if (!$_SESSION['authUser'] || $_SESSION['authUser'] == '')
			die ("FATAL ERROR: missing user credentials, please log in again!!");
	
		// Security setup
		if (\OpenEMR\Common\Acl\AclMain::aclCheckCore($realm, 'access')) $user_acl = ACCESS_ACL;
		if (\OpenEMR\Common\Acl\AclMain::aclCheckCore($realm, 'enter')) $user_acl = CREATE_ACL;
		if (\OpenEMR\Common\Acl\AclMain::aclCheckCore($realm, 'update')) $user_acl = UPDATE_ACL;
		if (\OpenEMR\Common\Acl\AclMain::aclCheckCore($realm, 'delete')) $user_acl = DELETE_ACL;
		if (\OpenEMR\Common\Acl\AclMain::aclCheckCore($realm, 'detail')) $user_acl = SUPER_ACL;
		if ($user_acl < $level) $user_acl = false; // does not meet requirement
		
		$user_acl = SUPER_ACL; // ---- TESTING -----
		
		return $user_acl;
	}
}


/**
 * This function generates an expection to obtain a trace and prints error
 * informaiton to the screen and the stderr log.
 * 
 * @version 1.0.0
 * @since 2017-01-01
 * @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 * @param 	String $level - the severity of the error
 * @param	Sting $message - the error message
 */
if (!function_exists('wmt\LogError')) {
	function LogError($level, $message) {

		// Generate stardard exception construct
		try {
			throw new \Exception($message);
		}
		catch (\Exception $e) {
			// Print the appropriate output
			$error = $level . ": " . $message;
			echo $error;
				
			// Log output to error file
			$error .= "\n";
			$error .= $e->getTraceAsString();
			error_log($error);
		}
		
	}
}

/**
 * This function reports the error caught by an exception thrown in the
 * main code to the stderr log file and the screen.
 *
 * @version 1.0.0
 * @since 2017-01-01
 * @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 * @param 	Exception $e 
 */
if (!function_exists('wmt\LogException')) {
	function LogException($e) {

		// Print the appropriate output
		$error = "EXCEPTION: " . $e->getMessage();
		echo $error;

		// Log output to error file
		$error .= "\n";
		$error .= $e->getTraceAsString();
		error_log($error);

	}
}

?>
