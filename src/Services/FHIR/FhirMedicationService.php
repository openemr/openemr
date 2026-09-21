<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationBatch;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\DrugService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Medication Service
 *
 * @package            OpenEMR
 * @link               https://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirMedicationService extends FhirServiceBase implements IResourceUSCIGProfileService, INonPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use VersionedProfileTrait;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-medication';

    /**
     * @var DrugService
     */
    private $medicationService;

    public function __construct()
    {
        parent::__construct();
        $this->medicationService = new DrugService();
    }

    /**
     * Returns an array mapping FHIR Medication Resource search parameters to OpenEMR Medication search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['drug_last_updated']);
    }

    /**
     * Parses an OpenEMR medication record, returning the equivalent FHIR Medication Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRMedication
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $medicationResource = new FHIRMedication();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['drug_last_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['drug_last_updated']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $medicationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $medicationResource->setId($id);

        if ($dataRecord['active'] == '1') {
            $medicationResource->setStatus("active");
        } else {
            $medicationResource->setStatus("inactive");
        }

        if (!empty($dataRecord['drug_code'])) {
            $medicationCoding = new FHIRCoding();
            $medicationCode = new FHIRCodeableConcept();
            foreach ($dataRecord['drug_code'] as $code => $codeValues) {
                $medicationCoding->setSystem($codeValues['system']);
                $medicationCoding->setCode($code);
                $medicationCoding->setDisplay($codeValues['description']);
                $medicationCode->addCoding($medicationCoding);
            }
            $medicationResource->setCode($medicationCode);
        }

        //alternative for switch case
        [$formDisplay, $formCode] = [
            '1' => ['suspension', 'C60928'],
            '2' => ['tablet', 'C42998'],
            '3' => ['capsule', 'C25158'],
            '4' => ['solution', 'C42986'],
            '5' => ['tsp', 'C48544'],
            '6' => ['ml', 'C28254'],
            '7' => ['units', 'C44278'],
            '8' => ['inhalation', 'C42944'],
            '9' => ['gtts(drops)', 'C48491'],
            '10' => ['cream', 'C28944'],
            '11' => ['ointment', 'C42966'],
            '12' => ['puff', 'C42944']
        ][$dataRecord['form']] ?? ['', ''];

        if (!empty($formCode)) {
            $form = new FHIRCodeableConcept();
            $formCoding = new FHIRCoding();
            $formCoding->setSystem("http://ncimeta.nci.nih.gov");
            $formCoding->setCode($formCode);
            $formCoding->setDisplay($formDisplay);
            $form->addCoding($formCoding);
        }

        if (isset($dataRecord['expiration']) || isset($dataRecord['expiration'])) {
            $batch = new FHIRMedicationBatch();
            if (isset($dataRecord['expiration'])) {
                $expirationDate = new FHIRDateTime();
                $expirationDate->setValue($dataRecord['expiration']);
                $batch->setExpirationDate($expirationDate);
            }
            if (isset($dataRecord['lot_number'])) {
                $batch->setLotNumber($dataRecord['lot_number']);
            }
            $medicationResource->setBatch($batch);
        }

        if ($encode) {
            return json_encode($medicationResource);
        } else {
            return $medicationResource;
        }
    }

    /**
     * Parses a FHIR Medication resource into the OpenEMR drugs-table shape.
     *
     * Medication is master data — there are no references to resolve. Mapping:
     *  - status -> active (1|0)
     *  - code.coding (prefer RxNorm, else first coding with a code value) -> drug_code
     *  - code.coding[0].display or code.text -> name
     *  - form.coding[0].code (NCI Thesaurus) -> form (integer string keyed into the read map)
     *
     * Batch (lot/expiration) is read-only at this layer — those columns live on drug_inventory,
     * not drugs — so we ignore them on write.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRMedication)) {
            throw new \InvalidArgumentException(
                'Expected FHIRMedication resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $data = [];

        $resourceId = $json['id'] ?? null;
        if (is_string($resourceId) && $resourceId !== '') {
            $data['uuid'] = $resourceId;
        }

        // status -> active
        $status = $json['status'] ?? null;
        if (is_string($status) && $status !== '') {
            $data['active'] = $status === 'active' ? 1 : 0;
        }

        // code.coding[] -> drug_code (prefer RxNorm) + name (display).
        //
        // The value is stored in OpenEMR's typed `TYPE:CODE` form rather than as a bare code.
        // DrugService::createResultRecordFromDatabaseResult() reads drug_code back through
        // addCoding(), which derives the FHIR system from that prefix and treats an unprefixed
        // value as RxNorm -- so a bare SNOMED or NDC code would come back out claiming to be
        // RXCUI.
        $codeTypesService = new CodeTypesService();
        $codeConcept = $json['code'] ?? null;
        $codings = FhirPayloadReader::codings($codeConcept);
        $primaryDisplay = null;
        $sawCode = false;
        foreach ($codings as $coding) {
            $system = $coding['system'] ?? '';
            $code = FhirPayloadReader::getString($coding, 'code');
            $display = FhirPayloadReader::getString($coding, 'display');
            if ($primaryDisplay === null && $display !== null) {
                $primaryDisplay = $display;
            }
            if ($code !== null) {
                $sawCode = true;
            }
            if (
                $system === FhirCodeSystemConstants::RXNORM
                && $code !== null
                && !isset($data['drug_code'])
            ) {
                $data['drug_code'] = $codeTypesService->getOpenEMRCodeForSystemAndCode($system, $code);
            }
        }
        // Fall back to the first coding whose system OpenEMR recognises. An unrecognised or
        // absent system is skipped rather than stored: getOpenEMRCodeForSystemAndCode() answers
        // the bare code in that case, and `drugs`.`drug_code` is read back through
        // CodeTypesService, which derives the system from the TYPE: prefix. A bare value has no
        // prefix, so the code would return under a system the caller never sent -- the server
        // asserting a provenance it was not given.
        if (!isset($data['drug_code'])) {
            foreach ($codings as $coding) {
                $fallbackCode = FhirPayloadReader::getString($coding, 'code');
                if ($fallbackCode === null) {
                    continue;
                }
                $fallbackSystem = $coding['system'] ?? null;
                $qualified = self::qualifiedCode(
                    $codeTypesService,
                    is_string($fallbackSystem) ? $fallbackSystem : null,
                    $fallbackCode
                );
                if ($qualified !== null) {
                    $data['drug_code'] = $qualified;
                    break;
                }
            }
        }
        // Codings were supplied and none could be qualified. Refused rather than stored
        // code-less: Medication.code is what identifies the drug, and a 201 for a medication
        // whose code was dropped tells the caller it was recorded. code.text alone is still
        // accepted -- that claims no code system, so there is nothing to misattribute.
        if ($sawCode && !isset($data['drug_code'])) {
            $data['__validation_error__'] = 'Medication.code.coding carries no system OpenEMR '
                . 'recognises, so the code cannot be stored without misattributing it';
            $data['__validation_field__'] = 'code';
        }
        $codeText = FhirPayloadReader::getString($codeConcept, 'text');
        if ($primaryDisplay !== null) {
            $data['name'] = $primaryDisplay;
        } elseif ($codeText !== null) {
            $data['name'] = $codeText;
        }

        // form.coding[0].code -> form (NCI -> integer reverse map mirroring parseOpenEMRRecord)
        $formCode = FhirPayloadReader::firstCodingCode($json['form'] ?? null);
        if ($formCode !== '') {
            $reverseFormMap = [
                'C60928' => '1',  // suspension
                'C42998' => '2',  // tablet
                'C25158' => '3',  // capsule
                'C42986' => '4',  // solution
                'C48544' => '5',  // tsp
                'C28254' => '6',  // ml
                'C44278' => '7',  // units
                'C42944' => '8',  // inhalation (collides with puff=12 on read; we pick inhalation)
                'C48491' => '9',  // gtts/drops
                'C28944' => '10', // cream
                'C42966' => '11', // ointment
            ];
            if (isset($reverseFormMap[$formCode])) {
                $data['form'] = $reverseFormMap[$formCode];
            }
        }

        return $data;
    }

    /**
     * The stored TYPE:CODE form, or null when the system is absent or unknown to OpenEMR.
     *
     * getOpenEMRCodeForSystemAndCode() answers the bare code for an unrecognised system, which
     * is indistinguishable on read from a code whose type was simply never recorded. Callers use
     * null to mean "cannot be represented" rather than storing something the reader will
     * mislabel.
     */
    private static function qualifiedCode(CodeTypesService $codeTypesService, ?string $system, string $code): ?string
    {
        if ($system === null || $system === '') {
            return null;
        }
        $stored = $codeTypesService->getOpenEMRCodeForSystemAndCode($system, $code);

        return str_contains($stored, ':') ? $stored : null;
    }

    /**
     * @param array<array-key, mixed> $record
     */
    private static function validationErrorFor(array $record): ?ProcessingResult
    {
        $message = $record['__validation_error__'] ?? null;
        if (!is_string($message) || $message === '') {
            return null;
        }
        $field = $record['__validation_field__'] ?? 'code';
        $result = new ProcessingResult();
        $result->setValidationMessages([is_string($field) ? $field : 'code' => $message]);

        return $result;
    }

    /**
     * @param mixed $openEmrRecord The parsed record from parseFhirResource()
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        $validationError = is_array($openEmrRecord) ? self::validationErrorFor($openEmrRecord) : null;
        if ($validationError !== null) {
            return $validationError;
        }

        return $this->medicationService->insert(FhirPayloadReader::stringKeyed($openEmrRecord));
    }

    /**
     * @param string $fhirResourceId
     * @param array<array-key, mixed> $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        $validationError = self::validationErrorFor($updatedOpenEMRRecord);
        if ($validationError !== null) {
            return $validationError;
        }

        return $this->medicationService->update(
            $fhirResourceId,
            FhirPayloadReader::stringKeyed($updatedOpenEMRRecord)
        );
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->medicationService->getAll($openEMRSearchParameters, true);
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
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }
}
