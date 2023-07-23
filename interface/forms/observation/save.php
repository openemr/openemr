<?php

/**
 * Functional cognitive status form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
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

$id             = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
$code           = $_POST["code"];
$code_obs       = $_POST["comments"];
$code_desc      = $_POST["description"];
$code_type      = $_POST["code_type"];
$table_code     = $_POST["table_code"];
$ob_value       = $_POST["ob_value"];
$ob_value_phin  = $_POST["ob_value_phin"];
$ob_unit        = $_POST["ob_unit"];
$code_date      = $_POST["code_date"];
$reasonCode     = $_POST['reasonCode'];
$reasonStatusCode     = $_POST['reasonCodeStatus'];
$reasonCodeText     = $_POST['reasonCodeText'];
$ob_type        = $_POST["ob_type"];
$code_date_end  = $_POST["code_date_end"];


if ($id && $id != 0) {
    sqlStatement(
        "DELETE FROM `form_observation` WHERE id=? AND pid = ? AND encounter = ?",
        array($id, $_SESSION["pid"], $_SESSION["encounter"])
    );
    $newid = $id;
} else {
    $res2 = sqlStatement("SELECT MAX(id) as largestId FROM `form_observation`");
    $getMaxid = sqlFetchArray($res2);
    if ($getMaxid['largestId']) {
        $newid = $getMaxid['largestId'] + 1;
    } else {
        $newid = 1;
    }

    addForm($encounter, "Observation Form", $newid, "observation", $_SESSION["pid"], $userauthorized);
}


$code_desc = array_filter($code_desc);
if (!empty($code_desc)) {
    foreach ($code_desc as $key => $codeval) :
        $ob_unit_value = $ob_unit[$key];
        if ($code[$key] == 'SS003') {
            $ob_value[$key] = $ob_value_phin[$key];
            $ob_unit_value = "";
        } elseif ($code[$key] == '8661-1') {
            $ob_unit_value = "";
        } elseif ($code[$key] == '21612-7') {
            if (!empty($ob_unit)) {
                foreach ($ob_unit as $key1 => $val) :
                    if ($key1 == 0) {
                        $ob_unit_value = $ob_unit[$key1];
                    } else {
                        if ($key1 == $key) {
                            $ob_unit_value = $ob_unit[$key1];
                        }
                    }
                endforeach;
            }
        }

        $sets = "id     = ?,
            pid         = ?,
            groupname   = ?,
            user        = ?,
            encounter   = ?,
            authorized  = ?,
            activity    = 1,
            observation = ?,
            code        = ?,
            code_type   = ?,
            description = ?,
            table_code  = ?,
            ob_type     = ?,
            ob_value    = ?,
            ob_unit     = ?,
            date        = ?,
            ob_reason_code = ?,
            ob_reason_status = ?,
            ob_reason_text = ?,
            date_end    = ?";
        sqlStatement(
            "INSERT INTO form_observation SET $sets",
            [
                $newid,
                $_SESSION["pid"],
                $_SESSION["authProvider"],
                $_SESSION["authUser"],
                $_SESSION["encounter"],
                $userauthorized,
                $code_obs[$key],
                $code[$key],
                $code_type[$key],
                $code_desc[$key],
                $table_code[$key],
                $ob_type[$key],
                $ob_value[$key],
                $ob_unit_value,
                $code_date[$key],
                $reasonCode[$key],
                $reasonStatusCode[$key],
                $reasonCodeText[$key],
                $code_date_end[$key] ?: null
            ]
        );
    endforeach;
}

formHeader("Redirecting....");
formJump();
formFooter();
