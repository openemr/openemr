<?php

/**
 * aftercare_plan save.php
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
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (! $encounter) { // comes from globals.php
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
    admit_date = ?,
    discharged = ?,
    goal_a_acute_intoxication =  ?,
    goal_a_acute_intoxication_I = ?,
    goal_a_acute_intoxication_II = ?,
    goal_b_emotional_behavioral_conditions = ?,
    goal_b_emotional_behavioral_conditions_I = ?,
    goal_c_relapse_potential = ?,
    goal_c_relapse_potential_I =  ?";


if (empty($id)) {
    $newid = sqlInsert(
        "INSERT INTO form_aftercare_plan SET $sets",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["provider"],
            $_POST["client_name"],
            $_POST["admit_date"],
            $_POST["discharged"],
            $_POST["goal_a_acute_intoxication"],
            $_POST["goal_a_acute_intoxication_I"],
            $_POST["goal_a_acute_intoxication_II"],
            $_POST["goal_b_emotional_behavioral_conditions"],
            $_POST["goal_b_emotional_behavioral_conditions_I"],
            $_POST["goal_c_relapse_potential"],
            $_POST["goal_c_relapse_potential_I"]
        ]
    );

    addForm($encounter, "Aftercare Plan", $newid, "aftercare_plan", $pid, $userauthorized);
} else {
    sqlStatement(
        "UPDATE form_aftercare_plan SET $sets WHERE id = ?;",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["provider"],
            $_POST["client_name"],
            $_POST["admit_date"],
            $_POST["discharged"],
            $_POST["goal_a_acute_intoxication"],
            $_POST["goal_a_acute_intoxication_I"],
            $_POST["goal_a_acute_intoxication_II"],
            $_POST["goal_b_emotional_behavioral_conditions"],
            $_POST["goal_b_emotional_behavioral_conditions_I"],
            $_POST["goal_c_relapse_potential"],
            $_POST["goal_c_relapse_potential_I"],
            $id
        ]
    );
}

formHeader("Redirecting....");
formJump();
formFooter();
