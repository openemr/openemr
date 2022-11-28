<?php

/*
 * This program saves data from the misc_billing_form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (C) 2007 Bo Huynh
 * @copyright Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (C) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (C) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General P
 */


require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// From billing manager so do stuff
if (isset($_SESSION['billencounter'])) {
    $pid = $_SESSION['billpid'];
    $encounter = $_SESSION['billencounter'];
    echo "<script src='" . $webroot . "/interface/main/tabs/js/include_opener.js'></script>";
}
if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

if ($_POST["off_work_from"] == "0000-00-00" || $_POST["off_work_from"] == "") {
    $_POST["is_unable_to_work"] = "0";
    $_POST["off_work_to"] = "";
} else {
    $_POST["is_unable_to_work"] = "1";
}

if ($_POST["hospitalization_date_from"] == "0000-00-00" || $_POST["hospitalization_date_from"] == "") {
    $_POST["is_hospitalized"] = "0";
    $_POST["hospitalization_date_to"] = "";
} else {
    $_POST["is_hospitalized"] = "1";
}

$id = formData('id', 'G') + 0;

$sets = "pid = ?,
    groupname = ?,
    user = ?,
    authorized = ?,
    activity = 1,
    date = NOW(),
    employment_related = ?,
    auto_accident = ?,
    accident_state = ?,
    other_accident = ?,
    outside_lab = ?,
    medicaid_referral_code = ?,
    epsdt_flag = ?,
    provider_id = ?,
    provider_qualifier_code = ?,
    lab_amount = ?,
    is_unable_to_work = ?,
    onset_date = ?,
    date_initial_treatment = ?,
    off_work_from = ?,
    off_work_to = ?,
    is_hospitalized = ?,
    hospitalization_date_from = ?,
    hospitalization_date_to = ?,
    medicaid_resubmission_code = ?,
    medicaid_original_reference = ?,
    prior_auth_number = ?,
    replacement_claim = ?,
    icn_resubmission_number = ?,
    box_14_date_qual = ?,
    box_15_date_qual = ?,
    comments = ?";

