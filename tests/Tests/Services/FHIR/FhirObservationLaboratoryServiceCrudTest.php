<?php

/**
 * FHIR Observation (laboratory) write tests against the database (#14343): a result
 * written against its order reads back the same through the lab Observation read, lands
 * on the right order line and report, and is refused when it names no usable order or a
 * code the order doesn't carry.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\Services\FHIR\FhirObservationService;
use OpenEMR\Services\ProcedureResultService;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcedureResultValidator;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FhirObservationLaboratoryServiceCrudTest extends TestCase
{
    private const GLUCOSE = '2345-7';
    private const HEMOGLOBIN = '718-7';

    private FixtureManager $fixtureManager;
    private FhirObservationService $fhirObservationService;
    private string $patientUuid;
    private int $pid;
    private string $orderUuid;
    private int $orderId;
    /** @var list<int> Orders created by the tests, removed in tearDown(). */
    private array $orderIds = [];
    /** @var array<string, int> */
    private array $countsBefore;
    /** @var list<string> form_encounter uuids (bytes) the tests insert, removed in tearDown(). */
    private array $encounterUuids = [];

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->countsBefore = self::counts();
        $this->fixtureManager->installPatientFixtures();

        [$this->patientUuid, $this->pid] = $this->patient(0);

        // Glucose is a bare LOINC code (as the ServiceRequest write stores it); hemoglobin
        // carries the LOINC: prefix the order screen can store.
        [$this->orderId, $this->orderUuid] = $this->createOrder($this->pid, [
            ['procedure_code' => self::GLUCOSE, 'procedure_name' => 'Glucose [Mass/volume] in Serum or Plasma'],
            ['procedure_code' => 'LOINC:' . self::HEMOGLOBIN, 'procedure_name' => 'Hemoglobin [Mass/volume] in Blood'],
        ]);

        $session = new Session(new MockArraySessionStorage());
        $session->set('authUserID', 1);
        $this->fhirObservationService = new FhirObservationService();
        $this->fhirObservationService->setLogger($this->createMock(LoggerInterface::class));
        $this->fhirObservationService->setSession($session);
    }

    protected function tearDown(): void
    {
        foreach ($this->orderIds as $orderId) {
            $reportIds = QueryUtils::fetchTableColumn(
                'SELECT procedure_report_id FROM procedure_report WHERE procedure_order_id = ?',
                'procedure_report_id',
                [$orderId]
            );
            foreach ($reportIds as $reportId) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM uuid_registry WHERE table_name = ? AND uuid IN (SELECT uuid FROM procedure_result WHERE procedure_report_id = ?)',
                    ['procedure_result', $reportId]
                );
                QueryUtils::sqlStatementThrowException('DELETE FROM procedure_result WHERE procedure_report_id = ?', [$reportId]);
            }
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM uuid_registry WHERE table_name = ? AND uuid IN (SELECT uuid FROM procedure_report WHERE procedure_order_id = ?)',
                ['procedure_report', $orderId]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM procedure_report WHERE procedure_order_id = ?', [$orderId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order_code WHERE procedure_order_id = ?', [$orderId]);
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM uuid_registry WHERE table_name = ? AND uuid IN (SELECT uuid FROM procedure_order WHERE procedure_order_id = ?)',
                ['procedure_order', $orderId]
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM forms WHERE form_id = ? AND formdir = 'procedure_order'",
                [$orderId]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order WHERE procedure_order_id = ?', [$orderId]);
        }
        foreach ($this->encounterUuids as $encounterUuid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE uuid = ?', [$encounterUuid]);
        }
        $this->fixtureManager->removePatientFixtures();

        $this->assertSame($this->countsBefore, self::counts(), 'the test leaves no rows behind');
    }

    /**
     * Rows in the tables these tests write, uuid_registry included.
     *
     * @return array<string, int>
     */
    private static function counts(): array
    {
        $counts = [];
        foreach (['procedure_order', 'procedure_order_code', 'procedure_report', 'procedure_result'] as $table) {
            $counts[$table] = self::intValue(QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM `$table`", 'n'));
        }
        $counts['uuid_registry'] = self::intValue(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM uuid_registry WHERE table_name IN ('procedure_order', 'procedure_report', 'procedure_result')",
            'n'
        ));
        return $counts;
    }

    /**
     * @return array{string, int}
     */
    private function patient(int $index): array
    {
        $fixture = $this->fixtureManager->getPatientFixtures()[$index] ?? null;
        $this->assertIsArray($fixture);
        $row = QueryUtils::querySingleRow('SELECT uuid, pid FROM patient_data WHERE pubpid = ?', [$fixture['pubpid']]);
        $this->assertIsArray($row);
        return [UuidRegistry::uuidToString($row['uuid']), self::intValue($row['pid'])];
    }

    /**
     * @param list<array<string, string>> $codes
     * @return array{int, string}
     */
    private function createOrder(int $pid, array $codes): array
    {
        $created = (new ProcedureService())->createOrder(['patient_id' => $pid], $codes);
        $this->assertTrue($created->isValid(), (string) json_encode(self::messages($created)));
        $row = self::firstRow($created);
        $orderId = self::intValue($row['procedure_order_id']);
        $this->orderIds[] = $orderId;
        $this->assertIsString($row['uuid']);
        return [$orderId, $row['uuid']];
    }

    /**
     * A bare encounter row for the patient, outside the uuid registry, so nothing but the row
     * itself needs removing.
     *
     * @return array{int, string} The encounter number and its uuid.
     */
    private function createEncounter(int $pid): array
    {
        $uuid = random_bytes(16);
        $encounter = random_int(900000000, 999999999);
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO form_encounter (uuid, encounter, pid, date) VALUES (?, ?, ?, NOW())',
            [$uuid, $encounter, $pid]
        );
        $this->encounterUuids[] = $uuid;
        return [$encounter, UuidRegistry::uuidToString($uuid)];
    }

    /**
     * @param array<string, mixed> $overrides A null value removes the element.
     * @return array<string, mixed>
     */
    private function labObservation(array $overrides = []): array
    {
        $base = [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => 'laboratory',
                ]],
            ]],
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => self::GLUCOSE, 'display' => 'Glucose']]],
            'subject' => ['reference' => 'Patient/' . $this->patientUuid],
            'basedOn' => [['reference' => 'ServiceRequest/' . $this->orderUuid]],
            'effectiveDateTime' => '2026-03-04T09:30:00+00:00',
            'valueQuantity' => ['value' => 5.2, 'unit' => 'mmol/L', 'system' => 'http://unitsofmeasure.org', 'code' => 'mmol/L'],
        ];
        return array_filter(array_merge($base, $overrides), fn($value): bool => $value !== null);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function post(array $payload): ProcessingResult
    {
        return $this->fhirObservationService->insert(new FHIRObservation($payload));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function postedUuid(array $payload): string
    {
        $result = $this->post($payload);
        $this->assertTrue($result->isValid(), 'POST failed: ' . json_encode(self::messages($result)) . json_encode($result->getInternalErrors()));
        $row = self::firstRow($result);
        $this->assertIsString($row['uuid']);
        return $row['uuid'];
    }

    /**
     * Reads one Observation back through the FHIR read, as a client would.
     *
     * @return array<string, mixed>
     */
    private function readBack(string $uuid): array
    {
        $found = $this->fhirObservationService->getAll(['_id' => $uuid]);
        $this->assertTrue($found->isValid());
        $data = $found->getData();
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $observation = $data[0];
        $this->assertInstanceOf(FHIRObservation::class, $observation);
        $json = json_decode((string) json_encode($observation), true);
        $this->assertIsArray($json);
        $keyed = [];
        foreach ($json as $key => $value) {
            $keyed[(string) $key] = $value;
        }
        return $keyed;
    }

    /**
     * @return array<string, mixed>
     */
    private static function resultRow(string $uuid): array
    {
        $row = QueryUtils::querySingleRow(
            'SELECT res.*, rep.procedure_order_id, rep.procedure_order_seq, rep.review_status, rep.date_report, rep.date_collected'
            . ' FROM procedure_result res JOIN procedure_report rep ON rep.procedure_report_id = res.procedure_report_id'
            . ' WHERE res.uuid = ?',
            [UuidRegistry::uuidToBytes($uuid)]
        );
        self::assertIsArray($row);
        $keyed = [];
        foreach ($row as $key => $value) {
            $keyed[(string) $key] = $value;
        }
        return $keyed;
    }

    private static function intValue(mixed $value): int
    {
        self::assertTrue(is_int($value) || (is_string($value) && ctype_digit($value)), 'expected an integer, got ' . var_export($value, true));
        return (int) $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertRefused(array $payload, string $element): void
    {
        $before = self::counts();
        $result = $this->post($payload);
        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey($element, self::messages($result), (string) json_encode(self::messages($result)));
        $this->assertSame($before, self::counts(), 'a refused write writes nothing');
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function messages(ProcessingResult $result): array
    {
        $messages = $result->getValidationMessages();
        self::assertIsArray($messages);
        return $messages;
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function firstRow(ProcessingResult $result): array
    {
        $data = $result->getData();
        self::assertIsArray($data);
        $row = $data[0] ?? null;
        self::assertIsArray($row);
        return $row;
    }

    /**
     * Walks a decoded FHIR body by keys, returning null where a step is missing.
     */
    private static function at(mixed $data, string|int ...$path): mixed
    {
        foreach ($path as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

    public function testPostWritesTheResultOnItsOrderLineAndReadsBackTheSame(): void
    {
        $uuid = $this->postedUuid($this->labObservation([
            'status' => 'preliminary',
            'referenceRange' => [['low' => ['value' => 0], 'high' => ['value' => 5.5]]],
            'interpretation' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'H']]]],
            'note' => [['text' => 'fasting']],
        ]));

        $row = self::resultRow($uuid);
        $this->assertSame($this->orderId, self::intValue($row['procedure_order_id']));
        $this->assertSame(1, self::intValue($row['procedure_order_seq']));
        $this->assertSame(self::GLUCOSE, $row['result_code']);
        $this->assertSame('Glucose', $row['result_text']);
        $this->assertSame('N', $row['result_data_type']);
        $this->assertSame('5.2', $row['result']);
        $this->assertSame('0-5.5', $row['range']);
        $this->assertSame('high', $row['abnormal']);
        $this->assertSame('prelim', $row['result_status']);
        $this->assertSame('received', $row['review_status'], 'a result from an app waits for review, like an imported one');
        $this->assertSame('2026-03-04 09:30:00', $row['date']);
        $this->assertSame($row['date'], $row['date_collected']);
        $this->assertSame($row['date'], $row['date_report']);

        $read = $this->readBack($uuid);
        $this->assertSame('preliminary', $read['status']);
        $this->assertSame(self::GLUCOSE, self::at($read, 'code', 'coding', 0, 'code'));
        $this->assertEqualsWithDelta(5.2, self::at($read, 'valueQuantity', 'value'), 0.0001);
        $this->assertSame('mmol/L', self::at($read, 'valueQuantity', 'unit'));
        $this->assertEqualsWithDelta(0.0, self::at($read, 'referenceRange', 0, 'low', 'value'), 0.0001);
        $this->assertEqualsWithDelta(5.5, self::at($read, 'referenceRange', 0, 'high', 'value'), 0.0001);
        $this->assertSame('H', self::at($read, 'interpretation', 0, 'coding', 0, 'code'));
        $this->assertSame('fasting', self::at($read, 'note', 0, 'text'));
        $this->assertSame('Patient/' . $this->patientUuid, self::at($read, 'subject', 'reference'));
        $this->assertSame('ServiceRequest/' . $this->orderUuid, self::at($read, 'basedOn', 0, 'reference'));
        $this->assertSame(
            strtotime('2026-03-04T09:30:00+00:00'),
            strtotime(is_string($read['effectiveDateTime'] ?? null) ? $read['effectiveDateTime'] : ''),
        );
    }

    public function testAZeroValueAndATextRangeReadBack(): void
    {
        $uuid = $this->postedUuid($this->labObservation([
            'valueQuantity' => ['value' => 0, 'unit' => '/[HPF]', 'code' => '/[HPF]'],
            'referenceRange' => [['text' => 'none seen']],
        ]));

        $read = $this->readBack($uuid);
        $value = self::at($read, 'valueQuantity', 'value');
        $this->assertTrue(is_int($value) || is_float($value), 'a 0 result is served as a number');
        $this->assertSame(0.0, (float) $value);
        $this->assertArrayNotHasKey('dataAbsentReason', $read);
        $this->assertSame('none seen', self::at($read, 'referenceRange', 0, 'text'));
    }

    public function testTheCodeChoosesTheLineAndASecondResultReusesItsReport(): void
    {
        $first = self::resultRow($this->postedUuid($this->labObservation([
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => self::HEMOGLOBIN]]],
            'valueQuantity' => ['value' => 13.5, 'unit' => 'g/dL', 'code' => 'g/dL'],
        ])));
        $second = self::resultRow($this->postedUuid($this->labObservation([
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => self::HEMOGLOBIN]]],
            'valueQuantity' => ['value' => 13.9, 'unit' => 'g/dL', 'code' => 'g/dL'],
        ])));

        $this->assertSame(2, self::intValue($first['procedure_order_seq']), 'LOINC:718-7 is the second line');
        $this->assertSame('Hemoglobin [Mass/volume] in Blood', $first['result_text'], 'with no display, the line names the result');
        $this->assertSame($first['procedure_report_id'], $second['procedure_report_id']);
    }

    public function testAResultAfterReviewGoesToANewReport(): void
    {
        $first = self::resultRow($this->postedUuid($this->labObservation()));
        QueryUtils::sqlStatementThrowException(
            "UPDATE procedure_report SET review_status = 'reviewed' WHERE procedure_report_id = ?",
            [$first['procedure_report_id']]
        );

        $second = self::resultRow($this->postedUuid($this->labObservation(['valueQuantity' => ['value' => 6.1, 'code' => 'mmol/L']])));

        $this->assertNotSame($first['procedure_report_id'], $second['procedure_report_id']);
        $this->assertSame('received', $second['review_status']);
    }

    public function testAResultReportedAtAnotherTimeGetsItsOwnReport(): void
    {
        $first = self::resultRow($this->postedUuid($this->labObservation()));
        $laterUuid = $this->postedUuid($this->labObservation([
            'effectiveDateTime' => '2026-03-11T09:30:00+00:00',
            'valueQuantity' => ['value' => 6.1, 'code' => 'mmol/L'],
        ]));
        $later = self::resultRow($laterUuid);

        $this->assertNotSame($first['procedure_report_id'], $later['procedure_report_id']);
        // The read dates a result by its report, so each keeps its own date.
        $effective = $this->readBack($laterUuid)['effectiveDateTime'] ?? null;
        $this->assertSame(strtotime('2026-03-11T09:30:00+00:00'), strtotime(is_string($effective) ? $effective : ''));
    }

    public function testAnUnchangedPutLeavesASignedReportSigned(): void
    {
        $uuid = $this->postedUuid($this->labObservation(['note' => [['text' => 'fasting']]]));
        $before = self::resultRow($uuid);
        QueryUtils::sqlStatementThrowException(
            "UPDATE procedure_report SET review_status = 'reviewed' WHERE procedure_report_id = ?",
            [$before['procedure_report_id']]
        );

        // What the read serves, sent straight back: a retry or a sync, not a change.
        $read = $this->readBack($uuid);
        $updated = $this->fhirObservationService->update($uuid, new FHIRObservation($read));
        $this->assertTrue($updated->isValid(), (string) json_encode(self::messages($updated)));

        $this->assertSame('reviewed', self::resultRow($uuid)['review_status']);
    }

    public function testPutChangesTheValueAndSendsASignedReportBackToReview(): void
    {
        $uuid = $this->postedUuid($this->labObservation());
        $before = self::resultRow($uuid);
        QueryUtils::sqlStatementThrowException(
            "UPDATE procedure_report SET review_status = 'reviewed' WHERE procedure_report_id = ?",
            [$before['procedure_report_id']]
        );

        $changed = $this->labObservation([
            'id' => $uuid,
            'status' => 'corrected',
            'valueQuantity' => ['value' => 6.4, 'unit' => 'mmol/L', 'code' => 'mmol/L'],
            'referenceRange' => [['low' => ['value' => 3.9], 'high' => ['value' => 6.1]]],
            'interpretation' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'H']]]],
            'note' => [['text' => 'corrected after recalibration']],
        ]);
        $updated = $this->fhirObservationService->update($uuid, new FHIRObservation($changed));
        $this->assertTrue($updated->isValid(), (string) json_encode(self::messages($updated)));
        $data = $updated->getData();
        $response = is_array($data) ? ($data[0] ?? null) : null;
        $this->assertInstanceOf(FHIRObservation::class, $response, 'PUT answers with the result as the read serves it');

        $after = self::resultRow($uuid);
        $this->assertSame('6.4', $after['result']);
        $this->assertSame('correct', $after['result_status']);
        $this->assertSame('3.9-6.1', $after['range']);
        $this->assertSame('high', $after['abnormal']);
        $this->assertSame('corrected after recalibration', $after['comments']);
        $this->assertSame('received', $after['review_status'], 'the changed result is reviewed again');
        foreach (['procedure_report_id', 'result_code', 'result_text', 'date'] as $unchanged) {
            $this->assertSame($before[$unchanged], $after[$unchanged], $unchanged);
        }
        $this->assertSame('corrected', $this->readBack($uuid)['status']);
    }

    public function testPutCannotMoveTheResult(): void
    {
        $uuid = $this->postedUuid($this->labObservation());
        [$otherOrderId, $otherOrderUuid] = $this->createOrder($this->pid, [['procedure_code' => self::GLUCOSE]]);
        $this->assertNotSame($this->orderId, $otherOrderId);

        $cases = [
            'basedOn' => ['basedOn' => [['reference' => 'ServiceRequest/' . $otherOrderUuid]]],
            'code' => ['code' => ['coding' => [['system' => 'http://loinc.org', 'code' => self::HEMOGLOBIN]]]],
            'effectiveDateTime' => ['effectiveDateTime' => '2026-03-05T09:30:00+00:00'],
            'issued' => ['issued' => '2026-03-06T09:30:00+00:00'],
        ];
        foreach ($cases as $element => $overrides) {
            $updated = $this->fhirObservationService->update(
                $uuid,
                new FHIRObservation($this->labObservation(['id' => $uuid, 'valueQuantity' => ['value' => 9.9, 'code' => 'mmol/L']] + $overrides))
            );
            $this->assertFalse($updated->isValid(), $element);
            $this->assertArrayHasKey($element, self::messages($updated), (string) json_encode(self::messages($updated)));
        }
        $this->assertSame('5.2', self::resultRow($uuid)['result'], 'no refused PUT changed the value');

        // A result whose order has since been deleted is not served, so it can't be changed either.
        QueryUtils::sqlStatementThrowException('UPDATE procedure_order SET activity = 0 WHERE procedure_order_id = ?', [$this->orderId]);
        $updated = $this->fhirObservationService->update($uuid, new FHIRObservation($this->labObservation(['id' => $uuid])));
        $this->assertArrayHasKey('uuid', self::messages($updated));
    }

    public function testRefusesAResultWithoutAUsableOrder(): void
    {
        [$otherPatientUuid, $otherPid] = $this->patient(1);
        [, $otherPatientsOrderUuid] = $this->createOrder($otherPid, [['procedure_code' => self::GLUCOSE]]);
        [$deletedOrderId, $deletedOrderUuid] = $this->createOrder($this->pid, [['procedure_code' => self::GLUCOSE]]);
        QueryUtils::sqlStatementThrowException('UPDATE procedure_order SET activity = 0 WHERE procedure_order_id = ?', [$deletedOrderId]);
        [$formDeletedOrderId, $formDeletedOrderUuid] = $this->createOrder($this->pid, [['procedure_code' => self::GLUCOSE]]);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir)"
            . " VALUES (NOW(), 0, 'Procedure Order', ?, ?, 'admin', 'Default', 1, 1, 'procedure_order')",
            [$formDeletedOrderId, $this->pid]
        );
        $this->assertNotSame('', $otherPatientUuid);

        $this->assertRefused($this->labObservation(['basedOn' => null]), 'basedOn');
        $this->assertRefused($this->labObservation(['basedOn' => [['reference' => 'ServiceRequest/' . '4d5e6f7a-8b9c-4d0e-9f1a-2b3c4d5e6f7a']]]), 'basedOn');
        $this->assertRefused($this->labObservation(['basedOn' => [['reference' => 'ServiceRequest/' . $otherPatientsOrderUuid]]]), 'basedOn');
        $this->assertRefused($this->labObservation(['basedOn' => [['reference' => 'ServiceRequest/' . $deletedOrderUuid]]]), 'basedOn');
        $this->assertRefused($this->labObservation(['basedOn' => [['reference' => 'ServiceRequest/' . $formDeletedOrderUuid]]]), 'basedOn');
    }

    public function testAnEncounterSentMustBeTheOrders(): void
    {
        [$orderEncounter, $orderEncounterUuid] = $this->createEncounter($this->pid);
        [, $otherEncounterUuid] = $this->createEncounter($this->pid);
        QueryUtils::sqlStatementThrowException(
            'UPDATE procedure_order SET encounter_id = ? WHERE procedure_order_id = ?',
            [$orderEncounter, $this->orderId]
        );

        $this->assertRefused($this->labObservation(['encounter' => ['reference' => 'Encounter/' . $otherEncounterUuid]]), 'encounter');
        $this->assertRefused($this->labObservation(['encounter' => ['reference' => 'Encounter/5e6f7a8b-9c0d-4e1f-8a2b-3c4d5e6f7a8b']]), 'encounter');

        $uuid = $this->postedUuid($this->labObservation(['encounter' => ['reference' => 'Encounter/' . $orderEncounterUuid]]));
        $this->assertSame('Encounter/' . $orderEncounterUuid, self::at($this->readBack($uuid), 'encounter', 'reference'));

        $moved = $this->fhirObservationService->update($uuid, new FHIRObservation($this->labObservation([
            'id' => $uuid,
            'encounter' => ['reference' => 'Encounter/' . $otherEncounterUuid],
        ])));
        $this->assertArrayHasKey('encounter', self::messages($moved));
    }

    public function testRefusesACodeNotOnTheOrder(): void
    {
        $this->assertRefused(
            $this->labObservation(['code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '2160-0', 'display' => 'Creatinine']]]]),
            'code'
        );
        // The order line's code compared byte for byte: a case or spacing variant isn't it.
        $this->assertRefused(
            $this->labObservation(['code' => ['coding' => [['system' => 'http://loinc.org', 'code' => 'loinc:' . self::GLUCOSE]]]]),
            'code'
        );
    }

    public function testRefusesWhatWouldNotReadBack(): void
    {
        $this->assertRefused($this->labObservation(['valueQuantity' => ['value' => 1, 'code' => str_repeat('u', 32)]]), 'valueQuantity');
        $this->assertRefused($this->labObservation(['valueQuantity' => null, 'valueString' => str_repeat('x', 256)]), 'value');
        $this->assertRefused(
            $this->labObservation(['interpretation' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'POS']]]]]),
            'interpretation'
        );
        $this->assertRefused(
            $this->labObservation(['valueQuantity' => null, 'valueCodeableConcept' => ['coding' => [['system' => 'http://example.org/not-a-code-system', 'code' => 'NEG']]]]),
            'valueCodeableConcept'
        );
        $this->assertRefused($this->labObservation(['effectiveDateTime' => null]), 'effectiveDateTime');
    }

    /**
     * The report and the result are one write: a failure after the report is inserted
     * leaves neither behind, nor the report's uuid_registry row.
     */
    public function testAFailureAfterTheReportIsInsertedRollsBackTheReport(): void
    {
        $failingResultUuids = $this->createMock(UuidRegistry::class);
        $failingResultUuids->method('createUuid')->willThrowException(new \RuntimeException('result uuid unavailable'));
        $service = new ProcedureResultService(
            new ProcedureResultValidator(),
            new UuidRegistry(['table_name' => 'procedure_report', 'table_id' => 'procedure_report_id']),
            $failingResultUuids,
        );
        $before = self::counts();

        try {
            $service->insertResult(
                $this->orderId,
                $this->pid,
                null,
                [self::GLUCOSE],
                [
                    'result_data_type' => 'N', 'result_code' => '', 'result_text' => '', 'date' => '2026-03-04 09:30:00',
                    'units' => 'mmol/L', 'result' => '5.2', 'range' => '', 'abnormal' => '', 'comments' => '', 'result_status' => 'final',
                ],
                '2026-03-04 09:30:00',
                1,
            );
            $this->fail('the insert should have thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('result uuid unavailable', $e->getMessage());
        }

        $this->assertSame($before, self::counts());
    }
}
