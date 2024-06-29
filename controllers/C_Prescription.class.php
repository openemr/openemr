<?php

/**
 * C_Prescription class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/registry.inc.php");
require_once($GLOBALS['fileroot'] . "/library/amc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\FormActionBarSettings;
use OpenEMR\Common\Http\oeHttp;
use OpenEMR\Rx\RxList;
use PHPMailer\PHPMailer\PHPMailer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Twig\TwigContainer;

class C_Prescription extends Controller
{
    var $template_mod;
    var $pconfig;
    var $providerid = 0;
    var $is_faxing = false;
    var $is_print_to_fax = false;
    var $RxList;
    var $prescriptions;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->template_mod = $template_mod;
        $this->assign("TOP_ACTION", $GLOBALS['webroot'] . "/controller.php?" . "prescription" . "&");
        $this->assign("STYLE", $GLOBALS['style']);
        $this->assign("WEIGHT_LOSS_CLINIC", $GLOBALS['weight_loss_clinic']);
        $this->assign("SIMPLIFIED_PRESCRIPTIONS", $GLOBALS['simplified_prescriptions']);
        $this->pconfig = $GLOBALS['oer_config']['prescriptions'];
        $this->RxList = new RxList();
        // test if rxnorm available for lookups.
        $rxn = sqlQuery("SELECT table_name FROM information_schema.tables WHERE table_name = 'RXNCONSO' OR table_name = 'rxconso'");
        $rxcui = sqlQuery("SELECT ct_id FROM `code_types` WHERE `ct_key` = ? AND `ct_active` = 1", array('RXCUI'));
        $this->assign("RXNORMS_AVAILABLE", !empty($rxn));
        $this->assign("RXCUI_AVAILABLE", !empty($rxcui));
        // Assign the CSRF_TOKEN_FORM
        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());

        if ($GLOBALS['inhouse_pharmacy']) {
            // Make an array of drug IDs and selectors for the template.
            $drug_array_values = array(0);
            $drug_array_output = array("-- " . xl('or select from inventory') . " --");
            $drug_attributes = '';

            // $res = sqlStatement("SELECT * FROM drugs ORDER BY selector");

            $res = sqlStatement("SELECT d.name, d.ndc_number, d.form, d.size, " .
                "d.unit, d.route, d.substitute, t.drug_id, t.selector, t.dosage, " .
                "t.period, t.quantity, t.refills, d.drug_code " .
                "FROM drug_templates AS t, drugs AS d WHERE " .
                "d.drug_id = t.drug_id ORDER BY t.selector");

            while ($row = sqlFetchArray($res)) {
                $tmp_output = $row['selector'];
                if ($row['ndc_number']) {
                    $tmp_output .= ' [' . $row['ndc_number'] . ']';
                }

                $drug_array_values[] = $row['drug_id'];
                $drug_array_output[] = $tmp_output;
                if ($drug_attributes) {
                    $drug_attributes .= ',';
                }

                $drug_attributes .=    "["  .
                    js_escape($row['name'])       . ","  . //  0
                    js_escape($row['form'])       . ","  . //  1
                    js_escape($row['dosage'])     . "," . //  2
                    js_escape($row['size'])       . ","  . //  3
                    js_escape($row['unit'])       . ","   . //  4
                    js_escape($row['route'])      . ","   . //  5
                    js_escape($row['period'])     . ","   . //  6
                    js_escape($row['substitute']) . ","   . //  7
                    js_escape($row['quantity'])   . ","   . //  8
                    js_escape($row['refills'])    . ","   . //  9
                    js_escape($row['quantity'])   . ","   . //  10 quantity per_refill
                    js_escape($row['drug_code'])  . "]";    //  11 rxnorm drug code
            }

            $this->assign("DRUG_ARRAY_VALUES", $drug_array_values);
            $this->assign("DRUG_ARRAY_OUTPUT", $drug_array_output);
            $this->assign("DRUG_ATTRIBUTES", $drug_attributes);
        }
    }

    function default_action()
    {
        $prescription = $this->prescriptions[0];
        $this->assign("prescription", $prescription);
        $vars = $this->getTemplateVars();
        $vars['enable_amc_prompting'] = $GLOBALS['enable_amc_prompting'] ?? false;
        $vars['weno_rx_enable'] = $GLOBALS['weno_rx_enable'] ?? false;
        $vars['topActionBarDisplay'] = FormActionBarSettings::shouldDisplayTopActionBar();
        $vars['bottomActionBarDisplay'] = FormActionBarSettings::shouldDisplayBottomActionBar();

        if ($GLOBALS['enable_amc_prompting']) {
            $vars['amcCollectReturnFlag'] = amcCollect('e_prescribe_amc', $prescription->patient->id, 'prescriptions', $prescription->id);
            $vars['amcCollectReturnFormulary'] = amcCollect('e_prescribe_chk_formulary_amc', $prescription->patient->id, 'prescriptions', $prescription->id);
            $vars['amcCollectReturnControlledSubstances'] = amcCollect('e_prescribe_cont_subst_amc', $prescription->patient->id, 'prescriptions', $prescription->id);
        }
        $twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
        echo $twig->render("prescription/" . $this->template_mod . "_edit.html.twig", $vars);
    }

    function edit_action($id = "", $patient_id = "", $p_obj = null)
    {

        if ($p_obj != null && get_class($p_obj) == "prescription") {
            $this->prescriptions[0] = $p_obj;
        } elseif (empty($this->prescriptions[0]) || !is_object($this->prescriptions[0]) || (get_class($this->prescriptions[0]) != "prescription")) {
            $this->prescriptions[0] = new Prescription($id);
        }

        if (!empty($patient_id)) {
            $this->prescriptions[0]->set_patient_id($patient_id);
        }

        $this->assign("GBL_CURRENCY_SYMBOL", $GLOBALS['gbl_currency_symbol']);

        // If quantity to dispense is not already set from a POST, set its
        // default value.
        if (! $this->getTemplateVars('DISP_QUANTITY')) {
            $this->assign('DISP_QUANTITY', $this->prescriptions[0]->quantity);
        }

        $this->default_action();
    }

    function list_action($id, $sort = "")
    {
        if (empty($id)) {
            $this->function_argument_error();
            exit;
        }

        if (!empty($sort)) {
            $this->assign("prescriptions", Prescription::prescriptions_factory($id, $sort));
        } else {
            $this->assign("prescriptions", Prescription::prescriptions_factory($id));
        }

        // Collect interactions if the global is turned on
        if ($GLOBALS['rx_show_drug_drug']) {
            $interaction = "";
            // Ensure RxNorm installed
            $rxn = sqlQuery("SELECT table_name FROM information_schema.tables WHERE table_name = 'RXNCONSO' OR table_name = 'rxconso'");
            if ($rxn == false) {
                $interaction = xlt("Could not find RxNorm Table! Please install.");
            } elseif ($rxn == true) {
                //   Grab medication list from prescriptions list and load into array
                $pid = $GLOBALS['pid'];
                $medList = sqlStatement("SELECT drug FROM prescriptions WHERE active = 1 AND patient_id = ?", array($pid));
                $nameList = array();
                while ($name = sqlFetchArray($medList)) {
                    $drug = explode(" ", $name['drug']);
                    $rXn = sqlQuery("SELECT `rxcui` FROM `" . mitigateSqlTableUpperCase('RXNCONSO') . "` WHERE `str` LIKE ?", array("%" . $drug[0] . "%"));
                    $nameList[] = $rXn['rxcui'];
                }
                if (count($nameList) < 2) {
                    $interaction = xlt("Need more than one drug.");
                } else {
                    // If there are drugs to compare, collect the data
                    // (array_filter removes empty items)
                    $rxcui_list = implode("+", array_filter($nameList));
                    // Unable to urlencode the $rxcui, since this breaks the + items on call to rxnav.nlm.nih.gov; so need to include it in the path
                    $response = oeHttp::get('https://rxnav.nlm.nih.gov/REST/interaction/list.json?rxcuis=' . $rxcui_list);
                    $data = $response->body();
                    $json = json_decode($data, true);
                    if (!empty($json['fullInteractionTypeGroup'][0]['fullInteractionType'])) {
                        foreach ($json['fullInteractionTypeGroup'][0]['fullInteractionType'] as $item) {
                            $interaction .= '<div class="alert alert-danger">';
                            $interaction .= xlt('Comment') . ":" . text($item['comment']) . "<br />";
                            $interaction .= xlt('Drug1 Name{{Drug1 Interaction}}') . ":" . text($item['minConcept'][0]['name']) . "<br />";
                            $interaction .= xlt('Drug2 Name{{Drug2 Interaction}}') . ":" . text($item['minConcept'][1]['name']) . "<br />";
                            $interaction .= xlt('Severity') . ":" . text($item['interactionPair'][0]['severity']) . "<br />";
                            $interaction .= xlt('Description') . ":" . text($item['interactionPair'][0]['description']);
                            $interaction .= '</div>';
                        }
                    } else {
                        $interaction = xlt('No interactions found');
                    }
                }
            }
            $this->assign("INTERACTION", $interaction);
        }

        // flag to indicate the CAMOS form is regsitered and active
        $this->assign("CAMOS_FORM", isRegistered("CAMOS"));

        $vars = $this->getTemplateVars();
        $vars['pid'] = $id;
        $vars['rx_send_email'] = $GLOBALS['rx_send_email'] ?? false;
        $vars['rx_show_drug_drug'] = $GLOBALS['rx_show_drug_drug'] ?? false;
        $vars['rx_zend_pdf_template'] = $GLOBALS['rx_zend_pdf_template'] ?? false;
        $vars['baseModDir'] = $GLOBALS['baseModDir'] ?? '';
        $vars['zendModDir'] = $GLOBALS['zendModDir'] ?? '';
        $vars['printm'] = null; // TODO: figure out where printm is used or defined
        $vars['rx_zend_pdf_action'] = $GLOBALS['rx_zend_pdf_action'] ?? '';
        $vars['rx_zend_html_template'] = $GLOBALS['rx_zend_html_template'] ?? '';
        $vars['rx_zend_html_action'] = $GLOBALS['rx_zend_pdf_action'] ?? '';
        $vars['rx_use_fax_template'] = $GLOBALS['rx_use_fax_template'] ?? '';
        $vars['rx_send_email'] = $GLOBALS['rx_send_email'] ?? false;
        $vars['faxSignatureMissing'] = false;
        if (!($this->pconfig['use_signature'] && $this->current_user_has_signature())) {
            $vars['faxSignatureMissing'] = true;
        }
        $twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
        echo $twig->render("prescription/" . $this->template_mod . "_list.html.twig", $vars);
    }

    function block_action($id, $sort = "")
    {
        if (empty($id)) {
            $this->function_argument_error();
            exit;
        }

        if (!empty($sort)) {
            $this->assign("prescriptions", Prescription::prescriptions_factory($id, $sort));
        } else {
            $this->assign("prescriptions", Prescription::prescriptions_factory($id));
        }

        //print_r(Prescription::prescriptions_factory($id));
        $this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_block.html");
    }

    /**
     * TODO: remove this function in a future expansion.
     * @deprecated As far as we can see this function isn't used
     * @param $id
     * @param $sort
     * @return void
     * @throws SmartyException
     */
    function fragment_action($id, $sort = "")
    {
        if (empty($id)) {
            $this->function_argument_error();
            exit;
        }

        if (!empty($sort)) {
            $this->assign("prescriptions", Prescription::prescriptions_factory($id, $sort));
        } else {
            $this->assign("prescriptions", Prescription::prescriptions_factory($id));
        }

        //print_r(Prescription::prescriptions_factory($id));
        $this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_fragment.html");
    }

    function lookup_action()
    {
        $this->assign("FORM_ACTION", $GLOBALS['webroot'] . "/controller.php?" . attr($_SERVER['QUERY_STRING']));
        $this->do_lookup();
        $this->display($GLOBALS['template_dir'] . "prescription/" . $this->template_mod . "_lookup.html");
    }

    function edit_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        //print_r($_POST);

    // Stupid Smarty code treats empty values as not specified values.
    // Since active is a checkbox, represent the unchecked state as -1.
        if (empty($_POST['active'])) {
            $_POST['active'] = '-1';
        }
        if (!empty($_POST['start_date'])) {
            $_POST['start_date'] = DateToYYYYMMDD($_POST['start_date']);
        }

        $this->prescriptions[0] = new Prescription($_POST['id']);
        parent::populate_object($this->prescriptions[0]);
        //echo $this->prescriptions[0]->toString(true);
        $this->prescriptions[0]->persist();
        $_POST['process'] = "";

        $this->assign("GBL_CURRENCY_SYMBOL", $GLOBALS['gbl_currency_symbol']);

        // If the "Prescribe and Dispense" button was clicked, then
        // redisplay as in edit_action() but also replicate the fee and
        // include a piece of javascript to call dispense().
        //
        if (!empty($_POST['disp_button'])) {
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
        } else {
              // remove the e-prescribe flag
              processAmcCall('e_prescribe_amc', true, 'remove', $this->prescriptions[0]->get_patient_id(), 'prescriptions', $this->prescriptions[0]->id);
        }

    // Set the AMC reporting flag (to record prescriptions that checked drug formulary)
        if (!(empty($_POST['checked_formulary_flag']))) {
              // add the e-prescribe flag
              processAmcCall('e_prescribe_chk_formulary_amc', true, 'add', $this->prescriptions[0]->get_patient_id(), 'prescriptions', $this->prescriptions[0]->id);
        } else {
              // remove the e-prescribe flag
              processAmcCall('e_prescribe_chk_formulary_amc', true, 'remove', $this->prescriptions[0]->get_patient_id(), 'prescriptions', $this->prescriptions[0]->id);
        }

    // Set the AMC reporting flag (to record prescriptions that are controlled substances)
        if (!(empty($_POST['controlled_substance_flag']))) {
              // add the e-prescribe flag
              processAmcCall('e_prescribe_cont_subst_amc', true, 'add', $this->prescriptions[0]->get_patient_id(), 'prescriptions', $this->prescriptions[0]->id);
        } else {
              // remove the e-prescribe flag
              processAmcCall('e_prescribe_cont_subst_amc', true, 'remove', $this->prescriptions[0]->get_patient_id(), 'prescriptions', $this->prescriptions[0]->id);
        }

        $this->list_action($this->prescriptions[0]->get_patient_id());
        exit;
    }

    function multiprintfax_header(&$pdf, $p)
    {
        return $this->multiprint_header($pdf, $p);
    }

    function multiprint_header(&$pdf, $p)
    {
        $this->providerid = $p->provider->id;
        //print header
        $pdf->ezImage($GLOBALS['oer_config']['prescriptions']['logo'], null, '50', '', 'center', '');
        $pdf->ezColumnsStart(array('num' => 2, 'gap' => 10));
        $res = sqlQuery("SELECT concat('<b>',f.name,'</b>\n',f.street,'\n',f.city,', ',f.state,' ',f.postal_code,'\nTel:',f.phone,if(f.fax != '',concat('\nFax: ',f.fax),'')) addr FROM users JOIN facility AS f ON f.name = users.facility where users.id ='" .
            add_escape_custom($p->provider->id) . "'");
        $pdf->ezText($res['addr'] ?? '', 12);
        $my_y = $pdf->y;
        $pdf->ezNewPage();
        $pdf->ezText('<b>' . $p->provider->get_name_display() . '</b>', 12);
    // A client had a bad experience with a patient misusing a DEA number, so
    // now the doctors write those in on printed prescriptions and only when
    // necessary.  If you need to change this back, then please make it a
    // configurable option.  Faxed prescriptions were not changed.  -- Rod
    // Now it is configureable. Change value in
    //     Administration->Globals->Rx
        if ($GLOBALS['rx_enable_DEA']) {
            if ($this->is_faxing || $GLOBALS['rx_show_DEA']) {
                $pdf->ezText('<b>' . xl('DEA') . ':</b>' . $p->provider->federal_drug_id, 12);
            } else {
                $pdf->ezText('<b>' . xl('DEA') . ':</b> ________________________', 12);
            }
        }

        if ($GLOBALS['rx_enable_NPI']) {
            if ($this->is_faxing || $GLOBALS['rx_show_NPI']) {
                    $pdf->ezText('<b>' . xl('NPI') . ':</b>' . $p->provider->npi, 12);
            } else {
                $pdf->ezText('<b>' . xl('NPI') . ':</b> _________________________', 12);
            }
        }

        if ($GLOBALS['rx_enable_SLN']) {
            if ($this->is_faxing || $GLOBALS['rx_show_SLN']) {
                $pdf->ezText('<b>' . xl('State Lic. #') . ':</b>' . $p->provider->state_license_number, 12);
            } else {
                $pdf->ezText('<b>' . xl('State Lic. #') . ':</b> ___________________', 12);
            }
        }

        $pdf->ezColumnsStop();
        if ($my_y < $pdf->y) {
            $pdf->ezSetY($my_y);
        }

        $pdf->ezText('', 10);
        $pdf->setLineStyle(1);
        $pdf->ezColumnsStart(array('num' => 2));
        $pdf->line($pdf->ez['leftMargin'], $pdf->y, $pdf->ez['pageWidth'] - $pdf->ez['rightMargin'], $pdf->y);
        $pdf->ezText('<b>' . xl('Patient Name & Address') . '</b>', 6);
        $pdf->ezText($p->patient->get_name_display(), 10);
        $res = sqlQuery("SELECT  concat(street,'\n',city,', ',state,' ',postal_code,'\n',if(phone_home!='',phone_home,if(phone_cell!='',phone_cell,if(phone_biz!='',phone_biz,'')))) addr from patient_data where pid =" . add_escape_custom($p->patient->id));
        $pdf->ezText($res['addr']);
        $my_y = $pdf->y;
        $pdf->ezNewPage();
        $pdf->line($pdf->ez['leftMargin'], $pdf->y, $pdf->ez['pageWidth'] - $pdf->ez['rightMargin'], $pdf->y);
        $pdf->ezText('<b>' . xl('Date of Birth') . '</b>', 6);
        $pdf->ezText($p->patient->date_of_birth, 10);
        $pdf->ezText('');
        $pdf->line($pdf->ez['leftMargin'], $pdf->y, $pdf->ez['pageWidth'] - $pdf->ez['rightMargin'], $pdf->y);
        $pdf->ezText('<b>' . xl('Medical Record #') . '</b>', 6);
        $pdf->ezText(str_pad($p->patient->get_pubpid(), 10, "0", STR_PAD_LEFT), 10);
        $pdf->ezColumnsStop();
        if ($my_y < $pdf->y) {
            $pdf->ezSetY($my_y);
        }

        $pdf->ezText('');
        $pdf->line($pdf->ez['leftMargin'], $pdf->y, $pdf->ez['pageWidth'] - $pdf->ez['rightMargin'], $pdf->y);
        $pdf->ezText('<b>' . xl('Prescriptions') . '</b>', 6);
        $pdf->ezText('', 10);
    }

    function multiprintcss_header($p)
    {
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
        $res = sqlQuery("SELECT concat('<b>',f.name,'</b>\n',f.street,'\n',f.city,', ',f.state,' ',f.postal_code,'\nTel:',f.phone,if(f.fax != '',concat('\nFax: ',f.fax),'')) addr FROM users JOIN facility AS f ON f.name = users.facility where users.id ='" . add_escape_custom($p->provider->id) . "'");
        if (!empty($res)) {
            $patterns = array ('/\n/','/Tel:/','/Fax:/');
            $replace = array ('<br />', xl('Tel') . ':', xl('Fax') . ':');
            $res = preg_replace($patterns, $replace, $res);
        }

        echo ('<span class="large">' . ($res['addr'] ?? '') . '</span>');
        echo ("</td>\n");
        echo ("<td>\n");
        echo ('<b><span class="large">' .  $p->provider->get_name_display() . '</span></b>' . '<br />');

        if ($GLOBALS['rx_enable_DEA']) {
            if ($GLOBALS['rx_show_DEA']) {
                echo ('<span class="large"><b>' . xl('DEA') . ':</b>' . $p->provider->federal_drug_id . '</span><br />');
            } else {
                echo ('<b><span class="large">' . xl('DEA') . ':</span></b> ________________________<br />' );
            }
        }

        if ($GLOBALS['rx_enable_NPI']) {
            if ($GLOBALS['rx_show_NPI']) {
                echo ('<span class="large"><b>' . xl('NPI') . ':</b>' . $p->provider->npi . '</span><br />');
            } else {
                echo ('<b><span class="large">' . xl('NPI') . ':</span></b> ________________________<br />');
            }
        }

        if ($GLOBALS['rx_enable_SLN']) {
            if ($GLOBALS['rx_show_SLN']) {
                echo ('<span class="large"><b>' . xl('State Lic. #') . ':</b>' . $p->provider->state_license_number . '</span><br />');
            } else {
                echo ('<b><span class="large">' . xl('State Lic. #') . ':</span></b> ________________________<br />');
            }
        }

        echo ("</td>\n");
        echo ("</tr>\n");
        echo ("<tr>\n");
        echo ("<td rowspan='2' class='bordered'>\n");
        echo ('<b><span class="small">' . xl('Patient Name & Address') . '</span></b>' . '<br />');
        echo ($p->patient->get_name_display() . '<br />');
        $res = sqlQuery("SELECT  concat(street,'\n',city,', ',state,' ',postal_code,'\n',if(phone_home!='',phone_home,if(phone_cell!='',phone_cell,if(phone_biz!='',phone_biz,'')))) addr from patient_data where pid =" . add_escape_custom($p->patient->id));
        if (!empty($res)) {
            $patterns = array ('/\n/');
            $replace = array ('<br />');
            $res = preg_replace($patterns, $replace, $res);
        }

        echo ($res['addr']);
        echo ("</td>\n");
        echo ("<td class='bordered'>\n");
        echo ('<b><span class="small">' . xl('Date of Birth') . '</span></b>' . '<br />');
        echo ($p->patient->date_of_birth );
        echo ("</td>\n");
        echo ("</tr>\n");
        echo ("<tr>\n");
        echo ("<td class='bordered'>\n");
        echo ('<b><span class="small">' . xl('Medical Record #') . '</span></b>' . '<br />');
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

    function multiprintcss_preheader()
    {
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

    function multiprintfax_footer(&$pdf)
    {
        return $this->multiprint_footer($pdf);
    }

    function current_user_has_signature()
    {
        if (!empty($this->pconfig['signature'])) {
            $sigfile = str_replace('{userid}', $_SESSION["authUser"], $this->pconfig['signature']);
            if (file_exists($sigfile)) {
                return true;
            }
        }
        return false;
    }

    function multiprint_footer(&$pdf)
    {
        if (
            $this->pconfig['use_signature']
            && $this->current_user_has_signature()
            && ( $this->is_faxing || $this->is_print_to_fax )
        ) {
            $sigfile = str_replace('{userid}', $_SESSION["authUser"], $this->pconfig['signature']);
            if (file_exists($sigfile)) {
                $pdf->ezText(xl('Signature') . ": ", 12);
                $width = 0; // set to 0 so it uses the image width
                $pdf->ezImage($sigfile, null, 0, "none", "center");
                $pdf->ezText(xl('Date') . ": " . date('Y-m-d'), 12);
                if ($this->is_print_to_fax) {
                    $pdf->ezText(xl('Please do not accept this prescription unless it was received via facsimile.'));
                }

                $addenumFile = $this->pconfig['addendum_file'];
                if (file_exists($addenumFile)) {
                    $pdf->ezText('');
                    $f = fopen($addenumFile, "r");
                    while ($line = fgets($f, 1000)) {
                        $pdf->ezText(rtrim($line));
                    }
                }

                return;
            }
        }

        $pdf->ezText("\n\n\n\n" . xl('Signature') . ":________________________________\n" . xl('Date') . ": " . date('Y-m-d'), 12);
    }

    function multiprintcss_footer()
    {
        echo ("<div class='signdiv'>\n");
        echo (xl('Signature') . ":________________________________<br />");
        echo (xl('Date') . ": " . date('Y-m-d'));
        echo ("</div>\n");
        echo ("</div>\n");
    }

    function multiprintcss_postfooter()
    {
        echo("<script>\n");
        echo("opener.top.printLogPrint(window);\n");
        echo("</script>\n");
        echo("</body>\n");
        echo("</html>\n");
    }

    function get_prescription_body_text($p)
    {
        $body = '<b>' . xlt('Rx') . ': ' . text($p->get_drug()) . ' ' . text($p->get_size()) . ' ' . text($p->get_unit_display());
        if ($p->get_form()) {
            $body .= ' [' . text($p->form_array[$p->get_form()]) . "]";
        }

        $body .= "</b>     <i>" .
            text($p->substitute_array[$p->get_substitute()]) . "</i>\n" .
            '<b>' . xlt('Disp #') . ':</b> <u>' . text($p->get_quantity()) . "</u>\n" .
            '<b>' . xlt('Sig') . ':</b> ' . text($p->get_dosage() ?? '') . ' ' . text($p->form_array[$p->get_form()] ?? '') . ' ' .
            text($p->route_array[$p->get_route()] ?? '') . ' ' . text($p->interval_array[$p->get_interval()] ?? '') . "\n";
        if ($p->get_refills() > 0) {
            $body .= "\n<b>" . xlt('Refills') . ":</b> <u>" .  text($p->get_refills());
            if ($p->get_per_refill()) {
                $body .= " " . xlt('of quantity') . " " . text($p->get_per_refill());
            }

            $body .= "</u>\n";
        } else {
            $body .= "\n<b>" . xlt('Refills') . ":</b> <u>0 (" . xlt('Zero') . ")</u>\n";
        }

        $note = $p->get_note();
        if ($note != '') {
            $body .= "\n" . text($note) . "\n";
        }

        return $body;
    }

    function multiprintfax_body(&$pdf, $p)
    {
        return $this->multiprint_body($pdf, $p);
    }

    function multiprint_body(&$pdf, $p)
    {
        $pdf->ez['leftMargin'] += $pdf->ez['leftMargin'];
        $pdf->ez['rightMargin'] += $pdf->ez['rightMargin'];
        $d = $this->get_prescription_body_text($p);
        if ($pdf->ezText($d, 10, array(), 1)) {
            $pdf->ez['leftMargin'] -= $pdf->ez['leftMargin'];
            $pdf->ez['rightMargin'] -= $pdf->ez['rightMargin'];
            $this->multiprint_footer($pdf);
            $pdf->ezNewPage();
            $this->multiprint_header($pdf, $p);
            $pdf->ez['leftMargin'] += $pdf->ez['leftMargin'];
            $pdf->ez['rightMargin'] += $pdf->ez['rightMargin'];
        }

        $my_y = $pdf->y;
        $pdf->ezText($d, 10);
        if ($this->pconfig['shading']) {
            $pdf->setColor(.9, .9, .9);
            $pdf->filledRectangle($pdf->ez['leftMargin'], $pdf->y, $pdf->ez['pageWidth'] - $pdf->ez['rightMargin'] - $pdf->ez['leftMargin'], $my_y - $pdf->y);
            $pdf->setColor(0, 0, 0);
        }

        $pdf->ezSetY($my_y);
        $pdf->ezText($d, 10);
        $pdf->ez['leftMargin'] = $GLOBALS['rx_left_margin'];
        $pdf->ez['rightMargin'] = $GLOBALS['rx_right_margin'];
        $pdf->ezText('');
        $pdf->line($pdf->ez['leftMargin'], $pdf->y, $pdf->ez['pageWidth'] - $pdf->ez['rightMargin'], $pdf->y);
        $pdf->ezText('');
    }

    function multiprintcss_body($p)
    {
        $d = $this->get_prescription_body_text($p);
        $patterns = array ('/\n/','/     /');
        $replace = array ('<br />','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $d = preg_replace($patterns, $replace, $d);
        echo ("<div class='scriptdiv'>\n" . $d . "</div>\n");
    }

    function multiprintfax_action($id = "")
    {
        $this->is_print_to_fax = true;
        return $this->multiprint_action($id);
    }

    function multiprint_action($id = "")
    {
        $_POST['process'] = "true";
        if (empty($id)) {
            $this->function_argument_error();
        }

        list($pdf, $patient) = $this->generatePdfObjectForPrescriptionIds($id);

        $pFirstName = $patient->fname; //modified by epsdky for prescription filename change to include patient name and ID
        $pFName = convert_safe_file_dir_name($pFirstName);
        $modedFileName = "Rx_{$pFName}_{$patient->id}.pdf";
        $pdf->ezStream(array('Content-Disposition' => $modedFileName));
        return;
    }

    function multiprintplain_header($p)
    {
        $this->providerid = $p->provider->id;
        $sql = "SELECT f.name, f.street, f.state, f.postal_code, f.phone, if(f.fax != '', f.fax, '') FROM users JOIN facility AS f ON f.name = users.facility where users.id = ?";
        $result = QueryUtils::fetchRecords($sql, [$p->provider->id]);
        $address = '';
        if (!empty($result)) {
            $res = $result[0];
            $parts = [];
            $parts[] = $res['name'];
            $parts[] = $res['street'];
            if (!empty($res['city'])) {
                $parts[] = $res['city'] ?? '' . ', ' . $res['state'] ?? '' . ' ' . $res['postal_code'] ?? '';
            } else if (!empty($res['state']) && !empty($res['postal_code'])) {
                $parts[] = $res['state'] ?? '' . ' ' . $res['postal_code'] ?? '';
            }
            if (!empty($res['phone'])) {
                $parts[] = xl('Tel:') . $res['phone'] ?? '';
            }
            if (!empty($res['fax'])) {
                $parts[] = xl('Fax:') . $res['fax'] ?? '';
            }
            $address = implode("\n", $parts);
            if (trim($address) == "") {
                $address = "";
            }
        }
        echo xl("Digital Prescription Information") . "\n";
        echo xl("Prescriber") . "\n";
        echo $address;
        echo ("\n");
        echo ("\n");
        echo ($p->provider->get_name_display()) . "\n";

        if ($GLOBALS['rx_enable_DEA']) {
            if ($GLOBALS['rx_show_DEA']) {
                echo (xl('DEA') . ':' . $p->provider->federal_drug_id . "\n");
            } else {
                echo (xl('DEA') . ": ________________________\n" );
            }
        }

        if ($GLOBALS['rx_enable_NPI']) {
            if ($GLOBALS['rx_show_NPI']) {
                echo (xl('NPI') . ':' . $p->provider->npi . '') . "\n";
            } else {
                echo ('' . xl('NPI') . ": ________________________\n");
            }
        }

        if ($GLOBALS['rx_enable_SLN']) {
            if ($GLOBALS['rx_show_SLN']) {
                echo (xl('State Lic. #') . ':' . $p->provider->state_license_number . "\n");
            } else {
                echo (xl('State Lic. #') . ": ________________________\n");
            }
        }
        echo "\n\n";
        echo (xl('Patient Name & Address') . "\n");
        echo ($p->patient->get_name_display() . "\n");
        $sql = "SELECT street, city, `state`, postal_code, if(phone_home!='',phone_home,if(phone_cell!='',phone_cell,if(phone_biz!='',phone_biz,''))) AS phone from patient_data where pid = ?";
        $result = QueryUtils::fetchRecords($sql, [$p->patient->id]);
        $address = '';
        if (!empty($result)) {
            $res = $result[0];
            $parts = [];
            $parts[] = $res['street'];
            if (!empty($res['city'])) {
                $parts[] = $res['city'] ?? '' . ', ' . $res['state'] ?? '' . ' ' . $res['postal_code'] ?? '';
            } else if (!empty($res['state']) && !empty($res['postal_code'])) {
                $parts[] = $res['state'] ?? '' . ' ' . $res['postal_code'] ?? '';
            }

            if (!empty($res['phone'])) {
                $parts[] = xl('Tel:') . $res['phone'] ?? '';
            }
            $address = implode("\n", $parts);
            $address = trim($address);
        }

        echo ($address);
        echo "\n";
        echo (xl('Date of Birth')) . " ";
        echo ($p->patient->date_of_birth );
        echo "\n";
        echo xl('Medical Record #');
        echo (str_pad($p->patient->get_pubpid(), 10, "0", STR_PAD_LEFT));
        echo "\n\n";
        echo xl('Prescriptions') . "\n";
    }


    function multiprintplain_footer()
    {
        echo xl('Signature') . ":________________________________\n";
        echo xl('Date') . ": " . date('Y-m-d') . "\n";
    }


    /**
     * Outputs a JSON response of the subject and message body for the prescription contents to go into a native
     * email client.  Useful if you want to use the native mailto: handler in the browser or user-agent's operating system.
     * @return void
     */
    function getDefaultMailClientText_action()
    {
        $idsGet = $_GET['ids'];

        if (empty($idsGet)) {
            $this->function_argument_error();
            return;
        }
        ob_start();

        $ids = preg_split('/::/', substr($idsGet, 1, strlen($idsGet) - 2), -1, PREG_SPLIT_NO_EMPTY);

        $on_this_page = 0;
        foreach ($ids as $id) {
            $p = new Prescription($id);
            if ($on_this_page == 0) {
                $this->multiprintplain_header($p);
            }

            if (++$on_this_page > 3 || $p->provider->id != $this->providerid) {
                $this->multiprintplain_footer();
                $this->multiprintplain_header($p);
                $on_this_page = 1;
            }

            // we don't want any html in the plain text rendering
            echo strip_tags($this->get_prescription_body_text($p));
        }

        $this->multiprintplain_footer();
        $data = ob_get_clean();
        $result = [
            'subject' => $GLOBALS['openemr_name'] . " " . xl(" Prescription ")
            ,'message' => $data
        ];
        http_response_code(200);
        header("Content-Type:" . "application/json");
        echo json_encode($result);
        return;
    }

    function multiprintcss_action($id = "")
    {
        $_POST['process'] = "true";
        if (empty($id)) {
            $this->function_argument_error();
        }

        $this->multiprintcss_preheader();

        $this->_state = false; // Added by Rod - see Controller.class.php
        $ids = preg_split('/::/', substr($id, 1, strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);

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

    function send_action_process()
    {
        $dummy = ""; // Added by Rod to avoid run-time warnings
        if ($_POST['process'] != "true") {
            return;
        }
        $id = $_POST['sendEmailPrescriptionIds'];

        if (empty($id)) {
            $this->function_argument_error();
        }
        $sendAsPDF = intval($_POST['sendAsPdf'] ?? 0) == 1;

        $patient = $this->email_prescription($id, $_POST['email_to'], $sendAsPDF);
        return $this->list_action($patient->id);
    }

    function print_prescription($p, &$toFile)
    {
        $pdf = new Cezpdf($GLOBALS['rx_paper_size']);
        $pdf->ezSetMargins($GLOBALS['rx_top_margin'], $GLOBALS['rx_bottom_margin'], $GLOBALS['rx_left_margin'], $GLOBALS['rx_right_margin']);

        $pdf->selectFont('Helvetica');

        // Signature images are to be used only when faxing.
        if (!empty($toFile)) {
            $this->is_faxing = true;
        }

        $this->multiprint_header($pdf, $p);
        $this->multiprint_body($pdf, $p);
        $this->multiprint_footer($pdf);

        if (!empty($toFile)) {
            $toFile = $pdf->ezOutput();
        } else {
            $pdf->ezStream();
            // $pdf->ezStream(array('compress' => 0)); // for testing with uncompressed output
        }

        return;
    }

    function print_prescription_css($p, &$toFile)
    {

        $this->multiprintcss_preheader();
        $this->multiprintcss_header($p);
        $this->multiprintcss_body($p);
        $this->multiprintcss_footer();
        $this->multiprintcss_postfooter();
    }

    function email_prescription($id, $email, $sendAsPdf)
    {
        if (empty($email)) {
            $this->assign("process_result", "Email could not be sent, the address supplied: '$email' was empty or invalid.");
            return;
        }

        $mail = new MyMailer();
        if ($sendAsPdf) {
            list($pdf, $patient) = $this->generatePdfObjectForPrescriptionIds($id);
            $pdfAsString = $pdf->output();
            $mailBody = $GLOBALS['openemr_name'] . " " . xl("Prescription attached to this email.") . " " . xl("Patient") . " " . $patient->get_name_display();
        } else {
            list($mailBody, $patient) = $this->generateHtmlObjectForPrescriptionIds($id);
            $mail->isHTML(true);
        }

        $mail->From = $GLOBALS['practice_return_email_path'];
//        $mail->FromName = $p->provider->get_name_display();
//        $text_body  = $p->get_prescription_display();
        $mail->Body = $mailBody;
        $mail->Subject = $GLOBALS['openemr_name'] . " " . xl("Prescription");
        $mail->AddAddress($email);
        if ($sendAsPdf) {
            $mail->addStringAttachment($pdfAsString, 'Prescription-' . date("Y-m-d_H_i_s") . ".pdf");
        }

        if ($mail->Send()) {
            $this->assign("process_result", "Email was successfully sent to: " . $email);
            return $patient;
        } else {
            $this->assign("process_result", "There has been a mail error sending to " . $_POST['email_to'] . " " . $mail->ErrorInfo);
            return $patient;
        }
    }

    function do_lookup()
    {
        if ($_POST['process'] != "true") {
                    // don't do a lookup
            $this->assign("drug", $_GET['drug']);
                    return;
        }

        // process the lookup
        $this->assign("drug", $_POST['drug']);
        $list = array();
        if (!empty($_POST['drug'])) {
            $list = $this->RxList->getList($_POST['drug']);
        }

        if (is_array($list)) {
            $list = array_flip($list);
            $this->assign("drug_options", $list);
            $this->assign("drug_values", array_keys($list));
        } else {
            $this->assign("NO_RESULTS", xl("No results found for") . ": " . $_POST['drug']);
        }

        //print_r($_POST);
        //$this->assign("PROCESS","");

        $_POST['process'] = "";
    }

    function fax_prescription($p, $faxNum)
    {
        $err = "Sent fax";
        //strip - ,(, ), and ws
        $faxNum = preg_replace("/(-*)(\(*)(\)*)(\s*)/", "", $faxNum);
        //validate the number

        if (!empty($faxNum) && is_numeric($faxNum)) {
            //get the sendfax command and execute it
            $cmd = $this->pconfig['sendfax'];
            // prepend any prefix to the fax number
            $pref = $this->pconfig['prefix'];
            $faxNum = $pref . $faxNum;
            if (empty($cmd)) {
                $err .= " Send fax not set in includes/config.php";
            } else {
                //generate file to fax
                $faxFile = "Failed";
                $this->print_prescription($p, $faxFile);
                if (empty($faxFile)) {
                    $err .= " print_prescription returned empty file";
                }

                $fileName = $GLOBALS['OE_SITE_DIR'] . "/documents/" . $p->get_id() .
                $p->get_patient_id() . "_fax_.pdf";
                //print "filename is $fileName";
                touch($fileName); // php bug
                $handle = fopen($fileName, "w");
                if (!$handle) {
                    $err .= " Failed to open file $fileName to write fax to";
                }

                if (fwrite($handle, $faxFile) === false) {
                    $err .= " Failed to write data to $fileName";
                }

                fclose($handle);
                $args = " -n -d $faxNum $fileName";
                //print "command is $cmd $args<br />";
                exec($cmd . $args);
            }
        } else {
            $err = "bad fax number passed to function";
        }

        if ($err) {
            $this->assign("process_result", $err);
        }
    }

    /**
     * @param mixed $id
     * @return array
     */
    private function generatePdfObjectForPrescriptionIds(mixed $id): array
    {
        $pdf = new Cezpdf($GLOBALS['rx_paper_size']);
        $pdf->ezSetMargins($GLOBALS['rx_top_margin'], $GLOBALS['rx_bottom_margin'], $GLOBALS['rx_left_margin'], $GLOBALS['rx_right_margin']);
        $pdf->selectFont('Helvetica');

        // $print_header = true;
        $on_this_page = 0;

        //print prescriptions body
        $this->_state = false; // Added by Rod - see Controller.class.php
        $ids = preg_split('/::/', substr($id, 1, strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);
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
        return array($pdf, $p->patient);
    }

    private function generateHtmlObjectForPrescriptionIds($id)
    {
        ob_start();
        $this->multiprintcss_action($id);
        $html = ob_get_clean();
        $ids = preg_split('/::/', substr($id, 1, strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($ids)) {
            $prescription = new Prescription($ids[0]);
        }
        return [$html, $prescription->patient];
    }
}
