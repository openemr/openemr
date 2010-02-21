<?php

require_once($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/InsuranceCompany.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/X12Partner.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/WSWrapper.class.php");

class C_InsuranceCompany extends Controller {

	var $template_mod;
	var $icompanies;

	function C_InsuranceCompany($template_mod = "general") {
		parent::Controller();
		$this->icompanies = array();
		$this->template_mod = $template_mod;
		$this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
		$this->assign("CURRENT_ACTION", $GLOBALS['webroot']."/controller.php?" . "practice_settings&insurance_company&");
		$this->assign("STYLE", $GLOBALS['style']);
		$this->assign("WEB_ROOT", $GLOBALS['webroot'] );		
	}

	function default_action() {
		return $this->list_action();
	}

	function edit_action($id = "",$patient_id="",$p_obj = null) {
		if ($p_obj != null && get_class($p_obj) == "insurancecompany") {
			$this->icompanies[0] = $p_obj;
		}
		elseif (get_class($this->icompanies[0]) != "insurancecompany" ) {
			$this->icompanies[0] = new InsuranceCompany($id);
		}

		$x = new X12Partner();
		$this->assign("x12_partners", $x->_utility_array($x->x12_partner_factory()));

		$this->assign("insurancecompany", $this->icompanies[0]);
		return $this->fetch($GLOBALS['template_dir'] . "insurance_companies/" . $this->template_mod . "_edit.html");
	}

	function list_action($sort = "") {

		if (!empty($sort)) {
			$this->assign("icompanies", InsuranceCompany::insurance_companies_factory("",$sort));
		}
		else {
			$this->assign("icompanies", InsuranceCompany::insurance_companies_factory());
		}

		return $this->fetch($GLOBALS['template_dir'] . "insurance_companies/" . $this->template_mod . "_list.html");
	}


	function edit_action_process() {
		if ($_POST['process'] != "true")
			return;
		//print_r($_POST);
		if (is_numeric($_POST['id'])) {
			$this->icompanies[0] = new InsuranceCompany($_POST['id']);
		}
		else {
			$this->icompanies[0] = new InsuranceCompany();
		}

  		parent::populate_object($this->icompanies[0]);

		$this->icompanies[0]->persist();
		$this->icompanies[0]->populate();

		// Post insurance companies as customers to the accounting system
		// unless globals.php requests otherwise.
		//
		if (! $GLOBALS['insurance_companies_are_not_customers'])
			$this->_sync_ws($this->icompanies[0]);

		//echo "action processeed";
		$_POST['process'] = "";
	}

	function _sync_ws($ic) {

		$db = $GLOBALS['adodb']['db'];

		$customer_info = array();

		$sql = "SELECT foreign_id,foreign_table FROM integration_mapping where local_table = 'insurance_companies' and local_id = '" . $ic->get_id() . "'";
		$result = $db->Execute($sql);
		if ($result && !$result->EOF) {
			$customer_info['foreign_update'] = true;
			$customer_info['foreign_id'] = $result->fields['foreign_id'];
			$customer_info['foreign_table'] = $result->fields['foreign_table'];
		}

		///xml rpc code to connect to accounting package and add user to it
		$customer_info['firstname'] = "";
		$customer_info['lastname'] = $ic->get_name();
		$a = $ic->get_address();
		$customer_info['address'] = $a->get_line1() . " " . $a->get_line2();
		$customer_info['suburb'] = $a->get_city();
		$customer_info['postcode'] = $a->get_zip();

		//ezybiz wants state as a code rather than abbreviation
		$customer_info['geo_zone_id'] = "";
		$sql = "SELECT zone_id from geo_zone_reference where zone_code = '" . strtoupper($a->get_state()) . "'";
		$db = $GLOBALS['adodb']['db'];
		$result = $db->Execute($sql);
		if ($result && !$result->EOF) {
			$customer_info['geo_zone_id'] = $result->fields['zone_id'];
		}

		//ezybiz wants country as a code rather than abbreviation
		$customer_info['country'] = "";

		//assume USA for insurance companies
		$country_code = 223;
		$sql = "SELECT countries_id from geo_country_reference where countries_iso_code_2 = '" . strtoupper($country_code) . "'";
		$db = $GLOBALS['adodb']['db'];
		$result = $db->Execute($sql);
		if ($result && !$result->EOF) {
			$customer_info['geo_country_id'] = $result->fields['countries_id'];
		}

		$customer_info['phone1'] = $ic->get_phone();
		$customer_info['phone1comment'] = "Phone Number";
		$customer_info['phone2'] = "";
		$customer_info['phone2comment'] = "";
		$customer_info['email'] = "";
		$customer_info['is_payer'] = true;
		$function['ezybiz.add_customer'] = array(new xmlrpcval($customer_info,"struct"));
		$ws = new WSWrapper($function);

		// if the remote patient was added make an entry in the local mapping table to that updates can be made correctly
		if (is_numeric($ws->value)) {
			$sql = "REPLACE INTO integration_mapping set id = '" . $db->GenID("sequences") . "', foreign_id ='" . $ws->value . "', foreign_table ='customer', local_id = '" . $ic->get_id() . "', local_table = 'insurance_companies' ";
			$db->Execute($sql) or die ("error: " . $db->ErrorMsg());
		}

	}

}

?>
