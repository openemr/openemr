<?php
require (dirname(__FILE__) . "/../sql.inc");

class DataObjectBase {

	var $xmlrpcerruser;
	var $_func_map;

	function DataObjectBase($xuser) {
		$this->xmlrpcerruser = $xuser;
		$this->_func_map=array();
	}

	function _handleError($err) {
		if ($err) {
			return new xmlrpcresp(0, $this->xmlrpcerruser, $err);
		}
	}

	function _addFunc($func,$mapping) {
		if (isset($this->_func_map[$func])) {
			$trace = debug_backtrace();
			trigger_error("Function <b>$func</b> already exists in function map in <b>" . $trace[0]['file'] . "</b> on line <b>" . $trace[0]['line'] . "</b>", E_USER_WARNING);
		}
		elseif (is_callable(array($this,$func))) {
			$this->_func_map[$func] = $mapping;
			return 0;
		}
		else {
   			$trace = debug_backtrace();
			trigger_error("Cannot add a function <b>$func</b> to function map which is not callable in <b>" . $trace[0]['file'] . "</b> on line <b>" . $trace[0]['line'] . "</b>", E_USER_ERROR);
		}

	}

	function _isodate ($date) {

		$format_iso = 'Ymd\TH:i:sO';
        $format_iso_utc = 'Ymd\TH:i:s';

		$dt = split("-",$date);

		$dt_stamp = $this->_ctime(0,0,0,$dt[1],$dt[2],$dt[0]);

		return gmdate($format_iso_utc, $dt_stamp);
	}

	function _ctime()	{

   $objArgs = func_get_args();
   $nCount = count($objArgs);
   if ($nCount < 7)
   {
       $objDate = getdate();
       if ($nCount < 1)
           $objArgs[] = $objDate["hours"];
       if ($nCount < 2)
           $objArgs[] = $objDate["minutes"];
       if ($nCount < 3)
           $objArgs[] = $objDate["seconds"];
       if ($nCount < 4)
           $objArgs[] = $objDate["mon"];
       if ($nCount < 5)
           $objArgs[] = $objDate["mday"];
       if ($nCount < 6)
           $objArgs[] = $objDate["year"];
       if ($nCount < 7)
           $objArgs[] = -1;
   }
   $nYear = $objArgs[5];
   $nOffset = 0;
   if ($nYear < 1970)
   {
       if ($nYear < 1902)
           return 0;
       else if ($nYear < 1952)
       {
           $nOffset = -2650838400;
           $objArgs[5] += 84;
           // Apparently dates before 1942 were never DST
           if ($nYear < 1942)
               $objArgs[6] = 0;
       }
       else
       {
           $nOffset = -883612800;
           $objArgs[5] += 28;
       }
   }

   return call_user_func_array("mktime", $objArgs) + $nOffset;
}

}


?>
