<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class Insured Extends DataObjectBase {

	function Insured() {
		$this->_addFunc("firstname",			array(	"name"	=>	"FreeB.FBInsured.FirstName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("middlename",			array(	"name"	=>	"FreeB.FBInsured.MiddleName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("lastname",				array(	"name"	=>	"FreeB.FBInsured.LastName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("id",					array(	"name"	=>	"FreeB.FBInsured.ID",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofbirth",			array(	"name"	=>	"FreeB.FBInsured.DateOfBirth",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("sex",					array(	"name"	=>	"FreeB.FBInsured.Sex",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("groupname",			array(	"name"	=>	"FreeB.FBInsured.GroupName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("groupnumber",			array(	"name"	=>	"FreeB.FBInsured.GroupNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isemployed",			array(	"name"	=>	"FreeB.FBInsured.isEmployed",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("employername",			array(	"name"	=>	"FreeB.FBInsured.EmployerName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isstudent",			array(	"name"	=>	"FreeB.FBInsured.isStudent",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("schoolname",			array(	"name"	=>	"FreeB.FBInsured.SchoolName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isassigning",			array(	"name"	=>	"FreeB.FBInsured.isAssigning",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("planname",				array(	"name"	=>	"FreeB.FBInsured.PlanName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("streetaddress",		array(	"name"	=>	"FreeB.FBInsured.StreetAddress",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("city",					array(	"name"	=>	"FreeB.FBInsured.City",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("state",				array(	"name"	=>	"FreeB.FBInsured.State",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("zipcode",				array(	"name"	=>	"FreeB.FBInsured.Zipcode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonecountry",			array(	"name"	=>	"FreeB.FBInsured.PhoneCountry",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phoneextension",		array(	"name"	=>	"FreeB.FBInsured.PhoneExtension",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonearea",			array(	"name"	=>	"FreeB.FBInsured.PhoneArea",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonenumber",			array(	"name"	=>	"FreeB.FBInsured.PhoneNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
	}

	function firstname($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_fname'];
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

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_mname'];
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

	function lastname($m) {

		$err="";
	
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_lname'];
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

	function id($m) {

		$err="";
		
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['policy_number'];
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
	
	function dateofbirth($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_DOB'];
			}
		}
		if (!empty($retval)) {
			$retval = $this->_isodate($retval);
		}
		else {
			$retval = "";
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,XMLRPCDATETIME));
		}
	}

	function sex($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_sex'];
			}
		}
		if (strtolower($retval) == "male") {
			$retval = "M";	
		}
		elseif (strtolower($retval) == "female") {
			$retval = "F";	
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
	
		

	function groupname($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_employer'];
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

	function groupnumber($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['group_number'];
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
	function isemployed($m) {

		$err="";
		
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		//default to true
		$pkey = true;

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($pkey,"i4"));
		}
	}
	
	function employername($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_employer'];
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

	function isstudent($m) {

		$err="";
		//unimplemented by OpenEMR
		$pkey = false;

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($pkey,"i4"));
		}
	}
	
	function schoolname($m) {

		$err="";

		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_employer'];
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

	function isassigning($m) {

		$err="";

		//defaulted to true
		$pkey = true;

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($pkey,"i4"));
		}
	}

	function planname($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['plan_name'];
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

		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_street'];
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

		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_city'];
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

		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_state'];
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

		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_postal_code'];
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
		//Unimplemented by OpenEMR
		$retval = "";
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
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_phone'];
			}
		}
		$phone_parts = array();
		preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$retval,$phone_parts);
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
		
		$sql = "SELECT * FROM insurance_data where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_phone'];
			}
		}
		$phone_parts = array();
		preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$retval,$phone_parts);
		$retval = $phone_parts[2] . "-" . $phone_parts[3];
		
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

//'FreeB.FBInsured.FirstName' 			=> \&FreeB_FBInsured_FirstName,
//'FreeB.FBInsured.LastName' 			=> \&FreeB_FBInsured_LastName,
//'FreeB.FBInsured.MiddleName' 			=> \&FreeB_FBInsured_MiddleName,
//'FreeB.FBInsured.ID' 				=> \&FreeB_FBInsured_ID,

//'FreeB.FBInsured.DateOfBirth' 			=> \&FreeB_FBInsured_DateOfBirth,
//'FreeB.FBInsured.Sex' 				=> \&FreeB_FBInsured_Sex,
//'FreeB.FBInsured.GroupName' 			=> \&FreeB_FBInsured_GroupName,
//'FreeB.FBInsured.GroupNumber' 			=> \&FreeB_FBInsured_GroupNumber,
//'FreeB.FBInsured.isEmployed' 			=> \&FreeB_FBInsured_isEmployed,
//'FreeB.FBInsured.EmployerName' 			=> \&FreeB_FBInsured_EmployerName,
//'FreeB.FBInsured.isStudent' 			=> \&FreeB_FBInsured_isStudent,
//'FreeB.FBInsured.SchoolName' 			=> \&FreeB_FBInsured_SchoolName,
//'FreeB.FBInsured.isAssigning' 			=> \&FreeB_FBInsured_isAssigning,
//'FreeB.FBInsured.PlanName' 			=> \&FreeB_FBInsured_PlanName,

//'FreeB.FBInsured.StreetAddress' 		=> \&FreeB_FBInsured_StreetAddress,
//'FreeB.FBInsured.City' 				=> \&FreeB_FBInsured_City,
//'FreeB.FBInsured.State' 			=> \&FreeB_FBInsured_State,
//'FreeB.FBInsured.Zipcode' 			=> \&FreeB_FBInsured_Zipcode,
//'FreeB.FBInsured.PhoneCountry' 			=> \&FreeB_FBInsured_PhoneCountry,
//'FreeB.FBInsured.PhoneExtension' 		=> \&FreeB_FBInsured_PhoneExtension,
//'FreeB.FBInsured.PhoneArea' 			=> \&FreeB_FBInsured_PhoneArea,
//'FreeB.FBInsured.PhoneNumber' 			=> \&FreeB_FBInsured_PhoneNumber,


?>
