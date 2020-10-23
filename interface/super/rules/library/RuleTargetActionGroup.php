<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of RuleGroup
 *
 * @author ken
 */
class RuleTargetActionGroup
{

    var $groupId;

    /**
     * @var RuleActions
     */
    var $ruleTargets;

    /**
     * @var RuleTargets
     */
    var $ruleActions;

    public function __construct($groupId = null)
    {
        $this->groupId = $groupId;
        $this->ruleActions = new RuleActions();
        $this->ruleTargets = new RuleTargets();
    }

    public function setRuleTargets(RuleTargets $ruleTargets)
    {
        $this->ruleTargets = $ruleTargets;
    }

    public function setRuleActions(RuleActions $ruleActions)
    {
        $this->ruleActions = $ruleActions;
    }

    function updateFromRequest()
    {
    }
}
