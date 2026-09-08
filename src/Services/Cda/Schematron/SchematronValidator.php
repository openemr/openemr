<?php

/**
 * SchematronValidator - pure-PHP schematron evaluator.
 *
 * Replaces the Node.js oe-schematron-service / oe-cda-schematron sidecar for
 * validating CCDA and QRDA documents. Ports the JS engine's assertion semantics
 * to PHP's native DOMXPath and pre-rewrites `document('voc.xml')` predicates
 * inline so no runtime XPath extension is required.
 *
 * Behavior parity notes vs oe-cda-schematron:
 *  - Context XPath: prefixed with `//` when it does not start with `/`.
 *  - Rule extension: `<sch:extends rule="X"/>` evaluates X's assertions using
 *    the current rule's context (recursive).
 *  - Level: inherited from `<sch:phase id="errors|warnings">`; overridden to
 *    'error' when the assertion description contains 'SHALL' and either lacks
 *    'SHOULD' or has SHALL appearing before SHOULD.
 *  - Ignored path: any test that throws during evaluation lands in `ignored`
 *    with an errorMessage, rather than being surfaced as an error or warning.
 *
 * Divergence: the JS engine cannot evaluate `document('voc.xml')/...` and
 * silently marks those assertions as ignored. This engine evaluates them via
 * the injected VocabularyLookup, which converts historically-ignored
 * assertions into real pass/fail results.
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
use DOMNode;
use DOMXPath;
use RuntimeException;

final readonly class SchematronValidator
{
    /**
     * Stable message used for assertions we could not evaluate. Matches the JS
     * engine's "Assertion skipped or malformed." tone and deliberately avoids
     * exposing exception messages, which may leak internal detail.
     */
    private const IGNORED_MESSAGE = 'Assertion skipped or malformed.';

    private DocumentPredicateRewriter $rewriter;

    public function __construct(
        VocabularyLookup $vocabulary,
        private bool $includeWarnings = false,
        private int $xmlSnippetMaxLength = 200,
    ) {
        $this->rewriter = new DocumentPredicateRewriter($vocabulary);
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
                $rule = $schematron->ruleMap[$ruleId];
                if ($rule->abstract) {
                    continue;
                }
                $checked = $this->checkRule($ruleId, null, $doc, $xpath, $schematron->ruleMap, $contextCache);
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
     * @return list<array<string, mixed>>
     */
    private function checkRule(
        string $ruleId,
        ?string $contextOverride,
        DOMDocument $doc,
        DOMXPath $xpath,
        array $ruleMap,
        array &$contextCache,
    ): array {
        $results = [];
        $rule = $ruleMap[$ruleId];
        $context = $contextOverride ?? $rule->context;

        $cacheKey = $context ?? '__doc__';
        if (!isset($contextCache[$cacheKey])) {
            if ($context !== null) {
                $ctxQuery = str_starts_with($context, '/') ? $context : '//' . $context;
                libxml_use_internal_errors(true);
                $sel = $xpath->query($ctxQuery);
                libxml_clear_errors();
                libxml_use_internal_errors(false);
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
                    $test = $this->rewriter->rewrite($originalTest);
                } catch (RuntimeException) {
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
                        'results' => $this->testAssertion($test, $selected, $xpath),
                    ];
                }
            } else {
                foreach ($this->checkRule($item->rule, $context, $doc, $xpath, $ruleMap, $contextCache) as $r) {
                    $results[] = $r;
                }
            }
        }
        return $results;
    }

    /**
     * @param list<DOMNode> $selected
     * @return list<array{result: bool, line: ?int, path: string, xml: ?string}>|array{ignored: true, errorMessage: string}
     */
    private function testAssertion(string $test, array $selected, DOMXPath $xpath): array
    {
        $results = [];
        foreach ($selected as $node) {
            try {
                libxml_use_internal_errors(true);
                $result = $xpath->evaluate('boolean(' . $test . ')', $node);
                $lastError = libxml_get_last_error();
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                if ($result === false && $lastError !== false) {
                    return ['ignored' => true, 'errorMessage' => 'xpath evaluation failed'];
                }
                if (!is_bool($result)) {
                    return ['ignored' => true, 'errorMessage' => 'Test returned non-boolean result'];
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
                    'result' => $result,
                    'line' => $line,
                    'path' => $this->buildXPath($node),
                    'xml' => $xmlSnippet,
                ];
            } catch (RuntimeException) {
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
