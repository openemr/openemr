<?php

/**
 * CcdaDataTransformer.php - Data Transformation for CCDA Generation
 *
 * This class replaces the populate*() functions from serveccda.js,
 * transforming the raw XML data from EncounterccdadispatchTable into
 * the structured format required by CCDA templates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder;

use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Utils\DateFormatter;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Utils\CodeCleaner;

class CcdaDataTransformer
{
    // Global context variables (mirrors serveccda.js globals)
    private array $all = [];
    private string $oidFacility = '';
    private string $npiProvider = '';
    private string $npiFacility = '';
    private string $webRoot = '';
    private string $authorDateTime = '';
    private string $documentLocation = '';

    /**
     * Route code mapping (from serveccda.js mapRouteCode)
     */
    private const ROUTE_MAP = [
        'PO' => 'C38288',
        'ORAL' => 'C38288',
        'IV' => 'C38276',
        'IM' => 'C28161',
        'SC' => 'C38299',
        'SUBCUT' => 'C38299',
        'SQ' => 'C38299',
        'TOP' => 'C38304',
        'TOPICAL' => 'C38304',
        'INH' => 'C38216',
        'NASAL' => 'C38284',
        'OPTH' => 'C38287',
        'OTIC' => 'C38192',
        'RECTAL' => 'C38295',
        'VAGINAL' => 'C38313',
        'SL' => 'C38300',
        'BUCCAL' => 'C38193',
        'TD' => 'C38305',
    ];

    /**
     * Main transform method - converts raw CCDA data to template format
     *
     * @param array $pd The parsed CCDA data array
     * @return array Transformed data ready for template engine
     */
    public function transform(array $pd): array
    {
        // Initialize global context
        $this->initializeContext($pd);

        $doc = [];
        $data = [];

        // Demographics (required for all documents)
        $data['demographics'] = $this->populateDemographics($pd);

        // Providers
        if (!empty($pd['primary_care_provider'])) {
            $providers = $this->populateProviders();
            $data['demographics'] = array_merge($data['demographics'], $providers);
        }

        // Process each section based on available data
        $data['allergies'] = $this->processSection($pd, 'allergies', 'allergy', 'populateAllergy');
        $data['medications'] = $this->processSection($pd, 'medications', 'medication', 'populateMedication');
        $data['problems'] = $this->processSection($pd, 'problem_lists', 'problem', 'populateProblem');
        $data['procedures'] = $this->processSection($pd, 'procedures', 'procedure', 'populateProcedure');
        // NOTE: Node.js doesn't have a 'results' section - lab results may be in other sections
        // $data['results'] = $this->processSection($pd, 'results', 'result', 'populateResult');
        $data['vitals'] = $this->processSection($pd, 'vitals', 'vital', 'populateVital');
        $data['immunizations'] = $this->processSection($pd, 'immunizations', 'immunization', 'populateImmunization');
        $data['encounters'] = $this->processSection($pd, 'encounter_list', 'encounter', 'populateEncounter');
        $data['plan_of_care'] = $this->processSection($pd, 'planofcare', 'item', 'populatePlanOfCare');
        $data['goals'] = $this->processSection($pd, 'goals', 'goal', 'populateGoal');
        $data['health_concerns'] = $this->processSection($pd, 'health_concerns', 'concern', 'populateHealthConcern');
        $data['medical_devices'] = $this->processSection($pd, 'medical_devices', 'device', 'populateMedicalDevice');

        // Social History
        if (!empty($pd['history_physical'])) {
            $data['social_history'] = $this->populateSocialHistory($pd['history_physical']);
        }

        // Care Team
        if (($pd['care_team']['is_active'] ?? '') === 'active') {
            $data['care_team'] = $this->populateCareTeamMembers($pd);
        }

        // Payers
        if (!empty($pd['payers']) && is_array($pd['payers'])) {
            $data['payers'] = $this->populatePayer($pd['payers']);
        }

        // Advance Directives
        if (!empty($pd['advance_directives']['directive'])) {
            $data['advance_directives'] = $this->processAdvanceDirectives($pd['advance_directives']['directive']);
        }

        // Clinical Notes sections
        $noteSections = [
            'progress_note', 'hospital_course', 'discharge_summary',
            'discharge_diagnosis', 'discharge_medications', 'complications',
            'postprocedure_diagnosis', 'postoperative_diagnosis', 'preoperative_diagnosis',
            'procedure_description', 'procedure_indications', 'anesthesia',
            'estimated_blood_loss', 'procedure_findings', 'procedure_specimens',
            'assessment_plan', 'chief_complaint', 'physical_exam',
            'review_of_systems', 'general_status', 'history_past_illness'
        ];

        foreach ($noteSections as $noteSection) {
            if (!empty($pd[$noteSection])) {
                $data[$noteSection] = $this->populateNote($pd[$noteSection]);
            }
        }

        // Assemble document
        $doc['data'] = $data;
        $doc['meta'] = $this->getMeta($pd);
        $doc['meta']['ccda_header'] = $this->populateHeader($pd);

        // Apply timezone
        if (!empty($pd['timezone_local_offset'])) {
            $this->applyTimezones($doc, $pd['timezone_local_offset']);
        }

        return $doc;
    }

    /**
     * Transform data for unstructured document
     */
    public function transformUnstructured(array $pd): array
    {
        $this->initializeContext($pd);
        $pd['doc_type'] = 'unstructured';

        $doc = [];
        $data = [];

        // Only demographics needed for unstructured
        $data['demographics'] = $this->populateDemographics($pd);

        if (!empty($pd['primary_care_provider'])) {
            $providers = $this->populateProviders();
            $data['demographics'] = array_merge($data['demographics'], $providers);
        }

        $doc['data'] = $data;
        $doc['meta'] = $this->getMeta($pd);
        $doc['meta']['ccda_header'] = $this->populateHeader($pd);

        if (!empty($pd['timezone_local_offset'])) {
            $this->applyTimezones($doc, $pd['timezone_local_offset']);
        }

        return $doc;
    }

    /**
     * Initialize context variables from input data
     */
    private function initializeContext(array $pd): void
    {
        $this->all = $pd;

        $primaryProvider = $pd['primary_care_provider']['provider'] ?? [];
        $this->npiProvider = $primaryProvider['npi'] ?? 'NI';

        $encounterProvider = $pd['encounter_provider'] ?? [];
        $this->oidFacility = $encounterProvider['facility_oid'] ?? '2.16.840.1.113883.19.5.99999.1';
        $this->npiFacility = $this->getNpiFacility($pd);

        $this->webRoot = $pd['serverRoot'] ?? '';
        $this->documentLocation = $pd['document_location'] ?? '';

        // Determine author datetime
        $this->authorDateTime = $pd['created_time_timezone'] ?? '';
        if (!empty($pd['author']['time']) && strlen((string) $pd['author']['time']) > 7) {
            $this->authorDateTime = $pd['author']['time'];
        } elseif (!empty($pd['encounter_list']['encounter'])) {
            $encounters = $pd['encounter_list']['encounter'];
            $this->authorDateTime = isset($encounters[0]) ? $encounters[0]['date'] ?? '' : $encounters['date'] ?? '';
        }
        $this->authorDateTime = DateFormatter::fDate($this->authorDateTime);
    }

    /**
     * Get facility NPI
     */
    private function getNpiFacility(array $pd, bool $returnNi = false): string
    {
        $npi = $pd['encounter_provider']['facility_npi'] ?? '';
        if (empty($npi)) {
            $npi = $pd['primary_care_provider']['provider']['facility_npi'] ?? '';
        }
        if (empty($npi) && $returnNi) {
            return 'NI';
        }
        return $npi;
    }

    /**
     * Process a section with multiple items
     * Handles various data structures from OpenEMR/serveccda.js
     */
    private function processSection(array $pd, string $sectionKey, string $itemKey, string $populateMethod): array
    {
        $result = [];

        // Check if section exists at all
        if (empty($pd[$sectionKey])) {
            return $result;
        }

        // Get the section data
        $sectionData = $pd[$sectionKey];
        
        // Handle nested structure: $pd['medications']['medication'][]
        if (isset($sectionData[$itemKey])) {
            $items = $sectionData[$itemKey];
        } elseif (is_array($sectionData) && isset($sectionData[0])) {
            // Already an array at top level
            $items = $sectionData;
        } else {
            // Single object or empty
            $items = $sectionData;
        }

        // Ensure we have something to process
        if (empty($items)) {
            return $result;
        }

        // Handle single item vs array
        if (!isset($items[0]) && is_array($items)) {
            $items = [$items];
        }

        foreach ($items as $item) {
            if (method_exists($this, $populateMethod)) {
                $populated = $this->$populateMethod($item);
                if (!empty($populated)) {
                    $result[] = $populated;
                }
            }
        }

        return $result;
    }

    /**
     * Count entities (single object vs array)
     */
    private function countEntities($data): int
    {
        if (empty($data)) {
            return 0;
        }
        if (is_array($data) && isset($data[0])) {
            return count($data);
        }
        if (is_array($data) || is_object($data)) {
            return 1;
        }
        return 0;
    }

    /**
     * Safe get - get nested array value with default
     */
    private function safeGet(array $arr, string $path, $default = ''): mixed
    {
        $keys = explode('.', $path);
        $value = $arr;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Map route code to NCI Thesaurus code
     */
    private function mapRouteCode(?string $routeCode): string
    {
        if (empty($routeCode)) {
            return '';
        }

        $cleaned = CodeCleaner::clean($routeCode);

        // Already a valid NCI code
        if (preg_match('/^C\d+$/', $cleaned)) {
            return $cleaned;
        }

        $upper = strtoupper($cleaned);
        return self::ROUTE_MAP[$upper] ?? $cleaned;
    }

    // =========================================================================
    // Population Methods (mirrors serveccda.js)
    // =========================================================================

    /**
     * Populate demographics data - exactly matches populate-demographics.js
     */
    private function populateDemographics(array $pd): array
    {
        $patient = $pd['patient'] ?? $pd;
        $guardian = $pd['guardian'] ?? [];
        $encounterProvider = $pd['encounter_provider'] ?? [];

        // Apply null flavor for unspecified values
        $race = $patient['race'] ?? '';
        $raceGroup = $patient['race_group'] ?? '';
        $ethnicity = $patient['ethnicity'] ?? '';
        
        if ($race === 'declined_to_specify' || $race === '') {
            $race = 'null_flavor';
        }
        if ($raceGroup === 'declined_to_specify' || $raceGroup === '') {
            $raceGroup = 'null_flavor';
        }
        if ($ethnicity === 'declined_to_specify' || $ethnicity === '') {
            $ethnicity = 'null_flavor';
        }

        return [
            'name' => [
                'prefix' => $patient['prefix'] ?? '',
                'suffix' => $patient['suffix'] ?? '',
                'middle' => [$patient['mname'] ?? ''],
                'last' => $patient['lname'] ?? '',
                'first' => $patient['fname'] ?? '',
            ],
            'birth_name' => [
                'middle' => $patient['birth_mname'] ?? '',
                'last' => $patient['birth_lname'] ?? '',
                'first' => $patient['birth_fname'] ?? '',
            ],
            'dob' => [
                'point' => [
                    'date' => DateFormatter::fDate($patient['dob'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'gender' => strtoupper($patient['gender'] ?? '') ?: 'null_flavor',
            'identifiers' => [
                [
                    'identifier' => $this->oidFacility ?: $this->npiFacility,
                    'extension' => $patient['uuid'] ?? '',
                ],
            ],
            'marital_status' => strtoupper($patient['status'] ?? ''),
            'addresses' => $this->fetchPreviousAddresses($patient),
            'phone' => [
                ['number' => $patient['phone_home'] ?? '', 'type' => 'primary home'],
                ['number' => $patient['phone_mobile'] ?? '', 'type' => 'primary mobile'],
                ['number' => $patient['phone_work'] ?? '', 'type' => 'work place'],
                ['number' => $patient['phone_emergency'] ?? '', 'type' => 'emergency contact'],
                ['email' => $patient['email'] ?? '', 'type' => 'contact_email'],
            ],
            'ethnicity' => $ethnicity,
            'race' => $race ?: 'null_flavor',
            'race_additional' => $raceGroup ?: 'null_flavor',
            'languages' => [
                [
                    'language' => $this->getLanguageCode($patient),
                    'preferred' => true,
                    'mode' => 'Expressed spoken',
                    'proficiency' => 'Good',
                ],
            ],
            'attributed_provider' => [
                'identity' => [
                    [
                        'root' => '2.16.840.1.113883.4.6',
                        'extension' => $this->npiFacility ?: '',
                    ],
                ],
                'phone' => [
                    ['number' => $encounterProvider['facility_phone'] ?? ''],
                ],
                'name' => [
                    ['full' => $encounterProvider['facility_name'] ?? ''],
                ],
                'address' => [
                    [
                        'street_lines' => [$encounterProvider['facility_street'] ?? ''],
                        'city' => $encounterProvider['facility_city'] ?? '',
                        'state' => $encounterProvider['facility_state'] ?? '',
                        'zip' => $encounterProvider['facility_postal_code'] ?? '',
                        'country' => $encounterProvider['facility_country_code'] ?? 'US',
                        'use' => 'work place',
                    ],
                ],
            ],
            'guardians' => $this->getGuardianInfo($guardian),
        ];
    }

    /**
     * Get language code - matches populate-demographics.js
     */
    private function getLanguageCode(array $patient): string
    {
        $lang = $patient['language'] ?? '';
        return match ($lang) {
            'English' => 'en-US',
            'Spanish' => 'sp-US',
            default => $patient['language_code'] ?? 'en-US',
        };
    }

    /**
     * Fetch previous addresses - matches previous-addresses.js
     */
    private function fetchPreviousAddresses(array $patient): array
    {
        $addresses = [];

        // Build street lines helper
        $buildStreetLines = function($streets) {
            if (!is_array($streets)) {
                return [$streets];
            }
            $streetLines = [$streets[0] ?? ''];
            if (!empty($streets[1])) {
                $streetLines[] = $streets[1];
            }
            return $streetLines;
        };

        // Current address
        $addresses[] = [
            'use' => 'HP',
            'street_lines' => $buildStreetLines($patient['street'] ?? ''),
            'city' => $patient['city'] ?? '',
            'state' => $patient['state'] ?? '',
            'zip' => $patient['postalCode'] ?? '',
            'country' => $patient['country'] ?? 'US',
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate(''),
                    'precision' => 'day',
                ],
            ],
        ];

        // Previous addresses
        $prevAddresses = $patient['previous_addresses']['address'] ?? null;
        if ($prevAddresses) {
            if (!isset($prevAddresses[0])) {
                $prevAddresses = [$prevAddresses];
            }
            foreach ($prevAddresses as $addr) {
                $addresses[] = [
                    'use' => $addr['use'] ?? 'BAD',
                    'street_lines' => $buildStreetLines($addr['street'] ?? ''),
                    'city' => $addr['city'] ?? '',
                    'state' => $addr['state'] ?? '',
                    'zip' => $addr['postalCode'] ?? '',
                    'country' => $addr['country'] ?? 'US',
                    'date_time' => [
                        'low' => [
                            'date' => DateFormatter::fDate($addr['period_start'] ?? ''),
                            'precision' => 'day',
                        ],
                        'high' => [
                            'date' => DateFormatter::fDate($addr['period_end'] ?? '') ?: DateFormatter::fDate(''),
                            'precision' => 'day',
                        ],
                    ],
                ];
            }
        }

        return $addresses;
    }

    /**
     * Get guardian info - matches populate-demographics.js
     */
    private function getGuardianInfo(array $guardian): array
    {
        if (empty($guardian['display_name'])) {
            return [];
        }

        // Parse display name into first/last
        $parts = explode(' ', (string) $guardian['display_name']);
        $names = count($parts) === 3
            ? [['first' => $parts[0], 'last' => $parts[2]]]
            : (count($parts) === 2
                ? [['first' => $parts[0], 'last' => $parts[1]]]
                : [['first' => 'Not Informed', 'last' => 'Not Informed']]);

        return [[
            'relation' => $guardian['relation'] ?? '',
            'addresses' => [[
                'street_lines' => [$guardian['address'] ?? $guardian['street'] ?? ''],
                'city' => $guardian['city'] ?? '',
                'state' => $guardian['state'] ?? '',
                'zip' => $guardian['postalCode'] ?? '',
                'country' => $guardian['country'] ?? 'US',
                'use' => 'primary home',
            ]],
            'names' => $names,
            'phone' => [['number' => $guardian['telecom'] ?? '', 'type' => 'primary home']],
        ]];
    }

    /**
     * Populate provider - matches providers.js populateProvider()
     */
    private function populateProvider(array $provider): array
    {
        return [
            'function_code' => !empty($provider['physician_type']) ? 'PP' : '',
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($provider['provider_since'] ?? ''),
                    'precision' => 'tz',
                ],
            ],
            'identity' => [
                [
                    'root' => !empty($provider['npi']) ? '2.16.840.1.113883.4.6' : $this->oidFacility,
                    'extension' => $provider['npi'] ?? $provider['table_id'] ?? 'NI',
                ],
            ],
            'type' => [
                [
                    'name' => $provider['taxonomy_description'] ?? '',
                    'code' => CodeCleaner::clean($provider['taxonomy'] ?? ''),
                    'code_system' => '2.16.840.1.113883.6.101',
                    'code_system_name' => 'NUCC Health Care Provider Taxonomy',
                ],
            ],
            'name' => [
                [
                    'last' => $provider['lname'] ?? '',
                    'first' => $provider['fname'] ?? '',
                ],
            ],
            'address' => [
                [
                    'street_lines' => [$this->all['encounter_provider']['facility_street'] ?? ''],
                    'city' => $this->all['encounter_provider']['facility_city'] ?? '',
                    'state' => $this->all['encounter_provider']['facility_state'] ?? '',
                    'zip' => $this->all['encounter_provider']['facility_postal_code'] ?? '',
                    'country' => $this->all['encounter_provider']['facility_country_code'] ?? 'US',
                ],
            ],
            'phone' => [
                ['number' => $this->all['encounter_provider']['facility_phone'] ?? ''],
            ],
        ];
    }

    /**
     * Populate previous names
     */
    private function populatePreviousNames($names): array
    {
        if (empty($names) || !is_array($names)) {
            return [];
        }

        // Ensure array
        if (!isset($names[0])) {
            $names = [$names];
        }

        $result = [];
        foreach ($names as $name) {
            $result[] = [
                'first' => $name['previous_name_first'] ?? '',
                'middle' => $name['previous_name_middle'] ?? '',
                'last' => $name['previous_name_last'] ?? $name['formatted_name'] ?? '',
                'prefix' => $name['previous_name_prefix'] ?? '',
                'suffix' => $name['previous_name_suffix'] ?? '',
            ];
        }

        return $result;
    }

    /**
     * Populate phone numbers (legacy - keeping for compatibility)
     */
    private function populatePhones(array $pd): array
    {
        $phones = [];

        if (!empty($pd['phone_home'])) {
            $phones[] = [
                'number' => $pd['phone_home'],
                'type' => 'primary home',
            ];
        }
        if (!empty($pd['phone_mobile'])) {
            $phones[] = [
                'number' => $pd['phone_mobile'],
                'type' => 'mobile contact',
            ];
        }
        if (!empty($pd['phone_contact'])) {
            $phones[] = [
                'number' => $pd['phone_contact'],
                'type' => 'contact',
            ];
        }
        if (!empty($pd['email'])) {
            $phones[] = [
                'email' => $pd['email'],
                'type' => 'contact',
            ];
        }

        return $phones;
    }

    /**
     * Populate medication data
     */
    private function populateMedication(array $pd): array
    {
        $pd['status'] = 'Completed'; // @todo handle prescribed status

        $author = $pd['author'] ?? [];

        return [
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($pd['start_date'] ?? ''),
                    'precision' => 'day',
                ],
                'high' => [
                    'date' => DateFormatter::fDate($pd['end_date'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? '',
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'status' => $pd['status'],
            'sig' => $pd['direction'] ?? '',
            'product' => [
                'identifiers' => [
                    [
                        'identifier' => $pd['sha_extension'] ?? '2a620155-9d11-439e-92b3-5d9815ff4ee8',
                        'extension' => !empty($pd['extension']) ? $pd['extension'] . '_1' : '',
                    ],
                ],
                'unencoded_name' => $pd['drug'] ?? '',
                'product' => [
                    'name' => $pd['drug'] ?? '',
                    'code' => CodeCleaner::clean($pd['rxnorm'] ?? ''),
                    'code_system_name' => 'RXNORM',
                ],
            ],
            'author' => $this->buildAuthorBlock($author),
            'administration' => [
                'route' => [
                    'name' => $pd['route'] ?? '',
                    'code' => $this->mapRouteCode($pd['route_code'] ?? ''),
                    'code_system_name' => 'Medication Route FDA',
                ],
                'form' => [
                    'name' => $pd['form'] ?? '',
                    'code' => CodeCleaner::clean($pd['form_code'] ?? ''),
                    'code_system_name' => 'Medication Route FDA',
                ],
                'dose' => [
                    'value' => !empty($pd['size']) ? (float)$pd['size'] : null,
                    'unit' => $pd['unit'] ?? '',
                ],
                'interval' => [
                    'period' => [
                        'value' => !empty($pd['dosage']) ? (float)$pd['dosage'] : null,
                        'unit' => $pd['interval'] ?? null,
                    ],
                    'frequency' => true,
                ],
            ],
        ];
    }

    /**
     * Populate allergy data
     */
    private function populateAllergy(array $pd): array
    {
        $author = $pd['author'] ?? [];

        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? '',
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($pd['begdate'] ?? ''),
                    'precision' => 'day',
                ],
                'high' => [
                    'date' => DateFormatter::fDate($pd['enddate'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'observation' => [
                'identifiers' => [
                    [
                        'identifier' => $pd['sha_extension'] ?? '',
                        'extension' => !empty($pd['extension']) ? $pd['extension'] . '_1' : '',
                    ],
                ],
                // intolerance is used for the <value> element
                'intolerance' => [
                    'name' => $pd['intolerance_title'] ?? $pd['type_title'] ?? '',
                    'code' => CodeCleaner::clean($pd['intolerance_code'] ?? $pd['type_code'] ?? ''),
                    'code_system' => '2.16.840.1.113883.6.96',
                    'code_system_name' => 'SNOMED CT',
                ],
                // allergen is used for the participant/playingEntity
                'allergen' => [
                    'name' => $pd['title'] ?? '',
                    'code' => CodeCleaner::clean($pd['rxnorm_drugcode'] ?? $pd['snomed_code'] ?? ''),
                    'code_system_name' => $pd['code_type'] ?? 'RXNORM',
                ],
                'status' => [
                    'name' => $pd['status'] ?? '',
                    'code' => $pd['status_code'] ?? '',
                ],
                'severity' => [
                    'code' => [
                        'name' => $pd['severity_al'] ?? '',
                        'code' => $pd['severity_al_code'] ?? '',
                        'code_system_name' => 'SNOMED CT',
                    ],
                ],
                'reactions' => [
                    [
                        'reaction' => [
                            'name' => $pd['reaction'] ?? '',
                            'code' => CodeCleaner::clean($pd['reaction_code'] ?? ''),
                            'code_system_name' => 'SNOMED CT',
                        ],
                    ],
                ],
            ],
            'author' => $this->buildAuthorBlock($author),
        ];
    }

    /**
     * Populate problem data
     */
    private function populateProblem(array $pd): array
    {
        $author = $pd['author'] ?? [];

        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? '',
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($pd['begdate'] ?? ''),
                    'precision' => 'day',
                ],
                'high' => [
                    'date' => DateFormatter::fDate($pd['enddate'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'problem' => [
                'identifiers' => [
                    [
                        'identifier' => $pd['sha_extension'] ?? '',
                        'extension' => !empty($pd['extension']) ? $pd['extension'] . '_1' : '',
                    ],
                ],
                'code' => [
                    'name' => $pd['title'] ?? '',
                    'code' => CodeCleaner::clean($pd['code'] ?? ''),
                    'code_system_name' => $pd['code_type'] ?? 'SNOMED CT',
                ],
                'status' => [
                    'name' => $pd['status'] ?? '',
                    'code' => $pd['status_code'] ?? '',
                ],
            ],
            'author' => $this->buildAuthorBlock($author),
        ];
    }

    /**
     * Build author block (shared structure)
     */
    private function buildAuthorBlock(array $author): array
    {
        return [
            'code' => [
                'name' => $author['physician_type'] ?? '',
                'code' => $author['physician_type_code'] ?? '',
                'code_system' => $author['physician_type_system'] ?? '',
                'code_system_name' => $author['physician_type_system_name'] ?? '',
            ],
            'date_time' => [
                'point' => [
                    'date' => DateFormatter::fDate($author['time'] ?? '') ?: DateFormatter::fDate(''),
                    'precision' => 'tz',
                ],
            ],
            'identifiers' => [
                [
                    'identifier' => !empty($author['npi']) ? '2.16.840.1.113883.4.6' : ($author['id'] ?? ''),
                    'extension' => !empty($author['npi']) ? $author['npi'] : 'NI',
                ],
            ],
            'address' => [
                'street_lines' => [$author['streetAddressLine'] ?? ''],
                'city' => $author['city'] ?? '',
                'state' => $author['state'] ?? '',
                'zip' => $author['postalCode'] ?? '',
                'country' => $author['country'] ?? 'US',
                'use' => 'WP',
            ],
            'phone' => [
                'value' => 'tel:' . ($author['telecom'] ?? ''),
                'use' => 'HP',
            ],
            'name' => [
                'last' => $author['lname'] ?? '',
                'first' => $author['fname'] ?? '',
            ],
            'organization' => [
                'identity' => [
                    'root' => $author['facility_oid'] ?? '2.16.840.1.113883.4.6',
                    'extension' => $author['facility_npi'] ?? 'NI',
                ],
                'name' => [$author['facility_name'] ?? ''],
            ],
        ];
    }

    /**
     * Populate providers
     */
    private function populateProviders(): array
    {
        $providerArray = [];

        // Primary provider
        if (!empty($this->all['primary_care_provider']['provider'])) {
            $providerArray[] = $this->populateProvider($this->all['primary_care_provider']['provider']);
        }

        // Care team providers
        $careTeam = $this->all['care_team'] ?? [];
        $providers = $careTeam['provider'] ?? [];

        if (!empty($providers)) {
            if (!isset($providers[0])) {
                $providers = [$providers];
            }
            foreach ($providers as $provider) {
                $providerArray[] = $this->populateProvider($provider);
            }
        }

        $primaryDiagnosis = $this->all['primary_diagnosis'] ?? [];

        return [
            'providers' => [
                'date_time' => [
                    'low' => [
                        'date' => DateFormatter::fDate($this->all['time_start'] ?? ''),
                        'precision' => 'tz',
                    ],
                    'high' => [
                        'date' => DateFormatter::fDate($this->all['time_end'] ?? ''),
                        'precision' => 'tz',
                    ],
                ],
                'code' => [
                    'name' => $primaryDiagnosis['text'] ?? '',
                    'code' => CodeCleaner::clean($primaryDiagnosis['code'] ?? ''),
                    'code_system_name' => $primaryDiagnosis['code_type'] ?? '',
                ],
                'provider' => $providerArray,
            ],
        ];
    }

    /**
     * Populate header data
     */
    private function populateHeader(array $pd): array
    {
        $encounterProvider = $pd['encounter_provider'] ?? [];
        $author = $pd['author'] ?? [];
        $custodian = $pd['custodian'] ?? [];

        return [
            'identifiers' => [
                [
                    'identifier' => $pd['document_uuid'] ?? ($this->oidFacility . '.' . time()),
                    'extension' => $pd['doc_extension'] ?? 'OE-DOC-0001',
                ],
            ],
            'code' => [
                'name' => $pd['doc_code_name'] ?? 'Continuity of Care Document',
                'code' => $pd['doc_code'] ?? '34133-9',
                'code_system_name' => 'LOINC',
            ],
            'template' => [
                'root' => $this->getDocumentTemplateId($pd['doc_type'] ?? 'ccd'),
                'extension' => '2015-08-01',
            ],
            'title' => $pd['doc_title'] ?? 'Continuity of Care Document',
            'date_time' => [
                'point' => [
                    'date' => $this->authorDateTime ?: DateFormatter::fDate(''),
                    'precision' => 'tz',
                ],
            ],
            'author' => $this->buildAuthorBlock($author),
            'custodian' => [
                'identity' => [
                    'root' => $this->oidFacility ?: '2.16.840.1.113883.4.6',
                    'extension' => $this->npiFacility,
                ],
                'name' => [$encounterProvider['facility_name'] ?? $custodian['name'] ?? ''],
                'address' => [
                    'street_lines' => [$encounterProvider['facility_street'] ?? $custodian['streetAddressLine'] ?? ''],
                    'city' => $encounterProvider['facility_city'] ?? $custodian['city'] ?? '',
                    'state' => $encounterProvider['facility_state'] ?? $custodian['state'] ?? '',
                    'zip' => $encounterProvider['facility_postal_code'] ?? $custodian['postalCode'] ?? '',
                    'country' => $encounterProvider['facility_country_code'] ?? $custodian['country'] ?? 'US',
                    'use' => 'work place',
                ],
                'phone' => [
                    'value' => 'tel:' . ($encounterProvider['facility_phone'] ?? $custodian['telecom'] ?? ''),
                    'use' => 'WP',
                ],
            ],
            'informant' => $this->buildInformant($pd),
            'information_recipient' => $this->buildInformationRecipient($pd),
            'component_of' => $this->buildComponentOf($pd),
        ];
    }

    /**
     * Get document template ID based on doc type
     */
    private function getDocumentTemplateId(string $docType): string
    {
        return match ($docType) {
            'referral' => '2.16.840.1.113883.10.20.22.1.14',
            'ccd' => '2.16.840.1.113883.10.20.22.1.2',
            'unstructured' => '2.16.840.1.113883.10.20.22.1.10',
            default => '2.16.840.1.113883.10.20.22.1.2',
        };
    }

    /**
     * Build informant block
     */
    private function buildInformant(array $pd): ?array
    {
        $informer = $pd['informer'] ?? null;
        if (empty($informer)) {
            return null;
        }

        return [
            'identifiers' => [['identifier' => $this->oidFacility]],
            'name' => [
                'organization' => $informer['organization'] ?? '',
            ],
        ];
    }

    /**
     * Build information recipient block
     */
    private function buildInformationRecipient(array $pd): ?array
    {
        $recipient = $pd['information_recipient'] ?? null;
        if (empty($recipient) || (empty($recipient['fname']) && empty($recipient['lname']))) {
            return null;
        }

        return [
            'name' => [
                'first' => $recipient['fname'] ?? '',
                'last' => $recipient['lname'] ?? '',
                'prefix' => $recipient['prefix'] ?? '',
                'suffix' => $recipient['suffix'] ?? '',
            ],
            'organization' => $recipient['organization'] ?? '',
        ];
    }

    /**
     * Build componentOf (encompassingEncounter) block
     */
    private function buildComponentOf(array $pd): ?array
    {
        $primaryDiagnosis = $pd['primary_diagnosis'] ?? [];
        $primaryProvider = $pd['primary_care_provider']['provider'] ?? [];

        if (empty($primaryDiagnosis) && empty($primaryProvider)) {
            return null;
        }

        return [
            'identifiers' => [
                [
                    'identifier' => $this->oidFacility,
                    'extension' => 'PT-' . ($pd['patient']['id'] ?? ''),
                ],
            ],
            'code' => [
                'name' => $primaryDiagnosis['text'] ?? '',
                'code' => $primaryDiagnosis['code'] ?? '',
                'code_system_name' => $primaryDiagnosis['code_type'] ?? '',
            ],
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($primaryDiagnosis['encounter_date'] ?? ''),
                    'precision' => 'tz',
                ],
                'high' => [
                    'date' => DateFormatter::fDate($primaryDiagnosis['encounter_end_date'] ?? ''),
                    'precision' => 'tz',
                ],
            ],
            'responsible_party' => [
                'root' => $this->oidFacility,
                'name' => [
                    'last' => $pd['author']['lname'] ?? '',
                    'first' => $pd['author']['fname'] ?? '',
                ],
            ],
            'encounter_participant' => [
                'root' => $this->oidFacility,
                'name' => [
                    'last' => $primaryProvider['lname'] ?? '',
                    'first' => $primaryProvider['fname'] ?? '',
                ],
                'address' => [
                    'street_lines' => [$pd['encounter_provider']['facility_street'] ?? ''],
                    'city' => $pd['encounter_provider']['facility_city'] ?? '',
                    'state' => $pd['encounter_provider']['facility_state'] ?? '',
                    'zip' => $pd['encounter_provider']['facility_postal_code'] ?? '',
                    'country' => $pd['encounter_provider']['facility_country_code'] ?? 'US',
                    'use' => 'work place',
                ],
                'phone' => [[
                    'value' => 'tel:' . ($pd['encounter_provider']['facility_phone'] ?? ''),
                    'use' => 'WP',
                ]],
            ],
        ];
    }

    /**
     * Get document metadata
     */
    private function getMeta(array $pd): array
    {
        return [
            'type' => $pd['doc_type'] ?? 'ccd',
            'identifiers' => [
                [
                    'identifier' => $pd['document_uuid'] ?? $this->oidFacility,
                    'extension' => $pd['doc_extension'] ?? '',
                ],
            ],
        ];
    }

    /**
     * Apply timezone offsets
     */
    private function applyTimezones(array &$doc, string $offset): void
    {
        // This would recursively apply timezone offset to all date fields
        // Implementation depends on how dates are stored in the structure
    }

    /**
     * Populate procedure data - matches serveccda.js populateProcedure()
     */
    private function populateProcedure(array $pd): array
    {
        $author = $pd['author'] ?? [];
        
        return [
            'procedure' => [
                'name' => $pd['code_text'] ?? $pd['description'] ?? '',
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'code_system' => $pd['code_type'] === 'SNOMED-CT' ? '2.16.840.1.113883.6.96' : 
                    ($pd['code_type'] === 'CPT4' ? '2.16.840.1.113883.6.12' : ''),
                'code_system_name' => $pd['code_type'] ?? 'CPT4',
                'translations' => !empty($pd['code2']) ? [
                    [
                        'code' => CodeCleaner::clean($pd['code2']),
                        'code_system_name' => $pd['code_type2'] ?? '',
                    ]
                ] : [],
            ],
            'identifiers' => [
                [
                    'identifier' => 'd68b7e32-7810-4f5b-9cc2-acd54b0fd85d',
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'status' => 'completed',
            'date_time' => [
                'point' => [
                    'date' => DateFormatter::fDate($pd['date'] ?? $pd['encounter'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'performers' => [  // FIXED: Changed from 'performer' to 'performers' (plural)
                [
                    'identifiers' => [
                        [
                            'identifier' => '2.16.840.1.113883.4.6',
                            'extension' => $pd['npi'] ?? $this->npiProvider,
                        ],
                    ],
                    'name' => [
                        [
                            'last' => $pd['provider_lname'] ?? '',
                            'first' => $pd['provider_fname'] ?? '',
                        ],
                    ],
                    'address' => [
                        [
                            'street_lines' => [$pd['address'] ?? ''],
                            'city' => $pd['city'] ?? '',
                            'state' => $pd['state'] ?? '',
                            'zip' => $pd['zip'] ?? '',
                            'country' => 'US',
                        ],
                    ],
                    'phone' => [
                        [
                            'number' => $pd['work_phone'] ?? '',
                            'type' => 'work place',
                        ],
                    ],
                    'organization' => [
                        [
                            'identifiers' => [
                                [
                                    'identifier' => $pd['facility_sha_extension'] ?? '',
                                    'extension' => $pd['facility_extension'] ?? '',
                                ],
                            ],
                            'name' => [$pd['facility_name'] ?? $this->all['encounter_provider']['facility_name'] ?? ''],
                            'address' => [
                                [
                                    'street_lines' => [$pd['facility_address'] ?? ''],
                                    'city' => $pd['facility_city'] ?? '',
                                    'state' => $pd['facility_state'] ?? '',
                                    'zip' => $pd['facility_zip'] ?? '',
                                    'country' => $pd['facility_country'] ?? 'US',
                                ],
                            ],
                            'phone' => [
                                [
                                    'number' => $pd['facility_phone'] ?? '',
                                    'type' => 'work place',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'author' => $this->buildAuthorBlock($author),
            'procedure_type' => 'procedure',  // FIXED: Added required field for template existsWhen condition
        ];
    }

    /**
     * Populate result data - matches serveccda.js populateResult()
     */
    private function populateResult(array $pd): array
    {
        // Results come as sets with individual results inside
        $results = [];
        $resultList = $pd['result'] ?? [];
        if (!isset($resultList[0])) {
            $resultList = [$resultList];
        }
        
        foreach ($resultList as $result) {
            $results[] = [
                'identifiers' => [
                    [
                        'identifier' => $result['sha_extension'] ?? $this->oidFacility,
                        'extension' => $result['extension'] ?? '',
                    ],
                ],
                'result' => [
                    'name' => $result['result_text'] ?? $result['title'] ?? '',
                    'code' => CodeCleaner::clean($result['result_code'] ?? $result['code'] ?? ''),
                    'code_system' => '2.16.840.1.113883.6.1',
                    'code_system_name' => 'LOINC',
                ],
                'date_time' => [
                    'point' => [
                        'date' => DateFormatter::fDate($result['result_date'] ?? $result['date'] ?? ''),
                        'precision' => 'tz',
                    ],
                ],
                'status' => 'completed',
                'value' => $result['result_value'] ?? '',
                'unit' => $result['result_unit'] ?? '',
                'reference_range' => [
                    'low' => $result['range_low'] ?? '',
                    'high' => $result['range_high'] ?? '',
                    'text' => $result['range'] ?? '',
                ],
                'interpretation' => [
                    'code' => $result['abnormal_flag'] ?? '',
                    'name' => $result['abnormal_flag'] === 'H' ? 'High' : 
                        ($result['abnormal_flag'] === 'L' ? 'Low' : 'Normal'),
                ],
            ];
        }

        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'result_set' => [
                'name' => $pd['title'] ?? $pd['result_text'] ?? '',
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'code_system' => '2.16.840.1.113883.6.1',
                'code_system_name' => 'LOINC',
            ],
            'date_time' => [
                'point' => [
                    'date' => DateFormatter::fDate($pd['date'] ?? ''),
                    'precision' => 'tz',
                ],
            ],
            'status' => 'completed',
            'results' => $results,
        ];
    }

    /**
     * Populate vital signs data
     */
    private function populateVital(array $pd): array
    {
        $vitalList = [];
        
        // Map common vital signs
        $vitalsMap = [
            'bps' => ['code' => '8480-6', 'name' => 'Systolic Blood Pressure', 'unit' => 'mm[Hg]'],
            'bpd' => ['code' => '8462-4', 'name' => 'Diastolic Blood Pressure', 'unit' => 'mm[Hg]'],
            'pulse' => ['code' => '8867-4', 'name' => 'Heart Rate', 'unit' => '/min'],
            'temperature' => ['code' => '8310-5', 'name' => 'Body Temperature', 'unit' => 'Cel'],
            'respiration' => ['code' => '9279-1', 'name' => 'Respiratory Rate', 'unit' => '/min'],
            'height' => ['code' => '8302-2', 'name' => 'Body Height', 'unit' => 'cm'],
            'weight' => ['code' => '29463-7', 'name' => 'Body Weight', 'unit' => 'kg'],
            'BMI' => ['code' => '39156-5', 'name' => 'Body Mass Index', 'unit' => 'kg/m2'],
            'oxygen_saturation' => ['code' => '2708-6', 'name' => 'Oxygen Saturation', 'unit' => '%'],
            'head_circ' => ['code' => '9843-4', 'name' => 'Head Circumference', 'unit' => 'cm'],
        ];

        foreach ($vitalsMap as $key => $info) {
            if (!empty($pd[$key])) {
                $vitalList[] = [
                    'identifiers' => [
                        [
                            'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                            'extension' => $pd['extension_' . $key] ?? $pd['extension'] ?? '',
                        ],
                    ],
                    'vital' => [
                        'name' => $info['name'],
                        'code' => $info['code'],
                        'code_system_name' => 'LOINC',
                    ],
                    'status' => 'completed',
                    'date_time' => [
                        'point' => [
                            'date' => DateFormatter::fDate($pd['effectivetime'] ?? $pd['date'] ?? ''),
                            'precision' => 'day',
                        ],
                    ],
                    'interpretations' => ['Normal'],  // ADDED: Required by template
                    'value' => floatval($pd[$key]),
                    'unit' => $pd[$key . '_unit'] ?? $info['unit'],
                    'author' => $this->buildAuthorBlock($pd['author'] ?? []),  // ADDED: Required by template
                ];
            }
        }

        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'status' => 'completed',
            'date_time' => [
                'point' => [
                    'date' => DateFormatter::fDate($pd['effectivetime'] ?? $pd['date'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'vital_list' => $vitalList,  // Already correct!
        ];
    }

    /**
     * Populate immunization data
     */
    private function populateImmunization(array $pd): array
    {
        $author = $pd['author'] ?? [];
        
        return [
            'date_time' => [
                'low' => [  // FIXED: Changed from 'point' to 'low' to match template expectation
                    'date' => DateFormatter::fDate($pd['administered_date'] ?? $pd['administered_on'] ?? $pd['date'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'status' => !empty($pd['completion_status']) ? $pd['completion_status'] : 'complete',
            'product' => [
                'product' => [  // FIXED: Added nested product structure as expected by template
                    'name' => $pd['cvx_code_text'] ?? $pd['code_text'] ?? $pd['title'] ?? '',
                    'code' => CodeCleaner::clean($pd['cvx_code'] ?? ''),
                    'code_system_name' => 'CVX',
                    'lot_number' => '',
                ],
                'lot_number' => $pd['lot_number'] ?? '',
                'manufacturer' => $pd['manufacturer'] ?? '',
            ],
            'administration' => [
                'route' => [
                    'name' => $pd['route'] ?? $pd['route_of_administration'] ?? '',
                    'code' => $this->mapRouteCode($pd['route_code'] ?? ''),
                    'code_system_name' => 'Medication Route FDA',
                ],
            ],
            'performer' => [
                'identifiers' => [
                    [
                        'identifier' => '2.16.840.1.113883.4.6',
                        'extension' => $pd['npi'] ?? $this->npiProvider,
                    ],
                ],
                'name' => [
                    [
                        'last' => $pd['provider_lname'] ?? $pd['lname'] ?? '',
                        'first' => $pd['provider_fname'] ?? $pd['fname'] ?? '',
                    ],
                ],
                'address' => [
                    [
                        'street_lines' => [$pd['address'] ?? ''],
                        'city' => $pd['city'] ?? '',
                        'state' => $pd['state'] ?? '',
                        'zip' => $pd['zip'] ?? '',
                        'country' => 'US',
                    ],
                ],
                'organization' => [
                    [
                        'identifiers' => [
                            [
                                'identifier' => '2.16.840.1.113883.4.6',
                                'extension' => $this->npiFacility,
                            ],
                        ],
                        'name' => [$pd['facility_name'] ?? ''],
                    ],
                ],
            ],
            'instructions' => [
                'code' => [
                    'name' => 'immunization education',
                    'code' => '171044003',
                    'code_system_name' => 'SNOMED CT',
                ],
                'free_text' => 'Needs Attention for more data.',
            ],
            'author' => $this->buildAuthorBlock($author),
        ];
    }

    /**
     * Populate encounter data
     */
    private function populateEncounter(array $pd): array
    {
        $author = $pd['author'] ?? [];
        
        // Process findings/diagnoses
        $findings = [];
        if (!empty($pd['encounter_diagnosis'])) {
            $diagnoses = $pd['encounter_diagnosis'];
            if (!isset($diagnoses[0])) {
                $diagnoses = [$diagnoses];
            }
            foreach ($diagnoses as $dx) {
                $findings[] = [
                    'value' => [
                        'name' => $dx['diagnosis'] ?? $dx['title'] ?? '',
                        'code' => CodeCleaner::clean($dx['code'] ?? ''),
                        'code_system' => $dx['code_type'] === 'SNOMED-CT' ? '2.16.840.1.113883.6.96' : '2.16.840.1.113883.6.103',
                        'code_system_name' => $dx['code_type'] ?? 'ICD-10-CM',
                    ],
                ];
            }
        }

        // ADDED: Process locations (required by template)
        $locations = [];
        if (!empty($pd['facility_name'])) {
            $locations[] = [
                'name' => $pd['facility_name'],
                'location_type' => [
                    'name' => 'General Acute Care Hospital',
                    'code' => '1118-9',
                    'code_system_name' => 'HealthcareServiceLocation',
                ],
                'address' => [
                    [
                        'street_lines' => [$pd['facility_address'] ?? ''],
                        'city' => $pd['facility_city'] ?? '',
                        'state' => $pd['facility_state'] ?? '',
                        'zip' => $pd['facility_zip'] ?? '',
                        'country' => 'US',
                    ],
                ],
                'telecom' => [
                    [
                        'number' => $pd['facility_phone'] ?? '',
                        'type' => 'work place',
                    ],
                ],
            ];
        }

        return [
            'encounter' => [
                'name' => $pd['pc_catname'] ?? $pd['code_text'] ?? '',
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'code_system' => '2.16.840.1.113883.6.12',
                'code_system_name' => 'CPT4',
                'translations' => [],
            ],
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($pd['date'] ?? ''),
                    'precision' => 'tz',
                ],
                'high' => [
                    'date' => DateFormatter::fDate($pd['date_end'] ?? $pd['date'] ?? ''),
                    'precision' => 'tz',
                ],
            ],
            'performers' => [  // FIXED: Changed from 'performer' to 'performers' (plural)
                [
                    'code' => [
                        'name' => $pd['provider_specialty'] ?? '',
                        'code' => $pd['provider_taxonomy'] ?? '',
                        'code_system' => '2.16.840.1.113883.6.101',
                        'code_system_name' => 'NUCC Health Care Provider Taxonomy',
                    ],
                    'identifiers' => [
                        [
                            'identifier' => '2.16.840.1.113883.4.6',
                            'extension' => $pd['npi'] ?? $this->npiProvider,
                        ],
                    ],
                    'name' => [
                        [
                            'last' => $pd['provider_lname'] ?? '',
                            'first' => $pd['provider_fname'] ?? '',
                        ],
                    ],
                ],
            ],
            'locations' => $locations,  // ADDED: Required by template
            'findings' => $findings,
        ];
    }

    /**
     * Populate plan of care data
     */
    private function populatePlanOfCare(array $pd): array
    {
        $author = $pd['author'] ?? [];
        
        // Process performers
        $performers = [];
        if (!empty($pd['provider'])) {
            $performers[] = [
                'identifiers' => [
                    [
                        'identifier' => '2.16.840.1.113883.4.6',
                        'extension' => $pd['provider']['npi'] ?? $this->npiProvider,
                    ],
                ],
                'code' => [
                    [
                        'name' => $pd['provider']['specialty'] ?? 'General physician',
                        'code' => $pd['provider']['specialty_code'] ?? '59058001',
                        'code_system_name' => 'SNOMED CT',
                    ],
                ],
                'name' => [
                    [
                        'last' => $pd['provider']['lname'] ?? '',
                        'first' => $pd['provider']['fname'] ?? '',
                    ],
                ],
                'phone' => [
                    [
                        'number' => $pd['provider']['phone'] ?? '',
                        'type' => 'work place',
                    ],
                ],
            ];
        }
        
        // Process locations
        $locations = [];
        if (!empty($pd['facility_name'])) {
            $locations[] = [
                'name' => $pd['facility_name'],
                'location_type' => [
                    'name' => $pd['facility_address'] ?? '',
                    'code' => '1160-1',
                    'code_system_name' => 'HealthcareServiceLocation',
                ],
                'address' => [
                    [
                        'street_lines' => [$pd['facility_address'] ?? ''],
                        'city' => $pd['facility_city'] ?? '',
                        'state' => $pd['facility_state'] ?? '',
                        'zip' => $pd['facility_zip'] ?? '',
                        'country' => 'US',
                    ],
                ],
                'phone' => [
                    [
                        'number' => $pd['facility_phone'] ?? '',
                        'type' => 'work place',
                    ],
                ],
            ];
        }
        
        // Process findings
        $findings = [];
        if (!empty($pd['findings'])) {
            $findingsList = $pd['findings'];
            if (!isset($findingsList[0])) {
                $findingsList = [$findingsList];
            }
            foreach ($findingsList as $finding) {
                $findings[] = [
                    'identifiers' => [
                        [
                            'identifier' => $finding['sha_extension'] ?? '',
                            'extension' => $finding['extension'] ?? '',
                        ],
                    ],
                    'value' => [
                        'name' => $finding['name'] ?? $finding['title'] ?? '',
                        'code' => CodeCleaner::clean($finding['code'] ?? ''),
                        'code_system_name' => $finding['code_type'] ?? 'SNOMED CT',
                    ],
                    'date_time' => [
                        'low' => [
                            'date' => DateFormatter::fDate($finding['date'] ?? ''),
                            'precision' => 'day',
                        ],
                    ],
                    'status' => $finding['status'] ?? 'Completed',
                    'reason' => $finding['reason'] ?? '',
                ];
            }
        }
        
        return [
            'plan' => [  // ADDED: Required field
                'name' => $pd['code_text'] ?? $pd['title'] ?? '',
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'code_system_name' => $pd['code_type'] ?? 'LOINC',
            ],
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'goal' => [  // ADDED: Goal field
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'name' => $pd['description'] ?? $pd['text'] ?? '',
            ],
            'date_time' => [
                'point' => [
                    'date' => DateFormatter::fDate($pd['date'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'type' => $pd['type'] ?? 'observation',  // ADDED: Required for template selection
            'status' => [
                'code' => $pd['status'] ?? 'active',
            ],
            'author' => $this->buildAuthorBlock($author),
            'performers' => $performers,  // ADDED: Required field
            'locations' => $locations,    // ADDED: Required field
            'findings' => $findings,      // ADDED: Required field
            'name' => $pd['description'] ?? $pd['text'] ?? '',
            'mood_code' => $pd['moodCode'] ?? 'INT',
        ];
    }

    /**
     * Populate goal data
     */
    private function populateGoal(array $pd): array
    {
        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'goal' => [
                'name' => $pd['description'] ?? $pd['title'] ?? '',
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'code_system_name' => $pd['code_type'] ?? 'SNOMED CT',
            ],
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($pd['date'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'status' => $pd['status'] ?? 'active',
        ];
    }

    /**
     * Populate health concern data
     */
    private function populateHealthConcern(array $pd): array
    {
        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'concern' => [
                'name' => $pd['title'] ?? $pd['description'] ?? '',
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'code_system_name' => $pd['code_type'] ?? 'SNOMED CT',
            ],
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($pd['begdate'] ?? $pd['date'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'status' => $pd['status'] ?? 'active',
        ];
    }

    /**
     * Populate medical device data
     */
    private function populateMedicalDevice(array $pd): array
    {
        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'device' => [
                'name' => $pd['title'] ?? $pd['code_text'] ?? '',
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'code_system_name' => $pd['code_type'] ?? 'SNOMED CT',
            ],
            'udi' => $pd['udi'] ?? '',
            'status' => $pd['status'] ?? 'completed',
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($pd['date'] ?? ''),
                    'precision' => 'day',
                ],
            ],
        ];
    }

    /**
     * Populate social history data
     */
    private function populateSocialHistory(array $pd): array
    {
        $observations = [];
        
        // Smoking status
        if (!empty($pd['smoking'])) {
            $observations[] = [
                'identifiers' => [
                    [
                        'identifier' => $this->oidFacility,
                        'extension' => 'smoking-' . ($pd['extension'] ?? ''),
                    ],
                ],
                'code' => [
                    'name' => 'Tobacco smoking status NHIS',
                    'code' => '72166-2',
                    'code_system' => '2.16.840.1.113883.6.1',
                    'code_system_name' => 'LOINC',
                ],
                'value' => [
                    'name' => $pd['smoking_status'] ?? $pd['smoking'] ?? '',
                    'code' => CodeCleaner::clean($pd['smoking_status_code'] ?? ''),
                    'code_system' => '2.16.840.1.113883.6.96',
                    'code_system_name' => 'SNOMED CT',
                ],
                'date_time' => [
                    'low' => [
                        'date' => DateFormatter::fDate($pd['date'] ?? ''),
                        'precision' => 'day',
                    ],
                ],
            ];
        }

        // Social history observations
        $socialFields = ['alcohol', 'recreational_drugs', 'sexual_activity', 'exercise'];
        foreach ($socialFields as $field) {
            if (!empty($pd[$field])) {
                $observations[] = [
                    'identifiers' => [
                        [
                            'identifier' => $this->oidFacility,
                            'extension' => $field . '-' . ($pd['extension'] ?? ''),
                        ],
                    ],
                    'code' => [
                        'name' => ucfirst(str_replace('_', ' ', $field)),
                        'code' => '',
                        'code_system_name' => 'SNOMED CT',
                    ],
                    'value' => $pd[$field],
                    'date_time' => [
                        'low' => [
                            'date' => DateFormatter::fDate($pd['date'] ?? ''),
                            'precision' => 'day',
                        ],
                    ],
                ];
            }
        }

        return $observations;
    }

    /**
     * Populate care team members
     */
    private function populateCareTeamMembers(array $pd): array
    {
        // Get provider data from correct path: care_team.provider
        $careTeam = $pd['care_team'] ?? [];
        if (empty($careTeam)) {
            return [];
        }

        $providerData = $careTeam['provider'] ?? [];
        
        // Handle both array and single object
        $providers = [];
        if (!empty($providerData)) {
            if (!isset($providerData[0])) {
                // Single provider object - wrap in array
                $providers = [$providerData];
            } else {
                // Already an array
                $providers = $providerData;
            }
        } else {
            return [];
        }

        $result = [];
        foreach ($providers as $member) {
            if (empty($member)) {
                continue;
            }

            $result[] = [
                'identifiers' => [
                    [
                        'identifier' => '2.16.840.1.113883.4.6',
                        'extension' => $member['npi'] ?? '',
                    ],
                ],
                'code' => [
                    'code' => $member['role_code'] ?? '',
                    'display_name' => $member['role_display'] ?? ($member['role'] ?? ''),
                    'code_system' => '2.16.840.1.113883.6.101',
                    'code_system_name' => 'SNOMED CT',
                ],
                'date_time' => [
                    'low' => [
                        'date' => DateFormatter::fDate($member['provider_since'] ?? ''),
                        'precision' => 'tz',
                    ],
                ],
                'name' => [
                    [
                        'last' => $member['lname'] ?? '',
                        'first' => $member['fname'] ?? '',
                        'prefix' => $member['prefix'] ?? '',
                    ],
                ],
                'address' => [
                    [
                        'street_lines' => [$member['street'] ?? ''],
                        'city' => $member['city'] ?? '',
                        'state' => $member['state'] ?? '',
                        'zip' => $member['zip'] ?? '',
                        'country' => 'US',
                    ],
                ],
                'phone' => [
                    ['number' => $member['telecom'] ?? ''],
                ],
                'status' => $member['status'] ?? 'active',
            ];
        }

        // Return in expected format with author and status
        return [
            'providers' => [
                'provider' => $result
            ],
            'status' => $careTeam['is_active'] ?? 'active',
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($providers[0]['provider_since'] ?? ''),
                    'precision' => 'tz',
                ],
            ],
            'author' => $this->populateAuthorFromAuthorContainer($careTeam),
        ];
    }

    /**
     * Populate payer/insurance data
     */
    private function populatePayer(array $pd): array
    {
        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'policy' => [
                'identifiers' => [
                    [
                        'identifier' => $pd['policy_number'] ?? '',
                    ],
                ],
                'insurance' => [
                    'name' => $pd['name'] ?? $pd['company_name'] ?? '',
                    'code' => '',
                ],
            ],
            'payer' => [
                'name' => $pd['name'] ?? $pd['company_name'] ?? '',
            ],
            'date_time' => [
                'low' => [
                    'date' => DateFormatter::fDate($pd['date'] ?? ''),
                    'precision' => 'day',
                ],
            ],
            'participant' => [
                'name' => [
                    [
                        'last' => $pd['subscriber_lname'] ?? '',
                        'first' => $pd['subscriber_fname'] ?? '',
                    ],
                ],
                'relationship' => $pd['subscriber_relationship'] ?? '',
            ],
        ];
    }

    /**
     * Process advance directives
     */
    private function processAdvanceDirectives(array $directives): array
    {
        $result = [];
        foreach ($directives as $directive) {
            $result[] = [
                'identifiers' => [
                    [
                        'identifier' => $directive['sha_extension'] ?? $this->oidFacility,
                        'extension' => $directive['extension'] ?? '',
                    ],
                ],
                'type' => [
                    'name' => $directive['code_text'] ?? $directive['title'] ?? '',
                    'code' => CodeCleaner::clean($directive['code'] ?? ''),
                    'code_system_name' => 'LOINC',
                ],
                'date_time' => [
                    'low' => [
                        'date' => DateFormatter::fDate($directive['date'] ?? ''),
                        'precision' => 'day',
                    ],
                ],
                'status' => $directive['status'] ?? 'completed',
            ];
        }
        return $result;
    }

    /**
     * Populate clinical note data
     */
    private function populateNote(array $pd): array
    {
        return [
            'identifiers' => [
                [
                    'identifier' => $pd['sha_extension'] ?? $this->oidFacility,
                    'extension' => $pd['extension'] ?? '',
                ],
            ],
            'code' => [
                'name' => $pd['code_text'] ?? $pd['note_type'] ?? 'Clinical Note',
                'code' => CodeCleaner::clean($pd['code'] ?? ''),
                'code_system_name' => 'LOINC',
            ],
            'date_time' => [
                'point' => [
                    'date' => DateFormatter::fDate($pd['date'] ?? ''),
                    'precision' => 'tz',
                ],
            ],
            'text' => $pd['description'] ?? $pd['note'] ?? '',
            'author' => $this->buildAuthorBlock($pd['author'] ?? []),
        ];
    }

    /**
     * Populate author information from author container
     * (matches JavaScript populateAuthorFromAuthorContainer)
     */
    private function populateAuthorFromAuthorContainer(array $container): array
    {
        if (empty($container)) {
            return [];
        }

        $author = $container['author'] ?? [];
        if (empty($author)) {
            return [];
        }

        return [
            'code' => [
                'name' => $author['physician_type'] ?? '',
                'code' => $author['physician_type_code'] ?? '',
                'code_system' => $author['physician_type_system'] ?? '',
                'code_system_name' => $author['physician_type_system_name'] ?? '',
            ],
            'date_time' => [
                'point' => [
                    'date' => DateFormatter::fDate($author['time'] ?? ''),
                    'precision' => 'tz',
                ],
            ],
            'identifiers' => [
                [
                    'identifier' => !empty($author['npi']) 
                        ? '2.16.840.1.113883.4.6' 
                        : ($author['id'] ?? ''),
                    'extension' => !empty($author['npi']) 
                        ? $author['npi'] 
                        : 'NI',
                ],
            ],
            'name' => [
                [
                    'last' => $author['lname'] ?? '',
                    'first' => $author['fname'] ?? '',
                ],
            ],
            'organization' => [
                [
                    'identity' => [
                        [
                            'root' => $author['facility_oid'] ?? '2.16.840.1.113883.4.6',
                            'extension' => $author['facility_npi'] ?? 'NI',
                        ],
                    ],
                    'name' => [
                        $author['facility_name'] ?? '',
                    ],
                ],
            ],
        ];
    }
}
