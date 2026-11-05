<?php

/**
 * Referrals and the replies and reports that came back on them, read from the database as the
 * eCQMs read them, and CMS50 (Closing the Referral Loop) calculated from them (#14745).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Qdm;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequestOne;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\ConsultantReportService;
use OpenEMR\Services\Qdm\Services\ReferralOrderService;
use OpenEMR\Services\Qrda\ExportCat1Service;
use OpenEMR\Services\ReferralTaskService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReferralLoopServicesDatabaseTest extends TestCase
{
    private const MEASURE = __DIR__ . '/../../../../vendor/openemr/oe-cqm-parsers/2026_reporting_period/json_measures/CMS50v14';

    // Synthetic patients and documents; every row made here is removed, and none goes through uuid_registry.
    private const PID = 990621;
    private const OTHER_PID = 990622;
    private const THIRD_PID = 990623;
    private const PIDS = [self::PID, self::OTHER_PID, self::THIRD_PID];
    private const FIRST_DOCUMENT_ID = 990621001;

    /** Patient referral to specialist, the first code of CMS50's Referral value set. */
    private const SPECIALIST = 'SNOMED-CT:103696004';

    private const LOINC = 'http://loinc.org';

    private int $documents = 0;

    /** @var list<int> */
    private array $tasks = [];

    /** @var list<int> */
    private array $transactions = [];

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patients before the test');
    }

    protected function tearDown(): void
    {
        foreach ($this->tasks as $task) {
            QueryUtils::sqlStatementThrowException('DELETE FROM `task_output` WHERE `task_id` = ?', [$task]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `task` WHERE `id` = ?', [$task]);
        }
        foreach ($this->transactions as $transaction) {
            QueryUtils::sqlStatementThrowException('DELETE FROM `lbt_data` WHERE `form_id` = ?', [$transaction]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `transactions` WHERE `id` = ?', [$transaction]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM `documents` WHERE `id` BETWEEN ? AND ?', [self::FIRST_DOCUMENT_ID, self::FIRST_DOCUMENT_ID + 99]);
        foreach (['form_care_plan', 'form_encounter', 'patient_data'] as $table) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `$table` WHERE `pid` IN " . self::in(), self::PIDS);
        }
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testAReferralIsReadWithItsIdDateAndRequestedService(): void
    {
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST);
        $this->referral(self::OTHER_PID, '2026-03-02', self::SPECIALIST);

        $this->assertSame([[self::PID, self::qdmId($referral), '2026-03-02', self::SPECIALIST]], $this->orders(self::PID));
        $this->assertCount(2, $this->orders(self::PID, self::OTHER_PID));
    }

    public function testOnlyAReferralWithADateAndARequestedServiceIsAnOrder(): void
    {
        $this->referral(self::PID, '', self::SPECIALIST);
        $this->referral(self::PID, '2026-03-02', '');
        $this->referral(self::PID, '2026-03-02', null);
        $this->referral(self::PID, null, self::SPECIALIST);
        $this->referral(self::PID, 'March 2', self::SPECIALIST);
        // Another kind of transaction with the same fields is not a referral.
        $this->referral(self::PID, '2026-03-02', self::SPECIALIST, 'LBTptreq');
        $kept = $this->referral(self::PID, ' 2026-03-09 ', ' ' . self::SPECIALIST . ' ');

        $this->assertSame([[self::PID, self::qdmId($kept), '2026-03-09', self::SPECIALIST]], $this->orders(self::PID), 'spaces around a value are not part of it');
    }

    public function testAReferralSavedWithoutAUuidIsStillAnOrder(): void
    {
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST, uuid: false);

        $this->assertSame([[self::PID, self::qdmId($referral), '2026-03-02', self::SPECIALIST]], $this->orders(self::PID));
    }

    public function testTheReplyRecordedOnTheReferralIsOneReport(): void
    {
        // As ReferralTaskService records it: the Reply Date on the referral, and the reply filed as a
        // document linked to the referral and attached to its Task. The document is that reply.
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST, reply: '2026-04-10');
        $task = $this->task($referral['uuid'], self::PID, 'completed', reason: ReferralTaskService::REASON_REPLY_RECORDED);
        $this->taskOutput($task, $this->document(self::PID, '2026-04-12 15:00:00', replyTo: $referral['id']), self::LOINC, '11488-4');

        $this->assertSame([[self::PID, self::qdmId($referral), '2026-04-10', self::LOINC, '11488-4', 'reply']], $this->reports(self::PID));
    }

    public function testAReplyThatNeverReachedATaskIsStillAReport(): void
    {
        // A referral saved with its Reply Date already in gets no Task, nor does one saved while
        // referral Tasks are off, which has no uuid either.
        $backEntered = $this->referral(self::PID, '2026-03-02', self::SPECIALIST, reply: '2026-04-10');
        $tasksOff = $this->referral(self::PID, '2026-03-09', self::SPECIALIST, uuid: false, reply: ' 2026-04-17 ');

        $this->assertSame([
            [self::PID, self::qdmId($backEntered), '2026-04-10', self::LOINC, '11488-4', 'reply'],
            [self::PID, self::qdmId($tasksOff), '2026-04-17', self::LOINC, '11488-4', 'reply'],
        ], $this->reports(self::PID));
    }

    public function testADocumentARecipientReturnedOnTheTaskIsAReport(): void
    {
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST);
        $this->taskOutput($this->task($referral['uuid'], self::PID, 'completed'), $this->document(self::PID, '2026-04-10 15:00:00'), self::LOINC, '11488-4');

        $this->assertSame([[self::PID, self::qdmId($referral), '2026-04-10 15:00:00', self::LOINC, '11488-4', 'document']], $this->reports(self::PID));
    }

    public function testEveryRecipientsReportCountsWhateverTheTaskBecame(): void
    {
        // A report an earlier recipient sent still counts after the referral is sent on, and so does
        // one a recipient attached to the Task a reply completed and a cleared Reply Date set aside.
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST);
        $statuses = [['completed', null], ['cancelled', ReferralTaskService::REASON_SENT_ELSEWHERE], ['rejected', null], ['accepted', null],
            ['entered-in-error', ReferralTaskService::REASON_REPLY_CLEARED]];
        foreach ($statuses as $i => [$status, $reason]) {
            $this->taskOutput($this->task($referral['uuid'], self::PID, $status, reason: $reason), $this->document(self::PID, "2026-04-1$i 15:00:00"), self::LOINC, '11488-4');
        }

        $this->assertCount(5, $this->reports(self::PID));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notAReport(): array
    {
        $cases = [];
        foreach (
            [
                'a reply whose Reply Date was cleared, its document left on the Task set aside',
                'a Task the recipient marked entered-in-error',
                'a Task marked entered-in-error for a reason that differs only in case',
                'a deleted document',
                'a document moved to another patient\'s chart',
                'a recipient\'s sub-Task',
                'a Task of another kind about the referral',
                'an output naming no document',
                'an output that is not a reference',
                'an output with no coded type',
                'a Task about another kind of transaction',
            ] as $case
        ) {
            $cases[$case] = [$case];
        }
        return $cases;
    }

    /**
     * Each case beside a report that counts, on another referral of the same patient, so the
     * query is seen to return reports at all.
     */
    #[DataProvider('notAReport')]
    public function testWhatIsNotAReport(string $case): void
    {
        $counted = $this->referral(self::PID, '2026-03-09', self::SPECIALIST);
        $this->taskOutput($this->task($counted['uuid'], self::PID, 'completed'), $this->document(self::PID, '2026-04-17 15:00:00'), self::LOINC, '11488-4');
        $date = '2026-04-10 15:00:00';
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST);
        $completed = fn (string $code = 'fulfill', ?string $partOf = null): int => $this->task($referral['uuid'], self::PID, 'completed', $code, $partOf);
        $returned = fn (int $task, ?string $document = null, ?string $code = '11488-4', string $element = 'valueReference'): int
            => $this->taskOutput($task, $document ?? $this->document(self::PID, $date), $code === null ? null : self::LOINC, $code, $element);
        match ($case) {
            'a reply whose Reply Date was cleared, its document left on the Task set aside' => $returned(
                $this->task($referral['uuid'], self::PID, 'entered-in-error', reason: ReferralTaskService::REASON_REPLY_CLEARED),
                $this->document(self::PID, $date, replyTo: $referral['id']),
            ),
            'a Task the recipient marked entered-in-error' => $returned($this->task($referral['uuid'], self::PID, 'entered-in-error', reason: 'Sent to the wrong clinic.')),
            'a Task marked entered-in-error for a reason that differs only in case' => $returned(
                $this->task($referral['uuid'], self::PID, 'entered-in-error', reason: strtoupper(ReferralTaskService::REASON_REPLY_CLEARED)),
            ),
            'a deleted document' => $returned($completed(), $this->document(self::PID, $date, deleted: true)),
            'a document moved to another patient\'s chart' => $returned($completed(), $this->document(self::OTHER_PID, $date)),
            'a recipient\'s sub-Task' => $returned($completed(partOf: random_bytes(16))),
            'a Task of another kind about the referral' => $returned($completed('approve')),
            'an output naming no document' => $returned($completed(), random_bytes(16)),
            'an output that is not a reference' => $returned($completed(), element: 'valueString'),
            'an output with no coded type' => $returned($completed(), code: null),
            'a Task about another kind of transaction' => $returned(
                $this->task($this->referral(self::PID, '2026-03-02', self::SPECIALIST, 'LBTptreq')['uuid'], self::PID, 'completed'),
            ),
            default => throw new \LogicException("No such case: $case"),
        };

        $this->assertSame([[self::PID, self::qdmId($counted), '2026-04-17 15:00:00', self::LOINC, '11488-4', 'document']], $this->reports(self::PID, self::OTHER_PID));
    }

    public function testTheSameDocumentReturnedTwiceIsOneReport(): void
    {
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST);
        $task = $this->task($referral['uuid'], self::PID, 'completed');
        $document = $this->document(self::PID, '2026-04-10 15:00:00');
        $this->taskOutput($task, $document, self::LOINC, '11488-4');
        $this->taskOutput($task, $document, self::LOINC, '11488-4', seq: 1);

        $this->assertCount(1, $this->reports(self::PID));
    }

    /**
     * Three patients seen in 2026 and referred to a specialist on 2026-03-02, run through the
     * builder and the calculator eCQM reporting uses: the first one's reply was recorded on
     * 2026-04-10; the second one's too, then its Reply Date was cleared, setting its Task aside;
     * the third one's specialist returned a consult note on the Task, and a care plan item on the
     * day of the referral records it again, as an intervention coded as a referral.
     */
    public function testTheBuilderClosesTheReferralLoop(): void
    {
        foreach (self::PIDS as $pid) {
            $this->patientSeenIn2026($pid);
        }
        $replied = $this->referral(self::PID, '2026-03-02', self::SPECIALIST, reply: '2026-04-10');
        $task = $this->task($replied['uuid'], self::PID, 'completed', reason: ReferralTaskService::REASON_REPLY_RECORDED);
        $this->taskOutput($task, $this->document(self::PID, '2026-04-10 15:00:00', replyTo: $replied['id']), self::LOINC, '11488-4');
        $cleared = $this->referral(self::OTHER_PID, '2026-03-02', self::SPECIALIST);
        $task = $this->task($cleared['uuid'], self::OTHER_PID, 'entered-in-error', reason: ReferralTaskService::REASON_REPLY_CLEARED);
        $this->taskOutput($task, $this->document(self::OTHER_PID, '2026-04-10 15:00:00', replyTo: $cleared['id']), self::LOINC, '11488-4');
        $returned = $this->referral(self::THIRD_PID, '2026-03-02', self::SPECIALIST);
        $this->taskOutput($this->task($returned['uuid'], self::THIRD_PID, 'completed'), $this->document(self::THIRD_PID, '2026-04-10 15:00:00'), self::LOINC, '11488-4');
        QueryUtils::sqlInsert(
            "INSERT INTO `form_care_plan` (`id`, `date`, `pid`, `encounter`, `user`, `groupname`, `authorized`, `activity`, `code`, `codetext`, `description`, `care_plan_type`)"
            . " VALUES (?, '2026-03-02 00:00:00', ?, ?, 'test', 'Default', 1, 1, ?, 'Patient referral to specialist', 'CMS50 test', 'intervention')",
            [self::THIRD_PID, self::THIRD_PID, self::THIRD_PID, self::SPECIALIST],
        );

        $measure = new Measure(MeasureService::fetchMeasureJson(self::MEASURE));
        $measure->measure_path = self::MEASURE;
        $patients = (new QdmBuilder())->build(new QdmRequestSome(self::PIDS));
        $this->assertCount(3, $patients);
        $results = (new CqmCalculator())->calculateMeasure($patients, $measure, '2026-01-01 00:00:00');

        $populations = [];
        foreach ($results as $patientId => $bySet) {
            $set = $bySet['PopulationSet_1'] ?? null;
            $this->assertIsArray($set);
            $populations[(string)$patientId] = [$set['IPP'] ?? null, $set['DENOM'] ?? null, $set['NUMER'] ?? null];
        }
        ksort($populations);
        $this->assertSame([[1, 1, 1], [1, 1, 0], [1, 1, 1]], array_values($populations), 'in pid order');
    }

    public function testTheQrdaCategoryOneExportCarriesTheReferralAndItsReport(): void
    {
        // The export renders every data element the builder makes: the referral as an Intervention
        // Order, and the reply as a Communication Performed whose relatedTo names the referral.
        $this->patientSeenIn2026(self::PID);
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST, reply: '2026-04-10');

        $xml = (new ExportCat1Service(new QdmBuilder(), new QdmRequestOne((string)self::PID)))->export([self::MEASURE], [
            'performance_period_start' => '2026-01-01 00:00:00',
            'performance_period_end' => '2026-12-31 23:59:59',
        ]);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($xml), 'the export is well-formed XML');
        $path = new \DOMXPath($document);
        $path->registerNamespace('cda', 'urn:hl7-org:v3');
        $path->registerNamespace('sdtc', 'urn:hl7-org:sdtc');
        // Intervention Order (V4) and Communication Performed (V3)
        $order = $path->query("//cda:act[cda:templateId/@root='2.16.840.1.113883.10.20.24.3.31']");
        $report = $path->query("//cda:act[cda:templateId/@root='2.16.840.1.113883.10.20.24.3.156']");
        $this->assertInstanceOf(\DOMNodeList::class, $order);
        $this->assertInstanceOf(\DOMNodeList::class, $report);
        $this->assertSame(1, $order->length);
        $this->assertSame(1, $report->length);
        $related = $path->query("//cda:act[cda:templateId/@root='2.16.840.1.113883.10.20.24.3.156']/sdtc:inFulfillmentOf1/sdtc:actReference/sdtc:id/@extension");
        $this->assertInstanceOf(\DOMNodeList::class, $related);
        $this->assertSame(self::qdmId($referral), $related->item(0)?->nodeValue);
    }

    /** @return list<array{int, ?string, string, string}> pid, QDM id, date and Requested Service of each order read, sorted */
    private function orders(int ...$pids): array
    {
        $result = $this->service(ReferralOrderService::class, ...$pids)->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $rows = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $rows[] = [(int)self::number($row['pid']), ReferralOrderService::qdmId($row['id']), self::text($row['refer_date']), self::text($row['refer_related_code'])];
        }
        sort($rows);
        return $rows;
    }

    /**
     * @return list<array{int, ?string, string, string, string, string}> pid, the referral's QDM id, received, type
     *     system and code, and whether a reply or a document, of each report read, sorted
     */
    private function reports(int ...$pids): array
    {
        $result = $this->service(ConsultantReportService::class, ...$pids)->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $rows = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $rows[] = [
                (int)self::number($row['pid']), ReferralOrderService::qdmId($row['id']), self::text($row['received']),
                self::text($row['type_system']), self::text($row['type_code']), $row['document_uuid'] === null ? 'reply' : 'document',
            ];
        }
        sort($rows);
        return $rows;
    }

    /**
     * @template T of ReferralOrderService|ConsultantReportService
     * @param class-string<T> $class
     * @return T
     */
    private function service(string $class, int ...$pids): ReferralOrderService|ConsultantReportService
    {
        $request = count($pids) === 1 ? new QdmRequestOne((string)$pids[0]) : new QdmRequestSome($pids);
        return new $class($request, new CodeTypesService());
    }

    /** @param array{id: int, uuid: string} $referral */
    private static function qdmId(array $referral): string
    {
        return 'referral-' . $referral['id'];
    }

    /**
     * A transaction (a referral unless another form is named) with its Referral Date, Requested
     * Service and Reply Date; a null leaves that field out.
     *
     * @return array{id: int, uuid: string}
     */
    private function referral(int $pid, ?string $date, ?string $requestedService, string $form = 'LBTref', bool $uuid = true, ?string $reply = null): array
    {
        $bytes = random_bytes(16);
        $id = QueryUtils::sqlInsert(
            "INSERT INTO `transactions` (`uuid`, `date`, `title`, `pid`, `user`, `groupname`, `authorized`) VALUES (?, '2026-03-02 10:00:00', ?, ?, 'test', 'Default', 1)",
            [$uuid ? $bytes : null, $form, $pid],
        );
        $this->transactions[] = $id;
        foreach (['refer_date' => $date, 'refer_related_code' => $requestedService, 'reply_date' => $reply] as $field => $value) {
            if ($value !== null) {
                QueryUtils::sqlInsert('INSERT INTO `lbt_data` (`form_id`, `field_id`, `field_value`) VALUES (?, ?, ?)', [$id, $field, $value]);
            }
        }
        return ['id' => $id, 'uuid' => $bytes];
    }

    private function task(string $focusUuid, int $pid, string $status, string $code = 'fulfill', ?string $partOf = null, ?string $reason = null): int
    {
        return $this->tasks[] = QueryUtils::sqlInsert(
            "INSERT INTO `task` (`uuid`, `status`, `status_reason`, `intent`, `code`, `code_system`, `patient_id`, `focus_type`, `focus_uuid`, `part_of_uuid`, `authored_on`)"
            . " VALUES (?, ?, ?, 'order', ?, 'http://hl7.org/fhir/CodeSystem/task-code', ?, 'ServiceRequest', ?, ?, '2026-03-02 10:00:00')",
            [random_bytes(16), $status, $reason, $code, $pid, $focusUuid, $partOf],
        );
    }

    /** An output of the Task, and its id. */
    private function taskOutput(int $taskId, string $valueUuid, ?string $system, ?string $code, string $element = 'valueReference', int $seq = 0): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO `task_output` (`task_id`, `seq`, `type_system`, `type_code`, `type_text`, `value_element`, `value_type`, `value_uuid`) VALUES (?, ?, ?, ?, 'Consult note', ?, 'DocumentReference', ?)",
            [$taskId, $seq, $system, $code, $element, $valueUuid],
        );
    }

    /**
     * A document in the patient's chart, filed at $date, and its uuid; with $replyTo, filed as the
     * reply to that referral, as ReferralTaskService files one.
     */
    private function document(int $pid, string $date, bool $deleted = false, ?int $replyTo = null): string
    {
        $uuid = random_bytes(16);
        QueryUtils::sqlInsert(
            "INSERT INTO `documents` (`id`, `uuid`, `type`, `size`, `date`, `revision`, `url`, `mimetype`, `owner`, `foreign_id`, `docdate`, `name`, `deleted`, `foreign_reference_id`, `foreign_reference_table`)"
            . " VALUES (?, ?, 'file_url', 10, ?, ?, 'file:///dev/null', 'text/plain', 1, ?, DATE(?), 'counter-referral.txt', ?, ?, ?)",
            [self::FIRST_DOCUMENT_ID + $this->documents++, $uuid, $date, $date, $pid, $date, $deleted ? 1 : 0, $replyTo, $replyTo === null ? null : 'transactions'],
        );
        return $uuid;
    }

    private function patientSeenIn2026(int $pid): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `patient_data` (`pid`, `pubpid`, `fname`, `lname`, `DOB`, `sex`, `date`) VALUES (?, ?, 'Referral', 'Cqmloop', '1970-04-01', 'Female', '2026-01-05 09:00:00')",
            [$pid, "CQM$pid"],
        );
        QueryUtils::sqlInsert(
            "INSERT INTO `form_encounter` (`date`, `date_end`, `reason`, `facility_id`, `pid`, `encounter`, `pc_catid`, `provider_id`, `sensitivity`, `encounter_type_code`)"
            . " VALUES ('2026-02-10 09:00:00', '2026-02-10 09:30:00', 'CMS50 test', 3, ?, ?, 5, 1, 'normal', 'CPT4:99213')",
            [$pid, $pid],
        );
    }

    private static function in(): string
    {
        return '(' . implode(', ', array_fill(0, count(self::PIDS), '?')) . ')';
    }

    private function rowsLeft(): int
    {
        $count = static fn (string $sql, array $binds): int => (int)self::number(QueryUtils::fetchSingleValue($sql, 'n', $binds));
        $left = $count('SELECT COUNT(*) AS n FROM `task` WHERE `patient_id` IN ' . self::in(), self::PIDS)
            + $count('SELECT COUNT(*) AS n FROM `transactions` WHERE `pid` IN ' . self::in(), self::PIDS)
            + $count('SELECT COUNT(*) AS n FROM `documents` WHERE `id` BETWEEN ? AND ?', [self::FIRST_DOCUMENT_ID, self::FIRST_DOCUMENT_ID + 99]);
        foreach (['form_care_plan', 'form_encounter', 'patient_data'] as $table) {
            $left += $count("SELECT COUNT(*) AS n FROM `$table` WHERE `pid` IN " . self::in(), self::PIDS);
        }
        foreach ($this->tasks as $task) {
            $left += $count('SELECT COUNT(*) AS n FROM `task_output` WHERE `task_id` = ?', [$task]);
        }
        foreach ($this->transactions as $transaction) {
            $left += $count('SELECT COUNT(*) AS n FROM `lbt_data` WHERE `form_id` = ?', [$transaction]);
        }
        return $left;
    }

    private static function number(mixed $value): int|float|string
    {
        self::assertIsNumeric($value);
        return $value;
    }

    private static function text(mixed $value): string
    {
        self::assertIsString($value);
        return $value;
    }
}
