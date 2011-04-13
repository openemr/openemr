<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<?php

require_once( src_dir() . "/clinical_rules.php");
require_once( library_src( 'RuleCriteriaFilterFactory.php') );
require_once( library_src( 'RuleCriteriaTargetFactory.php') );

/**
 * Responsible for handling the persistence (CRU operations, deletes are
 * not currently supported).
 * This class should be kept synchronized with clinical_rules.php
 * @author aron
 */
class RuleManager {
    const SQL_RULE_DETAIL =
    "SELECT lo.title as title, cr.*
           FROM clinical_rules cr
           JOIN list_options lo
            ON (cr.id = lo.option_id AND lo.list_id = 'clinical_rules')";

    const SQL_RULE_REMINDER_INTERVAL =
    "SELECT id,
            method,
            method_detail,
            value
       FROM rule_reminder
      WHERE id = ?";

    const SQL_RULE_FILTER =
    "SELECT PASSWORD(CONCAT( id, include_flag, required_flag, method, method_detail, value )) AS guid, rule_filter.*
       FROM rule_filter WHERE id = ?";

    const SQL_RULE_TARGET =
    "SELECT PASSWORD(CONCAT( id, group_id, include_flag, required_flag, method, value, rule_target.interval )) AS guid, rule_target.*
       FROM rule_target WHERE id = ?";

    const SQL_RULE_FILTER_BY_GUID =
    "SELECT * FROM rule_filter
     WHERE PASSWORD(CONCAT( id, include_flag, required_flag, method, method_detail, value )) = ?";
    
    const SQL_RULE_TARGET_BY_GUID =
    "SELECT * FROM rule_target
     WHERE PASSWORD(CONCAT( id, group_id, include_flag, required_flag, method, value, rule_target.interval )) = ?";

     const SQL_RULE_TARGET_BY_ID_GROUP_ID =
    "SELECT PASSWORD(CONCAT( id, group_id, include_flag, required_flag, method, value, rule_target.interval )) AS guid, rule_target.*
     FROM rule_target WHERE id = ? AND group_id = ?";

    const SQL_RULE_ACTIONS =
    "SELECT PASSWORD( CONCAT(id, category, item, group_id) ) AS guid, rule_action.* FROM rule_action
     WHERE id = ?";

    const SQL_RULE_ACTION_BY_GUID =
    "SELECT rule_action.*,
            rule_action_item.clin_rem_link,
            rule_action_item.reminder_message,
            rule_action_item.custom_flag
       FROM rule_action JOIN rule_action_item ON (rule_action_item.category = rule_action.category AND rule_action_item.item = rule_action.item )
     WHERE PASSWORD( CONCAT(rule_action.id, rule_action.category, rule_action.item, rule_action.group_id ) ) = ?";

    const SQL_UPDATE_FLAGS =
    "UPDATE clinical_rules
        SET active_alert_flag = ?,
            passive_alert_flag = ?,
            cqm_flag = ?,
            amc_flag = ?,
            patient_reminder_flag = ?
      WHERE id = ?";

    const SQL_UPDATE_TITLE =
    "UPDATE list_options
        SET title = ?
      WHERE list_id = 'clinical_rules' AND option_id = ?";

    const SQL_REMOVE_INTERVALS =
    "DELETE FROM rule_reminder
           WHERE id = ?";

    const SQL_INSERT_INTERVALS =
    "INSERT INTO rule_reminder
            (id, method, method_detail, value)
     VALUES ( ?, ?, ?, ?)";

    const SQL_UPDATE_FILTER =
    "UPDATE rule_filter SET include_flag = ?, required_flag = ?, method = ?, method_detail = ?, value = ?
      WHERE PASSWORD(CONCAT( id, include_flag, required_flag, method, method_detail, value )) = ?";

    const SQL_INSERT_FILTER =
    "INSERT INTO rule_filter (id, include_flag, required_flag, method, method_detail, value )
     VALUES ( ?, ?, ?, ?, ?, ? )";

    const SQL_UPDATE_TARGET =
    "UPDATE rule_target SET include_flag = ?, required_flag = ?, method = ?, value = ?
       WHERE PASSWORD(CONCAT( id, group_id, include_flag, required_flag, method, value, rule_target.interval )) = ?";

    const SQL_INSERT_TARGET =
    "INSERT INTO rule_target ( id, include_flag, required_flag, method, value, group_id )
     VALUES ( ?, ?, ?, ?, ?, ? )";

    var $filterCriteriaFactory;
    var $targetCriteriaFactory;

