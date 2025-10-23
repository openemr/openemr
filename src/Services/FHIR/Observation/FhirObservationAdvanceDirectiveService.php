<?php

/**
 * FhirObservationAdvanceDirectiveService.php
 * Provides FHIR Observation resources for Advance Directive documentation
 * Supports US Core 8.0 and USCDI v5 requirements
 *
 * This class structure initial version was created by Claude A.I.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Observation\Trait\FhirObservationTrait;
use OpenEMR\Services\PatientAdvanceDirectiveService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationAdvanceDirectiveService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirObservationTrait;

    const CATEGORY_ASSESSMENT = 'assessment';

    // LOINC codes for advance directive types
    const ADI_LIVING_WILL_CODE = '75320-2';
    const ADI_POWER_OF_ATTORNEY_CODE = '75787-2';
    const ADI_DNR_ORDER_CODE = '78823-2';
    const ADI_GENERIC_CODE = '42348-3';

    // US Core 8.0 Profile URI
    const USCDI_PROFILE_ADI_DOCUMENTATION_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation';

    /**
     * Column mappings for advance directive observation codes
     * Maps LOINC codes to their metadata and profile versions
     */
    const COLUMN_MAPPINGS = [
        self::ADI_LIVING_WILL_CODE => [
            'fullcode' => 'LOINC:' . self::ADI_LIVING_WILL_CODE,
            'code' => self::ADI_LIVING_WILL_CODE,
            'description' => 'Advance directive - living will',
            'category' => self::CATEGORY_ASSESSMENT,
            'document_type' => 'Living Will',
            'profiles' => [
                self::USCDI_PROFILE_ADI_DOCUMENTATION_URI => ['8.0.0']
            ]
        ],
        self::ADI_POWER_OF_ATTORNEY_CODE => [
            'fullcode' => 'LOINC:' . self::ADI_POWER_OF_ATTORNEY_CODE,
            'code' => self::ADI_POWER_OF_ATTORNEY_CODE,
            'description' => 'Advance directive - medical power of attorney',
            'category' => self::CATEGORY_ASSESSMENT,
            'document_type' => 'Durable Power of Attorney',
            'profiles' => [
                self::USCDI_PROFILE_ADI_DOCUMENTATION_URI => ['8.0.0']
            ]
        ],
        self::ADI_DNR_ORDER_CODE => [
            'fullcode' => 'LOINC:' . self::ADI_DNR_ORDER_CODE,
            'code' => self::ADI_DNR_ORDER_CODE,
            'description' => 'Do not resuscitate order',
            'category' => self::CATEGORY_ASSESSMENT,
            'document_type' => 'Do Not Resuscitate Order',
            'profiles' => [
                self::USCDI_PROFILE_ADI_DOCUMENTATION_URI => ['8.0.0']
            ]
        ],
        self::ADI_GENERIC_CODE => [
            'fullcode' => 'LOINC:' . self::ADI_GENERIC_CODE,
            'code' => self::ADI_GENERIC_CODE,
            'description' => 'Advance directive',
            'category' => self::CATEGORY_ASSESSMENT,
            'document_type' => 'Advance Directive',
            'profiles' => [
                self::USCDI_PROFILE_ADI_DOCUMENTATION_URI => ['8.0.0']
            ]
        ],
    ];

    private ?PatientAdvanceDirectiveService $advanceDirectiveService = null;

    /**
     * Check if this service supports a given LOINC code
     */
    public function supportsCode(string $code): bool
    {
        return isset(self::COLUMN_MAPPINGS[$code]);
    }

    /**
     * Check if this service supports a given category
     */
    public function supportsCategory($category): bool
    {
        return $category === self::CATEGORY_ASSESSMENT;
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     * @return array<string, FhirSearchParameterDefinition>
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [
                new ServiceField('uuid', ServiceField::TYPE_UUID)
            ]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_modified']);
    }

    /**
     * Get or create the PatientAdvanceDirectiveService instance
     */
    public function getAdvanceDirectiveService(): PatientAdvanceDirectiveService
    {
        if (!isset($this->advanceDirectiveService)) {
            $this->advanceDirectiveService = new PatientAdvanceDirectiveService();
        }
        return $this->advanceDirectiveService;
    }

    /**
     * Set the PatientAdvanceDirectiveService (for dependency injection/testing)
     */
    public function setAdvanceDirectiveService(PatientAdvanceDirectiveService $service): void
    {
        $this->advanceDirectiveService = $service;
    }

    /**
     * Search for advance directive observations
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $observationCodesToReturn = [];

            // Validate category if provided
            if (isset($openEMRSearchParameters['category']) && $openEMRSearchParameters['category'] instanceof TokenSearchField) {
                if (!$openEMRSearchParameters['category']->hasCodeValue(self::CATEGORY_ASSESSMENT)) {
                    throw new SearchFieldException("category", "invalid value - only 'assessment' category is supported");
                }
                unset($openEMRSearchParameters['category']);
            }

            // Process code parameter to filter specific advance directive types
            if (isset($openEMRSearchParameters['code'])) {
                /**
                 * @var TokenSearchField
                 */
                $code = $openEMRSearchParameters['code'];
                if (!($code instanceof TokenSearchField)) {
                    throw new SearchFieldException('code', "Invalid code");
                }
                foreach ($code->getValues() as $value) {
                    $codeValue = $value->getCode();
                    if ($this->supportsCode($codeValue)) {
                        $observationCodesToReturn[$codeValue] = $codeValue;
                    }
                }
                unset($openEMRSearchParameters['code']);
            }

            // If no specific codes requested, return all supported codes
            if (empty($observationCodesToReturn)) {
                $observationCodesToReturn = array_keys(self::COLUMN_MAPPINGS);
                $observationCodesToReturn = array_combine($observationCodesToReturn, $observationCodesToReturn);
            }

            // Extract patient UUID from search parameters
            // The search field contains ReferenceSearchValue objects with the UUID
            $patientUuidBytes = null;
            if (isset($openEMRSearchParameters['puuid'])) {
                $puuidField = $openEMRSearchParameters['puuid'];

                // Extract the UUID bytes from the search field
                if (is_object($puuidField) && method_exists($puuidField, 'getValues')) {
                    $values = $puuidField->getValues();
                    if (!empty($values)) {
                        // Get first value from the array
                        $firstValue = is_array($values) ? reset($values) : $values;

                        // Extract the UUID using the proper getter method
                        if (is_object($firstValue)) {
                            // ReferenceSearchValue objects typically have getId() or getReference() methods
                            if (method_exists($firstValue, 'getId')) {
                                $patientUuidBytes = $firstValue->getId();
                            } elseif (method_exists($firstValue, 'getReference')) {
                                $patientUuidBytes = $firstValue->getReference();
                            } elseif (method_exists($firstValue, 'getValue')) {
                                $patientUuidBytes = $firstValue->getValue();
                            }
                        } else {
                            // It's already a raw value (string/binary)
                            $patientUuidBytes = $firstValue;
                        }
                    }
                } elseif (is_string($puuidField)) {
                    // If it's already a string, convert to bytes if needed
                    if (strlen($puuidField) === 16) {
                        $patientUuidBytes = $puuidField;
                    } else {
                        // Convert string UUID to bytes
                        $patientUuidBytes = UuidRegistry::uuidToBytes($puuidField);
                    }
                }
            }

            if (!$patientUuidBytes) {
                $this->getSystemLogger()->debug(
                    "FhirObservationAdvanceDirectiveService->searchForOpenEMRRecords() - No patient UUID found in search parameters"
                );
                return $processingResult;
            }

            // Validate it's the right length (16 bytes for binary UUID)
            if (!is_string($patientUuidBytes) || strlen($patientUuidBytes) !== 16) {
                $this->getSystemLogger()->error(
                    "FhirObservationAdvanceDirectiveService->searchForOpenEMRRecords() - Invalid UUID format",
                    [
                        'type' => gettype($patientUuidBytes),
                        'length' => is_string($patientUuidBytes) ? strlen($patientUuidBytes) : 'N/A',
                        'hex' => is_string($patientUuidBytes) ? bin2hex($patientUuidBytes) : 'N/A'
                    ]
                );
                return $processingResult;
            }

            // Query patient_data to get PID using binary UUID
            $sql = "SELECT pid FROM patient_data WHERE uuid = ?";
            $result = sqlQuery($sql, [$patientUuidBytes]);

            if (empty($result['pid'])) {
                $this->getSystemLogger()->debug(
                    "FhirObservationAdvanceDirectiveService->searchForOpenEMRRecords() - Patient not found for UUID",
                    ['uuid_hex' => bin2hex($patientUuidBytes)]
                );
                return $processingResult;
            }

            $pid = (int)$result['pid'];

            // Get advance directive data using the service
            $advanceDirectiveService = $this->getAdvanceDirectiveService();
            $adiData = $advanceDirectiveService->getPatientAdvanceDirectives($pid, [
                'include_observations' => true,
                'include_documents' => true
            ]);

            // Convert patient UUID bytes to string for observation records
            $patientUuidString = UuidRegistry::uuidToString($patientUuidBytes);

            // Transform observations to OpenEMR record format
            foreach ($adiData['observations'] as $observation) {
                $code = $observation['code'];

                // Skip if this code is not in our requested codes
                if (!in_array($code, $observationCodesToReturn)) {
                    continue;
                }

                $mapping = self::COLUMN_MAPPINGS[$code] ?? null;
                if (!isset($mapping)) {
                    continue;
                }

                // For ADI observations, we use the document UUID directly
                // Each document creates ONE observation, so there's a 1:1 relationship
                // This is simpler than the patient_data pattern where one record creates multiple observations
                $documentUuid = $observation['document_uuid'];
                $observationUuid = $documentUuid; // Use document UUID as observation UUID

                // Get profile URIs for this observation
                $profileVersions = $mapping['profiles'] ?? [self::USCDI_PROFILE_ADI_DOCUMENTATION_URI => ['8.0.0']];
                $profiles = [];
                foreach ($profileVersions as $profile => $versions) {
                    $profiles[] = $this->getProfileForVersions($profile, $versions);
                }
                $profiles = array_merge(...$profiles);

                // Build the observation record
                // For coded values (like LA33-6), we need to structure them properly
                // Also provide explicit code coding to ensure correct display
                $record = [
                    "code" => $mapping['fullcode'],
                    "description" => $mapping['description'],
                    // Add explicit code coding to override trait's code lookup
                    "code_coding" => [
                        [
                            'system' => 'http://loinc.org',
                            'code' => $mapping['code'],
                            'display' => $mapping['description']
                        ]
                    ],
                    "ob_type" => self::CATEGORY_ASSESSMENT,
                    "ob_status" => 'final', // ADI observations are always final
                    "puuid" => $patientUuidString,
                    "uuid" => $observationUuid,
                    "date" => $observation['effective_date'],
                    "last_modified" => $observation['provenance']['time'] ?? $observation['effective_date'],
                    "profiles" => $profiles,
                    // Value as CodeableConcept - format: "system:code"
                    "value" => $observation['value_system'] . ":" . $observation['value_code'],
                    "value_code_description" => $observation['value_display'], // "Yes"
                    // Add focus element for DocumentReference link
                    "focus" => [
                        [
                            'reference' => 'DocumentReference/' . $observation['document_uuid']
                        ]
                    ],
                    // DocumentReference details
                    "document_id" => $observation['document_id'],
                    "document_uuid" => $observation['document_uuid'],
                    "document_location" => $observation['document_location'],
                    "document_name" => $observation['document_name'],
                    // Author/provenance
                    "author_id" => $observation['provenance']['author_id'] ?? null,
                ];

                $processingResult->addData($record);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        } catch (\Exception $exception) {
            $this->getSystemLogger()->error(
                "FhirObservationAdvanceDirectiveService->searchForOpenEMRRecords() exception",
                ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            $processingResult->addInternalError("Error retrieving advance directive observations");
        }

        return $processingResult;
    }

    /**
     * Get supported US Core versions for this service
     */
    public function getSupportedVersions(): array
    {
        return ['8.0.0']; // US Core 8.0 introduced ADI observations
    }

    /**
     * Get profile URIs supported by this service
     */
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(
            self::USCDI_PROFILE_ADI_DOCUMENTATION_URI,
            $this->getSupportedVersions()
        );
    }

    /**
     * Override setObservationCode to use pre-defined code_coding array for advance directives
     * This ensures we use the correct LOINC display text from COLUMN_MAPPINGS instead of
     * relying on code table lookups which may return incorrect or generic descriptions
     *
     * @param FHIRObservation $observation
     * @param array $dataRecord
     * @return void
     */
    protected function setObservationCode(FHIRObservation $observation, array $dataRecord): void
    {
        if (empty($dataRecord['code'])) {
            throw new \InvalidArgumentException("Code is required for observation");
        }

        // If code_coding array is provided (which we do for advance directives), use it directly
        // This allows us to control the exact display text instead of relying on code table lookups
        if (!empty($dataRecord['code_coding']) && is_array($dataRecord['code_coding'])) {
            $codeableConcept = new FHIRCodeableConcept();

            foreach ($dataRecord['code_coding'] as $codingData) {
                $coding = new FHIRCoding();

                if (!empty($codingData['system'])) {
                    $coding->setSystem(new FHIRUri($codingData['system']));
                }

                if (!empty($codingData['code'])) {
                    $coding->setCode(new FHIRCode($codingData['code']));
                }

                if (!empty($codingData['display'])) {
                    $coding->setDisplay(new FHIRString($codingData['display']));
                }

                $codeableConcept->addCoding($coding);
            }

            $observation->setCode($codeableConcept);
        }
    }
}
