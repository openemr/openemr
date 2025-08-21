<?php
/*
 * FhirConditionEncounterDiagnosisService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Condition;

use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCategoryTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;

class FhirConditionProblemsHealthConcernService extends FhirServiceBase implements IPatientCompartmentResourceService {

    use FhirServiceBaseEmptyTrait;
    use MappedServiceTrait;
    use MappedServiceCategoryTrait;

    const CATEGORY_SYSTEM = 'http://terminology.hl7.org/CodeSystem/condition-category';
    const CATEGORY_PROBLEM_LIST = 'problem-list-item';
    const CATEGORY_HEALTH_CONCERN = 'health-concern';

    const CATEGORY_ASSESSMENT_CONDITION_SDOH = 'sdoh';
    const CATEGORY_ASSESSMENT_CONDITION_FUNCTIONAL_STATUS = 'functional-status';
    const CATEGORY_ASSESSMENT_CONDITION_DISABILITY_STATUS = 'disability-status';
    const CATEGORY_ASSESSMENT_CONDITION_COGNITIVE_STATUS = 'cognitive-status';

    // A personal preference for a type of medical intervention (treatment) request under certain conditions.
    const CATEGORY_ASSESSMENT_CONDITION_TREATMENT_INTERVENTION_STATUS = 'treatment-intervention-status';

    const CATEGORY_ASSESSMENT_CONDITION_CARE_EXPERIENCE_PREFERENCE = 'care-experience-preference';
    const USCDI_PROFILE = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-problems-health-concerns";

    public function supportsCategory(string $category)
    {
        return in_array($category, [
//            self::CATEGORY_PROBLEM_LIST,
            self::CATEGORY_HEALTH_CONCERN
            ,self::CATEGORY_ASSESSMENT_CONDITION_SDOH
            ,self::CATEGORY_ASSESSMENT_CONDITION_FUNCTIONAL_STATUS
            ,self::CATEGORY_ASSESSMENT_CONDITION_DISABILITY_STATUS
            ,self::CATEGORY_ASSESSMENT_CONDITION_COGNITIVE_STATUS
            ,self::CATEGORY_ASSESSMENT_CONDITION_TREATMENT_INTERVENTION_STATUS
            ,self::CATEGORY_ASSESSMENT_CONDITION_CARE_EXPERIENCE_PREFERENCE
        ]);
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['diagnosis']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('condition_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated_time']);
    }
}
