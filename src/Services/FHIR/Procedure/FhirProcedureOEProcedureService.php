<?php

/**
 * FhirProcedureOEProcedureService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Procedure;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProcedure;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRProcedure\FHIRProcedurePerformer;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProcedureService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirProcedureOEProcedureService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;


    /**
     * @see list_options order_types (Order Types)
     * TODO: @adunsulag is there a better place to put this?  Its duplicated in FhirDiagnosticReportLaboratoryService
     */
    const PROCEDURE_ORDER_TEST_TYPE = "laboratory_test";

    /**
     * @var ProcedureService
     */
    private $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new ProcedureService();
    }

    /**
     * Returns an array mapping FHIR Procedure Resource search parameters to OpenEMR Procedure search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['report_date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('report_uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $openEMRSearchParameters = is_array($openEMRSearchParameters) ? $openEMRSearchParameters : [];
        $openEMRSearchParameters['procedure_type'] = new StringSearchField(
            'procedure_type',
            [self::PROCEDURE_ORDER_TEST_TYPE],
            SearchModifier::NOT_EQUALS_EXACT
        );

        // we only want records where a report is created as we go off the individual report_uuid
        if (!isset($openEMRSearchParameters['report_uuid'])) {
            // make sure we only return results with a matching report.
            $openEMRSearchParameters['report_uuid'] = new TokenSearchField('report_uuid', [new TokenSearchValue(false)], false);
            $openEMRSearchParameters['report_uuid']->setModifier(SearchModifier::MISSING);
        }
        return $this->service->search($openEMRSearchParameters);
    }


    /**
     * Parses an OpenEMR procedure record, returning the equivalent FHIR Procedure Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRProcedure
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $procedureResource = new FHIRProcedure();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
        $procedureResource->setMeta($meta);

        $report = array_pop($dataRecord['reports']);

        $id = new FHIRId();
        $id->setValue($report['uuid']);
        $procedureResource->setId($id);

        if (!empty($dataRecord['patient']['uuid'])) {
            $procedureResource->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['patient']['uuid']));
        } else {
            $procedureResource->setSubject(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['encounter']['uuid'])) {
            $procedureResource->setEncounter(UtilsService::createRelativeReference('Encounter', $dataRecord['encounter']['uuid']));
        }

        if (!empty($dataRecord['provider']['uuid'])) {
            $performer = new FHIRProcedurePerformer();
            $performer->setActor(UtilsService::createRelativeReference('Practitioner', $dataRecord['provider']['uuid']));
            $procedureResource->addPerformer($performer);
        }

        $codesService = new CodeTypesService();
        if (!empty($dataRecord['diagnosis'])) {
            $codes = explode(";", $dataRecord['diagnosis']);
            foreach ($codes as $code) {
                $codeParts = $codesService->parseCode($code);
                $codeParts['description'] = $codesService->lookup_code_description($code) ?? '';
                if (empty($codeParts['description'])) {
                    $codeParts['description'] = null;
                }
                $codeParts['system'] = $codesService->getSystemForCodeType($codeParts['code_type']);
                // if we don't have a system we need to just not return these values
                if (!empty($codeParts['system'])) {
                    $procedureResource->addReasonCode(UtilsService::createCodeableConcept([$codeParts['code'] => $codeParts]));
                }
            }
        }

        // code can be whatever the user provides but should usually be CPT4 or SNOMED.  If we can't detect the system
        // then we HAVE to go with standard code or report back nothing...
        $codeParts = $codesService->parseCode($dataRecord['code']);
        $fhirCodeableConcept = new FHIRCodeableConcept();
        if (!empty($codeParts['code'])) {
            $code = $codeParts['code'];
            $description = $codesService->lookup_code_description($code);
            $description = !empty($description) ? $description : null; // we can get an "" string back from lookup
            $system = $codesService->getSystemForCodeType($codeParts['code_type']) ?? null;
            if (!empty($system)) {
                $fhirCodeableConcept = UtilsService::createCodeableConcept(
                    [
                        $code => ['code' => $code, 'system' => $system, 'description' => $description]
                    ]
                );
            }
        }

        if (!empty($dataRecord['standard_code'])) {
            $code = $dataRecord['standard_code'];
            $system = FhirCodeSystemConstants::LOINC;
            $fullStandardCode = $codesService->getCodeWithType($dataRecord['standard_code'], CodeTypesService::CODE_TYPE_LOINC);
            $description = $codesService->lookup_code_description($fullStandardCode);
            $description = !empty($description) ? $description : $dataRecord['name']; // we can get an "" string back from lookup
            $fhirCodeableConcept->addCoding(UtilsService::createCoding($code, $description, $system));
        }

        if (empty($fhirCodeableConcept->getCoding())) {
            if (!empty($dataRecord['name'])) {
                $fhirCodeableConcept->setText($dataRecord['name']);
            } else {
                $fhirCodeableConcept = UtilsService::createDataAbsentUnknownCodeableConcept();
            }
        }
        $procedureResource->setCode($fhirCodeableConcept);

        $status = FhirProcedureService::FHIR_PROCEDURE_STATUS_COMPLETED;
        if (!empty($report['results'])) {
            foreach ($report['results'] as $result) {
                if ($result['status'] != 'final') {
                    $status = FhirProcedureService::FHIR_PROCEDURE_STATUS_IN_PROGRESS;
                    break;
                }
            }
        }
        $procedureResource->setStatus($status);


        if (!empty($report['date'])) {
            $procedureResource->setPerformedDateTime(gmdate('c', strtotime($report['date'])));
        } else {
            $procedureResource->setPerformedDateTime(UtilsService::createDataMissingExtension());
        }

        if (!empty($report['notes'])) {
            $annotation = new FHIRAnnotation();
            $annotation->setText($report['notes']);
        }

        if ($encode) {
            return json_encode($procedureResource);
        } else {
            return $procedureResource;
        }
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
        if (!($dataRecord instanceof FHIRProcedure)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $reference = null;
        if (!empty($dataRecord->getPerformer()) && count($dataRecord->getPerformer()) == 1) {
            $reference = $dataRecord->getPerformer()[0]->getActor();
        }
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $reference);

        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
