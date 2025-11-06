<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMoney;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCoverage\FHIRCoverageClass;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCoverage\FHIRCoverageCostToBeneficiary;
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
 * @link               http://www.open-emr.org
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
     * @param boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
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
        if (!empty($dataRecord['copay']) && is_numeric($dataRecord['copay']) && $dataRecord['copay'] > 0) {
            $costToBeneficiary = new FHIRCoverageCostToBeneficiary();

            $costType = new FHIRCodeableConcept();
            $costTypeCoding = new FHIRCoding();
            $costTypeCoding->setSystem("http://terminology.hl7.org/CodeSystem/coverage-copay-type");
            $costTypeCoding->setCode("copay");
            $costTypeCoding->setDisplay("Copay");
            $costType->addCoding($costTypeCoding);
            $costToBeneficiary->setType($costType);

            $valueMoney = new FHIRMoney();
            $valueMoney->setValue($dataRecord['copay']);
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
        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
        return $encode ? json_encode($fhirProvenance) : $fhirProvenance;
    }
}
