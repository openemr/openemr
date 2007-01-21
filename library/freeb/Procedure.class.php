<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");
class Procedure Extends DataObjectBase {

	function Procedure($xuser) {
		parent::DataObjectBase($xuser);
		$this->_addFunc("isusingclearinghouse",		array(	"name"	=>	"FreeB.FBProcedure.isUsingClearingHouse",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cpt4code",					array(	"name"	=>	"FreeB.FBProcedure.CPT4Code",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cpt5code",					array(	"name"	=>	"FreeB.FBProcedure.CPT5Code",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cptunits",					array(	"name"	=>	"FreeB.FBProcedure.CPTUnits",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cptmodifier",				array(	"name"	=>	"FreeB.FBProcedure.CPTModifier",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cptcharges",				array(	"name"	=>	"FreeB.FBProcedure.CPTCharges",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cptemergency",				array(	"name"	=>	"FreeB.FBProcedure.CPTEmergency",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cptcob",					array(	"name"	=>	"FreeB.FBProcedure.CPTCOB",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("cptepsdt",					array(	"name"	=>	"FreeB.FBProcedure.CPTEPSDT",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("amountpaid",				array(	"name"	=>	"FreeB.FBProcedure.AmountPaid",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCARRAY),
															"doc"	=>	""));
		$this->_addFunc("typeofservice",			array(	"name"	=>	"FreeB.FBProcedure.TypeOfService",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("priorauth",				array(	"name"	=>	"FreeB.FBProcedure.PriorAuth",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofservicestart",		array(	"name"	=>	"FreeB.FBProcedure.DateOfServiceStart",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofserviceend",			array(	"name"	=>	"FreeB.FBProcedure.DateOfServiceEnd",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofhospitalstart",		array(	"name"	=>	"FreeB.FBProcedure.DateOfHospitalStart",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("ishospitalized",			array(	"name"	=>	"FreeB.FBProcedure.isHospitalized",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isoutsidelab",				array(	"name"	=>	"FreeB.FBProcedure.isOutsideLab",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("outsidelabcharges",		array(	"name"	=>	"FreeB.FBProcedure.OutsideLabCharges",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("dateofhospitalend",		array(	"name"	=>	"FreeB.FBProcedure.DateOfHospitalEnd",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("medicaidresubmissioncode",	array(	"name"	=>	"FreeB.FBProcedure.MedicaidResubmissionCode",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("medicaidoriginalreference",array(	"name"	=>	"FreeB.FBProcedure.MedicaidOriginalReference",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("weightgrams",				array(	"name"	=>	"FreeB.FBProcedure.WeightGrams",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT,XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("weightpounds",				array(	"name"	=>	"FreeB.FBProcedure.WeightPounds",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("hcfalocaluse10d",			array(	"name"	=>	"FreeB.FBProcedure.HCFALocalUse10d",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("hcfalocaluse19",	array(	"name"	=>	"FreeB.FBProcedure.HCFALocalUse19",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("procarray", 		array(	"name"	=>	"FreeB.FBProcedure.ProcArray",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("diagarray", 		array(	"name"	=>	"FreeB.FBProcedure.DiagArray",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("patientkey",		array(	"name"	=>	"FreeB.FBProcedure.PatientKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("practicekey",		array(	"name"	=>	"FreeB.FBProcedure.PracticeKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("providerkey",		array(	"name"	=>	"FreeB.FBProcedure.ProviderKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("referringproviderkey",	array(	"name"	=>	"FreeB.FBPatient.ReferringProviderKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("insuredkey",		array(	"name"	=>	"FreeB.FBProcedure.InsuredKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("otherinsuredkey",	array(	"name"	=>	"FreeB.FBProcedure.OtherInsuredKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("payerkey", 		array(	"name"	=>	"FreeB.FBProcedure.PayerKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("secondpayerkey", 	array(	"name"	=>	"FreeB.FBProcedure.SecondPayerKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("facilitykey", 		array(	"name"	=>	"FreeB.FBProcedure.FacilityKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("billingcontactkey",array(	"name"	=>	"FreeB.FBProcedure.BillingContactKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("billingservicekey",array(	"name"	=>	"FreeB.FBProcedure.BillingServiceKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("isusingbillingservice",array(	"name"	=>	"FreeB.FBProcedure.isUsingBillingService",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("clearinghousekey", array(	"name"	=>	"FreeB.FBProcedure.ClearingHouseKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));

	}

	function isusingclearinghouse($m) {

		$err="";

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

	function cpt4code($m) {

		$err="";
		$retval = "";
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
				$retval = $results->fields['code'];
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

	function cpt5code($m) {

		$err="";

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

	function cptunits($m) {

		$err="";
		$retval = "";
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
				$retval = $results->fields['units'];
			}
		}

		if (empty ($retval) || $retval < 1) {
			$retval = 1;
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


	function cptmodifier($m) {

		$err="";
		$retval = "";
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
				$retval = $results->fields['modifier'];
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


	function cptcharges($m) {

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
				$retval = $results->fields['fee'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"double"));
		}
	}

	function cptemergency($m) {

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

	function cptcob($m) {

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

	function cptepsdt($m) {

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

	function amountpaid($m) {

		$err="";

		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM billing where activity = '1' and encounter = '" . $_SESSION['billkey'] . "'  and pid = '" . $_SESSION['patient_id'] . "' and code_type = 'COPAY'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['fee'];
			}
		}

		if (empty($retval)) {
			$retval = "0000";
		}

		$retval = str_replace(".","",$retval);

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

	function typeofservice($m) {

		$err="";
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		//phased out by HIPPA, use cpt modifiers instead
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

	function priorauth($m) {

		$err="";
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM forms JOIN form_misc_billing_options as fpa on fpa.id = forms.form_id where forms.encounter = '" . $_SESSION['billkey'] . "' and forms.pid = '" . $_SESSION['patient_id'] . "' and forms.formdir = 'misc_billing_options' order by forms.date";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['prior_auth_number'];
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

	function dateofservicestart($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM billing where pid = '" . $_SESSION['patient_id'] . "' and  encounter = '" . $_SESSION['billkey'] . "'and id ='" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['date'];
			}
		}


		$retval = $this->_isodate(date("Y-m-d",strtotime($retval)));

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

	function dateofserviceend($m) {

		$err="";

		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM billing where pid = '" . $_SESSION['patient_id'] . "' and  encounter = '" . $_SESSION['billkey'] . "'and id ='" . $key ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval  =	$results->fields['date'];
			}
		}

		$retval = $this->_isodate(date("Y-m-d",strtotime($retval)));

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

	function dateofhospitalstart($m) {
		$err="";
		//Now implemented by OpenEMR with Form: Misc Billing Options
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM forms JOIN form_misc_billing_options as fpa on fpa.id = forms.form_id where forms.encounter = '" . $_SESSION['billkey'] . "' and forms.pid = '" . $_SESSION['patient_id'] . "' and forms.formdir = 'misc_billing_options' order by forms.date";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['hospitalization_date_from'];
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



	function ishospitalized($m) {
		$err="";
		//Now implemented by OpenEMR with Form: Misc Billing Options
		$pkey = false;
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM forms JOIN form_misc_billing_options as fpa on fpa.id = forms.form_id where forms.encounter = '" . $_SESSION['billkey'] . "' and forms.pid = '" . $_SESSION['patient_id'] . "' and forms.formdir = 'misc_billing_options' order by forms.date";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['is_hospitalized'];
				if ($retval == "1") {$pkey = true;};
					}
		}
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


	function isoutsidelab($m) {
		$err="";
		//Now implemented by OpenEMR with Form: Misc Billing Options
		$pkey = false;
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM forms JOIN form_misc_billing_options as fpa on fpa.id = forms.form_id where forms.encounter = '" . $_SESSION['billkey'] . "' and forms.pid = '" . $_SESSION['patient_id'] . "' and forms.formdir = 'misc_billing_options' order by forms.date";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['outside_lab'];
				if ($retval == "1") {$pkey = true;};
					}
		}
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

	function outsidelabcharges($m) {

		$err="";

		//Now implemented by OpenEMR with form_misc_billing_options
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM forms JOIN form_misc_billing_options as fpa on fpa.id = forms.form_id where forms.encounter = '" . $_SESSION['billkey'] . "' and forms.pid = '" . $_SESSION['patient_id'] . "' and forms.formdir = 'misc_billing_options' order by forms.date";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['lab_amount'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($retval,"double"));
		}
	}

	function dateofhospitalend($m) {
		$err="";
		//Now implemented by OpenEMR with Form: Misc Billing Options
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM forms JOIN form_misc_billing_options as fpa on fpa.id = forms.form_id where forms.encounter = '" . $_SESSION['billkey'] . "' and forms.pid = '" . $_SESSION['patient_id'] . "' and forms.formdir = 'misc_billing_options' order by forms.date";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['hospitalization_date_to'];
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



	function medicaidresubmissioncode($m) {

		$err="";
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM forms JOIN form_misc_billing_options as fpa on fpa.id = forms.form_id where forms.encounter = '" . $_SESSION['billkey'] . "' and forms.pid = '" . $_SESSION['patient_id'] . "' and forms.formdir = 'misc_billing_options' order by forms.date";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['medicaid_resubmission_code'];
				
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

	function medicaidoriginalreference($m) {

		$err="";
		$retval = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM forms JOIN form_misc_billing_options as fpa on fpa.id = forms.form_id where forms.encounter = '" . $_SESSION['billkey'] . "' and forms.pid = '" . $_SESSION['patient_id'] . "' and forms.formdir = 'misc_billing_options' order by forms.date";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$retval = $results->fields['medicaid_original_reference'];
				
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


	function weightgrams($m) {

		$err="";

		$pkey = "2300";

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

	function weightpounds($m) {

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

	function hcfalocaluse10d($m) {

		$err="";

		//this needs to be customized on a payer to payer and state to state basis
		$pkey = "";

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

	function hcfalocaluse19($m) {

		$err="";

		//this needs to be customized on a payer to payer and state to state basis
		$pkey = "";

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

	function procarray($m) {

		$err="";

		$procs = array();

		$obj= $m->getparam(0);
		$key = $obj->getval();

		$keys = split("-",$key);
		$patient_id = $keys[0];
		$encounter = $keys[1];

		$_SESSION['billkey'] = $encounter;
		$_SESSION['patient_id'] = $patient_id;

		// Sort by procedure timestamp in order to get some consistency.  In particular
		// freeb determines the provider from the first procedure in this array.
		$sql = "SELECT * FROM billing where (code_type = 'CPT4' or code_type = 'HCPCS') AND " .
			"encounter = '$encounter' AND pid = '$patient_id' and activity = '1' " .
			"order by date, id";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			while (!$results->EOF) {
				$procs[] = 	new xmlrpcval($results->fields['id'],"i4");
				$results->MoveNext();
			}
		}


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

	function diagarray($m) {

		$err="";
		$diags = array();

		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM billing where id = '$key'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$diagstring = $results->fields['justify'];
			}
		}

		if (!empty($diagstring)) {

			$diag_codes = split(":",$diagstring);
			$diag_sql = "";
			foreach ($diag_codes as $dc) {
				if (!empty($dc)) {
					$diag_sql .= "'$dc',";
				}
			}
			if (substr($diag_sql,strlen($diag_sql) -1) == ",") {
				$diag_sql = substr($diag_sql,0,strlen($diag_sql) -1);
			}

			$sql = "SELECT * FROM billing where code in ($diag_sql) and  code_type = 'ICD9' AND encounter = '" . $_SESSION['billkey'] . "' and pid ='" . $_SESSION['patient_id'] . "' and activity = '1'";

			$results = $db->Execute($sql);
			if (!$results) {
				$err .= $db->ErrorMsg();
			}
			else {
				while (!$results->EOF) {
					$diags[$results->fields['code']] = 	new xmlrpcval($results->fields['id'],"i4");
					$results->MoveNext();
				}
			}
			$tmp_diags = $diags;
			$tmp_keys = array_keys($tmp_diags);
			$diags = array();
			foreach ($diag_codes as $code) {
				if (in_array($code,$tmp_keys)) {
					$diags[] = $tmp_diags[$code];
				}
			}
		}
		else {
			$diags[] = 	new xmlrpcval(0,"i4");
		}



		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($diags,"array"));
		}
	}

	function patientkey($m) {

		$err="";
		$pkey ="";

		$pkey = $_SESSION['patient_id'];

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

	function practicekey($m) {

		$err="";

		$sql = "SELECT * FROM form_encounter where encounter = '" . $_SESSION['billkey'] ."' and pid = '" . $_SESSION['patient_id'] ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$fname = $results->fields['facility'];
			}
		}

		$sql = "SELECT * FROM facility where name = '" . $fname ."'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$fkey =	$results->fields['id'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($fkey,"i4"));
		}
	}

	function providerkey($m) {

		$err="";

		$pkey ="";
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
				$pkey =	$results->fields['provider_id'];
			}
		}

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

	function referringproviderkey($m) {

		$err="";

		$pkey ="";
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
				$pkey =	$results->fields['providerID'];
			}
		}


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

	function insuredkey($m) {

		$err="";

		$payer_id = "";
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
				$payer_id  =	$results->fields['payer_id'];
			}
		}


		$insured_id = "";
		$sql = "SELECT * FROM insurance_data where pid = '" . $_SESSION['patient_id'] . "' and provider = '" . $payer_id . "'";
		//echo $sql;
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$insured_id  =	$results->fields['id'];
			}
		}
		//we are returning the record id of the appropriate entry in insurance_data
		//there is no relational data, all the subscriber/insured information is kept monolithically in that table

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($insured_id,"i4"));
		}
	}

	function otherinsuredkey($m) {

		$err="";
		//openemr does not currently implement other insured
		$pkey = 0;

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

	function payerkey($m) {
		$err="";

		$pkey = "";
		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM billing where id = '" . $key . "'";
		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);


		if (!$results) {
			$err = $db->ErrorMsg() . " $sql";
		}
		else {
			while (!$results->EOF) {
				$pkey = $results->fields['payer_id'];
				$results->MoveNext();
			}
		}

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

	function secondpayerkey($m) {

		$err="";
		//unimplemented by OpenEMR
		$pkey = 0;

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

	function facilitykey($m) {

				$eid = "";
		$patient_id = "";
		$fname ="";

		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM billing where id = '" . $key . "'";

		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$eid = $results->fields['encounter'];
				$patient_id = $results->fields['pid'];
			}
		}

		$sql = "SELECT * FROM form_encounter where encounter = '" . $eid ."' and pid = '" . $patient_id ."'";

		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$fname = $results->fields['facility'];
			}
		}

		$sql = "SELECT * FROM facility where name = '" . $fname ."'";
		$results = $db->Execute($sql);
		if (!$results) {
			echo "error";
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$fkey =	$results->fields['id'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($fkey,"i4"));
		}
	}

	function billingcontactkey($m) {

		$err="";

		$pkey = 0;

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
	function billingservicekey($m) {

		$err="";

		$pkey = 0;

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


	function isusingbillingservice($m) {

		$err="";

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

	function clearinghousekey($m) {

		$err="";

		$eid = "";
		$patient_id = "";
		$fname ="";

		$obj= $m->getparam(0);
		$key = $obj->getval();

		$sql = "SELECT * FROM billing where id = '" . $key . "'";

		$db = $GLOBALS['adodb']['db'];
		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$eid = $results->fields['encounter'];
				$patient_id = $results->fields['pid'];
			}
		}

		$sql = "SELECT * FROM form_encounter where encounter = '" . $eid ."' and pid = '" . $patient_id ."'";

		$results = $db->Execute($sql);

		if (!$results) {
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$fname = $results->fields['facility'];
			}
		}

		$sql = "SELECT * FROM facility where name = '" . $fname ."'";
		$results = $db->Execute($sql);
		if (!$results) {
			echo "error";
			$err = $db->ErrorMsg();
		}
		else {
			if (!$results->EOF) {
				$fkey =	$results->fields['id'];
			}
		}

		// if we generated an error, create an error return response
		if ($err) {
			return $this->_handleError($err);
		}
  		else {
			// otherwise, we create the right response
			// with the state name
			return new xmlrpcresp(new xmlrpcval($fkey,"i4"));
		}

	}



}

//'FreeB.FBProcedure.isUsingClearingHouse' 	=> \&FreeB_FBProcedure_isUsingClearingHouse,
//'FreeB.FBProcedure.ProcArray' 			=> \&FreeB_FBProcedure_ProcArray,
//'FreeB.FBProcedure.DiagArray' 			=> \&FreeB_FBProcedure_DiagArray,
//'FreeB.FBProcedure.CPT4Code' 			=> \&FreeB_FBProcedure_CPT4Code,
//'FreeB.FBProcedure.CPT5Code' 			=> \&FreeB_FBProcedure_CPT4Code,
//'FreeB.FBProcedure.CPTUnits' 			=> \&FreeB_FBProcedure_CPTUnits,
//'FreeB.FBProcedure.CPTModifier' 		=> \&FreeB_FBProcedure_CPTModifier,
//'FreeB.FBProcedure.CPTCharges'	 		=> \&FreeB_FBProcedure_CPTCharges,
//'FreeB.FBProcedure.CPTEmergency' 		=> \&FreeB_FBProcedure_CPTEmergency,
//'FreeB.FBProcedure.CPTCOB' 			=> \&FreeB_FBProcedure_CPTCOB,
//'FreeB.FBProcedure.CPTEPSDT' 			=> \&FreeB_FBProcedure_CPTEPSDT,
//'FreeB.FBProcedure.AmountPaid' 			=> \&FreeB_FBProcedure_AmountPaid,
//'FreeB.FBProcedure.TypeOfService' 		=> \&FreeB_FBProcedure_TypeOfService,
//'FreeB.FBProcedure.PriorAuth' 			=> \&FreeB_FBProcedure_PriorAuth,
//'FreeB.FBProcedure.DateOfServiceStart' 		=> \&FreeB_FBProcedure_DateOfServiceStart,
//'FreeB.FBProcedure.DateOfServiceEnd' 		=> \&FreeB_FBProcedure_DateOfServiceEnd,
//'FreeB.FBProcedure.DateOfHospitalStart' 	=> \&FreeB_FBProcedure_DateOfHospitalStart,
//'FreeB.FBProcedure.isHospitalized' 		=> \&FreeB_FBProcedure_isHospitalized,
//'FreeB.FBProcedure.isOutsideLab' 		=> \&FreeB_FBProcedure_isOutsideLab,
//'FreeB.FBProcedure.OutsideLabCharges' 		=> \&FreeB_FBProcedure_OutSideLabCharges,
//'FreeB.FBProcedure.DateOfHospitalEnd' 		=> \&FreeB_FBProcedure_DateOfHospitalEnd,
//'FreeB.FBProcedure.MedicaidResubmissionCode' 	=> \&FreeB_FBProcedure_MedicaidResubmissionCode,
//'FreeB.FBProcedure.MedicaidOriginalReference' 	=> \&FreeB_FBProcedure_MedicaidOriginalReference,
//'FreeB.FBProcedure.WeightGrams' 		=> \&FreeB_FBProcedure_WeightGrams,
//'FreeB.FBProcedure.WeightPounds'	 	=> \&FreeB_FBProcedure_WeightPounds,
//'FreeB.FBProcedure.HCFALocalUse10d' 		=> \&FreeB_FBProcedure_HCFALocalUse10d,
//'FreeB.FBProcedure.HCFALocalUse19' 		=> \&FreeB_FBProcedure_HCFALocalUse19,
?>
