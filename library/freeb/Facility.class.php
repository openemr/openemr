<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class Facility Extends DataObjectBase {

	function Facility() {
		$this->_addFunc("name",					array(	"name"	=>	"FreeB.FBFacility.Name",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("streetaddress",		array(	"name"	=>	"FreeB.FBFacility.StreetAddress",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("city",					array(	"name"	=>	"FreeB.FBFacility.City",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("state",				array(	"name"	=>	"FreeB.FBFacility.State",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("zipcode",				array(	"name"	=>	"FreeB.FBFacility.Zipcode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonecountry",			array(	"name"	=>	"FreeB.FBFacility.PhoneCountry",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phoneextension",		array(	"name"	=>	"FreeB.FBFacility.PhoneExtension",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonearea",			array(	"name"	=>	"FreeB.FBFacility.PhoneArea",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonenumber",			array(	"name"	=>	"FreeB.FBFacility.PhoneNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("x12code",				array(	"name"	=>	"FreeB.FBFacility.X12Code",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("hcfacode",				array(	"name"	=>	"FreeB.FBFacility.HCFACode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cliacode",				array(	"name"	=>	"FreeB.FBFacility.CLIACode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("npi",					array(	"name"	=>	"FreeB.FBFacility.NPI",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
	}


	function name($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['name'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}

	function streetaddress($m) {

		$err="";
		
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['street'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}


	function city($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['city'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}
	function state($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['state'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}
	function zipcode($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['postal_code'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}
	function phonecountry($m) {

		$err="";

		//OpenEMR only supports a sinlge coutry code for phones
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

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['phone'];
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
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}
	
	function phonenumber($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['phone'];
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
			return new xmlrpcresp(new xmlrpcval($pkey));
		}
	}
	
	function x12code($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['pos_code'];
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
	
	function hcfacode($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['pos_code'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"i4"));
		}
	}

	function cliacode($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT domain_identifier FROM facility where id = '" . $key ."'";
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['domain_identifier'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
		else {
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}
	function npi($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM facility where id = '" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval =	$results->fields['facility_npi'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}


}

//'FreeB.FBFacility.Name' 			=> \&FreeB_FBFacility_Name,
//'FreeB.FBFacility.StreetAddress' 		=> \&FreeB_FBFacility_StreetAddress,
//'FreeB.FBFacility.City' 			=> \&FreeB_FBFacility_City,
//'FreeB.FBFacility.State' 			=> \&FreeB_FBFacility_State,
//'FreeB.FBFacility.Zipcode' 			=> \&FreeB_FBFacility_Zipcode,
//'FreeB.FBFacility.PhoneNumber' 			=> \&FreeB_FBFacility_PhoneNumber,
//'FreeB.FBFacility.PhoneCountry' 		=> \&FreeB_FBFacility_PhoneCountry,
//'FreeB.FBFacility.PhoneExtension' 		=> \&FreeB_FBFacility_PhoneExtension,
//'FreeB.FBFacility.PhoneArea' 			=> \&FreeB_FBFacility_PhoneArea,
//'FreeB.FBFacility.X12Code' 			=> \&FreeB_FBFacility_X12Code,
//'FreeB.FBFacility.HCFACode' 			=> \&FreeB_FBFacility_HCFACode,
//'FreeB.FBFacility.NPI                         => \&FreeB_FBFacility_NPI


?>
