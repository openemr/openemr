<?php

/**
 * Orders and care plan items read from the database as the eCQMs read them: a device order
 * deleted in the order form (activity 0), and an order or care plan item whose form was deleted
 * from the visit (forms.deleted = 1), is left out by DeviceAppliedService, ProcedureService,
 * InterventionService, LaboratoryTestService and the AbstractCarePlanService services (here
 * InterventionOrderedService). One with no forms row, a kept forms row beside a deleted one, or
 * a form id another patient's deleted form shares, is kept. A lab test or procedure is read with the results of its own line of the
 * order, and without a result, or report, marked Error.
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
use OpenEMR\Services\Qdm\QdmRequestOne;
use OpenEMR\Services\Qdm\Services\AbstractCarePlanService;
use OpenEMR\Services\Qdm\Services\AbstractQdmService;
use OpenEMR\Services\Qdm\Services\DeviceAppliedService;
use OpenEMR\Services\Qdm\Services\InterventionOrderedService;
use OpenEMR\Services\Qdm\Services\InterventionService;
use OpenEMR\Services\Qdm\Services\LaboratoryTestService;
use OpenEMR\Services\Qdm\Services\ProcedureService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OrderServicesDatabaseTest extends TestCase
{
    // Synthetic patients; no patient_data rows are needed, and every row made here is removed.
    private const PID = 990618;
    private const OTHER_PID = 990619;
    // Care plan form ids, far above what a dev database holds; form_care_plan rows share their form's id.
    private const CARE_PLAN_FORM = 9906180001;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patients before the test');
    }

    protected function tearDown(): void
    {
        $pids = [self::PID, self::OTHER_PID];
        QueryUtils::sqlStatementThrowException(
            'DELETE RES FROM `procedure_result` AS RES JOIN `procedure_report` AS REP ON REP.`procedure_report_id` = RES.`procedure_report_id`'
            . ' JOIN `procedure_order` AS O ON O.`procedure_order_id` = REP.`procedure_order_id` WHERE O.`patient_id` IN (?, ?)',
            $pids,
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE REP FROM `procedure_report` AS REP JOIN `procedure_order` AS O ON O.`procedure_order_id` = REP.`procedure_order_id` WHERE O.`patient_id` IN (?, ?)',
            $pids,
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE OC FROM `procedure_order_code` AS OC JOIN `procedure_order` AS O ON O.`procedure_order_id` = OC.`procedure_order_id` WHERE O.`patient_id` IN (?, ?)',
            $pids,
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `procedure_order` WHERE `patient_id` IN (?, ?)', $pids);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_care_plan` WHERE `pid` IN (?, ?)', $pids);
        QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `pid` IN (?, ?)', $pids);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    /** @codeCoverageIgnore Data providers run before coverage instrumentation starts. */
    public static function orderServices(): \Generator
    {
        yield 'procedures' => [ProcedureService::class, 'order'];
        yield 'interventions' => [InterventionService::class, 'intervention'];
        yield 'laboratory tests' => [LaboratoryTestService::class, 'laboratory_test'];
        yield 'devices' => [DeviceAppliedService::class, 'device'];
    }

    /** @param class-string<AbstractQdmService> $service */
    #[DataProvider('orderServices')]
    public function testAnOrderWhoseFormWasDeletedFromTheVisitIsNotRead(string $service, string $type): void
    {
        $this->order($type, 'kept', form: 'kept');
        $this->order($type, 'form-deleted', form: 'deleted');
        $this->order($type, 'no-form');
        // Control: another patient's deleted form with this order's id hides nothing.
        $shared = $this->order($type, 'shared-id');
        $this->form('procedure_order', $shared, self::OTHER_PID, deleted: true);
        // One kept form is enough: a deleted second copy of the form doesn't hide the order.
        $copied = $this->order($type, 'one-copy-kept', form: 'kept');
        $this->form('procedure_order', $copied, self::PID, deleted: true);

        $this->assertSame(['kept', 'no-form', 'one-copy-kept', 'shared-id'], $this->codes($service, 'procedure_code'));
    }

    public function testADeviceOrderDeletedInTheOrderFormIsNotRead(): void
    {
        // Its three siblings already leave out activity 0; the device order didn't.
        $this->order('device', 'active');
        $this->order('device', 'inactive', activity: 0);

        $this->assertSame(['active'], $this->codes(DeviceAppliedService::class, 'procedure_code'));
    }

    public function testACarePlanItemWhoseFormWasDeletedFromTheVisitIsNotRead(): void
    {
        $this->carePlanItem(self::CARE_PLAN_FORM, 'kept');
        $this->form('care_plan', self::CARE_PLAN_FORM, self::PID, deleted: false);
        $this->carePlanItem(self::CARE_PLAN_FORM + 1, 'form-deleted');
        $this->form('care_plan', self::CARE_PLAN_FORM + 1, self::PID, deleted: true);
        $this->carePlanItem(self::CARE_PLAN_FORM + 2, 'no-form');
        // Control: another patient's deleted care plan form with the same id hides nothing.
        $this->carePlanItem(self::CARE_PLAN_FORM + 3, 'shared-id');
        $this->form('care_plan', self::CARE_PLAN_FORM + 3, self::OTHER_PID, deleted: true);

        $this->assertSame(['kept', 'no-form', 'shared-id'], $this->codes(InterventionOrderedService::class, 'code'));
    }

    /**
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     * @return \Generator<string, array{class-string<AbstractQdmService>, string, string}>
     */
    public static function resultServices(): \Generator
    {
        yield 'laboratory tests' => [LaboratoryTestService::class, 'laboratory_test', 'result'];
        yield 'procedures' => [ProcedureService::class, 'order', 'result_value'];
    }

    /** @param class-string<AbstractQdmService> $service */
    #[DataProvider('resultServices')]
    public function testEachResultIsReadWithItsOwnLinesCode(string $service, string $type, string $resultColumn): void
    {
        // One order of two tests, each with its own result: neither result is read under the other's code.
        $this->resultedOrder($type, [
            ['LOINC:4548-4', 'final', [['7.1', 'final']]],
            ['LOINC:2089-1', 'final', [['130', 'final']]],
        ]);

        $this->assertSame(['LOINC:2089-1 130', 'LOINC:4548-4 7.1'], $this->results($service, $resultColumn));
    }

    /** @param class-string<AbstractQdmService> $service */
    #[DataProvider('resultServices')]
    public function testAResultOrReportMarkedErrorIsNotRead(string $service, string $type, string $resultColumn): void
    {
        $this->resultedOrder($type, [['LOINC:4548-4', 'final', [['7.1', 'final'], ['9.9', 'error']]]]);
        $this->resultedOrder($type, [['LOINC:2089-1', 'error', [['130', 'final']]]]);
        // Kept: no status (each column's default, empty), preliminary and corrected.
        $this->resultedOrder($type, [['LOINC:2085-9', '', [['45', ''], ['46', 'prelim'], ['47', 'correct']]]]);

        $read = ['LOINC:2085-9 45', 'LOINC:2085-9 46', 'LOINC:2085-9 47', 'LOINC:4548-4 7.1'];
        if ($service === ProcedureService::class) {
            // The procedure whose report is marked Error is still read, without its result.
            $read = ['LOINC:2085-9 45', 'LOINC:2085-9 46', 'LOINC:2085-9 47', 'LOINC:2089-1 -', 'LOINC:4548-4 7.1'];
        }
        $this->assertSame($read, $this->results($service, $resultColumn));
    }

    /**
     * @param class-string<AbstractQdmService> $service
     * @return list<string> "code result" for each row the service reads (a missing result as -), sorted
     */
    private function results(string $service, string $resultColumn): array
    {
        $result = (new $service(new QdmRequestOne((string)self::PID), new CodeTypesService()))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $rows = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $code = $row['procedure_code'] ?? null;
            $value = $row[$resultColumn] ?? '-';
            $this->assertIsString($code);
            $this->assertIsString($value);
            $rows[] = "$code $value";
        }
        sort($rows);
        return $rows;
    }

    /**
     * @param class-string<AbstractQdmService> $service
     * @return list<string> the column's value for each row the service reads, sorted
     */
    private function codes(string $service, string $column): array
    {
        $result = (new $service(new QdmRequestOne((string)self::PID), new CodeTypesService()))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $codes = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $code = $row[$column] ?? null;
            $this->assertIsString($code);
            $codes[] = $code;
        }
        sort($codes);
        return $codes;
    }

    /**
     * An order of one code; a laboratory test gets a report and a result, which its service joins.
     *
     * @param 'kept'|'deleted'|null $form
     * @return int the order's id, which is its form's id
     */
    private function order(string $type, string $code, int $activity = 1, ?string $form = null): int
    {
        $id = QueryUtils::sqlInsert(
            "INSERT INTO `procedure_order` (`patient_id`, `encounter_id`, `date_ordered`, `procedure_order_type`, `activity`) VALUES (?, 0, '2026-03-02 09:00:00', ?, ?)",
            [self::PID, $type, $activity],
        );
        QueryUtils::sqlInsert(
            'INSERT INTO `procedure_order_code` (`procedure_order_id`, `procedure_order_seq`, `procedure_code`) VALUES (?, 1, ?)',
            [$id, $code],
        );
        if ($type === 'laboratory_test') {
            $report = QueryUtils::sqlInsert(
                "INSERT INTO `procedure_report` (`procedure_order_id`, `procedure_order_seq`, `date_report`) VALUES (?, 1, '2026-03-03 09:00:00')",
                [$id],
            );
            QueryUtils::sqlInsert(
                "INSERT INTO `procedure_result` (`procedure_report_id`, `result_code`, `result`, `date`) VALUES (?, '4548-4', '7.1', '2026-03-03 09:00:00')",
                [$report],
            );
        }
        if ($form !== null) {
            $this->form('procedure_order', $id, self::PID, deleted: $form === 'deleted');
        }
        return $id;
    }

    /**
     * An order of one or more lines, each with a report naming its line (procedure_order_seq, as the
     * results screen and the HL7 import file them) and the report's results.
     *
     * @param list<array{string, string, list<array{string, string}>}> $lines each line's code, its report's
     *                                                                          status, and its results' values and statuses
     */
    private function resultedOrder(string $type, array $lines): void
    {
        $id = QueryUtils::sqlInsert(
            "INSERT INTO `procedure_order` (`patient_id`, `encounter_id`, `date_ordered`, `procedure_order_type`, `activity`) VALUES (?, 0, '2026-03-02 09:00:00', ?, 1)",
            [self::PID, $type],
        );
        foreach ($lines as $index => [$code, $reportStatus, $results]) {
            $seq = $index + 1;
            QueryUtils::sqlInsert(
                'INSERT INTO `procedure_order_code` (`procedure_order_id`, `procedure_order_seq`, `procedure_code`) VALUES (?, ?, ?)',
                [$id, $seq, $code],
            );
            $report = QueryUtils::sqlInsert(
                "INSERT INTO `procedure_report` (`procedure_order_id`, `procedure_order_seq`, `date_report`, `report_status`) VALUES (?, ?, '2026-03-03 09:00:00', ?)",
                [$id, $seq, $reportStatus],
            );
            foreach ($results as [$value, $resultStatus]) {
                QueryUtils::sqlInsert(
                    "INSERT INTO `procedure_result` (`procedure_report_id`, `result_code`, `result`, `units`, `date`, `result_status`) VALUES (?, ?, ?, '%', '2026-03-03 09:00:00', ?)",
                    [$report, $code, $value, $resultStatus],
                );
            }
        }
    }

    private function carePlanItem(int $formId, string $code): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `form_care_plan` (`id`, `date`, `pid`, `encounter`, `user`, `groupname`, `authorized`, `activity`, `code`, `codetext`, `description`, `care_plan_type`)"
            . " VALUES (?, '2026-03-02 09:00:00', ?, '0', 'test', 'Default', 1, 1, ?, 'test item', 'test item', ?)",
            [$formId, self::PID, $code, AbstractCarePlanService::CARE_PLAN_TYPE_INTERVENTION],
        );
    }

    private function form(string $formdir, int $formId, int $pid, bool $deleted): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`)"
            . " VALUES ('2026-03-02 09:00:00', 0, 'test form', ?, ?, 'test', 'Default', 1, ?, ?)",
            [$formId, $pid, $deleted ? 1 : 0, $formdir],
        );
    }

    private function rowsLeft(): int
    {
        $pids = [self::PID, self::OTHER_PID];
        $count = 0;
        foreach (['SELECT COUNT(*) AS n FROM `procedure_order` WHERE `patient_id` IN (?, ?)', 'SELECT COUNT(*) AS n FROM `form_care_plan` WHERE `pid` IN (?, ?)', 'SELECT COUNT(*) AS n FROM `forms` WHERE `pid` IN (?, ?)'] as $sql) {
            $n = QueryUtils::fetchSingleValue($sql, 'n', $pids);
            $this->assertIsNumeric($n);
            $count += (int)$n;
        }
        return $count;
    }
}
