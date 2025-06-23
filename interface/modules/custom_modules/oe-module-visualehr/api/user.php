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

if (!isset($_GET["userId"])) {
    echo json_encode([]);
    return;
}
$userId = preg_replace("#[^0-9]#", "", $_GET["userId"]);

if ($method == 'GET') {
    echo json_encode(getUserDetails($userId));
}


function getUserDetails($userId)
{
    $datalist = array();
    $sql = "SELECT * FROM users WHERE id = '$userId'";
    $result = sqlStatement($sql);
    foreach ($result as $data) {
        $datalist[] = array(
            "id"             => $data['id'],
            "username"       => $data['username'],
            "fullname"       => $data['fname'] . " " . $data['mname'] . " " . $data['lname']
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query users",
        null, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    return $datalist;
}
