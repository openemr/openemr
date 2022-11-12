<?php

/**
 * FhirProcedureSurgeryService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Procedure;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProcedure;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirProcedureService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\SurgeryService;
use OpenEMR\Validators\ProcessingResult;

class FhirProcedureSurgeryService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    /**
     * @var SurgeryService
     */
    private $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new SurgeryService();
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
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['begdate']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
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
        $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $procedureResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $procedureResource->setId($id);
        if (!empty($dataRecord['puuid'])) {
            $procedureResource->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } else {
            $procedureResource->setSubject(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['euuid'])) {
            $encounter = new FHIRReference();
            $encounter->setReference('Encounter/' . $dataRecord['euuid']);
            $procedureResource->setEncounter(UtilsService::createRelativeReference('Encounter', $dataRecord['euuid']));
        }

        if ($dataRecord['status'] == "active") {
            $procedureResource->setStatus(FhirProcedureService::FHIR_PROCEDURE_STATUS_COMPLETED);
        } elseif ($dataRecord['status'] == "inactive") {
            $procedureResource->setStatus(FhirProcedureService::FHIR_PROCEDURE_STATUS_IN_PROGRESS);
        } else {
            $procedureResource->setStatus(FhirProcedureService::FHIR_PROCEDURE_STATUS_UNKNOWN);
        }

        if (!empty($dataRecord['diagnosis'])) {
            $codesService = new CodeTypesService();
            $codes = explode(";", $dataRecord['diagnosis']);
            $diagnosisCode = new FHIRCodeableConcept();
            foreach ($codes as $code) {
                $description = $codesService->lookup_code_description($code);
                $description = !empty($description) ? $description : null; // we can get an "" string back from lookup
                $system = $codesService->getSystemForCode($code);
                $diagnosisCode->addCoding(UtilsService::createCoding($code, $description, $system));
            }
            $procedureResource->setCode($diagnosisCode);
        }

        if (!empty($dataRecord['begdate'])) {
            $procedureResource->setPerformedDateTime(UtilsService::getLocalDateAsUTC($dataRecord['begdate']));
        }

        if (!empty($dataRecord['comments'])) {
            $procedureResource->addNote(['text' => $dataRecord['comments']]);
        }

        if (!empty($dataRecord['recorder_npi']) && !empty($dataRecord['recorder_uuid'])) {
            $procedureResource->setRecorder(UtilsService::createRelativeReference('Practitioner', $dataRecord['recorder_uuid']));
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
        $user = $dataRecord->getRecorder() ?? null;
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $user);

        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
