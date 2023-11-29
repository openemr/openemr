<?php

/**
 * This report lists a broad range of summarised data per patient, including
 * demographics, allergies, medical problems, medications, prescriptions,
 * communication preferences, insurance companies, encounters, observations,
 * procedures and lab results. Common columns include a patient's creation date,
 * name, ID, age, gender, ethnicity and provider.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Jack Stringer <jack5answers@gmail.com>
 * @copyright Copyright (c) 2014 Ensoftek, Inc
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2023 Jack Stringer <jack5answers@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../globals.php";
require_once "$srcdir/patient.inc.php";
require_once "$srcdir/options.inc.php";
require_once "../drugs/drugs.inc.php";

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

$search_options = [
    "Demographics",
    "Allergies",
    "Problems",
    "Medications",
    "Prescriptions",
    "Communication",
    "Insurance Companies",
    "Encounters",
    "Observations",
    "Procedures",
    "Lab Results"
];

$comarr = array(
    "allow_sms"   => xl("Allow SMS"),
    "allow_voice" => xl("Allow Voice Message"),
    "allow_mail"  => xl("Allow Mail Message"),
    "allow_email" => xl("Allow Email")
);

// Get array of all insurance companies from function in patient.inc.php
$insarr = getInsuranceProvidersExtra();
// Get array of all encounter types
$encarr = [];
$rez = sqlStatement('SELECT option_id, title FROM list_options WHERE list_id = "encounter-types" ORDER BY seq ASC');
for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
    $encarr[$row['option_id']] = $row['title'];
}

$_POST['form_details'] = true;

$sql_date_from = (!empty($_POST['date_from'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_from']) : date('Y-01-01 H:i:s');
$sql_date_to = (!empty($_POST['date_to'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_to']) : date('Y-m-d H:i:s');

$patient_id = trim($_POST["patient_id"] ?? '');
$provider_id = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
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

// Variables related to specific search options
$prescription_drug = trim($_POST["prescription_drug"] ?? '');
$communication = trim($_POST["communication"] ?? '');
$insurance_company = trim($_POST["insurance_companies"] ?? '');
$encounter_type = trim($_POST["encounter_type"] ?? '');
$observation_description = trim($_POST["observation_description"] ?? '');
$procedure_diagnosis = trim($_POST["procedure_diagnosis"] ?? '');

$csv = !empty($_POST['form_csvexport']) && $_POST['form_csvexport'] == true;
if ($csv) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=patient_list_custom.csv");
    header("Content-Description: File Transfer");
} else { ?>
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
                // $("#processing").show(); - Remains after CSV file is downloaded, removed temporarily
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

                <?php // Show inputs related to specific search options
                if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Prescriptions")) { ?>
                    $('#rx_drug').show();
                <?php }
                if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Communication")) { ?>
                    $('#com_pref').show();
                <?php }
                if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Insurance Companies")) { ?>
                    $('#ins_co').show();
                <?php }
                if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Encounters")) { ?>
                    $('#enc_type').show();
                <?php }
                if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Observations")) { ?>
                    $('#obs_desc').show();
                <?php }
                if (!empty($_POST['srch_option']) && ($_POST['srch_option'] == "Procedures" || $_POST['srch_option'] == "Lab Results")) { ?>
                    $('#pr_diag').show();
                <?php }
                ?>

                $('.datetimepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = true; ?>
                    <?php $datetimepicker_showseconds = true; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require $GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'; ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });

            function printForm() {
                 var win = top.printLogPrint ? top : opener.top;
                 win.printLogPrint(window);
            }

            function srch_option_change(elem) {
                $('#sortby').val('');
                $('#sortorder').val('');

                // Reset and show/hide inputs related to specific search options
                if (elem.value == 'Prescriptions') {
                    $('#rx_drug').show();
                } else {
                    $('#prescription_drug').val('');
                    $('#rx_drug').hide();
                }
                if (elem.value == 'Communication') {
                    $('#com_pref').show();
                } else {
                    $('#communication').val('');
                    $('#com_pref').hide();
                }
                if (elem.value == 'Insurance Companies') {
                    $('#ins_co').show();
                } else {
                    $('#insurance_companies').val('');
                    $('#ins_co').hide();
                }
                if (elem.value == 'Encounters') {
                    $('#enc_type').show();
                } else {
                    $('#encounter_type').val('');
                    $('#enc_type').hide();
                }
                if (elem.value == 'Observations') {
                    $('#obs_desc').show();
                } else {
                    $('#observation_description').val('');
                    $('#obs_desc').hide();
                }
                if (elem.value == 'Procedures' || elem.value == 'Lab Results') {
                    $('#pr_diag').show();
                } else {
                    $('#procedure_diagnosis').val('');
                    $('#pr_diag').hide();
                }
            }

        </script>
    </head>

    <body class="body_top">
        <!-- Required for the popup date selectors -->
        <div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>
        <span class='title'><?php echo xlt('Report - Patient List Creation'); ?></span>
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
            <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
            <div id="report_parameters">
                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                <table>
                    <tr>
                        <td width='640px'>
                            <div class="cancel-float" style='float: left'>
                                <table class='text'>
                                    <tr>
                                        <td class='col-form-label'><?php echo xlt('From'); ?>: </td>
                                        <td><input type='text' class='datetimepicker form-control' name='date_from' id="date_from" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_from, 0, true)); ?>'></td>
                                        <td class='col-form-label'><?php echo xlt('To{{range}}'); ?>: </td>
                                        <td><input type='text' class='datetimepicker form-control' name='date_to' id="date_to" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_to, 0, true)); ?>'></td>
                                        <td class='col-form-label'><?php echo xlt('Option'); ?>: </td>
                                        <td class='col-form-label'>
                                            <select class="form-control" name="srch_option" id="srch_option"
                                                onchange="srch_option_change(this)">
                                                <?php foreach ($search_options as $skey) { ?>
                                                    <option <?php echo (!empty($_POST['srch_option']) && ($_POST['srch_option'] == $skey)) ? 'selected' : ''; ?>
                                                    value="<?php echo attr($skey); ?>"><?php echo text(xl($skey)); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td colspan="3">
                                            <!-- Inputs for specific search options -->
                                            <span id="rx_drug" style="display: none">
                                                <input class="form-control" name="prescription_drug" id="prescription_drug" title="<?php echo xla('(% matches any string, _ matches any character)'); ?>" placeholder="<?php echo xlt('Drug'); ?>"<?php echo !empty($_POST['prescription_drug']) ? ' value="' . $_POST['prescription_drug'] . '"' : '' ?>/>
                                            </span>
                                            <span id="com_pref" style="display: none">
                                                <select class="form-control" name="communication" id="communication" title="<?php echo xlt('Select Communication Preferences'); ?>">
                                                    <option> <?php echo xlt('All'); ?></option>
                                                    <option value="allow_sms" <?php echo ($communication == "allow_sms") ? "selected" : ""; ?>><?php echo xlt('Allow SMS'); ?></option>
                                                    <option value="allow_voice" <?php echo ($communication == "allow_voice") ? "selected" : ""; ?>><?php echo xlt('Allow Voice Message'); ?></option>
                                                    <option value="allow_mail" <?php echo ($communication == "allow_mail") ? "selected" : ""; ?>><?php echo xlt('Allow Mail Message'); ?></option>
                                                    <option value="allow_email" <?php echo ($communication == "allow_email") ? "selected" : ""; ?>><?php echo xlt('Allow Email'); ?></option>
                                                </select>
                                            </span>
                                            <span id="ins_co" style="display: none">
                                                <select class="form-control" name="insurance_companies" id="insurance_companies" title="<?php echo xlt('Select Insurance Company'); ?>">
                                                    <option> <?php echo xlt('All'); ?></option>
                                                    <?php foreach ($insarr as $ins_id => $ins_co) { ?>
                                                        <option <?php echo (!empty($_POST['insurance_companies']) && ($_POST['insurance_companies'] == $ins_id)) ? 'selected' : ''; ?> value="<?php echo $ins_id; ?>"><?php echo text($ins_co); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </span>
                                            <span id="enc_type" style="display: none">
                                                <select class="form-control" name="encounter_type" id="encounter_type">
                                                    <option> <?php echo xlt('All'); ?></option>
                                                    <?php foreach ($encarr as $enc_id => $enc_t) { ?>
                                                        <option <?php echo (!empty($_POST['encounter_type']) && ($_POST['encounter_type'] == $enc_id)) ? 'selected' : ''; ?> value="<?php echo $enc_id; ?>"><?php echo text($enc_t); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </span>
                                            <span id="obs_desc" style="display: none">
                                                <input class="form-control" name="observation_description" id="observation_description" title="<?php echo xla('(% matches any string, _ matches any character)'); ?>" placeholder="<?php echo xlt('Code') . '/' . xlt('Description'); ?>"<?php echo !empty($_POST['observation_description']) ? ' value="' . $_POST['observation_description'] . '"' : '' ?>/>
                                            </span>
                                            <span id="pr_diag" style="display: none">
                                                <input class="form-control" name="procedure_diagnosis" id="procedure_diagnosis" title="<?php echo xla('(% matches any string, _ matches any character)'); ?>" placeholder="<?php echo xlt('Diagnosis Code'); ?>"<?php echo !empty($_POST['procedure_diagnosis']) ? ' value="' . $_POST['procedure_diagnosis'] . '"' : '' ?>/>
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
                                                    <td><input name='age_from' class="numeric_only form-control" type='text' id="age_from" value="<?php echo attr($age_from); ?>" size='3' maxlength='3'/></td>
                                                    <td class='col-form-label'>&#8212;</td>
                                                    <td><input name='age_to' class="numeric_only form-control" type='text' id="age_to" value="<?php echo attr($age_to); ?>" size='3' maxlength='3'/></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class='col-form-label'><?php echo xlt('Gender'); ?>:</td>
                                        <td colspan="2"><?php echo generate_select_list('gender', 'sex', $sql_gender, 'Select Gender', 'Unassigned', '', ''); ?></td>
                                        <td class='col-form-label'><?php echo xlt('Ethnicity'); ?>:</td>
                                        <td colspan="2"><?php echo generate_select_list('ethnicity', 'ethnicity', $sql_ethnicity, 'Select Ethnicity', 'Unassigned', '', ''); ?></td>
                                    </tr>
                                    <tr>
                                        <td class='col-form-label'><?php echo xlt('Provider'); ?>:</td>
                                        <td><?php generate_form_field(array('data_type' => 10, 'field_id' => 'provider', 'empty_title' => 'All'), $provider_id); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </td>

                        <td class='h-100' valign='middle' width="175">
                            <table class='w-100 h-100' style='border-left: 1px solid;'>
                                <tr>
                                    <td>
                                        <div class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href='#' class='btn btn-secondary btn-save' onclick="$('#form_csvexport').val(''); submitForm();"><?php echo xlt('Submit'); ?></a>
                                                <?php if (isset($_POST['form_refresh'])) {?>
                                                    <a href='#' class='btn btn-secondary btn-print' onclick="printForm()"><?php echo xlt('Print'); ?></a>
                                                    <a href='#' class='btn btn-secondary btn-transmit' onclick="$('#form_csvexport').attr('value', 'true'); submitForm();"><?php echo xlt('Export to CSV'); ?></a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- <td>
                                        <div id='processing' style='display:none;' ><img src='../pic/ajax-loader.gif'/></div>
                                    </td> -->
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- End of parameters -->
<?php }

// SQL scripts for the various searches
$sqlBindArray = [];
if (!empty($_POST['form_refresh'])) {
    $sqlstmt = "select
        pd.date as patient_date,
        concat(pd.lname, ', ', pd.fname) AS patient_name,
        pd.pid AS patient_id,
        DATE_FORMAT(FROM_DAYS(DATEDIFF('" . date('Y-m-d H:i:s') . "',pd.dob)), '%Y')+0 AS patient_age,
        pd.sex AS patient_sex,
        TRIM('|' FROM pd.race) AS patient_race,
        TRIM('|' FROM pd.ethnicity) AS patient_ethnic,
        concat(u.lname, ', ', u.fname) AS users_provider";

    $srch_option = $_POST['srch_option'];
    switch ($srch_option) {
        case "Medications":
        case "Allergies":
        case "Problems":
            $sqlstmt .= ", li.date AS other_date,
                    REPLACE(li.diagnosis, ';', ', ') AS lists_diagnosis,
                    li.title AS lists_title";
            break;
        case "Communication":
            $sqlstmt .= ", REPLACE(REPLACE(concat_ws(', ', IF(pd.hipaa_allowemail = 'YES', 'Email', 'NO'), IF(pd.hipaa_allowsms = 'YES', 'SMS', 'NO'),
                    IF(pd.hipaa_mail = 'YES', 'Mail Message', 'NO') , IF(pd.hipaa_voice = 'YES', 'Voice Message', 'NO') ), ', NO', ''), 'NO,', '') as communications";
            break;
        case "Insurance Companies":
            $sqlstmt .= ", id.type AS ins_type, id.provider AS ins_provider, ic.name as ins_name";
            break;
        case "Encounters":
            $sqlstmt .= ", enc.date AS other_date,
                    enc.reason AS enc_reason,
                    enc.facility AS enc_facility,
                    enc.encounter_type_description AS enc_type,
                    enc.discharge_disposition AS enc_discharge";
            break;
        case "Observations":
            $sqlstmt .= ", obs.date AS other_date,
                    obs.code AS obs_code,
                    obs.observation AS obs_comments,
                    obs.description AS obs_description,
                    obs.ob_type AS obs_type,
                    obs.ob_value AS obs_value,
                    obs.ob_unit AS obs_units";
            break;
        case "Prescriptions":
            $sqlstmt .= ", rx.date_added AS other_date,
                    rx.drug AS rx_drug,
                    CONCAT(rx.size, rxl_unit.title) AS rx_medicine_units,
                    CONCAT(rx.dosage, ' in ', rxl_form.title, ' ', rxl_interval.title) AS rx_directions,
                    rx.quantity AS rx_quantity,
                    rx.refills AS rx_refills";
            break;
        case "Procedures":
            $sqlstmt .= ", pr_ord.date_ordered AS other_date,
                    pr_ord.order_status AS pr_status,
                    pr_prov.name AS pr_lab,
                    pr_ord.order_diagnosis AS pr_diagnosis,
                    pr_code.procedure_name as prc_procedure,
                    pr_code.diagnoses AS prc_diagnoses";
            break;
        case "Lab Results":
            $sqlstmt .= ", pr_res.date AS other_date,
                    pr_res.facility AS result_facility,
                    pr_res.result_text AS result_description,
                    pr_res.units AS result_units,
                    pr_res.result AS result_result,
                    pr_res.range AS result_range,
                    pr_res.abnormal AS result_abnormal,
                    pr_res.comments AS result_comments,
                    pr_res.document_id AS result_document_id";
            break;
    }

    $sqlstmt .= " from patient_data as pd";
    // JOINs
    if ($srch_option != "Encounters" && $srch_option != "Observations" && $srch_option != "Prescriptions") {
        $sqlstmt .= " left outer join users as u on u.id = pd.providerid";
    }
    switch ($srch_option) {
        case "Problems":
            $sqlstmt .= " left outer join lists as li on (li.pid  = pd.pid AND li.type='medical_problem')";
            break;
        case "Medications":
            $sqlstmt .= " left outer join lists as li on (li.pid  = pd.pid AND (li.type='medication')) ";
            break;
        case "Allergies":
            $sqlstmt .= " left outer join lists as li on (li.pid  = pd.pid AND (li.type='allergy')) ";
            break;
        case "Insurance Companies":
            $sqlstmt .= " left outer join insurance_data as id on id.pid = pd.pid
                    left outer join insurance_companies as ic on ic.id = id.provider";
            break;
        case "Encounters":
            $sqlstmt .= " left outer join form_encounter as enc on pd.pid = enc.pid
                left outer join users as u on enc.provider_id = u.id";
            break;
        case "Observations":
            $sqlstmt .= " left outer join form_observation as obs on pd.pid = obs.pid
                left outer join users as u on obs.user = u.username";
            break;
        case "Prescriptions":
            $sqlstmt .= " left outer join prescriptions as rx on pd.pid = rx.patient_id
                    left outer join (SELECT option_id, title FROM list_options WHERE list_id = 'drug_units') as rxl_unit on rx.unit = rxl_unit.option_id
                    left outer join (SELECT option_id, title FROM list_options WHERE list_id = 'drug_form') as rxl_form on rx.form = rxl_form.option_id
                    left outer join (SELECT option_id, title FROM list_options WHERE list_id = 'drug_interval') as rxl_interval on rx.interval = rxl_interval.option_id
                    left outer join users as u on rx.provider_id = u.id";
            break;
        case "Procedures":
            $sqlstmt .= " left outer join procedure_order as pr_ord on pr_ord.patient_id = pd.pid
                left outer join procedure_providers as pr_prov on pr_prov.ppid = pr_ord.lab_id
                left outer join procedure_order_code as pr_code on pr_code.procedure_order_id = pr_ord.procedure_order_id";
            break;
        case "Lab Results":
            $sqlstmt .= " left outer join procedure_order as pr_ord on pr_ord.patient_id = pd.pid
                left outer join procedure_report as pr_rep on pr_rep.procedure_order_id = pr_ord.procedure_order_id
                left outer join procedure_order_code as pr_code on pr_code.procedure_order_id = pr_rep.procedure_order_id AND pr_code.procedure_order_seq = pr_rep.procedure_order_seq
                left outer join procedure_result as pr_res on pr_res.procedure_report_id = pr_rep.procedure_report_id";
            break;
    }

    // WHERE conditions started
    $whr_stmt = " where 1=1";
    switch ($srch_option) {
        case "Medications":
        case "Allergies":
            $whr_stmt .= " AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "Problems":
            $whr_stmt .= " AND li.title != '' AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "Communication":
            $whr_stmt .= " AND (pd.hipaa_allowsms = 'YES' OR pd.hipaa_voice = 'YES' OR pd.hipaa_mail  = 'YES' OR pd.hipaa_allowemail  = 'YES')
                AND pd.date >= ? AND pd.date < DATE_ADD(?, INTERVAL 1 DAY) AND pd.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "Insurance Companies":
            $whr_stmt .= " AND id.type = 'primary' AND ic.name != ''
                AND pd.date >= ? AND pd.date < DATE_ADD(?, INTERVAL 1 DAY) AND pd.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "Encounters":
            $whr_stmt .= " AND enc.date >= ? AND enc.date < DATE_ADD(?, INTERVAL 1 DAY) AND enc.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "Observations":
            $whr_stmt .= " AND obs.date >= ? AND obs.date < DATE_ADD(?, INTERVAL 1 DAY) AND obs.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "Prescriptions":
            $whr_stmt .= " AND rx.date_added >= ? AND rx.date_added < DATE_ADD(?, INTERVAL 1 DAY) AND rx.date_added <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "Procedures":
            $whr_stmt .= " AND pr_ord.date_ordered >= ? AND pr_ord.date_ordered < DATE_ADD(?, INTERVAL 1 DAY) AND pr_ord.date_ordered <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "Lab Results":
            $whr_stmt .= " AND pr_res.date >= ? AND pr_res.date < DATE_ADD(?, INTERVAL 1 DAY) AND pr_res.date <= ? AND pr_res.result != ''";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        default:
            $whr_stmt .= " AND pd.date >= ? AND pd.date < DATE_ADD(?, INTERVAL 1 DAY) AND pd.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
    }

    // WHERE conditions based on persistent inputs
    if (strlen($patient_id) != 0) {
        $whr_stmt .= " and pd.pid = ?";
        array_push($sqlBindArray, $patient_id);
    }
    if (strlen($provider_id) != 0) {
        $whr_stmt .= " and u.id = ?";
        array_push($sqlBindArray, $provider_id);
    }
    if (strlen($age_from) != 0) {
        $whr_stmt .= " and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 >= ?";
        array_push($sqlBindArray, $age_from);
    }
    if (strlen($age_to) != 0) {
        $whr_stmt .= " and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 <= ?";
        array_push($sqlBindArray, $age_to);
    }
    if (strlen($sql_gender) != 0) {
        $whr_stmt .= " and pd.sex = ?";
        array_push($sqlBindArray, $sql_gender);
    }
    if (strlen($sql_ethnicity) != 0) {
        $whr_stmt .= " and pd.ethnicity = ?";
        array_push($sqlBindArray, $sql_ethnicity);
    }

    // WHERE conditions based on inputs arising from specific search options
    if ($srch_option == "Prescriptions" && strlen($prescription_drug) > 0) {
        $whr_stmt .= " AND rx.drug LIKE ?";
        array_push($sqlBindArray, $prescription_drug);
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
        $whr_stmt .= " AND id.provider = ?";
        array_push($sqlBindArray, $insurance_company);
    }
    if ($srch_option == "Encounters" && strlen($encounter_type) > 0 && $encounter_type != "All") {
        $whr_stmt .= " AND enc.encounter_type_code = ?";
        array_push($sqlBindArray, $encounter_type);
    }
    if ($srch_option == "Observations" && strlen($observation_description) > 0) {
        $whr_stmt .= " AND (obs.code LIKE ? OR obs.description LIKE ?)";
        array_push($sqlBindArray, $observation_description, $observation_description);
    }
    if (($srch_option == "Procedures" || $srch_option == "Lab Results") && strlen($procedure_diagnosis) > 0) {
        $whr_stmt .= " AND (pr_ord.order_diagnosis LIKE ? OR pr_code.diagnoses LIKE ?)";
        array_push($sqlBindArray, $procedure_diagnosis, $procedure_diagnosis);
    }

    // Controls the columns displayed, their headings and widths, and how many columns are sorted from the left
    $report_options_arr = array(
        "Demographics" => array(
            "cols" => array(
                "patient_date"   => array("heading" => "Date Created", "width" => "nowrap"),
                "patient_name"   => array("heading" => "Patient Name", "width" => "10%"),
                "patient_id"     => array("heading" => "PID",          "width" => "nowrap"),
                "patient_age"    => array("heading" => "Age",          "width" => "nowrap"),
                "patient_sex"    => array("heading" => "Gender",       "width" => "nowrap"),
                "patient_ethnic" => array("heading" => "Ethnicity",    "width" => "10%"),
                "patient_race"   => array("heading" => "Race",         "width" => "10%"),
                "users_provider" => array("heading" => "Provider",     "width" => "10%")
            ),
            "acl" => ["patients", "demo"]
        ),
        "Diagnoses" => array( // Diagnosis Check - Medications, Allergies, Problems
            "cols" => array(
                "other_date"      => array("heading" => "Diagnosis Date", "width" => "nowrap"),
                "patient_name"    => array("heading" => "Patient Name",   "width" => "10%"),
                "patient_id"      => array("heading" => "PID",            "width" => "nowrap"),
                "patient_age"     => array("heading" => "Age",            "width" => "nowrap"),
                "patient_sex"     => array("heading" => "Gender",         "width" => "nowrap"),
                "patient_ethnic"  => array("heading" => "Ethnicity",      "width" => "10%"),
                "users_provider"  => array("heading" => "Provider",       "width" => "10%"),
                "lists_diagnosis" => array("heading" => "Diagnosis",      "width" => "15%"),
                "lists_title"     => array(                               "width" => "15%") // Heading assigned below
            ),
            "sort_cols" => 3,
            "acl" => ["patients", "med"]
        ),
        "Prescriptions" => array(
            "cols" => array(
                "other_date"        => array("heading" => "Filled",         "width" => "10%"),
                "patient_name"      => array("heading" => "Patient Name",   "width" => "10%"),
                "patient_id"        => array("heading" => "PID",            "width" => "nowrap"),
                "patient_age"       => array("heading" => "Age",            "width" => "nowrap"),
                "patient_sex"       => array("heading" => "Gender",         "width" => "nowrap"),
                "rx_drug"           => array("heading" => "Drug",           "width" => "20%"),
                "rx_medicine_units" => array("heading" => "Units",          "width" => "nowrap"),
                "rx_directions"     => array("heading" => "Directions",     "width" => "10%"),
                "rx_quantity"       => array("heading" => "Quantity",       "width" => "nowrap"),
                "rx_refills"        => array("heading" => "Refills",        "width" => "nowrap")
            ),
            "acl" => ["patients", "rx"]
        ),
        "Communication" => array(
            "cols" => array(
                "patient_date"   => array("heading" => "Date Created",  "width" => "nowrap"),
                "patient_name"   => array("heading" => "Patient Name",  "width" => "10%"),
                "patient_id"     => array("heading" => "PID",           "width" => "nowrap"),
                "patient_age"    => array("heading" => "Age",           "width" => "nowrap"),
                "patient_sex"    => array("heading" => "Gender",        "width" => "nowrap"),
                "patient_ethnic" => array("heading" => "Ethnicity",     "width" => "10%"),
                "users_provider" => array("heading" => "Provider",      "width" => "10%"),
                "communications" => array("heading" => "Communication", "width" => "15%")
            ),
            "acl" => ["patients", "med"]
        ),
        "Insurance Companies" => array(
            "cols" => array(
                "patient_date"   => array("heading" => "Date Created",       "width" => "nowrap"),
                "patient_name"   => array("heading" => "Patient Name",       "width" => "10%"),
                "patient_id"     => array("heading" => "PID",                "width" => "nowrap"),
                "patient_age"    => array("heading" => "Age",                "width" => "nowrap"),
                "patient_sex"    => array("heading" => "Gender",             "width" => "nowrap"),
                "patient_ethnic" => array("heading" => "Ethnicity",          "width" => "10%"),
                "users_provider" => array("heading" => "Insurance Provider", "width" => "10%"),
                "ins_name"       => array("heading" => "Primary Insurance",  "width" => "10%")
            ),
            "acl" => ["patients", "med"]
        ),
        "Encounters" => array(
            "cols" => array(
                "other_date"     => array("heading" => "Encounter Date",        "width" => "nowrap"),
                "patient_name"   => array("heading" => "Patient Name",          "width" => "10%"),
                "patient_id"     => array("heading" => "PID",                   "width" => "nowrap"),
                "patient_age"    => array("heading" => "Age",                   "width" => "nowrap"),
                "patient_sex"    => array("heading" => "Gender",                "width" => "nowrap"),
                "users_provider" => array("heading" => "Provider",              "width" => "10%"),
                "enc_type"       => array("heading" => "Encounter type",        "width" => "20%"),
                "enc_reason"     => array("heading" => "Reason",                "width" => "15%"),
                "enc_facility"   => array("heading" => "Facility",              "width" => "10%"),
                "enc_discharge"  => array("heading" => "Discharge Disposition", "width" => "10%")
            ),
            "acl" => ["encounters", "relaxed"]
        ),
        "Observations" => array(
            "cols" => array(
                "other_date"      => array("heading" => "Date",         "width" => "nowrap"),
                "patient_name"    => array("heading" => "Patient Name", "width" => "10%"),
                "patient_id"      => array("heading" => "PID",          "width" => "nowrap"),
                "patient_age"     => array("heading" => "Age",          "width" => "nowrap"),
                "patient_sex"     => array("heading" => "Gender",       "width" => "nowrap"),
                "users_provider"  => array("heading" => "Provider",     "width" => "10%"),
                "obs_code"        => array("heading" => "Code",         "width" => "nowrap"),
                "obs_description" => array("heading" => "Description",  "width" => "15%"),
                "obs_type"        => array("heading" => "Type",         "width" => "10%"),
                "obs_value"       => array("heading" => "Value",        "width" => "nowrap"),
                "obs_units"       => array("heading" => "Units",        "width" => "nowrap"),
                "obs_comments"    => array("heading" => "Comments",     "width" => "20%")
            ),
            "sort_cols" => -1,
            "acl" => ["encounters", "coding_a"]
        ),
        "Procedures" => array(
            "cols" => array(
                "other_date"      => array("heading" => "Order Date",         "width" => "nowrap"),
                "patient_name"    => array("heading" => "Patient Name",       "width" => "10%"),
                "patient_id"      => array("heading" => "PID",                "width" => "nowrap"),
                "users_provider"  => array("heading" => "Procedure Provider", "width" => "10%"),
                "pr_lab"          => array("heading" => "Lab",                "width" => "10%"),
                "pr_status"       => array("heading" => "Status",             "width" => "nowrap"),
                "pr_diagnosis"    => array("heading" => "Primary Diagnosis",  "width" => "15%"),
                "prc_procedure"   => array("heading" => "Procedure Test",     "width" => "10%"),
                "prc_diagnoses"   => array("heading" => "Diagnosis Codes",    "width" => "20%")
            ),
            "sort_cols" => -2,
            "acl" => ["encounters", "coding_a"]
        ),
        "Lab Results" => array(
            "cols" => array(
                "other_date"         => array("heading" => "Date",           "width" => "nowrap"),
                "result_facility"    => array("heading" => "Facility",       "width" => "10%"),
                "result_description" => array("heading" => "Procedure Test", "width" => "10%"),
                "result_result"      => array("heading" => "Result",         "width" => "5%"),
                "result_units"       => array("heading" => "Unit",           "width" => "nowrap"),
                "result_range"       => array("heading" => "Range",          "width" => "5%"),
                "result_abnormal"    => array("heading" => "Abnormal",       "width" => "nowrap"),
                "result_comments"    => array("heading" => "Comments",       "width" => "20%"),
                "result_document_id" => array("heading" => "Document ID",    "width" => "nowrap"),
                "patient_id"         => array("heading" => "PID",            "width" => "nowrap")
            ),
            "sort_cols" => 6,
            "acl" => ["patients", "lab"]
        )
    );
    if (in_array($srch_option, ["Medications", "Allergies", "Problems"])) {
        switch ($srch_option) {
            case "Medications":
                $report_options_arr["Diagnoses"]["cols"]["lists_title"]["heading"] = "Medication";
                break;
            case "Allergies":
                $report_options_arr["Diagnoses"]["cols"]["lists_title"]["heading"] = "Allergy";
                break;
            case "Problems":
                $report_options_arr["Diagnoses"]["cols"]["lists_title"]["heading"] = "Problem";
                break;
        }
        $srch_option = "Diagnoses";
    }

    if (!AclMain::aclCheckCore($report_options_arr[$srch_option]["acl"][0], $report_options_arr[$srch_option]["acl"][1])) {
        echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient List Creation") . " (" . xl($srch_option) . ")"]);
        exit;
    }

    // Sorting By filter fields
    $sortby = $_POST['sortby'] ?? '';
    $sortorder = $_POST['sortorder'] ?? '';

    // This is for sorting the records, which columns visually allow sorting are decided when drawing the table
    $sort = array_keys($report_options_arr[$srch_option]["cols"]);
    if ($sortby == "") {
        switch ($srch_option) {
            case "Diagnoses":
                $sortby = $sort[1];
                break;
            /* case "Lab Results":
                //$odrstmt = " result_result";
                break; */
            case "Communication":
                //$commsort = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(','))";
                $sortby = $sort[6];
                //$odrstmt = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
                break;
            case "Insurance Companies":
                //$commsort = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(','))";
                $sortby = $sort[7];
                //$odrstmt = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
                break;
            default:
                $sortby = $sort[0];
        }
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
            }
            break;
        }
    }

    switch ($srch_option) {
        case "Diagnoses":
        case "Procedures":
            $odrstmt = " ORDER BY other_date asc";
            break;
        case "Communication":
            $odrstmt = " ORDER BY ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) asc, communications asc";
            break;
        case "Demographics":
            $odrstmt = " ORDER BY patient_date asc";
            break;
        case "Insurance Companies":
            $odrstmt = " ORDER BY ins_provider asc";
            break;
        case "Encounters":
            $odrstmt = " ORDER BY other_date asc, enc_type asc, enc_reason asc, enc_discharge asc";
            break;
        case "Observations":
            $odrstmt = " ORDER BY other_date asc, obs_code asc, obs_type asc, obs_units asc, obs_value asc, obs_comments asc";
            break;
        case "Prescriptions":
            $odrstmt = " ORDER BY other_date asc, rx_quantity asc, rx_refills asc";
            break;
        case "Lab Results":
            $odrstmt = " ORDER BY other_date asc, result_description asc";
            break;
    }

    if (!empty($_POST['sortby']) && !empty($_POST['sortorder'])) {
        if ($_POST['sortby'] == "communications") {
            $odrstmt = " ORDER BY ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) " . escape_sort_order($_POST['sortorder']) . ", communications " . escape_sort_order($_POST['sortorder']);
        } elseif ($_POST['sortby'] == "insurance_companies") {
            $odrstmt = " ORDER BY ins_provider " . escape_sort_order($_POST['sortorder']);
        } else {
            $odrstmt = " ORDER BY " . escape_identifier($_POST['sortby'], $sort, true) . " " . escape_sort_order($_POST['sortorder']);
        }
    }

    $sqlstmt .= $whr_stmt . $odrstmt;
    //echo $sqlstmt."<hr>";
    $result = sqlStatement($sqlstmt, $sqlBindArray);
    //print_r($result);
    $row_id = 1.1;//given to each row to identify and toggle
    $img_id = 1.2;
    $k = 1.3;

    if (sqlNumRows($result) > 0 || $csv) {
        $smoke_codes_arr = getSmokeCodes();
        $report_data_arr = [];
        $patient_arr = [];
        while ($row = sqlFetchArray($result)) {
            $report_data = [];
            foreach (array_keys($report_options_arr[$srch_option]["cols"]) as $report_item_name_key => $report_item_name) {
                array_push($report_data, $row[$report_item_name]);
            }
            array_push($report_data_arr, $report_data);
            array_push($patient_arr, $row["patient_id"]);
        }

        if (!$csv) { ?>
            <br />
            <input type="hidden" name="sortby" id="sortby" value="<?php echo attr($sortby); ?>" />
            <input type="hidden" name="sortorder" id="sortorder" value="<?php echo attr($sortorder); ?>" />
            <div id="report_results">
                <table>
                    <tr>
                        <td class="text"><strong><?php echo xlt('Total Number of Patients')?>:</strong>&nbsp;<span id="total_patients"><?php echo text(count(array_unique($patient_arr))); ?></span></td>
                    </tr>
                </table>

                <table class='table' width='90%' align="center" cellpadding="5" cellspacing="0" style="font-family: Tahoma;" border="0">
                    <tr <?php echo ($srch_option == "Lab Results") ? 'bgcolor="#C3FDB8" align="left" ' : ''; ?>style="font-size:15px;">
        <?php }
        foreach (array_keys($report_options_arr[$srch_option]["cols"]) as $report_col_key => $report_col) {
            if (!$csv) {
                echo '<td ';
                if (isset($report_options_arr[$srch_option]["cols"][$report_col]["width"])) {
                    $width = $report_options_arr[$srch_option]["cols"][$report_col]["width"];
                    if (str_contains($width, '%')) {
                        echo 'width="' . $width . '" ';
                    } else if ($width == 'nowrap') {
                        echo 'width="1%" style="white-space: nowrap;" ';
                    } else {
                        echo 'colspan="' . $width . '" ';
                    }
                }
                echo 'class="font-weight-bold">' . xlt($report_options_arr[$srch_option]["cols"][$report_col]["heading"]);
                if (isset($report_options_arr[$srch_option]["sort_cols"])) {
                    if ($report_options_arr[$srch_option]["sort_cols"] != 0) {
                        if (
                            ($report_options_arr[$srch_option]["sort_cols"] > 0 && $report_col_key < $report_options_arr[$srch_option]["sort_cols"])
                            || ($report_options_arr[$srch_option]["sort_cols"] < 0 && $report_col_key < $report_options_arr[$srch_option]["sort_cols"] + count($report_options_arr[$srch_option]["cols"]))
                        ) {
                            echo $sortlink[$report_col_key];
                        }
                    }
                } else {
                    echo $sortlink[$report_col_key];
                }
                echo '</td>';
            } else {
                echo csvEscape(xlt($report_options_arr[$srch_option]["cols"][$report_col]["heading"]));
                if ($report_col_key < count($report_options_arr[$srch_option]["cols"]) - 1) {
                    echo ",";
                } else {
                    echo "\n";
                }
            }
        }
        if (!$csv) { ?>
                    </tr>
        <?php }

        foreach ($report_data_arr as $report_data_key => $report_data) {
            if (!$csv) { ?>
                    <tr bgcolor="#CCCCCC" style="font-size:15px;">
            <?php }
            foreach ($report_data as $report_value_key => $report_value) {
                $report_col = array_keys($report_options_arr[$srch_option]["cols"])[$report_value_key];
                $report_value_print = null;
                switch ($report_col) {
                    case "patient_date":
                    case "other_date":
                        $report_value_print = ($report_value != '') ? text(oeFormatDateTime($report_value, "global", true)) : '';
                        break;
                    case "patient_race":
                        $report_value_print = generate_display_field(array('data_type' => '36', 'list_id' => 'race'), $report_value);
                        break;
                    case "patient_ethnic":
                        $report_value_print = generate_display_field(array('data_type' => '36', 'list_id' => 'ethnicity'), $report_value);
                        break;
                    case "result_units":
                        $report_value_print = generate_display_field(array('data_type' => '1', 'list_id' => 'proc_unit'), $report_value) . '&nbsp;';
                        break;
                    case "enc_discharge":
                        $report_value_print = generate_display_field(array('data_type' => '1', 'list_id' => 'discharge-disposition'), $report_value);
                        break;
                    case "obs_type":
                        $report_value_print = generate_display_field(array('data_type' => '1', 'list_id' => 'Observation_Types'), $report_value);
                        break;
                    case "result_abnormal":
                        $report_value_print = generate_display_field(array('data_type' => '1', 'list_id' => 'proc_res_abnormal'), $report_value);
                        break;
                    case "pr_status":
                        $report_value_print = generate_display_field(array('data_type' => '1', 'list_id' => 'ord_status'), $report_value);
                        break;
                    // Procedure diagnoses can be hovered over to reveal their codes
                    case "pr_diagnosis":
                    case "prc_diagnoses":
                        if (!$csv) {
                            if ($report_value != '') {
                                $report_value_print = '<ul style="margin: 0; padding-left: 0.5em;">';
                                foreach (explode(';', $report_value) as $code_index => $code) {
                                    $report_value_print .= '<li><abbr title="' . text($code) . '">' . text(getCodeDescription($code)) . '</abbr></li>';
                                }
                                $report_value_print .= '</ul>';
                            }
                        } else {
                            $report_value_print = $report_value;
                        }
                        break;
                    default:
                        $report_value_print = text($report_value);
                }
                if (!$csv) {
                    $width = isset($report_options_arr[$srch_option]["cols"][$report_col]["width"]) ? $report_options_arr[$srch_option]["cols"][$report_col]["width"] : '';
                    if ($width != 'nowrap') {
                        echo '<td>';
                    } else {
                        echo '<td style="white-space: nowrap;">';
                    }
                    echo $report_value_print . '</td>';
                } else {
                    echo csvEscape($report_value_print);
                    if ($report_value_key < count($report_options_arr[$srch_option]["cols"]) - 1) {
                        echo ",";
                    } else {
                        echo "\n";
                    }
                }
            }
            if (!$csv) { ?>
                    </tr>
            <?php }
        }

        if (!$csv) { ?>
                </table>
                <!-- Main table ends -->
        <?php }
    } else { // End if $result ?>
                <table>
                    <tr><td class="text"><?php echo xlt('No records found.'); ?></td></tr>
        <?php if (isset($prescription_drug) || isset($observation_description) || isset($procedure_diagnosis)) { ?>
                    <tr><td class="text"><?php echo xlt('(% matches any string, _ matches any character)'); ?></td></tr>
        <?php } ?>
                </table>
    <?php }
    if (!$csv) { ?>
            </div>
    <?php }
} else { // End if form_refresh ?>
            <div class='text'><?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?></div>
<?php }
if (!$csv) { ?>
        </form>

    </body>
</html>
<?php } ?>
