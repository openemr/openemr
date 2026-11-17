<?php

/**
 * Reads the templateId of an element parsed from a C-CDA document.
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
 * The XML reader ({@see XmlExtended}) gives an element's one templateId as an
 * array of its attributes, and several as a list of such arrays. Documents
 * often carry several: the same template with and without its version
 * extension, as Synthea and C-CDA R2.1 documents do for Smoking Status.
 */
final class CdaTemplateId
{
    /**
     * The root OIDs of a parsed templateId, in document order.
     *
     * @return list<string>
     */
    public static function roots(mixed $templateId): array
    {
        if (!is_array($templateId)) {
            return [];
        }
        $roots = [];
        foreach (array_is_list($templateId) ? $templateId : [$templateId] as $id) {
            $root = is_array($id) ? ($id['root'] ?? null) : null;
            if (is_string($root) && $root !== '') {
                $roots[] = $root;
            }
        }
        return $roots;
    }
}