    function __construct() {
        $this->filterCriteriaFactory = new RuleCriteriaFilterFactory();
        $this->targetCriteriaFactory = new RuleCriteriaTargetFactory();
    }

    /**
     * Returns a Rule object if the supplied rule id matches a record in
     * clinical_rules. An optional patient id parameter allows you to get the
     * rules specific to the patient.
     *
     * Returns null if no rule is found matching the id or patient.
     * @param <type> $id
     * @param <type> $pid
     * @return Rule
     */
    function getRule($id, $pid = 0) {
        $ruleResult = sqlQuery( 
            self::SQL_RULE_DETAIL . " WHERE id = ? AND pid = ?", array($id, $pid)
        );

        if ( !$ruleResult ) {
            return null;
        }

        $rule = new Rule($id, $ruleResult['title']);
        $this->fillRuleTypes( $rule, $ruleResult );
        $this->fillRuleReminderIntervals( $rule );
        $this->fillRuleFilterCriteria( $rule );
        $this->fillRuleTargetActionGroups( $rule );

        return $rule;
    }

    function newRule() {
        $rule = new Rule();
        return $rule;
    }

    /**
     * Adds a RuleType to the given rule based on the sql result row
     * passed to it, evaluating the *_flag columns.
     * @param Rule $rule
     */
    private function fillRuleTypes( $rule, $ruleResult ) {
        if ($ruleResult['active_alert_flag'] == 1) {
            $rule->addRuleType(RuleType::from(RuleType::ActiveAlert));
        }
        if ($ruleResult['passive_alert_flag'] == 1) {
            $rule->addRuleType(RuleType::from(RuleType::PassiveAlert));
        }

        // not yet supported
        if ($ruleResult['cqm_flag'] == 1) {
            $rule->addRuleType(RuleType::from(RuleType::CQM));
        }
        if ($ruleResult['amc_flag'] == 1) {
            $rule->addRuleType(RuleType::from(RuleType::AMC));
        }
        if ($ruleResult['patient_reminder_flag'] == 1) {
            $rule->addRuleType(RuleType::from(RuleType::PatientReminder));
        }
    }

    /**
     * Fills the given rule with criteria derived from the rule_filter
     * table. Relies on the RuleCriteriaFilterFactory for the parsing of
     * rows in this table into concrete subtypes of RuleCriteria.
     * @param Rule $rule
     */
    private function fillRuleFilterCriteria( $rule ) {
        $stmt = sqlStatement( self::SQL_RULE_FILTER, array( $rule->id ) );
        $criterion = $this->gatherCriteria($rule, $stmt, $this->filterCriteriaFactory);
        $ruleFilters = new RuleFilters();
        $rule->setRuleFilters($ruleFilters);
        if ( sizeof( $criterion ) > 0 ) {
            foreach( $criterion as $criteria ) {
                $ruleFilters->add( $criteria );
            }
        }
    }
    
    private function fillRuleTargetActionGroups( $rule ) {
        $stmt = sqlStatement( self::SQL_RULE_TARGET, array( $rule->id ) );
        $criterion = $this->gatherCriteria($rule, $stmt, $this->targetCriteriaFactory);
                
        $ruleTargetGroups = $this->fetchRuleTargetCriteria( $rule );
        $ruleActionGroups = $this->fetchRuleActions( $rule );
        $groups = array();
        $groupCount = max( end(array_keys($ruleTargetGroups)), end(array_keys($ruleActionGroups)) );
        for ( $groupId = 0; $groupId <= $groupCount; $groupId++ ) {
            
            $group = new RuleTargetActionGroup( $groupId );
            $addGroup = false;
            if ( isset( $ruleTargetGroups[$groupId] ) ) {
                $group->setRuleTargets( $ruleTargetGroups[$groupId] );
                $addGroup = true;
            }
            
            if ( isset( $ruleActionGroups[$groupId] ) ) {
                $group->setRuleActions( $ruleActionGroups[$groupId] );
                $addGroup = true;
            }
            
            if ( $addGroup == true ) {
                $groups[$groupId]= $group;
            }
        }
        
        $rule->setGroups( $groups );
    }

