<?php

/*
 * FhirConditionCategoryDisplay.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Condition\Enum;

enum FhirConditionCategoryDisplay: string
{
    case PROBLEM_LIST_ITEM = 'Problem List Item';
    case ENCOUNTER_DIAGNOSIS = 'Encounter Diagnosis';
    case HEALTH_CONCERNS = 'Health Concern';
}
