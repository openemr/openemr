<?php

/**
 * Observations read from the database as the eCQMs read them (AbstractObservationService, here
 * through PhysicalExamService): one deleted in the Observation form, or whose Observation form was
 * deleted from the visit, is left out; an import's, with no form, is kept.
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
use OpenEMR\Services\Qdm\Services\PhysicalExamService;
use PHPUnit\Framework\TestCase;

class PhysicalExamServiceDatabaseTest extends TestCase
{
    // Synthetic patients; no patient_data rows are needed, and every row made here is removed.
    private const PID = 990613;
    private const OTHER_PID = 990614;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patient before the test');
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `pid` IN (?, ?)', [self::PID, self::OTHER_PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_observation` WHERE `pid` IN (?, ?)', [self::PID, self::OTHER_PID]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testADeletedObservationIsNotRead(): void
    {
        $this->observation('120', form: 'kept');
        $this->observation('121', activity: 0, form: 'kept');
        $this->observation('122', form: 'deleted');
        $this->observation('123');
        $this->observation('124', activity: null);

        $this->assertSame(['120', '123', '124'], $this->values(), 'deleted in the form (activity 0), or its form deleted from the visit: left out');
    }

    public function testEveryObservationOfAFormThatIsKeptIsRead(): void
    {
        // Two readings in one Observation form, and the form's other rows are not mistaken for
        // another form's: form ids repeat across formdirs, so only formdir 'observation' counts.
        $form = $this->observation('130', form: 'kept');
        $this->observation('131', formId: $form);
        QueryUtils::sqlInsert(
            "INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`)"
            . " VALUES ('2026-03-02 09:00:00', 0, 'Vitals', ?, ?, 'test', 'Default', 1, 1, 'vitals')",
            [$form, self::PID],
        );

        $this->assertSame(['130', '131'], $this->values());
    }

    public function testAnotherPatientsDeletedFormWithTheSameIdHidesNothing(): void
    {
        // Two writers pick form_id with an unlocked MAX() + 1, so two patients' forms can share one.
        $form = $this->observation('140', form: 'kept');
        QueryUtils::sqlInsert(
            "INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`)"
            . " VALUES ('2026-03-02 09:00:00', 0, 'Observation', ?, ?, 'test', 'Default', 1, 1, 'observation')",
            [$form, self::OTHER_PID],
        );

        $this->assertSame(['140'], $this->values());
    }

    /** @return list<string> the ob_value of each observation read, in the query's order */
    private function values(): array
    {
        $result = (new PhysicalExamService(new QdmRequestOne((string)self::PID), new CodeTypesService()))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $values = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $value = $row['ob_value'] ?? null;
            $this->assertIsString($value);
            $values[] = $value;
        }
        sort($values);
        return $values;
    }

    /**
     * Adds a systolic blood pressure exam: with no form (form_id 0, as the QRDA import writes them),
     * in a new Observation form that is kept or deleted, or in an existing form.
     *
     * @param 'kept'|'deleted'|null $form
     * @return int the form_id it was saved under
     */
    private function observation(string $value, ?int $activity = 1, ?string $form = null, int $formId = 0): int
    {
        if ($form !== null) {
            $formId = (int)self::number(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(`form_id`), 0) + 1 AS id FROM `form_observation`', 'id'));
            QueryUtils::sqlInsert(
                "INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`)"
                . " VALUES ('2026-03-02 09:00:00', 0, 'Observation', ?, ?, 'test', 'Default', 1, ?, 'observation')",
                [$formId, self::PID, $form === 'deleted' ? 1 : 0],
            );
        }
        QueryUtils::sqlInsert(
            "INSERT INTO `form_observation` (`form_id`, `date`, `pid`, `encounter`, `user`, `groupname`, `authorized`, `activity`,"
            . " `code`, `observation`, `ob_value`, `ob_unit`, `ob_type`) VALUES (?, '2026-03-02 09:00:00', ?, 0, 'test', 'Default', 1, ?, 'LOINC:8480-6', 'test exam', ?, 'mm[Hg]', ?)",
            [$formId, self::PID, $activity, $value, PhysicalExamService::OB_TYPE_PHYSICAL_EXAM],
        );
        return $formId;
    }

    private function rowsLeft(): int
    {
        $pids = [self::PID, self::OTHER_PID];
        return (int)self::number(QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM `forms` WHERE `pid` IN (?, ?)', 'n', $pids))
            + (int)self::number(QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM `form_observation` WHERE `pid` IN (?, ?)', 'n', $pids));
    }

    private static function number(mixed $value): int|float|string
    {
        self::assertIsNumeric($value);
        return $value;
    }
}
