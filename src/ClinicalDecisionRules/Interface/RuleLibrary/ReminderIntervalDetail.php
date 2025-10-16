<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

namespace OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary;

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalRange;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalType;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\TimeUnit;

/**
 * Description of OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalDetail
 *
 * @author aron
 */
class ReminderIntervalDetail
{
    /**
     *
     * @param ReminderIntervalType $intervalType
     * @param ReminderIntervalRange $intervalRange
     * @param integer $amount
     * @param TimeUnit $timeUnit
     */
    function __construct(public $intervalType, public $intervalRange, public $amount, public $timeUnit)
    {
    }

    function display()
    {
        $display = xl($this->intervalRange->lbl) . ": "
            . xl($this->amount) . " " . xl($this->timeUnit->lbl);
        return $display;
    }
}
