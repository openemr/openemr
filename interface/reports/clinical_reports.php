<?php
/**
 * Clinical reports.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 OpenEMR Support LLC
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("../drugs/drugs.inc.php");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$comarr = array('allow_sms'=>xl('Allow SMS'),'allow_voice'=>xl('Allow Voice Message'),'allow_mail'=>xl('Allow Mail Message'),'allow_email'=>xl('Allow Email'));

$sql_date_from = (!empty($_POST['date_from'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_from']) : date('Y-01-01 H:i:s');
$sql_date_to = (!empty($_POST['date_to'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_to']) : date('Y-m-d H:i:s');

$type = $_POST["type"];
$facility = isset($_POST['facility']) ? $_POST['facility'] : '';
$patient_id = trim($_POST["patient_id"]);
$age_from = $_POST["age_from"];
$age_to = $_POST["age_to"];
$sql_gender = $_POST["gender"];
$sql_ethnicity = $_POST["ethnicity"];
$sql_race=$_POST["race"];
$form_drug_name = trim($_POST["form_drug_name"]);
$form_diagnosis = trim($_POST["form_diagnosis"]);
$form_lab_results = trim($_POST["form_lab_results"]);
$form_service_codes = trim($_POST["form_service_codes"]);
$form_immunization = trim($_POST["form_immunization"]);
$communication = trim($_POST["communication"]);

?>
<html>
<head>

    <title>
        <?php echo xlt('Clinical Reports'); ?>
    </title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <script language="JavaScript">
        $(function() {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));
        });

        function toggle(id) {
            var tr = document.getElementById(id);
            if (tr==null) { return; }
            var bExpand = tr.style.display == '';
            tr.style.display = (bExpand ? 'none' : '');
        }
        function changeimage(id, sMinus, sPlus) {
            var img = document.getElementById(id);
            if (img!=null) {
                var bExpand = img.src.indexOf(sPlus) >= 0;
                if (!bExpand)
                    img.src = "../pic/blue-up-arrow.gif";
                else
                    img.src = "../pic/blue-down-arrow.gif";
            }
        }
        function Toggle_trGrpHeader2(t_id,i_id) {
            var img=i_id;
            changeimage(img, 'blue-down-arrow.gif', 'blue-up-arrow.gif');
            var id1=t_id;
            toggle(id1);
        }
        // This is for callback by the find-code popup.
        // Appends to or erases the current list of diagnoses.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0][current_sel_name];
            var s = f.value;
            if (code) {
                if (s.length > 0) s += ';';
                s += codetype + ':' + code;
            } else {
                s = '';
            }
            f.value = s;
        }

        //This invokes the find-code popup.
        function sel_diagnosis(e) {
            current_sel_name = e.name;
            dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr_url(collect_codetypes("diagnosis", "csv")); ?>', '_blank', 500, 400);
        }

        //This invokes the find-code popup.
        function sel_procedure(e) {
            current_sel_name = e.name;
            dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr_url(collect_codetypes("procedure", "csv")); ?>', '_blank', 500, 400);
        }
    </script>

    <style type="text/css">
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
        .optional_area_service_codes {
            <?php
            if ($type != 'Service Codes' || $type == '') {
                ?>
            display: none;
                <?php
            }
            ?>
        }
    </style>
    <script language="javascript" type="text/javascript">
        function checkType() {
            if($('#type').val() == 'Service Codes')
            {
                $('.optional_area_service_codes').css("display", "inline");
            }
            else
            {
                $('.optional_area_service_codes').css("display", "none");
            }
        }

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

            if(diff < 0) //negative
            {
                $('#date_error').css("display", "inline");
            }
            else
            {
                $("#form_refresh").attr("value","true");
                $("#theform").submit();
            }
        }

        $(function() {
            $(".numeric_only").keydown(function(event) {
                //alert(event.keyCode);
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

            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = true; ?>
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
<span class='title'>
<?php echo htmlspecialchars(xl('Report - Clinical'), ENT_NOQUOTES); ?>
</span>
<!-- Search can be done using age range, gender, and ethnicity filters.
Search options include diagnosis, procedure, prescription, medical history, and lab results.
-->
<div id="report_parameters_daterange"> <?php echo text(oeFormatDateTime($sql_date_from, "global", true)) .
      " &nbsp; " . xlt("to") . " &nbsp; ". text(oeFormatDateTime($sql_date_to, "global", true)); ?> </div>
<form name='theform' id='theform' method='post' action='clinical_reports.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <div id="report_parameters">
        <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
        <table>
              <tr>
            <td width='740px'><div style='float:left'>
                <table class='text'>
                    <tr>
                        <td class='control-label' width="100"><?php echo xlt('Facility'); ?>: </td>
                        <td width="250"> <?php dropdown_facility($facility, 'facility', false); ?> </td>
                        <td class='control-label' width="100"><?php echo xlt('From'); ?>: </td>
                        <td><input type='text' class='datetimepicker form-control' name='date_from' id="date_from" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_from, 0, true)); ?>'></td>
                    </tr>
                    <tr>
                        <td class='control-label'><?php echo xlt('Patient ID'); ?>:</td>
                        <td><input name='patient_id' class="numeric_only form-control" type='text' id="patient_id" title='<?php echo xla('Optional numeric patient ID'); ?>' value='<?php echo attr($patient_id); ?>' size='10' maxlength='20' /></td>
                        <td class='control-label'><?php echo xlt('To'); ?>: </td>
                        <td><input type='text' class='datetimepicker form-control' name='date_to' id="date_to" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_to, 0, true)); ?>'></td>
                    </tr>
                    <tr>
                        <td class='control-label'><?php echo xlt('Age Range'); ?>:</td>
                        <td><table>
                        <tr>
                        <td class='control-label'><?php echo xlt('From'); ?></td>
                        <td>
                            <input name='age_from' class="numeric_only form-control" type='text' id="age_from" value="<?php echo attr($age_from); ?>" size='3' maxlength='3' />
                        </td>
                        <td class='control-label'><?php echo xlt('To'); ?></td>
                        <td>
                            <input name='age_to' class="numeric_only form-control" type='text' id="age_to" value="<?php echo attr($age_to); ?>" size='3' maxlength='3' />
                        </td>
                        </tr>
                        </table></td>
                        <td class='control-label'><?php echo xlt('Problem DX'); ?>:</td>
                        <td><input type='text' name='form_diagnosis form-control' class= 'form-control' size='10' maxlength='250' value='<?php echo attr($form_diagnosis); ?>' onclick='sel_diagnosis(this)' title='<?php echo xla('Click to select or change diagnoses'); ?>' readonly /></td>
                                                <td>&nbsp;</td>
<!-- Visolve -->
                    </tr>
                    <tr>
                        <td class='control-label'><?php echo xlt('Gender'); ?>:</td>
                        <td><?php echo generate_select_list('gender', 'sex', $sql_gender, 'Select Gender', 'Unassigned', '', ''); ?></td>
                        <td class='control-label'><?php echo xlt('Drug'); ?>:</td>
                        <td><input type='text' name='form_drug_name' class='form-control' size='10' maxlength='250' value='<?php echo attr($form_drug_name); ?>' title='<?php echo xla('Optional drug name, use % as a wildcard'); ?>' /></td>

                    </tr>
                    <tr>
                        <td class='control-label'><?php echo xlt('Race'); ?>:</td>
                        <td><?php echo generate_select_list('race', 'race', $sql_race, 'Select Race', 'Unassigned', '', ''); ?></td>
                        <td class='control-label'><?php echo xlt('Ethnicity'); ?>:</td>
                        <td><?php echo generate_select_list('ethnicity', 'ethnicity', $sql_ethnicity, 'Select Ethnicity', 'Unassigned', '', ''); ?></td>
                        <td class='control-label'><?php echo xlt('Immunization'); ?>:</td>
                        <td><input type='text' name='form_immunization' class='form-control' size='10' maxlength='250' value='<?php echo attr($form_immunization); ?>' title='<?php echo xla('Optional immunization name or code, use % as a wildcard'); ?>' /></td>
                    </tr>
                    <tr>
                        <td class='control-label' width='100'><?php echo xlt('Lab Result'); ?>:</td>
                        <td width='100'><input type='text' name='form_lab_results' class='form-control' size='13' maxlength='250' value='<?php echo attr($form_lab_results); ?>' title='<?php echo xla('Result, use % as a wildcard'); ?>' /></td>

                        <td class='control-label' width='100'><?php echo xlt('Option'); ?>:</td>
                        <td><select name="type" class='form-control' id="type" onChange="checkType();">
                            <option> <?php echo xlt('Select'); ?></option>
                            <option value="Procedure" <?php
                            if ($type == 'Procedure') {
                                echo "selected";
                            } ?>><?php echo xlt('Procedure'); ?></option>
                            <option value="Medical History" <?php
                            if ($type == 'Medical History') {
                                echo "selected";
                            } ?>><?php echo xlt('Medical History'); ?></option>
                            <option value="Service Codes" <?php
                            if ($type == 'Service Codes') {
                                echo "selected";
                            } ?>><?php echo xlt('Service Codes'); ?></option>
                           </select>
                        </td>
                        <td class='control-label'><?php echo xlt('Communication'); ?>:</td>
                        <td>
                            <select name="communication" class='form-control' id="communication" title="<?php echo xla('Select Communication Preferences'); ?>">
                                <option value=""> <?php echo xlt('Select'); ?></option>
                                <?php foreach ($comarr as $comkey => $comvalue) { ?>
                                <option value="<?php echo attr($comkey); ?>" <?php
                                if ($communication == $comkey) {
                                    echo "selected";
                                } ?>><?php echo text($comvalue); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr class="optional_area_service_codes">
                    <td width='100'>&nbsp;</td>
                    <td width='100'>&nbsp;</td>
                    <td width='195'>&nbsp;</td>
                    <td class='control-label' width='76'><?php echo xlt('Code'); ?>:</td>
                                        <td> <input type='text' name='form_service_codes' class='form-control' size='10' maxlength='250' value='<?php echo attr($form_service_codes); ?>' onclick='sel_procedure(this)' title='<?php echo xla('Click to select or change service codes'); ?>' readonly />&nbsp;</td>
                                        </tr>
                </table>
                <table class='text'>
                    <tr>
                        <!-- Sort by Start -->
                                                 <td class='control-label' width='63'><?php echo xlt('Sort By'); ?>:</td>
                                                 <td>
                                                   <table>
                                                   <tr>
                                                   <td>
                                                   <input type='checkbox' class='form-control' name='form_pt_name'<?php
                                                    if ($_POST['form_pt_name'] == true) {
                                                        echo ' checked';
                                                    } ?>>
                                                   </td>
                                                   <td class='control-label'>
                                                    <?php echo xlt('Patient Name'); ?>&nbsp;
                                                   </td>
                                                   <td>
                                                   <input type='checkbox' class='form-control' name='form_pt_age'<?php
                                                    if ($_POST['form_pt_age'] == true) {
                                                        echo ' checked';
                                                    } ?>>
                                                   </td>
                                                   <td class='control-label'>
                                                    <?php echo xlt('Age'); ?>&nbsp;
                                                   </td>
                                                   <td>
                                                   <input type='checkbox' class='form-control' name='form_diagnosis_allergy'<?php
                                                    if ($_POST['form_diagnosis_allergy'] == true) {
                                                        echo ' checked';
                                                    } ?>>
                                                   </td>
                                                   <td class='control-label'>
                                                    <?php echo xlt('Allergies'); ?>&nbsp;
                                                   </td>
                                                   <td>
                                                   <input type='checkbox' class='form-control' name='form_diagnosis_medprb'<?php
                                                    if ($_POST['form_diagnosis_medprb'] == true) {
                                                        echo ' checked';
                                                    } ?>>
                                                   </td>
                                                   <td class='control-label'>
                                                    <?php echo xlt('Medical Problems'); ?>&nbsp;
                                                   </td>
                                                   <td>
                                                   <input type='checkbox' class='form-control' name='form_drug'<?php
                                                    if ($_POST['form_drug'] == true) {
                                                        echo ' checked';
                                                    } ?>>
                                                   </td>
                                                   <td class='control-label'>
                                                    <?php echo xlt('Drug'); ?>&nbsp;
                                                   </td>
                                                   <td>
                                                   <input type='checkbox' class='form-control' name='ndc_no'<?php
                                                    if ($_POST['ndc_no'] == true) {
                                                        echo ' checked';
                                                    } ?>>
                                                   </td>
                                                   <td class='control-label'>
                                                    <?php echo xlt('NDC Number'); ?>&nbsp;
                                                   </td>
                                                   <td>
                                                   <input type='checkbox' class='form-control' name='lab_results'<?php
                                                    if ($_POST['lab_results'] == true) {
                                                        echo ' checked';
                                                    } ?>>
                                                   </td>
                                                   <td class='control-label'>
                                                    <?php echo xlt('Lab Results'); ?>&nbsp;
                                                  </td>
                                                   <td>
                                                  <input type='checkbox' class='form-control' name='communication_check'<?php
                                                    if ($_POST['communication_check'] == true) {
                                                        echo ' checked';
                                                    } ?>>
                                                   </td>
                                                   <td class='control-label'>
                                                    <?php echo xlt('Communication'); ?>
                                                   </td>
                                                   </tr>
                                                   </table>
                                               </td>
                                        </tr>
                <!-- Sort by ends -->
                    </tr>
                    <tr>
                        <td colspan=3><span id="date_error" style="color: #F00; font-siz: 11px; display: none;"><?php echo xlt('From Date Cannot be Greater than To Date.'); ?></span>&nbsp;</td>
                    </tr>
                </table>
                </div></td>
                <td height="100%" valign='middle' width="175"><table style='border-left:1px solid; width:100%; height:100%'>
                    <tr>
                        <td>
                            <div class="text-center">
                                <div class="btn-group" role="group">
                                    <a href='#' class='btn btn-default btn-save' onclick='submitForm();'>
                                        <?php echo xlt('Submit'); ?>
                                    </a>
                                    <?php if ($_POST['form_refresh']) { ?>
                                        <a href='#' class='btn btn-default btn-print' id='printbutton'>
                                            <?php echo xlt('Print'); ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
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
if ($_POST['form_refresh']) {
    $sqlstmt = "select
                concat(pd.fname, ' ', pd.lname) AS patient_name,
                pd.pid AS patient_id,
                DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 AS patient_age,
                pd.sex AS patient_sex,
                pd.race AS patient_race,pd.ethnicity AS patient_ethinic,
                concat(u.fname, ' ', u.lname)  AS users_provider,
                REPLACE(REPLACE(concat_ws(',',IF(pd.hipaa_allowemail = 'YES', 'Allow Email','NO'),IF(pd.hipaa_allowsms = 'YES', 'Allow SMS','NO') , IF(pd.hipaa_mail = 'YES', 'Allow Mail Message','NO') , IF(pd.hipaa_voice = 'YES', 'Allow Voice Message','NO') ), ',NO',''), 'NO,','') as communications";
    if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
        $sqlstmt=$sqlstmt.",li.date AS lists_date,
                   li.diagnosis AS lists_diagnosis,
                        li.title AS lists_title";
    }

    if (strlen($form_drug_name) > 0 || $_POST['form_drug'] == true) {
        $sqlstmt=$sqlstmt.",r.id as id, r.date_modified AS prescriptions_date_modified, r.dosage as dosage, r.route as route, r.interval as hinterval, r.refills as refills, r.drug as drug,
		r.form as hform, r.size as size, r.unit as hunit, d.name as name, d.ndc_number as ndc_number,r.quantity as quantity";
    }

    if (strlen($form_lab_results) > 0 || $_POST['lab_results'] == true) {
        $sqlstmt = $sqlstmt.",pr.date AS procedure_result_date,
                           pr.facility AS procedure_result_facility,
                                pr.units AS procedure_result_units,
                                pr.result AS procedure_result_result,
                                pr.range AS procedure_result_range,
                                pr.abnormal AS procedure_result_abnormal,
                                pr.comments AS procedure_result_comments,
                                pr.document_id AS procedure_result_document_id";
    }

    if ($type == 'Procedure') {
        $sqlstmt = $sqlstmt.",po.date_ordered AS procedure_order_date_ordered,
            pt.standard_code AS procedure_type_standard_code,
            pc.procedure_name as procedure_name,
            po.order_priority AS procedure_order_order_priority,
            po.order_status AS procedure_order_order_status,
            po.encounter_id AS procedure_order_encounter,
            po.patient_instructions AS procedure_order_patient_instructions,
            po.activity AS procedure_order_activity,
            po.control_id AS procedure_order_control_id ";
    }

    if ($type == 'Medical History') {
        $sqlstmt = $sqlstmt.",hd.date AS history_data_date,
            hd.tobacco AS history_data_tobacco,
            hd.alcohol AS history_data_alcohol,
            hd.recreational_drugs AS history_data_recreational_drugs   ";
    }

    if ($type == 'Service Codes') {
          $sqlstmt .= ", c.code as code,
                        c.code_text as code_text,
                        fe.encounter as encounter,
                        b.date as date";
        $mh_stmt = $mh_stmt.",code,code_text,encounter,date";
    }

    if (strlen($form_immunization) > 0) {
        $sqlstmt .= ", immc.code_text as imm_code, immc.code_text_short as imm_code_short, immc.id as cvx_code, imm.administered_date as imm_date, imm.amount_administered, imm.amount_administered_unit,  imm.administration_site, imm.note as notes ";
    }

//from
    $sqlstmt=$sqlstmt." from patient_data as pd left outer join users as u on u.id = pd.providerid
            left outer join facility as f on f.id = u.facility_id";

    if (strlen($form_diagnosis) > 0 || ($_POST['form_diagnosis_allergy'] == true && $_POST['form_diagnosis_medprb'] == true)) {
        $sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND (li.type='medical_problem' OR li.type='allergy')) ";
    } elseif ($_POST['form_diagnosis_allergy'] == true) {
        $sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND (li.type='allergy')) ";
    } elseif ($_POST['form_diagnosis_medprb'] == true) {
        $sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND (li.type='medical_problem')) ";
    }

    if ($type == 'Procedure' ||( strlen($form_lab_results)!=0) || $_POST['lab_results'] == true) {
        $sqlstmt = $sqlstmt." left outer join procedure_order as po on po.patient_id = pd.pid
    left outer join procedure_order_code as pc on pc.procedure_order_id = po.procedure_order_id
    left outer join procedure_report as pp on pp.procedure_order_id   = po.procedure_order_id
    left outer join procedure_type as pt on pt.procedure_code = pc.procedure_code and pt.lab_id = po.lab_id ";
    }

    if (strlen($form_lab_results)!=0 || $_POST['lab_results'] == true) {
        $sqlstmt = $sqlstmt." left outer join procedure_result as pr on pr.procedure_report_id = pp.procedure_report_id ";
    }

    //Immunization added in clinical report
    if (strlen($form_immunization)!=0) {
        $sqlstmt = $sqlstmt." LEFT OUTER JOIN immunizations as imm ON imm.patient_id = pd.pid
						  LEFT OUTER JOIN codes as immc ON imm.cvx_code = immc.id ";
    }

    if (strlen($form_drug_name)!=0 || $_POST['form_drug'] == true) {
           $sqlstmt=$sqlstmt." left outer join prescriptions AS r on r.patient_id=pd.pid
                        LEFT OUTER JOIN drugs AS d ON d.drug_id = r.drug_id";
    }

    if ($type == 'Medical History') {
          $sqlstmt = $sqlstmt." left outer join history_data as hd on hd.pid   =  pd.pid
            and (isnull(hd.tobacco)  = 0
            or isnull(hd.alcohol)  = 0
            or isnull(hd.recreational_drugs)  = 0)";
    }

    if ($type == 'Service Codes') {
        $sqlstmt = $sqlstmt." left outer join billing as b on b.pid = pd.pid
            left outer join form_encounter as fe on fe.encounter = b.encounter and b.code_type = 'CPT4'
            left outer join codes as c on c.code = b.code ";
    }

//where
      $whr_stmt="where 1=1";
    if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
        $whr_stmt=$whr_stmt." AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(li.date) <= ?";
        array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
    }

    if (strlen($form_lab_results)!=0 || $_POST['lab_results'] == true) {
              $whr_stmt=$whr_stmt." AND pr.date >= ? AND pr.date < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(pr.date) <= ?";
              array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
    }

    if (strlen($form_drug_name)!=0 || $_POST['form_drug'] == true) {
        $whr_stmt=$whr_stmt." AND r.date_modified >= ? AND r.date_modified < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(r.date_modified) <= ?";
        array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
    }

    if ($type == 'Medical History') {
         $whr_stmt=$whr_stmt." AND hd.date >= ? AND hd.date < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(hd.date) <= ?";
             array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
    }

    if ($type == 'Procedure') {
         $whr_stmt=$whr_stmt." AND po.date_ordered >= ? AND po.date_ordered < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(po.date_ordered) <= ?";
             array_push($sqlBindArray, substr($sql_date_from, 0, 10), substr($sql_date_to, 0, 10), date("Y-m-d"));
    }

    if ($type == "Service Codes") {
             $whr_stmt=$whr_stmt." AND b.date >= ? AND b.date < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(b.date) <= ?";
             array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
    }

    if (strlen($form_lab_results) != 0 || $_POST['lab_results'] == true) {
        $whr_stmt= $whr_stmt." AND (pr.result LIKE ?) ";
        if (empty($form_lab_results)) {
            $form_lab_results ="%";
        }

        array_push($sqlBindArray, $form_lab_results);
    }

    if (strlen($form_drug_name) > 0 || $_POST['form_drug'] == true) {
            $whr_stmt .= " AND (
                        d.name LIKE ?
                        OR r.drug LIKE ?
                        ) ";
        if (empty($form_drug_name)) {
            $form_drug_name ="%";
        }

            array_push($sqlBindArray, $form_drug_name, $form_drug_name);
    }

    if ($type == 'Service Codes') {
        if (strlen($form_service_codes) != 0) {
            $whr_stmt = $whr_stmt." AND (b.code = ?) ";
            $service_code = explode(":", $form_service_codes);
            array_push($sqlBindArray, $service_code[1]);
        }
    }

    if (strlen($patient_id) != 0) {
        $whr_stmt = $whr_stmt."   and pd.pid = ?";
        array_push($sqlBindArray, $patient_id);
    }

    if (strlen($age_from) != 0) {
         $whr_stmt = $whr_stmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 >= ?";
         array_push($sqlBindArray, $age_from);
    }

    if (strlen($age_to) != 0) {
         $whr_stmt = $whr_stmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 <= ?";
         array_push($sqlBindArray, $age_to);
    }

    if (strlen($sql_gender) != 0) {
          $whr_stmt = $whr_stmt."   and pd.sex = ?";
          array_push($sqlBindArray, $sql_gender);
    }

    if (strlen($sql_ethnicity) != 0) {
         $whr_stmt = $whr_stmt."   and pd.ethnicity = ?";
         array_push($sqlBindArray, $sql_ethnicity);
    }

    if (strlen($sql_race) != 0) {
         $whr_stmt = $whr_stmt."   and pd.race = ?";
         array_push($sqlBindArray, $sql_race);
    }

    if ($facility != '') {
        $whr_stmt = $whr_stmt."   and f.id = ? ";
        array_push($sqlBindArray, $facility);
    }

    if (strlen($form_diagnosis) > 0) {
        $whr_stmt = $whr_stmt." AND (li.diagnosis LIKE ? or li.diagnosis LIKE ? or li.diagnosis LIKE ? or li.diagnosis = ?) ";
        array_push($sqlBindArray, $form_diagnosis."%", '%'.$form_diagnosis.'%', '%'.$form_diagnosis, $form_diagnosis);
    }

  //communication preferences added in clinical report
    if (strlen($communication) > 0 || $_POST['communication_check'] == true) {
        if ($communication == "allow_sms") {
            $whr_stmt .= " AND pd.hipaa_allowsms = 'YES' ";
        } else if ($communication == "allow_voice") {
            $whr_stmt .= " AND pd.hipaa_voice = 'YES' ";
        } else if ($communication == "allow_mail") {
            $whr_stmt .= " AND pd.hipaa_mail  = 'YES' ";
        } else if ($communication == "allow_email") {
            $whr_stmt .= " AND pd.hipaa_allowemail  = 'YES' ";
        } else if ($communication == "" && $_POST['communication_check'] == true) {
            $whr_stmt .= " AND (pd.hipaa_allowsms = 'YES' OR pd.hipaa_voice = 'YES' OR pd.hipaa_mail  = 'YES' OR pd.hipaa_allowemail  = 'YES') ";
        }
    }

  //Immunization where condition for full text or short text
    if (strlen($form_immunization) > 0) {
        $whr_stmt .= " AND (
				immc.code_text LIKE ?
				OR immc.code_text_short LIKE ?
				) ";
        array_push($sqlBindArray, '%'.$form_immunization.'%', '%'.$form_immunization.'%');
    }

// order by
    if ($_POST['form_pt_name'] == true) {
        $odrstmt=$odrstmt.",patient_name";
    }

    if ($_POST['form_pt_age'] == true) {
        $odrstmt=$odrstmt.",patient_age";
    }

    if ((strlen($form_diagnosis) > 0)) {
        $odrstmt=$odrstmt.",lists_diagnosis";
    } elseif (($_POST['form_diagnosis_allergy'] == true) || ($_POST['form_diagnosis_medprb'] == true)) {
        $odrstmt=$odrstmt.",lists_title";
    }

    if (($_POST['form_drug'] == true) || (strlen($form_drug_name) > 0)) {
        $odrstmt=$odrstmt.",r.drug";
    }

    if (($_POST['ndc_no'] == true) && (strlen($form_drug_name) > 0)) {
         $odrstmt=$odrstmt.",d.ndc_number";
    }

    if (($_POST['lab_results'] == true) || (strlen($form_lab_results) > 0)) {
         $odrstmt=$odrstmt.",procedure_result_result";
    }

    if (strlen($communication) > 0 || $_POST['communication_check'] == true) {
        $odrstmt=$odrstmt.",ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
    }


    if ($odrstmt == '') {
        $odrstmt = " ORDER BY patient_id";
    } else {
        $odrstmt = " ORDER BY ".ltrim($odrstmt, ",");
    }

    if ($type == 'Medical History') {
        $sqlstmt="select * from (".$sqlstmt." ".$whr_stmt." ".$odrstmt.",history_data_date desc) a group by patient_id";
    } else {
        $sqlstmt=$sqlstmt." ".$whr_stmt." ".$odrstmt;
    }

    $result = sqlStatement($sqlstmt, $sqlBindArray);

    $row_id = 1.1;//given to each row to identify and toggle
    $img_id = 1.2;
    $k=1.3;

    if (sqlNumRows($result) > 0) {
           //Added on 6-jun-2k14(regarding displaying smoking code descriptions)
           $smoke_codes_arr = getSmokeCodes();
        ?>
    <br>
    <div id = "report_results">

        <?php $pidarr = array();
        while ($row = sqlFetchArray($result)) { ?>
    <table width=90% align="center" cellpadding="5" cellspacing="0" style="font-family:tahoma;color:black;" border="0">
        <tr bgcolor = "#CCCCCC" style="font-size:15px;">
            <td><b><?php echo xlt('Summary of');
            echo " "; ?> <?php echo text($row['patient_name']); ?></b></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td align="center">
            <span onclick="javascript:Toggle_trGrpHeader2(<?php echo attr($row_id); ?>,<?php echo attr($img_id); ?>);"><img src="../pic/blue-down-arrow.gif" id="<?php echo attr($img_id);
            $img_id++; ?>" title="<?php echo xla('Click here to view patient details'); ?>" /></span>
            </td></tr>
            <table width="100%" align="center" id = "<?php echo attr($row_id);
            $row_id++;?>" class="border1" style="display:none; font-size:13px;" cellpadding=5>
                <tr bgcolor="#C3FDB8" align="left">
                <td width="15%"><b><?php echo xlt('Patient Name'); ?></b></td>
                <td width="5%"><b><?php echo xlt('PID');?></b></td>
                <td width="5%"><b><?php echo xlt('Age');?></b></td>
                <td width="10%"><b><?php echo xlt('Gender'); ?></b></td>
                <td width="15%"><b><?php echo xlt('Race');?></b></td>
                <td width="15%"><b><?php echo xlt('Ethnicity');?></b></td>
                <td width="15%" <?php
                if (strlen($communication) == 0 || $_POST['communication_check'] == true) {
                    ?> colspan=5 <?php
                } ?>><b><?php echo xlt('Provider');?></b></td>
                <?php if (strlen($communication) > 0 || ($_POST['communication_check'] == true)) { ?>
                <td colspan=4><b><?php echo xlt('Communication');?></b></td>
                <?php } ?>
                </tr>
                <tr bgcolor="#FFFFFF">
                <td><?php echo text($row['patient_name']); ?>&nbsp;</td>
                <td> <?php echo text($row['patient_id']); ?>&nbsp;</td>
                <td> <?php echo text($row['patient_age']); ?>&nbsp;</td>
                                <td> <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'sex'), $row['patient_sex']); ?>&nbsp;</td>
                <td> <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'race'), $row['patient_race']); ?>&nbsp;</td>
                               <td> <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'ethnicity'), $row['patient_ethinic']); ?>&nbsp;</td>
                               <td <?php
                                if (strlen($communication) == 0 || ($_POST['communication_check'] == true)) {
                                    ?> colspan=5 <?php
                                } ?>> <?php echo text($row['users_provider']); ?>&nbsp;</td>

                                <?php if (strlen($communication) > 0 || $_POST['communication_check'] == true) { ?>
                                        <td colspan=4><?php echo text($row['communications']); ?></td>
                                <?php }  ?>
                </tr>
<!-- Diagnosis Report Start-->
                <?php
                if (strlen($form_diagnosis) > 0 || $_POST['form_diagnosis_allergy'] == true || $_POST['form_diagnosis_medprb'] == true) {
                    ?>
                        <tr bgcolor="#C3FDB8" align= "left">
                <td colspan=12><b><?php echo "#";
                echo xlt('Diagnosis Report');?></b></td>
                </tr>
                <tr bgcolor="#C3FDB8" align= "left">
                <td><b><?php echo xlt('Diagnosis Date');?></b></td>
                <td><b><?php echo xlt('Diagnosis');?></b></td>
                <td colspan=10><b><?php echo xlt('Diagnosis Name');?></b></td>
                </tr>
                            <tr bgcolor="#FFFFFF">
                <td><?php echo text(oeFormatDateTime($row['lists_date'], "global", true)); ?>&nbsp;</td>
                <td><?php echo text($row['lists_diagnosis']); ?>&nbsp;</td>
                                <td colspan=10><?php echo text($row['lists_title']); ?>&nbsp;</td>
                </tr>
                    <?php
                } ?>
<!-- Diagnosis Report End-->

<!-- Prescription Report Start-->
            <?php
            if (strlen($form_drug_name) > 0 || $_POST['form_drug'] == true) {
                ?>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td colspan=12><b><?php echo "#";
                echo xlt('Prescription Report');?><b></td></tr>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td><b><?php echo xlt('Date'); ?></b></td>
                <td><b><?php echo xlt('Drug Name');?></b></td>
                <td><b><?php echo xlt('Route');?></b></td>
                <td><b><?php echo xlt('Dosage');?></b></td>
                <td><b><?php echo xlt('Form');?></b></td>
                <td><b><?php echo xlt('Interval');?></b></td>
                <td><b><?php echo xlt('Size');?></b></td>
                <td><b><?php echo xlt('Unit');?></b></td>
                <td><b><?php echo xlt('ReFill');?></b></td>
                <td><b><?php echo xlt('Quantity');?></b></td>
                <td colspan="2"><b><?php echo xlt('NDC');?></b></td>
                </tr>
                            <tr bgcolor="#FFFFFF" align="">
                <?php
                $rx_route =  generate_display_field(array('data_type'=>'1','list_id'=>'drug_route'), $row['route']) ;
                $rx_form = generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['hform']) ;
                $rx_interval = generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'), $row['hinterval']) ;
                $rx_units =   generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['hunit']);
                ?>
             <td> <?php echo text(oeFormatShortDate($row['prescriptions_date_modified'])); ?>&nbsp;</td>
                <td><?php echo text($row['drug']); ?></td>
                <td><?php echo $rx_route; ?></td>
                <td><?php echo text($row['dosage']); ?></td>
                <td><?php echo $rx_form; ?></td>
                <td><?php echo $rx_interval; ?></td>
                <td><?php echo text($row['size']); ?></td>
                <td><?php echo $rx_units; ?></td>
                <td><?php echo text($row['refills']); ?></td>
                <td><?php echo text($row['quantity']); ?></td>
                <td colspan="2"><?php echo text($row['ndc_number']); ?></td>
                            </tr>
                <?php
            } ?>
<!-- Prescription Report End-->

<!-- Lab Results Report Start-->
                <?php
                if (strlen($form_lab_results) > 0 || $_POST['lab_results'] == true) {
                    ?>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td colspan=12><b><?php echo "#";
                echo xlt('Lab Results Report');?><b></td></tr>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td><b><?php echo xlt('Date'); ?></b></td>
                <td><b><?php echo xlt('Facility');?></b></td>
                <td><b><?php echo xlt('Unit');?></b></td>
                <td><b><?php echo xlt('Result');?></b></td>
                <td><b><?php echo xlt('Range');?></b></td>
                <td><b><?php echo xlt('Abnormal');?></b></td>
                <td><b><?php echo xlt('Comments');?></b></td>
                <td colspan=5><b><?php echo xlt('Document ID');?></b></td>
                </tr>
                            <tr bgcolor="#FFFFFF">
                <td> <?php echo text(oeFormatShortDate($row['procedure_result_date'])); ?>&nbsp;</td>
                                <td> <?php echo text($row['procedure_result_facility']); ?>&nbsp;</td>
                                <td> <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'), $row['procedure_result_units']); ?>&nbsp;</td>
                                 <td> <?php echo text($row['procedure_result_result']); ?>&nbsp;</td>
                                 <td> <?php echo text($row['procedure_result_range']); ?>&nbsp;</td>
                                 <td> <?php echo text($row['procedure_result_abnormal']); ?>&nbsp;</td>
                                 <td> <?php echo text($row['procedure_result_comments']); ?>&nbsp;</td>
                                 <td colspan=5> <?php echo text($row['procedure_result_document_id']); ?>&nbsp;</td>
                        </tr>
                    <?php
                } ?>
<!-- Lab Results End-->

<!-- Procedures Report Start-->
                <?php
                if ($type == 'Procedure') {
                    ?>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td colspan=12><b><?php echo "#";
                echo xlt('Procedure Report');?><b></td></tr>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td><b><?php echo xlt('Date'); ?></b></td>
                <td><b><?php echo xlt('Standard Name');?></b></td>
                <td><b><?php echo xlt('Procedure'); ?></b></td>
                <td><b><?php echo xlt('Encounter');?></b></td>
                <td><b><?php echo xlt('Priority');?></b></td>
                <td><b><?php echo xlt('Status');?></b></td>
                <td><b><?php echo xlt('Instruction');?></b></td>
                <td><b><?php echo xlt('Activity');?></b></td>
                <td colspan=3><b><?php echo xlt('Control ID');?></b></td>
                </tr>
                            <tr bgcolor="#FFFFFF">
                    <?php
                                    $procedure_type_standard_code_arr = explode(':', $row['procedure_type_standard_code']);
                                    $procedure_type_standard_code = $procedure_type_standard_code_arr[1];
                    ?>
                                  <!-- Procedure -->
                                  <td> <?php echo text(oeFormatShortDate($row['procedure_order_date_ordered'])); ?>&nbsp;</td>
                                  <td> <?php echo text($procedure_type_standard_code); ?>&nbsp;</td>
                                  <td> <?php echo text($row['procedure_name']); ?>&nbsp;</td>
                                  <td> <?php echo text($row['procedure_order_encounter']); ?>&nbsp;</td>
                                  <td> <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'ord_priority'), $row['procedure_order_order_priority']); ?>&nbsp;</td>
                                  <td> <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'ord_status'), $row['procedure_order_order_status']); ?>&nbsp;</td>
                                  <td> <?php echo text($row['procedure_order_patient_instructions']); ?>&nbsp;</td>
                                  <td> <?php echo text($row['procedure_order_activity']); ?>&nbsp;</td>
                                  <td colspan=3> <?php echo text($row['procedure_order_control_id']); ?>&nbsp;</td>

                              </tr>
                    <?php
                } ?>
<!-- Procedure Report End-->

<!-- Medical History Report Start-->
                <?php
                if ($type == 'Medical History') {
                    ?>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td colspan=12><b><?php echo "#";
                echo xlt('Medical History');?><b></td></tr>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td><b><?php echo xlt('History Date'); ?></b></td>
                <td><b><?php echo xlt('Tobacco');?></b></td>
                <td><b><?php echo xlt('Alcohol');?></b></td>
                <td colspan=8><b><?php echo xlt('Recreational Drugs');?></b></td>
                </tr>
                            <tr bgcolor="#FFFFFF">
                    <?php
                    $tmp_t = explode('|', $row['history_data_tobacco']);
                    $tmp_a = explode('|', $row['history_data_alcohol']);
                    $tmp_d = explode('|', $row['history_data_recreational_drugs']);
                                        $his_tobac =  generate_display_field(array('data_type'=>'1','list_id'=>'smoking_status'), $tmp_t[3]) ;
                    ?>
                <td> <?php echo text(oeFormatShortDate($row['history_data_date'])); ?>&nbsp;</td>
                                <td> <?php
                                //Added on 6-jun-2k14(regarding displaying smoking code descriptions)
                                if (!empty($smoke_codes_arr[$tmp_t[3]])) {
                                    $his_tobac.= " ( ".text($smoke_codes_arr[$tmp_t[3]])." )";
                                }

                                echo $his_tobac; ?>&nbsp;</td>
                    <?php
                    if ($tmp_a[1] == "currentalcohol") {
                        $res = xl('Current Alcohol');
                    }

                    if ($tmp_a[1] == "quitalcohol") {
                        $res = xl('Quit Alcohol');
                    }

                    if ($tmp_a[1] == "neveralcohol") {
                        $res = xl('Never Alcohol');
                    }

                    if ($tmp_a[1] == "not_applicablealcohol") {
                        $res = xl('N/A');
                    }
                    ?>
                                 <td> <?php echo text($res); ?>&nbsp;</td>
                    <?php
                    if ($tmp_d[1] == "currentrecreational_drugs") {
                        $resd = xl('Current Recreational Drugs');
                    }

                    if ($tmp_d[1] == "quitrecreational_drugs") {
                        $resd = xl('Quit');
                    }

                    if ($tmp_d[1] == "neverrecreational_drugs") {
                        $resd = xl('Never');
                    }

                    if ($tmp_d[1] == "not_applicablerecreational_drugs") {
                        $resd = xl('N/A');
                    }
                    ?>
                                  <td colspan=8> <?php echo text($resd); ?>&nbsp;</td>
                          </tr>
                    <?php
                } ?>
<!-- Medical History Report End-->

<!-- Service Codes Report Start-->
                <?php
                if ($type == 'Service Codes') {
                    ?>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td colspan=11><b><?php echo "#";
                echo xlt('Service Codes');?><b></td></tr>
                            <tr bgcolor="#C3FDB8" align= "left">
                <td><b><?php echo xlt('Date'); ?></b></td>
                <td><b><?php echo xlt('Code');?></b></td>
                <td><b><?php echo xlt('Encounter ID');?></b></td>
                <td colspan=8><b><?php echo xlt('Code Text');?></b></td></tr>
                            <tr bgcolor="#FFFFFF">
                <td><?php echo text(oeFormatShortDate($row['date'])); ?>&nbsp;</td>
                    <td><?php echo text($row['code']); ?>&nbsp;</td>
                        <td><?php echo text($row['encounter']); ?>&nbsp;</td>
                <td colspan=8><?php echo text($row['code_text']); ?>&nbsp;</td>
                            </tr>
                    <?php
                } ?>
<!-- Service Codes Report End-->

<!-- Immunization Report Start-->
                <?php
                if (strlen($form_immunization) > 0) {?>
                    <tr bgcolor="#C3FDB8" align= "left">
                        <td colspan=12><b><?php echo "#";
                        echo xlt('Immunization Report');?></b></td>
                    </tr>
                    <tr bgcolor="#C3FDB8" align= "left">
                        <td><b><?php echo xlt('Immunization Date');?></b></td>
                        <td><b><?php echo xlt('CVX Code');?></b></td>
                        <td><b><?php echo xlt('Vaccine');?></b></td>
                        <td><b><?php echo xlt('Amount');?></b></td>
                        <td><b><?php echo xlt('Administered Site');?></b></td>
                        <td colspan="7"><b><?php echo xlt('Notes');?></b></td>
                    </tr>
                    <tr bgcolor="#FFFFFF">
                        <td><?php echo text(oeFormatDateTime($row['imm_date'])); ?>&nbsp;</td>
                        <td><?php echo text($row['cvx_code']); ?>&nbsp;</td>
                        <td><?php echo text($row['imm_code_short'])." (".text($row['imm_code']).")"; ?>&nbsp;</td>
                        <td>
                        <?php
                        if ($row["amount_administered"] > 0) {
                            echo text($row["amount_administered"]) . " " . generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['amount_administered_unit']);
                        } else {
                            echo "&nbsp;";
                        }
                        ?>

                      </td>

                      <td>
                        <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'proc_body_site'), $row['administration_site']); ?>
                      </td>

                      <td colspan="7">
                        <?php echo text($row['notes']); ?>
                      </td>
                    </tr>
                    <?php
                } ?>
<!-- Immunization Report End-->
                     </table>
            <?php
        }  //while loop end ?>
            </table> <!-- Main table ends -->
        <?php
    } //End if $result
} else { //End if form_refresh
    ?><div class='text'> <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?> </div><?php
}
?>
</form>
</body>

</html>
