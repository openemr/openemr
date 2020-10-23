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

function getNonCQMPlans()
{
    $plans = array();

    $sql_st =   "SELECT DISTINCT list_options.title, list_options.option_id as plan_id, clin_plans.pid " .
                "FROM `list_options` list_options " .
                "JOIN `clinical_plans` clin_plans ON clin_plans.id = list_options.option_id " .
                "LEFT JOIN `clinical_plans_rules` clin_plans_rules ON clin_plans_rules.plan_id = list_options.option_id " .
                "LEFT JOIN `clinical_rules` clin_rules ON clin_rules.id = clin_plans_rules.rule_id " .
                "WHERE (clin_rules.cqm_flag = 0 OR clin_rules.cqm_flag is NULL) " .
                "AND list_options.option_id NOT LIKE '%plan_cqm' AND clin_plans.pid = 0 AND list_options.list_id = ?;";
    $result = sqlStatement($sql_st, array('clinical_plans'));

    while ($row = sqlFetchArray($result)) {
        $plan_id = $row['plan_id'];
        $plan_pid = $row['pid'];
        $plan_title = $row['title'];

        $plan_info = array('plan_id' => $plan_id, 'plan_pid' => $plan_pid, 'plan_title' => $plan_title);
        array_push($plans, $plan_info);
    }

    return $plans;
}

function getRulesInPlan($plan_id)
{
    $rules = array();

    $sql_st = "SELECT lst_opt.option_id as rule_option_id, lst_opt.title as rule_title " .
                "FROM `clinical_plans_rules` cpr " .
                "JOIN `list_options` lst_opt ON lst_opt.option_id = cpr.rule_id " .
                "WHERE cpr.plan_id = ?;";
    $result = sqlStatement($sql_st, array($plan_id));

    while ($row = sqlFetchArray($result)) {
        $rules[$row['rule_option_id']] = $row['rule_title'];
    }

    return $rules;
}

function getRulesNotInPlan($plan_id)
{
    $rules = array();

    $sql_st = "SELECT lst_opt.option_id as rule_option_id, lst_opt.title as rule_title " .
                "FROM `clinical_rules` clin_rules " .
                "JOIN `list_options` lst_opt ON lst_opt.option_id = clin_rules.id " .
                "WHERE clin_rules.cqm_flag = 0 AND clin_rules.amc_flag = 0 AND lst_opt.option_id NOT IN " .
                    "( " .
                    "SELECT lst_opt.option_id " .
                    "FROM `clinical_plans_rules` cpr " .
                    "JOIN `list_options` lst_opt ON lst_opt.option_id = cpr.rule_id " .
                    "WHERE cpr.plan_id = ?" .
                    "); ";
    $result = sqlStatement($sql_st, array($plan_id));

    while ($row = sqlFetchArray($result)) {
        $rules[$row['rule_option_id']] = $row['rule_title'];
    }

    return $rules;
}

function addNewPlan($plan_name, $plan_rules)
{
    //Validate if plan name already exists
    $sql_st = "SELECT `option_id` " .
                "FROM `list_options` " .
                "WHERE `list_id` = 'clinical_plans' AND `title` = ?;";
    $res = sqlStatement($sql_st, array($plan_name));
    $row = sqlFetchArray($res);
    if ($row['option_id'] != null) {
        throw new Exception("002");
    }

    //Generate Plan Id
    $plan_id = generatePlanID();


    //Validate if plan id already exists in list options table
    $sql_st = "SELECT `option_id` " .
                "FROM `list_options` " .
                "WHERE `option_id` = ?;";
    $res = sqlStatement($sql_st, array($plan_id));
    $row = sqlFetchArray($res);
    if ($row != null) {
        //001 = plan name taken
        throw new Exception("003");
    }

    //Add plan into clinical_plans table
    $sql_st = "INSERT INTO `clinical_plans` (`id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group`) " .
                "VALUES (?, 0, 1, 0, '');";
    $res = sqlStatement($sql_st, array($plan_id));


    //Get sequence value
    $sql_st = "SELECT MAX(`seq`) AS max_seq " .
                "FROM `list_options` " .
                "WHERE `list_id` = 'clinical_plans'; ";
    $res = sqlStatement($sql_st, null);
    $max_seq = 0;

    if ($res != null) {
        while ($row = sqlFetchArray($res)) {
            $max_seq = $row['max_seq'];
        }

        $max_seq += 10;
    }


    //Insert plan into list_options table
    $sql_st = "INSERT INTO `list_options` " .
                "(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) " .
                "VALUES ('clinical_plans', ?, ?, ?, 0, 0, '', '', '');";
    $res = sqlStatement($sql_st, array($plan_id, $plan_name, $max_seq));


    //Add rules to plan
    addRulesToPlan($plan_id, $plan_rules);

    return $plan_id;
}

