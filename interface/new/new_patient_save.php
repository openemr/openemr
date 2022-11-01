<?php

/**
 * new_patient_save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// Validation for non-unique external patient identifier.
if (!empty($_POST["pubpid"])) {
    $form_pubpid = trim($_POST["pubpid"]);
    $result = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE " .
    "pubpid = ?", array($form_pubpid));
    if ($result['count']) {
        // Error, not unique.
        require_once("new.php");
        exit();
    }
}

require_once("$srcdir/pid.inc.php");
require_once("$srcdir/patient.inc.php");

//here, we lock the patient data table while we find the most recent max PID
//other interfaces can still read the data during this lock, however
sqlStatement("lock tables patient_data read");

$result = sqlQuery("select max(pid)+1 as pid from patient_data");

// TBD: This looks wrong to unlock the table before we have added our
// patient with its newly allocated pid!
//
sqlStatement("unlock tables");
//end table lock
$newpid = 1;

if ($result['pid'] > 1) {
    $newpid = $result['pid'];
}

setpid($newpid);

if ($pid == null) {
    $pid = 0;
}

// what do we set for the public pid?
if (isset($_POST["pubpid"]) && ($_POST["pubpid"] != "")) {
    $mypubpid = $_POST["pubpid"];
} else {
    $mypubpid = $pid;
}

if ($_POST['form_create']) {
    $form_fname = ucwords(trim($_POST["fname"]));
    $form_lname = ucwords(trim($_POST["lname"]));
    $form_mname = ucwords(trim($_POST["mname"]));

  // ===================
  // DBC SYSTEM WAS REMOVED
    $form_sex               = trim($_POST["sex"]) ;
    $form_dob               = DateToYYYYMMDD(trim($_POST["DOB"])) ;
    $form_street            = '' ;
    $form_city              = '' ;
    $form_postcode          = '' ;
    $form_countrycode       = '' ;
    $form_regdate           = DateToYYYYMMDD(trim($_POST['regdate']));
  // EOS DBC
  // ===================

    newPatientData(
        $_POST["db_id"],
        $_POST["title"],
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

  // Set referral source separately because we don't want it messed
  // with later by newPatientData().
    if ($refsource = trim($_POST["refsource"])) {
        sqlQuery("UPDATE patient_data SET referral_source = ? " .
        "WHERE pid = ?", array($refsource, $pid));
    }
}
?>
<html>
<body>
<script>
<?php
if ($alertmsg) {
    echo "alert(" . js_escape($alertmsg) . ");\n";
}

  echo "window.location='$rootdir/patient_file/summary/demographics.php?" .
    "set_pid=" . attr_url($pid) . "&is_new=1';\n";
?>
</script>

</body>
</html>
