<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class Diagnosis Extends DataObjectBase {

	function Diagnosis() {
		$this->_addFunc("relatedtohcfa",				array(	"name"	=>	"FreeB.FBDiagnosis.RelatedToHCFA",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isrelatedtootheraccident",		array(	"name"	=>	"FreeB.FBDiagnosis.isRelatedToOtherAccident",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofonset",					array(	"name"	=>	"FreeB.FBDiagnosis.DateOfOnset",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateoffirstsymptom",			array(	"name"	=>	"FreeB.FBDiagnosis.DateOfFirstSymptom",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isfirstoccurence",				array(	"name"	=>	"FreeB.FBDiagnosis.isFirstOccurrence",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateoffirstoccurence",			array(	"name"	=>	"FreeB.FBDiagnosis.DateOfFirstOccurrence",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("iscantwork",					array(	"name"	=>	"FreeB.FBDiagnosis.isCantWork",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofcantworkstart",			array(	"name"	=>	"FreeB.FBDiagnosis.DateOfCantWorkStart",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofcantworkend",			array(	"name"	=>	"FreeB.FBDiagnosis.DateOfCantWorkEnd",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isrelatedtoemployment",		array(	"name"	=>	"FreeB.FBDiagnosis.isRelatedToEmployment",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isrelatedtoautoaccident",		array(	"name"	=>	"FreeB.FBDiagnosis.isRelatedToAutoAccident",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("autoaccidentstate",			array(	"name"	=>	"FreeB.FBDiagnosis.AutoAccidentState",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("icd10code",					array(	"name"	=>	"FreeB.FBDiagnosis.ICD10Code",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("icd9code",						array(	"name"	=>	"FreeB.FBDiagnosis.ICD9Code",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateoflastvisit",						array(	"name"	=>	"FreeB.FBDiagnosis.DateOfLastVisit",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("comment",						array(	"name"	=>	"FreeB.FBDiagnosis.Comment",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
	}

	function relatedtohcfa($m) {

		$err="";

		//unimplemented by OpenEMR
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

	function isrelatedtootheraccident($m) {

		$err="";
		//unimplemented by OpenEMR
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

	function dateofonset($m) {

		$err="";

		$retval ="";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM form_encounter where encounter = '" . $_SESSION['billkey'] ."' and pid = '" . $_SESSION['patient_id'] ."' order by date DESC";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['onset_date'];
			}
		}
		$retval = date("Y-m-d",strtotime($retval));
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

	function dateoffirstsymptom($m) {

		$err="";

		$retval ="";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM form_encounter where encounter = '" . $_SESSION['billkey'] ."' and pid = '" . $_SESSION['patient_id'] ."' order by date DESC";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['onset_date'];
			}
		}
		$retval = date("Y-m-d",strtotime($retval));
		$retval = $this->_isodate($retval);

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

	function isfirstoccurence($m) {

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

	function dateoffirstoccurence($m) {

		$err="";

		$retval ="";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM form_encounter where encounter = '" . $_SESSION['billkey'] ."' and pid = '" . $_SESSION['patient_id'] ."' order by date DESC";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['onset_date'];
			}
		}
		$retval = date("Y-m-d",strtotime($retval));
		$retval = $this->_isodate($retval);

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

	function iscantwork($m) {

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

	function dateofcantworkstart($m) {

		$err="";
		//unimplemented by OpenEMR
		$pkey = $this->_isodate("");

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

	function dateofcantworkend($m) {

		$err="";
		//unimplemented by OpenEMR
		$pkey = $this->_isodate("");

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

	function isrelatedtoemployment($m) {

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

	function isrelatedtoautoaccident($m) {

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
			return new xmlrpcresp(new xmlrpcval($pkey,"i4"));
		}
	}

	function autoaccidentstate($m) {

		$err="";

		//unimplmeneted by OpenEMR
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
	function icd10code($m) {

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
	function icd9code($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM billing where id = '" . $key . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = 	$results->fields['code'];
			}
		}
		$retval = str_replace("."," ", $retval);

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
	function dateoflastvisit($m){
		$err="";

	        $retval ="";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM form_encounter where encounter = '" . $_SESSION['billkey'] ."' and pid = '" . $_SESSION['patient_id'] ."' order by date DESC";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['date'];
			}
		}
		$retval = date("Y-m-d",strtotime($retval));
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
	function comment($m){
		$err="";

	    $retval ="";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT reason FROM form_encounter where encounter = '" . $_SESSION['billkey'] ."' and pid = '" . $_SESSION['patient_id'] ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['reason'];
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
//'FreeB.FBDiagnosis.RelatedToHCFA' 		=> \&FreeB_FBDiagnosis_RelatedToHCFA,
//'FreeB.FBDiagnosis.isRelatedToOtherAccident' 	=> \&FreeB_FBDiagnosis_isRelatedToOtherAccident,
//'FreeB.FBDiagnosis.DateOfOnset' 		=> \&FreeB_FBDiagnosis_DateOfOnset,
//'FreeB.FBDiagnosis.DateOfFirstSymptom' 		=> \&FreeB_FBDiagnosis_DateOfFirstSymptom,
//'FreeB.FBDiagnosis.isFirstOccurrence' 		=> \&FreeB_FBDiagnosis_isFirstOccurence,
//'FreeB.FBDiagnosis.DateOfFirstOccurrence' 	=> \&FreeB_FBDiagnosis_DateOfFirstOccurence,
//'FreeB.FBDiagnosis.isCantWork' 			=> \&FreeB_FBDiagnosis_isCantWork,
//'FreeB.FBDiagnosis.DateOfCantWorkStart' 	=> \&FreeB_FBDiagnosis_DateOfCantWorkStart,
//'FreeB.FBDiagnosis.DateOfCantWorkEnd' 		=> \&FreeB_FBDiagnosis_DateOfCantWorkEnd,
//'FreeB.FBDiagnosis.isRelatedToEmployment'	=> \&FreeB_FBDiagnosis_isRelatedToEmployment,
//'FreeB.FBDiagnosis.isRelatedToAutoAccident'	=> \&FreeB_FBDiagnosis_isRelatedToAutoAccident,
//'FreeB.FBDiagnosis.AutoAccidentState' 		=> \&FreeB_FBDiagnosis_AutoAccidentState,
//'FreeB.FBDiagnosis.ICD10Code' 			=> \&FreeB_FBDiagnosis_ICD9Code,
//'FreeB.FBDiagnosis.ICD9Code' 			=> \&FreeB_FBDiagnosis_ICD9Code,?>
