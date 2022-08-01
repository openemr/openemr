<?php

/**
 * FhirCarePlanService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCarePlan;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanActivity;
use OpenEMR\Services\CarePlanService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirCarePlanService extends FhirServiceBase implements IResourceUSCIGProfileService, IPatientCompartmentResourceService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var CarePlanService
     */
    private $service;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-careplan';

    public function __construct()
    {
        parent::__construct();
        $this->service = new CarePlanService();
    }

    /**
     * Returns an array mapping FHIR CarePlan Resource search parameters to OpenEMR CarePlan search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'category' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['careplan_category']),
            // note even though we label this as a uuid, it is a SURROGATE UID because of the nature of CarePlan
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['uuid']),
        ];
    }

    /**
     * Parses an OpenEMR record, returning the equivalent FHIR Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCarePlan
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $carePlanResource = new FHIRCarePlan();

        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        $fhirMeta->setLastUpdated(gmdate('c'));
        $carePlanResource->setMeta($fhirMeta);

        $fhirId = new FHIRId();
        $fhirId->setValue($dataRecord['uuid']);
        $carePlanResource->setId($fhirId);

        if (isset($dataRecord['puuid'])) {
            $carePlanResource->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));
        } else {
            $carePlanResource->setSubject(UtilsService::createDataMissingExtension());
        }

        $codeableConcept = new FHIRCodeableConcept();
        $coding = new FHIRCoding();
        $coding->setCode("assess-plan");
        $coding->setSystem(FhirCodeSystemConstants::HL7_SYSTEM_CAREPLAN_CATEGORY);
        $codeableConcept->addCoding($coding);
        $carePlanResource->addCategory($codeableConcept);

        $carePlanResource->setIntent("plan");
        $carePlanResource->setStatus("active");

        // TODO: our care plan reason codes would go inside an activity's reasonCode property.
        //  Right now we don't generate activities, but this is what we would add here if we start including care plan activities.

        // ONC only requires a descriptive text.  Future FHIR implementors can grab these details and populate the
        // activity element if they so choose, for now we just return the combined description of the care plan.
        if (!empty($dataRecord['details'])) {
            $carePlanText = $this->getCarePlanTextFromDetails($dataRecord['details']);
            $carePlanResource->setDescription($carePlanText['text']);

            // since we pull the text from the description status is generated, if we had additional info we would
            // set status to 'additional'
            $narrative = new FHIRNarrative();
            $narrative->setStatus("generated");
            $narrative->setDiv('<div xmlns="http://www.w3.org/1999/xhtml">' . $carePlanText['xhtml'] . '</div>');
            $carePlanResource->setText($narrative);
        } else {
            $carePlanResource->setText(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['provider_uuid']) && !empty($dataRecord['provider_npi'])) {
            $carePlanResource->setAuthor(UtilsService::createRelativeReference("Practitioner", $dataRecord['provider_uuid']));
        }

        if ($encode) {
            return json_encode($carePlanResource);
        } else {
            return $carePlanResource;
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
        if (!($dataRecord instanceof FHIRCarePlan)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $provenanceService = new FhirProvenanceService();
        $provenance = $provenanceService->createProvenanceForDomainResource($dataRecord, $dataRecord->getAuthor());
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

    private function getCarePlanTextFromDetails($details)
    {
        $descriptions = [];
        foreach ($details as $detail) {
            // use description or fallback on codetext if needed
            $descriptions[] = $detail['description'] ?? $detail['codetext'] ?? "";
        }
        // make sure we clear any white space out that blows up FHIR validation
        $carePlanText = ['text' => trim(implode("\n", $descriptions)), "xhtml" => ""];
        if (!empty($descriptions)) {
            $carePlanText['xhtml'] = "<p>" . implode("</p><p>", $descriptions) . "</p>";
        }
        return $carePlanText;
    }
}
