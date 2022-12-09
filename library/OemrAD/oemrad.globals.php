<?php

namespace OpenEMR\OemrAd;

define('OEMRAD_PATH_LIB', __DIR__ . DIRECTORY_SEPARATOR);
define('OEMRAD_LIB_NAMESPACE', 'OemrAd');
define('OEMRAD_WEB_LIB_PATH', $GLOBALS['webroot'] . '/library/OemrAD/');

$ext_events = array();
global $ext_events;

class Core {

	public $error = ""; // LAST ERROR MESSAGE
	public $errorr = "";

	function __construct(){
	}

	function getObject($classN = "", $args = array()) {
		if(!empty($classN)) {
			$className = OEMRAD_LIB_NAMESPACE.'\\'.$classN;
			return new $className($args);
		} 
	    
	    return false; 
	}

	//Get Direcotry
	function getDirectoryList($dir) {
	  $dirList = $fileList = [];
	  $iter = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);

	  foreach ($iter as $file) {
	      if ($file->isDir()) {
	          $info = new \SplFileInfo($file->getPathname() . "/md" . $file->getFilename() . ".php");
	          $fileList[$file->getFilename()] = $info->getPathname();
	      } else {
	          //print_r($module);
	      	  $fileNameNoExtension = preg_replace("/\.[^.]+$/", "", $file->getFilename());
	          $fileList[$fileNameNoExtension] = $file->getPathname();
	      }
	  }

	  return $fileList;
	}

	//Check Function Exists
	function checkMethodExists($hook, $classesList) {
	  $list = array();
	  foreach ($classesList as $dir => $classItem) {
	      $file = $classItem;
	      if (file_exists($file)) {
	        //require_once($file);

	        $newDir = str_replace_first('md', '', $dir); 
	        $methods = get_class_methods(OEMRAD_LIB_NAMESPACE.'\\'.$newDir);

	        foreach ($methods as $key => $method) {
	          if($method == $hook) {
	            $list[$dir][] = $method;
	          }
	        }
	      }
	  }

	  return $list;
	}

	//Do Action
	function do_action($hook, $value = NULL) {
	  global $ext_events;
	  if (isset($ext_events[$hook])) {
	      foreach($ext_events[$hook] as $function) {
	          if (function_exists($function)) { call_user_func($function, $value); }
	      }
	  }

	  $classesList = $this->getDirectoryList(OEMRAD_PATH_LIB.'hooks');

	  foreach ($classesList as $dir1 => $classItem) {
	      if (file_exists($classItem)) {
	      	require_once($classItem);
	      }
	  }

	  $methodList = $this->checkMethodExists($hook, $classesList);

	  foreach ($methodList as $module => $methods) {
	  	$className = OEMRAD_LIB_NAMESPACE.'\\'.str_replace_first('md', '', $module); 

	    foreach ($methods as $method) {
	    	$mOject = new $className();
			if($module == "add_action_call") {
				call_user_func(array($mOject, $method), NULL);
			} else {
				if(is_string($value)) {
				  $value = array($value);
				}

	    		call_user_func(array($mOject, $method), $value);
				//call_user_func_array(array($mOject, $method), null);
			}
	    }
	  }
	}

	//Add Action
	function add_action($hook, $func, $val = NULL) {
	  global $ext_events;
	  $ext_events[$hook][] = $func;
	}

	/*Run Table SQLQuery*/
	function tableRun($fpath = '') {
		if(isset($fpath) && !empty($fpath)) {
		  require_once(OEMRAD_PATH_LIB . "sql_upgrade_fx.php");
		  //$file = //OEMRAD_LIB_NAMESPACE . "sql/".$module_name."/table.sql";
		  if (file_exists($fpath)) {
		    ob_start();
		    upgradeFromSqlFile($fpath);
		    $reponce = ob_get_clean();
		    return $reponce;
		  }
		}
	}

	/*Run*/
	function runSetup() {
		$iter = new \FilesystemIterator(OEMRAD_PATH_LIB . "sql", \FilesystemIterator::SKIP_DOTS);
		$responceStr = '';

		foreach ($iter as $file) {
		    $info = new \SplFileInfo($file->getPathname());
		    $responceStr .= $this->tableRun($file->getPathname());
		}
		return $responceStr;
	}
}

//Core Object
$_OEMRAD = new Core(); 

function ClassLoader($class) {
	$parts = explode('\\', $class); // break into components

	if ($parts[0] != 'OpenEMR' || $parts[1] != 'OemrAd') return; // not a wmt class

	if (strpos(end($parts), 'Module') === false) { // loading a class
		$class_file = $GLOBALS['srcdir']."/OemrAD/classes/md". end($parts) .".class.php";
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
		$module_file = $GLOBALS['srcdir']."/OemrAD/modules/md". $file_name .".module.php";
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

if (!$splList || !isset($splList['OpenEMR\OemrAd\ClassLoader'])) {
	spl_autoload_register('OpenEMR\OemrAd\ClassLoader');
}

?>