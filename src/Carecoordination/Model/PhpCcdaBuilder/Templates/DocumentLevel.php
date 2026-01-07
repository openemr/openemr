<?php

/**
 * DocumentLevel.php - Document-level template assembly
 *
 * PHP port of oe-blue-button-generate/lib/documentLevel.js
 * Assembles the complete ClinicalDocument structure using HeaderLevel and SectionLevel.
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

class DocumentLevel
{
    /**
     * Generate CCD2 (C-CDA 2.1) document template
     * This is the PHP equivalent of documentLevel.js exports.ccd2()
     */
    public static function ccd2(): array
    {
        return [
            'key' => 'ClinicalDocument',
            'attributes' => [
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xmlns' => 'urn:hl7-org:v3',
                'xmlns:voc' => 'urn:hl7-org:v3/voc',
                'xmlns:sdtc' => 'urn:hl7-org:sdtc',
            ],
            'content' => [
                // Header elements
                ['key' => 'realmCode', 'attributes' => ['code' => 'US']],
                ['key' => 'typeId', 'attributes' => ['root' => '2.16.840.1.113883.1.3', 'extension' => 'POCD_HD000040']],

                // US Realm Header templateIds
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.1.1', '2023-05-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.1.1'),
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.1.1', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.1.1'),

                // Document-specific template from input
                [
                    'key' => 'templateId',
                    'attributes' => [
                        'root' => fn($input) => $input['root'] ?? '',
                        'extension' => fn($input) => $input['extension'] ?? '',
                    ],
                    'dataKey' => 'meta.ccda_header.template',
                ],
                [
                    'key' => 'templateId',
                    'attributes' => [
                        'root' => fn($input) => $input['root'] ?? '',
                    ],
                    'dataKey' => 'meta.ccda_header.template',
                ],

                // Document ID
                [
                    'key' => 'id',
                    'attributes' => [
                        'root' => fn($input) => $input['identifier'] ?? '',
                        'extension' => fn($input) => $input['extension'] ?? '',
                    ],
                    'dataKey' => 'meta.identifiers.0',
                ],

                // Document code
                [
                    'key' => 'code',
                    'attributes' => [
                        'codeSystem' => '2.16.840.1.113883.6.1',
                        'codeSystemName' => 'LOINC',
                        'code' => fn($input) => $input['code'] ?? '',
                        'displayName' => fn($input) => $input['name'] ?? '',
                    ],
                    'dataKey' => 'meta.ccda_header.code',
                ],

                // Document title
                [
                    'key' => 'title',
                    'text' => fn($input) => $input['title'] ?? 'Clinical Document',
                    'dataKey' => 'meta.ccda_header',
                ],

                // Effective time
                [
                    'key' => 'effectiveTime',
                    'attributes' => [
                        'value' => fn($input) => $input['date'] ?? '',
                    ],
                    'dataKey' => 'meta.ccda_header.date_time.point',
                    'required' => true,
                ],

                // Confidentiality code
                [
                    'key' => 'confidentialityCode',
                    'attributes' => [
                        'code' => 'N',
                        'codeSystem' => '2.16.840.1.113883.5.25',
                        'codeSystemName' => 'Confidentiality',
                        'displayName' => 'Normal',
                    ],
                ],

                // Language code
                ['key' => 'languageCode', 'attributes' => ['code' => 'en-US']],

                // Set ID (optional)
                [
                    'key' => 'setId',
                    'attributes' => [
                        'root' => fn($input) => $input['identifier'] ?? '',
                        'extension' => fn($input) => $input['extension'] ?? '',
                    ],
                    'dataKey' => 'meta.set_id',
                    'existsWhen' => fn($input) => !empty($input['identifier']),
                ],

                // Version number
                ['key' => 'versionNumber', 'attributes' => ['value' => '1']],

                // Record Target (patient demographics)
                HeaderLevel::recordTarget(),

                // Author
                HeaderLevel::headerAuthor(),

                // Informant (optional)
                HeaderLevel::headerInformant(),

                // Custodian
                HeaderLevel::headerCustodian(),

                // Information Recipient (optional)
                HeaderLevel::headerInformationRecipient(),

                // Participants (optional)
                HeaderLevel::participant(),

                // Documentation Of (providers)
                HeaderLevel::providers(),

                // Component Of (encompassing encounter)
                HeaderLevel::headerComponentOf(),

                // Structured Body with all sections
                [
                    'key' => 'component',
                    'content' => [
                        [
                            'key' => 'structuredBody',
                            'content' => self::getAllSections(),
                        ],
                    ],
                    'dataKey' => 'data',
                ],
            ],
        ];
    }

    /**
     * Generate unstructured document template
     */
    public static function unstructured(): array
    {
        return [
            'key' => 'ClinicalDocument',
            'attributes' => [
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xmlns' => 'urn:hl7-org:v3',
                'xmlns:voc' => 'urn:hl7-org:v3/voc',
                'xmlns:sdtc' => 'urn:hl7-org:sdtc',
            ],
            'content' => [
                ['key' => 'realmCode', 'attributes' => ['code' => 'US']],
                ['key' => 'typeId', 'attributes' => ['root' => '2.16.840.1.113883.1.3', 'extension' => 'POCD_HD000040']],
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.1.1', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.1.1'),
                [
                    'key' => 'templateId',
                    'attributes' => [
                        'root' => fn($input) => $input['root'] ?? '',
                        'extension' => fn($input) => $input['extension'] ?? '',
                    ],
                    'dataKey' => 'meta.ccda_header.template',
                ],
                [
                    'key' => 'id',
                    'attributes' => [
                        'root' => fn($input) => $input['identifier'] ?? '',
                        'extension' => fn($input) => $input['extension'] ?? '',
                    ],
                    'dataKey' => 'meta.identifiers.0',
                ],
                [
                    'key' => 'code',
                    'attributes' => [
                        'codeSystem' => '2.16.840.1.113883.6.1',
                        'codeSystemName' => 'LOINC',
                        'code' => fn($input) => $input['code'] ?? '',
                        'displayName' => fn($input) => $input['name'] ?? '',
                    ],
                    'dataKey' => 'meta.ccda_header.code',
                ],
                [
                    'key' => 'title',
                    'text' => fn($input) => $input['title'] ?? 'Clinical Document',
                    'dataKey' => 'meta.ccda_header',
                ],
                [
                    'key' => 'effectiveTime',
                    'attributes' => ['value' => fn($input) => $input['date'] ?? ''],
                    'dataKey' => 'meta.ccda_header.date_time.point',
                    'required' => true,
                ],
                [
                    'key' => 'confidentialityCode',
                    'attributes' => [
                        'code' => 'N',
                        'codeSystem' => '2.16.840.1.113883.5.25',
                    ],
                ],
                ['key' => 'languageCode', 'attributes' => ['code' => 'en-US']],
                ['key' => 'versionNumber', 'attributes' => ['value' => '1']],

                HeaderLevel::recordTarget(),
                HeaderLevel::headerAuthor(),
                HeaderLevel::headerInformant(),
                HeaderLevel::headerCustodian(),
                HeaderLevel::headerInformationRecipient(),
                HeaderLevel::participant(),
                HeaderLevel::providers(),
                HeaderLevel::headerComponentOf(),

                // Empty content for unstructured - patient files added separately
                [],
            ],
        ];
    }

    /**
     * Get all section templates for structured body
     */
    private static function getAllSections(): array
    {
        return [
            SectionLevel::careTeamSection(
                HtmlHeaders::careTeamSectionHtmlHeader(),
                HtmlHeaders::careTeamSectionHtmlHeaderNA()
            ),
            [SectionLevel::allergiesSectionEntriesRequired(
                HtmlHeaders::allergiesSectionEntriesRequiredHtmlHeader(),
                HtmlHeaders::allergiesSectionEntriesRequiredHtmlHeaderNA()
            ), 'required' => true],
            [SectionLevel::medicationsSectionEntriesRequired(
                HtmlHeaders::medicationsSectionEntriesRequiredHtmlHeader(),
                HtmlHeaders::medicationsSectionEntriesRequiredHtmlHeaderNA()
            ), 'required' => true],
            [SectionLevel::problemsSectionEntriesRequired(
                HtmlHeaders::problemsSectionEntriesRequiredHtmlHeader(),
                HtmlHeaders::problemsSectionEntriesRequiredHtmlHeaderNA()
            ), 'required' => true],
            [SectionLevel::proceduresSectionEntriesRequired(
                HtmlHeaders::proceduresSectionEntriesRequiredHtmlHeader(),
                HtmlHeaders::proceduresSectionEntriesRequiredHtmlHeaderNA()
            ), 'required' => true],
            [SectionLevel::resultsSectionEntriesRequired(
                HtmlHeaders::resultsSectionEntriesRequiredHtmlHeader(),
                HtmlHeaders::resultsSectionEntriesRequiredHtmlHeaderNA()
            ), 'required' => true],
            SectionLevel::advanceDirectivesSection(
                HtmlHeaders::advanceDirectivesHtmlHeader(),
                HtmlHeaders::advanceDirectivesHtmlHeaderNA()
            ),
            SectionLevel::functionalStatusSection(
                HtmlHeaders::functionalStatusSectionHtmlHeader(),
                HtmlHeaders::functionalStatusSectionHtmlHeaderNA()
            ),
            SectionLevel::encountersSectionEntriesOptional(
                HtmlHeaders::encountersSectionEntriesOptionalHtmlHeader(),
                HtmlHeaders::encountersSectionEntriesOptionalHtmlHeaderNA()
            ),
            SectionLevel::immunizationsSectionEntriesOptional(
                HtmlHeaders::immunizationsSectionEntriesOptionalHtmlHeader(),
                HtmlHeaders::immunizationsSectionEntriesOptionalHtmlHeaderNA()
            ),
            SectionLevel::payersSection(
                HtmlHeaders::payersSectionHtmlHeader(),
                HtmlHeaders::payersSectionHtmlHeaderNA()
            ),
            SectionLevel::planOfCareSection(
                HtmlHeaders::planOfCareSectionHtmlHeader(),
                HtmlHeaders::planOfCareSectionHtmlHeaderNA()
            ),
            SectionLevel::goalSection(
                HtmlHeaders::goalSectionHtmlHeader(),
                HtmlHeaders::goalSectionHtmlHeaderNA()
            ),
            SectionLevel::healthConcernSection(
                HtmlHeaders::healthConcernSectionHtmlHeader(),
                HtmlHeaders::healthConcernSectionHtmlHeaderNA()
            ),
            SectionLevel::socialHistorySection(
                HtmlHeaders::socialHistorySectionHtmlHeader(),
                HtmlHeaders::socialHistorySectionHtmlHeaderNA()
            ),
            SectionLevel::vitalSignsSectionEntriesOptional(
                HtmlHeaders::vitalSignsSectionEntriesOptionalHtmlHeader(),
                HtmlHeaders::vitalSignsSectionEntriesOptionalHtmlHeaderNA()
            ),
            SectionLevel::medicalEquipmentSectionEntriesOptional(
                HtmlHeaders::medicalEquipmentSectionEntriesOptionalHtmlHeader(),
                HtmlHeaders::medicalEquipmentSectionEntriesOptionalHtmlHeaderNA()
            ),
        ];
    }
}
