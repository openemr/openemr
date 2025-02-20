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

switch ($method) {
    case 'POST':
        $user = json_decode(file_get_contents('php://input'));

        $date = date('Y-m-d');
        $sql = "INSERT INTO users(id, name, email, mobile, created_at) values(null,  $user->name, $user->email, $user->mobile, $date)";
        $result = sqlStatement($sql);

        EventAuditLogger::instance()->newEvent(
            "vehr: insert users",
            null, //pid
            $_SESSION["authUser"], //authUser
            $_SESSION["authProvider"], //authProvider
            $sql,
            1,
            'visual-ehr',
            'dashboard'
        );

        echo json_encode($result);
        break;

    case "GET":
        $sql = "SELECT * FROM form_vitals";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        if (isset($path[4]) && is_numeric($path[4])) {
            $sql .= " WHERE pid = $path[4]";
            $result = sqlStatement($sql);

            if (empty($result)) {
            } else {
                $datalist[] = array(
                    "id" => $result['id'],
                    "date" => $result["date"],
                    "pid" => $result["pid"],
                    "bps" => $result["bps"],
                    "bpd" => $result["bpd"],
                    "weight" => $result["weight"],
                    "height" => $result["height"],
                    "temperature" => $result["temperature"],
                    "temp_method" => $result["temp_method"],
                    "pulse" => $result["pulse"],
                    "respiration" => $result["respiration"],
                    "note" => $result["note"],
                    "BMI" => $result["BMI"],
                    "BMI_status" => $result["BMI_status"],
                    "waist_circ" => $result["waist_circ"],
                    "head_circ" => $result["head_circ"],
                    "oxygen_saturation" => $result["oxygen_saturation"],
                    "external_id" => $result["external_id"],
                    "oxygen_flow_rate" => $result["oxygen_flow_rate"],
                    "ped_weight_height" => $result["ped_weight_height"],
                    "ped_bmi" => $result["ped_bmi"],
                    "ped_head_circ" => $result["ped_head_circ"],
                    "inhaled_oxygen_concentration" => $result["inhaled_oxygen_concentration"],
                );

                EventAuditLogger::instance()->newEvent(
                    "vehr: query form_vitals",
                    $path[4], //pid
                    $_SESSION["authUser"], //authUser
                    $_SESSION["authProvider"], //authProvider
                    $sql,
                    1,
                    'visual-ehr',
                    'dashboard'
                );

                echo json_encode($datalist);
            }
        } else {
            $result = sqlStatement($sql);
            foreach ($result as $data) {
                $datalist[] = array(
                    "id" => $data['id'],
                    "pid" => $data['pid'],
                    "date" => $data["date"],
                    "bps" => $data["bps"],
                    "bpd" => $data["bpd"],
                    "weight" => $data["weight"],
                    "height" => $data["height"],
                    "temperature" => $data["temperature"],
                    "temp_method" => $data["temp_method"],
                    "pulse" => $data["pulse"],
                    "respiration" => $data["respiration"],
                    "note" => $data["note"],
                    "BMI" => $data["BMI"],
                    "BMI_status" => $data["BMI_status"],
                    "waist_circ" => $data["waist_circ"],
                    "head_circ" => $data["head_circ"],
                    "oxygen_saturation" => $data["oxygen_saturation"],
                    "external_id" => $data["external_id"],
                    "oxygen_flow_rate" => $data["oxygen_flow_rate"],
                    "ped_weight_height" => $data["ped_weight_height"],
                    "ped_bmi" => $data["ped_bmi"],
                    "ped_head_circ" => $data["ped_head_circ"],
                    "inhaled_oxygen_concentration" => $data["inhaled_oxygen_concentration"],
                );
            }
            echo json_encode($datalist);
        }
        break;

    case "PUT":
        $user = json_decode(file_get_contents('php://input'));
        $updated_at = date('Y-m-d');
        $sql = "UPDATE users SET name= $user->name, email =$user->email, mobile =$user->mobile, updated_at =$updated_at WHERE id = $user->id";
        $result = sqlStatement($sql);

        EventAuditLogger::instance()->newEvent(
            "vehr: update users",
            null, //pid
            $_SESSION["authUser"], //authUser
            $_SESSION["authProvider"], //authProvider
            $sql,
            1,
            'visual-ehr',
            'dashboard'
        );
        echo json_encode($response);
        break;

    case "DELETE":
        $sql = "DELETE FROM users WHERE id = $path[3]";
        $path = explode('/', $_SERVER['REQUEST_URI']);
        $result = sqlStatement($sql);

        EventAuditLogger::instance()->newEvent(
            "vehr: delete users",
            null, //pid
            $_SESSION["authUser"], //authUser
            $_SESSION["authProvider"], //authProvider
            $sql,
            1,
            'visual-ehr',
            'dashboard'
        );
        echo json_encode($response);
        break;
}
