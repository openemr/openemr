<?php

/**
 * transfer summary form.
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
  transfer_to = ?,
  transfer_date = ?,
  status_of_admission = ?,
  diagnosis = ?,
  intervention_provided = ?,
  overall_status_of_discharge = ?";


if (empty($id)) {
    $newid = sqlInsert(
        "INSERT INTO form_transfer_summary SET $sets",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["provider"],
            $_POST["client_name"],
            $_POST["transfer_to"],
            $_POST["transfer_date"],
            $_POST["status_of_admission"],
            $_POST["diagnosis"],
            $_POST["intervention_provided"],
            $_POST["overall_status_of_discharge"]
        ]
    );
    addForm($encounter, "Transfer Summary", $newid, "transfer_summary", $pid, $userauthorized);
} else {
    sqlStatement(
        "UPDATE form_transfer_summary SET $sets WHERE id = ?",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["provider"],
            $_POST["client_name"],
            $_POST["transfer_to"],
            $_POST["transfer_date"],
            $_POST["status_of_admission"],
            $_POST["diagnosis"],
            $_POST["intervention_provided"],
            $_POST["overall_status_of_discharge"],
            $id
        ]
    );
}

formHeader("Redirecting....");
formJump();
formFooter();
