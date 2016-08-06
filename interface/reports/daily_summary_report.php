<?php
/*
 *  Daily Summary Report. (/interface/reports/daily_summary_report.php)
 *  
 *
 *  This report shows date wise numbers of the Appointments Scheduled,
 *  New Patients, Visited patients, Total Charges, Total Co-pay and Balance amount for the selected facility & providers wise.
 * 
 * Copyright (C) 2016 Rishabh Software
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


$selectedFromDate = isset($_POST['form_from_date']) ? $_POST['form_from_date'] : date('Y-m-d'); // From date filter
$selectedToDate = isset($_POST['form_to_date']) ? $_POST['form_to_date'] : date('Y-m-d');   // To date filter
$selectedFacility = isset($_POST['form_facility']) ? $_POST['form_facility'] : "";  // facility filter
$selectedProvider = isset($_POST['form_provider']) ? $_POST['form_provider'] : "";  // provider filter

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

                if (fromDate === '') {
                    alert("<?php echo xls('Please select From date'); ?>");
                    return false;
                }
                if (toDate === '') {
                    alert("<?php echo xls('Please select To date'); ?>");
                    return false;
                }
                if (Date.parse(fromDate) > Date.parse(toDate)) {
                    alert("<?php echo xls('From date should be less than To date'); ?>");
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

        <span class='title'><?php echo xlt('Daily Summary Report'); ?></span>
        <!-- start of search parameters --> 
        <form method='post' name='report_form' id='report_form' action='' onsubmit='return top.restoreSession()'>
            <div id="report_parameters">
                <table class="tableonly">
                    <tr>
                        <td width='745px'>
                            <div style='float: left'>
                                <table class='text'>
                                    <tr>
                                        <td class='label'><?php echo xlt('Facility'); ?>:</td>
                                        <td><?php dropdown_facility($selectedFacility, 'form_facility', false); ?></td>				
                                        <td class='label'><?php echo xlt('From'); ?>:</td>
                                        <td>
                                            <input type='text' name='form_from_date' id="form_from_date"
                                                   size='10' value='<?php echo attr($from_date) ?>'
                                                   onkeyup='datekeyup(this, mypcc)' onblur='dateblur(this, mypcc)'
                                                   title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
                                                   align='absbottom' width='24' height='22' id='img_from_date'
                                                   border='0' alt='[?]' style='cursor: pointer'
                                                   title='<?php echo xla('Click here to choose a date'); ?>'>
                                        </td>
                                        <td class='label'><?php echo xlt('To'); ?>:</td>
                                        <td>
                                            <input type='text' name='form_to_date' id="form_to_date"
                                                   size='10' value='<?php echo attr($to_date) ?>'
                                                   onkeyup='datekeyup(this, mypcc)' onblur='dateblur(this, mypcc)'
                                                   title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
                                                   align='absbottom' width='24' height='22' id='img_to_date'
                                                   border='0' alt='[?]' style='cursor: pointer'
                                                   title='<?php echo xla('Click here to choose a date'); ?>'>
                                        </td>
                                </table>
                            </div>
                        </td>
                        <td class='label'><?php echo xlt('Provider'); ?>:</td>
                        <td>
                            <?php
                            generate_form_field(array('data_type' => 10, 'field_id' => 'provider',
                                'empty_title' => '-- All Providers --'), $selectedProvider);
                            ?>
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
                                               <span><?php echo xlt('Reset'); ?></span>
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
        $whereTotalVisitConditions = $whereTotalPaymentConditions = $wherePaidConditions = $whereNewPatientConditions = '1 = 1 ';

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

        // define provider and facility as null
        $providerID = $facilityID = NULL;
        // define all the bindarray variables as initial blank array
        $sqlBindArrayAppointment = $sqlBindArrayTotalVisit = $sqlBindArrayTotalPayment = $sqlBindArrayPaid = $sqlBindArrayNewPatient = array();

        // make all condition on by default today's date
        if ($dateSet != 1 && $facilitySet != 1) {
            $whereNewPatientConditions .= ' AND DATE(`OPE`.`pc_eventDate`) = ? ';
            array_push($sqlBindArrayNewPatient, date("Y-m-d"));
            $whereTotalVisitConditions .= ' AND DATE(`fc`.`date`) = ? ';
            array_push($sqlBindArrayTotalVisit, date("Y-m-d"));
            $whereTotalPaymentConditions .= ' AND DATE(`b`.`date`)  = ? ';
            array_push($sqlBindArrayTotalPayment, date("Y-m-d"));
            $wherePaidConditions .= ' AND DATE(`p`.`dtime`)  = ? ';
            array_push($sqlBindArrayPaid, date("Y-m-d"));
        }

        // if search based on facility then append condition for facility search 
        if (1 === $facilitySet) {
            $facilityID = $selectedFacility;
            $whereNewPatientConditions .= ' AND `f`.`id` = ?';
            array_push($sqlBindArrayNewPatient, $selectedFacility);
            $whereTotalVisitConditions .= ' AND `f`.`id` = ?';
            array_push($sqlBindArrayTotalVisit, $selectedFacility);
            $whereTotalPaymentConditions .= ' AND `f`.`id` = ?';
            array_push($sqlBindArrayTotalPayment, $selectedFacility);
            $wherePaidConditions .= ' AND `f`.`id` = ?';
            array_push($sqlBindArrayPaid, $selectedFacility);
        }

        // if date range wise search then append condition for date search 
        if (1 === $dateSet) {
            $whereNewPatientConditions .= ' AND DATE(`OPE`.`pc_eventDate`) BETWEEN ? AND ?';
            array_push($sqlBindArrayNewPatient, $selectedFromDate, $selectedToDate);
            $whereTotalVisitConditions .= ' AND DATE(`fc`.`date`) BETWEEN ? AND ?';
            array_push($sqlBindArrayTotalVisit, $selectedFromDate, $selectedToDate);
            $whereTotalPaymentConditions .= ' AND DATE(`b`.`date`) BETWEEN ? AND ?';
            array_push($sqlBindArrayTotalPayment, $selectedFromDate, $selectedToDate);
            $wherePaidConditions .= ' AND DATE(`p`.`dtime`) BETWEEN ? AND ?';
            array_push($sqlBindArrayPaid, $selectedFromDate, $selectedToDate);
        }

        // if provider selected then append condition for provider
        if (isset($selectedProvider) && !empty($selectedProvider)) {
            $providerID = $selectedProvider;
            $whereNewPatientConditions .= ' AND `OPE`.`pc_aid` = ?';
            array_push($sqlBindArrayNewPatient, $selectedProvider);
            $whereTotalVisitConditions .= ' AND `fc`.`provider_id` = ?';
            array_push($sqlBindArrayTotalVisit, $selectedProvider);
            $whereTotalPaymentConditions .= ' AND `fe`.`provider_id` = ?';
            array_push($sqlBindArrayTotalPayment, $selectedProvider);
            $wherePaidConditions .= ' AND `fe`.`provider_id` = ?';
            array_push($sqlBindArrayPaid, $selectedProvider);
        }

        // pass last parameter as Boolean,  which is getting the facility name in the resulted array 
        $totalAppointmentSql = fetchAppointments($from_date, $to_date, null, $providerID, $facilityID);
        if (count($totalAppointmentSql) > 0) { // check if $totalAppointmentSql array has value
            foreach ($totalAppointmentSql as $appointment) {
                
                $eventDate = $appointment['pc_eventDate'];
                $facility = $appointment['name'];
                $providerName = $appointment['ufname'] . ' ' . $appointment['ulname'];

                // initialize each level of the data structure if it doesn't already exist
                if (!isset($totalAppointment[$eventDate])) {
                    $totalAppointment[$eventDate] = [];
                }
                if (!isset($totalAppointment[$eventDate][$facility])) {
                    $totalAppointment[$eventDate][$facility] = [];
                }
                if (!isset($totalAppointment[$eventDate][$facility][$providerName])) {
                    $totalAppointment[$eventDate][$facility][$providerName] = [];
                }
                // initialize the number of appointment to 0
                if (!isset($totalAppointment[$eventDate][$facility][$providerName]['appointments'])) {
                    $totalAppointment[$eventDate][$facility][$providerName]['appointments'] = 0;
                }
                // increment the number of appointments
                $totalAppointment[$eventDate][$facility][$providerName]['appointments']++;
            }
        }
        
        //Count Total New Patient
        $newPatientSql = sqlStatement("SELECT `OPE`.`pc_eventDate` , `f`.`name` AS facility_Name , count( * ) AS totalNewPatient, `PD`.`providerID`, CONCAT( `u`.`fname`, ' ', `u`.`lname` ) AS provider_name
                                        FROM `patient_data` AS PD
                                        LEFT JOIN `openemr_postcalendar_events` AS OPE ON ( `OPE`.`pc_pid` = `PD`.`pid` )
                                        LEFT JOIN `facility` AS f ON ( `OPE`.`pc_facility` = `f`.`id` )
                                        LEFT JOIN `users` AS u ON ( `OPE`.`pc_aid` = `u`.`id` )
                                        WHERE `OPE`.`pc_title` = 'New Patient'
                                        AND  $whereNewPatientConditions
                                        GROUP BY `f`.`id` , `OPE`.`pc_eventDate`,provider_name
                                        ORDER BY `OPE`.`pc_eventDate` ASC", $sqlBindArrayNewPatient);



        while ($totalNewPatientRecord = sqlFetchArray($newPatientSql)) {
            $totalNewPatient[$totalNewPatientRecord['pc_eventDate']][$totalNewPatientRecord['facility_Name']][$totalNewPatientRecord['provider_name']]['newPatient'] = $totalNewPatientRecord['totalNewPatient'];
        }

        //Count Total Visit
        $totalVisitSql = sqlStatement("SELECT DATE( `fc`.`date` ) AS Date,`f`.`name` AS facility_Name, count( * ) AS totalVisit, `fc`.`provider_id`, CONCAT( `u`.`fname`, ' ', `u`.`lname` ) AS provider_name
                                                                    FROM `form_encounter` AS fc
                                                                    LEFT JOIN `facility` AS f ON ( `fc`.`facility_id` = `f`.`id` )
                                                                    LEFT JOIN `users` AS u ON ( `fc`.`provider_id` = `u`.`id` )
                                                                    WHERE $whereTotalVisitConditions
                                                                    GROUP BY `fc`.`facility_id`, DATE( `fc`.`date` ),provider_name ORDER BY DATE( `fc`.`date` ) ASC", $sqlBindArrayTotalVisit);

        while ($totalVisitRecord = sqlFetchArray($totalVisitSql)) {
            $totalVisit[$totalVisitRecord['Date']][$totalVisitRecord['facility_Name']][$totalVisitRecord['provider_name']]['visits'] = $totalVisitRecord['totalVisit'];
        }

        //Count Total Payments for only active records i.e. activity = 1
        $totalPaymetsSql = sqlStatement("SELECT DATE( `b`.`date` ) AS Date, `f`.`name` AS facilityName, SUM( `b`.`fee` ) AS totalpayment, `fe`.`provider_id`, CONCAT( `u`.`fname`, ' ', `u`.`lname` ) AS provider_name
                                                                    FROM `facility` AS f
                                                                    LEFT JOIN `form_encounter` AS fe ON ( `fe`.`facility_id` = `f`.`id` )
                                                                    LEFT JOIN `billing` AS b ON ( `fe`.`encounter` = `b`.`encounter` )
                                                                    LEFT JOIN `users` AS u ON ( `fe`.`provider_id` = `u`.`id` )
                                                                    WHERE `b`.`activity` =1 AND 
                                                                    $whereTotalPaymentConditions
                                                                    GROUP BY `b`.`encounter`,Date,provider_name ORDER BY Date ASC", $sqlBindArrayTotalPayment);

        while ($totalPaymentRecord = sqlFetchArray($totalPaymetsSql)) {
            $totalPayment[$totalPaymentRecord['Date']][$totalPaymentRecord['facilityName']][$totalPaymentRecord['provider_name']]['payments'] += $totalPaymentRecord['totalpayment'];
        }

        // total paid amount
        $totalPaidAmountSql = sqlStatement("SELECT DATE( `p`.`dtime` ) AS Date,`f`.`name` AS facilityName, SUM( `p`.`amount1` ) AS totalPaidAmount, `fe`.`provider_id`, CONCAT( `u`.`fname`, ' ', `u`.`lname` ) AS provider_name
                                                                        FROM `facility` AS f
                                                                        LEFT JOIN `form_encounter` AS fe ON ( `fe`.`facility_id` = `f`.`id` )
                                                                        LEFT JOIN `payments` AS p ON ( `fe`.`encounter` = `p`.`encounter` )
                                                                        LEFT JOIN `users` AS u ON ( `fe`.`provider_id` = `u`.`id` )
                                                                        WHERE $wherePaidConditions
                                                                        GROUP BY `p`.`encounter`, Date,provider_name ORDER BY Date ASC", $sqlBindArrayPaid);


        while ($totalPaidRecord = sqlFetchArray($totalPaidAmountSql)) {
            $totalPaid[$totalPaidRecord['Date']][$totalPaidRecord['facilityName']][$totalPaidRecord['provider_name']]['paidAmount'] += $totalPaidRecord['totalPaidAmount'];
        }

        // merge all array recursive in to one array
        $dailySummaryReport = array_merge_recursive($totalAppointment, $totalNewPatient, $totalVisit, $totalPayment, $totalPaid);
        ?>

        <div id="report_results" style="font-size: 12px">
            <?php echo '<b>' . xlt('From') . '</b> ' . $from_date . ' <b>' . xlt('To') . '</b> ' . $to_date; ?>

            <table class="flowboard" cellpadding='5' cellspacing='2' id="ds_report">
                <tr class="head">

                    <td><?php echo xlt('Date'); ?></td>
                    <td><?php echo xlt('Facility'); ?></td>
                    <td><?php echo xlt('Provider'); ?></td>
                    <td><?php echo xlt('Appointments'); ?></td>
                    <td><?php echo xlt('New Patients'); ?></td>
                    <td><?php echo xlt('Visited Patients'); ?></td>
                    <td><?php echo xlt('Total Charges'); ?></td>
                    <td><?php echo xlt('Total Co-Pay'); ?></td>
                    <td><?php echo xlt('Balance Payment'); ?></td>
                </tr>
                <?php
                if (count($dailySummaryReport) > 0) { // check if daily summary array has value
                    foreach ($dailySummaryReport as $date => $dataValue) { //   daily summary array which consists different/dynamic values
                        foreach ($facilities as $facility) { // facility array 
                            if (isset($dataValue[$facility])) {
                                foreach ($dataValue[$facility] as $provider => $information) { // array which consists different/dynamic values
                                    ?>
                                    <tr>
                                        <td><?php echo text($date) ?></td>
                                        <td><?php echo text($facility); ?></td>
                                        <td><?php echo text($provider); ?></td>
                                        <td><?php echo isset($information['appointments']) ? text($information['appointments']) : 0; ?></td>
                                        <td><?php echo isset($information['newPatient']) ? text($information['newPatient']) : 0; ?></td>
                                        <td><?php echo isset($information['visits']) ? text($information['visits']) : 0; ?></td>
                                        <td align="right"><?php echo isset($information['payments']) ? text(number_format($information['payments'], 2)) : number_format(0, 2); ?></td>
                                        <td align="right"><?php echo isset($information['paidAmount']) ? text(number_format($information['paidAmount'], 2)) : number_format(0, 2); ?></td>
                                        <td align="right">
                                            <?php
                                            if (isset($information['payments']) || isset($information['paidAmount'])) {
                                                $dueAmount = number_format(str_replace(",", "", $information['payments']) - str_replace(",", "", $information['paidAmount']), 2);
                                            } else {
                                                $dueAmount = number_format(0, 2);
                                            }
                                            echo text($dueAmount);
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    if (count($dailySummaryReport) > 0) { // calculate the total count of the appointments, new patient,visits, payments, paid amount and due amount
                                        $totalAppointments += $information['appointments'];
                                        $totalNewRegisterPatient += $information['newPatient'];
                                        $totalVisits += $information['visits'];
                                        $totalPayments += str_replace(",", "", $information['payments']);
                                        $totalPaidAmount += str_replace(",", "", $information['paidAmount']);
                                        $totalDueAmount += $dueAmount;
                                    }
                                }
                            }
                        }
                    }
                    ?>
                    <!--display total count-->
                    <tr class="totalrow">
                        <td><?php echo xlt("Total"); ?></td>
                        <td>-</td>
                        <td>-</td>
                        <td><?php echo text($totalAppointments); ?></td>
                        <td><?php echo text($totalNewRegisterPatient); ?></td>
                        <td><?php echo text($totalVisits); ?></td>
                        <td align="right"><?php echo text(number_format($totalPayments, 2)); ?></td>
                        <td align="right"><?php echo text(number_format($totalPaidAmount, 2)); ?></td>
                        <td align="right"><?php echo text(number_format($totalDueAmount, 2)); ?></td>
                    </tr>
                    <?php
                } else { // if there are no records then display message
                    ?>
                    <tr>
                        <td colspan="9" style="text-align:center;font-weight:bold;"> <?php echo xlt("There are no record(s) found."); ?></td>
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
