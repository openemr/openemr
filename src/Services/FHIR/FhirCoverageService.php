<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMoney;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCoverage\FHIRCoverageClass;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCoverage\FHIRCoverageCostToBeneficiary;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Coverage Service - US Core 8.0 / USCDI v5 Compliant
 *
 * @package            OpenEMR
 * @link               https://www.open-emr.org
 * @author             Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @author             Jerry Padgett <sjpadgett@gmail.com>
 * @copyright          Copyright (c) 2021 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright          Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Claude.AI: Reviewed and made changes for accuracy against FHIR R4 and US Core 8.0 standards.
 */
class FhirCoverageService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-coverage';

    /**
     * @var InsuranceService
     */
    private $coverageService;

    // Insurance type code to FHIR ActCode mapping (from insurance_companies.ins_type_code)
    private const INS_TYPE_CODE_MAP = [
        1 => 'PUBLICPOL',      // Medicare
        2 => 'PUBLICPOL',      // Medicaid
        3 => 'HIP',            // Commercial/Other
        4 => 'HIP',            // Workers Comp
        5 => 'PUBLICPOL',      // TRICARE
        6 => 'PUBLICPOL',      // CHAMPVA
        7 => 'HIP',            // Group Health Plan
        8 => 'HIP',            // FECA
        9 => 'PUBLICPOL',      // Other Federal
        10 => 'HIP',           // Self-pay
        11 => 'HIP',           // Central Certification
        12 => 'HIP',           // Other Non-Federal
        13 => 'HIP',           // Preferred Provider Org
        14 => 'HIP',           // Point of Service
        15 => 'HIP',           // Exclusive Provider Org
        16 => 'HIP',           // Indemnity Insurance
        17 => 'HIP'            // HMO Medicare Risk
    ];

    private const DEFAULT_COVERAGE_TYPE = 'HIP';

    public function __construct()
    {
        parent::__construct();
        $this->coverageService = new InsuranceService();
    }

    /**
     * Returns an array mapping FHIR Coverage Resource search parameters to OpenEMR Coverage search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'payor' => new FhirSearchParameterDefinition('payor', SearchFieldType::TOKEN, ['provider']),
            'type' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['type']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date']);
    }

    /**
     * Parses an OpenEMR Insurance record, returning the equivalent FHIR Coverage Resource
     * Compliant with US Core 8.0 and USCDI v5
     *
     * @param array   $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCoverage
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $coverageResource = new FHIRCoverage();

        // Meta information
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->addProfile(self::USCGI_PROFILE_URI);
        if (!empty($dataRecord['date'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $coverageResource->setMeta($meta);

        // Resource ID
        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $coverageResource->setId($id);

        // Identifier (Must Support) - Policy Number with Member type
        if (!empty($dataRecord['policy_number'])) {
            $identifier = new FHIRIdentifier();
            $identifierType = new FHIRCodeableConcept();
            $identifierTypeCoding = new FHIRCoding();
            $identifierTypeCoding->setSystem("http://terminology.hl7.org/CodeSystem/v2-0203");
            $identifierTypeCoding->setCode("MB");
            $identifierTypeCoding->setDisplay("Member Number");
            $identifierType->addCoding($identifierTypeCoding);
            $identifier->setType($identifierType);
            $identifier->setValue($dataRecord['policy_number']);
            $coverageResource->addIdentifier($identifier);
        }

        // Policy Type as additional identifier if present
        if (!empty($dataRecord['policy_type'])) {
            $policyTypeIdentifier = new FHIRIdentifier();
            $policyTypeIdentifier->setSystem("http://openemr.org/fhir/policy-type");
            $policyTypeIdentifier->setValue($dataRecord['policy_type']);
            $coverageResource->addIdentifier($policyTypeIdentifier);
        }

        // Status (Required) - dynamic determination based on dates
        $status = new FHIRCode();
        $statusValue = $this->determineStatus($dataRecord);
        $status->setValue($statusValue);
        $coverageResource->setStatus($status);

        // Type (Required) - uses ins_type_code from insurance_companies
        $coverageType = $this->mapCoverageType($dataRecord);
        $coverageResource->setType($coverageType);

        // SubscriberId (Must Support) - policy number
        if (!empty($dataRecord['policy_number'])) {
            $subscriberId = new FHIRString();
            $subscriberId->setValue($dataRecord['policy_number']);
            $coverageResource->setSubscriberId($subscriberId);
        }

        // Beneficiary (Required) - patient reference
        if (isset($dataRecord['puuid'])) {
            $patient = new FHIRReference();
            $patient->setReference('Patient/' . $dataRecord['puuid']);
            $coverageResource->setBeneficiary($patient);
        }

        // Dependent (Must Support) - for non-self relationships
        if (
            isset($dataRecord['subscriber_relationship']) &&
            strtolower($dataRecord['subscriber_relationship']) !== 'self'
        ) {
            $dependent = new FHIRString();
            $dependent->setValue('1');
            $coverageResource->setDependent($dependent);
        }

        // Relationship (Must Support) - relationship to subscriber
        if (isset($dataRecord['subscriber_relationship'])) {
            $relationship = $this->mapRelationship($dataRecord['subscriber_relationship']);
            $coverageResource->setRelationship($relationship);
        }

        // Period (Must Support) - coverage effective dates
        if (!empty($dataRecord['date']) || !empty($dataRecord['date_end'])) {
            $period = new FHIRPeriod();
            if (!empty($dataRecord['date'])) {
                $period->setStart(UtilsService::getLocalDateAsUTC($dataRecord['date']));
            }
            if (!empty($dataRecord['date_end'])) {
                $period->setEnd(UtilsService::getLocalDateAsUTC($dataRecord['date_end']));
            }
            $coverageResource->setPeriod($period);
        }

        // Payor (Required) - insurance organization reference
        if (isset($dataRecord['insureruuid'])) {
            $payor = new FHIRReference();
            $payor->setReference('Organization/' . $dataRecord['insureruuid']);
            $coverageResource->addPayor($payor);
        }

        // Class (Must Support) - Group Number
        if (!empty($dataRecord['group_number'])) {
            $classGroup = new FHIRCoverageClass();

            $classType = new FHIRCodeableConcept();
            $classTypeCoding = new FHIRCoding();
            $classTypeCoding->setSystem("http://terminology.hl7.org/CodeSystem/coverage-class");
            $classTypeCoding->setCode("group");
            $classTypeCoding->setDisplay("Group");
            $classType->addCoding($classTypeCoding);
            $classGroup->setType($classType);

            $classValue = new FHIRString();
            $classValue->setValue($dataRecord['group_number']);
            $classGroup->setValue($classValue);

            if (!empty($dataRecord['plan_name'])) {
                $className = new FHIRString();
                $className->setValue($dataRecord['plan_name']);
                $classGroup->setName($className);
            }

            $coverageResource->addClass($classGroup);
        }

        // Class (Must Support) - Plan
        if (!empty($dataRecord['plan_name'])) {
            $classPlan = new FHIRCoverageClass();

            $classType = new FHIRCodeableConcept();
            $classTypeCoding = new FHIRCoding();
            $classTypeCoding->setSystem("http://terminology.hl7.org/CodeSystem/coverage-class");
            $classTypeCoding->setCode("plan");
            $classTypeCoding->setDisplay("Plan");
            $classType->addCoding($classTypeCoding);
            $classPlan->setType($classType);

            $classValue = new FHIRString();
            $classValue->setValue($dataRecord['plan_name']);
            $classPlan->setValue($classValue);

            $className = new FHIRString();
            $className->setValue($dataRecord['plan_name']);
            $classPlan->setName($className);

            $coverageResource->addClass($classPlan);
        }

        // Order (Must Support) - coordination of benefits
        if (isset($dataRecord['type'])) {
            $order = $this->mapCoverageOrder($dataRecord['type']);
            if ($order !== null) {
                $coverageResource->setOrder($order);
            }
        }

        // CostToBeneficiary - copay information
        $copay = ValidationUtils::validateFloat($dataRecord['copay'] ?? null, min: 0.01);
        if ($copay !== false) {
            $costToBeneficiary = new FHIRCoverageCostToBeneficiary();

            $costType = new FHIRCodeableConcept();
            $costTypeCoding = new FHIRCoding();
            $costTypeCoding->setSystem("http://terminology.hl7.org/CodeSystem/coverage-copay-type");
            $costTypeCoding->setCode("copay");
            $costTypeCoding->setDisplay("Copay");
            $costType->addCoding($costTypeCoding);
            $costToBeneficiary->setType($costType);

            $valueMoney = new FHIRMoney();
            $valueMoney->setValue($copay);
            $valueMoney->setCurrency('USD');

            $costToBeneficiary->setValueMoney($valueMoney);

            $coverageResource->addCostToBeneficiary($costToBeneficiary);
        }

        if ($encode) {
            return json_encode($coverageResource);
        } else {
            return $coverageResource;
        }
    }

    /**
     * Determine coverage status based on effective dates
     *
     * @param array $dataRecord
     * @return string
     */
    private function determineStatus($dataRecord): string
    {
        $now = date('Y-m-d');

        if (empty($dataRecord['date'])) {
            return 'active';
        }

        $startDate = $dataRecord['date'];
        $endDate = $dataRecord['date_end'] ?? null;

        // Future coverage
        if ($startDate > $now) {
            return 'draft';
        }

        // Expired coverage
        if (!empty($endDate) && $endDate < $now) {
            return 'cancelled';
        }

        // Active coverage
        if ($startDate <= $now && (empty($endDate) || $endDate >= $now)) {
            return 'active';
        }

        return 'active';
    }

    /**
     * Map insurance type code to FHIR Coverage type CodeableConcept
     * Uses ins_type_code from insurance_companies table
     *
     * @param array $dataRecord
     * @return FHIRCodeableConcept
     */
    private function mapCoverageType($dataRecord): FHIRCodeableConcept
    {
        $coverageType = new FHIRCodeableConcept();
        $coding = new FHIRCoding();
        $coding->setSystem("http://terminology.hl7.org/CodeSystem/v3-ActCode");

        // Use ins_type_code from insurance_companies if available
        $code = self::DEFAULT_COVERAGE_TYPE;
        if (isset($dataRecord['ins_type_code']) && isset(self::INS_TYPE_CODE_MAP[$dataRecord['ins_type_code']])) {
            $code = self::INS_TYPE_CODE_MAP[$dataRecord['ins_type_code']];
        }

        $coding->setCode($code);

        // Set display text
        $displayMap = [
            'HIP' => 'health insurance plan number',
            'PUBLICPOL' => 'public healthcare',
            'EHCPOL' => 'extended healthcare',
        ];

        $coding->setDisplay($displayMap[$code] ?? 'health insurance plan number');
        $coverageType->addCoding($coding);

        return $coverageType;
    }

    /**
     * Map subscriber relationship to FHIR relationship CodeableConcept
     *
     * @param string $relationship
     * @return FHIRCodeableConcept
     */
    private function mapRelationship($relationship): FHIRCodeableConcept
    {
        $relationshipCode = new FHIRCodeableConcept();
        $coding = new FHIRCoding();
        $coding->setSystem("http://terminology.hl7.org/CodeSystem/subscriber-relationship");

        // Map common relationship values (case-insensitive)
        $relationshipMap = [
            'self' => 'self',
            'spouse' => 'spouse',
            'child' => 'child',
            'parent' => 'parent',
            'common' => 'common',
            'other' => 'other',
        ];

        $code = $relationshipMap[strtolower($relationship)] ?? 'other';
        $coding->setCode($code);
        $coding->setDisplay(ucfirst($code));
        $relationshipCode->addCoding($coding);

        return $relationshipCode;
    }

    /**
     * Map insurance type to coordination of benefits order
     *
     * @param string $insuranceType
     * @return FHIRPositiveInt|null
     */
    private function mapCoverageOrder($insuranceType): ?FHIRPositiveInt
    {
        $orderMap = [
            'primary' => 1,
            'secondary' => 2,
            'tertiary' => 3,
        ];

        if (isset($orderMap[$insuranceType])) {
            $order = new FHIRPositiveInt();
            $order->setValue($orderMap[$insuranceType]);
            return $order;
        }

        return null;
    }

    /**
     * Parses a FHIR Coverage resource, returning the equivalent OpenEMR insurance_data row.
     *
     * The reverse of parseOpenEMRRecord. Unresolved references (Patient/uuid, Organization/uuid)
     * stay as opaque uuid strings here; insertOpenEMRRecord/updateOpenEMRRecord resolve them to
     * internal numeric ids against patient_data / insurance_companies.
     *
     * Note: FHIR Coverage carries less information than OpenEMR's insurance_data schema requires
     * (no subscriber DOB, sex, address, etc.). When the FHIR resource declares
     * relationship == 'self' we backfill those required fields from the beneficiary's patient_data
     * row inside insertOpenEMRRecord. Non-self relationships require the missing fields to be
     * supplied via FHIR extensions; without them the CoverageValidator will surface a 422.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed> OpenEMR-shaped record
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRCoverage)) {
            throw new \InvalidArgumentException(
                'Expected FHIRCoverage resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $data = [];

        $resourceId = $json['id'] ?? null;
        if (is_string($resourceId) && $resourceId !== '') {
            $data['uuid'] = $resourceId;
        }

        // beneficiary -> puuid (Patient uuid, resolved to pid downstream)
        $beneficiaryRef = FhirPayloadReader::reference($json['beneficiary'] ?? null);
        if ($beneficiaryRef !== null) {
            $beneficiaryUuid = UtilsService::parseReferenceString($beneficiaryRef, 'Patient')['uuid'] ?? null;
            if (
                is_string($beneficiaryUuid) && $beneficiaryUuid !== ''
                && UuidRegistry::isValidStringUUID($beneficiaryUuid)
            ) {
                $data['puuid'] = $beneficiaryUuid;
            }
        }

        // payor[0] -> insureruuid (Organization uuid, resolved to insurance_companies.id downstream)
        $payorRef = FhirPayloadReader::reference(FhirPayloadReader::get($json['payor'] ?? null, 0));
        if ($payorRef !== null) {
            $payorUuid = UtilsService::parseReferenceString($payorRef, 'Organization')['uuid'] ?? null;
            if (is_string($payorUuid) && $payorUuid !== '' && UuidRegistry::isValidStringUUID($payorUuid)) {
                $data['insureruuid'] = $payorUuid;
            }
        }

        // subscriberId -> policy_number
        $subscriberId = $json['subscriberId'] ?? null;
        if (is_string($subscriberId) && $subscriberId !== '') {
            $data['policy_number'] = $subscriberId;
        }

        // relationship -> subscriber_relationship (uses our own reverse of mapRelationship)
        $relationshipCode = FhirPayloadReader::firstCodingCode($json['relationship'] ?? null);
        if ($relationshipCode !== '') {
            $data['subscriber_relationship'] = $relationshipCode;
        }

        // order (positiveInt: 1=primary, 2=secondary, 3=tertiary) -> type
        if (isset($json['order']) && is_numeric($json['order'])) {
            $data['type'] = match ((int) $json['order']) {
                1 => 'primary',
                2 => 'secondary',
                3 => 'tertiary',
                default => 'primary',
            };
        }

        // period -> date / date_end. Coverage periods are routinely quoted to
        // month precision ("cover starts 2024-01"), so partial values are
        // widened to the first day of the period rather than rejected.
        $period = $json['period'] ?? null;
        $periodStart = FhirDateTimeParser::toDbDate(
            is_array($period) ? ($period['start'] ?? null) : null,
            'Coverage.period.start',
            true
        );
        if ($periodStart !== null) {
            $data['date'] = $periodStart;
        }
        $periodEnd = FhirDateTimeParser::toDbDate(
            is_array($period) ? ($period['end'] ?? null) : null,
            'Coverage.period.end',
            true
        );
        if ($periodEnd !== null) {
            $data['date_end'] = $periodEnd;
        }

        // class[] -> group_number / plan_name
        $classEntries = $json['class'] ?? null;
        foreach (is_array($classEntries) ? $classEntries : [] as $coverageClass) {
            if (!is_array($coverageClass)) {
                continue;
            }
            $classCode = FhirPayloadReader::firstCodingCode($coverageClass['type'] ?? null);
            $classValue = $coverageClass['value'] ?? null;
            if ($classCode === '' || !is_string($classValue) || $classValue === '') {
                continue;
            }
            if ($classCode === 'group') {
                $data['group_number'] = $classValue;
            } elseif ($classCode === 'plan') {
                $data['plan_name'] = $classValue;
            }
        }

        // status (R4 1..1 modifier). OpenEMR has no status column; the read side derives
        // status from date/date_end. Rather than silently dropping caller-supplied status,
        // we stash it for the insert/update path to validate against the derived status.
        // If the values disagree the caller gets a 422.
        $status = $json['status'] ?? null;
        if (is_string($status) && $status !== '') {
            $data['__fhir_status__'] = $status;
        }

        // costToBeneficiary[copay].valueMoney.value -> copay (string column)
        $costs = $json['costToBeneficiary'] ?? null;
        foreach (is_array($costs) ? $costs : [] as $cost) {
            if (!is_array($cost) || FhirPayloadReader::firstCodingCode($cost['type'] ?? null) !== 'copay') {
                continue;
            }
            $money = $cost['valueMoney'] ?? null;
            $value = is_array($money) ? ($money['value'] ?? null) : null;
            if (is_numeric($value)) {
                $data['copay'] = (string) $value;
                break;
            }
        }

        return $data;
    }

    /**
     * Inserts an OpenEMR insurance_data record from a parsed FHIR Coverage.
     *
     * Resolves Patient/uuid -> pid and Organization/uuid -> insurance_companies.id. Backfills
     * subscriber_* fields from patient_data when relationship == 'self'. Applies safe defaults
     * for accept_assignment and policy_type. Validation is enforced downstream by CoverageValidator.
     *
     * @param mixed $openEmrRecord The parsed record from parseFhirResource()
     * @return ProcessingResult
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        if (!is_array($openEmrRecord)) {
            throw new \InvalidArgumentException('Expected a parsed OpenEMR Coverage record array');
        }

        $statusError = $this->validateStatusAgainstDates($openEmrRecord);
        if ($statusError !== null) {
            return $statusError;
        }
        unset($openEmrRecord['__fhir_status__']);

        $resolveResult = $this->resolveReferences($openEmrRecord);
        if ($resolveResult !== null) {
            return $resolveResult;
        }

        $this->applyDefaults($openEmrRecord);

        return $this->coverageService->insert($openEmrRecord);
    }

    /**
     * Updates an existing OpenEMR insurance_data record from a parsed FHIR Coverage.
     *
     * @param string $fhirResourceId The OpenEMR record's FHIR Resource ID (uuid)
     * @param array<array-key, mixed> $updatedOpenEMRRecord
     * @return ProcessingResult
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        $updatedOpenEMRRecord['uuid'] = $fhirResourceId;

        $resolveResult = $this->resolveReferences($updatedOpenEMRRecord);
        if ($resolveResult !== null) {
            return $resolveResult;
        }

        // InsuranceService::update() rewrites every insurance_data column in one statement,
        // binding each one unconditionally. FHIR Coverage cannot express most of them --
        // subscriber demographics, employer address, copay -- so the parsed record is only
        // ever a partial row. Overlay it on the stored row so the untouched columns keep
        // their current values instead of being read as undefined and written as null.
        $existing = $this->fetchStoredRecord($fhirResourceId);
        if ($existing === null) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['uuid' => 'Coverage not found']);
            return $result;
        }
        // A beneficiary naming a different patient is rejected, not merged. array_merge() lets
        // the request's pid win, so without this a PUT could move the coverage into another
        // patient's chart; omitting beneficiary entirely still keeps the stored pid, because
        // then there is no pid in the parsed record to override it with.
        $requestedPid = $updatedOpenEMRRecord['pid'] ?? null;
        $storedPid = $existing['pid'] ?? null;
        if (is_numeric($requestedPid) && is_numeric($storedPid) && (int) $requestedPid !== (int) $storedPid) {
            $result = new ProcessingResult();
            $result->setValidationMessages(
                ['beneficiary' => 'Coverage.beneficiary does not match the stored coverage\'s patient']
            );
            return $result;
        }

        $updatedOpenEMRRecord = array_merge($existing, $updatedOpenEMRRecord);

        // Validated after the overlay, not before: a PUT that carries `status` but omits
        // `period` has no dates of its own, so checking the request body alone would compare
        // the caller's status against an empty period and accept -- or reject -- the wrong
        // thing. The merged record is what actually gets written, so it is what gets checked.
        $statusError = $this->validateStatusAgainstDates($updatedOpenEMRRecord);
        if ($statusError !== null) {
            return $statusError;
        }
        unset($updatedOpenEMRRecord['__fhir_status__']);

        $this->applyDefaults($updatedOpenEMRRecord);

        $result = $this->coverageService->update($updatedOpenEMRRecord);

        return $result instanceof ProcessingResult ? $result : new ProcessingResult();
    }

    /**
     * Loads the stored insurance_data row for a Coverage uuid, keyed by column name.
     *
     * The `uuid` and `id` columns are dropped: `uuid` is binary here and the caller
     * supplies the string form, and `id` is not part of the update payload.
     *
     * @return array<string, mixed>|null Null when no row carries that uuid.
     */
    private function fetchStoredRecord(string $fhirResourceId): ?array
    {
        if (!UuidRegistry::isValidStringUUID($fhirResourceId)) {
            return null;
        }
        $row = QueryUtils::querySingleRow(
            "SELECT * FROM insurance_data WHERE uuid = ?",
            [UuidRegistry::uuidToBytes($fhirResourceId)]
        );
        if (!is_array($row)) {
            return null;
        }
        unset($row['uuid'], $row['id']);

        $stored = [];
        foreach ($row as $column => $value) {
            $stored[(string) $column] = $value;
        }

        return $stored;
    }

    /**
     * Validate caller-supplied FHIR Coverage.status (R4 1..1 modifier) against what
     * determineStatus() would derive from the supplied dates. OpenEMR has no status
     * column, so the read side is authoritative — but rather than silently dropping
     * a disagreeing input we surface a 422 so callers get an explicit signal.
     * Accepts 'active', 'cancelled', 'draft', and 'entered-in-error'.
     *
     * @param array<array-key, mixed> $record
     */
    private function validateStatusAgainstDates(array $record): ?ProcessingResult
    {
        $supplied = $record['__fhir_status__'] ?? null;
        if (!is_string($supplied) || $supplied === '') {
            return null;
        }
        if ($supplied === 'entered-in-error') {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'status' => 'FHIR Coverage.status="entered-in-error" is not writable; '
                    . 'OpenEMR has no equivalent state.',
            ]);
            return $result;
        }
        $derived = $this->determineStatus($record);
        if ($supplied !== $derived) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'status' => "FHIR Coverage.status='" . $supplied . "' disagrees with "
                    . "the status derived from period dates ('" . $derived . "'). "
                    . "OpenEMR derives status from period.start / period.end at read time; "
                    . "adjust the dates or omit status.",
            ]);
            return $result;
        }
        return null;
    }

    /**
     * Resolves opaque FHIR uuids (puuid, insureruuid) into the numeric ids that InsuranceService
     * requires (pid, provider). Returns a ProcessingResult with a 422-style error if a reference
     * cannot be resolved; null on success.
     *
     * @param array<array-key, mixed> $record (mutated in place)
     * @return ProcessingResult|null
     */
    private function resolveReferences(array &$record): ?ProcessingResult
    {
        $puuid = $record['puuid'] ?? null;
        if (is_string($puuid) && $puuid !== '') {
            $puuidBytes = UuidRegistry::uuidToBytes($puuid);
            $pid = QueryUtils::fetchSingleValue(
                "SELECT pid FROM patient_data WHERE uuid = ?",
                'pid',
                [$puuidBytes]
            );
            if (!is_numeric($pid)) {
                $result = new ProcessingResult();
                $result->setValidationMessages([
                    'beneficiary' => ['Patient reference could not be resolved' => $puuid],
                ]);
                return $result;
            }
            $record['pid'] = (int) $pid;
            unset($record['puuid']);
        }

        $insurerUuid = $record['insureruuid'] ?? null;
        if (is_string($insurerUuid) && $insurerUuid !== '') {
            $insurerUuidBytes = UuidRegistry::uuidToBytes($insurerUuid);
            $providerId = QueryUtils::fetchSingleValue(
                "SELECT id FROM insurance_companies WHERE uuid = ?",
                'id',
                [$insurerUuidBytes]
            );
            if (!is_numeric($providerId)) {
                $result = new ProcessingResult();
                $result->setValidationMessages([
                    'payor' => ['Organization reference could not be resolved to an insurance company' => $insurerUuid],
                ]);
                return $result;
            }
            $record['provider'] = (int) $providerId;
            unset($record['insureruuid']);
        }

        return null;
    }

    /**
     * Applies safe defaults for fields FHIR Coverage doesn't carry but CoverageValidator requires.
     * When relationship == 'self', subscriber demographic fields are sourced from patient_data.
     *
     * @param array<array-key, mixed> $record (mutated in place)
     */
    private function applyDefaults(array &$record): void
    {
        $record['accept_assignment'] ??= 'TRUE';
        $record['policy_type'] ??= '';

        $relationship = $record['subscriber_relationship'] ?? null;
        $pid = $record['pid'] ?? null;
        if ($relationship !== 'self' || !is_numeric($pid) || (int) $pid <= 0) {
            return;
        }

        $patient = QueryUtils::fetchRecords(
            "SELECT fname, mname, lname, DOB, sex, street, city, state, postal_code, country_code, phone_home, ss "
            . "FROM patient_data WHERE pid = ?",
            [$pid]
        );
        $p = $patient[0] ?? null;
        if (!is_array($p)) {
            return;
        }

        $record['subscriber_fname'] ??= $p['fname'] ?? '';
        $record['subscriber_mname'] ??= $p['mname'] ?? '';
        $record['subscriber_lname'] ??= $p['lname'] ?? '';
        $record['subscriber_DOB'] ??= $p['DOB'] ?? '';
        $record['subscriber_sex'] ??= $p['sex'] ?? '';
        $record['subscriber_street'] ??= $p['street'] ?? '';
        $record['subscriber_city'] ??= $p['city'] ?? '';
        $record['subscriber_state'] ??= $p['state'] ?? '';
        $record['subscriber_postal_code'] ??= $p['postal_code'] ?? '';
        $record['subscriber_country'] ??= $p['country_code'] ?? '';
        $record['subscriber_phone'] ??= $p['phone_home'] ?? '';
        $record['subscriber_ss'] ??= $p['ss'] ?? '';
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $result = $this->coverageService->search($openEMRSearchParameters);

        // Filter out records without insurance company (required payor)
        $filteredData = [];
        foreach ($result->getData() as $record) {
            if (!empty($record['provider']) && !empty($record['insureruuid'])) {
                $filteredData[] = $record;
            }
        }

        $filteredResult = new ProcessingResult();
        foreach ($filteredData as $record) {
            $filteredResult->addData($record);
        }

        return $filteredResult;
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, ['', '7.0.0', '8.0.0']);
    }

    /**
     * @param array $dataRecord
     * @param bool $encode
     * @return FHIRProvenance|string|false The FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function createProvenanceResource($dataRecord = [], $encode = false): FHIRProvenance|string|false
    {
        if (!($dataRecord instanceof FHIRCoverage)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenance = $this->getFhirProvenanceService()->createProvenanceForDomainResource($dataRecord);
        if ($fhirProvenance === null) {
            // Provenance can legitimately be unavailable (e.g. no resolvable organization/author
            // reference); FhirServiceBase::getAll() treats a falsy return as "no provenance
            // available" and continues (see issue #13054).
            return false;
        }
        return $encode ? json_encode($fhirProvenance) : $fhirProvenance;
    }

    /**
     * Seam so unit tests can substitute the provenance factory.
     */
    protected function getFhirProvenanceService(): FhirProvenanceService
    {
        return new FhirProvenanceService();
    }
}
