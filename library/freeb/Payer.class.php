<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");
require_once(dirname(__FILE__) . "/../classes/InsuranceCompany.class.php");

class Payer Extends DataObjectBase {

	function Payer() {
		$this->_addFunc("ismedicare",	array(	"name"	=>	"FreeB.FBPayer.isMedicare",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ismedicaid",	array(	"name"	=>	"FreeB.FBPayer.isMedicaid",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ischampusva",	array(	"name"	=>	"FreeB.FBPayer.isChampusva",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isbcbs",		array(	"name"	=>	"FreeB.FBPayer.isBcbs",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isfeca",		array(	"name"	=>	"FreeB.FBPayer.isFeca",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ischampus",	array(	"name"	=>	"FreeB.FBPayer.isChampus",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isotherhcfa",	array(	"name"	=>	"FreeB.FBPayer.isOtherHCFA",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("nationalplanid",	array(	"name"	=>	"FreeB.FBPayer.NationalPlanID",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("id",			array(	"name"	=>	"FreeB.FBPayer.ID",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("name",			array(	"name"	=>	"FreeB.FBPayer.Name",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("attn",			array(	"name"	=>	"FreeB.FBPayer.Attn",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("streetaddress",array(	"name"	=>	"FreeB.FBPayer.StreetAddress",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("city",			array(	"name"	=>	"FreeB.FBPayer.City",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("state",		array(	"name"	=>	"FreeB.FBPayer.State",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("zipcode",		array(	"name"	=>	"FreeB.FBPayer.Zipcode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonecountry",	array(	"name"	=>	"FreeB.FBPayer.PhoneCountry",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phoneextension",	array(	"name"	=>	"FreeB.FBPayer.PhoneExtension",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonearea",	array(	"name"	=>	"FreeB.FBPayer.PhoneArea",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("phonenumber",	array(	"name"	=>	"FreeB.FBPayer.PhoneNumber",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("procedurecodeset",	array(	"name"	=>	"FreeB.FBPayer.ProcedureCodeSet",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("diagnosiscodeset",	array(	"name"	=>	"FreeB.FBPayer.DiagnosisCodeSet",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ishcfacondensed",	array(	"name"	=>	"FreeB.FBPayer.isHCFACondensed",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("x12secondarymedicarecode",	array(	"name"	=>	"FreeB.FBPayer.X12SecondaryMedicareCode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("x12claimtype",	array(	"name"	=>	"FreeB.FBPayer.X12ClaimType",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCSTRING),
															"doc"	=>	""));
	}

	function ismedicare($m) {

		$err="";
		$retval = false;
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$type = $ic->get_freeb_type();
		
		//constants for types are defined in class InsuranceCompanies
		if ($type == FREEB_TYPE_MEDICARE) {
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

	function ismedicaid($m) {

		$err="";

		$retval = false;
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$type = $ic->get_freeb_type();
		
		//constants for types are defined in class InsuranceCompanies
		if ($type == FREEB_TYPE_MEDICAID) {
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

	function ischampusva($m) {

		$err="";

		$retval = false;
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$type = $ic->get_freeb_type();
		
		//constants for types are defined in class InsuranceCompanies
		if ($type == FREEB_TYPE_CHAMPUSVA) {
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

	function isbcbs($m) {

		$err="";

		$retval = false;
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$type = $ic->get_freeb_type();
		
		//constants for types are defined in class InsuranceCompanies
		if ($type == FREEB_TYPE_BCBS) {
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

	function isfeca($m) {

		$err="";

		$retval = false;
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$type = $ic->get_freeb_type();
		
		//constants for types are defined in class InsuranceCompanies
		if ($type == FREEB_TYPE_FECA) {
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

	function ischampus($m) {

		$err="";

		$retval = false;
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$type = $ic->get_freeb_type();
		
		//constants for types are defined in class InsuranceCompanies
		if ($type == FREEB_TYPE_CHAMPUS) {
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

	function isotherhcfa($m) {

		$err="";

		$retval = false;
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$type = $ic->get_freeb_type();
		
		//constants for types are defined in class InsuranceCompanies
		if ($type == FREEB_TYPE_OTHER_HCFA) {
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

	function nationalplanid($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$retval = $ic->get_cms_id();
		
		//constants for types are defined in class InsuranceCompanies
		
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

	function id($m) {

		$err="";

		$retval ="";
		$obj= $m->getparam(0);
		$key = $obj->getval();
		
		$sql = "SELECT * FROM insurance_companies where id='" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);	
		
		if (!$results) {
			$err = $db->ErrorMsg();	
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['cms_id'];
			}
		}
		
		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval),"string");
		}
	}

	function name($m) {

		
		$err="";
		
		$pkey = "";
		
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->get_name();
		
		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($pkey,"string"));
		}
	}

	function attn($m) {

		$err="";
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->get_attn();

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($pkey,"string"));
		}
	}

	function streetaddress($m) {

		$err="";

		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->address->get_lines_display();
		

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

		
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->address->get_city();

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
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->address->get_state();

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

		
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->address->get_zip();

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

		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->phone->get_country_code();

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

		
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->phone->get_area_code();

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
	function phonenumber($m) {

		$err="";

		
		$obj= $m->getparam(0);
		$ikey = $obj->getval();
		$ic = new InsuranceCompany($ikey);
		$pkey = $ic->phone->get_number();

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

	function procedurecodeset($m) {

		$err="";

		//OpenEMR only supports CPT-4 at this time
		//HCPCS support is almost fully developed
		$pkey = "CPT-4";

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


	function diagnosiscodeset($m) {

		$err="";

		//OpenEMR only supports ICD9 diagnosis codes
		$pkey = "ICD-9";
		
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

	function ishcfacondensed($m) {

		$err="";

		//defaults to uncondensed
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


	function x12secondarymedicarecode($m) {

		$err="";

		//unsupported by OpenEMR
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

	function x12claimtype($m) {

		$err="";

		//type of payer determined by HIPAA, supposed to be superceded by PlanID at some point, I don't think yet 06/03/2004

		//MB is medicare
		//16 is HMO
		$pkey = "16";

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

//'FreeB.FBPayer.isMedicare' 			=> \&FreeB_FBPayer_isMedicare,
//'FreeB.FBPayer.isMedicaid' 			=> \&FreeB_FBPayer_isMedicaid,
//'FreeB.FBPayer.isChampusva' 			=> \&FreeB_FBPayer_isChampusva,
//'FreeB.FBPayer.isBcbs' 				=> \&FreeB_FBPayer_isBcbs,
//'FreeB.FBPayer.isFeca' 				=> \&FreeB_FBPayer_isFeca,
//'FreeB.FBPayer.isChampus' 			=> \&FreeB_FBPayer_isChampus,
//'FreeB.FBPayer.isOtherHCFA' 			=> \&FreeB_FBPayer_isOtherHCFA,
//'FreeB.FBPayer.NationalPlanID' 			=> \&FreeB_FBPayer_NationalPlanID,
//'FreeB.FBPayer.ID' 				=> \&FreeB_FBPayer_ID,
//'FreeB.FBPayer.Name' 				=> \&FreeB_FBPayer_Name,
//'FreeB.FBPayer.Attn' 				=> \&FreeB_FBPayer_Attn,
//'FreeB.FBPayer.StreetAddress' 			=> \&FreeB_FBPayer_Street,
//'FreeB.FBPayer.City' 				=> \&FreeB_FBPayer_City,
//'FreeB.FBPayer.State' 				=> \&FreeB_FBPayer_State,
//'FreeB.FBPayer.Zipcode' 			=> \&FreeB_FBPayer_Zipcode,
//'FreeB.FBPayer.PhoneCountry' 			=> \&FreeB_FBPayer_PhoneCountry,
//'FreeB.FBPayer.PhoneExtension' 			=> \&FreeB_FBPayer_PhoneExtension,
//'FreeB.FBPayer.PhoneArea' 			=> \&FreeB_FBPayer_PhoneArea,
//'FreeB.FBPayer.PhoneNumber' 			=> \&FreeB_FBPayer_PhoneNumber,
//'FreeB.FBPayer.ProcedureCodeSet'	 	=> \&FreeB_FBPayer_ProcedureCodeSet,
//'FreeB.FBPayer.DiagnosisCodeSet' 		=> \&FreeB_FBPayer_DiagnosisCodeSet,
//'FreeB.FBPayer.isHCFACondensed' 		=> \&FreeB_FBPayer_isHCFACondensed,
//'FreeB.FBPayer.X12SecondaryMedicareCode' 	=> \&FreeB_FBPayer_X12SecondaryMedicareCode,
//'FreeB.FBPayer.X12ClaimType'	 		=> \&FreeB_FBPayer_X12ClaimType,
?>
