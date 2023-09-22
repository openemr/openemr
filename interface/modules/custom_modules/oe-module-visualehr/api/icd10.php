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

$datalist = [];
switch ($method) {
    case "GET":
        $sql = "SELECT * FROM `icd10_dx_order_code`";
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($path[3])) {
            $sql .= " WHERE (formatted_dx_code LIKE '%$path[3]%' OR short_desc LIKE '%$path[3]%') ORDER BY short_desc LIMIT 100";
            $result = sqlStatement($sql);
            EventAuditLogger::instance()->newEvent(
                "vehr: query icd10_dx_order_code",
                null, //pid
                $_SESSION["authUser"], //authUser
                $_SESSION["authProvider"], //authProvider
                $sql,
                1,
                'visual-ehr',
                'dashboard'
            );

            foreach ($result as $data) {
                $datalist[] = array(
                    "dx_id" => $data['dx_id'],
                    "dx_code" => $data['dx_code'],
                    "formatted_dx_code" => $data['formatted_dx_code'],
                    "valid_for_coding" => $data['valid_for_coding'],
                    "short_desc" => $data['short_desc'],
                    "long_desc" => $data['long_desc'],
                    "active" => $data['active'],
                    "revision" => $data['revision']
                );
            }
            if (!empty($datalist)) {
                echo json_encode($datalist);
            } else {
                echo json_encode($datalist);
            }
        } else {
            $sql .= "LIMIT 100";
            $result = sqlStatement($sql);
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

            foreach ($result as $data) {
                $datalist[] = array(
                    "dx_id" => $data['dx_id'],
                    "dx_code" => $data['dx_code'],
                    "formatted_dx_code" => $data['formatted_dx_code'],
                    "valid_for_coding" => $data['valid_for_coding'],
                    "short_desc" => $data['short_desc'],
                    "long_desc" => $data['long_desc'],
                    "active" => $data['active'],
                    "revision" => $data['revision']
                );
            }
            echo json_encode($datalist);
        }
        break;
}
