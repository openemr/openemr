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

//query occurence and assign it to the dropdown array()
$sql = "SELECT option_id as id, list_id, title as name FROM `list_options` where list_id LIKE '%occurrence%' AND activity = 1";
$occurrence_result = array();
foreach (sqlStatement($sql) as $data) {
    $occurrence_result[] = $data;
}
EventAuditLogger::instance()->newEvent(
    "vehr: query occurence",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'open-emr',
    'dashboard'
);


//query outcome and assign it to the dropdown array()
$sql = "SELECT option_id as id, list_id, title as name FROM `list_options` where list_id LIKE '%outcome%' AND activity = 1";
$outcome_result = array();
foreach (sqlStatement($sql) as $data) {
    $outcome_result[] = $data;
}
EventAuditLogger::instance()->newEvent(
    "vehr: query outcome",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'open-emr',
    'dashboard'
);


//query verification_status and assign it to the dropdown array()
$sql = "SELECT option_id as id, list_id, title as name FROM `list_options` where list_id LIKE '%condition-verification%' AND activity = 1";
$verification_result = array();
foreach (sqlStatement($sql) as $data) {
    $verification_result[] = $data;
}
EventAuditLogger::instance()->newEvent(
    "vehr: query condition-verification",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'open-emr',
    'dashboard'
);

$sql = "SELECT option_id as id, list_id, title as name FROM `list_options` where list_id LIKE '%medication-usage-category%' AND activity = 1";
$medication_usage = array();
foreach (sqlStatement($sql) as $data) {
    $medication_usage[] = $data;
}
EventAuditLogger::instance()->newEvent(
    "vehr: query medication-usage-category",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'open-emr',
    'dashboard'
);

$sql = "SELECT option_id as id, list_id, title as name FROM `list_options` where list_id LIKE '%medication-request-intent%' AND activity = 1";
$request_intent = array();
foreach (sqlStatement($sql) as $data) {
    $request_intent[] = $data;
}
EventAuditLogger::instance()->newEvent(
    "vehr: query medication-request-intent",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'open-emr',
    'dashboard'
);

$sql = "SELECT option_id as id, list_id, title as name FROM `list_options` where list_id LIKE '%severity_ccda%' AND activity = 1";
$severity = array();
foreach (sqlStatement($sql) as $data) {
    $severity[] = $data;
}
EventAuditLogger::instance()->newEvent(
    "vehr: query severity_ccda",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'open-emr',
    'dashboard'
);

$sql = "SELECT option_id as id, list_id, title as name FROM `list_options` where list_id LIKE '%reaction%' AND activity = 1";
$reaction = array();
foreach (sqlStatement($sql) as $data) {
    $reaction[] = $data;
}
EventAuditLogger::instance()->newEvent(
    "vehr: query reaction",
    null, //pid
    $_SESSION["authUser"], //authUser
    $_SESSION["authProvider"], //authProvider
    $sql,
    1,
    'open-emr',
    'dashboard'
);


$dropdown = array(
    "occurrence" => $occurrence_result,
    "outcome" => $outcome_result,
    "verification" => $verification_result,
    "medication_usage" => $medication_usage,
    "request_intent" => $request_intent,
    "severity" => $severity,
    "reaction" => $reaction,
);

echo json_encode($dropdown);
