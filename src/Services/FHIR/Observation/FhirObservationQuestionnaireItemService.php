<?php

/*
 * FhirObservationObservationFormService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\Observation\Trait\FhirObservationTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;

class FhirObservationQuestionnaireItemService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    use FhirObservationTrait;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-screening-assessment';
    const SUPPORTED_CATEGORIES = ['survey', 'exam', 'social-history', 'vital-signs', 'imaging', 'laboratory', 'procedure', 'survey', 'therapy'];

    public function supportsCategory($category)
    {
        return in_array($category, self::SUPPORTED_CATEGORIES);
    }

    public function supportsCode(string $code): bool
    {
        // we support all codes, since pretty much any code can be in a questionnaire item
        return true;
    }


    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     * @return array<string, FhirSearchParameterDefinition>
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['result_code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['report_date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('result_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['report_date']);
    }
}
