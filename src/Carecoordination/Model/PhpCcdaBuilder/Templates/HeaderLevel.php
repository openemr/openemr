<?php

/**
 * HeaderLevel.php - Header-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/headerLevel.js
 * Contains templates for recordTarget, author, custodian, and other header elements.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates;

use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\Condition;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\FieldLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\LeafLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\Translate;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates\EntryLevel\EntryLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates\EntryLevel\SharedEntryLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\CodeSystems\CcdaTemplateCodes;

class HeaderLevel
{
    /**
     * Patient name with use="L" (legal name)
     */
    public static function patientName(): array
    {
        $name = FieldLevel::usRealmName();
        $name['attributes'] = ['use' => 'L'];
        return $name;
    }

    /**
     * Patient element within patientRole
     */
    public static function patient(): array
    {
        return [
            'key' => 'patient',
            'content' => [
                // Legal name
                self::patientName(),
                // Birth name (if different)
                [
                    'key' => 'name',
                    'content' => [
                        [
                            'key' => 'given',
                            'attributes' => ['qualifier' => 'BR'],
                            'text' => fn($input) => $input['first'] ?? null,
                        ],
                        [
                            'key' => 'given',
                            'text' => fn($input) => $input['middle'] ?? null,
                            'existsWhen' => fn($input) => !empty($input['middle']),
                        ],
                        [
                            'key' => 'family',
                            'attributes' => ['qualifier' => 'BR'],
                            'text' => fn($input) => $input['last'] ?? null,
                        ],
                    ],
                    'dataKey' => 'birth_name',
                    'existsWhen' => fn($input) => !empty($input['last']),
                ],
                // Administrative Gender
                [
                    'key' => 'administrativeGenderCode',
                    'attributes' => [
                        'code' => function ($input) {
                            if (is_string($input)) {
                                return strtoupper(substr($input, 0, 1));
                            }
                            return strtoupper(substr($input['code'] ?? $input, 0, 1));
                        },
                        'codeSystem' => '2.16.840.1.113883.5.1',
                        'codeSystemName' => 'HL7 AdministrativeGender',
                        'displayName' => fn($input) => is_string($input) ? $input : ($input['name'] ?? $input),
                    ],
                    'dataKey' => 'gender',
                ],
                // Birth Time
                [
                    'key' => 'birthTime',
                    'attributes' => [
                        'value' => fn($input) => $input['point']['date'] ?? $input['date'] ?? null,
                    ],
                    'dataKey' => 'dob',
                ],
                // Marital Status
                [
                    'key' => 'maritalStatusCode',
                    'attributes' => [
                        'code' => function ($input) {
                            if (is_string($input)) {
                                return strtoupper(substr($input, 0, 1));
                            }
                            return strtoupper(substr($input['code'] ?? $input, 0, 1));
                        },
                        'displayName' => fn($input) => is_string($input) ? $input : ($input['name'] ?? $input),
                        'codeSystem' => '2.16.840.1.113883.5.2',
                        'codeSystemName' => 'HL7 Marital Status',
                    ],
                    'dataKey' => 'marital_status',
                    'existsWhen' => fn($input) => !empty($input),
                ],
                // Religious Affiliation
                [
                    'key' => 'religiousAffiliationCode',
                    'attributes' => LeafLevel::codeFromName('2.16.840.1.113883.5.1076'),
                    'dataKey' => 'religion',
                    'existsWhen' => fn($input) => !empty($input),
                ],
                // Race Code
                [
                    'key' => 'raceCode',
                    'attributes' => LeafLevel::codeFromName('2.16.840.1.113883.6.238'),
                    'dataKey' => 'race',
                    'existsWhen' => fn($input) => !empty($input),
                ],
                // Additional Race (sdtc extension)
                [
                    'key' => 'sdtc:raceCode',
                    'attributes' => LeafLevel::codeFromName('2.16.840.1.113883.6.238'),
                    'dataKey' => 'race_additional',
                    'existsWhen' => fn($input) => !empty($input),
                ],
                // Ethnic Group
                [
                    'key' => 'ethnicGroupCode',
                    'attributes' => LeafLevel::codeFromName('2.16.840.1.113883.6.238'),
                    'dataKey' => 'ethnicity',
                    'existsWhen' => fn($input) => !empty($input),
                ],
                // Guardian
                self::guardian(),
                // Birthplace
                self::birthplace(),
                // Language Communication
                self::languageCommunication(),
            ],
        ];
    }

    /**
     * Guardian element
     */
    public static function guardian(): array
    {
        return [
            'key' => 'guardian',
            'content' => [
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(...),
                    'dataKey' => 'code',
                ],
                [
                    'key' => 'addr',
                    'attributes' => ['use' => fn($input) => Translate::acronymize($input['use'] ?? 'HP')],
                    'content' => [
                        ['key' => 'streetAddressLine', 'text' => fn($input) => $input['street_lines'][0] ?? null, 'dataKey' => 'addresses'],
                        ['key' => 'city', 'text' => fn($input) => $input['city'] ?? null],
                        ['key' => 'state', 'text' => fn($input) => $input['state'] ?? null],
                        ['key' => 'postalCode', 'text' => fn($input) => $input['zip'] ?? null],
                        ['key' => 'country', 'text' => fn($input) => $input['country'] ?? 'US'],
                    ],
                    'dataKey' => 'addresses',
                ],
                FieldLevel::telecom(),
                [
                    'key' => 'guardianPerson',
                    'content' => [
                        'key' => 'name',
                        'content' => [
                            ['key' => 'given', 'text' => fn($input) => $input['first'] ?? null],
                            ['key' => 'family', 'text' => fn($input) => $input['last'] ?? null],
                        ],
                        'dataKey' => 'names',
                    ],
                ],
            ],
            'dataKey' => 'guardians',
            'existsWhen' => fn($input) => !empty($input),
        ];
    }

    /**
     * Birthplace element
     */
    public static function birthplace(): array
    {
        return [
            'key' => 'birthplace',
            'content' => [
                'key' => 'place',
                'content' => [
                    [
                        'key' => 'addr',
                        'content' => [
                            ['key' => 'city', 'text' => fn($input) => $input['city'] ?? null],
                            ['key' => 'state', 'text' => fn($input) => $input['state'] ?? null],
                            ['key' => 'postalCode', 'text' => fn($input) => $input['zip'] ?? null],
                            ['key' => 'country', 'text' => fn($input) => $input['country'] ?? null],
                        ],
                        'dataKey' => 'birthplace',
                    ],
                ],
            ],
            'existsWhen' => fn($input) => !empty($input['birthplace']),
        ];
    }

    /**
     * Language Communication element
     */
    public static function languageCommunication(): array
    {
        return [
            'key' => 'languageCommunication',
            'content' => [
                [
                    'key' => 'languageCode',
                    'attributes' => ['code' => fn($input) => $input],
                    'dataKey' => 'language',
                ],
                [
                    'key' => 'modeCode',
                    'attributes' => LeafLevel::codeFromName('2.16.840.1.113883.5.60'),
                    'dataKey' => 'mode',
                    'existsWhen' => fn($input) => !empty($input),
                ],
                [
                    'key' => 'proficiencyLevelCode',
                    'attributes' => [
                        'code' => function ($input) {
                            if (is_string($input)) {
                                return strtoupper(substr($input, 0, 1));
                            }
                            return strtoupper(substr($input['code'] ?? '', 0, 1));
                        },
                        'displayName' => fn($input) => is_string($input) ? $input : ($input['name'] ?? ''),
                        'codeSystem' => '2.16.840.1.113883.5.61',
                        'codeSystemName' => 'LanguageAbilityProficiency',
                    ],
                    'dataKey' => 'proficiency',
                    'existsWhen' => fn($input) => !empty($input),
                ],
                [
                    'key' => 'preferenceInd',
                    'attributes' => ['value' => fn($input) => $input ? 'true' : 'false'],
                    'dataKey' => 'preferred',
                    'existsWhen' => fn($input) => $input !== null,
                ],
            ],
            'dataKey' => 'languages',
            'existsWhen' => fn($input) => !empty($input),
        ];
    }

    /**
     * Provider Organization (attributed_provider)
     */
    public static function attributedProvider(): array
    {
        return [
            'key' => 'providerOrganization',
            'content' => [
                [
                    'key' => 'id',
                    'attributes' => [
                        'root' => fn($input) => $input['root'] ?? null,
                        'extension' => fn($input) => $input['extension'] ?? null,
                    ],
                    'dataKey' => 'identity',
                ],
                [
                    'key' => 'name',
                    'text' => fn($input) => $input['full'] ?? $input['name'] ?? null,
                    'dataKey' => 'name',
                ],
                [
                    'key' => 'telecom',
                    'attributes' => [
                        'use' => 'WP',
                        'value' => fn($input) => isset($input['number']) ? 'tel:' . $input['number'] : null,
                    ],
                    'dataKey' => 'phone',
                ],
                self::simpleAddress(),
            ],
            'dataKey' => 'attributed_provider',
            'existsWhen' => fn($input) => !empty($input),
        ];
    }

    /**
     * Simple address element
     */
    public static function simpleAddress(): array
    {
        return [
            'key' => 'addr',
            'attributes' => ['use' => fn($input) => Translate::acronymize($input['use'] ?? 'WP')],
            'content' => [
                ['key' => 'country', 'text' => fn($input) => $input['country'] ?? null],
                ['key' => 'state', 'text' => fn($input) => $input['state'] ?? null],
                ['key' => 'city', 'text' => fn($input) => $input['city'] ?? null],
                ['key' => 'postalCode', 'text' => fn($input) => $input['zip'] ?? null],
                [
                    'key' => 'streetAddressLine',
                    'text' => fn($input) => $input,
                    'dataKey' => 'street_lines',
                ],
            ],
            'dataKey' => 'address',
            'existsWhen' => fn($input) => !empty($input),
        ];
    }

    /**
     * Record Target - main patient demographic wrapper
     */
    public static function recordTarget(): array
    {
        return [
            'key' => 'recordTarget',
            'content' => [
                'key' => 'patientRole',
                'content' => [
                    // ID - uses identifiers from demographics
                    [
                        'key' => 'id',
                        'attributes' => [
                            'root' => fn($input) => $input['identifier'] ?? '',
                            'extension' => fn($input) => $input['extension'] ?? '',
                        ],
                        'dataKey' => 'identifiers',
                        'existsWhen' => fn($input) => !empty($input['identifier']),
                    ],
                    // Address - uses addresses array
                    [
                        'key' => 'addr',
                        'attributes' => ['use' => fn($input) => Translate::acronymize($input['use'] ?? 'HP')],
                        'content' => [
                            [
                                'key' => 'streetAddressLine',
                                'text' => fn($input) => is_array($input) ? ($input[0] ?? '') : $input,
                                'dataKey' => 'street_lines',
                            ],
                            ['key' => 'city', 'text' => fn($input) => $input['city'] ?? ''],
                            ['key' => 'state', 'text' => fn($input) => $input['state'] ?? ''],
                            ['key' => 'postalCode', 'text' => fn($input) => $input['zip'] ?? ''],
                            ['key' => 'country', 'text' => fn($input) => $input['country'] ?? 'US'],
                        ],
                        'dataKey' => 'addresses',
                    ],
                    // Telecom - handles both phone numbers and emails
                    [
                        'key' => 'telecom',
                        'attributes' => [
                            'value' => function ($input) {
                                // Handle email
                                if (!empty($input['email'])) {
                                    return 'mailto:' . $input['email'];
                                }
                                // Handle phone number
                                if (!empty($input['number'])) {
                                    $num = preg_replace('/[^\d+]/', '', (string) $input['number']);
                                    return 'tel:' . ($num[0] === '+' ? $num : '+' . $num);
                                }
                                // Handle pre-formatted value
                                return $input['value'] ?? null;
                            },
                            'use' => fn($input) => $input['use'] ?? Translate::acronymize($input['type'] ?? 'HP'),
                        ],
                        'dataKey' => 'phone',
                        'existsWhen' => fn($input) => !empty($input['number']) || !empty($input['email']) || !empty($input['value']),
                    ],
                    // Patient element
                    self::patient(),
                    // Provider Organization (attributed_provider)
                    self::attributedProvider(),
                ],
            ],
            'dataKey' => 'data.demographics',
        ];
    }
    /**
     * Header Author
     */
    public static function headerAuthor(): array
    {
        return [
            'key' => 'author',
            'content' => [
                [
                    'key' => 'time',
                    'attributes' => ['value' => fn($input) => $input['point']['date'] ?? $input['date'] ?? null],
                    'dataKey' => 'date_time',
                    'required' => true,
                ],
                [
                    'key' => 'assignedAuthor',
                    'content' => [
                        [
                            'key' => 'id',
                            'attributes' => [
                                'root' => fn($input) => $input['identifier'] ?? null,
                                'extension' => fn($input) => $input['extension'] ?? null,
                            ],
                            'dataKey' => 'identifiers',
                        ],
                        [
                            'key' => 'code',
                            'attributes' => LeafLevel::code(...),
                            'dataKey' => 'code',
                            'existsWhen' => fn($input) => !empty($input['code']),
                        ],
                        self::simpleAddress(),
                        [
                            'key' => 'telecom',
                            'attributes' => [
                                'value' => fn($input) => $input['value'] ?? null,
                                'use' => fn($input) => $input['use'] ?? null,
                            ],
                            'dataTransform' => Translate::telecom(...),
                        ],
                        [
                            'key' => 'assignedPerson',
                            'content' => [
                                'key' => 'name',
                                'content' => [
                                    ['key' => 'family', 'text' => fn($input) => $input['family'] ?? null],
                                    ['key' => 'given', 'text' => fn($input) => $input, 'dataKey' => 'given'],
                                    ['key' => 'prefix', 'text' => fn($input) => $input['prefix'] ?? null],
                                    ['key' => 'suffix', 'text' => fn($input) => $input['suffix'] ?? null],
                                ],
                                'dataKey' => 'name',
                                'dataTransform' => Translate::name(...),
                            ],
                        ],
                        [
                            'key' => 'representedOrganization',
                            'content' => [
                                [
                                    'key' => 'id',
                                    'attributes' => ['root' => fn($input) => $input['root'] ?? null],
                                    'dataKey' => 'identity',
                                ],
                                ['key' => 'name', 'text' => fn($input) => $input, 'dataKey' => 'name'],
                                [
                                    'key' => 'telecom',
                                    'attributes' => [
                                        'value' => fn($input) => $input['value'] ?? null,
                                        'use' => fn($input) => $input['use'] ?? null,
                                    ],
                                    'dataTransform' => Translate::telecom(...),
                                    'dataKey' => 'phone',
                                ],
                                self::simpleAddress(),
                            ],
                            'dataKey' => 'organization',
                        ],
                    ],
                ],
            ],
            'dataKey' => 'meta.ccda_header.author',
        ];
    }

    /**
     * Header Informant
     */
    public static function headerInformant(): array
    {
        return [
            'key' => 'informant',
            'content' => [
                'key' => 'assignedEntity',
                'content' => [
                    [
                        'key' => 'id',
                        'attributes' => ['root' => fn($input) => $input['identifier'] ?? null],
                        'dataKey' => 'identifiers',
                    ],
                    [
                        'key' => 'representedOrganization',
                        'content' => [
                            [
                                'key' => 'id',
                                'attributes' => ['root' => fn($input) => $input['identifier'] ?? null],
                                'dataKey' => 'identifiers',
                            ],
                            [
                                'key' => 'name',
                                'text' => fn($input) => $input['name'] ?? null,
                                'dataKey' => 'name',
                            ],
                        ],
                    ],
                ],
            ],
            'dataKey' => 'meta.ccda_header.informant',
            'existsWhen' => fn($input) => !empty($input),
        ];
    }

    /**
     * Header Custodian
     */
    public static function headerCustodian(): array
    {
        return [
            'key' => 'custodian',
            'content' => [
                'key' => 'assignedCustodian',
                'content' => [
                    [
                        'key' => 'representedCustodianOrganization',
                        'content' => [
                            [
                                'key' => 'id',
                                'attributes' => [
                                    'root' => fn($input) => $input['root'] ?? null,
                                    'extension' => fn($input) => $input['extension'] ?? null,
                                ],
                                'dataKey' => 'identity',
                            ],
                            ['key' => 'name', 'text' => fn($input) => $input, 'dataKey' => 'name'],
                            [
                                'key' => 'telecom',
                                'attributes' => [
                                    'value' => fn($input) => $input['value'] ?? null,
                                    'use' => fn($input) => $input['use'] ?? null,
                                ],
                                'dataTransform' => Translate::telecom(...),
                                'dataKey' => 'phone',
                            ],
                            self::simpleAddress(),
                        ],
                    ],
                ],
            ],
            'dataKey' => 'meta.ccda_header.custodian',
        ];
    }

    /**
     * Header Information Recipient
     */
    public static function headerInformationRecipient(): array
    {
        return [
            'key' => 'informationRecipient',
            'content' => [
                'key' => 'intendedRecipient',
                'content' => [
                    [
                        'key' => 'informationRecipient',
                        'content' => [
                            'key' => 'name',
                            'content' => [
                                ['key' => 'family', 'text' => fn($input) => $input['family'] ?? null],
                                ['key' => 'given', 'text' => fn($input) => $input, 'dataKey' => 'given'],
                                ['key' => 'prefix', 'text' => fn($input) => $input['prefix'] ?? null],
                                ['key' => 'suffix', 'text' => fn($input) => $input['suffix'] ?? null],
                            ],
                            'dataKey' => 'name',
                            'dataTransform' => Translate::name(...),
                        ],
                    ],
                    [
                        'key' => 'receivedOrganization',
                        'content' => [
                            [
                                'key' => 'name',
                                'text' => fn($input) => $input['name'] ?? null,
                                'dataKey' => 'organization',
                            ],
                        ],
                    ],
                ],
            ],
            'dataKey' => 'meta.ccda_header.information_recipient',
            'existsWhen' => fn($input) => !empty($input),
        ];
    }

    /**
     * Header Component Of (Encompassing Encounter)
     */
    public static function headerComponentOf(): array
    {
        return [
            'key' => 'componentOf',
            'content' => [
                'key' => 'encompassingEncounter',
                'content' => [
                    FieldLevel::id(),
                    [
                        'key' => 'code',
                        'attributes' => LeafLevel::code(...),
                        'dataKey' => 'code',
                        'existsWhen' => fn($input) => !empty($input['code']),
                    ],
                    [
                        'key' => 'effectiveTime',
                        'content' => [
                            [
                                'key' => 'low',
                                'attributes' => ['value' => fn($input) => $input['date'] ?? null],
                                'dataKey' => 'low',
                            ],
                            [
                                'key' => 'high',
                                'attributes' => ['value' => fn($input) => $input['date'] ?? null],
                                'dataKey' => 'high',
                            ],
                        ],
                        'dataKey' => 'date_time',
                        'required' => true,
                    ],
                    [
                        'key' => 'encounterParticipant',
                        'attributes' => ['typeCode' => 'ATND'],
                        'content' => [
                            [
                                'key' => 'assignedEntity',
                                'content' => [
                                    [
                                        'key' => 'id',
                                        'attributes' => ['root' => fn($input) => $input['root'] ?? null],
                                    ],
                                    FieldLevel::usRealmAddress(),
                                    FieldLevel::telecom(),
                                    [
                                        'key' => 'assignedPerson',
                                        'content' => FieldLevel::usRealmName(),
                                    ],
                                ],
                            ],
                        ],
                        'dataKey' => 'encounter_participant',
                        'existsWhen' => fn($input) => !empty($input['name']['last']),
                    ],
                ],
            ],
            'dataKey' => 'meta.ccda_header.component_of',
            'existsWhen' => fn($input) => !empty($input),
        ];
    }

    /**
     * Providers / Documentation Of
     */
    public static function providers(): array
    {
        return [
            'key' => 'documentationOf',
            'attributes' => ['typeCode' => 'DOC'],
            'content' => [
                'key' => 'serviceEvent',
                'attributes' => ['classCode' => 'PCPR'],
                'content' => [
                    [
                        'key' => 'code',
                        'attributes' => LeafLevel::code(...),
                        'dataKey' => 'providers.code',
                        'existsWhen' => fn($input) => !empty($input['code']),
                    ],
                    [
                        'key' => 'effectiveTime',
                        'content' => [
                            [
                                'key' => 'low',
                                'attributes' => ['value' => fn($input) => $input['date'] ?? null],
                                'dataKey' => 'low',
                            ],
                            [
                                'key' => 'high',
                                'attributes' => ['value' => fn($input) => $input['date'] ?? null],
                                'dataKey' => 'high',
                            ],
                        ],
                        'dataKey' => 'providers.date_time',
                        'required' => true,
                    ],
                    self::provider(),
                ],
            ],
            'dataKey' => 'data.demographics',
            'existsWhen' => fn($input) => !empty($input['providers']),
        ];
    }

    /**
     * Individual provider performer
     */
    public static function provider(): array
    {
        return [
            'key' => 'performer',
            'attributes' => ['typeCode' => 'PRF'],
            'content' => [
                [
                    'key' => 'functionCode',
                    'attributes' => [
                        'code' => 'PP',
                        'displayName' => 'Primary Performer',
                        'codeSystem' => '2.16.840.1.113883.12.443',
                        'codeSystemName' => 'Provider Role',
                    ],
                    'content' => [['key' => 'originalText', 'text' => fn() => 'Primary Care Provider']],
                    'existsWhen' => fn($input) => !empty($input['function_code']),
                ],
                [
                    'key' => 'assignedEntity',
                    'content' => [
                        [
                            'key' => 'id',
                            'attributes' => [
                                'root' => fn($input) => $input['root'] ?? null,
                                'extension' => fn($input) => $input['extension'] ?? null,
                            ],
                            'dataKey' => 'identity',
                        ],
                        [
                            'key' => 'code',
                            'attributes' => LeafLevel::code(...),
                            'content' => [['key' => 'originalText', 'text' => fn() => 'Care Team Member']],
                            'dataKey' => 'type',
                        ],
                        FieldLevel::usRealmAddress(),
                        FieldLevel::telecom(),
                        [
                            'key' => 'assignedPerson',
                            'content' => FieldLevel::usRealmName(),
                        ],
                    ],
                ],
            ],
            'dataKey' => 'providers.provider',
        ];
    }

    /**
     * Participant (related persons, etc.)
     */
    public static function participant(): array
    {
        return [
            'key' => 'participant',
            'attributes' => [
                'typeCode' => fn($input) => $input['typeCode'] ?? 'IND',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.5.8', '2023-05-01'),
                [
                    'key' => 'time',
                    'content' => [
                        ['key' => 'low', 'attributes' => ['value' => fn($input) => $input['date'] ?? null], 'dataKey' => 'low'],
                        ['key' => 'high', 'attributes' => ['value' => fn($input) => $input['date'] ?? null], 'dataKey' => 'high'],
                    ],
                    'dataKey' => 'date_time',
                    'required' => true,
                ],
                FieldLevel::assignedEntity(),
            ],
            'dataKey' => 'meta.ccda_header.participants',
            'existsWhen' => fn($input) => !empty($input),
        ];
    }
}