    /**
     * @param Rule $rule
     */
    private function fetchRuleTargetCriteria( $rule ) {
        $stmt = sqlStatement( self::SQL_RULE_TARGET, array( $rule->id ) );
        $criterion = $this->gatherCriteria($rule, $stmt,
                $this->targetCriteriaFactory);
        $ruleTargetGroups = array();
        if ( sizeof( $criterion ) > 0 ) {
            foreach( $criterion as $criteria ) {
                if ( !isset( $ruleTargetGroups[$criteria->groupId] ) ) {
                    $ruleTargetGroups[$criteria->groupId] = new RuleTargets();        
                }
                $ruleTargetGroups[$criteria->groupId]->add( $criteria );
            }    
        }
        ksort($ruleTargetGroups);
        return $ruleTargetGroups;
    }
    
	/**
     * @param Rule $rule
     */
    private function fetchRuleActions( $rule ) {
        $stmt = sqlStatement( self::SQL_RULE_ACTIONS, array( $rule->id ) );
        $ruleActionGroups = array();
        for ( $iter=0; $row=sqlFetchArray($stmt); $iter++ ) {
            $action = new RuleAction();
            $action->category = $row['category'];
            $action->item = $row['item'];
            $action->guid = $row['guid'];
            $action->groupId = $row['group_id'];
            if ( !isset( $ruleActionGroups[$action->groupId] ) ) {
                $ruleActionGroups[$action->groupId] = new RuleActions();
            }
            $ruleActionGroups[$action->groupId]->add( $action );
        }
        ksort($ruleActionGroups);
        return $ruleActionGroups;
    }

    /**
     * @param string $guid
     * @return RuleCriteria
     */
    function getRuleFilterCriteria( $rule, $guid ) {
        $stmt = sqlStatement( self::SQL_RULE_FILTER_BY_GUID, array( $guid ) );
        $criterion = $this->gatherCriteria($rule, $stmt,
                $this->filterCriteriaFactory );
        if ( sizeof( $criterion ) > 0 ) {
            $criteria = $criterion[0];
            $criteria->guid = $guid;
            return $criterion[0];
        }

        return null;
    }
    
	/**
     * @param string $guid
     * @return array of RuleTargetActionGroup
     */
    function getRuleTargetActionGroups( $rule ) {
        $criterion = $this->getRuleTargetCriteria( $rule );
        $actions = $this->getRuleAction( $rule );
        if ( sizeof( $criterion ) > 0 ) {
            $criteria = $criterion[0];
            $criteria->guid = $guid;
            return $criterion[0];
        }

        return null;
    }

    /**
     * @param string $guid
     * @return RuleCriteria
     */
    function getRuleTargetCriteria( $rule, $guid ) {
        $stmt = sqlStatement( self::SQL_RULE_TARGET_BY_GUID, array( $guid ) );
        $criterion = $this->gatherCriteria($rule,
                $stmt, $this->targetCriteriaFactory );
        if ( sizeof( $criterion ) > 0 ) {
            $criteria = $criterion[0];
            $criteria->guid = $guid;
            return $criteria;
        }

        return null;
    }

    /**
     * @param string $guid
     * @return RuleCriteria
     */
    function getRuleTargetCriteriaByGroupId( $rule, $groupId ) {
        $stmt = sqlStatement( self::SQL_RULE_TARGET_BY_ID_GROUP_ID, array( $rule->id, $groupId ) );
        $criterion = $this->gatherCriteria($rule,
                $stmt, $this->targetCriteriaFactory );
        if ( sizeof( $criterion ) > 0 ) {
            $criteria = $criterion[0];
            return $criterion[0];
        }

        return null;
    }

    /**
     * Given a sql source for gathering rule criteria (target or filter), this
     * method relies on its supplied subtype of RuleCriteriaFactory to parse out
     * instances of RuleCriteria from the sql source (typically rule_filter or
     * rule_target).
     *
     * Returns an array of RuleCriteria subtypes, if they were parsable from the
     * supplied sql source.
     * @param Rule $rule
     * @param RuleCriteriaFactory $factory
     */
    private function gatherCriteria( $rule, $stmt, $factory ) {
        $criterion = array();
        for($iter=0; $row=sqlFetchArray($stmt); $iter++) {
            $guid = $row['guid'];
            $method = $row['method'];
            $methodDetail = $row['method_detail'];
            $value = $row['value'];
            $inclusion = $row['include_flag'] == 1;
            $optional = $row['required_flag'] == 1;
            $groupId =  $row['group_id'];


            $criteria = $factory->build( $rule->id, $guid, $inclusion, $optional,
                    $method, $methodDetail, $value );

            if ( is_null($criteria) ) {
                // unrecognized critera
                continue;
            }

            if ( !is_null($groupId ) ) {
                $criteria->groupId = $groupId;
            }

            // else
            array_push($criterion, $criteria );
        }

        return $criterion;
    }

