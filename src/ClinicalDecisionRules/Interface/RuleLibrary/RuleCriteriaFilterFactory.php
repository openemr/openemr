<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

namespace OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary;

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaFactory;

/**
 * Description of RuleCriteraFilterFactory
 *
 * @author aron
 */
class RuleCriteriaFilterFactory extends RuleCriteriaFactory
{
    function modify($criteria, $ruleId)
    {
        // noop
    }
}
