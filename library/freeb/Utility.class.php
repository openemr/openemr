<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class Utility Extends DataObjectBase {

	function Utility() {
		$this->_addFunc("currentdate", 		array(	"name"	=>	"FreeB.FBGlobal.CurrentDate",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCSTRING),
															"doc"	=>	""));
	}


	function currentdate($m) {

		$err="";

		$pkey = $this->_isodate(date("Y-m-d"));

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($pkey,XMLRPCDATETIME));
		}
	}


//'FreeB.FBGlobal.CurrentDate' 			=> \&FreeB_FBGlobal_CurrentDate,


}


?>