if (empty($id)) {
    $newid = sqlInsert(
        "INSERT INTO form_misc_billing_options SET $sets",
        [
            $pid,
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            (isset($_POST["employment_related"]) ? $_POST["employment_related"] : ''),
            (isset($_POST["auto_accident"]) ? $_POST["auto_accident"] : ''),
            (isset($_POST["accident_state"]) ? $_POST["accident_state"] : ''),
            (isset($_POST["other_accident"]) ? $_POST["other_accident"] : ''),
            (isset($_POST["outside_lab"]) ? $_POST["outside_lab"] : ''),
            (isset($_POST["medicaid_referral_code"]) ? $_POST["medicaid_referral_code"] : ''),
            (isset($_POST["epsdt_flag"]) ? $_POST["epsdt_flag"] : ''),
            (isset($_POST["provider_id"]) ? $_POST["provider_id"] : ''),
            (isset($_POST["provider_qualifier_code"]) ? $_POST["provider_qualifier_code"] : ''),
            (isset($_POST["lab_amount"]) ? $_POST["lab_amount"] : ''),
            (isset($_POST["is_unable_to_work"]) ? $_POST["is_unable_to_work"] : ''),
            (isset($_POST["onset_date"]) ? $_POST["onset_date"] : ''),
            (isset($_POST["date_initial_treatment"]) ? $_POST["date_initial_treatment"] : ''),
            (isset($_POST["off_work_from"]) ? $_POST["off_work_from"] : ''),
            (isset($_POST["off_work_to"]) ? $_POST["off_work_to"] : ''),
            (isset($_POST["is_hospitalized"]) ? $_POST["is_hospitalized"] : ''),
            (isset($_POST["hospitalization_date_from"]) ? $_POST["hospitalization_date_from"] : ''),
            (isset($_POST["hospitalization_date_to"]) ? $_POST["hospitalization_date_to"] : ''),
            (isset($_POST["medicaid_resubmission_code"]) ? $_POST["medicaid_resubmission_code"] : ''),
            (isset($_POST["medicaid_original_reference"]) ? $_POST["medicaid_original_reference"] : ''),
            (isset($_POST["prior_auth_number"]) ? $_POST["prior_auth_number"] : ''),
            (isset($_POST["replacement_claim"]) ? $_POST["replacement_claim"] : ''),
            (isset($_POST["icn_resubmission_number"]) ? $_POST["icn_resubmission_number"] : ''),
            (isset($_POST["box_14_date_qual"]) ? $_POST["box_14_date_qual"] : ''),
            (isset($_POST["box_15_date_qual"]) ? $_POST["box_15_date_qual"] : ''),
            (isset($_POST["comments"]) ? $_POST["comments"] : '')
        ]
    );

    addForm($encounter, "Misc Billing Options", $newid, "misc_billing_options", $pid, $userauthorized);
} else {
    sqlStatement(
        "UPDATE form_misc_billing_options SET $sets WHERE id = ?",
        [
            $pid,
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            (isset($_POST["employment_related"]) ? $_POST["employment_related"] : ''),
            (isset($_POST["auto_accident"]) ? $_POST["auto_accident"] : ''),
            (isset($_POST["accident_state"]) ? $_POST["accident_state"] : ''),
            (isset($_POST["other_accident"]) ? $_POST["other_accident"] : ''),
            (isset($_POST["outside_lab"]) ? $_POST["outside_lab"] : ''),
            (isset($_POST["medicaid_referral_code"]) ? $_POST["medicaid_referral_code"] : ''),
            (isset($_POST["epsdt_flag"]) ? $_POST["epsdt_flag"] : ''),
            (isset($_POST["provider_id"]) ? $_POST["provider_id"] : ''),
            (isset($_POST["provider_qualifier_code"]) ? $_POST["provider_qualifier_code"] : ''),
            (isset($_POST["lab_amount"]) ? $_POST["lab_amount"] : ''),
            (isset($_POST["is_unable_to_work"]) ? $_POST["is_unable_to_work"] : ''),
            (isset($_POST["onset_date"]) ? $_POST["onset_date"] : ''),
            (isset($_POST["date_initial_treatment"]) ? $_POST["date_initial_treatment"] : ''),
            (isset($_POST["off_work_from"]) ? $_POST["off_work_from"] : ''),
            (isset($_POST["off_work_to"]) ? $_POST["off_work_to"] : ''),
            (isset($_POST["is_hospitalized"]) ? $_POST["is_hospitalized"] : ''),
            (isset($_POST["hospitalization_date_from"]) ? $_POST["hospitalization_date_from"] : ''),
            (isset($_POST["hospitalization_date_to"]) ? $_POST["hospitalization_date_to"] : ''),
            (isset($_POST["medicaid_resubmission_code"]) ? $_POST["medicaid_resubmission_code"] : ''),
            (isset($_POST["medicaid_original_reference"]) ? $_POST["medicaid_original_reference"] : ''),
            (isset($_POST["prior_auth_number"]) ? $_POST["prior_auth_number"] : ''),
            (isset($_POST["replacement_claim"]) ? $_POST["replacement_claim"] : ''),
            (isset($_POST["icn_resubmission_number"]) ? $_POST["icn_resubmission_number"] : ''),
            (isset($_POST["box_14_date_qual"]) ? $_POST["box_14_date_qual"] : ''),
            (isset($_POST["box_15_date_qual"]) ? $_POST["box_15_date_qual"] : ''),
            (isset($_POST["comments"]) ? $_POST["comments"] : ''),
            $id
        ]
    );
}

if (isset($_SESSION['billencounter'])) {
    SessionUtil::unsetSession(['billpid', 'billencounter']);
    echo "<script>dlgclose('SubmitTheScreen')</script>";
} else {
    formHeader("Redirecting....");
    formJump();
    formFooter();
}
