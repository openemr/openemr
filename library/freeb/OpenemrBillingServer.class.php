<?php
require_once("xmlrpc.inc");
require_once("xmlrpcs.inc");
require_once("Utility.class.php");
require_once("Payer.class.php");
require_once("Provider.class.php");
require_once("Patient.class.php");
require_once("Insured.class.php");
require_once("Practice.class.php");
require_once("Facility.class.php");
require_once("BillingContact.class.php");
require_once("BillingService.class.php");
require_once("ClearingHouse.class.php");
require_once("Diagnosis.class.php");
require_once("Procedure.class.php");

class OpenemrBillingServer {

	var $utility;
	var $payer;
	var $provider;
	var $patient;
	var $insured;
	var $practice;
	var $facility;
	var $bc;
	var $bs;
	var $cs;
	var $diagnosis;
	var $procedure;
	var $func_map;

	function OpenemrBillingServer($xuser) {

		$this->utility = new Utility($xuser);
		$this->payer = new Payer($xuser);
		$this->provider = new Provider($xuser);
		$this->patient = new Patient($xuser);
		$this->insured = new Insured($xuser);
		$this->practice = new Practice($xuser);
		$this->facility = new Facility($xuser);
		$this->bc = new BillingContact($xuser);
		$this->bs = new BillingService($xuser);
		$this->cs = new ClearingHouse($xuser);
		$this->diagnosis = new Diagnosis($xuser);
		$this->procedure = new Procedure($xuser);
	}

}
//$o = new Openemr_billing_server($GLOBALS['xmlrpcerruser']);
//print_r($o);



?>
