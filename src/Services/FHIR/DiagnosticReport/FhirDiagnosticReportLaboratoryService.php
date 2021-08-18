<?php

/**
 * FhirDiagnosticReportLaboratoryService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\DiagnosticReport;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDiagnosticReport;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Indicates;
use OpenEMR\Services\FHIR\OpenEMR;
use OpenEMR\Services\FHIR\openEMRSearchParameters;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirDiagnosticReportLaboratoryService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    /**
     * @var ProcedureService
     */
    private $service;

    const LAB_CATEGORY = "LAB";

    /**
     * @see list_options order_types (Order Types)
     */
    const PROCEDURE_ORDER_TEST_TYPE = "laboratory_test";

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new ProcedureService();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['standard_code']),
            // we ignore category for now because it defaults to LAB, at some point in the future we may allow a different category
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['report_date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('report_uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    public function supportsCategory($category)
    {
        return $category === self::LAB_CATEGORY;
    }


    public function supportsCode($code)
    {
        // we'll let them search on any LOINC code, technically we could just search procedure_codes for a valid code
        // and return false if there is nothing there... but unless the queries get really inefficient, we can just
        // leave this here
        return true;
    }

    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $report = new FHIRDiagnosticReport();
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
        $report->setMeta($meta);


        $dataRecordReport = array_pop($dataRecord['reports']);


        $id = new FHIRId();
        $id->setValue($dataRecordReport['uuid']);
        $report->setId($id);

        if (!empty($dataRecordReport['date'])) {
            $date = gmdate('c', strtotime($dataRecordReport['date']));
            $report->setEffectiveDateTime(new FHIRDateTime($date));
            $report->setIssued(new FHIRInstant($date));
        } else {
            $report->setDate(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['euuid'])) {
            $report->setEncounter(UtilsService::createRelativeReference('Encounter', $dataRecord['euuid']));
        }

        $fhirOrganizationService = new FhirOrganizationService();

        if (!empty($dataRecord['lab']['uuid'])) {
            $orgReference = UtilsService::createRelativeReference("Organization", $dataRecord['lab']['uuid']);
            $report->addPerformer($orgReference);
        } else {
            $report->addPerformer($fhirOrganizationService->getPrimaryBusinessEntityReference());
        }

        if (!empty($dataRecordReport['results'])) {
            foreach ($dataRecordReport['results'] as $result) {
                $obsReference = UtilsService::createRelativeReference("Observation", $result['uuid']);
                if (!empty($result['text'])) {
                    $obsReference->setDisplay(xlt($result['text']));
                }
                $report->addResult($obsReference);
            }
        }
        if (!empty($dataRecord['patient']['uuid'])) {
            $report->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['patient']['uuid']));
        }
        $report->addCategory(UtilsService::createCodeableConcept([
            self::LAB_CATEGORY => ['code' => self::LAB_CATEGORY, 'description' => "Laboratory", 'system' => FhirCodeSystemConstants::DIAGNOSTIC_SERVICE_SECTION_ID]
        ]));

        if (!empty($dataRecord['encounter']['uuid'])) {
            $report->setEncounter(UtilsService::createRelativeReference('Encounter', $dataRecord['encounter']['uuid']));
        }

        if (!empty($dataRecordReport['status'])) {
            $report->setStatus($dataRecordReport['status']);
        } else {
            $report->setStatus('final');
        }

        // note we use standard_code instead of code as we require a LOINC code here and standard_code is for LOINC
        // codes in the system.  @see procedure_type table if you are confused by the difference between procedure_code
        // and standard_code
        if (!empty($dataRecord['standard_code'])) {
            $code = UtilsService::createCodeableConcept([$dataRecord['standard_code'] =>
                ['code' => $dataRecord['standard_code'], 'description' => $dataRecord['name'], 'system' => FhirCodeSystemConstants::LOINC]
            ]);
            $report->setCode($code);
        } else {
            $report->setCode(UtilsService::createNullFlavorUnknownCodeableConcept());
        }

        return $report;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // this service ignores category_code
        if (isset($openEMRSearchParameters['category'])) {
            unset($openEMRSearchParameters['category']);
        }
        // procedure service can return procedures that have no attached report to them.  Because of this situation
        // we have to make sure we exclude any procedures where the report_uuid is empty
        if (empty($openEMRSearchParameters['_id'])) {
            $openEMRSearchParameters['_id'] = new TokenSearchField('report_uuid', [new TokenSearchValue(false)]);
            $openEMRSearchParameters['_id']->setModifier(SearchModifier::MISSING);
        }

        if (isset($openEMRSearchParameters['standard_code']) && $openEMRSearchParameters['standard_code'] instanceof TokenSearchField) {
            foreach ($openEMRSearchParameters['standard_code']->getValues() as $value) {
                // TODO: @adunsulag do we need to handle unknowable codes across all FHIR code systems?
                if ($value->getCode() == UtilsService::UNKNOWNABLE_CODE_DATA_ABSENT) {
                    $openEMRSearchParameters['standard_code'] = new TokenSearchField('standard_code', new TokenSearchValue(true));
                    $openEMRSearchParameters['standard_code']->setModifier(SearchModifier::MISSING);
                    break;
                }
            }
        }
        $openEMRSearchParameters['procedure_type'] = new TokenSearchField('procedure_type', [new TokenSearchValue(self::PROCEDURE_ORDER_TEST_TYPE)]);
        return $this->service->search($openEMRSearchParameters);
    }

    /**
     * Creates the Provenance resource  for the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRDiagnosticReport)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
