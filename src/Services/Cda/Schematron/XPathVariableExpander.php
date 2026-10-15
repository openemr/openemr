<?php

/**
 * XPathVariableExpander - resolves schematron `<sch:let>` variables into literals.
 *
 * PHP's DOMXPath implements XPath 1.0 with no API for binding variables, so `$name`
 * references in a schematron test cannot be evaluated directly - libxml raises
 * "Undefined variable" and the assertion is lost to the ignored bucket.
 *
 * ISO/IEC 19757-3 defines a variable as a *constant value* "evaluated within the
 * parent schema, phase, pattern or rule and scoped within" it: a rule-scoped
 * `<sch:let>` is calculated once against the rule's context node, and the value is
 * then substituted into the test. So this class evaluates each definition against
 * the context node and substitutes the resulting value as an XPath literal.
 *
 *   <sch:rule context="//cda:templateId[@extension]">
 *     <sch:let name="root" value="@root"/>
 *     <sch:assert test="../cda:templateId[(@root=$root) and not(@extension)]"/>
 *
 * Substituting the *expression* instead would inline `@root` into the predicate,
 * where it is re-evaluated against each candidate sibling and the comparison
 * degenerates into the tautology `@root=@root` - the assertion would then pass for
 * a sibling carrying a different root, silently dropping a real finding.
 * Parentheses do not help: a predicate changes the context node, not precedence.
 *
 * Definitions may reference other definitions (the QRDA NPI checksum builds `$sum`
 * out of `$s` and `$n`), so a definition is itself expanded against the same
 * context node before being evaluated. Undefined names and reference cycles throw,
 * which surfaces the assertion as ignored rather than evaluated incorrectly.
 *
 * Known limitations, both of which throw rather than guess:
 *  - A node-set of more than one node has no faithful XPath 1.0 literal form.
 *  - Substitution is textual, so a literal '$' inside a quoted XPath string would
 *    be read as a variable reference. No test in the shipped ccda, qrda1 or qrda3
 *    schematrons contains one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

use DOMNode;
use DOMNodeList;
use DOMXPath;
use RuntimeException;

final class XPathVariableExpander
{
    /**
     * An expression selecting nothing: the document root has no parent. Used for a
     * variable that evaluated to an empty node-set, because an empty node-set and an
     * empty string behave differently - `$empty != 'x'` is false for the node-set
     * and true for the string.
     */
    private const EMPTY_NODE_SET = '(/..)';

    /**
     * Matches a variable reference. XML names admit '-' and '.', so those belong in
     * the name class or `$NPI` would match inside `$NPI-Count`.
     */
    private const REFERENCE = '/\$([A-Za-z_][A-Za-z0-9_.\-]*)/';

    /**
     * Resolve every `$name` in $test to a literal.
     *
     * Rule-scoped definitions are evaluated against $context, the rule's context node.
     * Schema- and pattern-scoped definitions are evaluated against $documentContext,
     * because ISO/IEC 19757-3 5.4.5 says a let that is not a child of a rule "is
     * calculated with the context of the instance document root". A rule-scoped name
     * shadows a document-scoped one.
     *
     * @param array<string, string> $variables rule-scoped name => defining expression
     * @param array<string, string> $documentVariables schema- and pattern-scoped
     */
    public function expand(
        string $test,
        array $variables,
        DOMXPath $xpath,
        DOMNode $context,
        array $documentVariables = [],
        ?DOMNode $documentContext = null,
    ): string {
        if (!str_contains($test, '$')) {
            return $test;
        }

        /** @var array<string, string> $resolved */
        $resolved = [];
        return $this->substitute(
            $test,
            $variables,
            $documentVariables,
            $xpath,
            $context,
            $documentContext ?? $context,
            $resolved,
            [],
        );
    }

    /**
     * @param array<string, string> $variables rule-scoped definitions
     * @param array<string, string> $documentVariables schema- and pattern-scoped definitions
     * @param DOMNode $context node rule-scoped definitions resolve against
     * @param DOMNode $documentContext node document-scoped definitions resolve against
     * @param array<string, string> $resolved memo of name => literal, per expand() call
     * @param-out array<string, string> $resolved
     * @param list<string> $resolving names currently being resolved, to catch a cycle
     */
    private function substitute(
        string $expression,
        array $variables,
        array $documentVariables,
        DOMXPath $xpath,
        DOMNode $context,
        DOMNode $documentContext,
        array &$resolved,
        array $resolving,
    ): string {
        if (!str_contains($expression, '$')) {
            return $expression;
        }

        $out = preg_replace_callback(
            self::REFERENCE,
            function (array $m) use (
                $variables,
                $documentVariables,
                $xpath,
                $context,
                $documentContext,
                &$resolved,
                $resolving
            ): string {
                $name = $m[1];
                if (isset($resolved[$name])) {
                    return $resolved[$name];
                }
                // Rule scope first: an inner declaration shadows an outer one. Whichever
                // scope supplies the definition also supplies the node it is evaluated
                // against, and nested references inherit that scope.
                if (isset($variables[$name])) {
                    $definitionSource = $variables[$name];
                    $definitionContext = $context;
                } elseif (isset($documentVariables[$name])) {
                    $definitionSource = $documentVariables[$name];
                    $definitionContext = $documentContext;
                } else {
                    throw new RuntimeException("Undefined schematron variable: \$$name");
                }
                if (in_array($name, $resolving, true)) {
                    throw new RuntimeException("Cyclic schematron variable definition: \$$name");
                }
                $nested = $resolving;
                $nested[] = $name;
                // Both contexts pass through unchanged: each name inside this definition
                // picks its own scope again. Only the evaluation below is pinned to the
                // scope that supplied this definition.
                $definition = $this->substitute(
                    $definitionSource,
                    $variables,
                    $documentVariables,
                    $xpath,
                    $context,
                    $documentContext,
                    $resolved,
                    $nested,
                );
                $literal = $this->toLiteral($this->evaluate($definition, $xpath, $definitionContext), $name);
                $resolved[$name] = $literal;
                return $literal;
            },
            $expression,
        );

        if ($out === null) {
            throw new RuntimeException('Variable expansion failed for expression: ' . $expression);
        }
        return $out;
    }

    /**
     * Evaluate a variable definition against the rule context node.
     *
     * DOMXPath::evaluate() returns false both for a malformed expression and for a
     * legitimately false boolean, so the libxml buffer is cleared first and checked
     * after to tell them apart.
     *
     * @return bool|float|string|DOMNodeList<DOMNode>
     */
    private function evaluate(string $definition, DOMXPath $xpath, DOMNode $context): bool|float|string|DOMNodeList
    {
        $prevErrorMode = libxml_use_internal_errors(true);
        try {
            libxml_clear_errors();
            $value = $xpath->evaluate($definition, $context);
            $lastError = libxml_get_last_error();
            libxml_clear_errors();
        } finally {
            libxml_use_internal_errors($prevErrorMode);
        }

        if ($value === false && $lastError !== false) {
            throw new RuntimeException('Could not evaluate schematron variable definition');
        }
        if (is_bool($value) || is_float($value) || is_string($value) || $value instanceof DOMNodeList) {
            return $value;
        }
        if (is_int($value)) {
            return (float) $value;
        }
        throw new RuntimeException('Unsupported schematron variable value');
    }

    /**
     * @param bool|float|string|DOMNodeList<DOMNode> $value
     */
    private function toLiteral(bool|float|string|DOMNodeList $value, string $name): string
    {
        if (is_bool($value)) {
            return $value ? 'true()' : 'false()';
        }
        if (is_float($value)) {
            return self::numberLiteral($value);
        }
        if (is_string($value)) {
            return '(' . DocumentPredicateRewriter::xpathLit($value) . ')';
        }

        if ($value->length === 0) {
            return self::EMPTY_NODE_SET;
        }
        if ($value->length > 1) {
            // Inlining only the first node would change what `=` and `!=` mean over the
            // set, so report the assertion as unevaluable instead of answering wrongly.
            throw new RuntimeException("Schematron variable \$$name selected more than one node");
        }
        // length is exactly 1 here, so item(0) cannot be null.
        $node = $value->item(0);
        return '(' . DocumentPredicateRewriter::xpathLit($node->nodeValue ?? '') . ')';
    }

    /**
     * XPath 1.0 has no exponent notation and no NaN or infinity literal, so those are
     * produced by expression rather than written out.
     */
    private static function numberLiteral(float $value): string
    {
        if (is_nan($value)) {
            return "number('NaN')";
        }
        if (is_infinite($value)) {
            return $value > 0 ? '(1 div 0)' : '(-1 div 0)';
        }
        if ($value === floor($value) && abs($value) < 1.0e15) {
            return '(' . number_format($value, 0, '.', '') . ')';
        }
        return '(' . rtrim(rtrim(sprintf('%.12F', $value), '0'), '.') . ')';
    }
}
