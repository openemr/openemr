<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

namespace OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary;

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteria;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaFactory;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaType;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\TimeUnit;

/**
 * Description of OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaTargetFactory
 *
 * @author aron
 */
class RuleCriteriaTargetFactory extends RuleCriteriaFactory
{
    const SQL_RULE_INTERVAL =
        "SELECT * FROM rule_target
     WHERE method = 'target_interval'
       AND id = ?";

    /**
     *
     * @param RuleCriteria $criteria
     */
    function modify($criteria, $ruleId)
    {
        // get interval
        $result = sqlQuery(self::SQL_RULE_INTERVAL, array($ruleId));
        $criteria->interval = $result['interval'] ?? 1;
        $criteria->intervalType = isset($result['value']) ? TimeUnit::from($result['value']) : TimeUnit::from(TimeUnit::Month);
    }

    /**
     *
     * @param string $ruleId
     * @param RuleCriteriaType $criteriaType
     */
    function buildNewInstance($ruleId, $criteriaType)
    {
        $criteria = parent::buildNewInstance($ruleId, $criteriaType);
        $criteria->interval = 1;
        $criteria->intervalType = TimeUnit::from(TimeUnit::Month);
        return $criteria;
    }
}
