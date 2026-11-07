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

    /** Start from no rows for the synthetic patients, so tearDown()'s count back to zero means something. */
    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patients before the test');
    }

    /** Remove every row the test made, and count the synthetic patients' rows back to zero. */
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

    /**
     * A referral saved with its Reply Date already in gets no Task, nor does one saved while referral Tasks
     * are off, which has no uuid either.
     */
    public function testAReplyThatNeverReachedATaskIsStillAReport(): void
    {
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

    /**
     * A report an earlier recipient sent still counts after the referral is sent on, and so does one a
     * recipient attached to the Task a reply completed and a cleared Reply Date set aside.
     */
    public function testEveryRecipientsReportCountsWhateverTheTaskBecame(): void
    {
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
     * The same document on two of the referral's Tasks under different types is one report: typed as a reply
     * when either output is, else by the byte-smallest system and code.
     */
    public function testOneDocumentReturnedUnderTwoTypesIsOneReport(): void
    {
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST);
        $first = $this->task($referral['uuid'], self::PID, 'completed');
        $second = $this->task($referral['uuid'], self::PID, 'completed');
        $note = $this->document(self::PID, '2026-04-10 15:00:00');
        // 10164-2 sorts before 11488-4, so only the preference for a reply's type picks the latter.
        $this->taskOutput($first, $note, self::LOINC, '10164-2');
        $this->taskOutput($second, $note, self::LOINC, '11488-4');
        $summary = $this->document(self::PID, '2026-04-11 15:00:00');
        $this->taskOutput($first, $summary, 'http://snomed.info/sct', '371530004', seq: 1);
        $this->taskOutput($second, $summary, self::LOINC, '34133-9', seq: 1);

        $this->assertSame([
            [self::PID, self::qdmId($referral), '2026-04-10 15:00:00', self::LOINC, '11488-4', 'document'],
            [self::PID, self::qdmId($referral), '2026-04-11 15:00:00', self::LOINC, '34133-9', 'document'],
        ], $this->reports(self::PID));
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
        $this->carePlanReferral(self::THIRD_PID);

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
        // Order, and the reply as a Communication Performed whose relatedTo names the order's entry, so a
        // reader of the file (Cypress, say) can link them.
        $this->patientSeenIn2026(self::PID);
        $referral = $this->referral(self::PID, '2026-03-02', self::SPECIALIST, reply: '2026-04-10');
        // A care plan intervention is an Intervention Order too, and keeps an id of its own.
        $this->carePlanReferral(self::PID);

        $path = $this->categoryOne(self::PID);
        // Intervention Order (V4) and Communication Performed (V3)
        $this->assertSame(1, self::nodes($path, "//cda:act[cda:templateId/@root='2.16.840.1.113883.10.20.24.3.156']")->length);
        $related = self::values($path, "//cda:act[cda:templateId/@root='2.16.840.1.113883.10.20.24.3.156']/sdtc:inFulfillmentOf1/sdtc:actReference/sdtc:id/@extension");
        $ids = self::values($path, "//cda:act[cda:templateId/@root='2.16.840.1.113883.10.20.24.3.31']/cda:id/@extension");
        $this->assertCount(1, $related);
        $this->assertCount(2, $ids);
        $this->assertStringEndsWith('-' . self::qdmId($referral), $related[0], 'the referral\'s QDM id, scoped to the document');
        $this->assertContains($related[0], $ids, 'the report names the order\'s own entry id');
        $this->assertCount(2, array_unique($ids), 'each order has an id of its own');
    }

    /**
     * Two patients exported one at a time: each export numbers its data elements from the same start, so their
     * care plan orders have the same QDM id, yet their entries keep ids unique across documents.
     */
    public function testTheSameQdmIdInTwoPatientsExportsIsTwoIds(): void
    {
        foreach ([self::PID, self::OTHER_PID] as $pid) {
            $this->patientSeenIn2026($pid);
            $this->carePlanReferral($pid);
        }
        $order = "//cda:act[cda:templateId/@root='2.16.840.1.113883.10.20.24.3.31']/cda:id/@extension";
        $first = self::values($this->categoryOne(self::PID), $order);
        $second = self::values($this->categoryOne(self::OTHER_PID), $order);
        $this->assertCount(1, $first);
        $this->assertCount(1, $second);
        $this->assertSame(self::lastPart($first[0]), self::lastPart($second[0]), 'the same QDM id in both');
        $this->assertNotSame($first[0], $second[0]);
    }

    /** One patient's QRDA Category I document for CMS50v14, ready to query. */
    private function categoryOne(int $pid): \DOMXPath
    {
        $xml = (new ExportCat1Service(new QdmBuilder(), new QdmRequestOne((string)$pid)))->export([self::MEASURE], [
            'performance_period_start' => '2026-01-01 00:00:00',
            'performance_period_end' => '2026-12-31 23:59:59',
        ]);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($xml), 'the export is well-formed XML');
        $path = new \DOMXPath($document);
        $path->registerNamespace('cda', 'urn:hl7-org:v3');
        $path->registerNamespace('sdtc', 'urn:hl7-org:sdtc');
        return $path;
    }

    /** @return \DOMNodeList<\DOMNameSpaceNode|\DOMNode> */
    private static function nodes(\DOMXPath $path, string $query): \DOMNodeList
    {
        $nodes = $path->query($query);
        self::assertInstanceOf(\DOMNodeList::class, $nodes);
        return $nodes;
    }

    /** @return list<string> */
    private static function values(\DOMXPath $path, string $query): array
    {
        $values = [];
        foreach (self::nodes($path, $query) as $node) {
            $values[] = self::text($node->nodeValue);
        }
        return $values;
    }

    /** An exported id's QDM id: what follows the document's guid (five dash-separated groups), or the whole id. */
    private static function lastPart(string $id): string
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}-(.+)$/', $id, $match) === 1 ? $match[1] : $id;
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

    /** A referral Task for the ServiceRequest $focusUuid in $status, and its id, kept for tearDown(). */
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

    /** A care plan intervention on 2026-03-02 coded as a referral to a specialist, as the care plan form records one. */
    private function carePlanReferral(int $pid): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `form_care_plan` (`id`, `date`, `pid`, `encounter`, `user`, `groupname`, `authorized`, `activity`, `code`, `codetext`, `description`, `care_plan_type`)"
            . " VALUES (?, '2026-03-02 00:00:00', ?, ?, 'test', 'Default', 1, 1, ?, 'Patient referral to specialist', 'CMS50 test', 'intervention')",
            [$pid, $pid, $pid, self::SPECIALIST],
        );
    }

    /** A synthetic patient with an office visit in the 2026 measurement period. */
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

    /** The placeholders for self::PIDS in an IN list. */
    private static function in(): string
    {
        return '(' . implode(', ', array_fill(0, count(self::PIDS), '?')) . ')';
    }

    /** The rows the synthetic patients still have in every table this test writes. */
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
