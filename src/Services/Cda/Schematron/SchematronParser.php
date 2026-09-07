<?php

/**
 * SchematronParser - reads a schematron document into structured objects.
 *
 * Port of oe-cda-schematron/parseSchematron.js. Extracts:
 *  - namespace prefix => uri map from `<sch:ns>` declarations
 *  - pattern => rule => assertions/extensions tree
 *  - phase-based error/warning levels
 *
 * Uses `local-name()` XPath queries so the schematron element prefix does not
 * matter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use DOMXPath;
use RuntimeException;

final class SchematronParser
{
    public function parse(string $schematronXml): ParsedSchematron
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $ok = $doc->loadXML($schematronXml, LIBXML_PARSEHUGE);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        if (!$ok) {
            throw new RuntimeException('failed to parse schematron document');
        }
        $xp = new DOMXPath($doc);

        $namespaceMap = $this->extractNamespaces($xp);
        $patternLevelMap = $this->extractPatternLevels($xp);

        /** @var array<string, list<string>> $patternRuleMap */
        $patternRuleMap = [];
        /** @var array<string, ParsedRule> $ruleMap */
        $ruleMap = [];
        foreach (self::elements($xp->query('//*[local-name()="pattern"]')) as $pattern) {
            $patternId = $pattern->getAttribute('id');
            $defaultLevel = $patternLevelMap[$patternId] ?? 'warning';
            $patternRuleMap[$patternId] = [];
            foreach (self::elements($xp->query('./*[local-name()="rule"]', $pattern)) as $rule) {
                $ruleId = $rule->getAttribute('id');
                $patternRuleMap[$patternId][] = $ruleId;
                $ctx = $rule->getAttribute('context');
                $ruleMap[$ruleId] = new ParsedRule(
                    abstract: in_array($rule->getAttribute('abstract'), ['true', 'yes'], true),
                    context: $ctx !== '' ? $ctx : null,
                    items: $this->collectItems($rule, $defaultLevel, $xp),
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
     * @return list<ParsedAssertion|ParsedExtension>
     */
    private function collectItems(DOMElement $rule, string $defaultLevel, DOMXPath $xp): array
    {
        $out = [];
        foreach (self::elements($xp->query('./*[local-name()="assert"]', $rule)) as $assert) {
            $description = $assert->firstChild instanceof DOMText ? $assert->firstChild->data : '';
            $out[] = new ParsedAssertion(
                level: $this->classifyLevel($description, $defaultLevel),
                id: $assert->getAttribute('id') !== '' ? $assert->getAttribute('id') : null,
                test: $assert->getAttribute('test'),
                description: $description,
            );
        }
        foreach (self::elements($xp->query('./*[local-name()="extends"]', $rule)) as $ext) {
            $out[] = new ParsedExtension($ext->getAttribute('rule'));
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
