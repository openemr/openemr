<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationPerformer;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\ImmunizationService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDate;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationStatusCodes;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationEducation;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Immunization Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirImmunizationService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirImmunizationService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var ImmunizationService
     */
    private $immunizationService;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-immunization';

    public function __construct()
    {
        parent::__construct();
        $this->immunizationService = new ImmunizationService();
    }

    /**
     * Returns an array mapping FHIR Immunization Resource search parameters to OpenEMR Immunization search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    /**
     * Parses an OpenEMR immunization record, returning the equivalent FHIR Immunization Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRImmunization
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $immunizationResource = new FHIRImmunization();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $immunizationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $immunizationResource->setId($id);

        $status = new FHIRImmunizationStatusCodes();
        if ($dataRecord['added_erroneously'] != "0") {
            $status->setValue("entered-in-error");
        } elseif ($dataRecord['completion_status'] == "Completed") {
            $status->setValue("completed");
        } else {
            $status->setValue("not-done");

            // TODO: @adunsulag we need to update these codes here as we need to map better from NIP002
            // to these status codes here: https://terminology.hl7.org/3.1.0/CodeSystem-v3-ActReason.html
            //
            if (!empty($dataRecord['refusal_reason_cdc_nip_code'])) {
                $code = "PATOBJ";
                $display = "patient objection";

                // we are leaving these here just to document these values as PATOBJ corresponds to both patient or
                // guardian objection.  Other doesn't have a correspondance, and patient decision is already handled.
                switch ($dataRecord['refusal_reason_cdc_nip_code']) {
                    case '00': // Parental exemption
                        break;
                    case '01': // Religious exemption
                        $code = "RELIG";
                        $display =  "religious objection";
                        break;
                    case '02': // other
                        break;
                    case '03': // patient decision
                    default:
                        break;
                }
            }
            $statusReason = new FHIRCodeableConcept();
            $statusReasonCoding = new FHIRCoding();
            $statusReasonCoding->setSystem(FhirCodeSystemConstants::IMMUNIZATION_OBJECTION_REASON);
            $statusReasonCoding->setCode($code);
            $statusReasonCoding->setDisplay($display);
            $statusReason->addCoding($statusReasonCoding);
            $immunizationResource->setStatusReason($statusReason);
        }
        $immunizationResource->setStatus($status);
        $immunizationResource->setPrimarySource($dataRecord['primarySource']);

        if (!empty($dataRecord['cvx_code'])) {
            $vaccineCode = new FHIRCodeableConcept();
            $vaccineCode->addCoding(array(
                'system' => "http://hl7.org/fhir/sid/cvx",
                'code' =>  $dataRecord['cvx_code'],
                'display' => $dataRecord['cvx_code_text']
            ));
            $immunizationResource->setVaccineCode($vaccineCode);
        }

        if (!empty($dataRecord['puuid'])) {
            $patient = new FHIRReference(['reference' => 'Patient/' . $dataRecord['puuid']]);
            $immunizationResource->setPatient($patient);
        }

        if (!empty($dataRecord['administered_date'])) {
            $occurenceDateTime = new FHIRDateTime();
            $occurenceDateTime->setValue($dataRecord['administered_date']);
            $immunizationResource->setOccurrenceDateTime($occurenceDateTime);
        }

        if (!empty($dataRecord['create_date'])) {
            $recorded = new FHIRDateTime();
            $recorded->setValue($dataRecord['create_date']);
            $immunizationResource->setRecorded($recorded);
        }

        if (!empty($dataRecord['expiration_date'])) {
            $expirationDate = new FHIRDate();
            $expirationDate->setValue($dataRecord['expiration_date']);
            $immunizationResource->setExpirationDate($expirationDate);
        }

        if (!empty($dataRecord['note'])) {
            $immunizationResource->addNote(array(
                'text' => $dataRecord['note']
            ));
        }

        if (!empty($dataRecord['administration_site'])) {
            $siteCode = new FHIRCodeableConcept();
            $siteCode->addCoding(array(
                'system' => "http://terminology.hl7.org/CodeSystem/v3-ActSite",
                'code' =>  $dataRecord['site_code'],
                'display' => $dataRecord['site_display']
            ));
            $immunizationResource->setSite($siteCode);
        }

        if (!empty($dataRecord['lot_number'])) {
            $immunizationResource->setLotNumber($dataRecord['lot_number']);
        }

        if (!empty($dataRecord['administration_site'])) {
            $doseQuantity = new FHIRQuantity();
            $doseQuantity->setValue($dataRecord['amount_administered']);
            $doseQuantity->setSystem(FhirCodeSystemConstants::UNITS_OF_MEASURE);
            $doseQuantity->setCode($dataRecord['amount_administered_unit']);
            $immunizationResource->setDoseQuantity($doseQuantity);
        }

        if (!empty($dataRecord['provider_uuid']) && !empty($dataRecord['provider_npi'])) {
            $performer = new FHIRImmunizationPerformer();
            $performer->setActor(UtilsService::createRelativeReference("Practitioner", $dataRecord['provider_uuid']));
            $immunizationResource->addPerformer($performer);
        }

        // education is failing ONC validation, since we don't need it for ONC we are going to leave it off for now.
//        if (!empty($dataRecord['education_date'])) {
//            $education = new FHIRImmunizationEducation();
//            $educationDateTime = new FHIRDateTime();
//            $educationDateTime->setValue($dataRecord['education_date']);
//            $education->setPresentationDate($educationDateTime);
//            $immunizationResource->addEducation($education);
//        }

        if ($encode) {
            return json_encode($immunizationResource);
        } else {
            return $immunizationResource;
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        return $this->immunizationService->getAll($openEMRSearchParameters, true, $puuidBind);
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        if (!($dataRecord instanceof FHIRImmunization)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $author = null;
        if (!empty($dataRecord->getPerformer())) {
            $performer = current($dataRecord->getPerformer());
            $author = $performer->getActor();
        }
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $author);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    public function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
