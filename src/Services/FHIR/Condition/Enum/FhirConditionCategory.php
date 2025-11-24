<?php

/*
 * FhirConditionCategory.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Condition\Enum;

enum FhirConditionCategory: string
{
    case PROBLEM_LIST_ITEM = 'problem-list-item';
    case ENCOUNTER_DIAGNOSIS = 'encounter-diagnosis';
    case HEALTH_CONCERNS = 'health-concern';

    public function display(): FhirConditionCategoryDisplay
    {
        return match ($this) {
            self::PROBLEM_LIST_ITEM => FhirConditionCategoryDisplay::PROBLEM_LIST_ITEM,
            self::ENCOUNTER_DIAGNOSIS => FhirConditionCategoryDisplay::ENCOUNTER_DIAGNOSIS,
            self::HEALTH_CONCERNS => FhirConditionCategoryDisplay::HEALTH_CONCERNS,
        };
    }
}
