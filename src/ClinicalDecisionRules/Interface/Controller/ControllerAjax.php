<?php

/**
 * database interaction for admin-gui rules plan mappings.
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

namespace OpenEMR\ClinicalDecisionRules\Interface\Controller;

use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use OpenEMR\ClinicalDecisionRules\Interface\RulesPlanMappingEventHandlers;

class ControllerAjax extends BaseController
{
    public function _action_getNonCQMPlans()
    {
        $plans = RulesPlanMappingEventHandlers::getNonCQMPlans();
        $this->emit_json($plans);
    }

    public function _action_getRulesOfPlan()
    {
        $rules = RulesPlanMappingEventHandlers::getRulesInPlan($_GET["plan_id"]);
        $rules_list = array();

        foreach ($rules as $key => $value) {
            $rule_info = array('rule_id' => $key, 'rule_title' => $value);
            array_push($rules_list, $rule_info);
        }

        $this->emit_json($rules_list);
    }

    public function _action_getRulesNotInPlan()
    {
        $rules = RulesPlanMappingEventHandlers::getRulesNotInPlan($_GET["plan_id"]);
        $rules_list = array();

        foreach ($rules as $key => $value) {
            $rule_info = array('rule_id' => $key, 'rule_title' => $value);
            array_push($rules_list, $rule_info);
        }

        $this->emit_json($rules_list);
    }

    public function _action_getRulesInAndNotInPlan()
    {
        $rules = RulesPlanMappingEventHandlers::getRulesInPlan($_GET["plan_id"]);
        $rules_list = array();

        foreach ($rules as $key => $value) {
            $rule_info = array('rule_id' => $key, 'rule_title' => $value, 'selected' => 'true');
            array_push($rules_list, $rule_info);
        }

        $rules = RulesPlanMappingEventHandlers::getRulesNotInPlan($_GET["plan_id"]);
        foreach ($rules as $key => $value) {
            $rule_info = array('rule_id' => $key, 'rule_title' => $value, 'selected' => 'false');
            array_push($rules_list, $rule_info);
        }

        $this->emit_json($rules_list);
    }

    public function _action_commitChanges()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $plan_id = $data['plan_id'];
        $added_rules = $data['added_rules'];
        $removed_rules = $data['removed_rules'];
        $plan_name = $data['plan_name'];

        if ($plan_id == 'add_new_plan') {
            try {
                $plan_id = RulesPlanMappingEventHandlers::addNewPlan($plan_name, $added_rules);
            } catch (\Exception $e) {
                $status_code = '001';
                $status_mssg = $e->getMessage();

                if ($e->getMessage() == "002") {
                    $status_code = '002';
                    $status_mssg = xl('Plan Name Already Exists');
                } elseif ($e->getMessage() == "003") {
                    $status_code = '003';
                    $status_mssg = xl('Plan Already in list_options');
                }

                $status = array('status_code' => $status_code, 'status_message' => $status_mssg, 'plan_id' => $plan_id, 'plan_title' => $plan_name);
                $this->emit_json($status);

                return;
            }
        } elseif (!empty($plan_id)) {
            RulesPlanMappingEventHandlers::submitChanges($plan_id, $added_rules, $removed_rules);
        }

        $status = array('status_code' => '000', 'status_message' => 'Success', 'plan_id' => $plan_id, 'plan_title' => $plan_name);
        $this->emit_json($status);
    }

    public function _action_deletePlan()
    {
        $plan_id = $_GET["plan_id"];
        RulesPlanMappingEventHandlers::deletePlan($plan_id);
    }

    public function _action_togglePlanStatus()
    {
        $dataToggle = json_decode(file_get_contents('php://input'), true);
        $plan_id_toggle = $dataToggle['selected_plan'];
        $active_inactive = $dataToggle['plan_status'];
        $nm_flag = ($active_inactive == 'deactivate') ? 0 : 1;

        try {
            RulesPlanMappingEventHandlers::togglePlanStatus($plan_id_toggle, $nm_flag);
        } catch (\Exception $e) {
            // do a preg replace of all non-numeric values in exception message to just be safe in our values here
            $code_back = $e->getMessage();
            $code_back = preg_replace('/[^0-9]/', '', $code_back);
            $this->emit_json($code_back);
        }
    }

    public function _action_getPlanStatus()
    {
        $plan_id = $_GET["plan_id"];
        $isPlanActive = RulesPlanMappingEventHandlers::isPlanActive($plan_id);
        $isPlanActive = ($isPlanActive) ? 1 : 0;

        $plan_status = array('plan_id' => attr($plan_id), 'is_plan_active' => $isPlanActive);
        $this->emit_json($plan_status);
    }
}
