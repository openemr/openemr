<?php

/**
 * This report lists all the demographics, allergies, problems, medications and
 * lab results along with race, ethnicity, insurance company and provider for those items
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2014 Ensoftek, Inc
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once("../drugs/drugs.inc.php");
require_once("$srcdir/payment_jav.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient List Creation")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$search_options = array
(
    "Demographics"        => xl("Demographics"),
    "Problems"            => xl("Problems"),
    "Medications"         => xl("Medications"),
    "Allergies"           => xl("Allergies"),
    "Lab results"         => xl("Lab Results"),
    "Communication"       => xl("Communication"),
    "Insurance Companies" => xl("Insurance Companies")
);

$comarr = array
(
    "allow_sms"   => xl("Allow SMS"),
    "allow_voice" => xl("Allow Voice Message"),
    "allow_mail"  => xl("Allow Mail Message"),
    "allow_email" => xl("Allow Email")
);

// get array of all insurance companies from function in patient.inc.php
$insarr = getInsuranceProviders();

$_POST['form_details'] = true;

$sql_date_from = (!empty($_POST['date_from'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_from']) : date('Y-01-01 H:i:s');
$sql_date_to = (!empty($_POST['date_to'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_to']) : date('Y-m-d H:i:s');

$patient_id = trim($_POST["patient_id"] ?? '');
$age_from = $_POST["age_from"] ?? '';
$age_to = $_POST["age_to"] ?? '';
$sql_gender = $_POST["gender"] ?? '';
$sql_ethnicity = $_POST["ethnicity"] ?? '';
$sql_race = $_POST["race"] ?? '';
$form_drug_name = trim($_POST["form_drug_name"] ?? '');
$form_diagnosis = trim($_POST["form_diagnosis"] ?? '');
$form_lab_results = trim($_POST["form_lab_results"] ?? '');
$form_service_codes = trim($_POST["form_service_codes"] ?? '');
$form_immunization = trim($_POST["form_immunization"] ?? '');
$communication = trim($_POST["communication"] ?? '');
$insurance_company = trim($_POST["insurance_companies"] ?? '');
?>
<html>
    <head>

        <title>
            <?php echo xlt('Patient List Creation'); ?>
        </title>

        <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

        <script>
            function Form_Validate() {
                var d = document.forms[0];
                FromDate = d.date_from.value;
                ToDate = d.date_to.value;
                if ((FromDate.length > 0) && (ToDate.length > 0)) {
                    if (Date.parse(FromDate) > Date.parse(ToDate)) {
                        alert(<?php echo xlj('To date must be later than From date!'); ?>);
                        return false;
                    }
                }
                $("#processing").show();
                return true;
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
                #report_results table {
                    margin-top: 0px;
                }
                #report_image {
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
            }
        </style>
        <script>
            function submitForm() {
                var d_from = new String($('#date_from').val());
                var d_to = new String($('#date_to').val());

                var d_from_arr = d_from.split('-');
                var d_to_arr = d_to.split('-');

                var dt_from = new Date(d_from_arr[0], d_from_arr[1], d_from_arr[2]);
                var dt_to = new Date(d_to_arr[0], d_to_arr[1], d_to_arr[2]);

                var mili_from = dt_from.getTime();
                var mili_to = dt_to.getTime();
                var diff = mili_to - mili_from;

                $('#date_error').css("display", "none");

                if (diff < 0) //negative
                {
                    $('#date_error').css("display", "inline");
                } else {
                    $("#form_refresh").attr("value","true");
                    top.restoreSession();
                    $("#theform").submit();
                }
            }

            //sorting changes
            function sortingCols(sort_by,sort_order)
            {
                $("#sortby").val(sort_by);
                $("#sortorder").val(sort_order);
                $("#form_refresh").attr("value","true");
                $("#theform").submit();
            }

            $(function () {
                $(".numeric_only").keydown(function(event) {
                    // Allow only backspace and delete
                    if ( event.keyCode == 46 || event.keyCode == 8 ) {
                        // let it happen, don't do anything
                    }
                    else {
                        if(!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57)))
                        {
                            event.preventDefault();
                        }
                    }
                });

                <?php if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Communication")) { ?>
                    $('#com_pref').show();
                <?php } ?>

                <?php if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Insurance Companies")) { ?>
                    $('#ins_co').show();
                <?php } ?>

                $('.datetimepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = true; ?>
                    <?php $datetimepicker_showseconds = true; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });

            function printForm(){
                 var win = top.printLogPrint ? top : opener.top;
                 win.printLogPrint(window);
            }

            function srch_option_change(elem) {
                $('#sortby').val('');
                $('#sortorder').val('');

                if(elem.value == 'Communication') {
                    $('#communication').val('');
                    $('#com_pref').show();
                } else {
                    $('#communication').val('');
                    $('#com_pref').hide();
                }

                if(elem.value == 'Insurance Companies') {
                    $('#insurance_companies').val('');
                    $('#ins_co').show();
                } else {
                    $('#insurance_companies').val('');
                    $('#ins_co').hide();
                    }
            }

        </script>
    </head>

    <body class="body_top">
        <!-- Required for the popup date selectors -->
        <div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>
        <span class='title'>
        <?php echo xlt('Report - Patient List Creation');?>
        </span>
        <!-- Search can be done using age range, gender, and ethnicity filters.
        Search options include diagnosis, procedure, prescription, medical history, and lab results.
        -->

        <div id="report_parameters_daterange">
            <p>
            <?php echo "<span style='margin-left:5px;'><strong>" . xlt('Date Range') . ":</strong>&nbsp;" . text(oeFormatDateTime($sql_date_from, "global", true)) .
              " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatDateTime($sql_date_to, "global", true)) . "</span>"; ?>
            <span style="margin-left:5px;"><strong><?php echo xlt('Option'); ?>:</strong>&nbsp;<?php echo text($_POST['srch_option'] ?? '');
            if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Communication") && ($_POST['communication'] != "")) {
                if (isset($comarr[$_POST['communication']])) {
                    echo "(" . text($comarr[$_POST['communication']]) . ")";
                } else {
                    echo "(" . xlt('All') . ")";
                }
            }  ?></span>
            <span style="margin-left:5px;"><strong><?php echo xlt('Option'); ?>:</strong>&nbsp;<?php echo text($_POST['srch_option'] ?? '');
            if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Insurance Companies") && ($_POST['insurance_companies'] != "")) {
                if (isset($insarr[$_POST['insurance_companies']])) {
                    echo "(" . text($insarr[$_POST['insurance_companies']]) . ")";
                } else {
                    echo "(" . xlt('All') . ")";
                }
            }  ?></span>
            </p>
        </div>
        <form name='theform' id='theform' method='post' action='patient_list_creation.php' onSubmit="return Form_Validate();">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div id="report_parameters">
                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                <table>
                    <tr>
                    <td width='640px'>
                        <div class="cancel-float" style='float: left'>
                        <table class='text'>
                            <tr>
                                <td class='col-form-label'><?php echo xlt('From'); ?>: </td>
                                <td><input type='text' class='datetimepicker form-control' name='date_from' id="date_from" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_from, 0, true)); ?>'>
                                </td>
                                <td class='col-form-label'><?php echo xlt('To{{range}}'); ?>: </td>
                                <td><input type='text' class='datetimepicker form-control' name='date_to' id="date_to" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_to, 0, true)); ?>'>
                                </td>
                                <td class='col-form-label'><?php echo xlt('Option'); ?>: </td>
                                <td class='col-form-label'>
                                    <select class="form-control" name="srch_option" id="srch_option"
                                        onchange="srch_option_change(this)">
                                        <?php foreach ($search_options as $skey => $svalue) { ?>
                                            <option <?php echo (!empty($_POST['srch_option']) && ($_POST['srch_option'] == $skey)) ? 'selected' : ''; ?>
                                            value="<?php echo attr($skey); ?>"><?php echo text($svalue); ?></option>
                                        <?php } ?>
                                    </select>
                                    <?php ?>
                                </td>

                                <td >
                                    <span id="com_pref" style="display: none">
                                    <select class="form-control" name="communication" id="communication" title="<?php echo xlt('Select Communication Preferences'); ?>">
                                        <option> <?php echo xlt('All'); ?></option>
                                        <option value="allow_sms" <?php echo ($communication == "allow_sms") ? "selected" : ""; ?>><?php echo xlt('Allow SMS'); ?></option>
                                        <option value="allow_voice" <?php echo ($communication == "allow_voice") ? "selected" : ""; ?>><?php echo xlt('Allow Voice Message'); ?></option>
                                        <option value="allow_mail" <?php echo ($communication == "allow_mail") ? "selected" : ""; ?>><?php echo xlt('Allow Mail Message'); ?></option>
                                        <option value="allow_email" <?php echo ($communication == "allow_email") ? "selected" : ""; ?>><?php echo xlt('Allow Email'); ?></option>
                                    </select>
                                    </span>
                                </td>

                                <td >
                                    <span id="ins_co" style="display: none">
                                    <select class="form-control" name="insurance_companies" id="insurance_companies" title="<?php echo xlt('Select Insurance Company'); ?>">
                                        <option> <?php echo xlt('All'); ?></option>
                                        <?php foreach ($insarr as $ins_id => $ins_co) { ?>
                                            <option <?php echo (!empty($_POST['insurance_companies']) && ($_POST['insurance_companies'] == $ins_co)) ? 'selected' : ''; ?> value="<?php echo attr($ins_co); ?>"><?php echo text($ins_co); ?></option>
                                        <?php } ?>
                                    </select>
                                    </select>
                                    </span>
                                </td>

                            </tr>
                            <tr>
                                <td class='col-form-label'><?php echo xlt('Patient ID'); ?>:</td>
                                <td><input name='patient_id' class="numeric_only form-control" type='text' id="patient_id" title='<?php echo xla('Optional numeric patient ID'); ?>' value='<?php echo attr($patient_id); ?>' size='10' maxlength='20' /></td>
                                <td class='col-form-label'><?php echo xlt('Age Range'); ?>:</td>

                                <td>
                                <table>
                                <tr>
                                <td class='col-form-label'>
                                <?php echo xlt('From'); ?>:
                                </td>
                                <td>
                                <input name='age_from' class="numeric_only form-control" type='text' id="age_from" value="<?php echo attr($age_from); ?>" size='3' maxlength='3' />
                                </td>
                                <td class='col-form-label'>
                                <?php echo xlt('To{{range}}'); ?>:
                                </td>
                                <td>
                                <input name='age_to' class="numeric_only form-control" type='text' id="age_to" value="<?php echo attr($age_to); ?>" size='3' maxlength='3' />
                                </td>
                                </tr>
                                </table>
                                </td>

                                <td class='col-form-label'><?php echo xlt('Gender'); ?>:</td>
                                <td colspan="2"><?php echo generate_select_list('gender', 'sex', $sql_gender, 'Select Gender', 'Unassigned', '', ''); ?></td>
                                <td class='col-form-label'><?php echo xlt('Ethnicity'); ?>:</td>
                                <td colspan="2"><?php echo generate_select_list('ethnicity', 'ethnicity', $sql_ethnicity, 'Select Ethnicity', 'Unassigned', '', ''); ?></td>
                            </tr>

                        </table>

                        </div></td>
                        <td class='h-100' valign='middle' width="175">
                            <table class='w-100 h-100' style='border-left: 1px solid;'>
                            <tr>
                                <td>
                                    <div class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href='#' class='btn btn-secondary btn-save' onclick='submitForm();'>
                                                <?php echo xlt('Submit'); ?>
                                            </a>
                                            <?php if (isset($_POST['form_refresh'])) {?>
                                                <a href='#' class='btn btn-secondary btn-print' onclick="printForm()">
                                                    <?php echo xlt('Print'); ?>
                                                </a>
                                            <?php }?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div id='processing' style='display:none;' ><img src='../pic/ajax-loader.gif'/></div>
                                </td>

                            </tr>
                        </table></td>
                    </tr>
                </table>
            </div>
        <!-- end of parameters -->
        <?php

        // SQL scripts for the various searches
        $sqlBindArray = array();
        if (!empty($_POST['form_refresh'])) {
            $sqlstmt = "select
                        pd.date as patient_date,
                        concat(pd.lname, ', ', pd.fname) AS patient_name,
                        pd.pid AS patient_id,
                        DATE_FORMAT(FROM_DAYS(DATEDIFF('" . date('Y-m-d H:i:s') . "',pd.dob)), '%Y')+0 AS patient_age,
                        pd.sex AS patient_sex,
                        pd.race AS patient_race,
                        pd.ethnicity AS patient_ethnic,
                        concat(u.lname, ', ', u.fname)  AS users_provider";

            $srch_option = $_POST['srch_option'];
            switch ($srch_option) {
                case "Medications":
                case "Allergies":
                case "Problems":
                    $sqlstmt = $sqlstmt . ",li.date AS lists_date,
						   li.diagnosis AS lists_diagnosis,
								li.title AS lists_title";
                    break;
                case "Lab results":
                    $sqlstmt = $sqlstmt . ",pr.date AS procedure_result_date,
							pr.facility AS procedure_result_facility,
							pr.units AS procedure_result_units,
							pr.result AS procedure_result_result,
							pr.range AS procedure_result_range,
							pr.abnormal AS procedure_result_abnormal,
							pr.comments AS procedure_result_comments,
							pr.document_id AS procedure_result_document_id";
                    break;
                case "Communication":
                    $sqlstmt = $sqlstmt . ",REPLACE(REPLACE(concat_ws(',',IF(pd.hipaa_allowemail = 'YES', 'Allow Email','NO'),IF(pd.hipaa_allowsms = 'YES', 'Allow SMS','NO') , IF(pd.hipaa_mail = 'YES', 'Allow Mail Message','NO') , IF(pd.hipaa_voice = 'YES', 'Allow Voice Message','NO') ), ',NO',''), 'NO,','') as communications";
                    break;
                case "Insurance Companies":
                    $sqlstmt = $sqlstmt . ", id.type AS ins_type, id.provider AS ins_provider, ic.name as ins_name";
            }

            //from
            $sqlstmt = $sqlstmt . " from patient_data as pd left outer join users as u on u.id = pd.providerid";
            //JOINS
            switch ($srch_option) {
                case "Problems":
                    $sqlstmt = $sqlstmt . " left outer join lists as li on (li.pid  = pd.pid AND li.type='medical_problem')";
                    break;
                case "Medications":
                    $sqlstmt = $sqlstmt . " left outer join lists as li on (li.pid  = pd.pid AND (li.type='medication')) ";
                    break;
                case "Allergies":
                    $sqlstmt = $sqlstmt . " left outer join lists as li on (li.pid  = pd.pid AND (li.type='allergy')) ";
                    break;
                case "Lab results":
                    $sqlstmt = $sqlstmt . " left outer join procedure_order as po on po.patient_id = pd.pid
							left outer join procedure_order_code as pc on pc.procedure_order_id = po.procedure_order_id
							left outer join procedure_report as pp on pp.procedure_order_id = po.procedure_order_id
							left outer join procedure_type as pt on pt.procedure_code = pc.procedure_code and pt.lab_id = po.lab_id
							left outer join procedure_result as pr on pr.procedure_report_id = pp.procedure_report_id";
                    break;
                case "Insurance Companies":
                    $sqlstmt = $sqlstmt . " left outer join insurance_data as id on id.pid = pd.pid
                            left outer join insurance_companies as ic on ic.id = id.provider";
                    break;
            }

            //WHERE Conditions started
            $whr_stmt = "where 1=1";
            switch ($srch_option) {
                case "Medications":
                case "Allergies":
                    $whr_stmt = $whr_stmt . " AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
                    array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
                    break;
                case "Problems":
                    $whr_stmt = $whr_stmt . " AND li.title != '' ";
                    $whr_stmt = $whr_stmt . " AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
                    array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
                    break;
                case "Lab results":
                    $whr_stmt = $whr_stmt . " AND pr.date >= ? AND pr.date < DATE_ADD(?, INTERVAL 1 DAY) AND pr.date <= ?";
                    $whr_stmt = $whr_stmt . " AND (pr.result != '') ";
                    array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
                    break;
                case "Communication":
                    $whr_stmt .= " AND (pd.hipaa_allowsms = 'YES' OR pd.hipaa_voice = 'YES' OR pd.hipaa_mail  = 'YES' OR pd.hipaa_allowemail  = 'YES') ";
                    break;
                case "Insurance Companies":
                    $whr_stmt .= " AND id.type = 'primary' AND ic.name != ''";
                    break;
            }

            if (strlen($patient_id) != 0) {
                $whr_stmt = $whr_stmt . "   and pd.pid = ?";
                array_push($sqlBindArray, $patient_id);
            }

            if (strlen($age_from) != 0) {
                $whr_stmt = $whr_stmt . "   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 >= ?";
                array_push($sqlBindArray, $age_from);
            }

            if (strlen($age_to) != 0) {
                $whr_stmt = $whr_stmt . "   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 <= ?";
                array_push($sqlBindArray, $age_to);
            }

            if (strlen($sql_gender) != 0) {
                $whr_stmt = $whr_stmt . "   and pd.sex = ?";
                array_push($sqlBindArray, $sql_gender);
            }

            if (strlen($sql_ethnicity) != 0) {
                $whr_stmt = $whr_stmt . "   and pd.ethnicity = ?";
                array_push($sqlBindArray, $sql_ethnicity);
            }

            if ($srch_option == "Communication" && strlen($communication) > 0) {
                if ($communication == "allow_sms") {
                    $whr_stmt .= " AND pd.hipaa_allowsms = 'YES' ";
                } elseif ($communication == "allow_voice") {
                    $whr_stmt .= " AND pd.hipaa_voice = 'YES' ";
                } elseif ($communication == "allow_mail") {
                    $whr_stmt .= " AND pd.hipaa_mail  = 'YES' ";
                } elseif ($communication == "allow_email") {
                    $whr_stmt .= " AND pd.hipaa_allowemail  = 'YES' ";
                }
            }

            if ($srch_option == "Insurance Companies" && strlen($insurance_company) > 0 && $insurance_company != "All") {
                $whr_stmt = $whr_stmt . " AND ic.name = ?";
                array_push($sqlBindArray, $insurance_company);
            }

            //Sorting By filter fields
            $sortby = $_POST['sortby'] ?? '';
            $sortorder = $_POST['sortorder'] ?? '';

             // This is for sorting the records.
            switch ($srch_option) {
                case "Medications":
                case "Allergies":
                case "Problems":
                    $sort = array("lists_date","lists_diagnosis","lists_title");
                    if ($sortby == "") {
                        $sortby = $sort[1];
                    }
                    break;
                case "Lab results":
                    $sort = array("procedure_result_date","procedure_result_facility","procedure_result_units","procedure_result_result","procedure_result_range","procedure_result_abnormal");
                    //$odrstmt = " procedure_result_result";
                    break;
                case "Communication":
                    //$commsort = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(','))";
                    $sort = array("patient_date","patient_name","patient_id","patient_age","patient_sex","users_provider", "communications");
                    if ($sortby == "") {
                        $sortby = $sort[6];
                    }

                    //$odrstmt = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
                    break;
                case "Insurance Companies":
                    //$commsort = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(','))";
                    $sort = array("patient_date", "patient_name", "patient_id", "patient_age", "patient_sex", "users_provider", "insurance_companies");
                    if ($sortby == "") {
                        $sortby = $sort[7];
                    }

                    //$odrstmt = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
                    break;
                case "Demographics":
                    $sort = array("patient_date","patient_name","patient_id","patient_age","patient_sex","patient_race","patient_ethnic","users_provider");
                    break;
            }

            if ($sortby == "") {
                $sortby = $sort[0];
            }

            if ($sortorder == "") {
                $sortorder = "asc";
            }

            for ($i = 0; $i < count($sort); $i++) {
                $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols(" . attr_js($sort[$i]) . ",'asc');\" ><img src='" .  $GLOBALS['images_static_relative'] . "/sortdown.gif' border='0' alt=\"" . xla('Sort Up') . "\"></a>";
            }

            for ($i = 0; $i < count($sort); $i++) {
                if ($sortby == $sort[$i]) {
                    switch ($sortorder) {
                        case "asc":
                            $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols(" . attr_js($sortby) . ",'desc');\" ><img src='" .  $GLOBALS['images_static_relative'] . "/sortup.gif' border='0' alt=\"" . xla('Sort Up') . "\"></a>";
                            break;
                        case "desc":
                            $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols('" . attr_js($sortby) . "','asc');\" onclick=\"top.restoreSession()\"><img src='" . $GLOBALS['images_static_relative'] . "/sortdown.gif' border='0' alt=\"" . xla('Sort Down') . "\"></a>";
                            break;
                    } break;
                }
            }

            switch ($srch_option) {
                case "Medications":
                case "Allergies":
                case "Problems":
                    $odrstmt = " ORDER BY lists_date asc";
                    break;
                case "Lab results":
                    $odrstmt = " ORDER BY procedure_result_date asc";
                    break;
                case "Communication":
                    $odrstmt = "ORDER BY ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) asc, communications asc";
                    break;
                case "Demographics":
                    $odrstmt = " ORDER BY patient_date asc";
                    break;
                case "Insurance Companies":
                    $odrstmt = " ORDER BY ins_provider asc";
                    break;
            }

            if (!empty($_POST['sortby']) && !empty($_POST['sortorder'])) {
                if ($_POST['sortby'] == "communications") {
                    $odrstmt = "ORDER BY ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) " . escape_sort_order($_POST['sortorder']) . ", communications " . escape_sort_order($_POST['sortorder']);
                } elseif ($_POST['sortby'] == "insurance_companies") {
                    $odrstmt = "ORDER BY ins_provider " . escape_sort_order($_POST['sortorder']);
                } else {
                    $odrstmt = "ORDER BY " . escape_identifier($_POST['sortby'], $sort, true) . " " . escape_sort_order($_POST['sortorder']);
                }
            }

            $sqlstmt = $sqlstmt . " " . $whr_stmt . " " . $odrstmt;
            //echo $sqlstmt."<hr>";
            $result = sqlStatement($sqlstmt, $sqlBindArray);
            //print_r($result);
            $row_id = 1.1;//given to each row to identify and toggle
            $img_id = 1.2;
            $k = 1.3;

            if (sqlNumRows($result) > 0) {
                $patArr = array();

                $patDataArr = array();
                $smoke_codes_arr = getSmokeCodes();
                while ($row = sqlFetchArray($result)) {
                        $patArr[] = $row['patient_id'];
                        $patInfoArr = array();
                        $patInfoArr['patient_id'] = $row['patient_id'];
                        //Diagnosis Check
                    if ($srch_option == "Medications" || $srch_option == "Allergies" || $srch_option == "Problems") {
                        $patInfoArr['lists_date'] = $row['lists_date'];
                        $patInfoArr['lists_diagnosis'] = $row['lists_diagnosis'];
                        $patInfoArr['lists_title'] = $row['lists_title'];
                        $patInfoArr['patient_name'] = $row['patient_name'];
                        $patInfoArr['patient_age'] = $row['patient_age'];
                        $patInfoArr['patient_sex'] = $row['patient_sex'];
                        $patInfoArr['patient_race'] = $row['patient_race'];
                        $patInfoArr['patient_ethnic'] = $row['patient_ethnic'];
                        $patInfoArr['users_provider'] = $row['users_provider'];
                    } elseif ($srch_option == "Lab results") {
                        $patInfoArr['procedure_result_date'] = $row['procedure_result_date'];
                        $patInfoArr['procedure_result_facility'] = $row['procedure_result_facility'];
                        $patInfoArr['procedure_result_units'] = $row['procedure_result_units'];
                        $patInfoArr['procedure_result_result'] = $row['procedure_result_result'];
                        $patInfoArr['procedure_result_range'] = $row['procedure_result_range'];
                        $patInfoArr['procedure_result_abnormal'] = $row['procedure_result_abnormal'];
                        $patInfoArr['procedure_result_comments'] = $row['procedure_result_comments'];
                        $patInfoArr['procedure_result_document_id'] = $row['procedure_result_document_id'];
                    } elseif ($srch_option == "Communication") {
                        $patInfoArr['patient_date'] = $row['patient_date'];
                        $patInfoArr['patient_name'] = $row['patient_name'];
                        $patInfoArr['patient_age'] = $row['patient_age'];
                        $patInfoArr['patient_sex'] = $row['patient_sex'];
                        $patInfoArr['users_provider'] = $row['users_provider'];
                        $patInfoArr['communications'] = $row['communications'];
                    } elseif ($srch_option == "Insurance Companies") {
                        $patInfoArr['patient_date'] = $row['patient_date'];
                        $patInfoArr['patient_name'] = $row['patient_name'];
                        $patInfoArr['patient_age'] = $row['patient_age'];
                        $patInfoArr['patient_sex'] = $row['patient_sex'];
                        $patInfoArr['patient_ethnic'] = $row['patient_ethnic'];
                        $patInfoArr['users_provider'] = $row['users_provider'];
                        $patInfoArr['insurance_companies'] = $row['ins_name'];
                    } elseif ($srch_option == "Demographics") {
                        $patInfoArr['patient_date'] = $row['patient_date'];
                        $patInfoArr['patient_name'] = $row['patient_name'];
                        $patInfoArr['patient_age'] = $row['patient_age'];
                        $patInfoArr['patient_sex'] = $row['patient_sex'];
                        $patInfoArr['patient_race'] = $row['patient_race'];
                        $patInfoArr['patient_ethnic'] = $row['patient_ethnic'];
                        $patInfoArr['users_provider'] = $row['users_provider'];
                    }
                    $patFinalDataArr[] = $patInfoArr;
                }
                ?>

                <br />

                <input type="hidden" name="sortby" id="sortby" value="<?php echo attr($sortby); ?>" />
                <input type="hidden" name="sortorder" id="sortorder" value="<?php echo attr($sortorder); ?>" />
                <div id="report_results">
                    <table>
                        <tr>
                            <td class="text"><strong><?php echo xlt('Total Number of Patients')?>:</strong>&nbsp;<span id="total_patients"><?php echo text(count(array_unique($patArr))); ?></span></td>
                        </tr>
                    </table>

                    <table class='table' width='90%' align="center" cellpadding="5" cellspacing="0" style="font-family: Tahoma;" border="0">

                    <?php
                    if ($srch_option == "Medications" || $srch_option == "Allergies" || $srch_option == "Problems") { ?>
                        <tr style="font-size:15px;">
                            <td width="15%" class="font-weight-bold"><?php echo xlt('Diagnosis Date'); ?><?php echo $sortlink[0]; ?></td>
                            <td width="15%" class="font-weight-bold"><?php echo xlt('Diagnosis'); ?><?php echo $sortlink[1]; ?></td>
                            <td width="15%" class="font-weight-bold"><?php echo xlt('Diagnosis Name');?><?php echo $sortlink[2]; ?></td>
                            <td width="15%" class="font-weight-bold"><?php echo xlt('Patient Name'); ?></td>
                            <td width="5%" class="font-weight-bold"><?php echo xlt('PID');?></td>
                            <td width="5%" class="font-weight-bold"><?php echo xlt('Age');?></td>
                            <td width="10%" class="font-weight-bold"><?php echo xlt('Gender');?></td>
                            <td width="10%" class="font-weight-bold"><?php echo xlt('Ethnicity');?></td>
                            <td colspan='4' class="font-weight-bold"><?php echo xlt('Provider');?></td>
                        </tr>
                        <?php foreach ($patFinalDataArr as $patKey => $patDetailVal) { ?>
                                <tr bgcolor="#CCCCCC" style="font-size:15px;">
                                    <td ><?php echo text(oeFormatDateTime($patDetailVal['lists_date'], "global", true)); ?></td>
                                    <td ><?php echo text($patDetailVal['lists_diagnosis']); ?></td>
                                    <td ><?php echo text($patDetailVal['lists_title']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_name']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_id']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_age']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_sex']);?></td>
                                    <td colspan='4'><?php echo text($patDetailVal['users_provider']);?></td>
                                </tr>
                        <?php	}
                    } elseif ($srch_option == "Lab results") { ?>
                        <tr bgcolor="#C3FDB8" align= "left" >
                            <td width="15%"><strong><?php echo xlt('Date'); ?><?php echo $sortlink[0]; ?></strong></td>
                            <td width="15%"><strong><?php echo xlt('Facility');?><?php echo $sortlink[1]; ?></strong></td>
                            <td width="10%"><strong><?php echo xlt('Unit');?></strong><?php echo $sortlink[2]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Result');?></strong><?php echo $sortlink[3]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Range');?></strong><?php echo $sortlink[4]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Abnormal');?><?php echo $sortlink[5]; ?></strong></td>
                            <td><strong><?php echo xlt('Comments');?></strong></td>
                            <td width="5%"><strong><?php echo xlt('Document ID');?></strong></td>
                            <td width="5%"><strong><?php echo xlt('PID');?></strong></td>
                        </tr>
                        <?php
                        foreach ($patFinalDataArr as $patKey => $labResInsideArr) {?>
                                <tr bgcolor="#CCCCCC">
                                    <td> <?php echo text(oeFormatDateTime($labResInsideArr['procedure_result_date'], "global", true));?>&nbsp;</td>
                                    <td> <?php echo text($labResInsideArr['procedure_result_facility'], ENT_NOQUOTES); ?>&nbsp;</td>
                                    <td> <?php echo generate_display_field(array('data_type' => '1','list_id' => 'proc_unit'), $labResInsideArr['procedure_result_units']); ?>&nbsp;</td>
                                    <td> <?php echo text($labResInsideArr['procedure_result_result']); ?>&nbsp;</td>
                                    <td> <?php echo text($labResInsideArr['procedure_result_range']); ?>&nbsp;</td>
                                    <td> <?php echo text($labResInsideArr['procedure_result_abnormal']); ?>&nbsp;</td>
                                    <td> <?php echo text($labResInsideArr['procedure_result_comments']); ?>&nbsp;</td>
                                    <td> <?php echo text($labResInsideArr['procedure_result_document_id']); ?>&nbsp;</td>
                                    <td colspan="3"> <?php echo text($labResInsideArr['patient_id']); ?>&nbsp;</td>
                               </tr>
                                        <?php
                        }
                    } elseif ($srch_option == "Communication") { ?>
                        <tr style="font-size:15px;">
                            <td width="15%"><strong><?php echo xlt('Date'); ?></strong><?php echo $sortlink[0]; ?></td>
                            <td width="20%"><strong><?php echo xlt('Patient Name'); ?></strong><?php echo $sortlink[1]; ?></td>
                            <td width="5%"><strong><?php echo xlt('PID');?></strong><?php echo $sortlink[2]; ?></td>
                            <td width="5%"><strong><?php echo xlt('Age');?></strong><?php echo $sortlink[3]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Gender');?></strong><?php echo $sortlink[4]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Ethnicity');?></strong><?php echo $sortlink[5]; ?></td>
                            <td width="15%"><strong><?php echo xlt('Provider');?></strong><?php echo $sortlink[6]; ?></td>
                            <td ><strong><?php echo xlt('Communication');?></strong><?php echo $sortlink[7]; ?></td>
                        </tr>
                        <?php foreach ($patFinalDataArr as $patKey => $patDetailVal) { ?>
                                <tr bgcolor = "#CCCCCC" >
                                    <td ><?php echo ($patDetailVal['patient_date'] != '') ? text(oeFormatDateTime($patDetailVal['patient_date'], "global", true)) : ""; ?></td>
                                    <td ><?php echo text($patDetailVal['patient_name']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_id']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_age']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_sex']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_ethnic']);?></td>
                                    <td ><?php echo text($patDetailVal['users_provider']);?></td>
                                    <td ><?php echo text($patDetailVal['communications']);?></td>
                               </tr>
                        <?php }
                    } elseif ($srch_option == "Insurance Companies") { ?>
                        <tr style="font-size:15px;">
                            <td width="15%"><strong><?php echo xlt('Date'); ?></strong><?php echo $sortlink[0]; ?></td>
                            <td width="20%"><strong><?php echo xlt('Patient Name'); ?></strong><?php echo $sortlink[1]; ?></td>
                            <td width="5%"><strong><?php echo xlt('PID');?></strong><?php echo $sortlink[2]; ?></td>
                            <td width="5%"><strong><?php echo xlt('Age');?></strong><?php echo $sortlink[3]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Gender');?></strong><?php echo $sortlink[4]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Ethnicity');?></strong><?php echo $sortlink[5]; ?></td>
                            <td width="15%"><strong><?php echo xlt('Provider');?></strong><?php echo $sortlink[6]; ?></td>
                            <td ><strong><?php echo xlt('Insurance Companies');?></strong><?php echo $sortlink[7]; ?></td>
                        </tr>
                        <?php foreach ($patFinalDataArr as $patKey => $patDetailVal) { ?>
                                <tr bgcolor = "#CCCCCC" >
                                    <td ><?php echo ($patDetailVal['patient_date'] != '') ? text(oeFormatDateTime($patDetailVal['patient_date'], "global", true)) : ""; ?></td>
                                    <td ><?php echo text($patDetailVal['patient_name']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_id']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_age']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_sex']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_ethnic']);?></td>
                                    <td ><?php echo text($patDetailVal['users_provider']);?></td>
                                    <td ><?php echo text($patDetailVal['insurance_companies']);?></td>
                               </tr>
                        <?php }
                    } elseif ($srch_option == "Demographics") { ?>
                        <tr style="font-size:15px;">
                            <td width="15%"><strong><?php echo xlt('Date'); ?></strong><?php echo $sortlink[0]; ?></td>
                            <td width="20%"><strong><?php echo xlt('Patient Name'); ?></strong><?php echo $sortlink[1]; ?></td>
                            <td width="5%"><strong><?php echo xlt('PID');?></strong><?php echo $sortlink[2]; ?></td>
                            <td width="5%"><strong><?php echo xlt('Age');?></strong><?php echo $sortlink[3]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Gender'); ?></strong><?php echo $sortlink[4]; ?></td>
                            <td width="10%"><strong><?php echo xlt('Ethnicity'); ?></strong><?php echo $sortlink[5]; ?></td>
                            <td width="20%"><strong><?php echo xlt('Race');?></strong><?php echo $sortlink[6]; ?></td>
                            <td colspan=5><strong><?php echo xlt('Provider');?></strong><?php echo $sortlink[7]; ?></td>
                        </tr>
                            <?php foreach ($patFinalDataArr as $patKey => $patDetailVal) { ?>
                                <tr bgcolor = "#CCCCCC" style="font-size:15px;">
                                    <td ><?php echo ($patDetailVal['patient_date'] != '') ? text(oeFormatDateTime($patDetailVal['patient_date'], "global", true)) : ""; ?></td>
                                    <td ><?php echo text($patDetailVal['patient_name']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_id']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_age']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_sex']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_ethnic']);?></td>
                                    <td ><?php echo generate_display_field(array('data_type' => '36','list_id' => 'race'), $patDetailVal['patient_race']); ?></td>
                                    <td colspan=5><?php echo text($patDetailVal['users_provider']);?></td>
                                </tr>
                            <?php }
                    } ?>

                    </table>
                     <!-- Main table ends -->
                <?php
            } else {//End if $result?>
                    <table>
                        <tr>
                            <td class="text">&nbsp;&nbsp;<?php echo xlt('No records found.')?></td>
                        </tr>
                    </table>
                <?php
            }
            ?>
                </div>

            <?php
        } else {//End if form_refresh
            ?><div class='text'> <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?> </div><?php
        }
        ?>
        </form>

    </body>
</html>
