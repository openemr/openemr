<?php

/**
 * utility.php
 * Borrowed from new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../globals.php");
require_once("$srcdir/pid.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

/* Modify the static create patient */
$job_id = (($_REQUEST['jobId'] ?? null));
$search = (($_REQUEST['pop_add_chart'] ?? null)) == 1;
$data = json_decode(($_REQUEST['data'] ?? ''), true);
$SHORT_FORM  = ($GLOBALS['full_new_patient_form'] == '2' || $GLOBALS['full_new_patient_form'] == '3' || $GLOBALS['full_new_patient_form'] == '4');
$title = xlt('Create Patient');
if ($search) {
    $title = xlt('Copy Fax to Patient');
}

function getLayoutRes()
{
    global $SHORT_FORM;
    return sqlStatement("SELECT * FROM layout_options " .
        "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' " .
        ($SHORT_FORM ? "AND ( uor > 1 OR edit_options LIKE '%N%' ) " : "") .
        "ORDER BY group_id, seq");
}

if ($_POST['form_create'] ?? null) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $clientApp = AppDispatch::getApiService('fax');

    if (!empty($_POST["pubpid"])) {
        $form_pubpid = trim($_POST["pubpid"]);
        $result = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE " .
            "pubpid = ?", array($form_pubpid));
        if ($result['count']) {
            unset($_POST['form_create']);
            require_once("./utility.php");
        }
    }

    $result = sqlQuery("select max(pid)+1 as pid from patient_data");
    $newpid = 1;
    if ($result['pid'] > 1) {
        $newpid = $result['pid'];
    }
    setpid($newpid);
    if ($pid == null) {
        $pid = 0;
    }
    if (isset($_POST["pubpid"]) && ($_POST["pubpid"] != "")) {
        $mypubpid = $_POST["pubpid"] ?? '';
    } else {
        $mypubpid = $pid;
    }

    $form_fname = ucwords(trim($_POST["fname"] ?? ''));
    $form_lname = ucwords(trim($_POST["lname"] ?? ''));
    $form_mname = ucwords(trim($_POST["mname"] ?? ''));
    $form_sex = trim($_POST["sex"] ?? '');
    $form_dob = DateToYYYYMMDD(trim($_POST["DOB"] ?? null));
    $form_street = '';
    $form_city = '';
    $form_postcode = '';
    $form_countrycode = '';
    $form_regdate = DateToYYYYMMDD(trim($_POST['regdate']));
    newPatientData(
        $_POST["db_id"],
        $_POST["title"] ?? '',
        $form_fname,
        $form_lname,
        $form_mname,
        $form_sex, // sex
        $form_dob, // dob
        $form_street, // street
        $form_postcode, // postal_code
        $form_city, // city
        "", // state
        $form_countrycode, // country_code
        "", // ss
        "", // occupation
        "", // phone_home
        "", // phone_biz
        "", // phone_contact
        "", // status
        "", // contact_relationship
        "", // referrer
        "", // referrerID
        "", // email
        "", // language
        "", // ethnoracial
        "", // interpreter
        "", // migrantseasonal
        "", // family_size
        "", // monthly_income
        "", // homeless
        "", // financial_review
        "$mypubpid",
        $pid,
        "", // providerID
        "", // genericname1
        "", // genericval1
        "", // genericname2
        "", // genericval2
        "", //billing_note
        "", // phone_cell
        "", // hipaa_mail
        "", // hipaa_voice
        0,  // squad
        0,  // $pharmacy_id = 0,
        "", // $drivers_license = "",
        "", // $hipaa_notice = "",
        "", // $hipaa_message = "",
        $form_regdate
    );

    newEmployerData($pid);
    newHistoryData($pid);
    newInsuranceData($pid, "primary");
    newInsuranceData($pid, "secondary");
    newInsuranceData($pid, "tertiary");

    if (!empty($_POST['copy_job_id'] ?? null)) {
        $clientApp->chartFaxDocument($pid, $job_id);
    }
    echo "<script>parent.dlgclose();</script>";
    exit;
} elseif ($_POST['form_save_pid'] ?? null) {
    $clientApp = AppDispatch::getApiService('fax');

    if (!empty($_POST['copy_job_id'] ?? null)) {
        $clientApp->chartFaxDocument($_POST['form_save_pid'], $job_id);
        echo "<script>parent.dlgclose();</script>";
        exit;
    }
}

