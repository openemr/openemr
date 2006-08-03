<?php

require_once($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/Prescription.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/Provider.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/RXList.class.php");

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

		if ($GLOBALS['inhouse_pharmacy']) {
			// Make an array of drug IDs and selectors for the template.
			$drug_array = array(0 => "-- or select from inventory --");
			$drug_attributes = '';
			$res = sqlStatement("SELECT * FROM drugs ORDER BY selector");
			while ($row = sqlFetchArray($res)) {
				$drug_array[$row['drug_id']] = $row['selector'];
				if ($row['ndc_number']) {
					$drug_array[$row['drug_id']] .= ' [' . $row['ndc_number'] . ']';
				}
				if ($drug_attributes) $drug_attributes .= ',';
				$drug_attributes .=    "['"  .
					$row['name']       . "',"  . //  0
					$row['form']       . ",'"  . //  1
					$row['dosage']     . "',"  . //  2
					$row['size']       . ","   . //  3
					$row['unit']       . ","   . //  4
					$row['route']      . ","   . //  5
					$row['period']     . ","   . //  6
					$row['substitute'] . ","   . //  7
					$row['quantity']   . ","   . //  8
					$row['refills']    . ","   . //  9
					$row['per_refill'] . "]";    // 10
			}
			$this->assign("DRUG_ARRAY", $drug_array);
			$this->assign("DRUG_ATTRIBUTES", $drug_attributes);
		}

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

		// If the "Prescribe and Dispense" button was clicked, then
		// redisplay as in edit_action() but also replicate the fee and
		// include a piece of javascript to call dispense().
		//
		if ($_POST['disp_button']) {
			$this->assign("DISP_QUANTITY", $_POST['disp_quantity']);
			$this->assign("DISP_FEE", $_POST['disp_fee']);
			$this->assign("ENDING_JAVASCRIPT", "dispense();");
			$this->_state = false;
			return $this->edit_action($this->prescriptions[0]->id);
		}

		return $this->send_action($this->prescriptions[0]->id);
	}

	function send_action($id) {
		$_POST['process'] = "true";
		if(empty($id)) {
			$this->function_argument_error();
		}

		$rx = new Prescription($id);
		// Populate pharmacy info if the patient has a default pharmacy.
		// Probably the Prescription object should handle this instead, but
		// doing it there will require more careful research and testing.
		$prow = sqlQuery("SELECT pt.pharmacy_id FROM prescriptions AS rx, " .
			"patient_data AS pt WHERE rx.id = '$id' AND pt.pid = rx.patient_id");
		if ($prow['pharmacy_id']) {
			$rx->pharmacy->set_id($prow['pharmacy_id']);
			$rx->pharmacy->populate();
		}
		$this->assign("prescription", $rx);

		$this->_state = false;
		return $this->fetch($GLOBALS['template_dir'] . "prescription/" .
			$this->template_mod . "_send.html");
	}

        function multiprint_header(& $pdf, $p) {
                //print header
                $pdf->ezImage($GLOBALS['fileroot'] . '/interface/pic/Rx.png','','50','','center','');
                $pdf->ezColumnsStart(array('num'=>2, 'gap'=>10));
                $res = sqlQuery("SELECT concat('<b>',f.name,'</b>\n',f.street,'\n',f.city,', ',f.state,' ',f.postal_code,'\nTel:',f.phone) addr FROM users JOIN facility AS f ON f.name = users.facility where users.id ='"
                      . mysql_real_escape_string($p->provider->id) . "'");

                $pdf->ezText($res['addr'],12);
                $my_y = $pdf->y;
                $pdf->ezNewPage();
                $pdf->ezText('<b>' . $p->provider->get_name_display() . '</b>',12);
                $pdf->ezText('<b>DEA:</b>' . $p->provider->federal_drug_id,12);
                $pdf->ezColumnsStop();
                if ($my_y < $pdf->y){
                        $pdf->ezSetY($my_y);
                }
                $pdf->ezText('',10);
                $pdf->setLineStyle(1);
                $pdf->ezColumnsStart(array('num'=>2));
                $pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
                $pdf->ezText('<b>Patient Name & Address</b>',6);
                $pdf->ezText($p->patient->get_name_display(),10);
		$res = sqlQuery("SELECT  concat(street,'\n',city,', ',state,' ',postal_code,'\n',if(phone_home!='',phone_home,if(phone_cell!='',phone_cell,if(phone_biz!='',phone_biz,'')))) addr from patient_data where pid =". mysql_real_escape_string ($p->patient->id));
                $pdf->ezText($res['addr']);
                $my_y = $pdf->y;
                $pdf->ezNewPage();
                $pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
                $pdf->ezText('<b>Date of Birth</b>',6);
                $pdf->ezText($p->patient->date_of_birth,10);
                $pdf->ezText('');
                $pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
                $pdf->ezText('<b>Medical Record #</b>',6);
                $pdf->ezText(str_pad($p->patient->get_id(), 10, "0", STR_PAD_LEFT),10);
                $pdf->ezColumnsStop();
                if ($my_y < $pdf->y){
                        $pdf->ezSetY($my_y);
                }
                $pdf->ezText('');
                $pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);

                $pdf->ezText('<b>Prescriptions</b>',6);
                $pdf->ezText('',10);
        }

        function multiprint_footer(& $pdf){
                if($this->pconfig['use_signature'] == true ) {
                        $pdf->ezImage($this->pconfig['signature'],"","","none","left");
                }
                else{
                  $pdf->ezText("\n\n\n\nSignature:________________________________\nDate: " . date('Y-m-d'),12);
                }
        }

        function get_prescription_body_text($p) {
                $body = '<b>Rx: ' . $p->get_drug() . ' ' . $p->get_size() . ' ' . $p->get_unit_display()
                       . ' [' . $p->form_array[$p->get_form()] . "]</b>     <i>"
                       . $p->substitute_array[$p->get_substitute()] . "</i>\n"
                       . '<b>Disp #:</b> <u>' . $p->get_quantity() . "</u>\n"
		       . '<b>Sig:</b> ' . $p->get_dosage() . ' ' . $p->form_array[$p->get_form()] .' ' 
		       . $p->route_array[$p->get_route()] . ' ' . $p->interval_array[$p->get_interval()] .  "\n";
                if ($p->get_refills() > 0) {
                        $body .= "\n<b>Refills:</b> <u>" .  $p->get_refills() . " of quantity " . $p->get_per_refill() . "</u>\n";
                }
                else {
                        $body .= "\n<b>Refills:</b> <u>0 (Zero)</u>\n";
                }
                $note = $p->get_note();
                if ($note != '') {
                        $body .= "\n$note\n";
                }
                return $body;
        }

        function multiprint_body(& $pdf, $p){
                $pdf->ez['leftMargin'] += $pdf->ez['leftMargin'];
                $pdf->ez['rightMargin'] += $pdf->ez['rightMargin'];
                $d = $this->get_prescription_body_text($p);

                if ( $pdf->ezText($d,10,array(),1) ) {
                        $pdf->ez['leftMargin'] -= $pdf->ez['leftMargin'];
                        $pdf->ez['rightMargin'] -= $pdf->ez['rightMargin'];
                        $this->multiprint_footer($pdf, $p);
                        $pdf->ezNewPage();
                        $this->multiprint_header($pdf, $p);
                        $pdf->ez['leftMargin'] += $pdf->ez['leftMargin'];
                        $pdf->ez['rightMargin'] += $pdf->ez['rightMargin'];
                }
                $my_y = $pdf->y;
                $pdf->ezText($d,10);
                $pdf->setColor(.9,.9,.9);
                $pdf->filledRectangle($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin']-$pdf->ez['leftMargin'],$my_y - $pdf->y);
                $pdf->setColor(0,0,0);
                $pdf->ezSetY($my_y);
                $pdf->ezText($d,10);
                $pdf->ez['leftMargin'] = $GLOBALS['oer_config']['prescriptions']['left'];
                $pdf->ez['rightMargin'] = $GLOBALS['oer_config']['prescriptions']['right'];
                $pdf->ezText('');
                $pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
                $pdf->ezText('');

        }

        function multiprint_action($id = "") {
                $_POST['process'] = "true";
                if(empty($id)) {
                        $this->function_argument_error();
                }
                require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
                $pdf =& new Cezpdf($GLOBALS['oer_config']['prescriptions']['paper_size']);
                $pdf->ezSetMargins($GLOBALS['oer_config']['prescriptions']['top']
                      ,$GLOBALS['oer_config']['prescriptions']['bottom']
                                  ,$GLOBALS['oer_config']['prescriptions']['left']
                      ,$GLOBALS['oer_config']['prescriptions']['right']
                      );
                $pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");


                $print_header = true;

                //print prescriptions body
                $this->_state = false; // Added by Rod - see Controller.class.php
                $ids = preg_split('/::/', substr($id,1,strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($ids as $id) {
                        $p = new Prescription($id);
                        if ($print_header == true) {
                                $this->multiprint_header($pdf, $p);
                                $print_header = false;
                        }
                        $this->multiprint_body($pdf, $p);
                }

                $this->multiprint_footer($pdf);

                $pdf->ezStream();
                return;
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
                $pdf =& new Cezpdf($GLOBALS['oer_config']['prescriptions']['paper_size']);
                $pdf->ezSetMargins($GLOBALS['oer_config']['prescriptions']['top']
                      ,$GLOBALS['oer_config']['prescriptions']['bottom']
                                  ,$GLOBALS['oer_config']['prescriptions']['left']
                      ,$GLOBALS['oer_config']['prescriptions']['right']
                      );

                $pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");

                $this->multiprint_header($pdf, $p);

                $this->multiprint_body($pdf, $p);

                $this->multiprint_footer($pdf);

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

	function _print_prescription_old($p, & $toFile) {
		require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
		$pdf =& new Cezpdf($GLOBALS['oer_config']['prescriptions']['paper_size']);
		$pdf->ezSetMargins($GLOBALS['oer_config']['prescriptions']['top']
                      ,$GLOBALS['oer_config']['prescriptions']['bottom']
 		                  ,$GLOBALS['oer_config']['prescriptions']['left']
                      ,$GLOBALS['oer_config']['prescriptions']['right']
                      );

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
