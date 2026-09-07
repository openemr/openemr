<?php

/**
 * SchematronParser isolated test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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
        // Parser lists all assertions first, then extends (matches oe-cda-schematron order).
        $extensions = array_values(array_filter(
            $out->ruleMap['r-concrete']->items,
            fn(object $i): bool => $i instanceof ParsedExtension,
        ));
        self::assertCount(1, $extensions);
        self::assertSame('r-abstract', $extensions[0]->rule);
    }

    public function testMalformedSchematronThrows(): void
    {
        $this->expectException(RuntimeException::class);
        (new SchematronParser())->parse('this is not xml');
    }
}
