<?php

/**
 * FieldLevel.php - Field-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/fieldLevel.js
 * Provides reusable field templates for CCDA generation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core;

use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\CodeSystems\CcdaTemplateCodes;
use Ramsey\Uuid\Uuid;

class FieldLevel
{
    /**
     * Template ID without extension
     * JS: exports.templateId
     */
    public static function templateId(string $id): array
    {
        return [
            'key' => 'templateId',
            'attributes' => ['root' => $id],
        ];
    }

    /**
     * Template ID with extension
     * JS: exports.templateIdExt
     */
    public static function templateIdExt(string $id, string $ext): array
    {
        return [
            'key' => 'templateId',
            'attributes' => ['root' => $id, 'extension' => $ext],
        ];
    }

    /**
     * Template code from name using CcdaTemplateCodes
     * JS: exports.templateCode
     */
    public static function templateCode(string $name): array
    {
        $raw = CcdaTemplateCodes::get($name);
        return [
            'key' => 'code',
            'attributes' => [
                'code' => $raw['code'] ?? '',
                'displayName' => $raw['name'] ?? '',
                'codeSystem' => $raw['code_system'] ?? '',
                'codeSystemName' => $raw['code_system_name'] ?? '',
            ],
        ];
    }

    /**
     * Template title from name
     * JS: exports.templateTitle
     */
    public static function templateTitle(string $name): array
    {
        $raw = CcdaTemplateCodes::get($name);
        return [
            'key' => 'title',
            'text' => fn() => $raw['name'] ?? $name,
        ];
    }

    /**
     * ID element with identifier and extension
     * JS: exports.id
     */
    public static array $id = [
        'key' => 'id',
        'attributes' => [
            'root' => [LeafLevel::class, 'inputProperty', 'identifier'],
            'extension' => [LeafLevel::class, 'inputProperty', 'extension'],
        ],
        'dataKey' => 'identifiers',
        'existsWhen' => [Condition::class, 'keyExists', 'identifier'],
        'required' => true,
    ];

    /**
     * Get ID element
     */
    public static function id(): array
    {
        return [
            'key' => 'id',
            'attributes' => [
                'root' => LeafLevel::inputProperty('identifier'),
                'extension' => LeafLevel::inputProperty('extension'),
            ],
            'dataKey' => 'identifiers',
            'existsWhen' => Condition::keyExists('identifier'),
            'required' => true,
        ];
    }

    /**
     * Unique ID using context root and UUID
     * JS: exports.uniqueId
     */
    public static array $uniqueId = [
        'key' => 'id',
        'attributes' => [
            'root' => [self::class, '_uniqueIdRoot'],
            'extension' => [self::class, '_uniqueIdExtension'],
        ],
        'existsWhen' => [self::class, '_hasRootId'],
    ];

    public static function uniqueId(): array
    {
        return [
            'key' => 'id',
            'attributes' => [
                'root' => fn($input, $context) => $context['rootId'] ?? '2.16.840.1.113883.4.6',
                'extension' => fn() => Uuid::uuid4()->toString(),
            ],
            'existsWhen' => fn($input, $context) => !empty($context['rootId'] ?? true),
        ];
    }

    public static function _uniqueIdRoot($input, $context): string
    {
        return $context['rootId'] ?? '2.16.840.1.113883.4.6';
    }

    public static function _uniqueIdExtension(): string
    {
        return Uuid::uuid4()->toString();
    }

    public static function _hasRootId($input, $context): bool
    {
        return !empty($context['rootId'] ?? true);
    }

    /**
     * Unique ID with only root (UUID)
     * JS: exports.uniqueIdRoot
     */
    public static function uniqueIdRoot(): array
    {
        return [
            'key' => 'id',
            'attributes' => [
                'root' => fn() => Uuid::uuid4()->toString(),
            ],
        ];
    }

    /**
     * Status code completed
     * JS: exports.statusCodeCompleted
     */
    public static array $statusCodeCompleted = [
        'key' => 'statusCode',
        'attributes' => ['code' => 'completed'],
    ];

    /**
     * Status code completed - method version for compatibility
     */
    public static function statusCodeCompleted(): array
    {
        return self::$statusCodeCompleted;
    }

    /**
     * Status code active
     * JS: exports.statusCodeActive
     */
    public static array $statusCodeActive = [
        'key' => 'statusCode',
        'attributes' => ['code' => 'active'],
    ];

    /**
     * Status code active - method version for compatibility
     */
    public static function statusCodeActive(): array
    {
        return self::$statusCodeActive;
    }

    /**
     * Status code new
     * JS: exports.statusCodeNew
     */
    public static array $statusCodeNew = [
        'key' => 'statusCode',
        'attributes' => ['code' => 'new'],
    ];

    /**
     * Status code new - method version for compatibility
     */
    public static function statusCodeNew(): array
    {
        return self::$statusCodeNew;
    }

    /**
     * Effective time from document meta
     * JS: exports.effectiveDocumentTime
     */
    public static function effectiveDocumentTime(): array
    {
        return [
            'key' => 'effectiveTime',
            'attributes' => [
                'value' => LeafLevel::inputProperty('date'),
            ],
            'dataKey' => 'meta.ccda_header.date_time',
        ];
    }

    /**
     * Effective time now
     * JS: exports.effectiveTimeNow
     */
    public static function effectiveTimeNow(): array
    {
        return [
            'key' => 'effectiveTime',
            'attributes' => [
                'value' => date('YmdHis'),
            ],
        ];
    }

    /**
     * Time now
     * JS: exports.timeNow
     */
    public static function timeNow(): array
    {
        return [
            'key' => 'time',
            'attributes' => [
                'value' => date('Ymd'),
            ],
        ];
    }

    /**
     * Time from document
     * JS: exports.timeDocumentTime
     */
    public static function timeDocumentTime(): array
    {
        return [
            'key' => 'time',
            'attributes' => [
                'value' => LeafLevel::time(...),
            ],
        ];
    }

    /**
     * Effective time with point/low/high/center
     * JS: exports.effectiveTime
     */
    public static array $effectiveTime = [
        'key' => 'effectiveTime',
        'attributes' => [
            'value' => [LeafLevel::class, 'time'],
        ],
        'attributeKey' => 'point',
        'content' => [
            [
                'key' => 'low',
                'attributes' => ['value' => [LeafLevel::class, 'time']],
                'dataKey' => 'low',
            ],
            [
                'key' => 'high',
                'attributes' => ['value' => [LeafLevel::class, 'time']],
                'dataKey' => 'high',
            ],
            [
                'key' => 'center',
                'attributes' => ['value' => [LeafLevel::class, 'time']],
                'dataKey' => 'center',
            ],
        ],
        'dataKey' => 'date_time',
        'existsWhen' => [Condition::class, 'eitherKeyExists', 'point', 'low', 'high', 'center'],
    ];

    /**
     * Get effective time array
     */
    public static function effectiveTime(): array
    {
        return [
            'key' => 'effectiveTime',
            'attributes' => [
                'value' => LeafLevel::time(...),
            ],
            'attributeKey' => 'point',
            'content' => [
                [
                    'key' => 'low',
                    'attributes' => ['value' => LeafLevel::time(...)],
                    'dataKey' => 'low',
                ],
                [
                    'key' => 'high',
                    'attributes' => ['value' => LeafLevel::time(...)],
                    'dataKey' => 'high',
                ],
                [
                    'key' => 'center',
                    'attributes' => ['value' => LeafLevel::time(...)],
                    'dataKey' => 'center',
                ],
            ],
            'dataKey' => 'date_time',
            'existsWhen' => Condition::eitherKeyExists('point', 'low', 'high', 'center'),
        ];
    }

    /**
     * Effective time IVL_TS (interval)
     * JS: exports.effectiveTimeIVL_TS
     */
    public static function effectiveTimeIVL_TS(): array
    {
        return [
            'key' => 'effectiveTime',
            'attributes' => ['xsi:type' => 'IVL_TS'],
            'content' => [
                [
                    'key' => 'low',
                    'attributes' => ['value' => LeafLevel::time(...)],
                    'dataKey' => 'low',
                ],
                [
                    'key' => 'high',
                    'attributes' => ['value' => LeafLevel::time(...)],
                    'dataKey' => 'high',
                ],
                [
                    'key' => 'center',
                    'attributes' => ['value' => LeafLevel::time(...)],
                    'dataKey' => 'center',
                ],
            ],
            'dataKey' => 'date_time',
            'existsWhen' => Condition::eitherKeyExists('point', 'low', 'high', 'center'),
        ];
    }

    /**
     * Text with reference
     * JS: exports.text
     */
    public static function text(callable $referenceMethod): array
    {
        return [
            'key' => 'text',
            'text' => LeafLevel::inputProperty('free_text'),
            'content' => [
                [
                    'key' => 'reference',
                    'attributes' => ['value' => $referenceMethod],
                ],
            ],
        ];
    }

    /**
     * Null flavor element
     * JS: exports.nullFlavor
     */
    public static function nullFlavor(string $name): array
    {
        return [
            'key' => $name,
            'attributes' => ['nullFlavor' => 'UNK'],
        ];
    }

    /**
     * Useable period
     * JS: exports.useablePeriod
     */
    public static function useablePeriod(): array
    {
        return [
            'key' => 'useablePeriod',
            'attributes' => [
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:type' => 'IVL_TS',
            ],
            'content' => [
                [
                    'key' => 'low',
                    'attributes' => ['value' => LeafLevel::time(...)],
                    'dataKey' => 'low',
                ],
                [
                    'key' => 'high',
                    'attributes' => ['value' => LeafLevel::time(...)],
                    'dataKey' => 'high',
                ],
            ],
            'dataKey' => 'date_time',
            'existsWhen' => Condition::eitherKeyExists('point', 'low', 'high'),
        ];
    }

    /**
     * US Realm Address
     * JS: exports.usRealmAddress
     */
    public static function usRealmAddress(): array
    {
        return [
            'key' => 'addr',
            'attributes' => [
                'use' => LeafLevel::use('use'),
            ],
            'content' => [
                [
                    'key' => 'streetAddressLine',
                    'text' => LeafLevel::input(...),
                    'dataKey' => 'street_lines',
                    'existsWhen' => Condition::propertyNotEmpty('street_lines[0]'),
                ],
                [
                    'key' => 'city',
                    'text' => LeafLevel::inputProperty('city'),
                    'existsWhen' => Condition::propertyNotEmpty('city'),
                ],
                [
                    'key' => 'state',
                    'text' => LeafLevel::inputProperty('state'),
                    'existsWhen' => Condition::propertyNotEmpty('state'),
                ],
                [
                    'key' => 'postalCode',
                    'text' => LeafLevel::inputProperty('zip'),
                    'existsWhen' => Condition::propertyNotEmpty('zip'),
                ],
                [
                    'key' => 'country',
                    'text' => LeafLevel::inputProperty('country'),
                    'existsWhen' => Condition::propertyNotEmpty('country'),
                ],
                self::useablePeriod(),
            ],
            'dataKey' => 'address',
        ];
    }

    /**
     * US Realm Name
     * JS: exports.usRealmName
     */
    public static function usRealmName(): array
    {
        return [
            'key' => 'name',
            'content' => [
                ['key' => 'family', 'text' => LeafLevel::inputProperty('family')],
                ['key' => 'given', 'text' => LeafLevel::input(...), 'dataKey' => 'given'],
                ['key' => 'prefix', 'text' => LeafLevel::inputProperty('prefix')],
                ['key' => 'suffix', 'text' => LeafLevel::inputProperty('suffix')],
            ],
            'dataKey' => 'name',
            'dataTransform' => Translate::name(...),
        ];
    }

    /**
     * Telecom
     * JS: exports.telecom
     */
    public static function telecom(): array
    {
        return [
            'key' => 'telecom',
            'attributes' => [
                'value' => LeafLevel::inputProperty('value'),
                'use' => LeafLevel::inputProperty('use'),
            ],
            'dataTransform' => Translate::telecom(...),
        ];
    }

    /**
     * Assigned Entity
     * JS: exports.assignedEntity
     */
    public static function assignedEntity(): array
    {
        return [
            'key' => 'assignedEntity',
            'content' => [
                self::id(),
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'code',
                ],
                self::usRealmAddress(),
                self::telecom(),
                [
                    'key' => 'assignedPerson',
                    'content' => [self::usRealmName()],
                    'existsWhen' => Condition::keyExists('name'),
                ],
                self::representedOrganization(),
            ],
            'existsWhen' => Condition::eitherKeyExists('address', 'identifiers', 'organization', 'name'),
        ];
    }

    /**
     * Associated Entity
     * JS: exports.associatedEntity
     */
    public static function associatedEntity(): array
    {
        return [
            'key' => 'associatedEntity',
            'attributes' => [
                'classCode' => LeafLevel::inputProperty('classCode'),
            ],
            'content' => [
                self::id(),
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'code',
                ],
                self::usRealmAddress(),
                self::telecom(),
                [
                    'key' => 'associatedPerson',
                    'content' => [self::usRealmName()],
                    'existsWhen' => Condition::keyExists('name'),
                    'attributes' => [
                        'classCode' => 'PSN',
                        'determinerCode' => 'INSTANCE',
                    ],
                ],
            ],
        ];
    }

    /**
     * Represented Organization
     */
    private static function representedOrganization(): array
    {
        return [
            'key' => 'representedOrganization',
            'content' => [
                [
                    'key' => 'id',
                    'attributes' => [
                        'root' => LeafLevel::inputProperty('root'),
                        'extension' => LeafLevel::inputProperty('extension'),
                    ],
                    'dataKey' => 'identity',
                ],
                [
                    'key' => 'name',
                    'text' => LeafLevel::input(...),
                    'dataKey' => 'name',
                ],
            ],
            'dataKey' => 'organization',
        ];
    }

    /**
     * Author
     * JS: exports.author
     */
    public static function author(): array
    {
        return [
            'key' => 'author',
            'attributes' => ['typeCode' => 'AUT'],
            'content' => [
                self::templateId('2.16.840.1.113883.10.20.22.4.119'),
                array_merge(self::effectiveTime(), ['required' => true, 'key' => 'time']),
                [
                    'key' => 'assignedAuthor',
                    'content' => [
                        self::id(),
                        [
                            'key' => 'code',
                            'attributes' => LeafLevel::code(),
                            'existsWhen' => Condition::propertyNotEmpty('code'),
                            'dataKey' => 'code',
                        ],
                        [
                            'key' => 'assignedPerson',
                            'content' => [self::usRealmName()],
                        ],
                        self::representedOrganization(),
                    ],
                ],
            ],
            'dataKey' => 'author',
        ];
    }

    /**
     * Performer
     * JS: exports.performer
     */
    public static function performer(): array
    {
        return [
            'key' => 'performer',
            'content' => [
                array_merge(self::assignedEntity(), ['required' => true]),
            ],
            'dataKey' => 'performer',
        ];
    }

    /**
     * Act Author
     * JS: exports.actAuthor
     */
    public static function actAuthor(): array
    {
        return [
            'key' => 'author',
            'content' => [
                self::templateId('2.16.840.1.113883.10.20.22.4.119'),
                array_merge(self::effectiveTime(), ['required' => true, 'key' => 'time']),
                [
                    'key' => 'assignedAuthor',
                    'content' => [
                        self::id(),
                        [
                            'key' => 'assignedPerson',
                            'content' => [self::usRealmName()],
                        ],
                        [
                            'key' => 'representedOrganization',
                            'content' => [
                                [
                                    'key' => 'id',
                                    'attributes' => ['root' => LeafLevel::inputProperty('root')],
                                    'dataKey' => 'identity',
                                ],
                                [
                                    'key' => 'name',
                                    'text' => LeafLevel::input(...),
                                    'dataKey' => 'name',
                                ],
                            ],
                            'dataKey' => 'organization',
                        ],
                    ],
                ],
            ],
            'dataKey' => 'author',
        ];
    }

    /**
     * Responsible Party
     * JS: exports.responsibleParty
     */
    public static function responsibleParty(): array
    {
        return [
            'key' => 'responsibleParty',
            'content' => [
                [
                    'key' => 'assignedEntity',
                    'content' => [
                        [
                            'key' => 'id',
                            'attributes' => ['root' => LeafLevel::inputProperty('root')],
                        ],
                        [
                            'key' => 'assignedPerson',
                            'content' => [self::usRealmName()],
                        ],
                    ],
                ],
            ],
            'dataKey' => 'responsible_party',
            'existsWhen' => Condition::propertyValueNotEmpty('name.last'),
        ];
    }
}
