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
        $ruleId = _get('id');
        $types = _post('fld_ruleTypes');
        $title = _post('fld_title');
        $developer = _post('fld_developer');
        $funding = _post('fld_funding_source');
        $release = _post('fld_release');
        $web_ref = _post('fld_web_reference');
        $public_description = _post('fld_public_description');
        $viewer ='';
        
        if ($ruleId) {
            $viewer = "undecorated.php";
        }
        
        $ruleId = $this->getRuleManager()->updateSummary($ruleId, $types, $title, $developer, $funding, $release, $web_ref, $public_description);
        $rule = $this->getRuleManager()->getRule($ruleId);
        if (is_null($viewer)) {
            $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
        }
        $this->viewBean->rule = $rule;
        $this->set_view("view_summary.php", $viewer);
    }
    
    function _action_createCR()
    {
        $ruleId = _get('id');
        $types = _post('fld_ruleTypes');
        $title = _post('fld_title');
        $developer = _post('fld_developer');
        $funding = _post('fld_funding_source');
        $release = _post('fld_release');
        $web_ref = _post('fld_web_reference');
        $public_description = _post('fld_public_description');
        
        $ruleId = $this->getRuleManager()->updateSummary($ruleId, $types, $title, $developer, $funding, $release, $web_ref, $public_description);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
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
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);

        // new intervals object
        $intervals = new ReminderIntervals();
        $change = false;
        foreach (ReminderIntervalType::values() as $type) {
            foreach (ReminderIntervalRange::values() as $range) {
                $amtKey = $type->code . "-" . $range->code;
                $timeKey = $amtKey . "-timeunit";

                $amt = _post($amtKey);
                if (empty($amt)) {
                    $amt ='1';
                }
                $timeUnit = TimeUnit::from(_post($timeKey));
                if (empty($timeUnit)) {
                    $timeUnit ='month';
                }
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
        
        $rule = $this->getRuleManager()->getRule($ruleId);
        $this->viewBean->rule = $rule;
        $this->set_view("view_summary.php", "undecorated.php");
    }

    function _action_filter()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $rf_uid = _get('rf_uid');
        $criteria = $this->getRuleManager()->getRuleFilterCriteria($rule, $rf_uid);

        $this->viewBean->type = "filter";
        $this->viewBean->rule = $rule;
        $this->viewBean->criteria = $criteria;

        $this->addHelper("common.php");

        $this->set_view($criteria->getView(), "criteria_filter.php");
    }

    function _action_delete_filter()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $rf_uid = _get('rf_uid');
        $this->getRuleManager()->deleteRuleFilter($rule, $rf_uid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_target()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $rt_uid = _get('rt_uid');
        $groupId = _get('group_id');//Not used?
        // Do we need group here?  We have it available as _get('group').
        // Theorectically, with more than one group there could be
        // same target w/ different criterion for a different action
        
        $criteria = $this->getRuleManager()->getRuleTargetCriteria($rule, $rt_uid);

        $this->viewBean->type = "target";
        $this->viewBean->rule = $rule;
        $this->viewBean->criteria = $criteria;

        $this->addHelper("common.php");
        $this->set_view($criteria->getView(), "criteria_target.php");
    }

    function _action_delete_target()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $rt_uid = _get('rt_uid');
        $this->getRuleManager()->deleteRuleTarget($rule, $rt_uid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_codes()
    {
        $search = _get('q');
        $codes = $this->getCodeManager()->search($search);
        foreach ($codes as $code) {
            echo text($code->display()) . "|". text($code->id) . "\n";
        }
    }

    function _action_categories()
    {
        $stmts = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'rule_action_category' AND activity = 1");
        for ($iter=0; $row=sqlFetchArray($stmts); $iter++) {
            $columns[] = array( "code" => $row['option_id'], "lbl" => xl_list_label($row['title']) );
        }

        $this->emit_json($columns);
    }

    function _action_items()
    {
        $stmts = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'rule_action' AND activity = 1");
        for ($iter=0; $row=sqlFetchArray($stmts); $iter++) {
            $columns[] = array( "code" => $row['option_id'], "lbl" => xl_list_label($row['title']) );
        }

        $this->emit_json($columns);
    }

    function _action_columns()
    {
        $columns = array();
        $table = _get('table');
        $stmts = sqlStatement("SHOW COLUMNS FROM " . escape_table_name($table));
        for ($iter=0; $row=sqlFetchArray($stmts); $iter++) {
            $columns[] = $row['Field'];
        }

        $this->emit_json($columns);
    }

    function _action_submit_criteria()
    {
        // parse results from response
        $ruleId = _post('id');
        $groupId = _post('group_id');//hold up, not being used... why not?
        $rule = $this->getRuleManager()->getRule($ruleId);
        
        $rf_uid = _post('rf_uid');
        $rt_uid = _post('rt_uid');
        $type = _post('type');
        $criteriaTypeCode = _post('criteriaTypeCode');
        if (($type == "filter") && (!empty($rf_uid))) {
            $criteria = $this->getRuleManager()->getRuleFilterCriteria($rule, $rf_uid);
        } else if (!empty($rt_uid)) { //then it looks like it must be a target ...
            $criteria = $this->getRuleManager()->getRuleTargetCriteria($rule, $rt_uid);
        }
       
        if (is_null($criteria)) {
            $criteriaType = RuleCriteriaType::from($criteriaTypeCode);
            if ($type == "filter") {
                $criteria = $this->getRuleManager()->createFilterRuleCriteria($rule, $criteriaType);
            } else {
                $criteria = $this->getRuleManager()->createTargetRuleCriteria($rule, $criteriaType);
            }
        }
     
        if (!is_null($criteria)) {
            $dbView = $criteria->getDbView();
            $criteria->updateFromRequest();
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
        $uid = _get('ra_uid');
        $action = $this->getRuleManager()->getRuleAction($rule, $uid);
        $this->viewBean->action = $action;
        $this->viewBean->rule = $rule;
        $this->addHelper("common.php");
        $this->set_view("action.php", "undecorated.php");
    }

    function _action_delete_action()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $ra_uid = _get('ra_uid');
        $action = $this->getRuleManager()->deleteRuleAction($rule, $ra_uid);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }
    
    function _action_delete_rule()
    {
        $ruleId = _post('id');
        if (!is_null($ruleId)) {
            $this->getRuleManager()->deleteRuleTotally($ruleId);
            $this->redirect("index.php?action=alerts!listactmgr");
        }
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
        $this->set_view("action.php", "undecorated.php");
    }

    function _action_submit_action()
    {
        $ruleId = _post('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $groupId = _post('group_id');
        $ra_uid = _post('ra_uid');

        $category = _post("fld_category");
        $categoryLbl = _post("fld_category_lbl");
        $item = _post("fld_item");
        $itemLbl = _post("fld_item_lbl");
        $link = _post("fld_link");
        $message = _post("fld_message");
        
        $customOption = _post("fld_custom_input") == "yes" ? 1 : 0;

        $action = $this->getRuleManager()->getRuleAction($rule, $ra_uid);
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
        //$action->targetCriteria = $fld_target_criteria;//where is this from???
        //again no where to be found around here...

        $this->getRuleManager()->updateRuleAction($action);
        $this->redirect("index.php?action=detail!view&id=" . urlencode($ruleId));
    }

    function _action_add_criteria()
    {
        $ruleId = _get('id');
        $rule = $this->getRuleManager()->getRule($ruleId);
        $type = _get("criteriaType");
        $groupId = _get('group_id');
    
        if ($type == "filter") {
            $allowed = $this->getRuleManager()->getAllowedFilterCriteriaTypes();
        }

        if ($type == "target") {
            $allowed = $this->getRuleManager()->getAllowedTargetCriteriaTypes();
        }

        $this->viewBean->allowed = $allowed;
        $this->viewBean->type = $type;
        $this->viewBean->rule = $rule;
        $this->viewBean->rule_id = $ruleId;
        $this->viewBean->groupId = $groupId;
        $this->addHelper("common.php");
        $this->set_view("add_criteria.php", "criteria_".$type.".php");
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
        
        if (!empty($groupId)) {
            $criteria->groupId = $groupId;
        }
        $this->viewBean->type = $type;
        $this->viewBean->rule = $rule;
        $this->viewBean->id= $id;
        $this->viewBean->criteria = $criteria;

        $this->addHelper("common.php");

        $this->set_view($criteria->getView(), "criteria_".$type.".php");
    }
}
