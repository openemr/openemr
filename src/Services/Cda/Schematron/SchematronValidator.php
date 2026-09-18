<?php

/**
 * SchematronValidator - pure-PHP schematron evaluator.
 *
 * Replaces the Node.js oe-schematron-service / oe-cda-schematron sidecar for
 * validating CCDA and QRDA documents. Ports the JS engine's assertion semantics
 * to PHP's native DOMXPath, inlines `<sch:let>` variables, and pre-rewrites
 * `document('voc.xml')` predicates so no runtime XPath extension is required.
 *
 * Behavior parity notes vs oe-cda-schematron:
 *  - Context XPath: prefixed with `//` when it does not start with `/`.
 *  - Rule extension: `<sch:extends rule="X"/>` evaluates X's assertions using
 *    the current rule's context (recursive, cycle-guarded).
 *  - Level: inherited from `<sch:phase id="errors|warnings">`; overridden to
 *    'error' when the assertion description contains 'SHALL' and either lacks
 *    'SHOULD' or has SHALL appearing before SHOULD.
 *  - Ignored path: any test that cannot be evaluated lands in `ignored` with a
 *    generic errorMessage, rather than being surfaced as an error or warning.
 *
 * Divergences from the JS engine, both of which turn historically-ignored
 * assertions into real pass/fail results:
 *  - `document('voc.xml')/...` predicates are evaluated via the injected
 *    VocabularyLookup instead of being skipped.
 *  - `<sch:let>` variables are inlined by XPathVariableExpander instead of
 *    failing evaluation as undefined XPath variables.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMNode;
use DOMXPath;
use RuntimeException;

final readonly class SchematronValidator
{
    /**
     * Stable message used for assertions we could not evaluate. Matches the JS
     * engine's "Assertion skipped or malformed." tone and deliberately avoids
     * exposing exception messages, which may leak internal detail. It is the
     * only message written to the ignored bucket - callers key off the bucket,
     * not off a reason string.
     */
    private const IGNORED_MESSAGE = 'Assertion skipped or malformed.';

    private DocumentPredicateRewriter $rewriter;
    private XPathVariableExpander $expander;

    public function __construct(
        VocabularyLookup $vocabulary,
        private bool $includeWarnings = false,
        private int $xmlSnippetMaxLength = 200,
    ) {
        $this->rewriter = new DocumentPredicateRewriter($vocabulary);
        $this->expander = new XPathVariableExpander();
    }

    public function validate(string $targetXml, string $schematronXml): ValidationResult
    {
        $schematron = (new SchematronParser())->parse($schematronXml);

        $doc = new DOMDocument();
        $prevErrorMode = libxml_use_internal_errors(true);
        try {
            $ok = $doc->loadXML($targetXml, LIBXML_PARSEHUGE);
            libxml_clear_errors();
        } finally {
            libxml_use_internal_errors($prevErrorMode);
        }
        if (!$ok) {
            throw new RuntimeException('failed to parse target XML');
        }

        $xpath = new DOMXPath($doc);
        foreach ($schematron->namespaceMap as $prefix => $uri) {
            $xpath->registerNamespace($prefix, $uri);
        }

        $errors = [];
        $warnings = [];
        $ignored = [];

        /** @var array<string, list<DOMNode>> $contextCache */
        $contextCache = [];

        foreach ($schematron->patternRuleMap as $patternId => $ruleIds) {
            foreach ($ruleIds as $ruleId) {
                // A pattern can name a rule the schematron never defines; that is a
                // defect in the .sch, not a reason to fatal on an IG bump.
                $rule = $schematron->ruleMap[$ruleId] ?? null;
                if ($rule === null || $rule->abstract) {
                    continue;
                }
                $checked = $this->checkRule($ruleId, null, $doc, $xpath, $schematron->ruleMap, $contextCache, []);
                foreach ($checked as $item) {
                    $base = [
                        'type' => $item['type'],
                        'test' => $item['test'],
                        'simplifiedTest' => $item['simplifiedTest'],
                        'description' => $item['description'],
                        'patternId' => $patternId,
                        'ruleId' => $ruleId,
                        'assertionId' => $item['assertionId'],
                        'context' => $rule->context,
                    ];
                    $outcome = $item['results'];
                    if (!is_array($outcome)) {
                        continue;
                    }
                    if (($outcome['ignored'] ?? false) === true) {
                        $errorMessage = $outcome['errorMessage'] ?? '';
                        $ignored[] = $base + ['errorMessage' => is_string($errorMessage) ? $errorMessage : ''];
                        continue;
                    }
                    foreach ($outcome as $r) {
                        if (!is_array($r) || ($r['result'] ?? null) === true) {
                            continue;
                        }
                        $entry = $base + [
                            'line' => $r['line'] ?? null,
                            'path' => $r['path'] ?? '',
                            'xml' => $r['xml'] ?? null,
                        ];
                        if ($item['type'] === 'error') {
                            $errors[] = $entry;
                        } else {
                            $warnings[] = $entry;
                        }
                    }
                }
            }
        }

        return new ValidationResult($errors, $warnings, $ignored);
    }

    /**
     * @param array<string, ParsedRule> $ruleMap
     * @param array<string, list<DOMNode>> $contextCache
     * @param-out array<string, list<DOMNode>> $contextCache
     * @param list<string> $extendsPath rule ids already on the extends chain, to stop a cycle
     * @return list<array<string, mixed>>
     */
    private function checkRule(
        string $ruleId,
        ?string $contextOverride,
        DOMDocument $doc,
        DOMXPath $xpath,
        array $ruleMap,
        array &$contextCache,
        array $extendsPath,
    ): array {
        $results = [];
        // A <sch:extends> naming a rule the schematron never defines would otherwise
        // skip the inherited assertions and report a clean result. Fail loudly:
        // CdaValidateDocuments turns a thrown validation into a visible finding.
        $rule = $ruleMap[$ruleId] ?? null;
        if ($rule === null) {
            throw new RuntimeException("Undefined schematron rule: $ruleId");
        }
        $context = $contextOverride ?? $rule->context;

        $cacheKey = $context ?? '__doc__';
        if (!isset($contextCache[$cacheKey])) {
            if ($context !== null) {
                $ctxQuery = str_starts_with($context, '/') ? $context : '//' . $context;
                $prevErrorMode = libxml_use_internal_errors(true);
                try {
                    libxml_clear_errors();
                    $sel = $xpath->query($ctxQuery);
                    libxml_clear_errors();
                } finally {
                    libxml_use_internal_errors($prevErrorMode);
                }
                $contextCache[$cacheKey] = self::toNodeList($sel);
            } else {
                $contextCache[$cacheKey] = [$doc];
            }
        }
        $selected = $contextCache[$cacheKey];

        foreach ($rule->items as $item) {
            if ($item instanceof ParsedAssertion) {
                $originalTest = $item->test;
                try {
                    // The document() rewrite does not depend on the context node, so it
                    // happens once here. Variable expansion does depend on it and runs
                    // per node inside testAssertion().
                    $test = $this->rewriter->rewrite($originalTest);
                    $variables = array_map(
                        $this->rewriter->rewrite(...),
                        $rule->variables,
                    );
                } catch (RuntimeException | DOMException) {
                    $results[] = [
                        'type' => $item->level,
                        'assertionId' => $item->id,
                        'test' => $originalTest,
                        'simplifiedTest' => null,
                        'description' => $item->description,
                        'results' => ['ignored' => true, 'errorMessage' => self::IGNORED_MESSAGE],
                    ];
                    continue;
                }
                $simplified = $originalTest !== $test ? $test : null;

                if ($item->level === 'error' || $this->includeWarnings) {
                    $results[] = [
                        'type' => $item->level,
                        'assertionId' => $item->id,
                        'test' => $originalTest,
                        'simplifiedTest' => $simplified,
                        'description' => $item->description,
                        'results' => $this->testAssertion($test, $variables, $selected, $xpath),
                    ];
                }
            } else {
                // A rule that extends itself, directly or through a chain, would
                // otherwise recurse until the stack runs out.
                if (in_array($item->rule, $extendsPath, true) || $item->rule === $ruleId) {
                    continue;
                }
                $nextPath = $extendsPath;
                $nextPath[] = $ruleId;
                foreach ($this->checkRule($item->rule, $context, $doc, $xpath, $ruleMap, $contextCache, $nextPath) as $r) {
                    $results[] = $r;
                }
            }
        }
        return $results;
    }

    /**
     * @param array<string, string> $variables
     * @param list<DOMNode> $selected
     * @return list<array{result: bool, line: ?int, path: string, xml: ?string}>|array{ignored: true, errorMessage: string}
     */
    private function testAssertion(string $test, array $variables, array $selected, DOMXPath $xpath): array
    {
        $results = [];
        foreach ($selected as $node) {
            try {
                // Per ISO 19757-3 a rule-scoped <sch:let> is calculated against the
                // rule's context node, so this resolves once per selected node.
                $nodeTest = $this->expander->expand($test, $variables, $xpath, $node);
                $prevErrorMode = libxml_use_internal_errors(true);
                try {
                    // Clear before evaluating, not only after: libxml_use_internal_errors()
                    // does not empty the buffer, and a stale entry would make a legitimately
                    // false assertion look like a broken expression and vanish into ignored.
                    libxml_clear_errors();
                    $result = $xpath->evaluate('boolean(' . $nodeTest . ')', $node);
                    $lastError = libxml_get_last_error();
                    libxml_clear_errors();
                } finally {
                    libxml_use_internal_errors($prevErrorMode);
                }
                if ($result === false && $lastError !== false) {
                    return ['ignored' => true, 'errorMessage' => self::IGNORED_MESSAGE];
                }
                if (!is_bool($result)) {
                    return ['ignored' => true, 'errorMessage' => self::IGNORED_MESSAGE];
                }
                // Only a failing assertion is reported, so only a failing assertion needs
                // its line, path and XML snippet built.
                if ($result) {
                    $results[] = ['result' => true, 'line' => null, 'path' => '', 'xml' => null];
                    continue;
                }
                $line = null;
                $xmlSnippet = null;
                if ($node instanceof DOMElement) {
                    $lineNo = $node->getLineNo();
                    $line = $lineNo > 0 ? $lineNo : null;
                    $ownerDoc = $node->ownerDocument;
                    if ($ownerDoc !== null) {
                        $snippet = $ownerDoc->saveXML($node);
                        if (is_string($snippet)) {
                            $xmlSnippet = strlen($snippet) > $this->xmlSnippetMaxLength
                                ? substr($snippet, 0, $this->xmlSnippetMaxLength) . '...'
                                : $snippet;
                        }
                    }
                }
                $results[] = [
                    'result' => false,
                    'line' => $line,
                    'path' => $this->buildXPath($node),
                    'xml' => $xmlSnippet,
                ];
            } catch (RuntimeException | DOMException) {
                return ['ignored' => true, 'errorMessage' => self::IGNORED_MESSAGE];
            }
        }
        return $results;
    }

    /**
     * Build a positional path (/tag[N]/tag[M]/...) matching oe-cda-schematron's getXPath output.
     */
    private function buildXPath(DOMNode $node): string
    {
        $parts = [];
        $cur = $node;
        while ($cur !== null && !($cur instanceof DOMDocument)) {
            if ($cur->nodeType === XML_ELEMENT_NODE) {
                $count = 1;
                $sib = $cur->previousSibling;
                while ($sib !== null) {
                    if ($sib->nodeType === XML_ELEMENT_NODE && $sib->nodeName === $cur->nodeName) {
                        $count++;
                    }
                    $sib = $sib->previousSibling;
                }
                array_unshift($parts, $cur->nodeName . '[' . $count . ']');
            }
            $cur = $cur->parentNode;
        }
        return '/' . implode('/', $parts);
    }

    /**
     * @param \DOMNodeList<\DOMNameSpaceNode|DOMNode>|false $nodes
     * @return list<DOMNode>
     */
    private static function toNodeList(\DOMNodeList|false $nodes): array
    {
        if ($nodes === false) {
            return [];
        }
        $out = [];
        foreach ($nodes as $node) {
            if ($node instanceof DOMNode) {
                $out[] = $node;
            }
        }
        return $out;
    }
}
