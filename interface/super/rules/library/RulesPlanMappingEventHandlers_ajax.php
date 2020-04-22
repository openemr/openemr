<?php

/**
 * library/RulesPlanMappingEventHandlers.php database interaction for admin-gui rules plan mappings.
 *
 * Functions to allow safe database modifications
 * during changes to rules-plan mapping in Admin UI.
 *
 * Copyright (C) 2014 Jan Jajalla <Jajalla23@gmail.com>
 * Copyright (C) 2014 Roberto Vasquez <robertogagliotta@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Jan Jajalla <jajalla23@gmail.com>
 * @author  Roberto Vasquez <roberto.gagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__) . "/../../../globals.php");
require_once(dirname(__FILE__) . "/RulesPlanMappingEventHandlers.php");

$action = $_GET["action"];
switch ($action) {
    case "getNonCQMPlans":
        $plans = getNonCQMPlans();

        echo text(json_encode($plans));

        break;

    case "getRulesOfPlan":
        $rules = getRulesInPlan($_GET["plan_id"]);

        $rules_list = array();
        foreach ($rules as $key => $value) {
            $rule_info = array('rule_id' => $key, 'rule_title' => $value);
            array_push($rules_list, $rule_info);
        }

        echo json_encode($rules_list);

        break;

    case "getRulesNotInPlan":
        $rules = getRulesNotInPlan($_GET["plan_id"]);

        $rules_list = array();
        foreach ($rules as $key => $value) {
            $rule_info = array('rule_id' => $key, 'rule_title' => $value);
            array_push($rules_list, $rule_info);
        }

        echo json_encode($rules_list);

        break;

    case "getRulesInAndNotInPlan":
        $rules = getRulesInPlan($_GET["plan_id"]);

        $rules_list = array();
        foreach ($rules as $key => $value) {
            $rule_info = array('rule_id' => $key, 'rule_title' => $value, 'selected' => 'true');
            array_push($rules_list, $rule_info);
        }

        $rules = getRulesNotInPlan($_GET["plan_id"]);
        foreach ($rules as $key => $value) {
            $rule_info = array('rule_id' => $key, 'rule_title' => $value, 'selected' => 'false');
            array_push($rules_list, $rule_info);
        }

        echo json_encode($rules_list);

        break;

    case "commitChanges":
        $data = json_decode(file_get_contents('php://input'), true);

        $plan_id = $data['plan_id'];
        $added_rules = $data['added_rules'];
        $removed_rules = $data['removed_rules'];
        $plan_name = $data['plan_name'];

        if ($plan_id == 'add_new_plan') {
            try {
                $plan_id = addNewPlan($plan_name, $added_rules);
            } catch (Exception $e) {
                $status_mssg = $e->getMessage();
                $status_code = '001';

                if ($e->getMessage() == "002") {
                    //Plan Name Taken
                    $status_code = '002';
                    $status_mssg = xl('Plan Name Already Exists');
                } elseif ($e->getMessage() == "003") {
                    //Already in list options
                    $status_code = '003';
                    $status_mssg = xl('Plan Already in list_options');
                }

                $status = array('status_code' => $status_code, 'status_message' => $status_mssg, 'plan_id' => $plan_id, 'plan_title' => $plan_name);
                echo text(json_encode($status));

                break;
            }
        } elseif (strlen($plan_id) > 0) {
            submitChanges($plan_id, $added_rules, $removed_rules);
        }

        $status = array('status_code' => '000', 'status_message' => 'Success', 'plan_id' => $plan_id, 'plan_title' => $plan_name);
        echo text(json_encode($status));

        break;

    case "deletePlan":
        $plan_id = $_GET["plan_id"];
        deletePlan($plan_id);

        break;

    case "togglePlanStatus":
        $dataToggle  = json_decode(file_get_contents('php://input'), true);

        $plan_id_toggle = $dataToggle['selected_plan'];
        $plan_pid_toggle = $dataToggle['selected_plan_pid'];
        $active_inactive = $dataToggle['plan_status'];
        if ($active_inactive == 'deactivate') {
            $nm_flag = 0;
        } else {
            $nm_flag = 1;
        }

        try {
            togglePlanStatus($plan_id_toggle, $nm_flag);
        } catch (Exception $e) {
            if ($e->getMessage() == "007") {
                $code_back = "007";
                echo json_encode($code_back);
            }

            if ($e->getMessage() == "002") {
                $code_back = "002";
                echo json_encode($code_back);
            }
        }
        break;

    case "getPlanStatus":
        $plan_id = $_GET["plan_id"];

        $isPlanActive = isPlanActive($plan_id);

        $isPlanActive = ($isPlanActive) ? 1 : 0 ;

        $plan_status = array('plan_id' => attr($plan_id), 'is_plan_active' => $isPlanActive);
        echo json_encode($plan_status);

        break;

    default:
        break;
}
