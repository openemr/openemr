<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] ."/library/classes/Prescription.class.php");
require_once($GLOBALS['fileroot'] ."/library/classes/Provider.class.php");
require_once($GLOBALS['fileroot'] ."/library/classes/RXList.class.php");

class C_Prescription extends Controller {

	var $template_mod;
	var $pconfig;

	function C_Prescription($template_mod = "general") {
		parent::Controller();
		$this->template_mod = $template_mod;
		$this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
		$this->assign("TOP_ACTION", $GLOBALS['webroot']."/controller.php?" . "prescription" . "&");
		$this->assign("STYLE", $GLOBALS['style']);
		$this->pconfig = $GLOBALS['oer_config']['prescriptions'];
	}

	function default_action() {
		$this->assign("prescription",$this->prescriptions[0]);
		$this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_edit.html");
	}

	function edit_action($id = "",$patient_id="",$p_obj = null) {

		if ($p_obj != null && get_class($p_obj) == "prescription") {
			$this->prescriptions[0] = $p_obj;
		}
		elseif (get_class($this->prescriptions[0]) != "prescription" ) {
			$this->prescriptions[0] = new Prescription($id);
		}

		if (!empty($patient_id)) {
			$this->prescriptions[0]->set_patient_id($patient_id);
		}
		$this->default_action();
	}

	function list_action($id,$sort = "") {
		if (empty($id)) {
		 	$this->function_argument_error();
			exit;
		}
		if (!empty($sort)) {
			$this->assign("prescriptions", Prescription::prescriptions_factory($id,$sort));
		}
		else {
			$this->assign("prescriptions", Prescription::prescriptions_factory($id));
		}
		//print_r(Prescription::prescriptions_factory($id));
		$this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_list.html");
	}

	function block_action($id,$sort = "") {
		if (empty($id)) {
		 	$this->function_argument_error();
			exit;
		}
		if (!empty($sort)) {
			$this->assign("prescriptions", Prescription::prescriptions_factory($id,$sort));
		}
		else {
			$this->assign("prescriptions", Prescription::prescriptions_factory($id));
		}
		//print_r(Prescription::prescriptions_factory($id));
		$this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_block.html");
	}

	function lookup_action() {
		$this->do_lookup();
		$this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_lookup.html");
	}

	function edit_action_process() {
		if ($_POST['process'] != "true")
			return;
		//print_r($_POST);

		$this->prescriptions[0] = new Prescription($_POST['id']);
		parent::populate_object($this->prescriptions[0]);
		//echo $this->prescriptions[0]->toString(true);
		$this->prescriptions[0]->persist();
		$_POST['process'] = "";
		return $this->send_action($this->prescriptions[0]->id);
	}

