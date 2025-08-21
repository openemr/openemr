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

use OpenEMR\Services\ConditionService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCategoryTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;

class FhirConditionEncounterDiagnosisService extends FhirServiceBase implements IPatientCompartmentResourceService {

    use FhirServiceBaseEmptyTrait;
    use MappedServiceTrait;
    use MappedServiceCategoryTrait;

    public function setConditionService(ConditionService $conditionService) {
        $this->conditionService = $conditionService;
    }

    public function getConditionService(): ConditionService
    {
        return $this->conditionService;
    }

    const CATEGORY_SYSTEM = 'http://terminology.hl7.org/CodeSystem/condition-category';
    const CATEGORY_ENCOUNTER_DIAGNOSIS = 'encounter-diagnosis';

    public function supportsCategory(string $category)
    {
        return $category === self::CATEGORY_ENCOUNTER_DIAGNOSIS;
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        // TODO: Implement parseOpenEMRRecord() method.
    }
}
