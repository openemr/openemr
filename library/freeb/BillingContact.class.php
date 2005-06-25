<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class BillingContact Extends DataObjectBase {

	function BillingContact() {
		$this->_addFunc("firstname",			array(	"name"	=>	"FreeB.FBBillingContact.FirstName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("middlename",			array(	"name"	=>	"FreeB.FBBillingContact.MiddleName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("lastname",				array(	"name"	=>	"FreeB.FBBillingContact.LastName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("streetaddress",		array(	"name"	=>	"FreeB.FBBillingContact.StreetAddress",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("city",					array(	"name"	=>	"FreeB.FBBillingContact.City",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("state",				array(	"name"	=>	"FreeB.FBBillingContact.State",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("zipcode",				array(	"name"	=>	"FreeB.FBBillingContact.Zipcode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonecountry",			array(	"name"	=>	"FreeB.FBBillingContact.PhoneCountry",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phoneextension",		array(	"name"	=>	"FreeB.FBBillingContact.PhoneExtension",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonearea",			array(	"name"	=>	"FreeB.FBBillingContact.PhoneArea",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonenumber",			array(	"name"	=>	"FreeB.FBBillingContact.PhoneNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
	}



	function firstname($m) {

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
				$retval = 	$results->fields['attn'];
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

	function middlename($m) {

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

	function lastname($m) {

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

	function streetaddress($m) {

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


	function city($m) {

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
	
	function state($m) {

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
	
	function zipcode($m) {

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

}
//'FreeB.FBBillingContact.FirstName' 		=> \&FreeB_FBBillingContact_FirstName,
//'FreeB.FBBillingContact.MiddleName' 		=> \&FreeB_FBBillingContact_MiddleName,
//'FreeB.FBBillingContact.LastName' 		=> \&FreeB_FBBillingContact_LastName,
//'FreeB.FBBillingContact.StreetAddress' 		=> \&FreeB_FBBillingContact_StreetAddress,
//'FreeB.FBBillingContact.City' 			=> \&FreeB_FBBillingContact_City,
//'FreeB.FBBillingContact.State' 			=> \&FreeB_FBBillingContact_State,
//'FreeB.FBBillingContact.Zipcode' 		=> \&FreeB_FBBillingContact_Zipcode,
//'FreeB.FBBillingContact.PhoneCountry' 		=> \&FreeB_FBBillingContact_PhoneCountry,
//'FreeB.FBBillingContact.PhoneExtension' 	=> \&FreeB_FBBillingContact_PhoneExtension,
//'FreeB.FBBillingContact.PhoneArea' 		=> \&FreeB_FBBillingContact_PhoneArea,
//'FreeB.FBBillingContact.PhoneNumber' 		=> \&FreeB_FBBillingContact_PhoneNumber,






?>
