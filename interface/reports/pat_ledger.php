<?php

/**
 * This is a report to create a patient ledger of charges with payments
 * applied.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    WMT
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Rich Genandt <rgenandt@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once($GLOBALS['srcdir'] . '/patient.inc');
require_once($GLOBALS['srcdir'] . '/options.inc.php');
require_once($GLOBALS['srcdir'] . '/appointments.inc.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\FacilityService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$facilityService = new FacilityService();

$enc_units = $total_units = 0;
$enc_chg = $total_chg = 0;
$enc_pmt = $total_pmt = 0;
$enc_adj = $total_adj = 0;
$enc_bal = $total_bal = 0;
$bgcolor = "#FFFFDD";
$orow = 0;

$pat_pid = $_GET['patient_id'] ?? null;
$type_form = $_GET['form'];

if (! AclMain::aclCheckCore('acct', 'rep')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient Ledger by Date")]);
    exit;
}

function GetAllUnapplied($pat = '', $from_dt = '', $to_dt = '')
{
    $all = array();
    if (!$pat) {
        return($all);
    }

    $sql = "SELECT ar_session.*, ins.name, " .
      "pat.lname, pat.fname, pat.mname, " .
      "(SELECT SUM(ar_activity.pay_amount) FROM ar_activity WHERE " .
      "ar_activity.session_id = ar_session.session_id AND ar_activity.deleted IS NULL) AS applied " .
      "FROM ar_session " .
      "LEFT JOIN insurance_companies AS ins on ar_session.payer_id = ins.id " .
      "LEFT JOIN patient_data AS pat on ar_session.patient_id = pat.pid " .
      "WHERE " .
      "ar_session.created_time >= ? AND ar_session.created_time <= ? " .
      "AND ar_session.patient_id=?";
    $result = sqlStatement($sql, array($from_dt, $to_dt, $pat));
    $iter = 0;
    while ($row = sqlFetchArray($result)) {
        if (!$row['applied']) {
            continue;
        }
        $all[$iter] = $row;
        $iter++;
    }

    return($all);
}

function User_Id_Look($thisField)
{
    if (!$thisField) {
        return '';
    }

    $ret = '';
    $rlist = sqlStatement("SELECT lname, fname, mname FROM users WHERE id=?", array($thisField));
    $rrow = sqlFetchArray($rlist);
    if ($rrow) {
        $ret = $rrow['lname'] . ', ' . $rrow['fname'] . ' ' . $rrow['mname'];
    }

    return $ret;
}

function List_Look($thisData, $thisList)
{
    if ($thisList == 'occurrence') {
        if (!$thisData || $thisData == '') {
            return xl('Unknown or N/A');
        }
    }

    if ($thisData == '') {
        return '';
    }

    $fres = sqlStatement("SELECT title FROM list_options WHERE list_id = ? " .
        "AND option_id = ? AND activity = 1", array($thisList, $thisData));
    if ($fres) {
        $rret = sqlFetchArray($fres);
        $dispValue = xl_list_label($rret['title']);
        if ($thisList == 'occurrence' && $dispValue == '') {
            $dispValue = xl('Unknown or N/A');
        }
    } else {
        $dispValue = xl('Not Found');
    }

    return $dispValue;
}

function GetAllCredits($enc = '', $pat = '')
{
    $all = array();
    if (!$enc || !$pat) {
        return($all);
    }

    $sql = "SELECT activity.*, session.*, ins.name FROM ar_activity AS " .
    "activity LEFT JOIN ar_session AS session USING (session_id) " .
    "LEFT JOIN insurance_companies AS ins ON session.payer_id = " .
    "ins.id WHERE encounter = ? AND pid = ? AND activity.deleted IS NULL " .
    "ORDER BY sequence_no";
    $result = sqlStatement($sql, array($enc, $pat));
    $iter = 0;
    while ($row = sqlFetchArray($result)) {
        $all[$iter] = $row;
        $iter++;
    }

    return($all);
}
function PrintEncHeader($dt, $rsn, $dr)
{
    global $bgcolor, $orow;
    $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
    echo "<tr class='bg-white'>";
    if (strlen($rsn) > 50) {
        $rsn = substr($rsn, 0, 50) . '...';
    }

    echo "<td colspan='4'><span class='font-weight-bold'>" . xlt('Encounter Dt / Rsn') . ": </span><span class='detail'>" . text(substr($dt, 0, 10)) . " / " . text($rsn) . "</span></td>";
    echo "<td colspan='5'><span class='font-weight-bold'>" . xlt('Provider') . ": </span><span class='detail'>" . text(User_Id_Look($dr)) . "</span></td>";
    echo "</tr>\n";
    $orow++;
}
function PrintEncFooter()
{
    global $enc_units, $enc_chg, $enc_pmt, $enc_adj, $enc_bal;
    echo "<tr style='background-color: var(--gray300)'>";
    echo "<td colspan='3'>&nbsp;</td>";
    echo "<td class='detail'>" . xlt('Encounter Balance') . ":</td>";
    echo "<td class='detail text-center'>" . text($enc_units) . "</td>";
    echo "<td class='detail text-center'>" . text(oeFormatMoney($enc_chg)) . "</td>";
    echo "<td class='detail text-right'>" . text(oeFormatMoney($enc_pmt)) . "</td>";
    echo "<td class='detail text-right'>" . text(oeFormatMoney($enc_adj)) . "</td>";
    echo "<td class='detail text-right'>" . text(oeFormatMoney($enc_bal)) . "</td>";
    echo "</tr>\n";
}
function PrintCreditDetail($detail, $pat, $unassigned = false)
{
    global $enc_pmt, $total_pmt, $enc_adj, $total_adj, $enc_bal, $total_bal;
    global $bgcolor, $orow, $enc_units, $enc_chg;
    foreach ($detail as $pmt) {
        $uap_flag = false;
        if ($unassigned) {
            if (($pmt['pay_total'] - $pmt['applied']) == 0) {
                if (!$GLOBALS['show_payment_history']) {
                    continue;
                }
                $uap_flag = true;
            }
        }

        $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
        $print = "<tr style='background-color:" . attr($bgcolor) . ";'>";
        $print .= "<td class='detail'>&nbsp;</td>";
        $method = List_Look($pmt['payment_method'], 'payment_method');
        $desc = $pmt['description'];
        $ref = $pmt['reference'];
        if ($unassigned) {
              $memo = List_Look($pmt['adjustment_code'], 'payment_adjustment_code');
        } else {
              $memo = $pmt['memo'];
        }

        $description = $method;
        if ($ref) {
            if ($description) {
                $description .= ' - ';
            }

            $description .= $ref;
        }

        if ($desc) {
            if ($description) {
                $description .= ': ';
            }

            $description .= $desc;
        }

        if ($memo) {
            if ($description) {
                $description .= ' ';
            }

            $description .= '[' . $memo . ']';
        }
        if ($uap_flag === true) {
            if ($description) {
                $description .= ' ';
            }
            $description .= '{Pay History}';
        }
        $print .= "<td class='detail' colspan='2'>" .
                                      text($description) . "&nbsp;</td>";
        $payer = ($pmt['name'] == '') ? xl('Patient') : $pmt['name'];
        if ($unassigned) {
              $pmt_date = substr($pmt['post_to_date'], 0, 10);
        } else {
              $pmt_date = substr($pmt['post_time'], 0, 10);
        }

        $print .= "<td class='detail'>" .
        text($pmt_date) . "&nbsp;/&nbsp;" . text($payer) . "</td>";
        $type = List_Look($pmt['payment_type'], 'payment_type');
        $print .= "<td class='detail'>" . text($type) . "&nbsp;</td>";
        if ($unassigned) {
              $pmt_amt = $pmt['pay_total'] - $pmt['applied'];
              $uac_bal = $pmt_amt * -1;
              $uac_appl = $pmt['applied'];
              $uac_total = $pmt['pay_total'];
              $pmt_amt = $pmt['pay_total'];
              $total_pmt = $total_pmt - $uac_bal;
        } else {
              $uac_total = '';
              $uac_bal = '';
              $uac_appl = '';
              $pmt_amt = $pmt['pay_amount'];
              $adj_amt = $pmt['adj_amount'];
              $enc_pmt = $enc_pmt + $pmt['pay_amount'];
              $total_pmt = $total_pmt + $pmt['pay_amount'];
              $enc_adj = $enc_adj + $pmt['adj_amount'];
              $total_adj = $total_adj + $pmt['adj_amount'];
        }

        $print_pmt = '';
        if ($pmt_amt != 0) {
            $print_pmt = oeFormatMoney($pmt_amt);
        }

        $print_adj = '';
        if (!empty($adj_amt)) {
            $print_adj = oeFormatMoney($adj_amt);
        }

        $print_appl = $uac_appl ? oeFormatMoney($uac_appl) : "";
        $print_bal = $uac_bal ? oeFormatMoney($uac_bal) : "";

        $print .= "<td class='detail text-center'>" . text($print_appl) . "&nbsp;</td>";
        $print .= "<td class='detail text-right'>" . text($print_pmt) . "&nbsp;</td>";
        $print .= "<td class='detail text-right'>" . text($print_adj) . "&nbsp;</td>";
        $print .= "<td class='detail text-right'>" . text($print_bal) . "&nbsp;</td>";
        $print .= "</tr>\n";
        echo $print;
        if (!empty($pmt['follow_up_note'])) {
            $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
            $print = "<tr style='background-color:" . attr($bgcolor) . ";'>";
            $print .= "<td class='detail' colspan='2'>&nbsp;</td>";
            $print .= "<td colspan='7'>" . xlt('Follow Up Note') . ": ";
            $print .= text($pmt['follow_up_note']);
            $print .= "</td></tr>\n";
            echo $print;
        }

        if ($unassigned) {
            $total_bal = $total_bal + $uac_bal;
        } else {
            $enc_bal = $enc_bal - $pmt_amt - $adj_amt;
            $total_bal = $total_bal - $pmt_amt - $adj_amt;
        }

        $orow++;
    }

    $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
}

if (!isset($_REQUEST['form_facility'])) {
    $_REQUEST['form_facility'] = '';
}

if (!isset($_REQUEST['form_provider'])) {
    $_REQUEST['form_provider'] = '';
}

if ($type_form == '0') {
    if (!isset($_REQUEST['form_patient'])) {
        $_REQUEST['form_patient'] = '';
    }

    if (!isset($_REQUEST['form_pid'])) {
        $_REQUEST['form_pid'] = '';
    }
} else {
    if (!isset($_REQUEST['form_patient'])) {
        $_REQUEST['form_patient'] = $pat_pid;
    }

    if (!isset($_REQUEST['form_pid'])) {
        $_REQUEST['form_pid'] = $pat_pid;
    }
}

if (!isset($_REQUEST['form_csvexport'])) {
    $_REQUEST['form_csvexport'] = '';
}

if (!isset($_REQUEST['form_refresh'])) {
    $_REQUEST['form_refresh'] = '';
}

if (!isset($_REQUEST['$form_dob'])) {
    $_REQUEST['$form_dob'] = '';
}

if (substr($GLOBALS['ledger_begin_date'], 0, 1) == 'Y') {
    $ledger_time = substr($GLOBALS['ledger_begin_date'], 1, 1);
    $last_year = mktime(0, 0, 0, date('m'), date('d'), date('Y') - $ledger_time);
} elseif (substr($GLOBALS['ledger_begin_date'], 0, 1) == 'M') {
    $ledger_time = substr($GLOBALS['ledger_begin_date'], 1, 1);
    $last_year = mktime(0, 0, 0, date('m') - $ledger_time, date('d'), date('Y'));
} elseif (substr($GLOBALS['ledger_begin_date'], 0, 1) == 'D') {
    $ledger_time = substr($GLOBALS['ledger_begin_date'], 1, 1);
    $last_year = mktime(0, 0, 0, date('m'), date('d') - $ledger_time, date('Y'));
}

$form_from_date = date('Y-m-d', $last_year);
if (!empty($_REQUEST['form_from_date'])) {
    $form_from_date = DateToYYYYMMDD($_POST['form_from_date']);
}

$form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_REQUEST['form_facility'];
$form_provider  = $_REQUEST['form_provider'];
$form_patient   = $_REQUEST['form_patient'];
$form_pid       = $_REQUEST['form_pid'];
$form_dob       = $_REQUEST['form_dob'] ?? null;

if ($_REQUEST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=svc_financial_report_" . attr($form_from_date) . "--" . attr($form_to_date) . ".csv");
    header("Content-Description: File Transfer");
} else {
    ?>
<html>
<head>

    <title><?php echo xlt('Patient Ledger by Date'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

    <script>
        var pt_name;
        var pt_id;
        function checkSubmit() {
            var pat = document.forms[0].elements['form_patient'].value;
            if(!pat || pat == 0) {
                alert(<?php echo xlj('A Patient Must Be Selected to Generate This Report') ?>);
                return false;
            }
            document.forms[0].elements['form_refresh'].value = true;
            document.forms[0].elements['form_csvexport'].value = '';
            document.forms[0].submit();
        }
        function setpatient(pid, lname, fname, dob) {
          document.forms[0].elements['form_patient'].value = lname + ', ' + fname;
          document.forms[0].elements['form_pid'].value = pid;
          document.forms[0].elements['form_dob'].value = dob;
        }
        function sel_patient() {
            dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
        }
    </script>

    <style>
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }
            #report_results {
                margin-top: 30px;
            }
            #report_header {
                visibility: visible;
                display: inline;
            }
            #title {
                visibility: hidden;
                display: none;
            }
        }
        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
            #report_header {
                visibility: hidden;
                display: none;
            }
            #title {
                visibility: visible;
                display: inline;
            }
        }
    </style>
    <script>
        $(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });
    </script>
    <script>
    <?php require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal?>
    </script>
    <?php
    if ($type_form == '0') {
        $arrOeUiSettings = array(
        'heading_title' => xl('Report') . " - " . xl('Patient Ledger by Date'),
        'include_patient_name' => false,
        'expandable' => false,
        'expandable_files' => array("patient_ledger_report_xpd"),//all file names need suffix _xpd
        'action' => "conceal",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link and back
        'show_help_icon' => false,
        'help_file_name' => ""
        );
    } else {
        $arrOeUiSettings = array(
        'heading_title' => xl('Patient Ledger'),
        'include_patient_name' => true,
        'expandable' => true,
        'expandable_files' => array("patient_ledger_patient_xpd", "stats_full_patient_xpd", "external_data_patient_xpd"),//all file names need suffix _xpd
        'action' => "conceal",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link and back
        'show_help_icon' => true,
        'help_file_name' => "ledger_dashboard_help.php"
        );
    }
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>
<body>
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?> mt-3">
        <div class="row">
            <div class="col-sm-12">
                <?php
                if ($type_form != '0') {
                    require_once("$include_root/patient_file/summary/dashboard_header.php");
                } else {
                    echo  $oemr_ui->pageHeading() . "\r\n";
                } ?>
            </div>
        </div>
        <?php if ($type_form != '0') { ?>
        <div class="row">
            <div class="col-sm-12">
                <?php
                $list_id = "ledger"; // to indicate nav item is active, count and give correct id
                // Collect the patient menu then build it
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>

        <?php } ?>

        <div class="row hideaway" >
            <div class="col-sm-12">
                <form method='post' action='pat_ledger.php?form=<?php echo attr_url($type_form); ?>&patient_id=<?php echo attr_url($form_pid); ?>' id='theform' onsubmit='return top.restoreSession()'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div id="report_parameters">
                        <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                        <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <?php
                                    if ($type_form == '1') { ?>
                                    <td width='35%'>
                                        <?php
                                    } else { ?>
                                    <td width='70%'>
                                        <?php
                                    } ?>
                                        <div class="float-left">
                                            <table class='text'>
                                                    <tr>
                                                        <?php
                                                        if ($type_form == '0') { ?>
                                                        <td class='col-form-label'>
                                                            <?php echo xlt('Facility'); ?>:
                                                        </td>
                                                        <td>
                                                            <?php dropdown_facility($form_facility, 'form_facility', true); ?>
                                                        </td>
                                                        <td class='col-form-label'><?php echo xlt('Provider'); ?>:</td>
                                                        <td>
                                                            <?php
                                                            $query = "SELECT id, lname, fname FROM users WHERE " .
                                                            "authorized=1 AND active!=0 ORDER BY lname, fname";
                                                            $ures = sqlStatement($query);
                                                            echo "   <select name='form_provider' class='form-control'>\n";
                                                            echo "    <option value=''>-- " . xlt('All') . " --\n";
                                                            while ($urow = sqlFetchArray($ures)) {
                                                                $provid = $urow['id'];
                                                                echo "    <option value='" . attr($provid) . "'";
                                                                if ($provid == $_REQUEST['form_provider']) {
                                                                    echo " selected";
                                                                }
                                                                echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                                            }
                                                            echo "   </select>\n";
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                            <?php
                                                        } ?>
                                                        <td class='col-form-label'>
                                                            <?php echo xlt('From'); ?>:&nbsp;&nbsp;&nbsp;&nbsp;
                                                        </td>
                                                        <td>
                                                            <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>' />
                                                        </td>
                                                        <td class='col-form-label' class='col-form-label'>
                                                            <?php echo xlt('To{{Range}}'); ?>:
                                                        </td>
                                                        <td>
                                                            <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>' />
                                                        </td>
                                                        <?php
                                                        if ($type_form == '0') { ?>
                                                        <td><span class='col-form-label'><?php echo xlt('Patient'); ?>:&nbsp;&nbsp;</span></td>
                                                        <td>
                                                            <input type='text' size='20' name='form_patient' class='form-control' style='cursor:pointer;' id='form_patient' value='<?php echo ($form_patient) ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
                                                            <?php
                                                        } else { ?>
                                                            <input type='hidden' name='form_patient' value='<?php echo attr($form_patient); ?>' />
                                                            <?php
                                                        } ?>
                                                            <input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
                                                            <input type='hidden' name='form_dob' value='<?php echo attr($form_dob); ?>' />
                                                        </td>
                                                    </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td class="align-middle h-100 text-left">
                                        <table class="w-100 h-100 border-left" >
                                                <tr>
                                                    <td>
                                                        <div class="text-center">
                                                            <div class="btn-group" role="group">
                                                                <a href='#' class='btn btn-primary btn-save' onclick="checkSubmit();" >
                                                                <?php echo xlt('Submit'); ?>
                                                                </a>
                                                                <?php
                                                                if ($_REQUEST['form_refresh'] || $_REQUEST['form_csvexport']) { ?>
                                                                    <a href='#' class='btn btn-primary btn-print' id='printbutton'>
                                                                        <?php echo xlt('Print Ledger'); ?>
                                                                    </a>
                                                                    <?php
                                                                    if ($type_form == '1') { ?>
                                                                        <a href="../patient_file/summary/demographics.php" class="btn btn-secondary btn-transmit" onclick="top.restoreSession()">
                                                                            <?php echo xlt('Back To Patient');?>
                                                                        </a>
                                                                        <?php
                                                                    } ?>
                                                                    <?php
                                                                } ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div> <!-- end of parameters -->

                <?php
} // end not export
$from_date = $form_from_date . ' 00:00:00';
$to_date = $form_to_date . ' 23:59:59';
if ($_REQUEST['form_refresh'] || $_REQUEST['form_csvexport']) {
    $rows = array();
    $sqlBindArray = array();
    $query = "select b.code_type, b.code, b.code_text, b.modifier, b.pid, b.provider_id, " .
    "b.billed, b.payer_id, b.units, b.fee, b.bill_date, b.id, " .
    "ins.name, " .
    "fe.encounter, fe.date, fe.reason, fe.provider_id " .
    "FROM form_encounter AS fe " .
    "LEFT JOIN billing AS b ON b.pid=fe.pid AND b.encounter=fe.encounter " .
    "LEFT JOIN insurance_companies AS ins ON b.payer_id = ins.id " .
    "LEFT OUTER JOIN code_types AS c ON c.ct_key = b.code_type " .
    "WHERE fe.date >= ? AND fe.date <= ? AND fe.pid = ? ";
    array_push($sqlBindArray, $from_date, $to_date, $form_pid);
    if ($form_facility) {
        $query .= "AND fe.facility_id = ? ";
        array_push($sqlBindArray, $form_facility);
    }

    if ($form_provider) {
        $query .= "AND b.provider_id = ? ";
        array_push($sqlBindArray, $form_provider);
    }

    $query .= "AND c.ct_proc = '1' ";
    $query .= "AND activity > 0 ORDER BY fe.date, fe.id ";
    $res = sqlStatement($query, $sqlBindArray);

    if ($_REQUEST['form_csvexport']) {
      // CSV headers:
        if (true) {
            echo csvEscape("Code/Enc Dt") . ',';
            echo csvEscape("Description") . ',';
            echo csvEscape("Billed/Who") . ',';
            echo csvEscape("Type/Units") . ',';
            echo csvEscape("Chg/Pmt Amount") . "\n";
        }
    } else {
        if (!$form_facility) {
            $form_facility = '3';
        }

        $facility = $facilityService->getById($form_facility);
        $patient = sqlQuery("SELECT * from patient_data WHERE pid=?", array($form_patient));
        $pat_dob = $patient['DOB'] ?? null;
        $pat_name = ($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '');
        ?>
        <div id="report_header">
            <div class="table-responsive">
                <table class="border-0 table" width="98%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="title"><?php echo text($facility['name']); ?></td>
                    </tr>
                    <tr>
                        <td class="title"><?php echo text($facility['street']); ?></td>
                    </tr>
                    <tr>
                        <td class="title"><?php echo text($facility['city']) . ", " . text($facility['state']) . " " . text($facility['postal_code']); ?></td>
                    </tr>
                    <tr>
                        <td class="title"><?php echo xlt('Phone') . ': ' . text($facility['phone']); ?></td>
                    </tr>
                    <tr>
                        <td class="title"><?php echo xlt('Tax Id') . ': ' . text($facility['federal_ein']); ?></td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>
                    <tr>
                        <td class="title"><?php echo xlt('Patient Ledger'); ?></td>
                    </tr>
                    <tr>
                        <?php
                        $title = xl('All Providers');
                        if ($form_provider) {
                            $title = xl('For Provider') . ': ' . User_Id_Look($form_provider);
                        }
                        ?>
                        <td class="title" ><?php echo text($title); ?></td>
                    </tr>
                    <tr>
                        <?php
                        $title = xl('For Dates') . ': ' . oeFormatShortDate($form_from_date) . ' - ' . oeFormatShortDate($form_to_date);
                        ?>
                        <td class="title"><?php echo text($title); ?></td>
                    </tr>
                </table>
            </div>
            <br/>
            <div class="table-responsove">
                <table class="table border-0">
                    <tr>
                        <td class='font-weight-bold'><?php echo xlt('Date')?>:
                            <?php echo text(date('Y-m-d')); ?>
                        </td>
                        <td class='font-weight-bold'><?php echo xlt('Patient')?>:
                            <?php
                            if ($type_form == '1') { ?>
                                <?php echo text($pat_name); ?>
                            <?php } else { ?>
                                <?php echo text($form_patient); ?>
                            <?php } ?>
                        </td>
                        <td class='font-weight-bold'><?php echo xlt('DOB')?>:
                            <?php
                            if ($type_form == '1') { ?>
                                <?php echo text($pat_dob);?>
                            <?php } else { ?>
                                <?php echo text($form_dob); ?>
                            <?php } ?>
                        </td>
                        <td class='font-weight-bold'> <?php echo xlt('ID')?>:
                            <?php echo text($form_pid);?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="report_results" class="jumbotron py-4">
            <table>
                <tr>
                    <td class='font-weight-bold'><?php echo xlt('Code'); ?></td>
                    <td colspan="2" class='font-weight-bold'><?php echo xlt('Description'); ?></td>
                    <td class='font-weight-bold'><?php echo xlt('Billed Date'); ?> / <?php echo xlt('Payor'); ?></td>
                    <td class='font-weight-bold'><?php echo xlt('Type'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php echo xlt('Units'); ?></td>
                    <td class='font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('Charge'); ?></td>
                    <td class='text-right font-weight-bold'>&nbsp;&nbsp;<?php echo xlt('Payment'); ?></td>
                    <td class='text-right font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('Adjustment'); ?></td>
                    <td class='text-right font-weight-bold'>&nbsp;&nbsp;&nbsp;<?php echo xlt('Balance'); ?></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;</td>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;</td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td class='font-weight-bold'>&nbsp;&nbsp;&nbsp;<?php echo xlt('UAC Appl'); ?></td>
                    <td class='text-right font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('UAC Tot'); ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                    <?php
    }

    $orow = 0;
    $prev_encounter_id = -1;
    $hdr_printed = false;
    $prev_row = array();
    while ($erow = sqlFetchArray($res)) {
        $print = '';
        $csv = '';
        if ($erow['encounter'] != $prev_encounter_id) {
            if ($prev_encounter_id != -1) {
                $credits = GetAllCredits($prev_encounter_id, $form_pid);
                if (count($credits) > 0) {
                    if (!$hdr_printed) {
                        PrintEncHeader(
                            $prev_row['date'],
                            $prev_row['reason'],
                            $prev_row['provider_id']
                        );
                    }

                    PrintCreditDetail($credits, $form_pid);
                }

                if ($hdr_printed) {
                    PrintEncFooter();
                }

                $hdr_printed = false;
            }

            $enc_units = $enc_chg = $enc_pmt = $enc_adj = $enc_bal = 0;
        }

        if ($erow['id']) {
            // Now print an encounter heading line -
            if (!$hdr_printed) {
                PrintEncHeader(
                    $erow['date'],
                    $erow['reason'],
                    $erow['provider_id']
                );
                $hdr_printed = true;
            }

            $code_desc = $erow['code_text'];
            if (strlen($code_desc) > 50) {
                $code_desc = substr($code_desc, 0, 50) . '...';
            }

            $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
            $print = "<tr style='background-color:" . attr($bgcolor) . ";'>";
            $print .= "<td class='detail'>" . text($erow['code']);
            if ($erow['modifier']) {
                $print .= ":" . text($erow['modifier']);
            }
            $print .= "</td>";
            $print .= "<td class='detail' colspan='2'>" . text($code_desc) . "</td>";
            $who = ($erow['name'] == '') ? xl('Self') : $erow['name'];
            $bill = substr($erow['bill_date'], 0, 10);
            if ($bill == '') {
                $bill = 'unbilled';
            }

            $print .= "<td class='detail'>" . text($bill) . "&nbsp;/&nbsp;" . text($who) . "</td>";
            $print .= "<td class='detail text-center'>" . text($erow['units']) . "</td>";
            $print .= "<td class='detail text-center'>" . text(oeFormatMoney($erow['fee'])) . "</td>";
            $print .= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
            $print .= "</tr>\n";

            $total_units  += $erow['units'];
            $total_chg += $erow['fee'];
            $total_bal += $erow['fee'];
            $enc_units  += $erow['units'];
            $enc_chg += $erow['fee'];
            $enc_bal += $erow['fee'];
            $orow++;

            if ($_REQUEST['form_csvexport']) {
                echo $csv;
            } else {
                echo $print;
            }
        }

        $prev_encounter_id = $erow['encounter'];
        $prev_row = $erow;
    }

    if ($prev_encounter_id != -1) {
        $credits = GetAllCredits($prev_encounter_id, $form_pid);
        if (count($credits) > 0) {
            if (!$hdr_printed) {
                PrintEncHeader(
                    $prev_row['date'],
                    $prev_row['reason'],
                    $prev_row['provider_id']
                );
            }

            PrintCreditDetail($credits, $form_pid);
        }

        if ($hdr_printed) {
            PrintEncFooter();
        }
    }

// This is the end of the encounter/charge loop -
    $uac = GetAllUnapplied($form_pid, $from_date, $to_date);
    if (count($uac) > 0) {
        if ($orow) {
            $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
            echo "<tr style='background-color: var(--white);'><td colspan='9'>&nbsp;</td></tr>\n";
        }

        PrintCreditDetail($uac, $form_pid, true);
    }

    if (!$_REQUEST['form_csvexport'] && $orow) {
        echo "<tr style='background-color: #DDFFFF;'>\n";
        echo " <td colspan='2'>&nbsp;</td>";
        echo " <td class='font-weight-bold' colspan='2'>" . xlt("Grand Total") . "</td>\n";
        echo " <td class='font-weight-bold text-center'>" . text($total_units) . "</td>\n";
        echo " <td class='font-weight-bold text-center'>" . text(oeFormatMoney($total_chg)) . "</td>\n";
        echo " <td class='font-weight-bold text-right'>" . text(oeFormatMoney($total_pmt)) . "</td>\n";
        echo " <td class='font-weight-bold text-right'>" . text(oeFormatMoney($total_adj)) . "</td>\n";
        echo " <td class='font-weight-bold text-right'>" . text(oeFormatMoney($total_bal)) . "</td>\n";
        echo " </tr>\n";
        ?>
    </table>
    <tr><td>&nbsp;</td></tr><br /><br />
        <?php
        if ($GLOBALS['print_next_appointment_on_ledger'] == 1) {
            $next_day = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
        # add one day to date so it will not get todays appointment
            $current_date2 = date('Y-m-d', $next_day);
            $events = fetchNextXAppts($current_date2, $form_pid);
            $next_appoint_date = oeFormatShortDate($events[0]['pc_eventDate'] ?? null);
            $next_appoint_time = substr(($events[0]['pc_startTime'] ?? ''), 0, 5);
            if (strlen($events[0]['umname'] ?? '') != 0) {
                $next_appoint_provider = $events[0]['ufname'] . ' ' . $events[0]['umname'] . ' ' .  $events[0]['ulname'];
            } else {
                $next_appoint_provider = ($events[0]['ufname'] ?? '') . ' ' .  ($events[0]['ulname'] ?? '');
            }

            if (strlen($next_appoint_time) != 0) { ?>
                                        <tr>
                                        <td class="title" ><?php echo xlt('Next Appointment Date') . ': ' . text($next_appoint_date) . ' ' . xlt('Time') . ' ' . text($next_appoint_time) . ' ' . xlt('Provider') . ' ' . text($next_appoint_provider); ?></td>
                                        </tr>
                                        <?php
            }
        } // end ($GLOBALS['print_next_appointment_on_ledger'] == 1)
    } // end (!$_REQUEST['form_csvexport'] && $orow)
    echo "</div>\n";
}

if (! $_REQUEST['form_csvexport']) {
    if ($_REQUEST['form_refresh'] && $orow <= 0) {
        echo "<span style='font-size: 13px;'>";
        echo xlt('No matches found. Try search again.');
        echo "</span>";
        echo '<script>document.getElementById("report_results").style.display="none";</script>';
    }

    if (!$_REQUEST['form_refresh'] && !$_REQUEST['form_csvexport']) { ?>
                    <div class='text'>
                        <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
                    </div><?php
    } ?>
                </form>
            </div>
        </div>
    </div><!--end of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
    <script>
        var listId = '#' + <?php echo js_escape($list_id ?? null); ?>;
        $(function () {
            $(listId).addClass("active");
        });
    </script>
</body>

</html>
    <?php
} // End not csv export
?>
