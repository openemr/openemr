<?php

/**
 * This is a report to create a Patient Billing Summary of charges with payments
 * applied.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    WMT
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Rich Genandt <rgenandt@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("./../verify_session.php");
$ignoreAuth_onsite_portal = true;
global $ignoreAuth_onsite_portal;


require_once('../../interface/globals.php');
require_once($GLOBALS['srcdir'] . '/patient.inc.php');
require_once($GLOBALS['srcdir'] . '/options.inc.php');
require_once($GLOBALS['srcdir'] . '/appointments.inc.php');

use OpenEMR\Core\Header;

$enc_units = $total_units = 0;
$enc_chg = $total_chg = 0;
$enc_pmt = $total_pmt = 0;
$enc_adj = $total_adj = 0;
$enc_bal = $total_bal = 0;
$bgcolor = "#FFFFDD";
$orow = 0;

function GetAllUnapplied($pat = '', $from_dt = '', $to_dt = '')
{
    $all = array();
    if (!$pat) {
        return($all);
    }

    $sql = "SELECT ar_session.*, ins.name, " .
      "pat.lname, pat.fname, pat.mname, " .
      "(SELECT SUM(ar_activity.pay_amount) FROM ar_activity WHERE " .
      "ar_activity.deleted IS NULL AND ar_activity.session_id = ar_session.session_id) AS applied " .
      "FROM ar_session " .
      "LEFT JOIN insurance_companies AS ins on ar_session.payer_id = ins.id " .
      "LEFT JOIN patient_data AS pat on ar_session.patient_id = pat.pid " .
      "WHERE " .
      "ar_session.created_time >= ? AND ar_session.created_time <= ? " .
      "AND ar_session.patient_id=?";
    $result = sqlStatement($sql, array($from_dt, $to_dt, $pat));
    $iter = 0;
    while ($row = sqlFetchArray($result)) {
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

    $fres = sqlStatement("SELECT title FROM list_options WHERE list_id=? " .
        "AND option_id=?", array($thisList, $thisData));
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
    "ins.id WHERE deleted IS NULL AND encounter = ? AND pid = ? " .
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
    echo "<tr bgcolor='#DDFFFF'>";
    echo "<td colspan='3'>&nbsp;</td>";
    echo "<td class='detail'>" . xlt('Encounter Balance') . ":</td>";
    echo "<td class='detail text-right'>" . text($enc_units) . "</td>";
    echo "<td class='detail text-right'>" . text(oeFormatMoney($enc_chg)) . "</td>";
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
        if ($unassigned) {
            if (($pmt['pay_total'] - $pmt['applied']) == 0) {
                continue;
            }
        }

        $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
        $print = "<tr bgcolor='" . attr($bgcolor) . "'>";
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
              $uac_bal = oeFormatMoney($pmt_amt * -1);
              $uac_appl = oeFormatMoney($pmt['applied']);
              $uac_total = oeFormatMoney($pmt['pay_total']);
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
        if ($adj_amt != 0) {
            $print_adj = oeFormatMoney($adj_amt);
        }

        $print .= "<td class='detail text-right'>" . text($uac_appl) . "&nbsp;</td>";
        $print .= "<td class='detail text-right'>" . text($print_pmt) . "&nbsp;</td>";
        $print .= "<td class='detail text-right'>" . text($print_adj) . "&nbsp;</td>";
        $print .= "<td class='detail text-right'>" . text($uac_bal) . "&nbsp;</td>";
        $print .= "</tr>\n";
        echo $print;
        if ($pmt['follow_up_note'] != '') {
            $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
            $print = "<tr bgcolor='" . attr($bgcolor) . "'>";
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
if (!isset($_REQUEST['form_from_date'])) {
    $_REQUEST['form_from_date'] = '';
}

if (!isset($_REQUEST['form_to_date'])) {
    $_REQUEST['form_to_date'] = '';
}

if (!isset($_REQUEST['form_refresh'])) {
    $_REQUEST['form_refresh'] = '';
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
if ($_REQUEST['form_from_date']) {
    $form_from_date = fixDate($_REQUEST['form_from_date'], $last_year);
}

$form_to_date   = fixDate($_REQUEST['form_to_date'], date('Y-m-d')); ?>
<html>
<head>

    <?php Header::setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker']); ?>
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js?v=<?php echo $v_js_includes; ?>"></script>

<script>
function checkSubmit() {
    document.forms[0].elements['form_refresh'].value = true;
    document.forms[0].submit();
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

<title><?php echo xlt('Patient Billing Summary by Date') ?></title>

<script>
  $(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
  });
</script>

</head>
<body class="skin-blue">
<h2><?php echo xlt('Patient Billing Summary'); ?></h2>
<form method='post' action='./pat_ledger.php' id='theform'>
<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
    <td width='35%'>
    <div class="float-left">
    <table class='text'>
        <tr>
      <td class='col-form-label'>
        <?php echo xlt('From'); ?>:
      </td>
      <td>
        <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr($form_from_date) ?>' title='yyyy-mm-dd'>
      </td>
      <td class='col-form-label'>
          &nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('To{{Range}}'); ?>:
      </td>
      <td>
        <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr($form_to_date) ?>' title='yyyy-mm-dd'>
      </td>
      </td>
        </tr>
    </table>
    </div>
  </td>
  <td align='left' class='align-middle h-100'>
    <table class="w-100 h-100">
        <tr>
            <td>
                <div style='margin-left: 15px'>
                    <a href='#' class='btn btn-primary' onclick="checkSubmit();" ><?php echo xlt('Submit'); ?></a>
                </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

    <?php
    $from_date = $form_from_date . ' 00:00:00';
    $to_date = $form_to_date . ' 23:59:59';
    if ($_REQUEST['form_refresh']) {
        $rows = array();
        $sqlBindArray = array();
        $query = "select b.code_type, b.code, b.code_text, b.pid, b.provider_id, " .
            "b.billed, b.payer_id, b.units, b.fee, b.bill_date, b.id, " .
            "ins.name, " .
            "fe.encounter, fe.date, fe.reason, fe.provider_id " .
            "FROM form_encounter AS fe " .
            "LEFT JOIN billing AS b ON b.pid=fe.pid AND b.encounter=fe.encounter " .
            "LEFT JOIN insurance_companies AS ins ON b.payer_id = ins.id " .
            "LEFT OUTER JOIN code_types AS c ON c.ct_key = b.code_type " .
            "WHERE fe.date >= ? AND fe.date <= ? AND fe.pid = ? ";
        array_push($sqlBindArray, $from_date, $to_date, $pid);

        $query .= "AND c.ct_proc = '1' ";
        $query .= "AND activity > 0 ORDER BY fe.date, fe.id ";
        $res = sqlStatement($query, $sqlBindArray);

        $patient = sqlQuery("SELECT * from patient_data WHERE pid=?", array($pid));
        $pat_dob = $patient['DOB'];
        $pat_name = $patient['fname'] . ' ' . $patient['lname'];
        ?>
<div id="report_header">
<table width="98%" class="border-0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="title"><?php echo xlt('Patient Billing Summary'); ?></td>
  </tr>
    <tr>
        <?php
        $title = xl('All Providers');
        ?>
    <td class="title" ><?php echo text($title); ?></td>
    </tr>
    <tr>
        <?php
            $title = xl('For Dates') . ': ' . $form_from_date . ' - ' . $form_to_date;
        ?>
    <td class="title"><?php echo text($title); ?></td>
    </tr>
</table>
<br/>
<table class="w-100 border-0" cellspacing="0" cellpadding="0">
  <tr>
    <td class='font-weight-bold'><?php echo xlt('Date')?>: <?php echo text(date('Y-m-d')); ?></td>
    <td class='font-weight-bold'><?php echo xlt('Patient')?>: <?php echo text($pat_name); ?></td>
    <td class='font-weight-bold'><?php echo xlt('DOB')?>: <?php echo text($pat_dob);?></td>
    <td class='font-weight-bold'> <?php echo xlt('ID')?>: <?php echo text($pid);?></td>
  </tr>
</table>
</div>
<div id="report_results">
<table class="table">
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
        $orow = 0;
        $prev_encounter_id = -1;
        $hdr_printed = false;
        $prev_row = array();
        while ($erow = sqlFetchArray($res)) {
            $print = '';
            $csv = '';
            if ($erow['encounter'] != $prev_encounter_id) {
                if ($prev_encounter_id != -1) {
                    $credits = GetAllCredits($prev_encounter_id, $pid);
                    if (count($credits) > 0) {
                        if (!$hdr_printed) {
                            PrintEncHeader(
                                $prev_row['date'],
                                $prev_row['reason'],
                                $prev_row['provider_id']
                            );
                        }

                        PrintCreditDetail($credits, $pid);
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
                $print = "<tr bgcolor='" . attr($bgcolor) . "'>";
                $print .= "<td class='detail'>" . text($erow['code']) . "</td>";
                $print .= "<td class='detail' colspan='2'>" . text($code_desc) . "</td>";
                $who = ($erow['name'] == '') ? xl('Self') : $erow['name'];
                $bill = substr($erow['bill_date'], 0, 10);
                if ($bill == '') {
                    $bill = 'unbilled';
                }

                $print .= "<td class='detail'>" . text($bill) . "&nbsp;/&nbsp;" . text($who) . "</td>";
                $print .= "<td class='detail text-right'>" . text($erow['units']) . "</td>";
                $print .= "<td class='detail text-right'>" . text(oeFormatMoney($erow['fee'])) . "</td>";
                $print .= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
                $print .= "</tr>\n";

                $total_units  += $erow['units'];
                $total_chg += $erow['fee'];
                $total_bal += $erow['fee'];
                $enc_units  += $erow['units'];
                $enc_chg += $erow['fee'];
                $enc_bal += $erow['fee'];
                $orow++;

                echo $print;
            }

            $prev_encounter_id = $erow['encounter'];
            $prev_row = $erow;
        }

        if ($prev_encounter_id != -1) {
            $credits = GetAllCredits($prev_encounter_id, $pid);
            if (count($credits) > 0) {
                if (!$hdr_printed) {
                    PrintEncHeader(
                        $prev_row['date'],
                        $prev_row['reason'],
                        $prev_row['provider_id']
                    );
                }

                PrintCreditDetail($credits, $pid);
            }

            if ($hdr_printed) {
                PrintEncFooter();
            }
        }

        // This is the end of the encounter/charge loop -
            $uac = GetAllUnapplied($pid, $from_date, $to_date);
        if (count($uac) > 0) {
            if ($orow) {
                $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
                echo "<tr class='bg-white'><td colspan='9'>&nbsp;</td></tr>\n";
            }

            PrintCreditDetail($uac, $pid, true);
        }

        if ($orow) {
            echo "<tr bgcolor='#DDFFFF'>\n";
            echo " <td colspan='2'>&nbsp;</td>";
            echo " <td class='font-weight-bold' colspan='2'>" . xlt("Grand Total") . "</td>\n";
            echo " <td class='font-weight-bold text-right'>" . text($total_units) . "</td>\n";
            echo " <td class='font-weight-bold text-right'>" . text(oeFormatMoney($total_chg)) . "</td>\n";
            echo " <td class='font-weight-bold text-right'>" . text(oeFormatMoney($total_pmt)) . "</td>\n";
            echo " <td class='font-weight-bold text-right'>" . text(oeFormatMoney($total_adj)) . "</td>\n";
            echo " <td class='font-weight-bold text-right'>" . text(oeFormatMoney($total_bal)) . "</td>\n";
            echo " </tr>\n";
            ?>
        </table>
      <tr><td>&nbsp;</td></tr><br /><br />
            <?php if ($GLOBALS['print_next_appointment_on_ledger'] == 1) {
                        $next_day = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
                        # add one day to date so it will not get todays appointment
                        $current_date2 = date('Y-m-d', $next_day);
                        $events = fetchNextXAppts($current_date2, $pid);
                        $next_appoint_date = oeFormatShortDate($events[0]['pc_eventDate']);
                        $next_appoint_time = substr($events[0]['pc_startTime'], 0, 5);
                if (strlen($events[0]['umname']) != 0) {
                    $next_appoint_provider = $events[0]['ufname'] . ' ' . $events[0]['umname'] . ' ' .  $events[0]['ulname'];
                } else {
                    $next_appoint_provider = $events[0]['ufname'] . ' ' .  $events[0]['ulname'];
                }

                if (strlen($next_appoint_time) != 0) {
                    ?>
      <tr>
        <td class="title"><?php echo xlt('Next Appointment Date') . ': ' . text($next_appoint_date) . ' ' . xlt('Time') . ' ' . text($next_appoint_time) . ' ' . xlt('Provider') . ' ' . text($next_appoint_provider); ?></td>
      </tr>

                    <?php
                }
            } // end ($GLOBALS['print_next_appointment_on_ledger'] == 1)
        } // end ($orow)
          echo "</div>\n";
    }


    if ($_REQUEST['form_refresh'] && $orow <= 0) {
        echo "<span style='font-size: 0.8125rem;'>";
        echo xlt('No matches found. Try search again.');
        echo "</span>";
        echo '<script>document.getElementById("report_results").style.display="none";</script>';
        echo '<script>document.getElementById("controls").style.display="none";</script>';
    }

    if (!$_REQUEST['form_refresh']) { ?>
    <div class='text'>
            <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
    </div>
        <?php
    }
    ?>
</form>
</body>

</html>