    /**
     * Creates a ReminderIntervals object from rows in the rule_reminder table,
     * and sets it in the supplied Rule.
     * @param Rule $rule
     */
    private function fillRuleReminderIntervals( $rule ) {
        $stmt = sqlStatement( self::SQL_RULE_REMINDER_INTERVAL, array( $rule->id ) );
        $reminderInterval = new ReminderIntervals();

        for($iter=0; $row=sqlFetchArray($stmt); $iter++) {
            $amount = $row['value'];
            $unit = TimeUnit::from($row['method_detail']);
            $methodParts = explode( '_', $row['method'] );
            $type = ReminderIntervalType::from( $methodParts[0] );
            $range = ReminderIntervalRange::from( $methodParts[2] );
            if ( !is_null($type) && !is_null($range) && !is_null($unit) ) {
                $detail = new ReminderIntervalDetail( $type, $range, $amount, $unit );
                $reminderInterval->addDetail($detail);
            }
        }

        $rule->setReminderIntervals( $reminderInterval );
    }

    /**
     *
     * @return RuleAction
     */
    function getRuleAction( $rule, $guid ) {
        $result = sqlQuery( self::SQL_RULE_ACTION_BY_GUID, array($guid) );

        if ( !$result ) {
            return null;
        }

        $action = new RuleAction();
        $action->guid = $guid;
        $action->id = $result['id'];
        $action->category = $result['category'];
        $action->item = $result['item'];
        $action->reminderLink = $result['clin_rem_link'];
        $action->reminderMessage = $result['reminder_message'];
        $action->customRulesInput = $result['custom_flag'] == 1;
        $action->groupId = $result['group_id'];

        $target = $this->getRuleTargetCriteriaByGroupId($rule, $action->groupId);

        $action->targetCriteria = $target;

        return $action;
    }

    function deleteRuleAction( $rule, $guid ) {
        sqlQuery( "DELETE FROM rule_action WHERE PASSWORD( CONCAT(id, category, item, group_id) ) = '". $guid . "'" );
    }

    function deleteRuleTarget( $rule, $guid ) {
        sqlQuery( "DELETE FROM rule_target WHERE PASSWORD(CONCAT( id, group_id, include_flag, required_flag, method, value, rule_target.interval )) = '". $guid . "'" );
    }

    function deleteRuleFilter( $rule, $guid ) {
        sqlQuery( "DELETE FROM rule_filter WHERE PASSWORD(CONCAT( id, include_flag, required_flag, method, method_detail, value )) = '". $guid . "'" );
    }

    function updateSummary( $ruleId, $types, $title ) {
        $rule = $this->getRule( $ruleId );

        if ( is_null($rule) ) {
            // add
            $result = sqlQuery( "select count(*)+1 AS id from clinical_rules" );
            $ruleId = "rule_" . $result['id'];
            sqlQuery( sqlStatement( "INSERT INTO clinical_rules (id, pid, active_alert_flag, passive_alert_flag, cqm_flag, amc_flag, patient_reminder_flag ) " . 
                    "VALUES (?,?,?,?,?,?,?) ",
                    array(
                        $ruleId,
                        0,
                        in_array(RuleType::ActiveAlert, $types) ? 1 : 0,
                        in_array(RuleType::PassiveAlert, $types) ? 1 : 0,
                        in_array(RuleType::CQM, $types) ? 1 : 0,
                        in_array(RuleType::AMC, $types) ? 1 : 0,
                        in_array(RuleType::PatientReminder, $types) ? 1 : 0
                    )
            ));

            // do label
            $this->doRuleLabel(false, "clinical_rules", $ruleId, $title);
            return $ruleId;
        } else {
            // edit
            // update flags
            sqlQuery(sqlStatement( self::SQL_UPDATE_FLAGS, array(
                in_array(RuleType::ActiveAlert, $types) ? 1 : 0,
                in_array(RuleType::PassiveAlert, $types) ? 1 : 0,
                in_array(RuleType::CQM, $types) ? 1 : 0,
                in_array(RuleType::AMC, $types) ? 1 : 0,
                in_array(RuleType::PatientReminder, $types) ? 1 : 0,
                $rule->id )
            ));

            // update title
            sqlQuery( sqlStatement( self::SQL_UPDATE_TITLE, array( $title,
                $ruleId ) ));
            return $ruleId;
        }
    }

