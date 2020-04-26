<?php

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

class Controller_edit extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    function _action_summary()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        if (is_null($rule)) {
            $rule = $this->getRuleManager()->newRule();
        }

        $this->viewBean->rule = $rule;
        $this->set_view("summary.php");
    }

    function _action_submit_summary()
    {
        $ruleId = _post('id');
        $types = _post('fld_ruleTypes');
        $title = _post('fld_title');
        $developer = _post('fld_developer');
        $funding = _post('fld_funding_source');
        $release = _post('fld_release');
        $web_ref = _post('fld_web_reference');
        if (is_null($rule_id)) {
            // its a new rule submit
            $ruleId = $this->getRuleManager()->updateSummary($ruleId, $types, $title, $developer, $funding, $release, $web_ref);
            // redirect to the intervals page
            $this->redirect("index.php?action=edit!intervals&id=" . urlencode($ruleId));
        } else {
            $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
        }
    }

    function _action_intervals()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);

        $this->viewBean->rule = $rule;
        $this->set_view("intervals.php");
    }

    function _action_submit_intervals()
    {
        // parse results from response
        $ruleId = _post('id');
        $rule = $this->getRuleManager()->getRule($ruleId);

        // new intervals object
        $intervals = new ReminderIntervals();
        $change = false;
        foreach (ReminderIntervalType::values() as $type) {
            foreach (ReminderIntervalRange::values() as $range) {
                $amtKey = $type->code . "-" . $range->code;
                $timeKey = $amtKey . "-timeunit";

                $amt = _post($amtKey);
                $timeUnit = TimeUnit::from(_post($timeKey));

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
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = _get('guid');
        $criteria = $this->getRuleManager()->getRuleFilterCriteria($rule, $guid);

        $this->viewBean->type = "filter";
        $this->viewBean->rule = $rule;
        $this->viewBean->criteria = $criteria;

        $this->addHelper("common.php");

        $this->set_view($criteria->getView(), "criteria.php");
    }

    function _action_delete_filter()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = _get('guid');
        $this->getRuleManager()->deleteRuleFilter($rule, $guid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_target()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = _get('guid');
        $criteria = $this->getRuleManager()->getRuleTargetCriteria($rule, $guid);

        $this->viewBean->type = "target";
        $this->viewBean->rule = $rule;
        $this->viewBean->criteria = $criteria;

        $this->addHelper("common.php");

        $this->set_view($criteria->getView(), "criteria.php");
    }

    function _action_delete_target()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = _get('guid');
        $this->getRuleManager()->deleteRuleTarget($rule, $guid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_codes()
    {
        $search = _get('q');
        $codes = $this->getCodeManager()->search($search);
        foreach ($codes as $code) {
            echo text($code->display()) . "|" . text($code->id) . "\n";
        }
    }

    function _action_categories()
    {
        $stmts = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'rule_action_category' AND activity = 1");
        for ($iter = 0; $row = sqlFetchArray($stmts); $iter++) {
            $columns[] = array( "code" => $row['option_id'], "lbl" => xl_list_label($row['title']) );
        }

        $this->emit_json($columns);
    }

    function _action_items()
    {
        $stmts = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'rule_action' AND activity = 1");
        for ($iter = 0; $row = sqlFetchArray($stmts); $iter++) {
            $columns[] = array( "code" => $row['option_id'], "lbl" => xl_list_label($row['title']) );
        }

        $this->emit_json($columns);
    }

    function _action_columns()
    {
        $columns = array();
        $table = _get('table');
        $stmts = sqlStatement("SHOW COLUMNS FROM " . escape_table_name($table));
        for ($iter = 0; $row = sqlFetchArray($stmts); $iter++) {
            $columns[] = $row['Field'];
        }

        $this->emit_json($columns);
    }

    function _action_submit_criteria()
    {
        // parse results from response
        $ruleId = _post('id');
        $groupId = _post('group_id');
        $rule = $this->getRuleManager()->getRule($ruleId);

        $guid = _post('guid');
        $type = _post('type');
        if ($type == "filter") {
            $criteria = $this->getRuleManager()->getRuleFilterCriteria($rule, $guid);
        } else {
            $criteria = $this->getRuleManager()->getRuleTargetCriteria($rule, $guid);
        }

        if (is_null($criteria)) {
            $criteriaType = RuleCriteriaType::from(_post('criteriaTypeCode'));
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
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = _get('guid');
        $action = $this->getRuleManager()->getRuleAction($rule, $guid);
        $this->viewBean->action = $action;
        $this->viewBean->rule = $rule;
        $this->addHelper("common.php");
        $this->set_view("action.php");
    }

    function _action_delete_action()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $guid = _get('guid');
        $action = $this->getRuleManager()->deleteRuleAction($rule, $guid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_add_action()
    {
        $ruleId = _get('id');
        $groupId = _get('group_id');
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
        $ruleId = _post('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $groupId = _post('group_id');
        $guid = _post('guid');

        $category = _post("fld_category");
        $categoryLbl = _post("fld_category_lbl");
        $item = _post("fld_item");
        $itemLbl = _post("fld_item_lbl");
        $link = _post("fld_link");
        $message = _post("fld_message");
        $fld_target_guid = _post("fld_target");
        $customOption = _post("fld_custom_input") == "yes" ? 1 : 0;

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
        $action->targetCriteria = $fld_target_criteria;

        $this->getRuleManager()->updateRuleAction($action);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_add_criteria()
    {
        $type = _get("criteriaType");
        $id = _get("id");
        $groupId = _get("group_id");

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
        $type = _get("type");
        $id = _get("id");
        $groupId = _get("group_id");

        $criteriaType = RuleCriteriaType::from(_get("criteriaType"));
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
