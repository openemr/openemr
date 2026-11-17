<?php

/**
 * A vital signs organizer of several dates (issue #12302), imported twice: each date's
 * Vitals form is updated by the second import, not added again.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Cda;

use Carecoordination\Model\CarecoordinationTable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Cda\CdaTemplateImportDispose;
use OpenEMR\Services\Cda\CdaTemplateParse;
use OpenEMR\Services\Cda\XmlExtended;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class CdaVitalSignReimportTest extends TestCase
{
    // A synthetic patient; no patient_data row is needed, and every row made here is removed.
    private const PID = 990641;
    private const NPI = '9990641001';
    private const ORGANIZATION = 'Vitals Reimport Test Clinic';

    private CdaTemplateImportDispose $dispose;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patient before the test');
        QueryUtils::sqlInsert("INSERT INTO `users` (`username`, `fname`, `lname`, `npi`, `active`) VALUES ('', 'Reimport', 'Provider', ?, 1)", [self::NPI]);
        QueryUtils::sqlInsert("INSERT INTO `users` (`username`, `organization`, `active`, `abook_type`) VALUES ('', ?, 1, 'external_org')", [self::ORGANIZATION]);
        $this->dispose = new CdaTemplateImportDispose();
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_vitals` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_encounter` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `external_encounters` WHERE `ee_pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `users` WHERE `npi` = ? OR (`abook_type` = ? AND `organization` = ?)', [self::NPI, 'external_org', self::ORGANIZATION]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testImportingAnOrganizerOfSeveralDatesAgainUpdatesEachDate(): void
    {
        $table = $this->createMock(CarecoordinationTable::class);
        $this->dispose->InsertEncounter([[
            'extension' => 'reimport-visit',
            'date' => '20260301090000',
            'provider_npi' => self::NPI,
            'provider_name' => 'Reimport',
            'provider_family' => 'Provider',
            'represented_organization_name' => self::ORGANIZATION,
        ]], self::PID, $table, 0);

        $this->dispose->InsertVitals($this->parsed('72', '80'), self::PID, $table, 0);
        $this->assertSame(['72.000000', '80.000000'], $this->pulses());

        $this->dispose->InsertVitals($this->parsed('74', '82'), self::PID, $table, 0);
        $this->assertSame(['74.000000', '82.000000'], $this->pulses(), 'each date updated, none added');
    }

    /**
     * OpenEMR's own export gives every Vitals form of an encounter the encounter's id: two organizers with
     * one id are saved as two rows, not the second over the first.
     */
    public function testTwoOrganizersWithOneIdAreTwoRows(): void
    {
        $table = $this->createMock(CarecoordinationTable::class);
        $this->dispose->InsertEncounter([[
            'extension' => 'reimport-visit',
            'date' => '20240422090000',
            'provider_npi' => self::NPI,
            'provider_name' => 'Reimport',
            'provider_family' => 'Provider',
            'represented_organization_name' => self::ORGANIZATION,
        ]], self::PID, $table, 0);
        $parser = new CdaTemplateParse();
        foreach (['150', '128'] as $systolic) {
            $parser->fetchVitalSignData((new XmlExtended())->fromString(<<<XML
                <entry xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                  <organizer classCode="CLUSTER" moodCode="EVN">
                    <id root="1.3.6.1.4.1.115" extension="encounter-1"/>
                    <component>
                      <observation classCode="OBS" moodCode="EVN">
                        <code code="8480-6" codeSystem="2.16.840.1.113883.6.1"/>
                        <effectiveTime value="20240422"/>
                        <value xsi:type="PQ" value="$systolic" unit="mm[Hg]"/>
                      </observation>
                    </component>
                  </organizer>
                </entry>
                XML));
        }
        $templateData = (new ReflectionProperty(CdaTemplateParse::class, 'templateData'))->getValue($parser);
        $this->assertIsArray($templateData);
        $fields = $templateData['field_name_value_array'] ?? null;
        $this->assertIsArray($fields);
        $records = $fields['vital_sign'] ?? null;
        $this->assertIsArray($records);
        /** @var list<array<string, mixed>> $list */
        $list = array_values($records);

        $this->dispose->InsertVitals($list, self::PID, $table, 0);

        $rows = QueryUtils::fetchRecords('SELECT `bps` FROM `form_vitals` WHERE `pid` = ? ORDER BY `id`', [self::PID]);
        $this->assertSame(['150', '128'], array_map(static fn(array $row): string => is_scalar($row['bps']) ? (string) $row['bps'] : '', $rows));
    }

    /**
     * The organizer's two dates as the parser hands them to InsertVitals.
     *
     * @return list<array<string, mixed>>
     */
    private function parsed(string $firstPulse, string $secondPulse): array
    {
        $entry = (new XmlExtended())->fromString(<<<XML
            <entry xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
              <organizer classCode="CLUSTER" moodCode="EVN">
                <templateId root="2.16.840.1.113883.10.20.22.4.26" extension="2015-08-01"/>
                <id root="1.3.6.1.4.1.115" extension="reimport-vitals"/>
                <statusCode code="completed"/>
                <component>
                  <observation classCode="OBS" moodCode="EVN">
                    <code code="8867-4" codeSystem="2.16.840.1.113883.6.1"/>
                    <effectiveTime value="20180114093000"/>
                    <value xsi:type="PQ" value="$firstPulse" unit="/min"/>
                  </observation>
                </component>
                <component>
                  <observation classCode="OBS" moodCode="EVN">
                    <code code="8867-4" codeSystem="2.16.840.1.113883.6.1"/>
                    <effectiveTime value="20210120101500"/>
                    <value xsi:type="PQ" value="$secondPulse" unit="/min"/>
                  </observation>
                </component>
              </organizer>
            </entry>
            XML);
        $parser = new CdaTemplateParse();
        $parser->fetchVitalSignData($entry);
        $templateData = (new ReflectionProperty(CdaTemplateParse::class, 'templateData'))->getValue($parser);
        $this->assertIsArray($templateData);
        $fields = $templateData['field_name_value_array'] ?? null;
        $this->assertIsArray($fields);
        $records = $fields['vital_sign'] ?? null;
        $this->assertIsArray($records);
        $this->assertCount(2, $records);
        /** @var list<array<string, mixed>> */
        return array_values($records);
    }

    /** @return list<string> */
    private function pulses(): array
    {
        $rows = QueryUtils::fetchRecords('SELECT `pulse` FROM `form_vitals` WHERE `pid` = ? ORDER BY `date`', [self::PID]);
        return array_map(static fn(array $row): string => is_scalar($row['pulse']) ? (string) $row['pulse'] : '', $rows);
    }

    private function rowsLeft(): int
    {
        $count = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS `n` FROM `form_vitals` WHERE `pid` = ?', 'n', [self::PID]);
        return is_numeric($count) ? (int) $count : -1;
    }
}
