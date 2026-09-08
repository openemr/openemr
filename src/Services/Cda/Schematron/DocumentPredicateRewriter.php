<?php

/**
 * DocumentPredicateRewriter - rewrites schematron `document('voc.xml')` predicates inline.
 *
 * Every `document('voc.xml')` predicate observed across the ccda/qrda1/qrda3 schematrons
 * takes exactly one shape:
 *   <attr>=document('voc.xml')/voc:systems/voc:system[@valueSetOid='<OID>']/voc:code/@value
 *
 * This class turns each occurrence into an inline disjunction:
 *   (<attr>='v1' or <attr>='v2' or ...)
 *
 * using values from the injected VocabularyLookup. This lets PHP's native DOMXPath
 * (XPath 1.0, no document() support) evaluate the assertion end-to-end.
 *
 * Any occurrence not matching the expected shape throws — matching the JS engine's
 * behavior of surfacing the assertion as ignored/errored rather than silently
 * evaluating an incorrect expression.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

use RuntimeException;

final readonly class DocumentPredicateRewriter
{
    private const PATTERN = "/([\w:@]+)\s*=\s*document\(['\"]voc\.xml['\"]\)\/voc:systems\/voc:system\[@valueSetOid=['\"]([0-9.]+)['\"]\]\/voc:code\/@value/";

    public function __construct(private VocabularyLookup $vocabulary)
    {
    }

    public function rewrite(string $test): string
    {
        if (!str_contains($test, "document('voc.xml')") && !str_contains($test, 'document("voc.xml")')) {
            return $test;
        }

        $rewritten = preg_replace_callback(
            self::PATTERN,
            function (array $m): string {
                [$_, $lhs, $oid] = $m;
                $values = $this->vocabulary->getValuesForOid($oid);
                if ($values === null) {
                    throw new RuntimeException("Vocabulary OID not found: $oid");
                }
                if ($values === []) {
                    return '(false())';
                }
                $terms = array_map(fn(string $v): string => $lhs . '=' . self::xpathLit($v), $values);
                return '(' . implode(' or ', $terms) . ')';
            },
            $test,
        );

        if ($rewritten === null || str_contains($rewritten, 'document(')) {
            throw new RuntimeException("Unhandled document() predicate shape in test: $test");
        }

        return $rewritten;
    }

    /**
     * Produce an XPath 1.0 string literal that safely wraps arbitrary content.
     * Falls back to `concat()` when the value contains both quote flavors.
     */
    public static function xpathLit(string $s): string
    {
        if (!str_contains($s, "'")) {
            return "'$s'";
        }
        if (!str_contains($s, '"')) {
            return "\"$s\"";
        }
        $parts = explode("'", $s);
        return 'concat(' . implode(",\"'\",", array_map(fn(string $p): string => "'$p'", $parts)) . ')';
    }
}
