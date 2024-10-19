<?php

namespace OpenEMR\ClinicalDecisionRules\Interface\Controller;

use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalDetail;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalRange;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervals;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalType;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleAction;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaType;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\TimeUnit;
use OpenEMR\ClinicalDecisionRules\Interface\Common;

/**
 * interface/super/rules/controllers/edit/controller.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class ControllerEdit extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    function _action_summary()
    {
        $ruleId = Common::get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        if (is_null($rule)) {
            $rule = $this->getRuleManager()->newRule();
        }

        $this->viewBean->rule = $rule;
        $this->set_view("summary.php");
    }

    function _action_submit_summary()
    {
        $ruleId = Common::post('id');
        $values = [
            'title'
            ,'developer'
            ,'funding_source'
            ,'release'
            ,'web_reference'
            ,'bibliographic_citation'
            ,'linked_referential_cds'
            ,'patient_dob_usage'
            ,'patient_ethnicity_usage'
            ,'patient_health_status_usage'
            ,'patient_gender_identity_usage'
            ,'patient_language_usage'
            ,'patient_race_usage'
            ,'patient_sex_usage'
            ,'patient_sexual_orientation_usage'
            ,'patient_sodh_usage'
        ];
        $rule = $this->getRuleManager()->getRule($ruleId);
        if (is_null($rule)) {
            $rule = $this->getRuleManager()->newRule();
        }
            $ruleTypes = Common::post('fld_ruleTypes') ?? [];
        if (!is_array($ruleTypes)) {
            $ruleTypes = [$ruleTypes];
        }
        // ruleTypes is an array of string values
        $rule->ruleTypes = $ruleTypes;

            // TODO: could write a validator here
        foreach ($values as $val) {
            if (property_exists($rule, $val)) {
                $rule->$val = Common::post('fld_' . $val, '');
            }
        }
        // its a new rule submit
        $ruleId = $this->getRuleManager()->updateSummaryForRule($rule);
        // redirect to the intervals page
        $this->redirect("index.php?action=edit!intervals&id=" . urlencode($ruleId));
    }

    function _action_intervals()
    {
        $ruleId = Common::get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);

        $this->viewBean->rule = $rule;
        $this->set_view("intervals.php");
    }

    function _action_submit_intervals()
    {
        // parse results from response
        $ruleId = Common::post('id');
        $rule = $this->getRuleManager()->getRule($ruleId);

        // new intervals object
        $intervals = new ReminderIntervals();
        $change = false;
        foreach (ReminderIntervalType::values() as $type) {
            foreach (ReminderIntervalRange::values() as $range) {
                $amtKey = $type->code . "-" . $range->code;
                $timeKey = $amtKey . "-timeunit";

                $amt = Common::post($amtKey);
                $timeUnit = TimeUnit::from(Common::post($timeKey));

                if ($amt && $timeUnit) {
                    $detail = new ReminderIntervalDetail($type, $range, $amt, $timeUnit);
                    $intervals->addDetail($detail);
                    $change = true;
                }
            }
        }

        if ($change) {
            $this->getRuleManager()->updateIntervals($rule, $intervals);
        }

        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_filter()
    {
        $ruleId = Common::get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = Common::get('guid');
        $criteria = $this->getRuleManager()->getRuleFilterCriteria($rule, $guid);

        $this->viewBean->type = "filter";
        $this->viewBean->rule = $rule;
        $this->viewBean->criteria = $criteria;

        $this->addHelper("common.php");

        $this->set_view($criteria->getView(), "criteria.php");
    }

    function _action_delete_filter()
    {
        $ruleId = Common::get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = Common::get('guid');
        $this->getRuleManager()->deleteRuleFilter($rule, $guid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_target()
    {
        $ruleId = Common::get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = Common::get('guid');
        $criteria = $this->getRuleManager()->getRuleTargetCriteria($rule, $guid);

        $this->viewBean->type = "target";
        $this->viewBean->rule = $rule;
        $this->viewBean->criteria = $criteria;

        $this->addHelper("common.php");

        $this->set_view($criteria->getView(), "criteria.php");
    }

    function _action_delete_target()
    {
        $ruleId = Common::get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = Common::get('guid');
        $this->getRuleManager()->deleteRuleTarget($rule, $guid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_codes()
    {
        $search = Common::get('q');
        $codes = $this->getCodeManager()->search($search);
        foreach ($codes as $code) {
            echo text($code->display()) . "|" . text($code->id) . "\n";
        }
    }

    function _action_categories()
    {
        $stmts = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'rule_action_category' AND activity = 1");
        for ($iter = 0; $row = sqlFetchArray($stmts); $iter++) {
            $columns[] = array("code" => $row['option_id'], "lbl" => xl_list_label($row['title']));
        }

        $this->emit_json($columns);
    }

    function _action_items()
    {
        $stmts = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'rule_action' AND activity = 1");
        for ($iter = 0; $row = sqlFetchArray($stmts); $iter++) {
            $columns[] = array("code" => $row['option_id'], "lbl" => xl_list_label($row['title']));
        }

        $this->emit_json($columns);
    }

    function _action_columns()
    {
        $columns = array();
        $table = Common::get('table');
        $stmts = sqlStatement("SHOW COLUMNS FROM " . escape_table_name($table));
        for ($iter = 0; $row = sqlFetchArray($stmts); $iter++) {
            $columns[] = $row['Field'];
        }

        $this->emit_json($columns);
    }

    function _action_submit_criteria()
    {
        // parse results from response
        $ruleId = Common::post('id');
        $groupId = Common::post('group_id');
        $rule = $this->getRuleManager()->getRule($ruleId);

        $guid = Common::post('guid');
        $type = Common::post('type');
        if ($type == "filter") {
            $criteria = $this->getRuleManager()->getRuleFilterCriteria($rule, $guid);
        } else {
            $criteria = $this->getRuleManager()->getRuleTargetCriteria($rule, $guid);
        }

        if (is_null($criteria)) {
            $criteriaType = RuleCriteriaType::from(Common::post('criteriaTypeCode'));
            $criteria = $this->getRuleManager()->createFilterRuleCriteria($rule, $criteriaType);
        }

        if (!is_null($criteria)) {
            $dbView = $criteria->getDbView();
            $criteria->updateFromRequest();
            $dbView = $criteria->getDbView();

            if ($type == "filter") {
                $this->ruleManager->updateFilterCriteria($rule, $criteria);
            } else {
                $this->ruleManager->updateTargetCriteria($rule, $criteria);
            }
        }

        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_action()
    {
        $ruleId = Common::get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = Common::get('guid');
        $action = $this->getRuleManager()->getRuleAction($rule, $guid);
        $this->viewBean->action = $action;
        $this->viewBean->rule = $rule;
        $this->addHelper("common.php");
        $this->set_view("action.php");
    }

    function _action_delete_action()
    {
        $ruleId = Common::get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = Common::get('guid');
        $action = $this->getRuleManager()->deleteRuleAction($rule, $guid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_add_action()
    {
        $ruleId = Common::get('id');
        $groupId = Common::get('group_id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $action = new RuleAction();
        $action->id = $ruleId;
        $action->groupId = $groupId;
        $this->viewBean->action = $action;
        $this->viewBean->rule = $rule;
        $this->addHelper("common.php");
        $this->set_view("action.php");
    }

    function _action_submit_action()
    {
        $ruleId = Common::post('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $groupId = Common::post('group_id');
        $guid = Common::post('guid');

        $category = Common::post("fld_category");
        $categoryLbl = Common::post("fld_category_lbl");
        $item = Common::post("fld_item");
        $itemLbl = Common::post("fld_item_lbl");
        $link = Common::post("fld_link");
        $message = Common::post("fld_message");
        $fld_target_guid = Common::post("fld_target");
        $customOption = Common::post("fld_custom_input") == "yes" ? 1 : 0;

        $action = $this->getRuleManager()->getRuleAction($rule, $guid);
        if (is_null($action)) {
            $action = new RuleAction();
            $action->id = $ruleId;
        }

        // update from post
        $action->category = $category;
        $action->categoryLbl = $categoryLbl;
        $action->item = $item;
        $action->itemLbl = $itemLbl;
        $action->groupId = $groupId;
        $action->customRulesInput = $customOption;
        $action->reminderLink = $link;
        $action->reminderMessage = $message;
        $action->targetCriteria = $fld_target_criteria ?? null;

        $this->getRuleManager()->updateRuleAction($action);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_add_criteria()
    {
        $type = Common::get("criteriaType");
        $id = Common::get("id");
        $groupId = Common::get("group_id");

        if ($type == "filter") {
            $allowed = $this->getRuleManager()->getAllowedFilterCriteriaTypes();
        }

        if ($type == "target") {
            $allowed = $this->getRuleManager()->getAllowedTargetCriteriaTypes();
        }

        $this->viewBean->allowed = $allowed;
        $this->viewBean->id = $id;
        $this->viewBean->groupId = $groupId;
        $this->viewBean->type = $type;
        $this->addHelper("common.php");
        $this->set_view("add_criteria.php");
    }

    function _action_choose_criteria()
    {
        $type = Common::get("type");
        $id = Common::get("id");
        $groupId = Common::get("group_id");

        $criteriaType = RuleCriteriaType::from(Common::get("criteriaType"));
        $rule = $this->getRuleManager()->getRule($id);

        if ($type == "filter") {
            $criteria = $this->getRuleManager()->createFilterRuleCriteria($rule, $criteriaType);
        }

        if ($type == "target") {
            $criteria = $this->getRuleManager()->createTargetRuleCriteria($rule, $criteriaType);
        }

        $criteria->groupId = $groupId;
        $this->viewBean->type = $type;
        $this->viewBean->rule = $rule;
        $this->viewBean->criteria = $criteria;

        $this->addHelper("common.php");

        $this->set_view($criteria->getView(), "criteria.php");
    }
}