function deletePlan($plan_id)
{
    $sql_st = "DELETE FROM `clinical_plans` WHERE `clinical_plans`.`id` = ?;";
    $res = sqlStatement($sql_st, array($plan_id));

    $sql_st = "DELETE FROM `list_options` WHERE `list_id` = 'clinical_plans' AND `option_id` = ?;";
    $res = sqlStatement($sql_st, array($plan_id));

    $sql_st = "DELETE FROM `clinical_plans_rules` WHERE `plan_id` = ?;";
    $res = sqlStatement($sql_st, array($plan_id));
}

function togglePlanStatus($plan_id, $nm_flag)
{
         $sql_st = "UPDATE clinical_plans SET " .
                   "normal_flag = ? " .
                   "WHERE id = ? AND pid = 0 ";
         sqlStatement($sql_st, array($nm_flag, $plan_id));
    if ($nm_flag = 0) {
        $nm_chk = 1;
    }

    if ($nm_flag = 1) {
        $nm_chk = 0;
    }

           $sql_check = "SELECT `id` " .
                              "FROM `clinical_plans` " .
                              "WHERE ((`id` = ?) AND (`pid` = 0) AND (`normal_flag` = ?));";
         $res_chk = sqlStatement($sql_check, array($plan_id, $nm_chk));
         $row_chk = sqlFetchArray($res_chk);
    if ($row_chk == $plan_id) {
        throw new Exception("002");
    } else {
        throw new Exception("007");
    }
}

function submitChanges($plan_id, $added_rules, $removed_rules)
{
    //add
    if (sizeof($added_rules) > 0) {
        addRulesToPlan($plan_id, $added_rules);
    }

    //remove
    if (sizeof($removed_rules) > 0) {
        removeRulesFromPlan($plan_id, $removed_rules);
    }
}

function addRulesToPlan($plan_id, $list_of_rules)
{
    //Insert
    $sql_st = "INSERT INTO `clinical_plans_rules` (`plan_id`, `rule_id`) " .
                "VALUES (?, ?);";

    foreach ($list_of_rules as $rule) {
        //Check if rule already exists in plan
        $sql_st_check = "SELECT * FROM `clinical_plans_rules` " .
                        "WHERE `plan_id` = ? and `rule_id` = ?";
        $res_check = sqlStatement($sql_st_check, array($plan_id, $rule));
        $row = sqlFetchArray($res_check);
        if ($row == null) {
            $res = sqlStatement($sql_st, array($plan_id, $rule));
        }
    }
}

function removeRulesFromPlan($plan_id, $list_of_rules)
{
    $sql_st = "DELETE FROM `clinical_plans_rules` " .
                "WHERE `plan_id` = ? AND `rule_id` = ?;";

    foreach ($list_of_rules as $rule) {
        $res = sqlStatement($sql_st, array($plan_id, $rule));
    }
}

function generatePlanID()
{
    $plan_id = 1;
    $sql_st = "SELECT MAX(SUBSTR(clin_plans.id, 1, LOCATE('_plan', clin_plans.id)-1)) as max_planid " .
            "FROM `clinical_plans` clin_plans " .
            "WHERE clin_plans.id like '%_plan' AND SUBSTR(clin_plans.id, 1, LOCATE('_plan', clin_plans.id)) REGEXP '[0-9]+'; ";
    $res = sqlStatement($sql_st, null);

    if ($res != null) {
        while ($row = sqlFetchArray($res)) {
            $plan_id = $row['max_planid'];
        }

        $plan_id += 1;
    }

    $plan_id = $plan_id . '_plan';

    return $plan_id;
}

function isPlanActive($plan_id)
{
    $sql_st = "SELECT `normal_flag` " .
                "FROM `clinical_plans` " .
                "WHERE `id` = ? AND `pid` = 0;";

    $res = sqlStatement($sql_st, array($plan_id));

    $row = sqlFetchArray($res);
    if ($row['normal_flag'] == 1) {
        return true;
    } else {
        return false;
    }
}
