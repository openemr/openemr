<?php

/**
 * HtmlHeaders.php - HTML header templates for CCDA sections
 *
 * PHP port of oe-blue-button-generate/lib/htmlHeaders.js
 * Provides HTML table structures for human-readable narrative in CCDA sections.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
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

class HtmlHeaders
{
    private const NDA = 'No Data Available';

    /**
     * Generic table builder
     */
    public static function getText(string $topArrayKey, array $headers, array $values): array
    {
        $headerContent = [];
        foreach ($headers as $header) {
            $headerContent[] = [
                'key' => 'th',
                'text' => $header,
            ];
        }

        $valueContent = [];
        foreach ($values as $value) {
            $data = is_callable($value) ? $value : LeafLevel::deepInputProperty($value, '');
            $valueContent[] = [
                'key' => 'td',
                'text' => $data,
            ];
        }

        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists($topArrayKey),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => [
                        'border' => '1',
                        'width' => '100%',
                    ],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => $headerContent,
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => $valueContent,
                                    'dataKey' => $topArrayKey,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Allergies Section HTML Header
     */
    public static function allergiesSectionEntriesRequiredHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::propertyValueEmpty('allergies.0.no_know_allergies'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Substance', 'Overall Severity', 'Reaction', 'Reaction Severity', 'Status'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('observation.allergen.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('severity')],
                                            'text' => LeafLevel::deepInputProperty('observation.severity.code.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('reaction')],
                                            'text' => LeafLevel::deepInputProperty('observation.reactions.0.reaction.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('severity')],
                                            'text' => LeafLevel::deepInputProperty('observation.reactions.0.severity.code.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('observation.status.name', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'allergies',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Medications Section HTML Header
     */
    public static function medicationsSectionEntriesRequiredHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('medications'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Medication Class', '# fills', 'Last fill date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('medinfo')],
                                            'text' => function ($input) {
                                                $value = LeafLevel::getDeepValue($input, 'product.product.name');
                                                if (!$value) {
                                                    $value = LeafLevel::getDeepValue($input, 'product.unencoded_name');
                                                }
                                                return $value ?: '';
                                            },
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('supply.repeatNumber', ''),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('supply.date_time.low', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'medications',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Problems Section HTML Header
     */
    public static function problemsSectionEntriesRequiredHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('problems'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Concern', 'Last Observation', 'Reported'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('problem')],
                                            'text' => LeafLevel::deepInputProperty('problem.code.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('healthStatus')],
                                            'text' => LeafLevel::deepInputProperty('patient_status', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('problem.date_time.low', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'problems',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Procedures Section HTML Header
     */
    public static function proceduresSectionEntriesRequiredHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('procedures'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Procedure Name', 'Body Site', 'Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('procedure')],
                                            'text' => LeafLevel::deepInputProperty('procedure.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('body_sites.0.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'procedures',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Results Section HTML Header
     */
    public static function resultsSectionEntriesRequiredHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('results'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Test', 'Value', 'Units', 'Range', 'Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('result')],
                                            'text' => LeafLevel::deepInputProperty('result.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('value', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('unit', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('reference_range.range', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'results',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Encounters Section HTML Header
     */
    public static function encountersSectionEntriesOptionalHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('encounters'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Type', 'Location', 'Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('Encounter')],
                                            'text' => LeafLevel::deepInputProperty('encounter.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('locations.0.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'encounters',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Immunizations Section HTML Header
     */
    public static function immunizationsSectionEntriesOptionalHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('immunizations'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Vaccine', 'Date', 'Status'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('immunization')],
                                            'text' => LeafLevel::deepInputProperty('product.product.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('status', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'immunizations',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Payers Section HTML Header
     */
    public static function payersSectionHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('payers'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Payer Name', 'Policy ID', 'Authorization'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('payer')],
                                            'text' => LeafLevel::deepInputProperty('policy.insurance.performer.organization.name.0', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('policy.identifiers.0.identifier', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('authorization.identifiers.0.identifier', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'payers',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Plan of Care Section HTML Header
     */
    public static function planOfCareSectionHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('plan_of_care'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Planned Activity', 'Type', 'Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('planOfCare')],
                                            'text' => LeafLevel::deepInputProperty('plan.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('type', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'plan_of_care',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Goals Section HTML Header
     */
    public static function goalSectionHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('goals'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Goal', 'Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('goal')],
                                            'text' => LeafLevel::deepInputProperty('name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'goals',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Social History Section HTML Header
     */
    public static function socialHistorySectionHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('social_history'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Social History Element', 'Description', 'Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('social')],
                                            'text' => LeafLevel::deepInputProperty('code.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('value', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'social_history',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Vital Signs Section HTML Header
     * EXACT PORT from htmlHeaders.js - shows horizontal layout with specific vital indices
     */
    public static function vitalSignsSectionEntriesOptionalHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('vitals'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => [
                                                'Date',
                                                'Body Temperature',
                                                'Systolic[90-140 mmHg]',
                                                'Diastolic[60-90 mmHg]',
                                                'Heart Rate',
                                                'Height',
                                                'Weight Measured',
                                                'BMI (Body Mass Index)'
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('vital')],
                                            'text' => LeafLevel::deepInputProperty('vital_list.10.value', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('vital')],
                                            'text' => LeafLevel::deepInputProperty('vital_list.0.value', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('vital')],
                                            'text' => LeafLevel::deepInputProperty('vital_list.1.value', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('vital')],
                                            'text' => LeafLevel::deepInputProperty('vital_list.8.value', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('vital')],
                                            'text' => LeafLevel::deepInputProperty('vital_list.5.value', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('vital')],
                                            'text' => LeafLevel::deepInputProperty('vital_list.6.value', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('vital')],
                                            'text' => LeafLevel::deepInputProperty('vital_list.7.value', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'vitals',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Medical Equipment Section HTML Header
     */
    public static function medicalEquipmentSectionEntriesOptionalHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('medical_devices'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Device', 'Status', 'Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('device')],
                                            'text' => LeafLevel::deepInputProperty('device.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('device.status', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.point', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'medical_devices',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Functional Status Section HTML Header
     */
    public static function functionalStatusSectionHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('functional_status'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Functional Condition', 'Status', 'Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('functional')],
                                            'text' => LeafLevel::deepInputProperty('observation.value.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('observation.status', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('observation.date_time.point', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'functional_status',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Assessment Section HTML Header
     */
    public static function assessmentSectionHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('assessments'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Narrative'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('assessment')],
                                            'text' => LeafLevel::deepInputProperty('description', self::NDA),
                                        ],
                                    ],
                                ],
                            ],
                            'dataKey' => 'assessments',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Care Team Section HTML Header
     */
    public static function careTeamSectionHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['width' => '100%', 'border' => '1'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'th',
                                            'text' => LeafLevel::input(...),
                                            'dataTransform' => fn() => ['Performer Name', 'Performer Role', 'Performer Since Date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('careTeam')],
                                            'text' => LeafLevel::deepInputProperty('full_name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputProperty('function_code.name', self::NDA),
                                        ],
                                        [
                                            'key' => 'td',
                                            'text' => LeafLevel::deepInputDate('date_time.low', self::NDA),
                                        ],
                                    ],
                                    'dataKey' => 'providers.provider',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'dataKey' => 'care_team',
        ];
    }

    /**
     * Health Concern Section HTML Header
     */
    public static function healthConcernSectionHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('concern'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['border' => '1', 'width' => '100%'],
                    'content' => [
                        [
                            'key' => 'caption',
                            'text' => 'Health Concerns',
                        ],
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        ['key' => 'th', 'text' => 'Assessment'],
                                        ['key' => 'th', 'text' => 'Concern (Narrative)'],
                                        ['key' => 'th', 'text' => 'Concern (Description)'],
                                        ['key' => 'th', 'text' => 'Code'],
                                        ['key' => 'th', 'text' => 'CodeSystem'],
                                        ['key' => 'th', 'text' => 'Status'],
                                        ['key' => 'th', 'text' => 'Onset(Low)'],
                                        ['key' => 'th', 'text' => 'Author(First,Last)'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'dataKey' => 'concern',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        ['key' => 'td', 'text' => LeafLevel::deepInputProperty('assessment', self::NDA)],
                                        ['key' => 'td', 'text' => LeafLevel::inputProperty('text', self::NDA)],
                                        ['key' => 'td', 'text' => LeafLevel::deepInputProperty('value.name', self::NDA)],
                                        ['key' => 'td', 'text' => LeafLevel::deepInputProperty('value.code', self::NDA)],
                                        ['key' => 'td', 'text' => LeafLevel::deepInputProperty('value.code_system_name', self::NDA)],
                                        ['key' => 'td', 'text' => 'active'],
                                        ['key' => 'td', 'text' => LeafLevel::deepInputDate('date_time.low', self::NDA)],
                                        [
                                            'key' => 'td',
                                            'text' => function ($input) {
                                                $first = LeafLevel::getDeepValue($input, 'author.name.0.first') ?? '';
                                                $last = LeafLevel::getDeepValue($input, 'author.name.0.last') ?? '';
                                                return ($first || $last) ? trim("$first $last") : 'â€”';
                                            },
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Advance Directives HTML Header
     */
    public static function advanceDirectivesHtmlHeader(): array
    {
        return [
            'key' => 'text',
            'existsWhen' => Condition::keyExists('advance_directives'),
            'content' => [
                [
                    'key' => 'table',
                    'attributes' => ['border' => '1', 'width' => '100%'],
                    'content' => [
                        [
                            'key' => 'thead',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        ['key' => 'th', 'text' => 'Document Type'],
                                        ['key' => 'th', 'text' => 'Status'],
                                        ['key' => 'th', 'text' => 'Effective Date'],
                                        ['key' => 'th', 'text' => 'Location'],
                                        ['key' => 'th', 'text' => 'Author'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tbody',
                            'content' => [
                                [
                                    'key' => 'tr',
                                    'content' => [
                                        [
                                            'key' => 'td',
                                            'attributes' => ['ID' => LeafLevel::nextTableReference('directive')],
                                            'text' => LeafLevel::inputProperty('type'),
                                        ],
                                        ['key' => 'td', 'text' => LeafLevel::inputProperty('status')],
                                        ['key' => 'td', 'text' => LeafLevel::inputProperty('effective_date')],
                                        ['key' => 'td', 'text' => LeafLevel::inputProperty('location')],
                                        ['key' => 'td', 'text' => LeafLevel::inputProperty('author_name')],
                                    ],
                                ],
                            ],
                            'dataKey' => 'advance_directives',
                        ],
                    ],
                ],
            ],
        ];
    }

    // NA constants
    public const ASSESSMENT_NA = 'Not Available';
    public const CARE_TEAM_NA = 'Not Available';
    public const FUNCTIONAL_STATUS_NA = 'Not Available';
    public const ALLERGIES_NA = 'Not Available';
    public const MEDICATIONS_NA = 'Not Available';
    public const PROBLEMS_NA = 'Not Available';
    public const PROCEDURES_NA = 'Not Available';
    public const RESULTS_NA = 'Not Available';
    public const ENCOUNTERS_NA = 'Not Available';
    public const IMMUNIZATIONS_NA = 'Not Available';
    public const PAYERS_NA = 'Not Available';
    public const PLAN_OF_CARE_NA = 'Not Available';
    public const GOALS_NA = 'Not Available';
    public const SOCIAL_HISTORY_NA = 'Not Available';
    public const VITAL_SIGNS_NA = 'Not Available';
    public const MEDICAL_EQUIPMENT_NA = 'Not Available';
    public const HEALTH_CONCERN_NA = 'Not Available';
    public const ADVANCE_DIRECTIVES_NA = 'Not Available';

    /**
     * NA Header methods - return string values for empty sections
     */
    public static function allergiesSectionEntriesRequiredHtmlHeaderNA(): string
    {
        return self::ALLERGIES_NA;
    }

    public static function medicationsSectionEntriesRequiredHtmlHeaderNA(): string
    {
        return self::MEDICATIONS_NA;
    }

    public static function problemsSectionEntriesRequiredHtmlHeaderNA(): string
    {
        return self::PROBLEMS_NA;
    }

    public static function proceduresSectionEntriesRequiredHtmlHeaderNA(): string
    {
        return self::PROCEDURES_NA;
    }

    public static function resultsSectionEntriesRequiredHtmlHeaderNA(): string
    {
        return self::RESULTS_NA;
    }

    public static function encountersSectionEntriesOptionalHtmlHeaderNA(): string
    {
        return self::ENCOUNTERS_NA;
    }

    public static function immunizationsSectionEntriesOptionalHtmlHeaderNA(): string
    {
        return self::IMMUNIZATIONS_NA;
    }

    public static function payersSectionHtmlHeaderNA(): string
    {
        return self::PAYERS_NA;
    }

    public static function planOfCareSectionHtmlHeaderNA(): string
    {
        return self::PLAN_OF_CARE_NA;
    }

    public static function goalSectionHtmlHeaderNA(): string
    {
        return self::GOALS_NA;
    }

    public static function socialHistorySectionHtmlHeaderNA(): string
    {
        return self::SOCIAL_HISTORY_NA;
    }

    public static function vitalSignsSectionEntriesOptionalHtmlHeaderNA(): string
    {
        return self::VITAL_SIGNS_NA;
    }

    public static function medicalEquipmentSectionEntriesOptionalHtmlHeaderNA(): string
    {
        return self::MEDICAL_EQUIPMENT_NA;
    }

    public static function healthConcernSectionHtmlHeaderNA(): string
    {
        return self::HEALTH_CONCERN_NA;
    }

    public static function advanceDirectivesHtmlHeaderNA(): string
    {
        return self::ADVANCE_DIRECTIVES_NA;
    }

    public static function careTeamSectionHtmlHeaderNA(): string
    {
        return self::CARE_TEAM_NA;
    }

    public static function functionalStatusSectionHtmlHeaderNA(): string
    {
        return self::FUNCTIONAL_STATUS_NA;
    }

    public static function assessmentSectionHtmlHeaderNA(): string
    {
        return self::ASSESSMENT_NA;
    }
}
