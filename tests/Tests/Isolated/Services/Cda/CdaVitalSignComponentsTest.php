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
use PHPUnit\Framework\Attributes\DataProvider;
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
     * C-CDA dates an organizer by an interval around its readings, so one visit's readings can be minutes
     * apart. Readings of different measures on one date are one record, dated by the first.
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
     * A measure taken twice in a day is two records, each dated by its own time, rather than the later
     * reading replacing the earlier one.
     */
    public function testTheSameMeasureTwiceInADayIsTwoRecords(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20260301080000"/>'],
            ['9279-1', '16', '/min', '<effectiveTime value="20260301080500"/>'],
            ['8867-4', '90', '/min', '<effectiveTime value="20260301160000"/>'],
        ]));

        $this->assertSame(['20260301080000', '72', '16'], [$this->vitalSign(1)['date'], $this->vitalSign(1)['pulse'], $this->vitalSign(1)['respiration']]);
        $this->assertSame(['20260301160000', '90'], [$this->vitalSign(2)['date'], $this->vitalSign(2)['pulse']]);
        $this->assertSame(2, $this->vitalSignCount());
    }

    /**
     * A later visit's readings stay together: its blood pressure doesn't join the morning's record just
     * because the morning had none, when its pulse can't.
     */
    public function testALaterVisitsReadingsStayTogether(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '88', '/min', '<effectiveTime value="20260301080000"/>'],
            ['8480-6', '128', 'mm[Hg]', '<effectiveTime value="20260301160000"/>'],
            ['8867-4', '72', '/min', '<effectiveTime value="20260301160000"/>'],
        ]));

        $this->assertSame(['20260301080000', '88'], [$this->vitalSign(1)['date'], $this->vitalSign(1)['pulse']]);
        $this->assertArrayNotHasKey('bps', $this->vitalSign(1));
        $this->assertSame(['20260301160000', '128', '72'], [$this->vitalSign(2)['date'], $this->vitalSign(2)['bps'], $this->vitalSign(2)['pulse']]);
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

    /**
     * Many documents date vitals by the day only. A measure taken twice under one time is two records, the
     * second's id made from its place at that time, rather than the later reading replacing the earlier;
     * another measure at that time joins the first.
     */
    public function testTheSameMeasureTwiceAtOneTimeIsTwoRecords(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20240422"/>'],
            ['8867-4', '90', '/min', '<effectiveTime value="20240422"/>'],
            ['9279-1', '16', '/min', '<effectiveTime value="20240422"/>'],
        ]));

        $first = $this->vitalSign(1);
        $this->assertSame(['20240422', '72', '16', 'vitals-1'], [$first['date'], $first['pulse'], $first['respiration'], $first['extension']]);
        $second = $this->vitalSign(2);
        $this->assertSame(['20240422', '90'], [$second['date'], $second['pulse']]);
        $this->assertIsString($second['extension']);
        $this->assertNotSame('vitals-1', $second['extension']);
        $this->assertSame(2, $this->vitalSignCount());
    }

    /**
     * A copy of a reading with the same value at the same time is one reading.
     */
    public function testAnEqualCopyAtOneTimeIsOneRecord(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['2710-2', '97', '%', '<effectiveTime value="20260301093000"/>'],
            ['59408-5', '97.0', '%', '<effectiveTime value="20260301093000"/>'],
        ]));

        $this->assertSame(1, $this->vitalSignCount());
        $this->assertEquals(97, $this->vitalSign(1)['oxygen_saturation']);
    }

    /**
     * An id longer than external_id's 20 characters would be cut on saving and never match again, so the first
     * record gets a short one made from it too.
     */
    public function testAnIdTooLongForExternalIdIsShortenedForEveryRecord(): void
    {
        $guid = '0f4e5c1a-7b2d-4e8f-9a3c-6d1b2e3f4a5b';
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20180114093000"/>'],
            ['8867-4', '80', '/min', '<effectiveTime value="20210120101500"/>'],
        ], $guid));

        foreach ([1, 2] as $index) {
            $id = $this->vitalSign($index)['extension'];
            $this->assertIsString($id);
            $this->assertLessThanOrEqual(20, strlen($id));
            $this->assertNotSame(substr($guid, 0, 20), $id);
        }
        $this->assertNotSame($this->vitalSign(1)['extension'], $this->vitalSign(2)['extension']);
    }

    /**
     * A blank value is no reading: it doesn't replace one already read, and a date with only blank values
     * isn't saved as a form of zeros.
     */
    public function testABlankValueIsNoReading(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20260301093000"/>'],
            ['8867-4', '', '/min', '<effectiveTime value="20260301093000"/>'],
            ['9279-1', ' ', '/min', '<effectiveTime value="20260415093000"/>'],
        ]));

        $this->assertSame('72', $this->vitalSign(1)['pulse']);
        $this->assertSame(1, $this->vitalSignCount());
    }

    /**
     * OpenEMR's own export gives every Vitals form of an encounter the encounter's id, so one document can
     * hold two organizers with one id. Each record still gets an id of its own, or the second would be
     * saved over the first.
     */
    public function testTwoOrganizersWithOneIdGetIdsOfTheirOwn(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([['8480-6', '150', 'mm[Hg]', '<effectiveTime value="20240422"/>']]));
        $this->parser->fetchVitalSignData($this->organizerOf([['8480-6', '128', 'mm[Hg]', '<effectiveTime value="20240422"/>']]));

        $this->assertSame(['150', 'vitals-1'], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['extension']]);
        $this->assertSame('128', $this->vitalSign(2)['bps']);
        $this->assertIsString($this->vitalSign(2)['extension']);
        $this->assertNotSame('vitals-1', $this->vitalSign(2)['extension']);
    }

    /**
     * A later visit whose readings are minutes apart stays together: its blood pressure joins the afternoon
     * record nearest in time, not the morning's, which had none.
     */
    public function testALaterVisitMinutesApartStaysTogether(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '88', '/min', '<effectiveTime value="20260301080000"/>'],
            ['8867-4', '72', '/min', '<effectiveTime value="20260301160000"/>'],
            ['8480-6', '128', 'mm[Hg]', '<effectiveTime value="20260301160200"/>'],
            ['8462-4', '82', 'mm[Hg]', '<effectiveTime value="20260301160200"/>'],
        ]));

        $morning = $this->vitalSign(1);
        $this->assertSame(['20260301080000', '88'], [$morning['date'], $morning['pulse']]);
        $this->assertArrayNotHasKey('bps', $morning);
        $afternoon = $this->vitalSign(2);
        $this->assertSame(['20260301160000', '72', '128', '82'], [$afternoon['date'], $afternoon['pulse'], $afternoon['bps'], $afternoon['bpd']]);
        $this->assertSame(2, $this->vitalSignCount());
    }

    /**
     * A blood pressure taken twice under one day-only time, sent as separate systolic and diastolic
     * components, keeps each pair: the second's diastolic, equal to the first's, isn't taken as a copy.
     */
    public function testABloodPressureTakenTwiceKeepsEachPair(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8480-6', '150', 'mm[Hg]', '<effectiveTime value="20240422"/>'],
            ['8462-4', '90', 'mm[Hg]', '<effectiveTime value="20240422"/>'],
            ['8480-6', '128', 'mm[Hg]', '<effectiveTime value="20240422"/>'],
            ['8462-4', '90', 'mm[Hg]', '<effectiveTime value="20240422"/>'],
        ]));

        $this->assertSame(['150', '90'], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
        $this->assertSame(['128', '90'], [$this->vitalSign(2)['bps'], $this->vitalSign(2)['bpd']]);
        $this->assertSame(2, $this->vitalSignCount());
    }

    /**
     * Some senders write times with a zone offset (YYYYMMDDHHMM-0500): read as times, a later visit's
     * readings still stay together.
     */
    public function testTimesWithAZoneOffsetAreReadAsTimes(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '88', '/min', '<effectiveTime value="202603010800-0500"/>'],
            ['8867-4', '72', '/min', '<effectiveTime value="202603011600-0500"/>'],
            ['8480-6', '128', 'mm[Hg]', '<effectiveTime value="202603011602-0500"/>'],
        ]));

        $this->assertSame(['202603010800-0500', '88'], [$this->vitalSign(1)['date'], $this->vitalSign(1)['pulse']]);
        $this->assertArrayNotHasKey('bps', $this->vitalSign(1));
        $this->assertSame(['202603011600-0500', '72', '128'], [$this->vitalSign(2)['date'], $this->vitalSign(2)['pulse'], $this->vitalSign(2)['bps']]);
    }

    /**
     * A blood pressure whose diastolic was sent without a value, then a second whole one under the same
     * day-only time: the second diastolic stays with its own systolic.
     */
    public function testABlankDiastolicDoesNotTakeTheNextPairsReading(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8480-6', '150', 'mm[Hg]', '<effectiveTime value="20240422"/>'],
            ['8462-4', null, 'mm[Hg]', '<effectiveTime value="20240422"/>'],
            ['8480-6', '128', 'mm[Hg]', '<effectiveTime value="20240422"/>'],
            ['8462-4', '90', 'mm[Hg]', '<effectiveTime value="20240422"/>'],
        ]));

        $this->assertSame(['150', null], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
        $this->assertSame(['128', '90'], [$this->vitalSign(2)['bps'], $this->vitalSign(2)['bpd']]);
    }

    /**
     * A reading taken minutes before the rest of its visit joins that visit, not a morning record hours
     * earlier that happened to lack it.
     */
    public function testAReadingMinutesBeforeItsVisitJoinsIt(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '88', '/min', '<effectiveTime value="20260301080000"/>'],
            ['8310-5', '98.6', '[degF]', '<effectiveTime value="20260301155500"/>'],
            ['8480-6', '128', 'mm[Hg]', '<effectiveTime value="20260301160000"/>'],
            ['8867-4', '72', '/min', '<effectiveTime value="20260301160000"/>'],
        ]));

        $this->assertSame(['20260301080000', '88'], [$this->vitalSign(1)['date'], $this->vitalSign(1)['pulse']]);
        $this->assertArrayNotHasKey('temperature', $this->vitalSign(1));
        $afternoon = $this->vitalSign(2);
        $this->assertEquals(98.6, $afternoon['temperature']);
        $this->assertSame(['20260301155500', '128', '72'], [$afternoon['date'], $afternoon['bps'], $afternoon['pulse']]);
    }

    /**
     * A blood pressure retaken with the same diastolic, each part a component of its own and seconds apart:
     * the second diastolic is the retake's, not a copy of the first.
     */
    public function testARetakenBloodPressureKeepsItsEqualDiastolic(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8462-4', '80', 'mm[Hg]', '<effectiveTime value="20260301095955"/>'],
            ['8480-6', '150', 'mm[Hg]', '<effectiveTime value="20260301100000"/>'],
            ['8462-4', '80', 'mm[Hg]', '<effectiveTime value="20260301100955"/>'],
            ['8480-6', '128', 'mm[Hg]', '<effectiveTime value="20260301101000"/>'],
        ]));

        $this->assertSame(['150', '80'], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
        $this->assertSame(['128', '80'], [$this->vitalSign(2)['bps'], $this->vitalSign(2)['bpd']]);
    }

    /**
     * An oxygen saturation sent under both its codes is one reading, kept with the pulse it was taken with,
     * even when a second pulse at that time has none.
     */
    public function testAReadingUnderItsOtherCodeIsACopy(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20240422"/>'],
            ['8867-4', '90', '/min', '<effectiveTime value="20240422"/>'],
            ['2710-2', '97', '%', '<effectiveTime value="20240422"/>'],
            ['59408-5', '97', '%', '<effectiveTime value="20240422"/>'],
        ]));

        $this->assertSame(['72', '97'], [$this->vitalSign(1)['pulse'], $this->vitalSign(1)['oxygen_saturation']]);
        $this->assertSame('90', $this->vitalSign(2)['pulse']);
        $this->assertArrayNotHasKey('oxygen_saturation', $this->vitalSign(2));
    }

    /**
     * Weight (3141-9 and 29463-7) and head circumference (8287-5 and 9843-4) have two codes too.
     */
    public function testAWeightOrHeadCircumferenceUnderItsOtherCodeIsACopy(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20240422"/>'],
            ['8867-4', '90', '/min', '<effectiveTime value="20240422"/>'],
            ['3141-9', '70', 'kg', '<effectiveTime value="20240422"/>'],
            ['29463-7', '70', 'kg', '<effectiveTime value="20240422"/>'],
            ['8287-5', '35', 'cm', '<effectiveTime value="20240422"/>'],
            ['9843-4', '35', 'cm', '<effectiveTime value="20240422"/>'],
        ]));

        $first = $this->vitalSign(1);
        $this->assertSame(['72', 154.323584, 13.779528], [$first['pulse'], $first['weight'], $first['head_circ']]);
        $this->assertSame('90', $this->vitalSign(2)['pulse']);
        $this->assertArrayNotHasKey('weight', $this->vitalSign(2));
        $this->assertArrayNotHasKey('head_circ', $this->vitalSign(2));
        $this->assertSame(2, $this->vitalSignCount());
    }

    /**
     * A blood pressure retaken under one day-only time keeps each pair whichever half repeats: the repeated
     * half isn't taken as a copy of the first pair's.
     *
     * @return array<string, array{list<array{string, string}>, array{string, string}, array{string, string}}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function retakenPairProvider(): array
    {
        return [
            'systolic repeats, systolic first' => [[['8480-6', '150'], ['8462-4', '90'], ['8480-6', '150'], ['8462-4', '80']], ['150', '90'], ['150', '80']],
            'diastolic repeats, diastolic first' => [[['8462-4', '90'], ['8480-6', '150'], ['8462-4', '90'], ['8480-6', '128']], ['150', '90'], ['128', '90']],
        ];
    }

    /**
     * @param list<array{string, string}> $readings
     * @param array{string, string}       $first
     * @param array{string, string}       $second
     */
    #[DataProvider('retakenPairProvider')]
    public function testARetakenPairKeepsItsHalves(array $readings, array $first, array $second): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf(array_map(
            fn(array $reading): array => [$reading[0], $reading[1], 'mm[Hg]', '<effectiveTime value="20240422"/>'],
            $readings
        )));

        $this->assertSame($first, [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
        $this->assertSame($second, [$this->vitalSign(2)['bps'], $this->vitalSign(2)['bpd']]);
        $this->assertSame(2, $this->vitalSignCount());
    }

    /**
     * A reading sent at the day's precision is in the same visit as that day's timed readings.
     */
    public function testADayOnlyReadingJoinsThatDaysTimedReadings(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '72', '/min', '<effectiveTime value="20260301"/>'],
            ['9279-1', '16', '/min', '<effectiveTime value="20260301103000"/>'],
        ]));

        $this->assertSame(['72', '16'], [$this->vitalSign(1)['pulse'], $this->vitalSign(1)['respiration']]);
        $this->assertSame(1, $this->vitalSignCount());
    }

    /**
     * A day-only reading isn't in the visit of a reading late the evening before.
     */
    public function testADayOnlyReadingIsNotInTheEveningBeforesVisit(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([
            ['8867-4', '88', '/min', '<effectiveTime value="20260228233000"/>'],
            ['3141-9', '150', '[lb_av]', '<effectiveTime value="20260301"/>'],
        ]));

        $this->assertSame(['20260228233000', '88'], [$this->vitalSign(1)['date'], $this->vitalSign(1)['pulse']]);
        $this->assertArrayNotHasKey('weight', $this->vitalSign(1));
        $this->assertSame('20260301', $this->vitalSign(2)['date']);
        $this->assertEquals(150, $this->vitalSign(2)['weight']);
    }

    /**
     * One observation nested under a code the form doesn't record (a flow rate under an inhaled oxygen
     * concentration) is no blood pressure.
     */
    public function testOneNestedObservationUnderAnotherCodeIsNoBloodPressure(): void
    {
        $this->parser->fetchVitalSignData(['organizer' => [
            'id' => ['root' => '1.3.6.1.4.1.115', 'extension' => 'vitals-1'],
            'component' => [
                ['observation' => [
                    'code' => ['code' => '3150-0'],
                    'effectiveTime' => ['value' => '20260301093000'],
                    'value' => ['value' => '28', 'unit' => '%'],
                    'entryRelationship' => ['observation' => ['code' => ['code' => '3151-8'], 'value' => ['value' => '2', 'unit' => 'L/min']]],
                ]],
                ['observation' => [
                    'code' => ['code' => '8867-4'],
                    'effectiveTime' => ['value' => '20260301093000'],
                    'value' => ['value' => '72', 'unit' => '/min'],
                ]],
            ],
        ]]);

        $this->assertSame('72', $this->vitalSign(1)['pulse']);
        $this->assertArrayNotHasKey('bps', $this->vitalSign(1));
    }

    /**
     * The ids made for a second record at one time, and for a second organizer with the same id, are the
     * same on every import, so importing the document again updates those records too.
     */
    public function testMadeIdsAreTheSameOnEveryImport(): void
    {
        $import = function (): array {
            $this->reflection->getProperty('templateData')->setValue($this->parser, []);
            $this->parser->fetchVitalSignData($this->organizerOf([
                ['8867-4', '72', '/min', '<effectiveTime value="20240422"/>'],
                ['8867-4', '90', '/min', '<effectiveTime value="20240422"/>'],
            ]));
            $this->parser->fetchVitalSignData($this->organizerOf([['8867-4', '80', '/min', '<effectiveTime value="20240422"/>']]));
            $ids = [];
            foreach ([1, 2, 3] as $index) {
                $id = $this->vitalSign($index)['extension'];
                $this->assertIsString($id);
                $ids[] = $id;
            }
            return $ids;
        };

        $first = $import();
        $this->assertCount(3, array_unique($first));
        $this->assertSame($first, $import());
    }

    /**
     * external_id is compared without case or trailing spaces, so ids that differ only in those are one id.
     */
    public function testIdsThatDifferOnlyInCaseAreKeptApart(): void
    {
        $this->parser->fetchVitalSignData($this->organizerOf([['8867-4', '72', '/min', '<effectiveTime value="20240422"/>']], 'abc1'));
        $this->parser->fetchVitalSignData($this->organizerOf([['8867-4', '90', '/min', '<effectiveTime value="20240422"/>']], 'ABC1'));

        $this->assertSame('abc1', $this->vitalSign(1)['extension']);
        $this->assertIsString($this->vitalSign(2)['extension']);
        $this->assertNotSame('abc1', strtolower($this->vitalSign(2)['extension']));
    }

    /**
     * A blood pressure panel's readings are read by their codes, whichever comes first.
     */
    public function testABloodPressurePanelIsReadByItsCodes(): void
    {
        $reading = fn(string $code, string $value): array => ['observation' => ['code' => ['code' => $code], 'value' => ['value' => $value]]];
        $this->parser->fetchVitalSignData(['organizer' => [
            'id' => ['root' => '1.3.6.1.4.1.115', 'extension' => 'vitals-1'],
            'component' => [['observation' => [
                'code' => ['code' => '85354-9'],
                'effectiveTime' => ['value' => '20260301093000'],
                'entryRelationship' => [$reading('8462-4', '80'), $reading('8480-6', '120')],
            ]]],
        ]]);

        $this->assertSame(['120', '80'], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
    }

    /**
     * A panel whose readings carry other codes (sitting systolic 8459-0, diastolic 8453-3) is read by position,
     * as before; one with a standard systolic and another diastolic code takes the diastolic by position.
     *
     * @return array<string, array{string, string, string}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function panelProvider(): array
    {
        return [
            'short panel, sitting codes' => ['55417-0', '8459-0', '8453-3'],
            'standard systolic, sitting diastolic' => ['85354-9', '8480-6', '8453-3'],
        ];
    }

    /**
     * The same, listed diastolic first: the reading without a standard code fills the column left, from the
     * relationship not read yet.
     *
     * @return array<string, array{string, string, string}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function reversedPanelProvider(): array
    {
        return [
            'sitting diastolic, then standard systolic' => ['8453-3', '80', '8480-6'],
            'standard diastolic, then sitting systolic' => ['8462-4', '80', '8459-0'],
        ];
    }

    #[DataProvider('reversedPanelProvider')]
    public function testAReversedPanelWithOneStandardCodeKeepsBothReadings(string $firstCode, string $firstValue, string $secondCode): void
    {
        $reading = fn(string $code, string $value): array => ['observation' => ['code' => ['code' => $code], 'value' => ['value' => $value]]];
        $this->parser->fetchVitalSignData(['organizer' => [
            'id' => ['root' => '1.3.6.1.4.1.115', 'extension' => 'vitals-1'],
            'component' => [['observation' => [
                'code' => ['code' => '85354-9'],
                'effectiveTime' => ['value' => '20260301093000'],
                'entryRelationship' => [$reading($firstCode, $firstValue), $reading($secondCode, '120')],
            ]]],
        ]]);

        $this->assertSame(['120', '80'], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
    }

    #[DataProvider('panelProvider')]
    public function testABloodPressurePanelWithOtherCodesIsReadByPosition(string $panel, string $systolic, string $diastolic): void
    {
        $reading = fn(string $code, string $value): array => ['observation' => ['code' => ['code' => $code], 'value' => ['value' => $value]]];
        $this->parser->fetchVitalSignData(['organizer' => [
            'id' => ['root' => '1.3.6.1.4.1.115', 'extension' => 'vitals-1'],
            'component' => [['observation' => [
                'code' => ['code' => $panel],
                'effectiveTime' => ['value' => '20260301093000'],
                'entryRelationship' => [$reading($systolic, '120'), $reading($diastolic, '80')],
            ]]],
        ]]);

        $this->assertSame(['120', '80'], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
    }

    /**
     * A systolic with its diastolic nested as an entry relationship keeps its own reading.
     */
    public function testASystolicWithANestedDiastolicKeepsBoth(): void
    {
        $this->parser->fetchVitalSignData(['organizer' => [
            'id' => ['root' => '1.3.6.1.4.1.115', 'extension' => 'vitals-1'],
            'component' => [['observation' => [
                'code' => ['code' => '8480-6'],
                'effectiveTime' => ['value' => '20260301093000'],
                'value' => ['value' => '120', 'unit' => 'mm[Hg]'],
                'entryRelationship' => ['observation' => ['code' => ['code' => '8462-4'], 'value' => ['value' => '80']]],
            ]]],
        ]]);

        $this->assertSame(['120', '80'], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
    }

    /**
     * A comment ahead of a panel's readings takes no reading's place.
     */
    public function testACommentAheadOfAPanelsReadingsIsSkipped(): void
    {
        $reading = fn(string $code, string $value): array => ['observation' => ['code' => ['code' => $code], 'value' => ['value' => $value]]];
        $this->parser->fetchVitalSignData(['organizer' => [
            'id' => ['root' => '1.3.6.1.4.1.115', 'extension' => 'vitals-1'],
            'component' => [['observation' => [
                'code' => ['code' => '85354-9'],
                'effectiveTime' => ['value' => '20260301093000'],
                'entryRelationship' => [['act' => ['code' => ['code' => '48767-8']]], $reading('8459-0', '120'), $reading('8453-3', '80')],
            ]]],
        ]]);

        $this->assertSame(['120', '80'], [$this->vitalSign(1)['bps'], $this->vitalSign(1)['bpd']]);
    }

    /**
     * Another observation's entry relationship (a comment on a weight) isn't a blood pressure: the
     * observation is read by its own code.
     */
    public function testAWeightWithACommentIsReadAsAWeight(): void
    {
        $this->parser->fetchVitalSignData(['organizer' => [
            'id' => ['root' => '1.3.6.1.4.1.115', 'extension' => 'vitals-1'],
            'component' => [['observation' => [
                'code' => ['code' => '3141-9'],
                'effectiveTime' => ['value' => '20260301093000'],
                'value' => ['value' => '154.5', 'unit' => '[lb_av]'],
                'entryRelationship' => ['act' => ['code' => ['code' => '48767-8'], 'text' => 'weighed in clothes']],
            ]]],
        ]]);

        $vital = $this->vitalSign(1);
        $this->assertEquals(154.5, $vital['weight']);
        $this->assertArrayNotHasKey('bps', $vital);
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
    private function organizerOf(array $components, string $extension = 'vitals-1'): array
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
                <id root="1.3.6.1.4.1.115" extension="$extension"/>
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
