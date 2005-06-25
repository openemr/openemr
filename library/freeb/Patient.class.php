<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class Patient Extends DataObjectBase {

	function Patient() {

		$this->_addFunc("firstname",			array(	"name"	=>	"FreeB.FBPatient.FirstName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("middlename",			array(	"name"	=>	"FreeB.FBPatient.MiddleName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("lastname",				array(	"name"	=>	"FreeB.FBPatient.LastName",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("streetaddress",		array(	"name"	=>	"FreeB.FBPatient.StreetAddress",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("city",					array(	"name"	=>	"FreeB.FBPatient.City",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("state",				array(	"name"	=>	"FreeB.FBPatient.State",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("zipcode",				array(	"name"	=>	"FreeB.FBPatient.Zipcode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonecountry",			array(	"name"	=>	"FreeB.FBPatient.PhoneCountry",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phoneextension",		array(	"name"	=>	"FreeB.FBPatient.PhoneExtension",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonearea",			array(	"name"	=>	"FreeB.FBPatient.PhoneArea",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonenumber",			array(	"name"	=>	"FreeB.FBPatient.PhoneNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("title",				array(	"name"	=>	"FreeB.FBPatient.Title",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("account",				array(	"name"	=>	"FreeB.FBPatient.Account",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isdead",				array(	"name"	=>	"FreeB.FBPatient.isDead",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofbirth",			array(	"name"	=>	"FreeB.FBPatient.DateOfBirth",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofdeath",			array(	"name"	=>	"FreeB.FBPatient.DateOfDeath",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("sex",					array(	"name"	=>	"FreeB.FBPatient.Sex",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("issingle",				array(	"name"	=>	"FreeB.FBPatient.isSingle",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ismarried",			array(	"name"	=>	"FreeB.FBPatient.isMarried",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ismaritalotherhcfa",	array(	"name"	=>	"FreeB.FBPatient.isMaritalOtherHCFA",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isemployed",			array(	"name"	=>	"FreeB.FBPatient.isEmployed",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isfulltimestudent",	array(	"name"	=>	"FreeB.FBPatient.isFullTimeStudent",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isparttimestudent",	array(	"name"	=>	"FreeB.FBPatient.isPartTimeStudent",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ischildofinsured",		array(	"name"	=>	"FreeB.FBPatient.isChildOfInsured",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ishusbandofinsured",	array(	"name"	=>	"FreeB.FBPatient.isHusbandOfInsured",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("iswifeofinsured",		array(	"name"	=>	"FreeB.FBPatient.isWifeOfInsured",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isdivorceeofinsured",	array(	"name"	=>	"FreeB.FBPatient.isDivorceeOfInsured",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isselfofinsured",		array(	"name"	=>	"FreeB.FBPatient.isSelfOfInsured",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isotherofinsured",		array(	"name"	=>	"FreeB.FBPatient.isOtherOfInsured",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("socialsecuritynumber",	array(	"name"	=>	"FreeB.FBPatient.SocialSecurityNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("coveragecount",		array(	"name"	=>	"FreeB.FBPatient.CoverageCount",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("x12insuredrelationship",	array(	"name"	=>	"FreeB.FBPatient.X12InsuredRelationship",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ispregnant",			array(	"name"	=>	"FreeB.FBPatient.isPregnant",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
	}


	function firstname($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['fname'];
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
		
		$sql = "SELECT * FROM patient_data where pid = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['mname'];
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
		
		$sql = "SELECT * FROM patient_data where pid = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['lname'];
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
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['street'];
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
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['city'];
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
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['state'];
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
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['postal_code'];
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

		//OpenEMR only supports US country code
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

		//unimplemented by openemr
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
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['phone_home'];
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
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['phone_home'];
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
			return new xmlrpcresp(new xmlrpcval($retval,"string"));
		}
	}


	function title($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['title'];
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

	function account($m) {

		$err="";

		
		$retval = $_SESSION['patient_id'] . "-" . $_SESSION['billkey'];

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

	function isdead($m) {

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

	function dateofbirth($m) {

		$err="";
		
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['DOB'];
			}
		}
		

		$retval = $this->_isodate($retval);

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

	function dateofdeath($m) {

		$err="";
		
		//Unimplimented by OpenEMR
		$pkey = "";

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

	function sex($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['sex'];
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
	
		

	function issingle($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['status'];
			}
		}
		
		if ($retval == "single") {
			$retval = true;	
		}
		else {
			$retval = false;	
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

	function ismarried($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['status'];
			}
		}
		
		if ($retval == "married") {
			$retval = true;	
		}
		else {
			$retval = false;	
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

	function ismaritalotherhcfa($m) {

		$err="";

				$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['status'];
			}
		}
		
		if ($retval == "domestic partner") {
			$retval = true;	
		}
		else {
			$retval = false;	
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

	function isemployed($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['occupation'];
			}
		}
		
		if ($retval == "Unemployed" || empty($retval)) {
			$retval = false;	
		}
		else {
			$retval = true;	
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

	function isfulltimestudent($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['occupation'];
			}
		}
		
		if (strtolower($retval) == "student") {
			$retval = true;	
		}
		else {
			$retval = false;	
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

	function isparttimestudent($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['occupation'];
			}
		}
		
		if (strtolower($retval) == "pt student") {
			$retval = true;	
		}
		else {
			$retval = false;	
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
	
	function ischildofinsured($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		$obj= $m->getparam(1);
		$key2 = $obj->getval();
		$sql = "SELECT * FROM insurance_data where pid = '" . $key . "' AND id = '" . $key2 . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_relationship'];
			}
		}
		
		if (strtolower($retval) == "child") {
			$retval = true;	
		}
		else {
			$retval = false;	
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
	
	function ishusbandofinsured($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		$obj= $m->getparam(1);
		$key2 = $obj->getval();
		$sql = "SELECT * FROM insurance_data where pid = '" . $key . "' AND id = '" . $key2 . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_relationship'];
			}
		}
		
		if (strtolower($retval) == "spouse") {
			$retval = true;	
		}
		else {
			$retval = false;	
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
	
	function iswifeofinsured($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		$obj= $m->getparam(1);
		$key2 = $obj->getval();
		$sql = "SELECT * FROM insurance_data where pid = '" . $key . "' AND id = '" . $key2 . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_relationship'];
			}
		}
		
		if (strtolower($retval) == "spouse" && $results->fields['sex'] == "male") {
			$retval = true;	
		}
		else {
			$retval = false;	
		}

		$retval = false;
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
	
	function isdivorceeofinsured($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		$obj= $m->getparam(1);
		$key2 = $obj->getval();
		$sql = "SELECT * FROM insurance_data where pid = '" . $key . "' AND id = '" . $key2 . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_relationship'];
			}
		}
		
		if (strtolower($retval) == "divorcee") {
			$retval = true;	
		}
		else {
			$retval = false;	
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
	
	function isselfofinsured($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		$obj= $m->getparam(1);
		$key2 = $obj->getval();
		$sql = "SELECT * FROM insurance_data where pid = '" . $key . "' AND id = '" . $key2 . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_relationship'];
			}
		}
		
		if (strtolower($retval) == "self") {
			$retval = true;	
		}
		else {
			$retval = false;	
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
	
	function isotherofinsured($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		$obj= $m->getparam(1);
		$key2 = $obj->getval();
		$sql = "SELECT * FROM insurance_data where pid = '" . $key . "' AND id = '" . $key2 . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_relationship'];
			}
		}
		
		if (strtolower($retval) == "other") {
			$retval = true;	
		}
		else {
			$retval = false;	
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

	function socialsecuritynumber($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM patient_data where pid = '" . $_SESSION['patient_id'] . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['ss'];
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


	function coveragecount($m) {

		$err="";
		
		//unimplemented in OpenEMR
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

	function x12insuredrelationship($m) {

		$err="";
		$obj= $m->getparam(0);
		$patientkey = $obj->getval();

		$obj= $m->getparam(1);
		$insuredkey = $obj->getval();
		
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		$obj= $m->getparam(1);
		$key2 = $obj->getval();
		$sql = "SELECT * FROM insurance_data where pid = '" . $key . "' AND id = '" . $key2 . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['subscriber_relationship'];
			}
		}
		
		if (strtolower($retval) == "self") {
			$retval = "18";	
		}
		elseif (strtolower($retval) == "spouse") {
			$retval = "01";	
		}
		elseif (strtolower($retval) == "child") {
			$retval = "19";	
		}
		elseif (strtolower($retval) == "other") {
			$retval = "G8";	
		}
		
		/**
		*  For Reference these values are currently in use, we only support a subset
		*	01 Spouse
		*	04 Grandfather or Grandmother
		*	05 Grandson or Granddaughter
		*	07 Nephew or Niece
		*	09 Adopted Child
		*	10 Foster Child
		*	15 Ward
		*	17 Stepson or Stepdaughter
		*	19 Child
		*	20 Employee
		*	21 Unknown
		*	22 Handicapped Dependent
		*	23 Sponsored Dependent
		*	24 Dependent of a Minor Dependent
		*	29 Significant Other
		*	32 Mother
		*	33 Father
		*	34 Other Adult
		*	36 Emancipated Minor
		*	39 Organ Donor
		*	40 Cadaver Donor
		*	41 Injured Plaintiff
		*	43 Child Where Insured Has No Financial Responsibility
		*	53 Life Partner
		*	G8 Other Relationship
		*/
		

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

	function x12secondarymedicarecode($m) {

		$err="";

		//unimplemented in OpenEMR
		$pkey = "";
	
		/**
		*	For future reference values can be:
		*12 Medicare Secondary Working Aged Beneficiary or Spouse with Employer Group Health Plan
		*13 Medicare Secondary End-Stage Renal Disease Beneficiary in the 12 month coordination period with an employer's group health plan
		*14 Medicare Secondary, No-fault Insurance including Auto is Primary
		*15 Medicare Secondary Worker's Compensation
		*16 Medicare Secondary Public Health Service (PHS)or Other Federal Agency
		*41 Medicare Secondary Black Lung
		*42 Medicare Secondary Veteran's Administration
		*43 Medicare Secondary Disabled Beneficiary Under Age 65 with Large Group Health Plan (LGHP)
		*47 Medicare Secondary, Other Liability Insurance is Primary 
		*/
		
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

	function ispregnant($m) {

		$err="";

		//unimplemented in OpenEMR
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






}



//'FreeB.FBPatient.FirstName' 			=> \&FreeB_FBPatient_FirstName,
//'FreeB.FBPatient.MiddleName' 			=> \&FreeB_FBPatient_MiddleName,
//'FreeB.FBPatient.LastName' 			=> \&FreeB_FBPatient_LastName,
//'FreeB.FBPatient.StreetAddress' 		=> \&FreeB_FBPatient_StreetAddres,
//'FreeB.FBPatient.City' 				=> \&FreeB_FBPatient_City,
//'FreeB.FBPatient.Zipcode' 			=> \&FreeB_FBPatient_Zipcode,
//'FreeB.FBPatient.State' 			=> \&FreeB_FBPatient_State,
//'FreeB.FBPatient.PhoneCountry'			=> \&FreeB_FBPatient_PhoneCountry,
//'FreeB.FBPatient.PhoneExtension'		=> \&FreeB_FBPatient_PhoneExtension,
//'FreeB.FBPatient.PhoneArea'  			=> \&FreeB_FBPatient_PhoneArea,
//'FreeB.FBPatient.PhoneNumber' 			=> \&FreeB_FBPatient_PhoneNumber,
//'FreeB.FBPatient.Title' 			=> \&FreeB_FBPatient_Title,
//'FreeB.FBPatient.Account' 			=> \&FreeB_FBPatient_Account,
//'FreeB.FBPatient.isDead' 			=> \&FreeB_FBPatient_isDead,
//'FreeB.FBPatient.DateOfBirth' 			=> \&FreeB_FBPatient_DateOfBirth,
//'FreeB.FBPatient.DateOfDeath' 			=> \&FreeB_FBPatient_DateOfDeath,
//'FreeB.FBPatient.Sex' 				=> \&FreeB_FBPatient_Sex,
//'FreeB.FBPatient.isSingle' 			=> \&FreeB_FBPatient_isSingle,
//'FreeB.FBPatient.isMarried' 			=> \&FreeB_FBPatient_isMarried,
//'FreeB.FBPatient.isMaritalOtherHCFA'		=> \&FreeB_FBPatient_isMaritalOtherHCFA,
//'FreeB.FBPatient.isEmployed' 			=> \&FreeB_FBPatient_isEmployed,
//'FreeB.FBPatient.isFullTimeStudent' 		=> \&FreeB_FBPatient_isFullTimeStudent,
//'FreeB.FBPatient.isPartTimeStudent' 		=> \&FreeB_FBPatient_isPartTimeStudent,
//'FreeB.FBPatient.isChildOfInsured' 		=> \&FreeB_FBPatient_isChildOfInsured,
//'FreeB.FBPatient.isHusbandOfInsured' 		=> \&FreeB_FBPatient_isHusbandOfInsured,
//'FreeB.FBPatient.isWifeOfInsured' 		=> \&FreeB_FBPatient_isWifeOfInsured,
//'FreeB.FBPatient.isDivorceeOfInsured' 		=> \&FreeB_FBPatient_isDivorceeOfInsured,
//'FreeB.FBPatient.isSelfOfInsured' 		=> \&FreeB_FBPatient_isSelfOfInsured,
//'FreeB.FBPatient.isOtherOfInsured' 		=> \&FreeB_FBPatient_isOtherOfInsured,
//'FreeB.FBPatient.ReferringProviderKey'	 	=> \&FreeB_FBPatient_ReferringProviderKey,
//'FreeB.FBPatient.SocialSecurityNumber' 		=> \&FreeB_FBPatient_SocialSecurityNumber,
//'FreeB.FBPatient.CoverageCount' 		=> \&FreeB_FBPatient_CoverageCount,
//'FreeB.FBPatient.X12InsuredRelationship' 	=> \&FreeB_FBPatient_X12InsuredRelationship,
//'FreeB.FBPatient.isPregnant'		 	=> \&FreeB_FBPatient_isPregnant,

?>
