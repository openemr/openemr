<?php

require_once($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/Prescription.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/Provider.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/RXList.class.php");

class C_Prescriptiondutch extends Controller {

       var $template_mod;
       var $pconfig;
       var $providerid = 0;
       var $use_signature_images = false;

       function C_Prescriptiondutch($template_mod = "general") {
               parent::Controller();

               $this->template_mod = $template_mod;
               $this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
               $this->assign("TOP_ACTION", $GLOBALS['webroot']."/controller.php?" . "prescriptiondutch" . "&");
               $this->assign("STYLE", $GLOBALS['style']);
               $this->assign("WEIGHT_LOSS_CLINIC", $GLOBALS['weight_loss_clinic']);
               $this->assign("SIMPLIFIED_PRESCRIPTIONS", $GLOBALS['simplified_prescriptions']);
               $this->pconfig = $GLOBALS['oer_config']['prescriptions'];

               if ($GLOBALS['inhouse_pharmacy']) {
                       // Make an array of drug IDs and selectors for the template.
                       $drug_array_values = array(0);
                       $drug_array_output = array("-- or select from inventory --");
                       $drug_attributes = '';

                       // $res = sqlStatement("SELECT * FROM drugs ORDER BY selector");

                       $res = sqlStatement("SELECT d.name, d.ndc_number, d.form, d.size, " .
                               "d.unit, d.route, d.substitute, t.drug_id, t.selector, t.dosage, " .
                               "t.period, t.quantity, t.refills " .
                               "FROM drug_templates AS t, drugs AS d WHERE " .
                               "d.drug_id = t.drug_id ORDER BY t.selector");

                       while ($row = sqlFetchArray($res)) {
                               $tmp_output = $row['selector'];
                               if ($row['ndc_number']) {
                                       $tmp_output .= ' [' . $row['ndc_number'] . ']';
                               }
                               $drug_array_values[] = $row['drug_id'];
                               $drug_array_output[] = $tmp_output;
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
                                       $row['quantity']   . "]";    // 10 quantity per_refill
                       }
                       $this->assign("DRUG_ARRAY_VALUES", $drug_array_values);
                       $this->assign("DRUG_ARRAY_OUTPUT", $drug_array_output);
                       $this->assign("DRUG_ATTRIBUTES", $drug_attributes);
               }
       }

       function default_action() {
               $this->assign("prescription",$this->prescriptions[0]);
               $this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_edit_dutch.html");
       }

       function edit_action($id = "",$patient_id="",$p_obj = null) {

               if ($p_obj != null && get_class($p_obj) == "prescriptiondutch") {
                       $this->prescriptions[0] = $p_obj;
               }
               elseif (get_class($this->prescriptions[0]) != "prescriptiondutch" ) {
                       $this->prescriptions[0] = new Prescription($id);
               }

               if (!empty($patient_id)) {
                       $this->prescriptions[0]->set_patient_id($patient_id);
               }

               // If quantity to dispense is not already set from a POST, set its
               // default value.
               if (! $this->get_template_vars('DISP_QUANTITY')) {
                       $this->assign('DISP_QUANTITY', $this->prescriptions[0]->quantity);
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
               $this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_list_dutch.html");
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
               $this->providerid = $p->provider->id;
               $pdf->ezImage($GLOBALS['fileroot'] . '/interface/pic/logo_bosmanggz.jpg','5','280','','center','');

               //print header
               $pdf->ezText('Bosman GGz',9, array('left' => 480, 'justification' => 'right'));
               $pdf->ezText('Kanaalweg 17h',9, array('left' => 480, 'justification' => 'right'));
               $pdf->ezText('3526 KL Utrecht',9, array('left' => 480, 'justification' => 'right'));
               $pdf->ezText('Tel (030) 751 9820',9, array('left' => 460, 'justification' => 'right'));
               $pdf->ezText('Tel (030) 751 9831',9, array('left' => 460, 'justification' => 'right'));
               $pdf->ezText('Email: info@bosmanggz.nl',9, array('left' => 440, 'justification' => 'right'));
               $pdf->ezText('Web: www.bosmanggz.nl',9, array('left' => 450, 'justification' => 'right'));

               $pdf->ezText('Recept',12, array('left' => 30));
               $pdf->ezText(str_pad('Datum:', 26) . date('d-m-Y'), 12, array('left' => 30, 'spacing' => 2));
               $pdf->ezText(str_pad('Naam:', 25) . $p->patient->get_name_display(), 12, array('left' => 30, 'spacing' => 2));

               // prepare data for dutch usage
               $da = explode('/', $p->patient->date_of_birth);
               $dap = $da[1] . '-' . $da[0] . '-' .$da[2];
               $pdf->ezText(str_pad('Geboortedatum:', 20) . $dap, 12, array('left' => 30, 'spacing' => 2));

               $pdf->ezText('');
               $pdf->setLineStyle(0.2);
               $pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
               $pdf->ezText('',10);
       }

       function multiprint_footer(& $pdf, $p) {
               if($this->pconfig['use_signature'] && $this->use_signature_images) {
                       /*$sigfile = str_replace('{userid}', $this->providerid, $this->pconfig['signature']);
                       if (file_exists($sigfile)) {
                               $pdf->ezText("<i>Handtekening</i>: ",12);
                               // $pdf->ezImage($sigfile, "", "", "none", "left");
                               $pdf->ezImage($sigfile, "", "", "none", "center");
                               $pdf->ezText("Date: " . date('Y-m-d'), 12);
                               return;
                       }*/
               }
               $pdf->setLineStyle(0.2);
               $pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
               $pdf->ezText('');
               $pdf->ezText('<b>' . $p->provider->get_name_display() . '</b>, '.  $p->provider->get_specialty(), 10,  array('left' => 30));
               $pdf->addJpegFromFile('prescriptionbg.jpg', 10, 10);
       }

       function get_prescription_body_text($p) {
               $body = "\n\n" . 'R/ ' . $p->get_drug() . ' ' . $p->get_size() . ' ' . $p->get_unit_display();
               //if ($p->get_form()) $body .= ' [' . $p->form_array[$p->get_form()] . "]";
               $body .=  "\n" .
                       'No.' . $p->get_quantity() . "\n" .
                       'S. ' . $p->route_array[$p->get_route()] . ' ' . $p->interval_array[$p->get_interval()] .
                       ' ' . $p->get_dosage().  "\n";
               if ($p->get_refills() > 0) {
                       $body .= "\n" .  $p->get_refills() . ' x herhalen';
                       /*if ($p->get_per_refill()) {
                               $body .= " of quantity " . $p->get_per_refill();
                       }*/
                       $body .= "\n";
               }
               else {
                       $body .= "\n<b>Niet herhalen</b>\n";
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
               if($this->pconfig['shading']) {
                       $pdf->setColor(.9,.9,.9);
                       $pdf->filledRectangle($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin']-$pdf->ez['leftMargin'],$my_y - $pdf->y);
                       $pdf->setColor(0,0,0);
               }
               $pdf->ezSetY($my_y);
               $pdf->ezText($d,10);
               $pdf->ez['leftMargin'] = $GLOBALS['oer_config']['prescriptions']['left'];
               $pdf->ez['rightMargin'] = $GLOBALS['oer_config']['prescriptions']['right'];
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

               // $print_header = true;
               $on_this_page = 0;

               //print prescriptions body
               $this->_state = false; // Added by Rod - see Controller.class.php
               $ids = preg_split('/::/', substr($id,1,strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);
               foreach ($ids as $id) {
                       $p = new Prescription($id);
                       // if ($print_header == true) {
                       if ($on_this_page == 0) {
                               $this->multiprint_header($pdf, $p);
                       }
                       if (++$on_this_page > 3 || $p->provider->id != $this->providerid) {
                               $this->multiprint_footer($pdf, $p);
                               $pdf->ezNewPage();
                               $this->multiprint_header($pdf, $p);
                               // $print_header = false;
                               $on_this_page = 1;
                       }
                       $this->multiprint_body($pdf, $p);
               }

               $this->multiprint_footer($pdf, $p);

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
               // here the names are the same on the form!!
               case "Afdrukken":
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

               // Signature images are to be used only when faxing.
               if(!empty($toFile)) $this->use_signature_images = true;

               $this->multiprint_header($pdf, $p);
               $this->multiprint_body($pdf, $p);
               $this->multiprint_footer($pdf, $p);

               if(!empty($toFile)) {
                       $toFile = $pdf->ezOutput();
               }
               else {
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
