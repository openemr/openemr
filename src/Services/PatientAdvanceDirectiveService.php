<?php

/**
 * PatientAdvanceDirectiveService.php
 *
 * @package    OpenEMR
 * @link       https://www.open-emr.org
 * @author   Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Common\Logging\SystemLogger;

class PatientAdvanceDirectiveService
{
    const ADVANCE_DIRECTIVE_TYPES = [
        'living_will' => 'Living Will',
        'durable_power_attorney' => 'Durable Power of Attorney',
        'dnr_order' => 'Do Not Resuscitate Order'
    ];

    const LOINC_CODES = [
        'Living Will' => [
            'code' => '75320-2',
            'system' => '2.16.840.1.113883.6.1',
            'display' => 'Advance directive - living will'
        ],
        'Durable Power of Attorney' => [
            'code' => '75787-2',
            'system' => '2.16.840.1.113883.6.1',
            'display' => 'Advance directive - medical power of attorney'
        ],
        'Do Not Resuscitate Order' => [
            'code' => '78823-2',
            'system' => '2.16.840.1.113883.6.1',
            'display' => 'Do not resuscitate order'
        ],
        'default' => [
            'code' => '42348-3',
            'system' => '2.16.840.1.113883.6.1',
            'display' => 'Advance directive'
        ]
    ];

    /**
     * Get all advance directive documents and observations for a patient
     *
     * @param int $pid Patient ID
     * @param array $options Optional filters and settings
     * @return array Array of advance directive data
     */
    public function getPatientAdvanceDirectives($pid, $options = []): array
    {
        $includeObservations = $options['include_observations'] ?? true;
        $includeDocuments = $options['include_documents'] ?? true;
        $documentTypes = $options['document_types'] ?? null; // Filter by specific types

        $result = [
            'documents' => [],
            'observations' => []
        ];

        if ($includeDocuments) {
            $result['documents'] = $this->getAdvanceDirectiveDocuments($pid, $documentTypes);
        }

        if ($includeObservations) {
            $result['observations'] = $this->generateObservationsFromDocuments($result['documents']);
        }

        return $result;
    }

    /**
     * Get advance directive documents from the documents table
     *
     * @param int $pid Patient ID
     * @param array|null $documentTypes Filter by specific document types
     * @return array Array of document data
     */
    public function getAdvanceDirectiveDocuments($pid, $documentTypes = null)
    {
        $sql = "SELECT d.id, d.uuid, d.name, d.docdate, d.date, d.mimetype, d.url, d.hash,
                       d.owner, d.revision, d.encounter_id, d.foreign_reference_id,
                       c.name as category_name, c.id as category_id,
                       u.fname, u.lname, u.npi, u.id as owner_user_id
                FROM documents d
                LEFT JOIN categories_to_documents c2d ON d.id = c2d.document_id  
                LEFT JOIN categories c ON c2d.category_id = c.id
                LEFT JOIN users u ON d.owner = u.id
                WHERE d.foreign_id = ? 
                AND d.deleted = 0
                AND (LOWER(d.name) LIKE '%living%will%' 
                     OR LOWER(d.name) LIKE '%durable%power%attorney%'
                     OR LOWER(d.name) LIKE '%do%not%resuscitate%'
                     OR LOWER(c.name) LIKE '%do%not%resuscitate%'
                     OR LOWER(c.name) LIKE '%living%will%'
                     OR LOWER(c.name) LIKE '%power%attorney%')
                ORDER BY d.docdate DESC, d.date DESC";

        $documents = [];
        $result = sqlStatement($sql, [$pid]);

        while ($row = sqlFetchArray($result)) {
            $docType = $this->determineAdvanceDirectiveType($row['name'], $row['category_name']);

            // Filter by document types if specified
            if ($documentTypes && !in_array($docType, $documentTypes)) {
                continue;
            }

            $document = [
                'id' => (int)$row['id'],
                'uuid' => UuidRegistry::uuidToString($row['uuid']),
                'name' => $row['name'],
                'type' => $docType,
                'status' => $this->determineDocumentStatus($row),
                'effective_date' => $row['docdate'] ?: substr((string) $row['date'], 0, 10),
                'location' => $row['url'] ?: 'Electronic Health Record',
                'mimetype' => $row['mimetype'],
                'hash' => $row['hash'],
                'category_name' => $row['category_name'],
                'category_id' => $row['category_id'],
                'encounter_id' => $row['encounter_id'],
                'foreign_reference_id' => $row['foreign_reference_id'],
                'created_date' => $row['date'],
                'last_modified' => $row['revision'],
                'author' => [
                    'user_id' => $row['owner_user_id'],
                    'first_name' => $row['fname'],
                    'last_name' => $row['lname'],
                    'npi' => $row['npi']
                ],
                'provenance' => [
                    'author_id' => $row['owner_user_id'],
                    'time' => $row['revision']
                ]
            ];

            $documents[] = $document;
        }

        return $documents;
    }

    /**
     * Generate observations from advance directive documents for USCDI v5 compliance
     *
     * @param array $documents Array of document data
     * @return array Array of observation data
     */
    public function generateObservationsFromDocuments($documents): array
    {
        $observations = [];

        foreach ($documents as $document) {
            $loincCode = $this->getLoincCodeForDocumentType($document['type']);

            $observation = [
                'id' => $document['id'] . '_obs',
                'document_id' => $document['id'],
                'document_uuid' => $document['uuid'],
                'type' => 'advance_directive_observation',
                'code' => $loincCode['code'],
                'code_system' => $loincCode['system'],
                'code_display' => $loincCode['display'],
                'value_code' => 'LA33-6', // "Yes" - document exists
                'value_display' => 'Yes',
                'value_system' => '2.16.840.1.113883.6.1',
                'status' => 'completed',
                'effective_date' => $document['effective_date'],
                'document_reference' => $document['uuid'],
                'document_location' => $document['location'],
                'document_type' => $document['type'],
                'document_name' => $document['name'],
                'provenance' => $document['provenance']
            ];

            $observations[] = $observation;
        }

        return $observations;
    }

    /**
     * Get advance directive summary for a patient
     *
     * @param int $pid Patient ID
     * @return array Summary of advance directives
     */
    public function getAdvanceDirectiveSummary($pid): array
    {
        $data = $this->getPatientAdvanceDirectives($pid);

        $summary = [
            'patient_id' => $pid,
            'total_documents' => count($data['documents']),
            'total_observations' => count($data['observations']),
            'document_types' => [],
            'has_living_will' => false,
            'has_power_of_attorney' => false,
            'has_dnr_order' => false,
            'most_recent_date' => null
        ];

        $dates = [];
        foreach ($data['documents'] as $doc) {
            $summary['document_types'][] = $doc['type'];
            $dates[] = $doc['effective_date'];

            switch ($doc['type']) {
                case 'Living Will':
                    $summary['has_living_will'] = true;
                    break;
                case 'Durable Power of Attorney':
                    $summary['has_power_of_attorney'] = true;
                    break;
                case 'Do Not Resuscitate Order':
                    $summary['has_dnr_order'] = true;
                    break;
            }
        }

        $summary['document_types'] = array_unique($summary['document_types']);
        $summary['most_recent_date'] = $dates ? max($dates) : null;

        return $summary;
    }

    /**
     * Check if patient has specific advance directive type
     *
     * @param int $pid Patient ID
     * @param string $type Document type to check
     * @return bool
     */
    public function hasAdvanceDirectiveType($pid, $type): bool
    {
        $documents = $this->getAdvanceDirectiveDocuments($pid, [$type]);
        return !empty($documents);
    }

    /**
     * Determine advance directive type from document name and category
     *
     * @param string $name Document name
     * @param string $category Document category
     * @return string Document type
     */
    private function determineAdvanceDirectiveType($name, $category): string
    {
        $name_lower = strtolower($name);
        $category_lower = strtolower($category);

        // Check document name first
        if (str_contains($name_lower, 'living') && str_contains($name_lower, 'will')) {
            return 'Living Will';
        }
        if (str_contains($name_lower, 'durable') && str_contains($name_lower, 'power')) {
            return 'Durable Power of Attorney';
        }
        if (str_contains($name_lower, 'dnr') || str_contains($name_lower, 'do not resuscitate')) {
            return 'Do Not Resuscitate Order';
        }

        // Check category if name doesn't match
        if (str_contains($category_lower, 'living will')) {
            return 'Living Will';
        }
        if (str_contains($category_lower, 'durable power of attorney')) {
            return 'Durable Power of Attorney';
        }
        if (str_contains($category_lower, 'do not resuscitate')) {
            return 'Do Not Resuscitate Order';
        }

        return 'Advance Directive';
    }

    /**
     * Determine document status based on dates and other factors
     *
     * @param array $document Document data from database
     * @return string Status (active, expired, superseded)
     */
    private function determineDocumentStatus($document): string
    {
        // For now, return active - could be enhanced to check expiration dates
        // or compare with newer versions of the same document type
        return 'active';
    }

    /**
     * Get LOINC code for document type
     *
     * @param string $docType Document type
     * @return array LOINC code information
     */
    private function getLoincCodeForDocumentType($docType)
    {
        $codes = [
            'Living Will' => [
                'code' => '75320-2',
                'system' => '2.16.840.1.113883.6.1',
                'display' => 'Advance directive - living will'
            ],
            'Durable Power of Attorney' => [
                'code' => '75787-2',
                'system' => '2.16.840.1.113883.6.1',
                'display' => 'Advance directive - medical power of attorney'
            ],
            'Do Not Resuscitate Order' => [
                'code' => '304251008',
                'system' => '2.16.840.1.113883.6.96',
                'display' => 'Resuscitation status'
            ]
        ];

        return $codes[$docType] ?? [
            'code' => '42348-3',
            'system' => '2.16.840.1.113883.6.1',
            'display' => 'Advance directive'
        ];
    }

    /**
     * Validate advance directive data structure
     *
     * @param array $data Advance directive data
     * @return array Validation results
     */
    public function validateAdvanceDirectiveData($data)
    {
        $errors = [];
        $warnings = [];

        if (empty($data['documents']) && empty($data['observations'])) {
            $warnings[] = 'No advance directive documents or observations found';
        }

        foreach ($data['documents'] ?? [] as $index => $doc) {
            if (empty($doc['uuid'])) {
                $errors[] = "Document at index $index missing UUID";
            }
            if (empty($doc['type'])) {
                $errors[] = "Document at index $index missing type";
            }
            if (empty($doc['effective_date'])) {
                $warnings[] = "Document at index $index missing effective date";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Convert advance directive data to FHIR DocumentReference resources
     *
     * @param int   $pid     Patient ID
     * @param array $options Optional filters and settings
     * @return array Array of FHIR DocumentReference resources
     */
    public function getFhirDocumentReferences($pid, $options = []): array
    {
        $advanceDirectives = $this->getPatientAdvanceDirectives($pid, $options);
        $fhirResources = [];

        foreach ($advanceDirectives['documents'] as $document) {
            $fhirResources[] = $this->convertDocumentToFhirDocumentReference($document, $pid);
        }

        return $fhirResources;
    }

    /**
     * Convert advance directive observations to FHIR Observation resources
     *
     * @param int   $pid     Patient ID
     * @param array $options Optional filters and settings
     * @return array Array of FHIR Observation resources
     */
    public function getFhirObservations($pid, $options = []): array
    {
        $advanceDirectives = $this->getPatientAdvanceDirectives($pid, $options);
        $fhirResources = [];

        foreach ($advanceDirectives['observations'] as $observation) {
            $fhirResources[] = $this->convertObservationToFhirObservation($observation, $pid);
        }

        return $fhirResources;
    }

    /**
     * Get FHIR Bundle containing all advance directive resources for a patient
     *
     * @param int   $pid     Patient ID
     * @param array $options Optional filters and settings
     * @return array FHIR Bundle resource
     */
    public function getFhirBundle($pid, $options = []): array
    {
        $documentReferences = $this->getFhirDocumentReferences($pid, $options);
        $observations = $this->getFhirObservations($pid, $options);

        $entries = [];

        // Add DocumentReference entries
        foreach ($documentReferences as $docRef) {
            $entries[] = [
                'fullUrl' => "DocumentReference/" . $docRef['id'],
                'resource' => $docRef,
                'search' => [
                    'mode' => 'match'
                ]
            ];
        }

        // Add Observation entries
        foreach ($observations as $obs) {
            $entries[] = [
                'fullUrl' => "Observation/" . $obs['id'],
                'resource' => $obs,
                'search' => [
                    'mode' => 'match'
                ]
            ];
        }

        return [
            'resourceType' => 'Bundle',
            'id' => 'advance-directives-' . $pid,
            'type' => 'searchset',
            'total' => count($entries),
            'entry' => $entries
        ];
    }

    /**
     * Convert document data to FHIR DocumentReference resource
     *
     * @param array $document Document data from CCDA
     * @param int   $pid      Patient ID
     * @return array FHIR DocumentReference resource
     */
    private function convertDocumentToFhirDocumentReference($document, $pid): array
    {
        $loincCode = $this->getLoincCodeForDocumentType($document['type']);

        $fhirResource = [
            'resourceType' => 'DocumentReference',
            'id' => 'doc-' . $document['id'],
            'meta' => [
                'profile' => ['http://hl7.org/fhir/us/core/StructureDefinition/us-core-documentreference']
            ],
            'identifier' => [
                [
                    'system' => 'urn:ietf:rfc:3986',
                    'value' => 'urn:uuid:' . $document['uuid']
                ]
            ],
            'status' => $this->mapDocumentStatusToFhir($document['status']),
            'docStatus' => 'final',
            'type' => [
                'coding' => [
                    [
                        'system' => $loincCode['system'],
                        'code' => $loincCode['code'],
                        'display' => $loincCode['display']
                    ]
                ],
                'text' => $document['type']
            ],
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category',
                            'code' => 'clinical-note',
                            'display' => 'Clinical Note'
                        ]
                    ]
                ]
            ],
            'subject' => [
                'reference' => "Patient/$pid"
            ],
            'date' => $this->formatFhirDateTime($document['effective_date']),
            'author' => [
                [
                    'reference' => "Practitioner/" . ($document['author']['user_id'] ?? 'unknown'),
                    'display' => trim(($document['author']['first_name'] ?? '') . ' ' . ($document['author']['last_name'] ?? ''))
                ]
            ],
            'custodian' => [
                'reference' => "Organization/1", // Default organization reference
                'display' => "Healthcare Organization"
            ],
            'description' => $document['name'],
            'content' => [
                [
                    'attachment' => [
                        'contentType' => $document['mimetype'] ?? 'application/pdf',
                        'url' => $document['location'],
                        'title' => $document['name'],
                        'creation' => $this->formatFhirDateTime($document['created_date'])
                    ]
                ]
            ],
            'context' => [
                'period' => [
                    'start' => $this->formatFhirDateTime($document['effective_date'])
                ],
                'facilityType' => [
                    'coding' => [
                        [
                            'system' => 'http://snomed.info/sct',
                            'code' => '257622000',
                            'display' => 'Healthcare facility'
                        ]
                    ]
                ]
            ]
        ];

        // Add encounter reference if available
        if (!empty($document['encounter_id'])) {
            $fhirResource['context']['encounter'] = [
                'reference' => "Encounter/" . $document['encounter_id']
            ];
        }

        return $fhirResource;
    }

    /**
     * Convert observation data to FHIR Observation resource
     *
     * @param array $observation Observation data from CCDA
     * @param int   $pid         Patient ID
     * @return array FHIR Observation resource
     */
    private function convertObservationToFhirObservation($observation, $pid): array
    {
        return [
            'resourceType' => 'Observation',
            'id' => 'obs-' . $observation['id'],
            'meta' => [
                'profile' => ['http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-clinical-result']
            ],
            'identifier' => [
                [
                    'system' => 'urn:ietf:rfc:3986',
                    'value' => 'urn:uuid:' . ($observation['document_uuid'] ?? $observation['id'])
                ]
            ],
            'status' => $observation['status'] ?? 'final',
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                            'code' => 'survey',
                            'display' => 'Survey'
                        ]
                    ]
                ]
            ],
            'code' => [
                'coding' => [
                    [
                        'system' => $observation['code_system'],
                        'code' => $observation['code'],
                        'display' => $observation['code_display']
                    ]
                ],
                'text' => $observation['document_type']
            ],
            'subject' => [
                'reference' => "Patient/$pid"
            ],
            'effectiveDateTime' => $this->formatFhirDateTime($observation['effective_date']),
            'valueCodeableConcept' => [
                'coding' => [
                    [
                        'system' => $observation['value_system'],
                        'code' => $observation['value_code'],
                        'display' => $observation['value_display']
                    ]
                ],
                'text' => $observation['value_display']
            ],
            'derivedFrom' => [
                [
                    'reference' => "DocumentReference/doc-" . $observation['document_id'],
                    'display' => $observation['document_name']
                ]
            ]
        ];
    }

    /**
     * Get FHIR search parameters for advance directives
     *
     * @return array Array of supported search parameters
     */
    public function getFhirSearchParameters(): array
    {
        return [
            'patient' => [
                'type' => 'reference',
                'description' => 'Who/what is the subject of the document'
            ],
            'type' => [
                'type' => 'token',
                'description' => 'Kind of document (LOINC if possible)'
            ],
            'category' => [
                'type' => 'token',
                'description' => 'Categorization of document'
            ],
            'status' => [
                'type' => 'token',
                'description' => 'current | superseded | entered-in-error'
            ],
            'date' => [
                'type' => 'date',
                'description' => 'When this document reference was created'
            ],
            'author' => [
                'type' => 'reference',
                'description' => 'Who and/or what authored the document'
            ],
            'identifier' => [
                'type' => 'token',
                'description' => 'Master Version Specific Identifier'
            ]
        ];
    }

    /**
     * Search for advance directive FHIR resources with parameters
     *
     * @param array $searchParams FHIR search parameters
     * @return array FHIR search results
     */
    public function searchFhirResources($searchParams): array
    {
        $options = [];

        // Convert FHIR search params to internal options
        if (isset($searchParams['patient'])) {
            $pid = $this->extractPatientIdFromReference($searchParams['patient']);
        } else {
            throw new \InvalidArgumentException('Patient parameter is required');
        }

        if (isset($searchParams['type'])) {
            $options['document_types'] = $this->mapFhirTypeToDocumentType($searchParams['type']);
        }

        if (isset($searchParams['status'])) {
            $options['status_filter'] = $searchParams['status'];
        }

        if (isset($searchParams['date'])) {
            $options['date_filter'] = $searchParams['date'];
        }

        return $this->getFhirBundle($pid, $options);
    }

    /**
     * Map document status to FHIR DocumentReference status
     *
     * @param string $status Internal status
     * @return string FHIR status
     */
    private function mapDocumentStatusToFhir($status): string
    {
        $statusMap = [
            'active' => 'current',
            'expired' => 'superseded',
            'superseded' => 'superseded',
            'inactive' => 'superseded'
        ];

        return $statusMap[$status] ?? 'current';
    }

    /**
     * Format date/datetime for FHIR
     *
     * @param string $date Date string
     * @return string FHIR formatted date
     */
    private function formatFhirDateTime($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format('Y-m-d\TH:i:s\Z');
        } catch (\Exception) {
            // If it's just a date, format as date only
            try {
                $dateTime = new \DateTime($date);
                return $dateTime->format('Y-m-d');
            } catch (\Exception) {
                return $date; // Return as-is if can't parse
            }
        }
    }

    /**
     * Extract patient ID from FHIR reference
     *
     * @param string $reference FHIR patient reference
     * @return int Patient ID
     */
    private function extractPatientIdFromReference($reference): int
    {
        if (preg_match('/Patient\/(\d+)/', $reference, $matches)) {
            return (int)$matches[1];
        }

        throw new \InvalidArgumentException('Invalid patient reference format');
    }

    /**
     * Map FHIR type code to internal document type
     *
     * @param string $fhirType FHIR type code
     * @return array Array of document types
     */
    private function mapFhirTypeToDocumentType($fhirType): array
    {
        $typeMap = [
            '75320-2' => ['Living Will'],
            '75787-2' => ['Durable Power of Attorney'],
            '78823-2' => ['Do Not Resuscitate Order'],
            '42348-3' => ['Advance Directive']
        ];

        return $typeMap[$fhirType] ?? [];
    }

    /**
     * Validate FHIR resource structure
     *
     * @param array  $resource     FHIR resource
     * @param string $resourceType Expected resource type
     * @return array Validation results
     */
    public function validateFhirResource($resource, $resourceType): array
    {
        $errors = [];
        $warnings = [];

        if (!isset($resource['resourceType']) || $resource['resourceType'] !== $resourceType) {
            $errors[] = "Invalid or missing resourceType. Expected: $resourceType";
        }

        if (!isset($resource['id'])) {
            $errors[] = "Missing required 'id' field";
        }

        if ($resourceType === 'DocumentReference') {
            if (!isset($resource['status'])) {
                $errors[] = "Missing required 'status' field";
            }
            if (!isset($resource['content']) || empty($resource['content'])) {
                $errors[] = "Missing required 'content' field";
            }
            if (!isset($resource['subject'])) {
                $errors[] = "Missing required 'subject' field";
            }
        }

        if ($resourceType === 'Observation') {
            if (!isset($resource['status'])) {
                $errors[] = "Missing required 'status' field";
            }
            if (!isset($resource['code'])) {
                $errors[] = "Missing required 'code' field";
            }
            if (!isset($resource['subject'])) {
                $errors[] = "Missing required 'subject' field";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Get FHIR CapabilityStatement for advance directives
     *
     * @return array FHIR CapabilityStatement snippet
     */
    public function getFhirCapabilityStatement(): array
    {
        return [
            'resource' => [
                [
                    'type' => 'DocumentReference',
                    'profile' => 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-documentreference',
                    'interaction' => [
                        ['code' => 'read'],
                        ['code' => 'search-type']
                    ],
                    'searchParam' => [
                        ['name' => 'patient', 'type' => 'reference'],
                        ['name' => 'type', 'type' => 'token'],
                        ['name' => 'category', 'type' => 'token'],
                        ['name' => 'status', 'type' => 'token'],
                        ['name' => 'date', 'type' => 'date']
                    ]
                ],
                [
                    'type' => 'Observation',
                    'profile' => 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-clinical-result',
                    'interaction' => [
                        ['code' => 'read'],
                        ['code' => 'search-type']
                    ],
                    'searchParam' => [
                        ['name' => 'patient', 'type' => 'reference'],
                        ['name' => 'code', 'type' => 'token'],
                        ['name' => 'category', 'type' => 'token'],
                        ['name' => 'status', 'type' => 'token'],
                        ['name' => 'date', 'type' => 'date']
                    ]
                ]
            ]
        ];
    }
}
