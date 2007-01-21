<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class Provider Extends DataObjectBase {

	function Provider() {
		$this->_addFunc("socialsecuritynumber",	array(	"name"	=>	"FreeB.FBProvider.SocialSecurityNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("tin",					array(	"name"	=>	"FreeB.FBProvider.TIN",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ipn",					array(	"name"	=>	"FreeB.FBProvider.IPN",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("npi",					array(	"name"	=>	"FreeB.FBProvider.NPI",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("firstname",			array(	"name"	=>	"FreeB.FBProvider.FirstName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("middlename",			array(	"name"	=>	"FreeB.FBProvider.MiddleName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("lastname",				array(	"name"	=>	"FreeB.FBProvider.LastName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("streetaddress",		array(	"name"	=>	"FreeB.FBProvider.StreetAddress",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("city",					array(	"name"	=>	"FreeB.FBProvider.City",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("state",				array(	"name"	=>	"FreeB.FBProvider.State",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("zipcode",				array(	"name"	=>	"FreeB.FBProvider.Zipcode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonecountry",			array(	"name"	=>	"FreeB.FBProvider.PhoneCountry",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phoneextension",		array(	"name"	=>	"FreeB.FBProvider.PhoneExtension",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonearea",			array(	"name"	=>	"FreeB.FBProvider.PhoneArea",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonenumber",			array(	"name"	=>	"FreeB.FBProvider.PhoneNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));

	}

	function socialsecuritynumber($m) {
		// since this function is useless I will get the NPI number using this
		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['npi'];
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

	function tin($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['federaltaxid'];
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

	function ipn($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['upin'];
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

	function npi($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['npi'];
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


	function firstname($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['fname'];
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

	function middlename($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['federaltaxid'];
			}
		}


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

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['lname'];
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

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['facility'];
			}
		}
		
		$sql = "SELECT * FROM facility where name = '" . $retval ."'";
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

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['facility'];
			}
		}
		
		$sql = "SELECT * FROM facility where name = '" . $retval ."'";
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
			return new xmlrpcresp(new xmlrpcval($pkey));
		}
	}
	
	function state($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['facility'];
			}
		}
		
		$sql = "SELECT * FROM facility where name = '" . $retval ."'";
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

		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['facility'];
			}
		}
		
		$sql = "SELECT * FROM facility where name = '" . $retval ."'";
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

		//unimplmented by OpenEMR
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
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['facility'];
			}
		}
		
		$sql = "SELECT * FROM facility where name = '" . $retval ."'";
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

				$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM users where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['facility'];
			}
		}
		
		$sql = "SELECT * FROM facility where name = '" . $retval ."'";
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
		$retval = $phone_parts[2] . "-" . $phone_parts[3] ;

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

//'FreeB.FBProvider.SocialSecurityNumber' 	=> \&FreeB_FBProvider_SocialSecurityNumber,
//'FreeB.FBProvider.TIN' 			=> \&FreeB_FBProvider_TIN,
//'FreeB.FBProvider.IPN' 			=> \&FreeB_FBProvider_IPN,
//'FreeB.FBProvider.NPI' 			=> \&FreeB_FBProvider_NPI,
//'FreeB.FBProvider.FirstName' 			=> \&FreeB_FBProvider_FirstName,
//'FreeB.FBProvider.MiddleName' 		=> \&FreeB_FBProvider_MiddleName,
//'FreeB.FBProvider.LastName' 			=> \&FreeB_FBProvider_LastName,
//'FreeB.FBProvider.StreetAddress' 		=> \&FreeB_FBProvider_StreetAddress,
//'FreeB.FBProvider.City' 			=> \&FreeB_FBProvider_City,
//'FreeB.FBProvider.State' 			=> \&FreeB_FBProvider_State,
//'FreeB.FBProvider.Zipcode' 			=> \&FreeB_FBProvider_Zipcode,
//'FreeB.FBProvider.PhoneCountry' 		=> \&FreeB_FBProvider_PhoneCountry,
//'FreeB.FBProvider.PhoneExtension' 		=> \&FreeB_FBProvider_PhoneExtension,
//'FreeB.FBProvider.PhoneNumber' 		=> \&FreeB_FBProvider_PhoneNumber,
//'FreeB.FBProvider.PhoneArea' 			=> \&FreeB_FBProvider_PhoneArea,

?>
