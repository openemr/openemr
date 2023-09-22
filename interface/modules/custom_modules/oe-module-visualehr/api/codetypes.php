<?php

/**
 * Contains all of the Visual Dashboard global settings and configuration
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 Visual EHR <https://visualehr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$_SESSION['site_id'] = 'default';

require_once "../../../../globals.php";

use OpenEMR\Common\Logging\EventAuditLogger;


$method = $_SERVER['REQUEST_METHOD'];

$dropdown = array();

//query occurence and assign it to the dropdown array()
$sql = "SELECT ct_key, ct_id, ct_seq, ct_label FROM `code_types` where ct_problem = 1 AND ct_active = 1";
$problem_rel = sqlStatement($sql);

EventAuditLogger::instance()->newEvent(
    "vehr: query code-types-problems",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'visual-ehr',
    'dashboard'
);

$sql = "SELECT ct_key, ct_id, ct_seq, ct_label FROM `code_types` where ct_drug = 1 AND ct_active = 1";
$medication_rel = sqlStatement($sql);

EventAuditLogger::instance()->newEvent(
    "vehr: query code-types-medication",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'visual-ehr',
    'dashboard'
);

$dropdown = array(
    "medication_rel" => $medication_rel,
    "problem_rel" => $problem_rel,
);

echo json_encode($dropdown);
