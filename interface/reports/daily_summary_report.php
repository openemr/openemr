<?php
/*
 *  Daily Summary Report. (/interface/reports/daily_summary_report.php
 *  
 *
 *  This report shows date wise numbers of the Appointments Scheduled,
 *  New Patients, Visited patients, Total Charges, Total Co-pay and Balance amount for the selected facility.
 * 
 * Copyright (C) ....
 * 
 * LICENSE: This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 3 
 * of the License, or (at your option) any later version. 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
 * GNU General Public License for more details. 
 * You should have received a copy of the GNU General Public License 
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;. 
 * 
 * @package OpenEMR 
 * @author Rishabh Software
 * @link http://www.open-emr.org 
 *
 */

$fake_register_globals = false;
$sanitize_all_escapes = true;

require_once("../globals.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";


$selectedFromDate = isset($_POST['form_from_date'])?$_POST['form_from_date']:date('Y-m-d'); // From date filter
$selectedToDate = isset($_POST['form_to_date'])?$_POST['form_to_date']:date('Y-m-d');     // To date filter
$selectedFacility = isset($_POST['form_facility'])?$_POST['form_facility']:"";  // facility filter
 
$from_date = fixDate($selectedFromDate, date('Y-m-d'));
$to_date = fixDate($selectedToDate, date('Y-m-d'));

?>

<html>
    <head>
        <?php html_header_show(); ?>
        <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
        <script type="text/javascript" src="../../library/textformat.js"></script>
        <script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
        <script type="text/javascript">


            var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

            function submitForm() {
                var fromDate = $("#form_from_date").val();
                var toDate = $("#form_to_date").val();
                
                var dayDifference =  Math.floor(( Date.parse(toDate) - Date.parse(fromDate) ) / 86400000);
                
                if (fromDate === '') {
                    alert("Please select From date");
                    return false;
                }
                if (toDate === '') {
                    alert("Please select To date");
                    return false;
                }
                if(dayDifference > 31){
                    alert("Please select date range within month");
                    return false;
                }
                if (Date.parse(fromDate) > Date.parse(toDate)) {
                    alert("From date should be less than To date");
                    return false;
                }
                else {
                    $("#form_refresh").attr("value", "true");
                    $("#report_form").submit();
                }
            }
        </script>

    </head>

    <body class="body_top">

        <span class='title'><?php echo xlt('Daily'); ?> - <?php echo xlt('Summary').' '.xlt('Reports'); ?></span>
        <!-- start of search parameters --> 
        <form method='post' name='report_form' id='report_form' action='' onsubmit='return top.restoreSession()'>
            <div id="report_parameters">
                <table class="tableonly">
                    <tr>
                        <td width='750px'>
                            <div style='float: left'>
                                <table class='text'>
                                    <tr>
                                        <td class='label'><?php echo xlt('Facility'); ?>:</td>
                                        <td><?php dropdown_facility($selectedFacility, 'form_facility'); ?></td>				
                                        <td class='label'><?php echo xlt('From'); ?>:</td>
                                        <td>
                                            <input type='text' name='form_from_date' id="form_from_date"
                                                   size='10' value='<?php echo attr($from_date) ?>'
                                                   onkeyup='datekeyup(this, mypcc)' onblur='dateblur(this, mypcc)'
                                                   title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
                                                   align='absbottom' width='24' height='22' id='img_from_date'
                                                   border='0' alt='[?]' style='cursor: pointer'
                                                   title='<?php echo xlt('Click here to choose a date'); ?>'>
                                        </td>
                                        <td class='label'><?php echo xlt('To'); ?>:</td>
                                        <td>
                                            <input type='text' name='form_to_date' id="form_to_date"
                                                   size='10' value='<?php echo attr($to_date) ?>'
                                                   onkeyup='datekeyup(this, mypcc)' onblur='dateblur(this, mypcc)'
                                                   title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
                                                   align='absbottom' width='24' height='22' id='img_to_date'
                                                   border='0' alt='[?]' style='cursor: pointer'
                                                   title='<?php echo xlt('Click here to choose a date'); ?>'>
                                        </td>
                                </table>
                            </div>
                        </td>
                        <td align='left' valign='middle' height="100%">
                            <table style='border-left: 1px solid; width: 100%; height: 100%'>
                                <tr>
                                    <td>
                                        <div style='margin-left: 15px'>
                                            <a href='#' class='css_button' onclick='return submitForm();'>
                                                <span> <?php echo xlt('Submit'); ?> </span>
                                            </a> 
                                            <a href='' class="css_button" id='new0' onClick=" return top.window.parent.left_nav.loadFrame2('new0', 'RTop', 'reports/daily_summary_report.php')">
                                                <span><?php echo htmlspecialchars(xlt('Reset'), ENT_QUOTES); ?></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='form_refresh' id='form_refresh' value='' />
            </div>
        </form>
        <!-- end of search parameters --> 

        <?php
        $dateSet = $facilitySet = 0;
        if (!empty($selectedFromDate) && !empty($selectedToDate)) {
            $dateSet = 1;
        }
        if (isset($selectedFacility) && !empty($selectedFacility)) {
            $facilitySet = 1;
        }

        // define all the variables as initial blank array
        $facilities = $totalAppointment = $totalNewPatient = $totalVisit = $totalPayment = $dailySummaryReport = $totalPaid = array();

        // define all the where condition variable as initial value set 1=1
        $whereAppointmentConditions = $whereTotalVisitConditions = $whereTotalPaymentConditions = $wherePaidConditions = $whereNewPatientConditions = '1 = 1 ';

        // fetch all facility from the table
        $facilityReacords = sqlStatement("SELECT `id`,`name` from facility");
        while ($facilityList = sqlFetchArray($facilityReacords)) {
            if (1 === $facilitySet && $facilityList['id'] == $selectedFacility) {
                $facilities[$facilityList['id']] = $facilityList['name'];
            }
            if (empty($selectedFacility)) {
                $facilities[$facilityList['id']] = $facilityList['name'];
            }
        }


        // make all condition on by default today's date
        if ($dateSet != 1 && $facilitySet != 1) {
            $whereAppointmentConditions .= ' AND `c`.`pc_eventDate` = CURDATE()';
            $whereTotalVisitConditions .= ' AND DATE(`fc`.`date`) = CURDATE()';
            $whereTotalPaymentConditions .= ' AND DATE(`b`.`date`) = CURDATE()';
            $wherePaidConditions .= ' AND DATE(`p`.`dtime`) = CURDATE()';
            $whereNewPatientConditions .= ' AND DATE(`OPE`.`pc_eventDate`) = CURDATE()';
        }

        // if search based on facility then append condition for facility search 
        if (1 === $facilitySet) {
            $whereAppointmentConditions .= ' AND `f`.`id` =' . $selectedFacility;
            $whereTotalVisitConditions .= ' AND `f`.`id` =' . $selectedFacility;
            $whereTotalPaymentConditions .= ' AND `f`.`id` =' . $selectedFacility;
            $wherePaidConditions .= ' AND `f`.`id` =' . $selectedFacility;
            $whereNewPatientConditions .= ' AND `f`.`id` =' . $selectedFacility;
        }

        // if date range wise search then append condition for date search 
        if (1 === $dateSet) {
            $whereAppointmentConditions .= ' AND (`c`.`pc_eventDate`) BETWEEN' . "'" . $selectedFromDate . "'" . ' AND ' . "'" . $selectedToDate . "'";
            $whereTotalVisitConditions .= ' AND DATE(`fc`.`date`) BETWEEN' . "'" . $selectedFromDate . "'" . ' AND ' . "'" . $selectedToDate . "'";
            $whereTotalPaymentConditions .= ' AND DATE(`b`.`date`) BETWEEN' . "'" . $selectedFromDate . "'" . ' AND ' . "'" . $selectedToDate . "'";
            $wherePaidConditions .= ' AND DATE(`p`.`dtime`) BETWEEN' . "'" . $selectedFromDate . "'" . ' AND ' . "'" . $selectedToDate . "'";
            $whereNewPatientConditions .= ' AND DATE(`OPE`.`pc_eventDate`) BETWEEN' . "'" . $selectedFromDate . "'" . ' AND ' . "'" . $selectedToDate . "'";
        }

        //Count Total Appointments
        $totalAppointmentSql = sqlStatement("SELECT `c`.`pc_eventDate` ,`f`.`name` AS facility_Name, count( * ) AS totalAppointment
                                                FROM `openemr_postcalendar_events` AS c
                                                LEFT JOIN `facility` AS f ON ( `c`.`pc_facility` = `f`.`id` )
                                                WHERE  $whereAppointmentConditions GROUP BY facility_Name, `c`.`pc_eventDate` ORDER BY `c`.`pc_eventDate` ASC");

        while ($totalAppointmentRecord = sqlFetchArray($totalAppointmentSql)) {
            $totalAppointment[$totalAppointmentRecord['pc_eventDate']][$totalAppointmentRecord['facility_Name']]['appointments'] = $totalAppointmentRecord['totalAppointment'];
        }

        //Count Total New Patient
        $newPatientSql = sqlStatement("SELECT `OPE`.`pc_eventDate` , `f`.`name` AS facility_Name , count( * ) AS totalNewPatient
                                        FROM `patient_data` AS PD
                                        LEFT JOIN `openemr_postcalendar_events` AS OPE ON ( `OPE`.`pc_pid` = `PD`.`pid` )
                                        LEFT JOIN `facility` AS f ON ( `OPE`.`pc_facility` = `f`.`id` )
                                        WHERE `OPE`.`pc_title` = 'New Patient'
                                        AND  $whereNewPatientConditions
                                        GROUP BY `f`.`id` , `OPE`.`pc_eventDate`
                                        ORDER BY `OPE`.`pc_eventDate` ASC");

        while ($totalNewPatientRecord = sqlFetchArray($newPatientSql)) {
            $totalNewPatient[$totalNewPatientRecord['pc_eventDate']][$totalNewPatientRecord['facility_Name']]['newPatient'] = $totalNewPatientRecord['totalNewPatient'];
        }

        //Count Total Visit
        $totalVisitSql = sqlStatement("SELECT DATE( `fc`.`date` ) AS Date,`f`.`name` AS facility_Name, count( * ) AS totalVisit
                                                                    FROM `form_encounter` AS fc
                                                                    LEFT JOIN `facility` AS f ON ( `fc`.`facility_id` = `f`.`id` )
                                                                    WHERE $whereTotalVisitConditions
                                                                    GROUP BY `fc`.`facility_id`, DATE( `fc`.`date` ) ORDER BY DATE( `fc`.`date` ) ASC");
        while ($totalVisitRecord = sqlFetchArray($totalVisitSql)) {
            $totalVisit[$totalVisitRecord['Date']][$totalVisitRecord['facility_Name']]['visits'] = $totalVisitRecord['totalVisit'];
        }

        //Count Total Payments for only active records i.e. activity = 1
        $totalPaymetsSql = sqlStatement("SELECT DATE( `b`.`date` ) AS Date, `f`.`name` AS facilityName, SUM( `b`.`fee` ) AS totalpayment
                                                                    FROM `facility` AS f
                                                                    LEFT JOIN `form_encounter` AS fe ON ( `fe`.`facility_id` = `f`.`id` )
                                                                    LEFT JOIN `billing` AS b ON ( `fe`.`encounter` = `b`.`encounter` )
                                                                    WHERE `b`.`activity` =1 AND 
                                                                    $whereTotalPaymentConditions
                                                                    GROUP BY `b`.`encounter` , Date ORDER BY Date ASC");


        while ($totalPaymentRecord = sqlFetchArray($totalPaymetsSql)) {
            $totalPayment[$totalPaymentRecord['Date']][$totalPaymentRecord['facilityName']]['payments'] += $totalPaymentRecord['totalpayment'];
        }

        // total paid amount
        $totalPaidAmountSql = sqlStatement("SELECT DATE( `p`.`dtime` ) AS Date,`f`.`name` AS facilityName, SUM( `p`.`amount1` ) AS totalPaidAmount
                                                                        FROM `facility` AS f
                                                                        LEFT JOIN `form_encounter` AS fe ON ( `fe`.`facility_id` = `f`.`id` )
                                                                        LEFT JOIN `payments` AS p ON ( `fe`.`encounter` = `p`.`encounter` )
                                                                        WHERE $wherePaidConditions
                                                                        GROUP BY `p`.`encounter`, Date ORDER BY Date ASC");

        while ($totalPaidRecord = sqlFetchArray($totalPaidAmountSql)) {
            $totalPaid[$totalPaidRecord['Date']][$totalPaidRecord['facilityName']]['paidAmount'] += $totalPaidRecord['totalPaidAmount'];
        }

        // merge all array recursive in to one array
        $dailySummaryReport = array_merge_recursive($totalAppointment, $totalNewPatient, $totalVisit, $totalPayment, $totalPaid);
        ?>

        <div id="report_results" style="font-size: 12px">
            <?php  echo '<b>'.xlt('From', 'e').'</b> '. $from_date . ' <b>'.xlt('To', 'e').'</b> '. $to_date; ?>

            <table class="flowboard" cellpadding='5' cellspacing='2' id="ds_report">
                <tr class="head">

                    <td><?php xl('Date', 'e'); ?></td>
                    <td><?php xl('Facility', 'e'); ?></td>
                    <td><?php xl('Scheduled Appointments', 'e'); ?></td>
                    <td><?php xl('New Patients', 'e'); ?></td>
                    <td><?php xl('Visited Patients', 'e'); ?></td>
                    <td><?php xl('Total Charges', 'e'); ?></td>
                    <td><?php xl('Total Co-Pay', 'e'); ?></td>
                    <td><?php xl('Balance Payment', 'e'); ?></td>
                </tr>
 <?php       if (count($dailySummaryReport) > 0) { // check if daily summary array has value
                    foreach ($dailySummaryReport as $date => $dataValue) { //   daily summary array which consists different/dynamic values
                        foreach ($facilities as $facility) { // facility array 
                            if (isset($dataValue[$facility])) {
                                ?>
                                <tr>
                                    <td><?php echo $date ?></td>
                                    <td><?php echo $facility; ?></td>
                                    <td><?php echo isset($dataValue[$facility]['appointments']) ? $dataValue[$facility]['appointments'] : 0; ?></td>
                                    <td><?php echo isset($dataValue[$facility]['newPatient']) ? $dataValue[$facility]['newPatient'] : 0; ?></td>
                                    <td><?php echo isset($dataValue[$facility]['visits']) ? $dataValue[$facility]['visits'] : 0; ?></td>
                                    <td align="right"><?php echo isset($dataValue[$facility]['payments']) ? number_format($dataValue[$facility]['payments'], 2) : 0; ?></td>
                                    <td align="right"><?php echo isset($dataValue[$facility]['paidAmount']) ? number_format($dataValue[$facility]['paidAmount'], 2) : 0; ?></td>
                                    <td align="right">
                                        <?php
                                        if (isset($dataValue[$facility]['payments']) || isset($dataValue[$facility]['paidAmount'])) {
                                            $dueAmount = number_format(str_replace(",", "", $dataValue[$facility]['payments']) - str_replace(",", "", $dataValue[$facility]['paidAmount']), 2);
                                        } else {
                                             $dueAmount = number_format(0, 2);
                                        }
                                         echo $dueAmount;
                                        ?>
                                    </td>
                                </tr>
                                <?php
                                if (count($dailySummaryReport) > 0) { // calculate the total count of the appointments, new patient,visits, payments, paid amount and due amount
                                    $totalAppointments += $dataValue[$facility]['appointments'];
                                    $totalNewRegisterPatient += $dataValue[$facility]['newPatient'];
                                    $totalVisits += $dataValue[$facility]['visits'];
                                    $totalPayments += str_replace(",", "", $dataValue[$facility]['payments']);
                                    $totalPaidAmount += str_replace(",", "", $dataValue[$facility]['paidAmount']);
                                    $totalDueAmount += $dueAmount;
                                }
                            }
                        }
                    }
                        ?>
                <!--display total count-->
                    <tr class="totalrow">
                            <td>Total</td>
                            <td>-</td>
                            <td><?php echo $totalAppointments; ?></td>
                            <td><?php echo $totalNewRegisterPatient; ?></td>
                            <td><?php echo $totalVisits; ?></td>
                            <td align="right"><?php echo number_format($totalPayments, 2); ?></td>
                            <td align="right"><?php echo number_format($totalPaidAmount, 2); ?></td>
                            <td align="right"><?php echo number_format($totalDueAmount, 2); ?></td>
                        </tr>
                        <?php
                    }else { // if there are no records then display message
                        ?>
                        <tr>
                            <td colspan="8" style="text-align:center;font-weight:bold;"> <?php echo xl("There are no record(s) found."); ?></td>
                        </tr>
                    <?php } ?>

            </table>
        </div>
    </body>

    <!-- stuff for the popup calendar -->
    <style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
    <script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
    <?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
    <script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
    <script type="text/javascript">
        Calendar.setup({inputField: "form_from_date", ifFormat: "%Y-%m-%d", button: "img_from_date"});
        Calendar.setup({inputField: "form_to_date", ifFormat: "%Y-%m-%d", button: "img_to_date"});
    </script>

</html>