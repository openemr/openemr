<?php

/**
 * The C-CDA import reads every vital sign in a vital signs organizer: the thirteen
 * OpenEMR's own export writes, and an organizer with a single one.
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
use Psr\Log\NullLogger;
use ReflectionClass;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CdaVitalSignComponentsTest extends TestCase
{
    /** A C-CDA as OpenEMR exports it, with one vital signs organizer of thirteen components. */
    private const OPENEMR_EXPORT = __DIR__ . '/../../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/ccda-output-demo1.xml';

    private CdaTemplateParse $parser;
    /** @var ReflectionClass<CdaTemplateParse> */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(CdaTemplateParse::class);
        $this->parser = $this->reflection->newInstanceWithoutConstructor();
        $this->reflection->getProperty('templateData')->setValue($this->parser, []);
        $this->reflection->getProperty('codeService')->setValue($this->parser, $this->createMock(CodeTypesService::class));
        $this->reflection->getProperty('ed')->setValue($this->parser, $this->createMock(EventDispatcherInterface::class));
        $this->reflection->getProperty('is_qrda_import')->setValue($this->parser, false);
        $this->reflection->getProperty('currentOid')->setValue($this->parser, '');
        $this->reflection->getProperty('logger')->setValue($this->parser, new NullLogger());
    }

    public function testOpenEmrsOwnExportIsReadPastTheNinthVitalSign(): void
    {
        $this->parser->VitalSign($this->vitalSignsSection());

        $vital = $this->vitalSign(1);
        // The first nine components, read before as well.
        $this->assertSame('130', $vital['bps']);
        $this->assertSame('80', $vital['bpd']);
        // Converted to the Vitals form's units, height and temperature come back as numbers.
        $this->assertEquals('70', $vital['height']);
        $this->assertSame('31.6', $vital['BMI']);
        $this->assertSame('60', $vital['pulse']);
        // The tenth to the twelfth, which were dropped; oxygen saturation is exported as 59408-5.
        $this->assertSame('20', $vital['respiration']);
        $this->assertEquals('98', $vital['temperature']);
        $this->assertSame('98', $vital['oxygen_saturation']);
    }

    public function testAnOrganizerWithASingleVitalSignIsRead(): void
    {
        $organizer = (new XmlExtended())->fromString(<<<'XML_WRAP'
            <entry xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
              <organizer classCode="CLUSTER" moodCode="EVN">
                <templateId root="2.16.840.1.113883.10.20.22.4.26" extension="2015-08-01"/>
                <id root="1.3.6.1.4.1.115" extension="vitals-1"/>
                <statusCode code="completed"/>
                <effectiveTime value="20260301093000"/>
                <component>
                  <observation classCode="OBS" moodCode="EVN">
                    <templateId root="2.16.840.1.113883.10.20.22.4.27" extension="2014-06-09"/>
                    <code code="8867-4" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC"/>
                    <statusCode code="completed"/>
                    <effectiveTime value="20260301093000"/>
                    <value xsi:type="PQ" value="72" unit="/min"/>
                  </observation>
                </component>
              </organizer>
            </entry>
            XML_WRAP);
        $this->assertIsArray($organizer);

        $this->parser->fetchVitalSignData($organizer);

        $vital = $this->vitalSign(1);
        $this->assertSame('20260301093000', $vital['date']);
        $this->assertSame('72', $vital['pulse']);
    }

    /**
     * C-CDA allows an observation's time as the start of an interval (effectiveTime/low)
     * instead of a value.
     */
    public function testAVitalSignDatedByTheStartOfAnIntervalIsRead(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime><low value="20260301093000"/></effectiveTime>'],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertSame('20260301093000', $vital['date']);
        $this->assertSame('72', $vital['pulse']);
    }

    public function testAnOrganizerWhoseFirstVitalSignIsDatedByAnIntervalIsRead(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime><low value="20260301093000"/></effectiveTime>'],
            ['9279-1', '16', '/min', '<effectiveTime value="20260301093000"/>'],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertSame('20260301093000', $vital['date']);
        $this->assertSame('72', $vital['pulse']);
        $this->assertSame('16', $vital['respiration']);
    }

    /**
     * Synthea writes all of a patient's vital signs into one organizer, each with its own time
     * (issue #12302). Each date is a record of its own. The first keeps the organizer's id, which
     * finds an entry imported before; a later date gets an id of its own made from it.
     */
    public function testAnOrganizerOfSeveralDatesIsReadAsOneRecordPerDate(): void
    {
        $this->parser->fetchVitalSignData($this->severalDates());

        $first = $this->vitalSign(1);
        $this->assertSame(['20180114093000', '72', '16', 'vitals-1'], [$first['date'], $first['pulse'], $first['respiration'], $first['extension']]);
        $second = $this->vitalSign(2);
        $this->assertSame(['20210120101500', '80', '18'], [$second['date'], $second['pulse'], $second['respiration']]);
        $this->assertSame('1.3.6.1.4.1.115', $second['root']);
        $this->assertIsString($second['extension']);
        $this->assertNotSame('vitals-1', $second['extension']);
        $this->assertLessThanOrEqual(20, strlen($second['extension']), "fits form_vitals.external_id's 20 characters");
    }

    /**
     * Importing the document again finds each date's entry by its id, so it's updated rather
     * than added again.
     */
    public function testEachDateGetsTheSameIdOnEveryImport(): void
    {
        $this->parser->fetchVitalSignData($this->severalDates());
        $firstImport = [$this->vitalSign(1)['extension'], $this->vitalSign(2)['extension']];
        $this->reflection->getProperty('templateData')->setValue($this->parser, []);

        $this->parser->fetchVitalSignData($this->severalDates());

        $this->assertSame($firstImport, [$this->vitalSign(1)['extension'], $this->vitalSign(2)['extension']]);
    }

    /**
     * C-CDA dates an organizer by an interval around its readings, so one visit's readings can
     * be minutes apart. They're one record, dated by the first.
     */
    public function testReadingsMinutesApartOnOneDateAreOneRecord(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20260301093000"/>'],
            ['9279-1', '16', '/min', '<effectiveTime value="20260301093500"/>'],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertSame(['20260301093000', '72', '16', 'vitals-1'], [$vital['date'], $vital['pulse'], $vital['respiration'], $vital['extension']]);
        $this->assertSame(1, $this->vitalSignCount());
    }

    /**
     * A date whose readings the Vitals form doesn't record (pain severity here) isn't saved as an
     * empty form, and the organizer's id goes to the first date that is saved.
     */
    public function testADateWithNothingTheVitalsFormRecordsIsNotSaved(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['72514-3', '3', '{score}', '<effectiveTime value="20180114093000"/>'],
            ['8867-4', '80', '/min', '<effectiveTime value="20210120101500"/>'],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertSame(['20210120101500', '80', 'vitals-1'], [$vital['date'], $vital['pulse'], $vital['extension']]);
        $this->assertSame(1, $this->vitalSignCount());
    }

    public function testAnOrganizerWhoseFirstVitalSignHasNoTimeIsRead(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['9279-1', '16', '/min', ''],
            ['8867-4', '72', '/min', '<effectiveTime value="20260301093000"/>'],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertSame(['20260301093000', '72', '16'], [$vital['date'], $vital['pulse'], $vital['respiration']]);
    }

    /**
     * A blood pressure is read from its two entry relationships; a second copy without values
     * doesn't replace the reading.
     */
    public function testABloodPressureCopyWithoutValuesKeepsTheReading(): void
    {
        $bp = fn(?string $systolic, ?string $diastolic): array => ['observation' => [
            'code' => ['code' => '85354-9'],
            'effectiveTime' => ['value' => '20260301093000'],
            'entryRelationship' => [
                ['observation' => ['value' => ['value' => $systolic]]],
                ['observation' => ['value' => ['value' => $diastolic]]],
            ],
        ]];
        $this->parser->fetchVitalSignData(['organizer' => [
            'id' => ['root' => '1.3.6.1.4.1.115', 'extension' => 'vitals-1'],
            'component' => [$bp('120', '80'), $bp(null, null)],
        ]]);

        $vital = $this->vitalSign(1);
        $this->assertSame(['120', '80'], [$vital['bps'], $vital['bpd']]);
    }

    public function testAVitalSignWithNoTimeOfItsOwnIsReadWithTheFirst(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20260301093000"/>'],
            ['9279-1', '16', '/min', ''],
        ]));

        $vital = $this->vitalSign(1);
        $this->assertSame(['20260301093000', '72', '16'], [$vital['date'], $vital['pulse'], $vital['respiration']]);
        $this->assertSame(1, $this->vitalSignCount());
    }

    /**
     * Oxygen saturation can come as both 2710-2 and 59408-5; a copy without a value keeps the reading.
     */
    public function testAMeasureSentTwiceKeepsItsReading(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['2710-2', '97', '%', '<effectiveTime value="20260301093000"/>'],
            ['59408-5', null, '%', '<effectiveTime value="20260301093000"/>'],
        ]));

        $this->assertSame('97', $this->vitalSign(1)['oxygen_saturation']);
    }

    public function testEachTimeIsReadInTheVitalsFormsUnits(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['3141-9', '70', 'kg', '<effectiveTime value="20180114093000"/>'],
            ['3141-9', '80', 'kg', '<effectiveTime value="20210120101500"/>'],
            ['3141-9', '81', 'cm', '<effectiveTime value="20210120101500"/>'],
        ]));

        $this->assertEqualsWithDelta(154.32, $this->vitalSign(1)['weight'], 0.01);
        // A weight in a unit that doesn't convert is left out and doesn't replace that time's reading.
        $this->assertEqualsWithDelta(176.37, $this->vitalSign(2)['weight'], 0.01);
        $this->assertSame(2, $this->vitalSignCount());
    }

    /**
     * A QRDA Physical Exam, Performed is dated by its relevant period, which can have only a start.
     */
    public function testAQrdaPhysicalExamDatedByTheStartOfItsPeriodIsRead(): void
    {
        $this->parser->fetchPhysicalExamPerformedData([
            'observation' => [
                'code' => ['code' => '8867-4', 'codeSystem' => '2.16.840.1.113883.6.1', 'codeSystemName' => 'LOINC'],
                'text' => 'Heart rate',
                'effectiveTime' => ['low' => ['value' => '20260301093000']],
                'value' => ['value' => '72', 'unit' => '/min'],
            ],
        ]);

        $vital = $this->vitalSign(1);
        $this->assertSame(['20260301093000', '72'], [$vital['date'], $vital['pulse']]);
    }

    /**
     * Pulse and respiration on two dates.
     *
     * @return array<string, mixed>
     */
    private function severalDates(): array
    {
        return $this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20180114093000"/>'],
            ['9279-1', '16', '/min', '<effectiveTime value="20180114093000"/>'],
            ['8867-4', '80', '/min', '<effectiveTime value="20210120101500"/>'],
            ['9279-1', '18', '/min', '<effectiveTime value="20210120101500"/>'],
        ]);
    }

    /**
     * A vital signs organizer entry with one component per [LOINC code, value, unit, effectiveTime];
     * a null value is sent as nullFlavor.
     *
     * @param list<array{string, ?string, string, string}> $components
     * @return array<string, mixed>
     */
    private function organizerOf(array $components): array
    {
        $xml = '';
        foreach ($components as [$code, $value, $unit, $effectiveTime]) {
            $valueXml = $value === null ? '<value xsi:type="PQ" nullFlavor="NI"/>' : "<value xsi:type=\"PQ\" value=\"$value\" unit=\"$unit\"/>";
            $xml .= <<<XML
                <component>
                  <observation classCode="OBS" moodCode="EVN">
                    <templateId root="2.16.840.1.113883.10.20.22.4.27" extension="2014-06-09"/>
                    <code code="$code" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC"/>
                    <statusCode code="completed"/>
                    $effectiveTime
                    $valueXml
                  </observation>
                </component>
                XML;
        }
        $organizer = (new XmlExtended())->fromString(<<<XML
            <entry xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
              <organizer classCode="CLUSTER" moodCode="EVN">
                <templateId root="2.16.840.1.113883.10.20.22.4.26" extension="2015-08-01"/>
                <id root="1.3.6.1.4.1.115" extension="vitals-1"/>
                <statusCode code="completed"/>
                $xml
              </organizer>
            </entry>
            XML);
        $this->assertIsArray($organizer);
        /** @var array<string, mixed> $organizer */
        return $organizer;
    }

    /**
     * The vital signs section of the export, as the import hands it to VitalSign().
     *
     * @return array<string, mixed>
     */
    private function vitalSignsSection(): array
    {
        $document = (new XmlExtended())->fromFile(self::OPENEMR_EXPORT);
        $body = $document['component'] ?? null;
        $this->assertIsArray($body);
        $structuredBody = $body['structuredBody'] ?? null;
        $this->assertIsArray($structuredBody);
        $sections = $structuredBody['component'] ?? null;
        $this->assertIsArray($sections);
        foreach ($sections as $component) {
            $this->assertIsArray($component);
            $section = $component['section'] ?? null;
            $this->assertIsArray($section);
            $templateIds = $section['templateId'] ?? [];
            $this->assertIsArray($templateIds);
            $templateIds = isset($templateIds['root']) ? [$templateIds] : $templateIds;
            foreach ($templateIds as $templateId) {
                if (is_array($templateId) && ($templateId['root'] ?? null) === '2.16.840.1.113883.10.20.22.2.4.1') {
                    /** @var array<string, mixed> $component */
                    return $component;
                }
            }
        }
        $this->fail('the export has no vital signs section');
    }

    private function vitalSignCount(): int
    {
        $templateData = $this->reflection->getProperty('templateData')->getValue($this->parser);
        $this->assertIsArray($templateData);
        $fields = $templateData['field_name_value_array'] ?? null;
        $this->assertIsArray($fields);
        $records = $fields['vital_sign'] ?? null;
        $this->assertIsArray($records, 'no vital signs read');
        return count($records);
    }

    /**
     * @return array<string, mixed>
     */
    private function vitalSign(int $index): array
    {
        $templateData = $this->reflection->getProperty('templateData')->getValue($this->parser);
        $this->assertIsArray($templateData);
        $fields = $templateData['field_name_value_array'] ?? null;
        $this->assertIsArray($fields);
        $records = $fields['vital_sign'] ?? null;
        $this->assertIsArray($records, 'no vital signs read');
        $record = $records[$index] ?? null;
        $this->assertIsArray($record, "no vital sign record $index");
        /** @var array<string, mixed> $record */
        return $record;
    }
}
