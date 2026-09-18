<?php

/**
 * SchematronParser - reads a schematron document into structured objects.
 *
 * Port of oe-cda-schematron/parseSchematron.js. Extracts:
 *  - namespace prefix => uri map from `<sch:ns>` declarations
 *  - pattern => rule => assertions/extensions tree
 *  - phase-based error/warning levels
 *  - `<sch:let>` variable declarations, merged schema -> pattern -> rule
 *
 * Uses `local-name()` XPath queries so the schematron element prefix does not
 * matter.
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
use DOMNodeList;
use DOMXPath;
use RuntimeException;

final class SchematronParser
{
    public function parse(string $schematronXml): ParsedSchematron
    {
        $doc = new DOMDocument();
        $prevErrorMode = libxml_use_internal_errors(true);
        try {
            $ok = $doc->loadXML($schematronXml, LIBXML_PARSEHUGE);
            libxml_clear_errors();
        } finally {
            libxml_use_internal_errors($prevErrorMode);
        }
        if (!$ok) {
            throw new RuntimeException('failed to parse schematron document');
        }
        $xp = new DOMXPath($doc);

        $namespaceMap = $this->extractNamespaces($xp);
        $patternLevelMap = $this->extractPatternLevels($xp);
        $schemaVariables = $this->directVariables($doc->documentElement);

        /** @var array<string, list<string>> $patternRuleMap */
        $patternRuleMap = [];
        /** @var array<string, ParsedRule> $ruleMap */
        $ruleMap = [];
        $anonymousRuleSeq = 0;
        foreach (self::elements($xp->query('//*[local-name()="pattern"]')) as $pattern) {
            $patternId = $pattern->getAttribute('id');
            $defaultLevel = $patternLevelMap[$patternId] ?? 'warning';
            $patternVariables = $schemaVariables;
            foreach ($this->directVariables($pattern) as $name => $value) {
                $patternVariables[$name] = $value;
            }
            $patternRuleMap[$patternId] = [];
            foreach (self::elements($xp->query('./*[local-name()="rule"]', $pattern)) as $rule) {
                // A rule without an id cannot be addressed by <sch:extends>, but it still
                // has to occupy a distinct slot - keying every id-less rule on '' silently
                // drops all but the last one.
                $ruleId = $rule->getAttribute('id');
                if ($ruleId === '') {
                    $ruleId = sprintf('__anon_rule_%d__', $anonymousRuleSeq++);
                }
                $patternRuleMap[$patternId][] = $ruleId;
                $ctx = $rule->getAttribute('context');

                $ruleVariables = $patternVariables;
                foreach ($this->directVariables($rule) as $name => $value) {
                    $ruleVariables[$name] = $value;
                }

                $ruleMap[$ruleId] = new ParsedRule(
                    abstract: in_array($rule->getAttribute('abstract'), ['true', 'yes'], true),
                    context: $ctx !== '' ? $ctx : null,
                    items: $this->collectItems($rule, $defaultLevel),
                    variables: $ruleVariables,
                );
            }
        }

        return new ParsedSchematron($namespaceMap, $patternRuleMap, $ruleMap);
    }

    /**
     * @return array<string, string>
     */
    private function extractNamespaces(DOMXPath $xp): array
    {
        $out = [];
        foreach (self::elements($xp->query('//*[local-name()="ns"]')) as $ns) {
            $out[$ns->getAttribute('prefix')] = $ns->getAttribute('uri');
        }
        return $out;
    }

    /**
     * Collect `<sch:let>` declared as a direct child of the given element. Only direct
     * children, so a pattern does not absorb the variables of the rules it contains.
     *
     * @return array<string, string>
     */
    private function directVariables(?DOMElement $parent): array
    {
        $out = [];
        if ($parent === null) {
            return $out;
        }
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'let') {
                $name = $child->getAttribute('name');
                if ($name !== '') {
                    $out[$name] = $child->getAttribute('value');
                }
            }
        }
        return $out;
    }

    /**
     * @return array<string, 'error'|'warning'>
     */
    private function extractPatternLevels(DOMXPath $xp): array
    {
        $out = [];
        foreach (['errors' => 'error', 'warnings' => 'warning'] as $phaseId => $level) {
            $phases = $xp->query('//*[local-name()="phase" and @id=' . DocumentPredicateRewriter::xpathLit($phaseId) . ']');
            if ($phases === false || $phases->length === 0) {
                continue;
            }
            $phase = $phases->item(0);
            if (!($phase instanceof DOMElement)) {
                continue;
            }
            foreach ($phase->childNodes as $child) {
                if ($child instanceof DOMElement && $child->localName === 'active') {
                    $out[$child->getAttribute('pattern')] = $level;
                }
            }
        }
        return $out;
    }

    /**
     * Walk the rule's direct children once so assertions and extensions keep their
     * document order, which decides the order findings are reported in.
     *
     * @return list<ParsedAssertion|ParsedExtension>
     */
    private function collectItems(DOMElement $rule, string $defaultLevel): array
    {
        $out = [];
        foreach ($rule->childNodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            }
            if ($child->localName === 'assert') {
                // textContent, not firstChild: an assert with mixed content (the C-CDA
                // R1.1-compatibility meta-rule has a child element mid-sentence) would
                // otherwise lose every word after the first child, and with it the
                // 'SHALL' that decides whether the finding is an error or a warning.
                $description = $child->textContent;
                $id = $child->getAttribute('id');
                $out[] = new ParsedAssertion(
                    level: $this->classifyLevel($description, $defaultLevel),
                    id: $id !== '' ? $id : null,
                    test: $child->getAttribute('test'),
                    description: $description,
                );
            } elseif ($child->localName === 'extends') {
                $out[] = new ParsedExtension($child->getAttribute('rule'));
            }
        }
        return $out;
    }

    /**
     * Match oe-cda-schematron level classification:
     *   phase-default level overridden to 'error' when description contains 'SHALL'
     *   and either lacks 'SHOULD' or has SHALL appearing before SHOULD.
     *
     * @return 'error'|'warning'
     */
    private function classifyLevel(string $description, string $defaultLevel): string
    {
        $shall = strpos($description, 'SHALL');
        if ($shall === false) {
            /** @var 'error'|'warning' */
            return $defaultLevel;
        }
        $should = strpos($description, 'SHOULD');
        if ($should === false || $shall < $should) {
            return 'error';
        }
        /** @var 'error'|'warning' */
        return $defaultLevel;
    }

    /**
     * Filter a DOMXPath::query() result down to element nodes only, tolerating false returns.
     *
     * @param DOMNodeList<\DOMNameSpaceNode|DOMNode>|false $nodes
     * @return list<DOMElement>
     */
    private static function elements(DOMNodeList|false $nodes): array
    {
        if ($nodes === false) {
            return [];
        }
        $out = [];
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $out[] = $node;
            }
        }
        return $out;
    }
}
