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


function getFavorites($type)
{
    //$sql = "SELECT option_id as id, list_id, title as name FROM `list_options` where list_id = $type AND activity = 1";
    $sql = "SELECT option_id as id, list_id, title as name, codes FROM `list_options` WHERE `list_id` LIKE '$type' ORDER BY `option_id` ASC";
    $result = array();
    foreach (sqlStatement($sql) as $data) {
        $result[] = $data;
    }
    EventAuditLogger::instance()->newEvent(
        "vehr: query list_options",
        null, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );
    return $result;
}

$data = array(
    "medication_favorites" => getFavorites("medication_issue_list"),
    "allergy_favorites" => getFavorites("allergy_issue_list"),
    "problem_favorites" => getFavorites("medical_problem_issue_list"),
    "suregery_favorites" => getFavorites("surgery_issue_list"),
);

echo json_encode($data);
