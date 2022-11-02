<?php

/**
 * treatment plan form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Naina Mohamed <naina@capminds.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

$id = (int) (isset($_GET['id']) ? $_GET['id'] : '');

$sets = "pid = ?,
  groupname = ?,
  user = ?,
  authorized = ?,
  activity = 1,
  date = NOW(),
  provider = ?,
  client_name = ?,
  client_number = ?,
  admit_date = ?,
  presenting_issues = ?,
  patient_history = ?,
  medications = ?,
  anyother_relevant_information = ?,
  diagnosis = ?,
  treatment_received = ?,
  recommendation_for_follow_up = ?";

if (empty($id)) {
    $newid = sqlInsert(
        "INSERT INTO form_treatment_plan SET $sets",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["provider"],
            $_POST["client_name"],
            $_POST["client_number"],
            $_POST["admit_date"],
            $_POST["presenting_issues"],
            $_POST["patient_history"],
            $_POST["medications"],
            $_POST["anyother_relevant_information"],
            $_POST["diagnosis"],
            $_POST["treatment_received"],
            $_POST["recommendation_for_follow_up"]
        ]
    );

    addForm($encounter, "Treatment Plan", $newid, "treatment_plan", $pid, $userauthorized);
} else {
    sqlStatement(
        "UPDATE form_treatment_plan SET $sets WHERE id = ?",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["provider"],
            $_POST["client_name"],
            $_POST["client_number"],
            $_POST["admit_date"],
            $_POST["presenting_issues"],
            $_POST["patient_history"],
            $_POST["medications"],
            $_POST["anyother_relevant_information"],
            $_POST["diagnosis"],
            $_POST["treatment_received"],
            $_POST["recommendation_for_follow_up"],
            $id
        ]
    );
}

formHeader("Redirecting....");
formJump();
formFooter();
