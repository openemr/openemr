<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class ClearingHouse Extends DataObjectBase {

	function ClearingHouse() {
		$this->_addFunc("name",					array(	"name"	=>	"FreeB.FBClearingHouse.Name",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("streetaddress",		array(	"name"	=>	"FreeB.FBClearingHouse.StreetAddress",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("city",					array(	"name"	=>	"FreeB.FBClearingHouse.City",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("state",				array(	"name"	=>	"FreeB.FBClearingHouse.State",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("zipcode",				array(	"name"	=>	"FreeB.FBClearingHouse.Zipcode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonecountry",			array(	"name"	=>	"FreeB.FBClearingHouse.PhoneCountry",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phoneextension",		array(	"name"	=>	"FreeB.FBClearingHouse.PhoneExtension",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonearea",			array(	"name"	=>	"FreeB.FBClearingHouse.PhoneArea",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonenumber",			array(	"name"	=>	"FreeB.FBClearingHouse.PhoneNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("etin",					array(	"name"	=>	"FreeB.FBClearingHouse.ETIN",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("x12gsreceiverid",		array(	"name"	=>	"FreeB.FBClearingHouse.X12GSReceiverID",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("x12gssenderid",		array(	"name"	=>	"FreeB.FBClearingHouse.X12GSSenderID",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("x12gsversionstring",	array( "name"		=>	"FreeB.FBClearingHouse.X12GSVersionString",
															"sig"	=>	array(XMLRPCSTRING, XMLRPCINT),
															"doc"	=>	""));
	}


	function name($m) {

		$err="";

		//val zero is deprecated and is the facility identifier
		//val two should be the procedure key, or put another way an id in the billing able
		//trim due to ugly perl string cast hack
		$obj= $m->getparam(1);
		$key = trim($obj->getval());
		
		$db = $GLOBALS['adodb']['db'];
		
		$sql = "SELECT x.name FROM billing as b LEFT JOIN x12_partners as x on x.id = b.x12_partner_id where b.id= " .$db->qstr($key) ;
		//echo $sql;
		
		$results = $db->Execute($sql);	
		
		$vals = array();
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['name'];
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
	function etin($m) {

		$err="";
		
		//val zero is deprecated and is the facility identifier
		//val two should be the procedure key, or put another way an id in the billing able
		//trim due to ugly perl string cast hack
		$obj= $m->getparam(1);
		$key = trim($obj->getval());
		
		$db = $GLOBALS['adodb']['db'];
		
		$sql = "SELECT x.id_number FROM billing as b LEFT JOIN x12_partners as x on x.id = b.x12_partner_id where b.id= " .$db->qstr($key) ;
		//echo $sql;
		
		$results = $db->Execute($sql);	
		
		$vals = array();
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['id_number'];
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
	function x12gsreceiverid($m) {

		$err="";
		
		//val zero is deprecated and is the facility identifier
		//val two should be the procedure key, or put another way an id in the billing able
		//trim due to ugly perl string cast hack
		$obj= $m->getparam(1);
		$key = trim($obj->getval());
		
		$db = $GLOBALS['adodb']['db'];
		
		$sql = "SELECT x.x12_receiver_id FROM billing as b LEFT JOIN x12_partners as x on x.id = b.x12_partner_id where b.id= " .$db->qstr($key) ;
		//echo $sql;
		
		$results = $db->Execute($sql);	
		
		$vals = array();
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['x12_receiver_id'];
			}
		}		
		
		while (strlen($retval) < 15) {
			$retval .= " ";	
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
	
	function x12gssenderid($m) {

		$err="";

		//val zero is deprecated and is the facility identifier
		//val two should be the procedure key, or put another way an id in the billing able
		//trim due to ugly perl string cast hack
		$obj= $m->getparam(1);
		$key = trim($obj->getval());
		
		$db = $GLOBALS['adodb']['db'];
		
		$sql = "SELECT x.x12_sender_id FROM billing as b LEFT JOIN x12_partners as x on x.id = b.x12_partner_id where b.id= " .$db->qstr($key) ;
		//echo $sql;
		
		$results = $db->Execute($sql);	
		
		$vals = array();
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['x12_sender_id'];
			}
		}		
		
		while (strlen($retval) < 15) {
			$retval .= " ";	
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


function x12gsversionstring($m) {

		$err="";

		//val zero is deprecated and is the facility identifier
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$db = $GLOBALS['adodb']['db'];
		
		$sql = "SELECT x.x12_version FROM billing AS b LEFT JOIN  x12_partners as x ON x.id = b.x12_partner_id WHERE b.id= ".$db->qstr($key); 
		//echo $sql;
	
		$results = $db->Execute($sql);	
		
		$vals = array();
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['x12_version'];
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
//'FreeB.FBClearingHouse.Name' 			=> \&FreeB_FBClearingHouse_Name,
//'FreeB.FBClearingHouse.StreetAddress' 		=> \&FreeB_FBClearingHouse_StreetAddress,
//'FreeB.FBClearingHouse.City' 			=> \&FreeB_FBClearingHouse_City,
//'FreeB.FBClearingHouse.State' 			=> \&FreeB_FBClearingHouse_State,
//'FreeB.FBClearingHouse.Zipcode' 		=> \&FreeB_FBClearingHouse_Zipcode,
//'FreeB.FBClearingHouse.PhoneCountry' 		=> \&FreeB_FBClearingHouse_PhoneCountry,
//'FreeB.FBClearingHouse.PhoneExtension' 		=> \&FreeB_FBClearingHouse_PhoneExtension,
//'FreeB.FBClearingHouse.PhoneArea' 		=> \&FreeB_FBClearingHouse_PhoneArea,
//'FreeB.FBClearingHouse.PhoneNumber' 		=> \&FreeB_FBClearingHouse_PhoneNumber,
//'FreeB.FBClearingHouse.ETIN' 			=> \&FreeB_FBClearingHouse_ETIN,
//'FreeB.FBClearingHouse.X12GSReceiverID' 	=> \&FreeB_FBClearingHouse_X12GSReceiverID,
//'FreeB.FBClearingHouse.X12GSSenderID'	 	=> \&FreeB_FBClearingHouse_X12GSSenderID,




?>
