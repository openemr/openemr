<?php

/**
 * Reads a C-CDA Social History entry for import.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda;

/**
 * The import keeps one kind of Social History entry, Smoking Status, as the
 * record the Care Coordination audit tables store (see
 * {@see CdaTemplateParse::fetchSocialHistoryData()}).
 */
final class CdaSocialHistoryEntry
{
    /** Template root => the import's field for it. */
    public const FIELDS = [
        '2.16.840.1.113883.10.20.22.4.78' => 'smoking', // Smoking Status
    ];

    /**
     * The import's field and record for an entry, or null when the entry has
     * no coded value or isn't a template the import keeps.
     *
     * @return ?array{field: string, record: array{extension: ?string, root: ?string, status: string, begdate: ?string, enddate: ?string, value: ?string}}
     */
    public static function read(mixed $entry): ?array
    {
        $status = self::text($entry, 'observation', 'value', 'code');
        if ($status === null || $status === '' || $status === '0') {
            return null;
        }
        $field = null;
        foreach (CdaTemplateId::roots(self::at($entry, 'observation', 'templateId')) as $root) {
            if (isset(self::FIELDS[$root])) {
                $field = self::FIELDS[$root];
                break;
            }
        }
        if ($field === null) {
            return null;
        }
        return [
            'field' => $field,
            'record' => [
                'extension' => self::text($entry, 'observation', 'id', 'extension'),
                'root' => self::text($entry, 'observation', 'id', 'root'),
                'status' => $status,
                // C-CDA R2.1 Smoking Status gives a point in time; older documents a low.
                'begdate' => self::text($entry, 'observation', 'effectiveTime', 'low', 'value')
                    ?? self::text($entry, 'observation', 'effectiveTime', 'value'),
                'enddate' => self::text($entry, 'observation', 'effectiveTime', 'high', 'value'),
                'value' => self::text($entry, 'observation', 'value', 'displayName'),
            ],
        ];
    }

    private static function at(mixed $node, string ...$path): mixed
    {
        foreach ($path as $key) {
            if (!is_array($node) || !array_key_exists($key, $node)) {
                return null;
            }
            $node = $node[$key];
        }
        return $node;
    }

    private static function text(mixed $node, string ...$path): ?string
    {
        $value = self::at($node, ...$path);
        return is_string($value) ? $value : null;
    }
}
