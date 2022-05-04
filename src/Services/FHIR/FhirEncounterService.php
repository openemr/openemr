<?php

/**
 * FhirEncounterService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson snielson@discoverandchange.com
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020, 2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2020 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use DateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterHospitalization;
use OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterLocation;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterParticipant;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirEncounterService extends FhirServiceBase implements
    IFhirExportableResourceService,
    IPatientCompartmentResourceService,
    IResourceUSCIGProfileService
{
    use PatientSearchTrait;
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    public const ENCOUNTER_STATUS_FINISHED = "finished";

    public const ENCOUNTER_TYPE_CHECK_UP = "185349003";
    public const ENCOUNTER_TYPE_CHECK_UP_DESCRIPTION = "Encounter for check up (procedure)";

    public const ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER = "PPRF";
    public const ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER_TEXT = "Primary Performer";

    public const ENCOUNTER_PARTICIPANT_TYPE_REFERRER = "REF";
    public const ENCOUNTER_PARTICIPANT_TYPE_REFERRER_TEXT = "Referrer";


    /**
     * @var EncounterService
     */
    private $encounterService;

    public function __construct()
    {
        parent::__construct();
        $this->encounterService = new EncounterService();
    }

    /**
     * Returns an array mapping FHIR Encounter Resource search parameters to OpenEMR Encounter search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition(
                '_id',
                SearchFieldType::TOKEN,
                [
                    new ServiceField(
                        'euuid',
                        ServiceField::TYPE_UUID
                    )
                ]
            ),
            'patient' => $this->getPatientContextSearchField(),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date'])
        ];
    }

    /**
     * Parses an OpenEMR patient record, returning the equivalent FHIR Patient Resource
     * https://build.fhir.org/ig/HL7/US-Core-R4/StructureDefinition-us-core-encounter-definitions.html
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIREncounter
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $encounterResource = new FHIREncounter();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
        $encounterResource->setMeta($meta);

        $id = new FhirId();
        $id->setValue($dataRecord['euuid']);
        $encounterResource->setId($id);

        // identifier - required
        $identifier = new FHIRIdentifier();
        $identifier->setValue($dataRecord['euuid']);
        $identifier->setSystem(FhirCodeSystemConstants::RFC_3986);
        $encounterResource->addIdentifier($identifier);

        // status - required
        $status = new FHIRCode(self::ENCOUNTER_STATUS_FINISHED);
        $encounterResource->setStatus($status);

        // class
        if (!empty($dataRecord['class_code'])) {
            $class = new FHIRCoding();
            $class->setSystem(FhirCodeSystemConstants::HL7_V3_ACT_CODE);
            $class->setCode($dataRecord['class_code']);
            $class->setDisplay($dataRecord['class_title']);
            $encounterResource->setClass($class);
        } else {
            $encounterResource->setClass(UtilsService::createDataAbsentUnknownCodeableConcept());
        }

        // TODO: @adunsulag check with @brady.miller and find out if this really is the only possible encounter type
        // ...  it was here originally
        $type = UtilsService::createCodeableConcept(
            [self::ENCOUNTER_TYPE_CHECK_UP => [
                'code' => self::ENCOUNTER_TYPE_CHECK_UP
                , "description" => self::ENCOUNTER_TYPE_CHECK_UP_DESCRIPTION
                , "system" => FhirCodeSystemConstants::SNOMED_CT
            ]]
        );
        $encounterResource->addType($type);

        // subject - required
        if (!empty($dataRecord['puuid'])) {
            $encounterResource->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } else {
            $encounterResource->setSubject(UtilsService::createDataMissingExtension());
        }

        // participant - must support
        if (!empty($dataRecord['provider_uuid'])) {
            $participant = new FHIREncounterParticipant();
            $participant->setIndividual(
                UtilsService::createRelativeReference(
                    "Practitioner",
                    $dataRecord['provider_uuid']
                )
            );
            $period = new FHIRPeriod();
            $period->setStart(DateTime::createFromFormat("Y-m-d H:i:s", $dataRecord['date'])->format('c'));
            $period->setStart(gmdate('c', strtotime($dataRecord['date'])));
            $participant->setPeriod($period);

            $participantType = UtilsService::createCodeableConcept([
                self::ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER =>
                [
                    'code' => self::ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER
                    ,'description' => self::ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER_TEXT
                    ,'system' => FhirCodeSystemConstants::HL7_PARTICIPATION_TYPE
                ]
            ]);
            $participant->addType($participantType);
            $encounterResource->addParticipant($participant);
        }

        // referring provider
        if (!empty($dataRecord['referrer_uuid'])) {
            $participant = new FHIREncounterParticipant();
            $participant->setIndividual(
                UtilsService::createRelativeReference(
                    "Practitioner",
                    $dataRecord['referrer_uuid']
                )
            );
            $period = new FHIRPeriod();
            $period->setStart(DateTime::createFromFormat("Y-m-d H:i:s", $dataRecord['date'])->format('c'));
            $period->setStart(gmdate('c', strtotime($dataRecord['date'])));
            $participant->setPeriod($period);

            $participantType = UtilsService::createCodeableConcept([
                self::ENCOUNTER_PARTICIPANT_TYPE_REFERRER =>
                [
                    'code' => self::ENCOUNTER_PARTICIPANT_TYPE_REFERRER
                    ,'description' => self::ENCOUNTER_PARTICIPANT_TYPE_REFERRER_TEXT
                    ,'system' => FhirCodeSystemConstants::HL7_PARTICIPATION_TYPE
                ]
            ]);
            $participant->addType($participantType);
            $encounterResource->addParticipant($participant);
        }

        // period - must support
        if (!empty($dataRecord['date'])) {
            $period = new FHIRPeriod();
            $period->setStart(DateTime::createFromFormat("Y-m-d H:i:s", $dataRecord['date'])->format('c'));
            $encounterResource->setPeriod($period);
        }

        // reasonCode - must support OR must support reasonReference
        if (!empty($dataRecord['reason'])) {
            // Note: that we use the encounter textual representation for the reason here which is just fine as ccda
            // uses a textual representation of this.  According to HL7 chat this is just fine as epoch and
            // other systems do it this way
            // @see https://chat.fhir.org/#narrow/stream/179175-argonaut/topic/Encounter.20Reason.20For.20Visit
            // (beware of link rot)
            $reason = new FHIRCodeableConcept();
            $reasonText = $dataRecord['reason'] ?? "";
            $reason->setText(trim($reasonText));
            $encounterResource->addReasonCode($reason);
        }
        // hospitalization - must support

        // hospitalization.dischargeDisposition - must support
        if (!empty($dataRecord['discharge_disposition'])) {
            $code = $dataRecord['discharge_disposition'];
            $text = $dataRecord['discharge_disposition_text'];

            $hospitalization = new FHIREncounterHospitalization();
            $hospitalization->setDischargeDisposition(UtilsService::createCodeableConcept(
                [
                    $code => [
                        'code' => $text,
                        'description' => $text,
                        'system' => FhirCodeSystemConstants::HL7_DISCHARGE_DISPOSITION
                    ]
                ]
            ));
            $encounterResource->setHospitalization($hospitalization);
        }

        // SHALL support either location.location OR serviceProvider
        // however ONC inferno requires both serviceProvider AND location.location
        // location.location - must support
        // serviceProvider - must support
        if (!empty($dataRecord['facility_uuid'])) {
            $encounterResource->setServiceProvider(
                UtilsService::createRelativeReference(
                    'Organization',
                    $dataRecord['facility_uuid']
                )
            );

            // grab the facility location address
            if (!empty($dataRecord['facility_location_uuid'])) {
                $location = new FHIREncounterLocation();
                $location->setLocation(
                    UtilsService::createRelativeReference(
                        "Location",
                        $dataRecord['facility_location_uuid']
                    )
                );
                $encounterResource->addLocation($location);
            }
        }

        if ($encode) {
            return json_encode($encounterResource);
        } else {
            return $encounterResource;
        }
    }

    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        if (!($dataRecord instanceof FHIREncounter)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $provenanceService = new FhirProvenanceService();
        $author = null;
        if (!empty($dataRecord->getParticipant())) {
            // grab the first one for author
            $participant = reset($dataRecord->getParticipant());
            $author = $participant->getIndividual() ?? null;
        }
        $provenance = $provenanceService->createProvenanceForDomainResource($dataRecord, $author);
        return $provenance;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($searchParam, $puuidBind = null): ProcessingResult
    {
        return $this->encounterService->search($searchParam, true, $puuidBind);
    }

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    function getProfileURIs(): array
    {
        return [
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-encounter'
        ];
    }
}