function getLayoutUOR($form_id, $field_id)
{
    $crow = sqlQuery("SELECT uor FROM layout_options WHERE " .
        "form_id = ? AND field_id = ? LIMIT 1", array($form_id, $field_id));
    return 0 + $crow['uor'];
}
if (empty($_POST) && !empty($data)) {
    $_POST = $data;
    unset($data);
}
$form_pubpid = $_POST['pubpid'] ? trim($_POST['pubpid']) : '';
$form_title = $_POST['title'] ? trim($_POST['title']) : '';
$form_fname = $_POST['fname'] ? trim($_POST['fname']) : '';
$form_mname = $_POST['mname'] ? trim($_POST['mname']) : '';
$form_lname = $_POST['lname'] ? trim($_POST['lname']) : '';
$form_refsource = $_POST['refsource'] ? trim($_POST['refsource']) : '';
$form_sex = $_POST['sex'] ? trim($_POST['sex']) : '';
$form_refsource = $_POST['refsource'] ? trim($_POST['refsource']) : '';
$form_dob = $_POST['DOB'] ? trim($_POST['DOB']) : '';
$form_regdate = $_POST['regdate'] ? trim($_POST['regdate']) : date('Y-m-d');

?>
<!DOCTYPE html>
<html>
<head>
    <?php
    Header::setupHeader(['datetime-picker', 'opener']);
    include_once($GLOBALS['srcdir'] . "/options.js.php");
    ?>

    <script>
        function validate() {
            var f = document.forms[0];
            if (f.fname.value.length == 0) {
                alert(xl('Please select First Name!'));
                return false;
            }
            if (f.lname.value.length == 0) {
                alert(xl('Please select Last Name!'));
                return false;
            }
            if (f.sex.selectedIndex <= 0) {
                alert(xl('Please select a value for Gender!'));
                return false;
            }
            if (f.DOB.value.length == 0) {
                alert(xl('Please select a Birth Date!'));
                return false;
            }
            top.restoreSession();
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
            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });

            $('#search').click(function () {
                searchme();
            });
            $('#create').click(function () {
                checkDupl();
            });

            function checkDupl(e) {
                if (!validate()) {
                    return false;
                }
                const f = document.forms[0];
                let url = <?php echo js_escape($GLOBALS['web_root'] . '/interface/new/new_search_popup.php'); ?>;
                let flds = ['fname', 'mname', 'lname', 'DOB'];
                let separator = '?';
                for (let i = 0; i < flds.length; ++i) {
                    let fval = $("[name='" + flds[i] + "']").val();
                    if (fval && fval != '') {
                        url += separator;
                        separator = '&';
                        url += 'mf_' + flds[i] + '=' + encodeURIComponent(fval);
                    }
                }
                url += separator + "close&simple_search=1";

                dlgopen(url, '_blank', 950, 500);
            }

            function searchme() {
                var f = document.forms[0];
                var url = top.webroot_url + '/interface/main/finder/patient_select.php?popup=1&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>';
                <?php
                $lres = getLayoutRes();
                while ($lrow = sqlFetchArray($lres)) {
                    $field_id  = $lrow['field_id'];
                    if (strpos($field_id, 'em_') === 0) {
                        continue;
                    }
                    $data_type = $lrow['data_type'];
                    $fldname = "$field_id";
                    if (!in_array($lrow['field_id'], ['lname','fname','mname','DOB','sex'])) {
                        continue;
                    }
                    switch ((int)$data_type) {
                        case 2:
                        case 3:
                        case 4:
                            echo
                                " if ((f." . attr($fldname) . ".value) > '') {\n" .
                                "  url += '&" . attr($field_id) . "=' + encodeURIComponent(f." . attr($fldname) . ".value);\n" .
                                " }\n";
                            break;
                        case 1:
                        case 10:
                        case 11:
                        case 12:
                        case 13:
                        case 14:
                        case 26:
                        case 35:
                            echo
                                " if (f." . attr($fldname) . ".selectedIndex > 0) {\n" .
                                "  url += '&" . attr($field_id) . "=' + encodeURIComponent(f." . attr($fldname) . ".options[f." . attr($fldname) . ".selectedIndex].value);\n" .
                                " }\n";
                            break;
                    }
                }
                ?>

                dlgopen(url, '_blank', 700, 500);
            }
        });

        function srchDone(pid){
            $("#form_save_pid").val(pid);
            let yn = confirm(
                xl("Saving selected fax to selected patient.") + "\n\n" +
                xl("Do you want to continue?")
            );
            if (!yn) {
                return false;
            }
            top.restoreSession();
            document.forms[0].submit();
        }

        function srcConfirmSave() {
            let yn = confirm(
            xl("Creating New Patient.") + "\n\n" +
            xl("Do you want to continue?")
        );
            if (!yn) {
                return false;
            }
            $("#form_create").val('save');
            document.forms[0].submit();
        }
    </script>
