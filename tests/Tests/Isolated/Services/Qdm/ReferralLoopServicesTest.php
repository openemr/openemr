<?php

/**
 * Referrals reach the eCQMs as Intervention, Order with an id of their own, and the replies and
 * reports that came back on them as Communication, Performed related to that id, so CMS50
 * (Closing the Referral Loop) can tell which report closes which referral (#14745).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Cqm\Qdm\CommunicationPerformed;
use OpenEMR\Cqm\Qdm\InterventionOrder;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\AbstractQdmService;
use OpenEMR\Services\Qdm\Services\ConsultantReportService;
use OpenEMR\Services\Qdm\Services\EncounterService;
use OpenEMR\Services\Qdm\Services\InterventionOrderedService;
use OpenEMR\Services\Qdm\Services\ReferralOrderService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReferralLoopServicesTest extends TestCase
{
    private const MEASURE = __DIR__ . '/../../../../../vendor/openemr/oe-cqm-parsers/2026_reporting_period/json_measures/CMS50v14';

    /** A document's uuid, as documents.uuid holds it. */
    private const DOCUMENT = "\x01\x9b\x2b\x3c\x4d\x5e\x7f\x80\x91\xa2\xb3\xc4\xd5\xe6\xf7\x10";

    /** Patient referral to specialist, the first code of CMS50's Referral value set. */
    private const SPECIALIST = 'SNOMED-CT:103696004';

    /**
     * The order's QDM id is "referral-" and the transaction id, the id a report names; the builder's
     * count stays its _id.
     */
    public function testAReferralIsAnOrderNamedByItsTransaction(): void
    {
        $order = $this->order(['pid' => 1, 'id' => 12, 'refer_date' => '2026-03-02', 'refer_related_code' => self::SPECIALIST], 10007);
        $json = self::json($order);
        $this->assertSame('QDM::InterventionOrder', $json['_type'] ?? null);
        $this->assertSame('referral-12', $json['id'] ?? null, 'the id a report can name');
        $this->assertSame(AbstractQdmService::convertToObjectIdBSONFormat(10007), $json['_id'] ?? null, 'the builder\'s count stays the record\'s _id');
        $this->assertSame([['code' => '103696004', 'system' => '2.16.840.1.113883.6.96']], self::codes($json));
        $this->assertIsString($json['authorDatetime'] ?? null);
        $this->assertStringStartsWith('2026-03-02T00:00:00', $json['authorDatetime']);
    }

    public function testEachRequestedServiceIsACode(): void
    {
        // The Requested Service picker can hold several codes, separated by semicolons.
        $order = $this->order(['pid' => 1, 'id' => 12, 'refer_date' => '2026-03-02', 'refer_related_code' => 'CPT4:99213;' . self::SPECIALIST], 1);
        $this->assertSame(
            [['code' => '99213', 'system' => '2.16.840.1.113883.6.12'], ['code' => '103696004', 'system' => '2.16.840.1.113883.6.96']],
            self::codes(self::json($order)),
        );
    }

    /** Not by its uuid, which a referral saved while referral Tasks are off gets only when FHIR first reads it. */
    public function testAReferralIsNamedByItsTransactionIdAlone(): void
    {
        $this->assertSame('referral-12', ReferralOrderService::qdmId('12'), 'as the database returns the id');
        $this->assertSame('referral-12', ReferralOrderService::qdmId(12));
        $this->assertNull(ReferralOrderService::qdmId(null));
        $this->assertNull(ReferralOrderService::qdmId('0'));
        $this->assertNull(ReferralOrderService::qdmId('12a'));
        $this->assertNull($this->order(['pid' => 1, 'id' => null, 'refer_date' => '2026-03-02', 'refer_related_code' => self::SPECIALIST], 1));
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notAReferralDate(): array
    {
        return [
            'none' => [null],
            'blank' => [''],
            'a date in another form' => ['03/02/2026'],
            'a day that does not exist' => ['2026-02-29'],
            'day zero' => ['2026-11-00'],
            'month thirteen' => ['2026-13-01'],
            'a date with a line break' => ["2026-03-02\n"],
            'a date with other text' => ['2026-03-02 soon'],
        ];
    }

    #[DataProvider('notAReferralDate')]
    public function testAReferralWithoutAReferralDateIsNoOrder(mixed $date): void
    {
        $this->assertNull($this->order(['pid' => 1, 'id' => 12, 'refer_date' => $date, 'refer_related_code' => self::SPECIALIST], 1));
    }

    /** A site's layout can record the Referral Date with a time; FHIR's authoredOn keeps the day. */
    public function testADateAndTimeIsReadAsItsDay(): void
    {
        foreach (['2026-03-02 10:15', '2026-03-02 10:15:30'] as $date) {
            $json = self::json($this->order(['pid' => 1, 'id' => 12, 'refer_date' => $date, 'refer_related_code' => self::SPECIALIST], 1));
            $this->assertIsString($json['authorDatetime'] ?? null);
            $this->assertStringStartsWith('2026-03-02T00:00:00', $json['authorDatetime'], $date);
        }
    }

    /**
     * A Reply Date names a day, so the reply counts as received by its end; a date that doesn't exist
     * gives no report.
     */
    public function testAReplyIsACommunicationReceivedByTheEndOfItsReplyDate(): void
    {
        $report = $this->report(['pid' => 1, 'id' => 12, 'document_uuid' => null, 'received' => '2026-04-10', 'type_system' => 'http://loinc.org', 'type_code' => '11488-4'], 10010);
        $json = self::json($report);
        $this->assertSame('QDM::CommunicationPerformed', $json['_type'] ?? null);
        $this->assertSame(['referral-12'], $json['relatedTo'] ?? null);
        $this->assertSame([['code' => '11488-4', 'system' => '2.16.840.1.113883.6.1']], self::codes($json));
        $this->assertIsString($json['receivedDatetime'] ?? null);
        $this->assertStringStartsWith('2026-04-10T23:59:59', $json['receivedDatetime'], 'by the end of the day it names');
        $this->assertSame($json['receivedDatetime'], $json['authorDatetime'] ?? null);
        $this->assertSame(AbstractQdmService::convertToObjectIdBSONFormat(10010), $json['id'] ?? null);
        $this->assertNull($this->report(['pid' => 1, 'id' => 12, 'document_uuid' => null, 'received' => '2026-02-30', 'type_system' => 'http://loinc.org', 'type_code' => '11488-4'], 1), 'a Reply Date that names no day');
    }

    /** Unlike a Reply Date, a document a Task returned keeps the time of day it was filed. */
    public function testADocumentOnTheTaskIsACommunicationReceivedWhenItWasFiled(): void
    {
        $json = self::json($this->report(['pid' => 1, 'id' => 12, 'document_uuid' => self::DOCUMENT, 'received' => '2026-04-10 15:00:00', 'type_system' => 'http://loinc.org', 'type_code' => '11488-4'], 1));
        $this->assertIsString($json['receivedDatetime'] ?? null);
        $this->assertStringStartsWith('2026-04-10T15:00:00', $json['receivedDatetime']);
        $this->assertSame(['referral-12'], $json['relatedTo'] ?? null);
    }

    public function testAReportTypedInASystemOpenEmrDoesNotKnowIsUncoded(): void
    {
        $report = $this->report(['pid' => 1, 'id' => 12, 'document_uuid' => self::DOCUMENT, 'received' => '2026-04-10 15:00:00', 'type_system' => 'urn:example:notes', 'type_code' => '11488-4'], 1);
        $this->assertSame([], self::codes(self::json($report)));
    }

    public function testAReportWithoutItsReferralOrDateIsNoCommunication(): void
    {
        $this->assertNull($this->report(['pid' => 1, 'id' => null, 'document_uuid' => self::DOCUMENT, 'received' => '2026-04-10 15:00:00', 'type_system' => 'http://loinc.org', 'type_code' => '11488-4'], 1));
        $this->assertNull($this->report(['pid' => 1, 'id' => 12, 'document_uuid' => self::DOCUMENT, 'received' => null, 'type_system' => 'http://loinc.org', 'type_code' => '11488-4'], 1));
    }

    public function testTheBuilderRunsBothServicesReferralsAfterCarePlanOrders(): void
    {
        $services = (new \ReflectionProperty(QdmBuilder::class, 'services'))->getValue(new QdmBuilder());
        $this->assertIsArray($services);
        $this->assertContains(ConsultantReportService::class, $services);
        $order = array_search(ReferralOrderService::class, $services, true);
        $carePlan = array_search(InterventionOrderedService::class, $services, true);
        $this->assertIsInt($order);
        $this->assertIsInt($carePlan);
        $this->assertGreaterThan($carePlan, $order, 'on a tie of dates CMS50\'s sort puts the later data element first');
    }

    /**
     * Each case: the patient's referrals [transaction id, Referral Date, Requested Service] and
     * reports [the referral's transaction id, received, LOINC type, from a document], and CMS50v14's
     * IPP, DENOM and NUMER.
     *
     * @return array<string, array{list<array{int, string, string}>, list<array{int, string, string, bool}>, array{int, int, int}}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function referralLoops(): array
    {
        $referral = [12, '2026-03-02', self::SPECIALIST];
        return [
            'a reply received after the referral' => [[$referral], [[12, '2026-04-10', '11488-4', false]], [1, 1, 1]],
            'a consult note filed after it' => [[$referral], [[12, '2026-04-10 15:00:00', '11488-4', true]], [1, 1, 1]],
            'a reply dated the day of the referral' => [[$referral], [[12, '2026-03-02', '11488-4', false]], [1, 1, 1]],
            'the note filed before it' => [[$referral], [[12, '2026-02-20 15:00:00', '11488-4', true]], [1, 1, 0]],
            'a reply dated the day before it' => [[$referral], [[12, '2026-03-01', '11488-4', false]], [1, 1, 0]],
            'a reply on the last day of the period' => [[$referral], [[12, '2026-12-31', '11488-4', false]], [1, 1, 1]],
            'the reply received after the period' => [[$referral], [[12, '2027-01-01', '11488-4', false]], [1, 1, 0]],
            'no report' => [[$referral], [], [1, 1, 0]],
            'a report that is not a consultant report' => [[$referral], [[12, '2026-04-10 15:00:00', '34133-9', true]], [1, 1, 0]],
            'a referral coded with a billing code' => [[[12, '2026-03-02', 'CPT4:99213']], [[12, '2026-04-10', '11488-4', false]], [0, 0, 0]],
            'a report on the later of two referrals' => [[$referral, [13, '2026-05-04', self::SPECIALIST]], [[13, '2026-06-01', '11488-4', false]], [1, 1, 0]],
            'a referral made after October' => [[[12, '2026-11-02', self::SPECIALIST]], [[12, '2026-11-20', '11488-4', false]], [0, 0, 0]],
        ];
    }

    /**
     * One patient with a 2026 office visit and these referrals and reports, as the services make
     * them from database rows, through the calculator eCQM reporting uses.
     *
     * @param list<array{int, string, string}>       $referrals
     * @param list<array{int, string, string, bool}> $reports
     * @param array{int, int, int}                   $expected IPP, DENOM, NUMER
     */
    #[DataProvider('referralLoops')]
    public function testClosingTheReferralLoop(array $referrals, array $reports, array $expected): void
    {
        $patient = new Patient(['birthDatetime' => '1970-04-01 00:00:00']);
        $count = 1;
        $patient->add_data_element((new EncounterService(new QdmRequestSome([1]), new CodeTypesService()))->makeQdmModel(new QdmRecord([
            'pid' => 1, 'encounter' => 7, 'date' => '2026-02-10 09:00:00', 'date_end' => '2026-02-10 09:30:00',
            'encounter_type_code' => 'CPT4:99213', 'discharge_disposition' => null, 'discharge_dispo_code' => null,
            'diagnosis' => null, 'pc_duration' => null,
        ], 1, $count++)));
        foreach ($referrals as [$id, $date, $code]) {
            $order = $this->order(['pid' => 1, 'id' => $id, 'refer_date' => $date, 'refer_related_code' => $code], $count++);
            $this->assertNotNull($order);
            $patient->add_data_element($order);
        }
        foreach ($reports as [$id, $received, $loinc, $isDocument]) {
            $report = $this->report([
                'pid' => 1, 'id' => $id, 'document_uuid' => $isDocument ? self::DOCUMENT : null,
                'received' => $received, 'type_system' => 'http://loinc.org', 'type_code' => $loinc,
            ], $count++);
            $this->assertNotNull($report);
            $patient->add_data_element($report);
        }

        $measure = new Measure(MeasureService::fetchMeasureJson(self::MEASURE));
        $measure->measure_path = self::MEASURE;
        $results = (new CqmCalculator())->calculateMeasure([$patient], $measure, '2026-01-01 00:00:00');
        $populations = array_values($results)[0]['PopulationSet_1'] ?? [];
        $this->assertSame(
            ['IPP' => $expected[0], 'DENOM' => $expected[1], 'NUMER' => $expected[2]],
            ['IPP' => $populations['IPP'] ?? null, 'DENOM' => $populations['DENOM'] ?? null, 'NUMER' => $populations['NUMER'] ?? null],
        );
    }

    /**
     * @param array<string, mixed> $record
     */
    private function order(array $record, int $count): ?InterventionOrder
    {
        return (new ReferralOrderService(new QdmRequestSome([1]), new CodeTypesService()))->makeQdmModel(new QdmRecord($record, 1, $count));
    }

    /**
     * @param array<string, mixed> $record
     */
    private function report(array $record, int $count): ?CommunicationPerformed
    {
        return (new ConsultantReportService(new QdmRequestSome([1]), new CodeTypesService()))->makeQdmModel(new QdmRecord($record, 1, $count));
    }

    /**
     * The data element as the JSON the calculator receives.
     *
     * @return array<mixed>
     */
    private static function json(?object $model): array
    {
        self::assertNotNull($model);
        $json = json_decode(json_encode($model, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($json);
        return $json;
    }

    /**
     * @param array<mixed> $json
     * @return list<array{code: mixed, system: mixed}>
     */
    private static function codes(array $json): array
    {
        $codes = $json['dataElementCodes'] ?? null;
        self::assertIsArray($codes);
        return array_values(array_map(
            static fn (mixed $code): array => is_array($code) ? ['code' => $code['code'] ?? null, 'system' => $code['system'] ?? null] : ['code' => null, 'system' => null],
            $codes,
        ));
    }
}
