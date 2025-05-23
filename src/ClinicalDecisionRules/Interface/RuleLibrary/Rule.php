<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
namespace OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary;

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervals;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleActions;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleFilters;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleType;
use RuleTargetActionGroups;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleTargets;

/**
 * This is the primary domain object representing a rule in the rules engine.
 * Rules are composed of:
 * - one or more rule types (see OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleType enum)
 * - a OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervals object
 * - a OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleFilters object
 * - a OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleTargets object
 * - a OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleActions object
 *
 * Rules are typically assembled by the OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleManager.
 * @author aron
 */
class Rule
{
    public $ruleTypes;
    public $id;
    public string $title;

    /**
     * US Regulation 170.315(b)(11)(iv)(A)(1)
     * @var string Bibliographic citation of the intervention (clinical research or
    guideline) that the rule is based on
     */
    public string $bibliographic_citation;

    /**
     * US Regulation 170.315(b)(11)(iv)(A)(2)
     * @var string Developer of the intervention (translation from clinical research or guideline)
     */
    public string $developer;

    /**
     * US Regulation 170.315(b)(11)(iv)(A)(3)
     * @var string Funding source of the technical implementation for the intervention(s) development
     */
    public string $funding_source;

    /**
     * US Regulation 170.315(b)(11)(iv)(A)(4)
     * @var string Release and, if applicable, revision dates of the intervention or reference source
     */
    public string $release;

    /**
     * @var string web reference for the rule (e.g. URL)
     */
    public string $web_reference;

    /**
     * @var string the linked referential CDS
     */
    public string $linked_referential_cds;

    /**
     * @var string version identifier of the rule
     */
    public string $version;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(5) - Use of race as expressed in the standards in § 170.213
     * @var string The rule use of a patient's race
     */
    public string $patient_race_usage;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(6) - Use of ethnicity as expressed in the standards in § 170.213
     * @var string The rule use of a patient's ethnicity
     */
    public string $patient_ethnicity_usage;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(7) - Use of language as expressed in the standards in § 170.213
     * @var string The rule use of a patient's language
     */
    public string $patient_language_usage;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(8) - Use of sexual orientation as expressed in the standards in §
     * @var string The rule use of a patient's sexual orientation
     */
    public string $patient_sexual_orientation_usage;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(9) - Use of gender identity as expressed in the standards in §
     * @var string The rule use of a patient's gender identity
     */
    public string $patient_gender_identity_usage;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(10) - Use of sex as expressed in the standards in § 170.213
     * @var string The rule use of a patient's sex
     */
    public string $patient_sex_usage;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(11) - Use of date of birth as expressed in the standards in § 170.213
     * @var string The rule use of a patient's date of birth
     */
    public string $patient_dob_usage;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(12) - Use of social determinants of health data
     * @var string The rule use of a patient's Social Determinant's of Health
     */
    public string $patient_sodh_usage;

    /**
     * US Regulation § 170.315(b)(11)(iv)(A)(13) - Use of health status assessments as expressed in the standards in § 170.213
     * @var string The rule use of a patient's health status assessments
     */
    public string $patient_health_status_usage;

    /**
     * @var ReminderIntervals
     */
    var $reminderIntervals;

    /**
     * @var RuleFilters
     */
    var $filters;

    /**
     * @var RuleTargetActionGroups
     */
    var $groups;

    /**
     * User provided feedback on an applied rule instance
     * @return void
     */
    public ?string $feedback;

    function __construct($id = '', $title = '', $ruleTypes = array())
    {
        $this->id = $id;
        $this->title = $title;
        $this->ruleTypes = $ruleTypes;
        $this->bibliographic_citation = '';
        $this->developer = '';
        $this->funding_source = '';
        $this->release = '';
        $this->web_reference = '';
        $this->linked_referential_cds = '';
        $this->version = '';
        $this->patient_dob_usage = '';
        $this->patient_ethnicity_usage = '';
        $this->patient_health_status_usage = '';
        $this->patient_gender_identity_usage = '';
        $this->patient_language_usage = '';
        $this->patient_race_usage = '';
        $this->patient_sex_usage = '';
        $this->patient_sexual_orientation_usage = '';
        $this->patient_sodh_usage = '';
        $this->feedback = '';
    }

    public function updateEmptySourceAttributesWithDefaultMessage(string $message)
    {
        // certification requirement, show a message on each field if the field is empty that the provider did not provide any information
        $fields = [
            'bibliographic_citation',
            'developer',
            'funding_source',
            'release',
            'web_reference',
            'linked_referential_cds',
            'patient_race_usage',
            'patient_ethnicity_usage',
            'patient_language_usage',
            'patient_sexual_orientation_usage',
            'patient_gender_identity_usage',
            'patient_sex_usage',
            'patient_dob_usage',
            'patient_sodh_usage',
            'patient_health_status_usage'
        ];
// Check each field and set default if empty
        foreach ($fields as $field) {
            if (empty($this->$field)) {
                $this->$field = $message ?? ''; // if its null we need to set it to empty string
            }
        }
    }

    /**
     * @return string|null
     */
    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    /**
     * @param string|null $feedback
     */
    public function setFeedback(?string $feedback): void
    {
        $this->feedback = $feedback;
    }

    function getTitle()
    {
        return $this->title;
    }

    function setBibliographicCitation($s)
    {
        $this->bibliographic_citation = $s;
    }

