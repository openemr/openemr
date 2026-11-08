<?php

/**
 * The eCQM patient's payer (Patient Characteristic Payer, built by
 * Qdm\Services\PatientService): one element for each run of days a primary
 * policy covers, each with its own start and stop. The measure's logic keeps
 * the ones that overlap its period.
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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\QdmRequestOne;
use OpenEMR\Services\Qdm\Services\PatientService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PatientPayerDatabaseTest extends TestCase
{
    private const PID = 99995401;
    private const MEDICARE = 99995411;
    private const COMMERCIAL = 99995412;

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (uuid, pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, 'JANE', 'TESTPATIENT', '1950-01-01', 'Female', NOW())",
            [(new UuidRegistry(['table_name' => 'patient_data']))->createUuid(), self::PID, (string) self::PID]
        );
        // Source of Payment Typology: 1 Medicare, 5 private health insurance.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_companies (id, name, cqm_sop) VALUES (?, 'TEST-FIXTURE MEDICARE', 1), (?, 'TEST-FIXTURE COMMERCIAL', 5)",
            [self::MEDICARE, self::COMMERCIAL]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * A patient who changed plans has a payer for each, the first ending the
     * day before the second starts; a patient row for each policy is no longer
     * made.
     */
    #[Test]
    public function eachPrimaryHasAPayerWithItsOwnStartAndStop(): void
    {
        $this->addPolicy('primary', self::COMMERCIAL, '2020-01-01', '2025-06-30');
        $this->addPolicy('primary', self::MEDICARE, '2025-07-01', null);

        self::assertSame(
            ['patient_rows' => 1, 'payers' => [['5', '2020-01-01', '2025-06-30'], ['1', '2025-07-01', null]]],
            $this->payers()
        );
    }

    /**
     * Next year's card is a payer from its start; this year's plan, with no
     * end entered, is a payer until the day before.
     */
    #[Test]
    public function nextYearsCardStopsTheCurrentPayerTheDayBefore(): void
    {
        $this->addPolicy('primary', self::COMMERCIAL, '2020-01-01', null);
        $this->addPolicy('primary', self::MEDICARE, '2027-01-01', null);

        self::assertSame(
            ['patient_rows' => 1, 'payers' => [['5', '2020-01-01', '2026-12-31'], ['1', '2027-01-01', null]]],
            $this->payers()
        );
    }

    /**
     * An undated primary has been in force always: its payer has no start.
     */
    #[Test]
    public function anUndatedPrimaryHasNoStart(): void
    {
        $this->addPolicy('primary', self::MEDICARE, null, null);

        self::assertSame(['patient_rows' => 1, 'payers' => [['1', null, null]]], $this->payers());
    }

    /**
     * A patient with no primary keeps the one payer with no code.
     */
    #[Test]
    public function withNoPrimaryThereIsOnePayerWithNoCode(): void
    {
        $this->addPolicy('secondary', self::MEDICARE, '2020-01-01', null);

        self::assertSame(['patient_rows' => 1, 'payers' => [[null, null, null]]], $this->payers());
    }

    /**
     * @return array{patient_rows: int, payers: list<array{mixed, ?string, ?string}>}
     */
    private function payers(): array
    {
        $service = new PatientService(new QdmRequestOne((string) self::PID), new CodeTypesService());
        $result = $service->executeQuery();
        self::assertInstanceOf(\ADORecordSet::class, $result);
        $rows = 0;
        $last = null;
        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $rows++;
            $last = $row;
        }
        self::assertIsArray($last);
        $model = json_decode((string) json_encode($service->makeQdmModel(new QdmRecord($last, self::PID, self::PID))), true);
        self::assertIsArray($model);
        $payers = [];
        foreach (is_array($model['dataElements'] ?? null) ? $model['dataElements'] : [] as $element) {
            if (!is_array($element) || ($element['qdmStatus'] ?? null) !== 'payer') {
                continue;
            }
            $period = is_array($element['relevantPeriod'] ?? null) ? $element['relevantPeriod'] : [];
            $codes = is_array($element['dataElementCodes'] ?? null) ? $element['dataElementCodes'] : [];
            $code = is_array($codes[0] ?? null) ? ($codes[0]['code'] ?? null) : null;
            $day = static fn(mixed $date): ?string => is_string($date) && $date !== '' ? substr($date, 0, 10) : null;
            $payers[] = [$code, $day($period['low'] ?? null), $day($period['high'] ?? null)];
        }
        return ['patient_rows' => $rows, 'payers' => $payers];
    }

    private function addPolicy(string $type, int $company, ?string $start, ?string $end): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, pid, type, provider, date, date_end, plan_name) VALUES (?, ?, ?, ?, ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), self::PID, $type, (string) $company, $start, $end]
        );
    }

    private function cleanUp(): void
    {
        foreach (['insurance_data', 'patient_data'] as $table) {
            foreach (QueryUtils::fetchTableColumn("SELECT uuid FROM `$table` WHERE pid = ? AND uuid IS NOT NULL", 'uuid', [self::PID]) as $uuid) {
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE table_name = ? AND uuid = ?', [$table, $uuid]);
            }
        }
        foreach (QueryUtils::fetchTableColumn('SELECT uuid FROM insurance_companies WHERE id IN (?, ?) AND uuid IS NOT NULL', 'uuid', [self::MEDICARE, self::COMMERCIAL]) as $uuid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'insurance_companies' AND uuid = ?", [$uuid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_companies WHERE id IN (?, ?)', [self::MEDICARE, self::COMMERCIAL]);
    }
}