</head>
<body class="body_top" onload="javascript:document.new_patient.fname.focus();">
    <div class="container-fluid">
        <div class='title'><?php echo $title; ?></div>
        <form class="form" name='new_patient' method='post' action="" onsubmit='return validate()'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="hidden" id="form_create" name="form_create" value="" />
            <input type="hidden" id="form_save_pid" name="form_save_pid" value="" />
            <div class="form-group col">
                <label class="font-weight-bold"><?php echo xlt('Title'); ?>:</label>
                <select class="form-control" required name='title'>
                    <option value=''><?php echo xlt("Unassigned"); ?></option>
                    <?php
                    $ores = sqlStatement("SELECT option_id, title FROM list_options " .
                        "WHERE list_id = 'titles' AND activity = 1 ORDER BY seq");
                    while ($orow = sqlFetchArray($ores)) {
                        echo " <option value='" . attr($orow['option_id']) . "'";
                        if ($orow['option_id'] == $form_title) {
                            echo " selected";
                        }
                        echo ">" . text($orow['title']) . "</option>\n";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col">
                <label class="font-weight-bold"><?php echo xlt('First Name'); ?>: </label>
                <input type='text' class="form-control" required name='fname' value='<?php echo attr($form_fname); ?>' />
            </div>
            <div class="form-group col">
                <label class="font-weight-bold"><?php echo xlt('Middle Name'); ?>: </label>
                <input type='text' class="form-control" name='mname' value='<?php echo attr($form_mname); ?>' />
            </div>
            <div class="form-group col">
                <label class="font-weight-bold"><?php echo xlt('Last Name'); ?>: </label>
                <input type='text' class="form-control" required name='lname' value='<?php echo attr($form_lname); ?>' />
            </div>
            <div class="form-group col">
                <label class="font-weight-bold"><?php echo xlt('Gender'); ?>: </label>
                <select class="form-control" required name='sex'>
                    <option value=''><?php echo xlt("Unassigned"); ?></option>
                    <?php
                    $ores = sqlStatement("SELECT option_id, title FROM list_options " .
                        "WHERE list_id = 'sex' AND activity = 1 ORDER BY seq");
                    while ($orow = sqlFetchArray($ores)) {
                        echo "    <option value='" . attr($orow['option_id']) . "'";
                        if ($orow['option_id'] == $form_sex) {
                            echo " selected";
                        }
                        echo ">" . text($orow['title']) . "</option>\n";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col">
                <label class="font-weight-bold"><?php echo xlt('Birth Date'); ?>:</label>
                <input type='text' class='form-control datepicker' required name='DOB' id='DOB' value='<?php echo attr($form_dob); ?>' />
            </div>
            <div class="form-check">
                <label for="copy_job_id" class="form-check-label" style="font-weight: 600;">
                    <input type="checkbox" class="form-check-input" id="copy_job_id" name="copy_job_id" checked value="<?php echo attr($job_id); ?>" />
                    <?php echo xlt('Copy Fax Id') . " '" . text($job_id) . "' " . xlt('to Documents'); ?>
                </label>
            </div>
            <div class="form-group col">
            </div>
            <div class="float-right">
                <button type="button" class="btn btn-primary btn-search" id="search" value="<?php echo xla('Search'); ?>">
                    <?php echo xlt('Existing Patient'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-save" name='create' id="create" value="save">
                    <?php echo xlt('Confirm New Patient'); ?>
                </button>
            </div>
        </form>
    </div>
    <script>
        <?php
        if ($form_pubpid) {
            echo "alert(" . xlj('This patient public ID is already in use!') . ");\n";
        }
        ?>
    </script>

</body>
</html>
