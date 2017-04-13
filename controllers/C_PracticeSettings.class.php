<?php


class C_PracticeSettings extends Controller {

	var $template_mod;

	var $direction;

	function __construct($template_mod = "general")
	{
		parent::__construct();
		$this->template_mod = $template_mod;
		$this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
		$this->assign("TOP_ACTION", $GLOBALS['webroot']."/controller.php?" . "practice_settings" . "&");
		$this->assign("STYLE", $GLOBALS['style']);
	    $this->assign("CSS_HEADER",  $GLOBALS['css_header'] );
		$this->direction = ($GLOBALS['language_direction'] == 'rtl') ? 'right' : 'left';
		$this->mainColClass = ($this->direction == 'right') ? '' : 'col-md-offset-2';
	}

	function default_action($display = "") {
		$this->assign("display",$display);
		$this->assign("sidebar_direction", $this->direction);
		$this->assign("mainColClass", $this->mainColClass);
		$this->display($GLOBALS['template_dir'] . "practice_settings/" . $this->template_mod . "_list.html");
	}

	function pharmacy_action($arg) {
		$c = new Controller();

		//this dance is so that the controller system which only cares about the name part of the first two arguments get what it wants
		//and the rest gets passed as normal argument values, really this all goes back to workarounds for problems with call_user_func
		//and value passing

		$fga = func_get_args();

		$fga = array_slice($fga,1);
		$args = array_merge(array("pharmacy" => "",$arg => ""),$fga);
		$this->assign("sidebar_direction", $this->direction);
		$this->assign("mainColClass", $this->mainColClass);
		$display =  $c->act($args);
		$this->assign("ACTION_NAME", xl("Pharmacies") );
		$this->default_action($display);
	}

	function insurance_company_action($arg) {
		$c = new Controller();

		//this dance is so that the controller system which only cares about the name part of the first two arguments get what it wants
		//and the rest gets passed as normal argument values, really this all goes back to workarounds for problems with call_user_func
		//and value passing

		$fga = func_get_args();

		$fga = array_slice($fga,1);
		$args = array_merge(array("insurance_company" => "",$arg => ""),$fga);

		$display =  $c->act($args);
		$this->assign("sidebar_direction", $this->direction);
		$this->assign("mainColClass", $this->mainColClass);
		$this->assign("ACTION_NAME", xl("Insurance Companies") );
		$this->default_action($display);
	}

	function insurance_numbers_action($arg) {
		$c = new Controller();

		//this dance is so that the controller system which only cares about the name part of the first two arguments get what it wants
		//and the rest gets passed as normal argument values, really this all goes back to workarounds for problems with call_user_func
		//and value passing

		$fga = func_get_args();

		$fga = array_slice($fga,1);
		$args = array_merge(array("insurance_numbers" => "",$arg => ""),$fga);

		$display =  $c->act($args);

		$this->assign("ACTION_NAME", xl("Insurance Numbers") );
		$this->assign("sidebar_direction", $this->direction);
		$this->assign("mainColClass", $this->mainColClass);
		$this->default_action($display);
	}

	function document_action($arg) {
		$c = new Controller();

		//this dance is so that the controller system which only cares about the name part of the first two arguments get what it wants
		//and the rest gets passed as normal argument values, really this all goes back to workarounds for problems with call_user_func
		//and value passing

		$fga = func_get_args();

		$fga = array_slice($fga,1);
		$args = array_merge(array("document" => "",$arg => ""),$fga);

		$display =  $c->act($args);

		$this->assign("ACTION_NAME", xl("Documents") );
		$this->assign("sidebar_direction", $this->direction);
		$this->assign("mainColClass", $this->mainColClass);
		$this->default_action($display);
	}

	function document_category_action($arg) {
		$c = new Controller();

		//this dance is so that the controller system which only cares about the name part of the first two arguments get what it wants
		//and the rest gets passed as normal argument values, really this all goes back to workarounds for problems with call_user_func
		//and value passing

		$fga = func_get_args();

		$fga = array_slice($fga,1);
		$args = array_merge(array("document_category" => "",$arg => ""),$fga);

		$display =  $c->act($args);

		$this->assign("ACTION_NAME", xl("Documents") );
		$this->assign("sidebar_direction", $this->direction);
		$this->assign("mainColClass", $this->mainColClass);
		$this->default_action($display);
	}

	function x12_partner_action($arg) {
		$c = new Controller();

		//this dance is so that the controller system which only cares about the name part of the first two arguments get what it wants
		//and the rest gets passed as normal argument values, really this all goes back to workarounds for problems with call_user_func
		//and value passing

		$fga = func_get_args();

		$fga = array_slice($fga,1);
		$args = array_merge(array("x12_partner" => "",$arg => ""),$fga);

		$display =  $c->act($args);

		$this->assign("ACTION_NAME", xl("X12 Partners") );
		$this->assign("sidebar_direction", $this->direction);
		$this->assign("mainColClass", $this->mainColClass);
		$this->default_action($display);
	}


	function hl7_action($arg) {
		$c = new Controller();

		//this dance is so that the controller system which only cares about the name part of the first two arguments get what it wants
		//and the rest gets passed as normal argument values, really this all goes back to workarounds for problems with call_user_func
		//and value passing

		$fga = func_get_args();
		$fga = array_slice($fga,1);
		$args = array_merge(array("hl7" => "",$arg => ""),$fga);
		$display =  $c->act($args);
		$this->assign("ACTION_NAME", xl("HL7 Viewer") );
		$this->assign("sidebar_direction", $this->direction);
		$this->assign("mainColClass", $this->mainColClass);
		$this->default_action($display);
	}

}

?>
