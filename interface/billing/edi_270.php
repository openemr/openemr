<?php

/**
 * main file for the 270 batch creation.
 * This report is the batch report required for batch eligibility verification.
 *
 * This program creates the batch for the x12 270 eligibility file
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2010 MMF Systems, Inc
 * @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/patient.inc.php");
require_once "$srcdir/options.inc.php";
require_once("$srcdir/calendar.inc.php");
require_once("$srcdir/appointments.inc.php");

use OpenEMR\Billing\EDI270;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Element data seperator
$eleDataSep = "*";
// Segment Terminator
$segTer = "~";
// Component Element seperator
$compEleSep     = ":";

// filter conditions for the report and batch creation

$from_date      = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$to_date        = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = (!empty($_POST['form_facility'])) ? $_POST['form_facility'] : '';
$form_provider  = (!empty($_POST['form_users'])) ? $_POST['form_users'] : '';
$exclude_policy = (!empty($_POST['removedrows'])) ? $_POST['removedrows'] : '';
$x12_partner    = (!empty($_POST['form_x12'])) ? $_POST['form_x12'] : '';
$X12info        = EDI270::getX12Partner($x12_partner);

// grab appointments, sort by date and make unique to first upcoming appt by pid.
$appts = fetchAppointments($from_date, $to_date);
$appts = sortAppointments($appts);
$appts = unique_by_key($appts, 'pid');
$ids = [];
foreach ($appts as $eid) {
    $ids[] = $eid['pc_eid'];
}
//Set up the sql variable binding array (this prevents sql-injection attacks)
$sqlBindArray = array();

$ids = count($ids) > 0 ? implode(',', $ids) : "'0'";
$where  = "e.pc_eid in($ids) ";

if ($form_facility != "") {
    $where .= " AND f.id = ? ";
    array_push($sqlBindArray, $form_facility);
}

if ($form_provider != "") {
    $where .= " AND d.id = ? ";
    array_push($sqlBindArray, $form_provider);
}

if ($exclude_policy != "") {
    $arrayExplode = explode(",", $exclude_policy);
    $excludePlacemakers = "";
    $firstFlag = true;
    foreach ($arrayExplode as $processExclude) {
        // grab the string between the _ character and the ending ' character (and then drop these characters)
        $processExclude = strstr($processExclude, '_');
        $processExclude = substr($processExclude, 1, strlen($processExclude) - 2);
        array_push($sqlBindArray, $processExclude);
        if ($firstFlag) {
            $excludePlacemakers = "?";
            $firstFlag = false;
        } else {
            $excludePlacemakers .= ",?";
        }
    }
    $where .= " AND i.policy_number NOT IN ($excludePlacemakers)";
}
    $where .= " AND (i.policy_number is NOT NULL AND i.policy_number != '')";
    $where .= " GROUP BY p.pid ORDER BY c.name";
    $query = sprintf("SELECT e.pc_facility,
        e.pc_eid,
        p.lname,
        p.fname,
        p.mname,
        DATE_FORMAT(p.dob, '%%Y%%m%%d') as dob,
        p.ss,
        p.sex,
        p.pid,
        p.pubpid,
        i.subscriber_ss,
        i.policy_number,
        i.provider as payer_id,
        i.subscriber_relationship,
        i.subscriber_lname,
        i.subscriber_fname,
        i.subscriber_mname,
        DATE_FORMAT(i.subscriber_dob, '%%Y%%m%%d') as subscriber_dob,
        i.policy_number,
        i.subscriber_sex,
        DATE_FORMAT(i.date,'%%Y%%m%%d') as date,
        d.lname as provider_lname,
        d.fname as provider_fname,
        d.npi as provider_npi,
        d.upin as provider_pin,
        f.federal_ein as federal_ein,
        f.facility_npi as facility_npi,
        f.name as facility_name,
        c.cms_id as cms_id,
        c.eligibility_id as eligibility_id,
        c.name as payer_name
        FROM openemr_postcalendar_events AS e
        LEFT JOIN users AS d on (e.pc_aid is not null and e.pc_aid = d.id)
        LEFT JOIN facility AS f on (f.id = e.pc_facility)
        LEFT JOIN patient_data AS p ON p.pid = e.pc_pid
        LEFT JOIN insurance_data AS i ON (i.id =(SELECT id FROM insurance_data AS i WHERE pid = p.pid AND type = 'primary' ORDER BY date DESC LIMIT 1))
        LEFT JOIN insurance_companies as c ON (c.id = i.provider)
        WHERE %s ", $where);

    // Run the query
    $rslt = sqlStatement($query, $sqlBindArray);
    $res = [];
    while ($row = sqlFetchArray($rslt)) {
        foreach ($appts as $tmp) {
            if ((int)$tmp['pc_eid'] === (int)$row['pc_eid']) {
                $row['pc_eventDate'] = date("Ymd", strtotime($tmp['pc_eventDate']));
            }
        }
        $res[] = $row;
    }
    // Get the facilities information
    $facilities     = getUserFacilities($_SESSION['authUserID']);

    // Get the Providers information
    $providers      = EDI270::getUsernames();

    //Get the x12 partners information
    $clearinghouses = EDI270::getX12Partner();

    if (isset($_POST['form_xmit']) && !empty($_POST['form_xmit']) && $res) {
        $eFlag = !$GLOBALS['disable_eligibility_log'];
        // make the batch request
        $log = EDI270::requestRealTimeEligible($res, $X12info, $segTer, $compEleSep, $eFlag);
        $e = strpos($log, "Error:");
        if ($e !== false) {
            $log =  text(xlt("One or more transactions failed") .
                "\n" . $log . "\n");
        }
        if ($eFlag) {
            $fn = sprintf(
                'elig-log_%s_%s.txt',
                strtolower(str_replace(' ', '', $X12info['name'])),
                date("Y-m-d:H:i:s")
            );
            $log = str_replace('~', "~\r", $log);
            while (@ob_end_flush()) {
            }
            header('Content-Type: text/plain');
            header("Content-Length: " . strlen($log));
            header('Content-Disposition: attachment; filename="' . $fn . '"');
            ob_start();
            echo $log;
            exit();
        }
    }

    if (isset($_POST['form_savefile']) && !empty($_POST['form_savefile']) && $res) {
        header('Content-Type: text/plain');
        header(sprintf(
            'Content-Disposition: attachment; filename="batch-elig-270.%s.%s.txt"',
            strtolower(str_replace(' ', '', $X12info['name'])),
            date("Y-m-d:H:i:s")
        ));
        EDI270::printElig($res, $X12info, $segTer, $compEleSep);
        exit;
    }

// unique multidimensional array by key
    function unique_by_key($source, $key)
    {
        $i = 0;
        $rtn_array = array();
        $key_array = array();

        foreach ($source as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $rtn_array[$i] = $val;
            }
            $i++;
        }
        return $rtn_array;
    }
    ?>

<html>

    <head>

        <title><?php echo xlt('Eligibility 270 Inquiry Batch'); ?></title>

        <?php Header::setupHeader('datetime-picker'); ?>

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
                #report_results table {
                   margin-top: 0px;
                }
            }

            /* specifically exclude some from the screen */
            @media screen {
                #report_parameters_daterange {
                    visibility: hidden;
                    display: none;
                }
            }

        </style>

        <script>

            var stringDelete = <?php echo xlj('Do you want to remove this record?'); ?>;
            var stringBatch  = <?php echo xlj('Please select X12 partner, required to create the 270 batch'); ?>;

            // for form refresh

            function refreshme() {
                document.forms[0].submit();
            }

            //  To delete the row from the reports section
            function deletetherow(id){
                var suredelete = confirm(stringDelete);
                if(suredelete == true){
                    document.getElementById('PR'+id).style.display="none";
                    if(document.getElementById('removedrows').value == ""){
                        document.getElementById('removedrows').value = "'" + id + "'";
                    }else{
                        document.getElementById('removedrows').value = document.getElementById('removedrows').value + ",'" + id + "'";

                    }
                }

            }

            //  To validate the batch file generation - for the required field [clearing house/x12 partner]
            function validate_batch(eFlag) {
                if (document.getElementById('form_x12').value == '') {
                    alert(stringBatch);
                    return false;
                }
                else {
                    if (eFlag === true) {
                        document.getElementById('form_xmit').value = "true";
                    } else {
                        document.getElementById('form_savefile').value = "true";
                    }

                    document.theform.submit();
                }
            }

            // To Clear the hidden input field

            function validate_policy()
            {
                document.getElementById('removedrows').value = "";
                document.getElementById('form_savefile').value = "";
                document.getElementById('form_xmit').value = "";
                return true;
            }

            // To toggle the clearing house empty validation message
            function toggleMessage(id,x12){

                var spanstyle = String();

                spanstyle       = document.getElementById(id).style.visibility;
                selectoption    = document.getElementById(x12).value;

                if(selectoption != '')
                {
                    document.getElementById(id).style.visibility = "hidden";
                }
                else
                {
                    document.getElementById(id).style.visibility = "visible";
                    document.getElementById(id).style.display = "inline";
                }
                return true;

            }

            $(function () {
                $('.datepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = false; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });

        </script>

    </head>
    <body class="body_top">

        <!-- Required for the popup date selectors -->
        <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

        <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Eligibility 270 Inquiry Batch'); ?></span>

        <div id="report_parameters_daterange">
            <?php echo text(oeFormatShortDate($from_date)) . " &nbsp; " . xlt('to{{Range}}') . "&nbsp; " . text(oeFormatShortDate($to_date)); ?>
        </div>

        <form method='post' name='theform' id='theform' action='edi_270.php' onsubmit="return top.restoreSession()">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="hidden" name="removedrows" id="removedrows" value="">
            <div id="report_parameters">
                <table>
                    <tr>
                        <td width='550px'>
                            <div style='float:left'>
                                <table class='text'>
                                    <tr>
                                        <td class='col-form-label'>
                                            <?php echo xlt('From'); ?>:
                                        </td>
                                        <td>
                                           <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
                                        </td>
                                        <td class='col-form-label'>
                                            <?php echo xlt('To{{Range}}'); ?>:
                                        </td>
                                        <td>
                                           <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                                        </td>
                                        <td>&nbsp;</td>
                                    </tr>

                                    <tr>
                                        <td class='col-form-label'>
                                            <?php echo xlt('Facility'); ?>:
                                        </td>
                                        <td>
                                            <?php dropdown_facility($form_facility, 'form_facility', false);  ?>
                                        </td>
                                        <td class='col-form-label'>
                                            <?php echo xlt('Provider'); ?>:
                                        </td>
                                        <td>
                                            <select name='form_users' class='form-control' onchange='form.submit();'>
                                                <option value=''>-- <?php echo xlt('All'); ?> --</option>
                                                <?php foreach ($providers as $user) : ?>
                                                    <option value='<?php echo attr($user['id']); ?>'
                                                        <?php echo $form_provider == $user['id'] ? " selected " : null; ?>
                                                    ><?php echo text($user['fname'] . " " . $user['lname']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>&nbsp;
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class='col-form-label'>
                                            <?php echo xlt('X12 Partner'); ?>:
                                        </td>
                                        <td colspan='5'>
                                            <select name='form_x12' id='form_x12' class='form-control' onchange='return toggleMessage("emptyVald","form_x12");'>
                                                <option value=''>--<?php echo xlt('select'); ?>--</option>
                                                <?php
                                                if (isset($clearinghouses) && !empty($clearinghouses)) {
                                                    foreach ($clearinghouses as $clearinghouse) {
                                                        if (!empty($clearinghouse['id'])) {
                                                            echo "<option value='" . attr($clearinghouse['id']) . "'" .
                                                                (!empty($X12info['id']) && ($clearinghouse['id'] == $X12info['id']) ? " selected " : '') . ">" . text($clearinghouse['name']) . "</option>";
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                                <span id='emptyVald' class='text-danger' style='font-size:12px;visibility: <?php echo (!empty($X12info['id'])) ? "hidden" : ""; ?>'> *
                                                    <?php echo xlt('Clearing house info required for EDI 270 batch creation.'); ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        <td align='left' valign='middle' height="100%">
                            <table style='border-left:1px solid; width:100%; height:100%' >
                                <tr>
                                    <td>
                                        <div class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href='#' class='btn btn-secondary btn-refresh' onclick='validate_policy(); $("#theform").submit();'>
                                                    <?php echo xlt('Refresh'); ?>
                                                </a>
                                                <a href='#' class='btn btn-secondary btn-transmit' onclick='return validate_batch(false);'>
                                                    <?php echo xlt('Create batch'); ?>
                                                    <input type='hidden' name='form_savefile' id='form_savefile' value=''></input>

                                                    <?php if ($GLOBALS['enable_eligibility_requests']) {
                                                        echo "<a href='#' class='btn btn-secondary btn-transmit' onclick='return validate_batch(true);'>" . xlt('Request Eligibility') . "</a>\n";
                                                    }
                                                    ?>
                                                    <input type='hidden' name='form_xmit' id='form_xmit' value=''></input>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <div class='text'>
                <?php echo xlt('Please choose date range criteria above, and click Refresh to view results.'); ?>
            </div>

        </form>

        <?php
        if ($res) {
            EDI270::showElig($res, $X12info, $segTer, $compEleSep);
        }
        ?>
    </body>

    <script>
        <?php
        if (!empty($alertmsg)) {
            echo " alert(" . js_escape($alertmsg) . ");\n";
        } ?>
    </script>

</html>