    /**
     *
     * @param Rule $rule
     * @param ReminderIntervals $intervals
     */
    function updateIntervals( $rule, $intervals ) {
        // remove old intervals
        sqlQuery(sqlStatement( self::SQL_REMOVE_INTERVALS, array( $rule->id )));

        // insert new intervals
        foreach( $intervals->getTypes() as $type ) {
            $typeDetails = $intervals->getDetailFor($type);
            foreach( $typeDetails as $detail ) {
                sqlQuery( sqlStatement( self::SQL_INSERT_INTERVALS, array(
                    $rule->id,                                                      //id
                    $type->code . "_reminder_" . $detail->intervalRange->code,      // method
                    $detail->timeUnit->code,                                        // method_detail
                    $detail->amount                                                 // value
                )));

            }
        }
    }

    /**
     *
     * @param Rule $rule
     * @param RuleCriteria $criteria
     */
    function updateFilterCriteria( $rule, $criteria ) {
        $dbView = $criteria->getDbView();
        $method = "filt_" . $dbView->method;

        $guid = $criteria->guid;
        if ( is_null( $guid ) ) {
            /// insert
            sqlQuery(sqlStatement( self::SQL_INSERT_FILTER, array(
                $rule->id,
                $dbView->inclusion ? 1 : 0,
                $dbView->optional ? 1 : 0,
                $dbView->method = $method,
                $dbView->methodDetail = $dbView->methodDetail,
                $dbView->value = $dbView->value)
            ));
        } else {
            // update flags
            sqlQuery(sqlStatement( self::SQL_UPDATE_FILTER, array(
                $dbView->inclusion ? 1 : 0,
                $dbView->optional ? 1 : 0,
                $dbView->method = $method,
                $dbView->methodDetail = $dbView->methodDetail,
                $dbView->value = $dbView->value,
                $criteria->guid )
            ));
        }
    }

    /**
     *
     * @param Rule $rule
     * @param RuleCriteria $criteria
     */
    function updateTargetCriteria( $rule, $criteria ) {
        $dbView = $criteria->getDbView();
        $method = "target_" . $dbView->method;

        $guid = $criteria->guid;
        $group_id = $criteria->groupId;

        if ( is_null( $guid ) ) {
            /// insert
            if ( !$group_id ) {
                $result = sqlQuery( "SELECT max(group_id) AS group_id FROM rule_target WHERE id = ?", array($rule->id) );
                $group_id = 1;
                if ( $result ) {
                    $group_id = $result['group_id'] ? $result['group_id'] + 1 : 1;
                }
            }
            
            sqlQuery(sqlStatement( self::SQL_INSERT_TARGET, array(
                $rule->id,
                $dbView->inclusion ? 1 : 0,
                $dbView->optional ? 1 : 0,
                $dbView->method = $method,
                $dbView->value = $dbView->value,
                $group_id )
            ));

        } else {
            // update flags
            sqlQuery(sqlStatement( self::SQL_UPDATE_TARGET, array(
                $dbView->inclusion ? 1 : 0,
                $dbView->optional ? 1 : 0,
                $dbView->method = $method,
                $dbView->value = $dbView->value,
                $criteria->guid )
            ));
        }

        // interval
        $result = sqlQuery( "SELECT COUNT(*) AS interval_count FROM rule_target WHERE rule_target.id = ? AND rule_target.method = ?",
                array($rule->id, 'target_interval') );
        if ($result && $result['interval_count'] > 0 ) {
            // update interval
            $intervalSql =
                "UPDATE rule_target
                    SET rule_target.value = ?, rule_target.interval = ?
                  WHERE rule_target.method = ?
                    AND rule_target.id = ?";

            sqlQuery(sqlStatement( $intervalSql, array(
                $dbView->intervalType,
                $dbView->interval,
                'target_interval',
                $rule->id )
            ));
        } else {
            // insert
            sqlQuery(sqlStatement( "INSERT INTO rule_target ( rule_target.value, rule_target.interval, rule_target.method, rule_target.id ) "
                                 . "VALUES ( ?, ?, ?, ? ) ", array(
                $dbView->intervalType,
                $dbView->interval,
                'target_interval',
                $rule->id )
            ));
        }
    }

    function getAllowedFilterCriteriaTypes() {
        $allowed = array();
        foreach( RuleCriteriaType::values() as $type ) {
            $criteria = RuleCriteriaType::from($type);
            array_push($allowed, $criteria);
        }
        return $allowed;
    }