    function setDeveloper($s)
    {
        $this->developer = $s;
    }

    function setFunding($s)
    {
        $this->funding_source = $s;
    }

    function setRelease($s)
    {
        $this->release = $s;
    }

    function setWeb_reference($s)
    {
        $this->web_reference = $s;
    }

    function setLinkedReferentialCds($s)
    {
        $this->linked_referential_cds = $s;
    }

    /**
     * @param RuleType $ruleType
     */
    function addRuleType($ruleType)
    {
        if (!$this->hasRuleType($ruleType)) {
            array_push($this->ruleTypes, $ruleType->code);
        }
    }

    /**
     *
     * @param RuleType $ruleType
     * @return boolean
     */
    function hasRuleType($ruleType)
    {
        foreach ($this->ruleTypes as $type) {
            if ($type == $ruleType->code) {
                return true;
            }
        }

        return false;
    }

    function isActiveAlert()
    {
        return $this->hasRuleType(RuleType::from(RuleType::ActiveAlert));
    }

    function isPassiveAlert()
    {
        return $this->hasRuleType(RuleType::from(RuleType::PassiveAlert));
    }

    function isCqm()
    {
        return $this->hasRuleType(RuleType::from(RuleType::CQM));
    }

    function isAmc()
    {
        return $this->hasRuleType(RuleType::from(RuleType::AMC));
    }

    function isReminder()
    {
        return $this->hasRuleType(RuleType::from(RuleType::PatientReminder));
    }

    /**
     * @param ReminderIntervals $reminderIntervals
     */
    function setReminderIntervals($reminderIntervals)
    {
        $this->reminderIntervals = $reminderIntervals;
    }

    /**
     *
     * @param RuleFilters $ruleFilters
     */
    function setRuleFilters($ruleFilters)
    {
        $this->filters = $ruleFilters;
    }

    function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     *
     * @param RuleTargets $ruleTargets
     */
    function setRuleTargets($ruleTargets)
    {
        $this->targets = $ruleTargets;
    }

    /**
     * @param RuleActions $actions
     */
    function setRuleActions($actions)
    {
        $this->actions = $actions;
    }

    function isEditable()
    {
        return true;
    }

    function getRuleTypeLabels()
    {
        $labels = array();
        foreach ($this->ruleTypes as $ruleType) {
            array_push($labels, RuleType::from($ruleType)->lbl);
        }

        return $labels;
    }

    public function getSummaryDataToPersist(): array
    {
        return [
            'id' => $this->id,
            'active_alert_flag' => in_array(RuleType::ActiveAlert, $this->ruleTypes) ? 1 : 0,
            'passive_alert_flag' => in_array(RuleType::PassiveAlert, $this->ruleTypes) ? 1 : 0,
            'cqm_flag' => in_array(RuleType::CQM, $this->ruleTypes) ? 1 : 0,
            'amc_flag' => in_array(RuleType::AMC, $this->ruleTypes) ? 1 : 0,
            'patient_reminder_flag' => in_array(RuleType::PatientReminder, $this->ruleTypes) ? 1 : 0,
            'developer' => $this->developer,
            'funding_source' => $this->funding_source,
            'release_version' => $this->release,
            'web_reference' => $this->web_reference,
            'bibliographic_citation' => $this->bibliographic_citation,
            'linked_referential_cds' => $this->linked_referential_cds,
            'patient_dob_usage' => $this->patient_dob_usage,
            'patient_ethnicity_usage' => $this->patient_ethnicity_usage,
            'patient_health_status_usage' => $this->patient_health_status_usage,
            'patient_gender_identity_usage' => $this->patient_gender_identity_usage,
            'patient_language_usage' => $this->patient_language_usage,
            'patient_race_usage' => $this->patient_race_usage,
            'patient_sex_usage' => $this->patient_sex_usage,
            'patient_sexual_orientation_usage' => $this->patient_sexual_orientation_usage,
            'patient_sodh_usage' => $this->patient_sodh_usage
        ];
    }

    public function populateSummaryDataWithPersistedValues(array $ruleResult)
    {

        $this->setBibliographicCitation($ruleResult['bibliographic_citation']);
        $this->setDeveloper($ruleResult['developer']);
        $this->setFunding($ruleResult['funding_source']);
        $this->setRelease($ruleResult['release_version']);
        $this->setWeb_reference($ruleResult['web_reference']);
        $this->setLinkedReferentialCds($ruleResult['linked_referential_cds']);
        $this->patient_dob_usage = $ruleResult['patient_dob_usage'] ?? '';
        $this->patient_ethnicity_usage = $ruleResult['patient_ethnicity_usage'] ?? '';
        $this->patient_health_status_usage = $ruleResult['patient_health_status_usage'] ?? '';
        $this->patient_gender_identity_usage = $ruleResult['patient_gender_identity_usage'] ?? '';
        $this->patient_language_usage = $ruleResult['patient_language_usage'] ?? '';
        $this->patient_race_usage = $ruleResult['patient_race_usage'] ?? '';
        $this->patient_sex_usage = $ruleResult['patient_sex_usage'] ?? '';
        $this->patient_sexual_orientation_usage = $ruleResult['patient_sexual_orientation_usage'] ?? '';
        $this->patient_sodh_usage = $ruleResult['patient_sodh_usage'] ?? '';
    }
}
