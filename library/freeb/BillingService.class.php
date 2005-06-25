<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class BillingService Extends DataObjectBase {

	function BillingService() {
		$this->_addFunc("name",					array(	"name"	=>	"FreeB.FBBillingService.Name",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("streetaddress",		array(	"name"	=>	"FreeB.FBBillingService.StreetAddress",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("city",					array(	"name"	=>	"FreeB.FBBillingService.City",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("state",				array(	"name"	=>	"FreeB.FBBillingService.State",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("zipcode",				array(	"name"	=>	"FreeB.FBBillingService.Zipcode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonecountry",			array(	"name"	=>	"FreeB.FBBillingService.PhoneCountry",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phoneextension",		array(	"name"	=>	"FreeB.FBBillingService.PhoneExtension",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonearea",			array(	"name"	=>	"FreeB.FBBillingService.PhoneArea",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonenumber",			array(	"name"	=>	"FreeB.FBBillingService.PhoneNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("etin",					array(	"name"	=>	"FreeB.FBBillingService.ETIN",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("tin",					array(	"name"	=>	"FreeB.FBBillingService.TIN",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
	}


	function name($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM facility where billing_location = '1'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = 	$results->fields['name'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}

	function streetaddress($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM facility where billing_location = '1'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = 	$results->fields['street'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}


	function city($m) {

		$err="";


		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM facility where billing_location = '1'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = 	$results->fields['city'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}
	
	function state($m) {

		$err="";


		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM facility where billing_location = '1'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = 	$results->fields['state'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}
	
	function zipcode($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM facility where billing_location = '1'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = 	$results->fields['postal_code'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}
	
	function phonecountry($m) {

		$err="";

		//unimplemented by OpenEMR
		$pkey = "1";

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($pkey));
		}
	}

	function phoneextension($m) {

		$err="";

		//unimplemented by OpenEMR
		$pkey = "";

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($pkey));
		}
	}
	
	function phonearea($m) {

		$err="";

		$sql = "SELECT * FROM facility where billing_location = '1'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = 	$results->fields['phone'];
			}
		}

		$phone_parts = array();
//	preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$retval,$phone_parts);
		preg_match("/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",$retval,$phone_parts);
		$retval = $phone_parts[1];

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}
	
	function phonenumber($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM facility where billing_location = '1'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = 	$results->fields['phone'];
			}
		}

		$phone_parts = array();
//	preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$retval,$phone_parts);
		preg_match("/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",$retval,$phone_parts);
		$retval = $phone_parts[2] . "-" . $phone_parts[3];

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}
	
	function etin($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM facility where billing_location = 1";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		$vals = array();
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['federal_ein'];
				
			}
		}		

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}
	
	function tin($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM facility where billing_location = 1";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		$vals = array();
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['federal_ein'];
				
			}
		}		
		
		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval));
		}
	}

}

//'FreeB.FBBillingService.Name' 			=> \&FreeB_FBBillingService_Name,
//'FreeB.FBBillingService.StreetAddress' 		=> \&FreeB_FBBillingService_StreetAddress,
//'FreeB.FBBillingService.City' 			=> \&FreeB_FBBillingService_City,
//'FreeB.FBBillingService.State' 			=> \&FreeB_FBBillingService_State,
//'FreeB.FBBillingService.Zipcode' 		=> \&FreeB_FBBillingService_Zipcode,
//'FreeB.FBBillingService.PhoneCountry' 		=> \&FreeB_FBBillingService_PhoneCountry,
//'FreeB.FBBillingService.PhoneExtension' 	=> \&FreeB_FBBillingService_PhoneExtension,
//'FreeB.FBBillingService.PhoneArea' 		=> \&FreeB_FBBillingService_PhoneArea,
//'FreeB.FBBillingService.PhoneNumber' 		=> \&FreeB_FBBillingService_PhoneNumber,
//'FreeB.FBBillingService.ETIN' 			=> \&FreeB_FBBillingService_ETIN,
//'FreeB.FBBillingService.TIN' 			=> \&FreeB_FBBillingService_TIN,






?>
