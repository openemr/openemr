<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class ActionKeys Extends DataObjectBase {

	function ActionKeys() {
		$this->_addFunc("procarray", 		array(	"name"	=>	"FreeB.FBProcedure.ProcArray",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("diagarray", 		array(	"name"	=>	"FreeB.FBProcedure.DiagArray",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("patientkey",		array(	"name"	=>	"FreeB.FBProcedure.PatientKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("practicekey",		array(	"name"	=>	"FreeB.FBProcedure.PracticeKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("providerkey",		array(	"name"	=>	"FreeB.FBProcedure.ProviderKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("insuredkey",		array(	"name"	=>	"FreeB.FBProcedure.InsuredKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("otherinsuredkey",	array(	"name"	=>	"FreeB.FBProcedure.OtherInsuredKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("payerkey", 		array(	"name"	=>	"FreeB.FBProcedure.PayerKey",
															"sig"	=>	array(XMLRPCSTRING,XMLRPCINT),
															"doc"	=>	""));
		$this->_addFunc("secondpayerkey", 	array(	"name"	=>	"FreeB.FBProcedure.SecondPayerKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("facilitykey", 		array(	"name"	=>	"FreeB.FBProcedure.FacilityKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("billingcontactkey",array(	"name"	=>	"FreeB.FBProcedure.BillingContactKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("billingservicekey",array(	"name"	=>	"FreeB.FBProcedure.BillingServiceKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("isusingbillingservice",array(	"name"	=>	"FreeB.FBProcedure.IsUsingBillingService",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
		$this->_addFunc("clearinghousekey", array(	"name"	=>	"FreeB.FBProcedure.ClearingHouseKey",
															"sig"	=>	array(XMLRPCSTRING),
															"doc"	=>	""));
	}


	function procarray($m) {

		$err="";

		$procs = array(new xmlrpcval(144,"i4"),new xmlrpcval(233,"i4"));

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

		$diags = array(new xmlrpcval(104,"i4"),new xmlrpcval(101,"i4"));

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

		$pkey = 555111555;

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

	function practicekey($m) {

		$err="";

		$pkey = 111555111;

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

	function providerkey($m) {

		$err="";

		$pkey = 111555111;

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

	function insuredkey($m) {

		$err="";

		$pkey = 111555111;

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

	function otherinsuredkey($m) {

		$err="";

		$pkey = 111555111;

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

	function payerkey($m) {
		$err="";

		$pkey = 111555111;

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

	function secondpayerkey($m) {

		$err="";

		$pkey = 111555111;

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

	function facilitykey($m) {

		$err="";

		$pkey = 111555111;

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

	function billingcontactkey($m) {

		$err="";

		$pkey = 111555111;

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
	function billingservicekey($m) {

		$err="";

		$pkey = 111555111;

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
			return new xmlrpcresp(new xmlrpcval($pkey));
		}
	}

	function clearinghousekey($m) {

		$err="";

		$pkey = 44;

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


//'FreeB.FBProcedure.ProcArray' 			=> \&FreeB_FBProcedure_ProcArray,
//'FreeB.FBProcedure.DiagArray' 			=> \&FreeB_FBProcedure_DiagArray,
//'FreeB.FBProcedure.PatientKey' 			=> \&FreeB_FBProcedure_PatientKey,
//'FreeB.FBProcedure.PracticeKey' 		=> \&FreeB_FBProcedure_PracticeKey,
//'FreeB.FBProcedure.ProviderKey' 		=> \&FreeB_FBProcedure_ProviderKey,
//'FreeB.FBProcedure.InsuredKey' 			=> \&FreeB_FBProcedure_InsuredKey,
//'FreeB.FBProcedure.OtherInsuredKey' 		=> \&FreeB_FBProcedure_OtherInsuredKey,
//'FreeB.FBProcedure.PayerKey' 			=> \&FreeB_FBProcedure_PayerKey,
//'FreeB.FBProcedure.SecondPayerKey' 		=> \&FreeB_FBProcedure_SecondPayerKey,
//'FreeB.FBProcedure.FacilityKey' 		=> \&FreeB_FBProcedure_FacilityKey,
//'FreeB.FBProcedure.BillingContactKey' 		=> \&FreeB_FBProcedure_BillingContactKey,
//'FreeB.FBProcedure.BillingServiceKey' 		=> \&FreeB_FBProcedure_BillingServiceKey,
//'FreeB.FBProcedure.isUsingBillingService' 	=> \&FreeB_FBProcedure_isUsingBillingService,
//'FreeB.FBProcedure.ClearingHouseKey' 		=> \&FreeB_FBProcedure_ClearingHouseKey,


}


?>
