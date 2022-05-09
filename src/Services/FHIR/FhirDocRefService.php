<?php

/**
 * FhirDocRefService handles the creation / retrieve of Clinical Summary of Care (CCD) documents for a patient.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\ResourceServiceSearchTrait;
use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

// TODO: @adunsulag look at putting this into its own operations folder
class FhirDocRefService
{
    use ResourceServiceSearchTrait;
    use PatientSearchTrait;

    private $resourceSearchParameters;

    const LOINC_CCD_CLINICAL_SUMMARY_OF_CARE = "34133-9";

    public function __construct()
    {
        $this->resourceSearchParameters = $this->loadSearchParameters();
        $searchFieldFactory = new FHIRSearchFieldFactory($this->resourceSearchParameters);
        $this->setSearchFieldFactory($searchFieldFactory);
    }

    /**
     * Returns an array mapping FHIR Coverage Resource search parameters to OpenEMR Condition search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'start' => new FhirSearchParameterDefinition('start', SearchFieldType::DATETIME, ['start_datetime']),
            'end' => new FhirSearchParameterDefinition('end', SearchFieldType::DATETIME, ['end_datetime']),
            'type' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['type']),
        ];
    }

    public function getAll($searchParams, $puuidBind): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        try {
            $oeSearchParameters = $this->createOpenEMRSearchParameters($searchParams, $puuidBind);
            $type = $oeSearchParameters['type'] ?? $this->createDefaultType();
            // if type != 'CCD LOINC' then return no data
            if ($this->isValidType($type)) {
                $oeSearchParameters['type'] = $type;
            } else {
                throw new SearchFieldException("type", "Unsupported code for parameter");
            }

            // if no start & end, return current CCD
            if ($this->shouldReturnMostRecentCCD($oeSearchParameters)) {
                $documentReference = $this->getMostRecentCCDReference($oeSearchParameters, $fhirSearchResult);
            } else {
                // else
                // generate CCD using start & end
                $documentReference = $this->generateCCD($oeSearchParameters);
            }
            $fhirSearchResult->addData($documentReference);
        } catch (SearchFieldException $exception) {
            $systemLogger = new SystemLogger();
            $systemLogger->error(get_class($this) . "->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField(), 'trace' => $exception->getTraceAsString()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    private function createDefaultType()
    {
        return new TokenSearchField('type', [self::LOINC_CCD_CLINICAL_SUMMARY_OF_CARE]);
    }

    private function isValidType(TokenSearchField $type)
    {

        if ($type->hasCodeValue(self::LOINC_CCD_CLINICAL_SUMMARY_OF_CARE)) {
            return true;
        }
        return false;
    }

    private function shouldReturnMostRecentCCD($oeSearchparameters): bool
    {

        return empty($oeSearchparameters['start']) || empty($oeSearchparameters['end']);
    }

    private function getMostRecentCCDReference($oeSearchParameters): FHIRDocumentReference
    {
        // grab the CCD reference from the ccd table and return it
        return new FHIRDocumentReference();
    }

    private function generateCCD($oeSearchParameters): FHIRDocumentReference
    {
        return new FHIRDocumentReference();
    }
}