    function getAllowedTargetCriteriaTypes() {
        $allowed = array();
        array_push($allowed, RuleCriteriaType::from(RuleCriteriaType::lifestyle));
        array_push($allowed, RuleCriteriaType::from(RuleCriteriaType::custom));
        array_push($allowed, RuleCriteriaType::from(RuleCriteriaType::custom_bucket));
        return $allowed;
    }

    /**
    *
     * @param Rule $rule
     * @param RuleCriteriaType $criteriaType
     * @return RuleCriteria
     */
    function createFilterRuleCriteria( $rule, $criteriaType ) {
        return $this->filterCriteriaFactory->buildNewInstance($rule->id, $criteriaType);
    }

    /**
    *
     * @param Rule $rule
     * @param RuleCriteriaType $criteriaType
     * @return RuleCriteria
     */
    function createTargetRuleCriteria( $rule, $criteriaType ) {
        return $this->targetCriteriaFactory->buildNewInstance($rule->id, $criteriaType);
    }

    /**
    *
     *
     * @param RuleAction $action
     */
    function updateRuleAction( $action ) {
        $ruleId = $action->id;
        $rule = $this->getRule( $ruleId );
        $groupId = $action->groupId;
        $guid = $action->guid;

        $category = $action->category;
        $categoryLbl = $action->categoryLbl;
        $item = $action->item;
        $itemLbl = $action->itemLbl;
        $link = $action->reminderLink;
        $message = $action->reminderMessage;
        $customOption = $action->customRulesInput;

        // do labels -- if new category or item, insert them
        $exists = $this->labelExists('rule_action_category', $category, $categoryLbl);
        if ( !$exists ) {
            $category = 'act_cat_' . $categoryLbl;
        }
        $this->doRuleLabel($exists, 'rule_action_category', $category, $categoryLbl);

        $exists = $this->labelExists('rule_action', $item, $itemLbl);
        if ( !$exists ) {
            $item = 'act_' . $itemLbl;
        }
        $this->doRuleLabel($exists, 'rule_action', $item, $itemLbl);

        // persist action itself
        if ( !$guid ) {
            // its a brand new action
            sqlQuery(sqlStatement(
                    "INSERT INTO rule_action (id, group_id, category, item ) VALUES (?,?,?,?)",
                    array( $ruleId, $groupId, $category, $item )
            ));

        } else {
            // its an action edit
            if ( !is_null( $groupId ) ) {
                sqlQuery(sqlStatement(
                    "UPDATE rule_action SET group_id = ?, category = ?, item = ? " .
                     "WHERE PASSWORD( CONCAT(rule_action.id, rule_action.category, rule_action.item, rule_action.group_id ) ) = ? ",
                    array( $groupId, $category, $item, $guid )
                ));
            }
        }

        // handle rule action_item
        $result = sqlQuery( "SELECT * FROM rule_action_item WHERE category = ? AND item = ?", array($category, $item));
        if ( $result ) {
            sqlQuery(sqlStatement( "UPDATE rule_action_item SET clin_rem_link = ?, reminder_message = ?, custom_flag = ? "
                                  . "WHERE category = ? AND item = ?", array(
                $link,
                $message,
                $customOption,
                $category,
                $item )
            ));
        } else {
            sqlQuery(sqlStatement( "INSERT INTO rule_action_item (clin_rem_link, reminder_message, custom_flag, category, item) "
                                 . "VALUES (?,?,?,?,?)", array(
                $link,
                $message,
                $customOption,
                $category,
                $item )
            ));
        }
    }

    private function doRuleLabel( $exists, $listId, $optionId, $title ) {
        if ( $exists) {
            // edit
            sqlQuery(sqlStatement( "UPDATE list_options SET title = ? WHERE list_id = ? AND option_id = ?", array(
                $title,
                $listId,
                $optionId )
            ));
        } else {
            // update
            $result = sqlQuery( "select max(seq)+10 AS seq from list_options where list_id = ?", array($listId) );
            $seq = $result['seq'];
            sqlQuery(sqlStatement("INSERT INTO list_options (list_id,option_id,title,seq) VALUES ( ?, ?, ?, ? )", array(
                $listId,
                $optionId,
                $title,
                $seq )
            ));
        }
    }

    private function labelExists( $listId, $optionId, $title ) {
        $result = sqlQuery( "SELECT COUNT(*) AS CT FROM list_options WHERE list_id = ? AND option_id = ? AND title = ?", array($listId, $optionId, $title) );
        if ( $result && $result['CT'] > 0 ) {
            return true;
        } else {
            return false;
        }
    }

}
?>
