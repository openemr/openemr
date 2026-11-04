<?php

/**
 * A QRDA Category I import's payer (the Patient Characteristic Payer entry, a Source of Payment
 * Typology code and a start date) is meant to become the patient's primary policy, but
 * InsertPayers() saves none: the coverage validator refuses its insert, which has no subscriber,
 * and the import only logs that.
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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Cda\CdaTemplateImportDispose;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CdaImportPayerTest extends TestCase
{
    // A synthetic patient, and the Source of Payment Typology code 9999 (Unavailable / Unknown),
    // which no insurance company in a test database carries. Every row made here is removed.
    private const PID = 990652;
    private const SOP_CODE = '9999';
    private const COMPANY_NAME = 'QRDA Insurance Company Payer ' . self::SOP_CODE;

    private CdaTemplateImportDispose $dispose;
    private CarecoordinationTable&MockObject $table;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patient or company before the test');
        QueryUtils::sqlInsert(
            'INSERT INTO `patient_data` (`pid`, `pubpid`, `fname`, `lname`, `DOB`, `sex`, `street`, `city`, `state`, `postal_code`, `date`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [self::PID, 'qrda-payer-' . self::PID, 'Pia', 'Payertest', '1960-05-01', 'Female', '1 Main St', 'Barre', 'VT', '05641']
        );
        $this->dispose = new CdaTemplateImportDispose();
        $this->table = $this->createMock(CarecoordinationTable::class);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_data' AND `uuid` IN (SELECT `uuid` FROM `insurance_data` WHERE `pid` = ?)",
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `insurance_data` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `addresses` WHERE `foreign_id` IN (SELECT `id` FROM `insurance_companies` WHERE `name` = ?)',
            [self::COMPANY_NAME]
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_companies' AND `uuid` IN (SELECT `uuid` FROM `insurance_companies` WHERE `name` = ?)",
            [self::COMPANY_NAME]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `insurance_companies` WHERE `name` = ?', [self::COMPANY_NAME]);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'patient_data' AND `uuid` IN (SELECT `uuid` FROM `patient_data` WHERE `pid` = ?)",
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `patient_data` WHERE `pid` = ?', [self::PID]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testAnImportedPayerIsSavedAsThePrimaryPolicy(): void
    {
        $this->dispose->InsertPayers([1 => $this->payer('20240115')], self::PID, $this->table, 0);

        $this->assertSame(
            [['type' => 'primary', 'date' => '2024-01-15', 'cqm_sop' => self::SOP_CODE]],
            $this->policies(),
            "the document's payer is the patient's primary policy, from the payer's start date"
        );
    }

    public function testImportingAgainKeepsTheStoredPolicysSubscriber(): void
    {
        // A primary policy entered by staff, with its subscriber, before the document is imported.
        QueryUtils::sqlInsert(
            "INSERT INTO `insurance_data` (`uuid`, `pid`, `type`, `provider`, `policy_number`, `date`, `subscriber_lname`, `subscriber_fname`,"
            . " `subscriber_relationship`, `subscriber_DOB`, `subscriber_sex`) VALUES (?, ?, 'primary', 0, 'STAFF-1', '2023-01-01', 'Payertest', 'Sam', 'spouse', '1958-02-02', 'Male')",
            [UuidRegistry::getRegistryForTable('insurance_data')->createUuid(), self::PID]
        );

        $this->dispose->InsertPayers([1 => $this->payer('20240115')], self::PID, $this->table, 0);

        $row = QueryUtils::fetchRecords(
            'SELECT `subscriber_lname`, `subscriber_fname`, `subscriber_relationship`, `subscriber_DOB` FROM `insurance_data` WHERE `pid` = ? AND `policy_number` = ?',
            [self::PID, 'STAFF-1']
        );
        $this->assertSame(
            [['subscriber_lname' => 'Payertest', 'subscriber_fname' => 'Sam', 'subscriber_relationship' => 'spouse', 'subscriber_DOB' => '1958-02-02']],
            $row,
            "importing a payer doesn't clear the subscriber staff entered on the patient's policy"
        );
    }

    /**
     * A payer as the import hands it to InsertPayers(): the Source of Payment Typology code, the
     * entry's status and its effectiveTime as written in the document.
     *
     * @return array{code: string, status: string, low_date: string, high_date: null}
     */
    private function payer(string $lowDate): array
    {
        return ['code' => self::SOP_CODE, 'status' => 'active', 'low_date' => $lowDate, 'high_date' => null];
    }

    /**
     * @return list<array<mixed>>
     */
    private function policies(): array
    {
        return QueryUtils::fetchRecords(
            'SELECT `i`.`type`, `i`.`date`, `c`.`cqm_sop` FROM `insurance_data` AS `i`'
            . ' JOIN `insurance_companies` AS `c` ON `c`.`id` = `i`.`provider` WHERE `i`.`pid` = ? ORDER BY `i`.`id`',
            [self::PID]
        );
    }

    private function rowsLeft(): int
    {
        $count = 0;
        foreach (
            [
                'SELECT COUNT(*) AS n FROM `patient_data` WHERE `pid` = ?' => [self::PID],
                'SELECT COUNT(*) AS n FROM `insurance_data` WHERE `pid` = ?' => [self::PID],
                'SELECT COUNT(*) AS n FROM `insurance_companies` WHERE `name` = ? OR `cqm_sop` = ?' => [self::COMPANY_NAME, self::SOP_CODE],
            ] as $sql => $binds
        ) {
            $count += self::number(QueryUtils::fetchSingleValue($sql, 'n', $binds));
        }
        return $count;
    }

    private static function number(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
