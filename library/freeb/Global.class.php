<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class ActionKeys Extends DataObjectBase {

	function ActionKeys() {
		$this->_addFunc("currentdate", 		array(	"name"	=>	"FreeB.FBGlobal.CurrentDate",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
	}


	function currentdate($m) {

		$err="";

		$procs = array(new xmlrpcval(144,"i4"),new xmlrpcval(233,"i4"));

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($procs,"array"));
		}
	}


//'FreeB.FBGlobal.CurrentDate' 			=> \&FreeB_FBGlobal_CurrentDate,


}


?>
