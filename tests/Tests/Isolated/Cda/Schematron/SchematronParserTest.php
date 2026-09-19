<?php

/**
 * SchematronParser isolated test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Schematron;

use OpenEMR\Services\Cda\Schematron\ParsedAssertion;
use OpenEMR\Services\Cda\Schematron\ParsedExtension;
use OpenEMR\Services\Cda\Schematron\SchematronParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SchematronParserTest extends TestCase
{
    private const SCHEMATRON = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron" xmlns:cda="urn:hl7-org:v3">
    <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
    <sch:ns prefix="voc" uri="http://www.lantanagroup.com/voc"/>
    <sch:phase id="errors">
        <sch:active pattern="p-strict"/>
    </sch:phase>
    <sch:phase id="warnings">
        <sch:active pattern="p-lenient"/>
    </sch:phase>
    <sch:pattern id="p-strict">
        <sch:rule id="r-strict" context="cda:patient">
            <sch:assert id="a-shall" test="cda:name">SHALL contain name.</sch:assert>
            <sch:assert id="a-should" test="cda:birthTime">SHOULD contain birthTime.</sch:assert>
        </sch:rule>
    </sch:pattern>
    <sch:pattern id="p-lenient">
        <sch:rule id="r-lenient" context="cda:encounter">
            <sch:assert id="a-neutral" test="@classCode">has a classCode.</sch:assert>
        </sch:rule>
    </sch:pattern>
    <sch:pattern id="p-with-extends">
        <sch:rule id="r-abstract" abstract="true">
            <sch:assert id="a-abstract-check" test="@code">SHALL have code.</sch:assert>
        </sch:rule>
        <sch:rule id="r-concrete" context="cda:observation">
            <sch:extends rule="r-abstract"/>
            <sch:assert id="a-status" test="cda:statusCode">SHALL have statusCode.</sch:assert>
        </sch:rule>
    </sch:pattern>
    <sch:pattern id="p-vars">
        <sch:let name="docRoot" value="/cda:ClinicalDocument"/>
        <sch:rule id="r-vars" context="cda:id">
            <sch:let name="ext" value="normalize-space(@extension)"/>
            <sch:let name="len" value="string-length($ext)"/>
            <sch:assert id="a-vars" test="$len = 10 and $docRoot">SHALL be ten characters.</sch:assert>
        </sch:rule>
    </sch:pattern>
    <sch:pattern id="p-mixed">
        <sch:rule id="r-mixed" context="cda:templateId">
            <sch:assert id="a-mixed" test="@root">A compatible templateId <sch:emph>must</sch:emph> be present and SHALL carry a root.</sch:assert>
            <sch:assert id="a-should-first" test="@extension">SHOULD carry an extension, though it SHALL never be blank.</sch:assert>
        </sch:rule>
    </sch:pattern>
    <sch:pattern id="p-anonymous">
        <sch:rule context="cda:first">
            <sch:assert id="a-anon-one" test="@a">SHALL have a.</sch:assert>
        </sch:rule>
        <sch:rule context="cda:second">
            <sch:assert id="a-anon-two" test="@b">SHALL have b.</sch:assert>
        </sch:rule>
    </sch:pattern>
</sch:schema>
XML;

    public function testExtractsNamespaces(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        self::assertSame([
            'cda' => 'urn:hl7-org:v3',
            'voc' => 'http://www.lantanagroup.com/voc',
        ], $out->namespaceMap);
    }

    public function testMapsPatternsToRules(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        self::assertSame(['r-strict'], $out->patternRuleMap['p-strict']);
        self::assertSame(['r-lenient'], $out->patternRuleMap['p-lenient']);
        self::assertSame(['r-abstract', 'r-concrete'], $out->patternRuleMap['p-with-extends']);
    }

    public function testRuleContextResolved(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        self::assertSame('cda:patient', $out->ruleMap['r-strict']->context);
    }

    public function testAbstractRuleFlagged(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        self::assertTrue($out->ruleMap['r-abstract']->abstract);
        self::assertFalse($out->ruleMap['r-concrete']->abstract);
    }

    public function testShallAssertionMarkedError(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        $first = $out->ruleMap['r-strict']->items[0];
        self::assertInstanceOf(ParsedAssertion::class, $first);
        self::assertSame('error', $first->level);
        self::assertSame('a-shall', $first->id);
    }

    public function testShouldAssertionUsesPhaseDefault(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        // p-strict is in the errors phase, so default level = error
        $second = $out->ruleMap['r-strict']->items[1];
        self::assertInstanceOf(ParsedAssertion::class, $second);
        self::assertSame('a-should', $second->id);
        self::assertSame('error', $second->level);
    }

    public function testNoShallInWarningPhaseStaysWarning(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        $first = $out->ruleMap['r-lenient']->items[0];
        self::assertInstanceOf(ParsedAssertion::class, $first);
        self::assertSame('warning', $first->level);
    }

    public function testExtendsRecordedAsExtension(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        // Items keep document order, so the extends precedes the assertion here.
        $extensions = array_values(array_filter(
            $out->ruleMap['r-concrete']->items,
            fn(object $i): bool => $i instanceof ParsedExtension,
        ));
        self::assertCount(1, $extensions);
        self::assertSame('r-abstract', $extensions[0]->rule);
    }

    public function testItemsKeepDocumentOrder(): void
    {
        $items = (new SchematronParser())->parse(self::SCHEMATRON)->ruleMap['r-concrete']->items;
        self::assertInstanceOf(ParsedExtension::class, $items[0], 'extends is first in the source');
        self::assertInstanceOf(ParsedAssertion::class, $items[1]);
    }

    public function testRuleVariablesMergeOverPatternScope(): void
    {
        $rule = (new SchematronParser())->parse(self::SCHEMATRON)->ruleMap['r-vars'];
        self::assertSame(
            ['docRoot' => '/cda:ClinicalDocument', 'ext' => 'normalize-space(@extension)', 'len' => 'string-length($ext)'],
            $rule->variables,
        );
    }

    public function testPatternVariablesDoNotLeakIntoOtherPatterns(): void
    {
        $rule = (new SchematronParser())->parse(self::SCHEMATRON)->ruleMap['r-strict'];
        self::assertSame([], $rule->variables);
    }

    public function testMixedContentAssertionKeepsTextAfterChildElement(): void
    {
        $first = (new SchematronParser())->parse(self::SCHEMATRON)->ruleMap['r-mixed']->items[0];
        self::assertInstanceOf(ParsedAssertion::class, $first);
        self::assertStringContainsString('SHALL carry a root', $first->description);
        // p-mixed is in no phase, so the default is warning. The SHALL sits after a
        // child element; reading only the first text node would demote this to a
        // warning and, with includeWarnings off, drop the finding entirely.
        self::assertSame('error', $first->level);
    }

    public function testShouldBeforeShallUnderWarningDefaultStaysWarning(): void
    {
        $second = (new SchematronParser())->parse(self::SCHEMATRON)->ruleMap['r-mixed']->items[1];
        self::assertInstanceOf(ParsedAssertion::class, $second);
        self::assertSame('a-should-first', $second->id);
        // SHOULD precedes SHALL, so the phase default (warning) wins.
        self::assertSame('warning', $second->level);
    }

    public function testRulesWithoutIdsGetDistinctKeys(): void
    {
        $out = (new SchematronParser())->parse(self::SCHEMATRON);
        $ruleIds = $out->patternRuleMap['p-anonymous'];
        self::assertCount(2, $ruleIds);
        self::assertNotSame($ruleIds[0], $ruleIds[1], 'id-less rules must not share a key');
        self::assertSame('cda:first', $out->ruleMap[$ruleIds[0]]->context);
        self::assertSame('cda:second', $out->ruleMap[$ruleIds[1]]->context);
    }

    public function testMalformedSchematronThrows(): void
    {
        $this->expectException(RuntimeException::class);
        (new SchematronParser())->parse('this is not xml');
    }
}
