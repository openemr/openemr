<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/Prescription.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/Provider.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/RXList.class.php");
require_once($GLOBALS['fileroot'] . "/library/registry.inc");
require_once($GLOBALS['fileroot'] . "/library/amc.php");

class C_Prescription extends Controller {

	var $template_mod;
	var $pconfig;
	var $providerid = 0;
	var $is_faxing = false;
	var $is_print_to_fax = false;

	function C_Prescription($template_mod = "general") {
		parent::Controller();

		$this->template_mod = $template_mod;
		$this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
		$this->assign("TOP_ACTION", $GLOBALS['webroot']."/controller.php?" . "prescription" . "&");
		$this->assign("STYLE", $GLOBALS['style']);
		$this->assign("WEIGHT_LOSS_CLINIC", $GLOBALS['weight_loss_clinic']);
		$this->assign("SIMPLIFIED_PRESCRIPTIONS", $GLOBALS['simplified_prescriptions']);
		$this->pconfig = $GLOBALS['oer_config']['prescriptions'];
	    $this->assign("CSS_HEADER",  $GLOBALS['css_header'] );
	    $this->assign("WEB_ROOT", $GLOBALS['webroot'] );

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

                // flag to indicate the CAMOS form is regsitered and active
                $this->assign("CAMOS_FORM", isRegistered("CAMOS"));

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

	function fragment_action($id,$sort = "") {
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
		$this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_fragment.html");
	}

	function lookup_action() {
		$this->do_lookup();
		$this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_lookup.html");
	}

	function edit_action_process() {
		if ($_POST['process'] != "true")
			return;
		//print_r($_POST);

    // Stupid Smarty code treats empty values as not specified values.
    // Since active is a checkbox, represent the unchecked state as -1.
    if (empty($_POST['active'])) $_POST['active'] = '-1';

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

    // Set the AMC reporting flag (to record percentage of prescriptions that
    // are set as e-prescriptions)
    if (!(empty($_POST['escribe_flag']))) {
      // add the e-prescribe flag
      processAmcCall('e_prescribe_amc', true, 'add', $this->prescriptions[0]->get_patient_id(), 'prescriptions', $this->prescriptions[0]->id); 
    }
    else {
      // remove the e-prescribe flag
      processAmcCall('e_prescribe_amc', true, 'remove', $this->prescriptions[0]->get_patient_id(), 'prescriptions', $this->prescriptions[0]->id);
    }

    if ($this->prescriptions[0]->get_active() > 0) {
      return $this->send_action($this->prescriptions[0]->id);
    }
    $this->list_action($this->prescriptions[0]->get_patient_id());
    exit;
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

	function multiprintfax_header(& $pdf, $p) {
		return $this->multiprint_header( $pdf, $p );
	}

	function multiprint_header(& $pdf, $p) {
		$this->providerid = $p->provider->id;
		//print header
		$pdf->ezImage($GLOBALS['oer_config']['prescriptions']['logo'],'','50','','center','');
		$pdf->ezColumnsStart(array('num'=>2, 'gap'=>10));
		$res = sqlQuery("SELECT concat('<b>',f.name,'</b>\n',f.street,'\n',f.city,', ',f.state,' ',f.postal_code,'\nTel:',f.phone,if(f.fax != '',concat('\nFax: ',f.fax),'')) addr FROM users JOIN facility AS f ON f.name = users.facility where users.id ='" .
			mysql_real_escape_string($p->provider->id) . "'");
		$pdf->ezText($res['addr'],12);
		$my_y = $pdf->y;
		$pdf->ezNewPage();
		$pdf->ezText('<b>' . $p->provider->get_name_display() . '</b>',12);
    // A client had a bad experience with a patient misusing a DEA number, so
    // now the doctors write those in on printed prescriptions and only when
    // necessary.  If you need to change this back, then please make it a
    // configurable option.  Faxed prescriptions were not changed.  -- Rod
    // Now it is configureable. Change value in
    //     Administration->Globals->Rx
    if ($GLOBALS['rx_enable_DEA']) {
        if ($this->is_faxing || $GLOBALS['rx_show_DEA']) {
            $pdf->ezText('<b>' . xl('DEA') . ':</b>' . $p->provider->federal_drug_id, 12);
        }
        else {
            $pdf->ezText('<b>' . xl('DEA') . ':</b> ________________________', 12);
        }
    }

    if ($GLOBALS['rx_enable_NPI']) {
	if ($this->is_faxing || $GLOBALS['rx_show_NPI']) {
            $pdf->ezText('<b>' . xl('NPI') . ':</b>' . $p->provider->npi, 12);
        }
	else {
	    $pdf->ezText('<b>' . xl('NPI') . ':</b> _________________________', 12);
        }
    }
    if ($GLOBALS['rx_enable_SLN']) {
        if ($this->is_faxing || $GLOBALS['rx_show_SLN']) {
            $pdf->ezText('<b>' . xl('State Lic. #') . ':</b>' . $p->provider->state_license_number, 12);
        }
	else {
	    $pdf->ezText('<b>' . xl('State Lic. #') . ':</b> ___________________', 12);
        }
    }
		$pdf->ezColumnsStop();
		if ($my_y < $pdf->y){
			$pdf->ezSetY($my_y);
		}
		$pdf->ezText('',10);
		$pdf->setLineStyle(1);
		$pdf->ezColumnsStart(array('num'=>2));
		$pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
		$pdf->ezText('<b>' . xl('Patient Name & Address') . '</b>',6);
		$pdf->ezText($p->patient->get_name_display(),10);
		$res = sqlQuery("SELECT  concat(street,'\n',city,', ',state,' ',postal_code,'\n',if(phone_home!='',phone_home,if(phone_cell!='',phone_cell,if(phone_biz!='',phone_biz,'')))) addr from patient_data where pid =". mysql_real_escape_string ($p->patient->id));
		$pdf->ezText($res['addr']);
		$my_y = $pdf->y;
		$pdf->ezNewPage();
		$pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
		$pdf->ezText('<b>' . xl('Date of Birth') . '</b>',6);
		$pdf->ezText($p->patient->date_of_birth,10);
		$pdf->ezText('');
		$pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
		$pdf->ezText('<b>' . xl('Medical Record #') . '</b>',6);
		$pdf->ezText(str_pad($p->patient->get_pubpid(), 10, "0", STR_PAD_LEFT),10);
		$pdf->ezColumnsStop();
		if ($my_y < $pdf->y){
			$pdf->ezSetY($my_y);
		}
		$pdf->ezText('');
		$pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
		$pdf->ezText('<b>' . xl('Prescriptions') . '</b>',6);
		$pdf->ezText('',10);
	}

        function multiprintcss_header($p) {
                echo("<div class='paddingdiv'>\n");
                $this->providerid = $p->provider->id;
	        echo ("<table cellspacing='0' cellpadding='0' width='100%'>\n");
	        echo ("<tr>\n");
	        echo ("<td></td>\n");
	        echo ("<td>\n");
                echo ("<img WIDTH='68pt' src='./interface/pic/" . $GLOBALS['oer_config']['prescriptions']['logo_pic'] . "' />");
                echo ("</td>\n");
	        echo ("</tr>\n");
	        echo ("<tr>\n");
	        echo ("<td>\n");
	        $res = sqlQuery("SELECT concat('<b>',f.name,'</b>\n',f.street,'\n',f.city,', ',f.state,' ',f.postal_code,'\nTel:',f.phone,if(f.fax != '',concat('\nFax: ',f.fax),'')) addr FROM users JOIN facility AS f ON f.name = users.facility where users.id ='" . mysql_real_escape_string($p->provider->id) . "'");
	        $patterns = array ('/\n/','/Tel:/','/Fax:/');
	        $replace = array ('<br>', xl('Tel').':', xl('Fax').':');
	        $res = preg_replace($patterns, $replace, $res);
                echo ('<span class="large">' . $res['addr'] . '</span>');
	        echo ("</td>\n");
	        echo ("<td>\n");
                echo ('<b><span class="large">' .  $p->provider->get_name_display() . '</span></b>'. '<br>');
                
                if ($GLOBALS['rx_enable_DEA']) {
                    if ($GLOBALS['rx_show_DEA']) {
                        echo ('<span class="large"><b>' . xl('DEA') . ':</b>' . $p->provider->federal_drug_id . '</span><br>');
                    }
                    else {
                        echo ('<b><span class="large">' . xl('DEA') . ':</span></b> ________________________<br>' );
                    }
                }
                if ($GLOBALS['rx_enable_NPI']) {
                    if ($GLOBALS['rx_show_NPI']) {
                        echo ('<span class="large"><b>' . xl('NPI') . ':</b>' . $p->provider->npi . '</span><br>');
                    }
                    else {
                        echo ('<b><span class="large">' . xl('NPI') . ':</span></b> ________________________<br>');
                    }
               }
               if ($GLOBALS['rx_enable_SLN']) {
                   if ($GLOBALS['rx_show_SLN']) {
                       echo ('<span class="large"><b>' . xl('State Lic. #') . ':</b>' . $p->provider->state_license_number . '</span><br>');
                   }
                   else {
                       echo ('<b><span class="large">' . xl('State Lic. #') . ':</span></b> ________________________<br>');
                   }
               }
	        echo ("</td>\n");
	        echo ("</tr>\n");
	        echo ("<tr>\n");
	        echo ("<td rowspan='2' class='bordered'>\n");
                echo ('<b><span class="small">' . xl('Patient Name & Address') . '</span></b>'. '<br>');
                echo ($p->patient->get_name_display() . '<br>');
                $res = sqlQuery("SELECT  concat(street,'\n',city,', ',state,' ',postal_code,'\n',if(phone_home!='',phone_home,if(phone_cell!='',phone_cell,if(phone_biz!='',phone_biz,'')))) addr from patient_data where pid =". mysql_real_escape_string ($p->patient->id));
                $patterns = array ('/\n/');
	        $replace = array ('<br>');
	        $res = preg_replace($patterns, $replace, $res);
                echo ($res['addr']);
	        echo ("</td>\n");
	        echo ("<td class='bordered'>\n");
                echo ('<b><span class="small">' . xl('Date of Birth') . '</span></b>' . '<br>');
                echo ($p->patient->date_of_birth );
	        echo ("</td>\n");
	        echo ("</tr>\n");
	        echo ("<tr>\n");
	        echo ("<td class='bordered'>\n");
                echo ('<b><span class="small">' . xl('Medical Record #') . '</span></b>' . '<br>');
                echo (str_pad($p->patient->get_pubpid(), 10, "0", STR_PAD_LEFT));
	        echo ("</td>\n");
	        echo ("</tr>\n");
	        echo ("<tr>\n");
	        echo ("<td colspan='2' class='bordered'>\n");
                echo ('<b><span class="small">' . xl('Prescriptions') . '</span></b>');
	        echo ("</td>\n");
	        echo ("</tr>\n");
	        echo ("</table>\n");
        }

        function multiprintcss_preheader() {
	        // this sets styling and other header information of the multiprint css sheet
                echo ("<html>\n");
                echo ("<head>\n");
                echo ("<style>\n");
	        echo ("div {\n");
	        echo (" padding: 0;\n");
	        echo (" margin: 0;\n");
	        echo ("}\n");
                echo ("body {\n");
                echo (" font-family: sans-serif;\n");
                echo (" font-weight: normal;\n");
                echo (" font-size: 10pt;\n");
                echo (" background: white;\n");
                echo (" color: black;\n");
                echo ("}\n");
	        echo ("span.large {\n");
	        echo (" font-size: 12pt;\n");
	        echo ("}\n");
	        echo ("span.small {\n");
	        echo (" font-size: 6pt;\n");
	        echo ("}\n");
	        echo ("td {\n");
	        echo (" vertical-align: top;\n");
	        echo (" width: 50%;\n");
	        echo (" font-size: 10pt;\n");
	        echo (" padding-bottom: 8pt;\n");
	        echo ("}\n");
	        echo ("td.bordered {\n");
	        echo (" border-top:1pt solid black;\n");
	        echo ("}\n");
                echo ("div.paddingdiv {\n");
                echo (" width: 524pt;\n");
	        echo (" height: 668pt;\n");
                echo ("}\n");
                echo ("div.scriptdiv {\n");
	        echo (" padding-top: 12pt;\n");
	        echo (" padding-bottom: 22pt;\n");
	        echo (" padding-left: 35pt;\n");
	        echo (" border-bottom:1pt solid black;\n");
	        echo ("}\n");
	        echo ("div.signdiv {\n");
	        echo (" margin-top: 40pt;\n");
                echo (" font-size: 12pt;\n");
	        echo ("}\n");
                echo ("</style>\n");

                echo ("<title>" . xl('Prescription') . "</title>\n");
                echo ("</head>\n");
                echo ("<body>\n");
        }

	function multiprintfax_footer( & $pdf ) {
		return $this->multiprint_footer( $pdf );
	}

	function multiprint_footer(& $pdf) {
		if($this->pconfig['use_signature'] && ( $this->is_faxing || $this->is_print_to_fax ) ) {
			$sigfile = str_replace('{userid}', $_SESSION{"authUser"}, $this->pconfig['signature']);
			if (file_exists($sigfile)) {
				$pdf->ezText( xl('Signature') . ": ",12);
				// $pdf->ezImage($sigfile, "", "", "none", "left");
				$pdf->ezImage($sigfile, "", "", "none", "center");
				$pdf->ezText( xl('Date') . ": " . date('Y-m-d'), 12);
				if ( $this->is_print_to_fax ) {
					$pdf->ezText(xl('Please do not accept this prescription unless it was received via facsimile.'));
				}

				$addenumFile = $this->pconfig['addendum_file'];
				if ( file_exists( $addenumFile ) ) {
					$pdf->ezText('');
					$f = fopen($addenumFile, "r");
					while ( $line = fgets($f, 1000) ) {
						$pdf->ezText(rtrim($line));
					}
				}

				return;
			}
		}
		$pdf->ezText("\n\n\n\n" . xl('Signature') . ":________________________________\n" . xl('Date') . ": " . date('Y-m-d'),12);
	}

        function multiprintcss_footer() {
	        echo ("<div class='signdiv'>\n");
                echo (xl('Signature') . ":________________________________<br>");
                echo (xl('Date') . ": " . date('Y-m-d'));
	        echo ("</div>\n");
                echo ("</div>\n");
        }

        function multiprintcss_postfooter() {
                echo("<script language='JavaScript'>\n");
                echo("window.print();\n");
                echo("</script>\n");
                echo("</body>\n");
                echo("</html>\n");
        }

	function get_prescription_body_text($p) {
		$body = '<b>' . xl('Rx') . ': ' . $p->get_drug() . ' ' . $p->get_size() . ' ' . $p->get_unit_display();
		if ($p->get_form()) $body .= ' [' . $p->form_array[$p->get_form()] . "]";
		$body .= "</b>     <i>" .
			$p->substitute_array[$p->get_substitute()] . "</i>\n" .
			'<b>' . xl('Disp #') . ':</b> <u>' . $p->get_quantity() . "</u>\n" .
			'<b>' . xl('Sig') . ':</b> ' . $p->get_dosage() . ' ' . $p->form_array[$p->get_form()] . ' ' .
			$p->route_array[$p->get_route()] . ' ' . $p->interval_array[$p->get_interval()] . "\n";
		if ($p->get_refills() > 0) {
			$body .= "\n<b>" . xl('Refills') . ":</b> <u>" .  $p->get_refills();
			if ($p->get_per_refill()) {
				$body .= " " . xl('of quantity') . " " . $p->get_per_refill();
			}
			$body .= "</u>\n";
		}
		else {
			$body .= "\n<b>" . xl('Refills') . ":</b> <u>0 (" . xl('Zero') . ")</u>\n";
		}
		$note = $p->get_note();
		if ($note != '') {
			$body .= "\n$note\n";
		}
		return $body;
	}

	function multiprintfax_body(& $pdf, $p){
		return $this->multiprint_body( $pdf, $p );
	}

	function multiprint_body(& $pdf, $p){
		$pdf->ez['leftMargin'] += $pdf->ez['leftMargin'];
		$pdf->ez['rightMargin'] += $pdf->ez['rightMargin'];
		$d = $this->get_prescription_body_text($p);
		if ( $pdf->ezText($d,10,array(),1) ) {
			$pdf->ez['leftMargin'] -= $pdf->ez['leftMargin'];
			$pdf->ez['rightMargin'] -= $pdf->ez['rightMargin'];
			$this->multiprint_footer($pdf);
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
		$pdf->ez['leftMargin'] = $GLOBALS['rx_left_margin'];
		$pdf->ez['rightMargin'] = $GLOBALS['rx_right_margin'];
		$pdf->ezText('');
		$pdf->line($pdf->ez['leftMargin'],$pdf->y,$pdf->ez['pageWidth']-$pdf->ez['rightMargin'],$pdf->y);
		$pdf->ezText('');
	}

        function multiprintcss_body($p){
                $d = $this->get_prescription_body_text($p);
                $patterns = array ('/\n/','/     /');
	        $replace = array ('<br>','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
	        $d = preg_replace($patterns, $replace, $d);
                echo ("<div class='scriptdiv'>\n" . $d . "</div>\n");
        }

	function multiprintfax_action($id = "") {
		$this->is_print_to_fax=true;
		return $this->multiprint_action( $id );
	}

	function multiprint_action($id = "") {
		$_POST['process'] = "true";
		if(empty($id)) {
			$this->function_argument_error();
		}
		require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
		$pdf =& new Cezpdf($GLOBALS['rx_paper_size']);
		$pdf->ezSetMargins($GLOBALS['rx_top_margin']
			,$GLOBALS['rx_bottom_margin']
			,$GLOBALS['rx_left_margin']
			,$GLOBALS['rx_right_margin']
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
				$this->multiprint_footer($pdf);
				$pdf->ezNewPage();
				$this->multiprint_header($pdf, $p);
				// $print_header = false;
				$on_this_page = 1;
			}
			$this->multiprint_body($pdf, $p);
		}

		$this->multiprint_footer($pdf);

		$pdf->ezStream();
		return;
	}

        function multiprintcss_action($id = "") {
                $_POST['process'] = "true";
                if(empty($id)) {
                        $this->function_argument_error();
                }

	        $this->multiprintcss_preheader();

                $this->_state = false; // Added by Rod - see Controller.class.php
                $ids = preg_split('/::/', substr($id,1,strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);

                $on_this_page = 0;
                foreach ($ids as $id) {
                        $p = new Prescription($id);
                        if ($on_this_page == 0) {
                                $this->multiprintcss_header($p);
                        }
                        if (++$on_this_page > 3 || $p->provider->id != $this->providerid) {
                                $this->multiprintcss_footer();
                                $this->multiprintcss_header($p);
                                $on_this_page = 1;
                        }
                        $this->multiprintcss_body($p);
                }
                $this->multiprintcss_footer();
                $this->multiprintcss_postfooter();
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

		case (xl("Print")." (".xl("PDF").")"):
				// The following statement added by Rod.
				// Looking at Controller.class.php, it appears that _state is set to false
				// to indicate that no further HTML is to be generated.
				$this->_state = false; // Added by Rod - see Controller.class.php
				return $this->_print_prescription($p, $dummy);
				break;
		case (xl("Print")." (".xl("HTML").")"):
                                $this->_state = false;
		                return $this->_print_prescription_css($p, $dummy);
		                break;
		case xl("Print To Fax"):
				$this->_state = false;
				$this->is_print_to_fax = true;
				return $this->_print_prescription($p, $dummy);
				break;
		case xl("Email"):
				return $this->_email_prescription($p,$_POST['email_to']);
				break;
		case xl("Fax"):
				//this is intended to be the hook for the hylafax code we already have that hasn't worked its way into the tree yet.
				//$this->assign("process_result","No fax server is currently setup.");
				return $this->_fax_prescription($p,$_POST['fax_to']);
				break;
		case xl("Auto Send"):
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
		$pdf =& new Cezpdf($GLOBALS['rx_paper_size']);
		$pdf->ezSetMargins($GLOBALS['rx_top_margin']
			,$GLOBALS['rx_bottom_margin']
			,$GLOBALS['rx_left_margin']
			,$GLOBALS['rx_right_margin']
		);

		$pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");

		// Signature images are to be used only when faxing.
		if(!empty($toFile)) $this->is_faxing = true;

		$this->multiprint_header($pdf, $p);
		$this->multiprint_body($pdf, $p);
		$this->multiprint_footer($pdf);

		if(!empty($toFile)) {
			$toFile = $pdf->ezOutput();
		}
		else {
			$pdf->ezStream();
			// $pdf->ezStream(array('compress' => 0)); // for testing with uncompressed output
		}
		return;
	}

        function _print_prescription_css($p, & $toFile) {

                $this->multiprintcss_preheader();
                $this->multiprintcss_header($p);
                $this->multiprintcss_body($p);
                $this->multiprintcss_footer();
	        $this->multiprintcss_postfooter();

        }

	function _print_prescription_old($p, & $toFile) {
		require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
		$pdf =& new Cezpdf($GLOBALS['rx_paper_size']);
		$pdf->ezSetMargins($GLOBALS['rx_top_margin']
                      ,$GLOBALS['rx_bottom_margin']
		                  ,$GLOBALS['rx_left_margin']
                      ,$GLOBALS['rx_right_margin']
                      );
		$pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");
		if(!empty($this->pconfig['logo'])) {
			$pdf->ezImage($this->pconfig['logo'],"","","none","left");
		}
		$pdf->ezText($p->get_prescription_display(),10);
		if($this->pconfig['use_signature']) {
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
		if ($_POST['process'] != "true") {
                    // don't do a lookup
		    $this->assign("drug", $_GET['drug']);
                    return;
                }

                // process the lookup
		$this->assign("drug", $_POST['drug']);
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
        $fileName = $GLOBALS['OE_SITE_DIR'] . "/documents/" . $p->get_id() .
          $p->get_patient_id() . "_fax_.pdf";
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