	function send_action($id) {
		$_POST['process'] = "true";
		if(empty($id)) {
			$this->function_argument_error();
		}

		$this->assign("prescription",new Prescription($id));
		$this->_state = false;
		return $this->fetch($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_send.html");
	}

	function send_action_process($id) {
		$dummy = ""; // Added by Rod to avoid run-time warnings
		if ($_POST['process'] != "true")
			return;
		if(empty($id)) {
			$this->function_argument_error();
		}
		$p = new Prescription($id);
		switch ($_POST['submit']) {

		case "Print":
				// The following statement added by Rod.
				// Looking at Controller.class.php, it appears that _state is set to false
				// to indicate that no further HTML is to be generated.
				$this->_state = false; // Added by Rod - see Controller.class.php
				return $this->_print_prescription($p, $dummy);
				break;
		case "Email":
				return $this->_email_prescription($p,$_POST['email_to']);
				break;
		case "Fax":
				//this is intended to be the hook for the hylafax code we already have that hasn't worked its way into the tree yet.
				//$this->assign("process_result","No fax server is currently setup.");
				return $this->_fax_prescription($p,$_POST['fax_to']);
				break;
		case "Auto Send":
				$pharmacy_id = $_POST['pharmacy_id'];
				//echo "auto sending to : " . $_POST['pharmacy_id'];
				$phar = new Pharmacy($_POST['pharmacy_id']);
				//print_r($phar);
				if ($phar->get_transmit_method() == TRANSMIT_PRINT) {
					return $this->_print_prescription($p, $dummy);
				}
				elseif ($phar->get_transmit_method() == TRANSMIT_EMAIL) {
					$email = $phar->get_email();
					if (!empty($email)) {
						return $this->_email_prescription($p,$phar->get_email());
					}
					//else print it
				}
				elseif ($phar->get_transmit_method() == TRANSMIT_FAX) {
					$faxNum= $phar->get_fax();
					if(!empty($faxNum)) {
						Return $this->_fax_prescription ($p,$faxNum);
					}
					// return $this->assign("process_result","No fax server is currently setup.");
					// else default is printing,
				}
				else {
			 		//the pharmacy has no default or default is print
					return $this->_print_prescription($p, $dummy);
				}
				break;
		}

		return;

	}

	function _print_prescription($p, & $toFile) {
		require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
		$pdf =& new Cezpdf("LETTER");
		$pdf->ezSetMargins(72,30,50,30);
		$pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");

		if(!empty($this->pconfig['logo'])) {
			$pdf->ezImage($this->pconfig['logo'],"","","none","left");
		}
		$pdf->ezText($p->get_prescription_display(),10);
		if($this->pconfig['use_signature'] == true ) {
			$pdf->ezImage($this->pconfig['signature'],"","","none","left");
		}
		else{
		  $pdf->ezText("\n\n\n\nSignature:________________________________",10);
		}

		if(!empty($toFile))
		{
			$toFile = $pdf->ezOutput();
		}
		else
		{
			$pdf->ezStream();
			// $pdf->ezStream(array('compress' => 0)); // for testing with uncompressed output
		}
		return;
	}

	function _email_prescription($p,$email) {
		if (empty($email)) {
			$this->assign("process_result","Email could not be sent, the address supplied: '$email' was empty or invalid.");
			return;
		}
		require($GLOBALS['fileroot'] . "/library/classes/class.phpmailer.php");
		$mail = new PHPMailer();
		$mail->SetLanguage("en",$GLOBALS['fileroot'] . "/library/" );
		//this is a temporary config item until the rest of the per practice billing settings make their way in
		$mail->From = $GLOBALS['practice_return_email_path'];
		$mail->FromName = $p->provider->get_name_display();
		$mail->isMail();
		$mail->Host     = "localhost";
		$mail->Mailer   = "mail";
		$text_body  = $p->get_prescription_display();
		$mail->Body = $text_body;
		$mail->Subject = "Prescription for: " . $p->patient->get_name_display();
		$mail->AddAddress($email);
		if($mail->Send()) {
			$this->assign("process_result","Email was successfully sent to: " . $email);
			return;
		}
		else {
			$this->assign("process_result","There has been a mail error sending to " . $_POST['email_to'] . " " . $mail->ErrorInfo);
			return;
		}
	}

	function do_lookup() {
		if ($_POST['process'] != "true")
			return;
		$list = array();
		if (!empty($_POST['drug'])) {
			$list = @RxList::get_list($_POST['drug']);
		}
		if (is_array($list)) {
			$list = array_flip($list);
			$this->assign("drug_options",$list);
			$this->assign("drug_values",array_keys($list));
		}
		else {
			$this->assign("NO_RESULTS","No results found for: " .$_POST['drug'] . "<br />");
		}
		//print_r($_POST);
		//$this->assign("PROCESS","");

		$_POST['process'] = "";
	}

	function _fax_prescription($p,$faxNum)
	{
		$err = "Sent fax";
		//strip - ,(, ), and ws
		$faxNum = preg_replace("/(-*)(\(*)(\)*)(\s*)/","",$faxNum);
		//validate the number

		if(!empty($faxNum) && is_numeric($faxNum))
		{
			//get the sendfax command and execute it
			$cmd = $this->pconfig['sendfax'];
			// prepend any prefix to the fax number
			$pref=$this->pconfig['prefix'];
			$faxNum=$pref.$faxNum;
			if(empty($cmd))
			{
				$err .= " Send fax not set in includes/config.php";
				break;
			}
			else
			{
				//generate file to fax
				$faxFile = "Failed";
				$this->_print_prescription($p, $faxFile);
				if(empty($faxFile))
				{
					$err .= " _print_prescription returned empty file";
					break;
				}
				$fileName = dirname(__FILE__)."/../documents/".$p->get_id()
								.$p->get_patient_id()."_fax_.pdf";
				//print "filename is $fileName";
				touch($fileName); // php bug
				$handle = fopen($fileName,"w");
				if(!$handle)
				{
					$err .= " Failed to open file $fileName to write fax to";
					break;
				}
				if(fwrite($handle, $faxFile) === false)
				{
					$err .= " Failed to write data to $fileName";
					break;
				}
				fclose($handle);
				$args = " -n -d $faxNum $fileName";
				//print "command is $cmd $args<br>";
				exec($cmd . $args);
			}

		}
		else
		{
			$err = "bad fax number passed to function";
		}
		if($err)
		{
			$this->assign("process_result",$err);
		}
	}
}

?>
