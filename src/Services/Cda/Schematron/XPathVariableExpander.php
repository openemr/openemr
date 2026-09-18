<?php

/**
 * XPathVariableExpander - inlines schematron `<sch:let>` variables into a test expression.
 *
 * PHP's DOMXPath implements XPath 1.0 with no API for binding variables, so `$name`
 * references in a schematron test cannot be evaluated directly - libxml raises
 * "Undefined variable" and the assertion is lost to the ignored bucket.
 *
 * This class substitutes each `$name` with its parenthesized defining expression:
 *
 *   <sch:let name="s" value="normalize-space(@extension)"/>
 *   <sch:assert test="string-length($s) = 10"/>
 *       becomes    string-length((normalize-space(@extension))) = 10
 *
 * Substituting the expression rather than an evaluated value preserves node-set
 * semantics and keeps evaluation anchored to the assertion's context node, which is
 * what `<sch:let>` means for rule-scoped variables. Definitions may reference other
 * definitions (the QRDA NPI checksum builds `$sum` out of `$s` and `$n`), so
 * substitution repeats until the expression is free of variables.
 *
 * Throws on an undefined variable or a reference cycle, which surfaces the assertion
 * as ignored rather than silently evaluating something wrong.
 *
 * Known limitation: substitution is textual, so a literal '$' inside a quoted XPath
 * string would be treated as a variable reference. No test in the shipped ccda,
 * qrda1 or qrda3 schematrons contains one; revisit if that stops being true.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

use RuntimeException;

final readonly class XPathVariableExpander
{
    /**
     * Substitution passes allowed before a definition chain is treated as cyclic.
     * The deepest chain in the shipped schematrons is three ($sum -> $n -> $s).
     */
    private const MAX_PASSES = 20;

    /**
     * Matches a variable reference, refusing to stop mid-name. XML names admit
     * '-' and '.', so those are part of the lookahead or `$NPI` would match
     * inside `$NPI-Count`.
     */
    private const REFERENCE = '/\$([A-Za-z_][A-Za-z0-9_.\-]*)/';

    /**
     * @param array<string, string> $variables name => defining XPath expression
     */
    public function expand(string $test, array $variables): string
    {
        if (!str_contains($test, '$')) {
            return $test;
        }

        $current = $test;
        for ($pass = 0; $pass < self::MAX_PASSES; $pass++) {
            $replaced = preg_replace_callback(
                self::REFERENCE,
                function (array $m) use ($variables): string {
                    $name = $m[1];
                    if (!isset($variables[$name])) {
                        throw new RuntimeException("Undefined schematron variable: \$$name");
                    }
                    return '(' . $variables[$name] . ')';
                },
                $current,
            );
            if ($replaced === null) {
                throw new RuntimeException('Variable expansion failed for test: ' . $test);
            }
            if ($replaced === $current) {
                return $current;
            }
            $current = $replaced;
        }

        throw new RuntimeException('Cyclic schematron variable definition in test: ' . $test);
    }
}
