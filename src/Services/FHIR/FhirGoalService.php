<?php

/**
 * FhirGoalService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGoal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDate;
use OpenEMR\FHIR\R4\FHIRElement\FHIRGoalLifecycleStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRGoal\FHIRGoalTarget;
use OpenEMR\Services\CarePlanService;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirGoalService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var CarePlanService
     */
    private $service;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-goal';

    public function __construct()
    {
        parent::__construct();
        // goals are stored inside the care plan forms
        $this->service = new CarePlanService(CarePlanService::TYPE_GOAL);
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            // note even though we label this as a uuid, it is a SURROGATE UID because of the nature of how goals are stored
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['uuid']),
        ];
    }

    /**
     * Parses an OpenEMR careTeam record, returning the equivalent FHIR CareTeam Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCareTeam
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $goal = new FHIRGoal();

        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        $fhirMeta->setLastUpdated(gmdate('c'));
        $goal->setMeta($fhirMeta);

        $fhirId = new FHIRId();
        $fhirId->setValue($dataRecord['uuid']);
        $goal->setId($fhirId);

        if (isset($dataRecord['puuid'])) {
            $goal->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));
        } else {
            $goal->setSubject(UtilsService::createDataMissingExtension());
        }

        $lifecycleStatus = new FHIRGoalLifecycleStatus();
        $lifecycleStatus->setValue("active");
        $goal->setLifecycleStatus($lifecycleStatus);

        if (!empty($dataRecord['provider_uuid']) && !empty($dataRecord['provider_npi'])) {
            $goal->setExpressedBy(UtilsService::createRelativeReference("Practitioner", $dataRecord['provider_uuid']));
        }


        // ONC only requires a descriptive text.  Future FHIR implementors can grab these details and populate the
        // activity element if they so choose, for now we just return the combined description of the care plan.
        if (!empty($dataRecord['details'])) {
            $text = $this->getGoalTextFromDetails($dataRecord['details']);
            $codeableConcept = new FHIRCodeableConcept();
            $codeableConcept->setText($text['text']);
            $goal->setDescription($codeableConcept);

            $codeTypeService = new CodeTypesService();
            foreach ($dataRecord['details'] as $detail) {
                $fhirGoalTarget = new FHIRGoalTarget();
                if (!empty($detail['date'])) {
                    $fhirDate = new FHIRDate();
                    $parsedDateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $detail['date']);
                    $fhirDate->setValue($parsedDateTime->format("Y-m-d"));
                    $fhirGoalTarget->setDueDate($fhirDate);
                } else {
                    $fhirGoalTarget->setDueDate(UtilsService::createDataMissingExtension());
                }
                $detailDescription = trim($detail['description'] ?? "");
                if (!empty($detailDescription)) {
                    // if description is populated we also have to populate the measure with the correct code
                    $fhirGoalTarget->setDetailString($detailDescription);

                    if (!empty($detail['code'])) {
                        $codeText = $codeTypeService->lookup_code_description($detail['code']);
                        $codeSystem = $codeTypeService->getSystemForCode($detail['code']);

                        $targetCodeableConcept = new FHIRCodeableConcept();
                        $coding = new FhirCoding();
                        $coding->setCode($detail['code']);
                        if (empty($codeText)) {
                            $coding->setDisplay(UtilsService::createDataMissingExtension());
                        } else {
                            $coding->setDisplay(xlt($codeText));
                        }

                        $coding->setSystem($codeSystem); // these should always be LOINC but we want this generic
                        $targetCodeableConcept->addCoding($coding);
                        $fhirGoalTarget->setMeasure($targetCodeableConcept);
                    } else {
                        $fhirGoalTarget->setMeasure(UtilsService::createDataMissingExtension());
                    }
                }
                $goal->addTarget($fhirGoalTarget);
            }
        }

        if ($encode) {
            return json_encode($goal);
        } else {
            return $goal;
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        return $this->service->search($openEMRSearchParameters, true, $puuidBind);
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRGoal)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $provenanceService = new FhirProvenanceService();
        $provenance = $provenanceService->createProvenanceForDomainResource($dataRecord, $dataRecord->getExpressedBy());
        return $provenance;
    }

    public function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    private function getGoalTextFromDetails($details)
    {
        $descriptions = [];
        foreach ($details as $detail) {
            // use description or fallback on codetext if needed
            $descriptions[] = $detail['description'] ?? $detail['codetext'] ?? "";
        }
        $carePlanText = ['text' => trim(implode("\n", $descriptions)), "xhtml" => ""];
        if (!empty($descriptions)) {
            $carePlanText['xhtml'] = "<p>" . implode("</p><p>", $descriptions) . "</p>";
        }
        return $carePlanText;
    }
}
