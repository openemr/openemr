<?php

/**
 * Vital signs imported from a C-CDA or QRDA document reach the Vitals form in its own
 * units. Each test reads a document fragment through XmlExtended, as the import does,
 * so the value and unit attributes arrive in the shape real documents give them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\CdaTemplateParse;
use OpenEMR\Services\Cda\XmlExtended;
use OpenEMR\Services\CodeTypesService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CdaVitalSignUnitsTest extends TestCase
{
    private CdaTemplateParse $parser;
    /** @var ReflectionClass<CdaTemplateParse> */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(CdaTemplateParse::class);
        $this->parser = $this->reflection->newInstanceWithoutConstructor();

        $codeService = $this->createMock(CodeTypesService::class);
        $codeService->method('resolveCode')
            ->willReturnCallback(fn(?string $code, ?string $codeType, ?string $codeText): array => [
                'code' => $code ?? '',
                'formatted_code' => ($code ?? '') . ':' . ($codeType ?? ''),
                'code_text' => $codeText ?? '',
            ]);

        $this->reflection->getProperty('templateData')->setValue($this->parser, []);
        $this->reflection->getProperty('codeService')->setValue($this->parser, $codeService);
        $this->reflection->getProperty('ed')->setValue($this->parser, $this->createMock(EventDispatcherInterface::class));
        $this->reflection->getProperty('is_qrda_import')->setValue($this->parser, false);
        $this->reflection->getProperty('currentOid')->setValue($this->parser, '');
    }

    public function testMetricVitalsAreStoredInTheVitalsFormUnits(): void
    {
        $this->parser->fetchVitalSignData($this->organizer([
            ['8302-2', '170', 'cm'],
            ['3141-9', '70', 'kg'],
            ['8310-5', '37', 'Cel'],
            ['8287-5', '35', 'cm'],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertSame(66.929134, $vital['height']);
        $this->assertSame(154.323584, $vital['weight']);
        $this->assertSame(98.6, $vital['temperature']);
        $this->assertSame(13.779528, $vital['head_circ']);
    }

    public function testVitalsAlreadyInTheVitalsFormUnitsKeepTheirValues(): void
    {
        $this->parser->fetchVitalSignData($this->organizer([
            ['8302-2', '67', '[in_i]'],
            ['3141-9', '154.5', '[lb_av]'],
            ['8310-5', '98.6', '[degF]'],
        ]));

        // Compared as numbers: the value may come back as a float instead of the string sent.
        $vital = $this->vitalSign(1);
        $this->assertEquals(67, $vital['height']);
        $this->assertEquals(154.5, $vital['weight']);
        $this->assertEquals(98.6, $vital['temperature']);
    }

    public function testVitalsStoredWithTheirOwnUnitsAreNotConverted(): void
    {
        $this->parser->fetchVitalSignData($this->organizer([
            ['39156-5', '24.2', 'kg/m2'],
            ['8867-4', '72', '/min'],
            ['2710-2', '98', '%'],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertSame('24.2', $vital['BMI']);
        $this->assertSame('72', $vital['pulse']);
        $this->assertSame('98', $vital['oxygen_saturation']);
    }

    public function testAVitalInAUnitThatDoesNotConvertIsLeftOut(): void
    {
        $this->parser->fetchVitalSignData($this->organizer([
            ['3141-9', '160', '[oz_av]'],
            ['8302-2', '170', 'cm'],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertNull($vital['weight']);
        $this->assertSame(66.929134, $vital['height']);
    }

    public function testAQrdaPhysicalExamInKilogramsIsStoredInPounds(): void
    {
        $this->reflection->getProperty('currentOid')->setValue($this->parser, '2.16.840.1.113883.10.20.24.3.59');
        $entry = $this->fromXml(<<<'XML_WRAP'
        <entry xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <observation classCode="OBS" moodCode="EVN">
            <templateId root="2.16.840.1.113883.10.20.24.3.59" extension="2019-12-01"/>
            <id root="1.3.6.1.4.1.115" extension="exam-1"/>
            <code code="29463-7" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Body weight"/>
            <text>Body weight</text>
            <statusCode code="completed"/>
            <effectiveTime value="20260301093000"/>
            <value xsi:type="PQ" value="70" unit="kg"/>
          </observation>
        </entry>
        XML_WRAP);

        $this->parser->fetchPhysicalExamPerformedData($entry);

        $this->assertSame(154.323584, $this->vitalSign(1)['weight']);
        // The exam's own copy keeps the value and unit it was sent in.
        $observation = $this->field('observation_preformed', 1);
        $this->assertSame('70', $observation['result_code_text']);
        $this->assertSame('kg', $observation['result_code_unit']);
    }

    /**
     * A vital signs organizer with one component per [LOINC code, value, unit].
     *
     * @param list<array{string, string, string}> $components
     * @return array<string, mixed>
     */
    private function organizer(array $components): array
    {
        $xml = '';
        foreach ($components as [$code, $value, $unit]) {
            $xml .= <<<XML
                <component>
                  <observation classCode="OBS" moodCode="EVN">
                    <templateId root="2.16.840.1.113883.10.20.22.4.27" extension="2014-06-09"/>
                    <code code="$code" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC"/>
                    <statusCode code="completed"/>
                    <effectiveTime value="20260301093000"/>
                    <value xsi:type="PQ" value="$value" unit="$unit"/>
                  </observation>
                </component>
                XML;
        }
        return $this->fromXml(<<<XML
            <entry xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
              <organizer classCode="CLUSTER" moodCode="EVN">
                <templateId root="2.16.840.1.113883.10.20.22.4.26" extension="2015-08-01"/>
                <id root="1.3.6.1.4.1.115" extension="vitals-1"/>
                <code code="46680005" codeSystem="2.16.840.1.113883.6.96" codeSystemName="SNOMED CT"/>
                <statusCode code="completed"/>
                <effectiveTime value="20260301093000"/>
                $xml
              </organizer>
            </entry>
            XML);
    }

    /**
     * @return array<string, mixed>
     */
    private function fromXml(string $xml): array
    {
        $parsed = (new XmlExtended())->fromString($xml);
        $this->assertIsArray($parsed);
        /** @var array<string, mixed> $parsed */
        return $parsed;
    }

    /**
     * @return array<string, mixed>
     */
    private function vitalSign(int $index): array
    {
        return $this->field('vital_sign', $index);
    }

    /**
     * @return array<string, mixed>
     */
    private function field(string $entity, int $index): array
    {
        $templateData = $this->reflection->getProperty('templateData')->getValue($this->parser);
        $this->assertIsArray($templateData);
        $fields = $templateData['field_name_value_array'] ?? null;
        $this->assertIsArray($fields);
        $records = $fields[$entity] ?? null;
        $this->assertIsArray($records, "no $entity records");
        $record = $records[$index] ?? null;
        $this->assertIsArray($record, "no $entity record $index");
        /** @var array<string, mixed> $record */
        return $record;
    }
}
