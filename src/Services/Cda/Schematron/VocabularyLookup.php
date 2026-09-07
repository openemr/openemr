<?php

/**
 * VocabularyLookup - resolves value-set OIDs to their allowed @value codes.
 *
 * Used by DocumentPredicateRewriter to rewrite the single-shape predicate
 * `document('voc.xml')/voc:systems/voc:system[@valueSetOid='X']/voc:code/@value`
 * into an inline `(attr='v1' or attr='v2' ...)` expression.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

interface VocabularyLookup
{
    /**
     * @return list<string>|null null when the OID is not present; a (possibly empty) list otherwise
     */
    public function getValuesForOid(string $oid): ?array;
}
