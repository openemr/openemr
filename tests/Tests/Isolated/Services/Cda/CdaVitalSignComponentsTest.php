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
    }

    public function testEveryVitalSignInOpenEmrsOwnExportIsRead(): void
    {
        $this->parser->VitalSign($this->vitalSignsSection());

        $vital = $this->vitalSign(1);
        // The first nine components, read before as well.
        $this->assertSame('130', $vital['bps']);
        $this->assertSame('80', $vital['bpd']);
        $this->assertSame('70', $vital['height']);
        $this->assertSame('31.6', $vital['BMI']);
        $this->assertSame('60', $vital['pulse']);
        // The tenth to the twelfth, which were dropped; oxygen saturation is exported as 59408-5.
        $this->assertSame('20', $vital['respiration']);
        $this->assertSame('98', $vital['temperature']);
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
     * A vital signs organizer entry with one component per [LOINC code, value, unit, effectiveTime].
     *
     * @param list<array{string, string, string, string}> $components
     * @return array<string, mixed>
     */
    private function organizerOf(array $components): array
    {
        $xml = '';
        foreach ($components as [$code, $value, $unit, $effectiveTime]) {
            $xml .= <<<XML
                <component>
                  <observation classCode="OBS" moodCode="EVN">
                    <templateId root="2.16.840.1.113883.10.20.22.4.27" extension="2014-06-09"/>
                    <code code="$code" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC"/>
                    <statusCode code="completed"/>
                    $effectiveTime
                    <value xsi:type="PQ" value="$value" unit="$unit"/>
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
