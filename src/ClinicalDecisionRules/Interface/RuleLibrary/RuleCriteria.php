<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

namespace OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary;

use OpenEMR\ClinicalDecisionRules\Interface\Common;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaDbView;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteriaType;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\TimeUnit;
use OpenEMR\ClinicalDecisionRules\Interface\RuleTemplateExtension;

/**
 * Description of OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteria
 *
 * @author aron
 */
abstract class RuleCriteria
{
    /**
     * if true, then criteria is optional; required otherwise
     *
     * @var boolean
     */
    public $optional;

    /**
     * if true, then criteira is an inclusion; exclusion otherwise
     *
     * @var boolean
     */
    public $inclusion = true;

    /**
     * @var string
     */
    public $interval;

    /**
     * @var TimeUnit
     */
    public $intervalType;

    /**
     * uniquely identifies this criteria
     *
     * @var string
     */
    public $guid;

    /**
     *
     * @var RuleCriteriaType
     */
    public $criteriaType;

    public $groupId;

    function getCharacteristics()
    {
        // HR: reverse this to match logic behavior
        $characteristics = $this->optional ? xl("Required") : xl("Optional");
        $characteristics .= " ";
        $characteristics .= $this->inclusion ? xl("Inclusion") : xl("Exclusion");

        return $characteristics;
    }

    abstract function getRequirements();

    abstract function getTitle();

    abstract function getView();

    function getInterval()
    {
        if (is_null($this->interval) || is_null($this->intervalType)) {
            return null;
        }

        return xl($this->interval) . " x " . " "
            . xl($this->intervalType->lbl);
    }

    protected function getLabel($value, $list_id)
    {
        return RuleTemplateExtension::getLabel($value, $list_id);
    }

    protected function getLayoutLabel($value, $form_id)
    {
        return RuleTemplateExtension::getLayoutLabel($value, $form_id);
    }

    protected function decodeComparator($comparator)
    {
        return match ($comparator) {
            "eq" => "",
            "ne" => "!=",
            "gt" => ">",
            "lt" => "<",
            "ge" => ">=",
            "le" => "<=",
            default => "",
        };
    }

    /**
     * @return RuleCriteriaDbView
     */
    function getDbView()
    {
        $dbView = new RuleCriteriaDbView();
        $dbView->inclusion = $this->inclusion;
        $dbView->optional = $this->optional;
        $dbView->interval = $this->interval;
        $dbView->intervalType = $this->intervalType->code ?? null;

        return $dbView;
    }

    function updateFromRequest()
    {
        $inclusion = "yes" == Common::post("fld_inclusion");
        $optional = "yes" == Common::post("fld_optional");
        $groupId = Common::post("group_id");
        $interval = Common::post("fld_target_interval");
        $intervalType = TimeUnit::from(Common::post("fld_target_interval_type"));

        $this->groupId = $groupId;
        $this->optional = $optional;
        $this->inclusion = $inclusion;
        $this->interval = $interval;
        $this->intervalType = $intervalType;
    }
}
