<?php

/**
 * When a QRDA document's payer code (a Source of Payment Typology code) matches no insurance
 * company, the import makes a placeholder company for it. The placeholder holds what the
 * document says: the code, in cqm_sop. It has no address, which the document doesn't give,
 * and no insurance type, which is the claim type, a different code set.
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
use PHPUnit\Framework\TestCase;

class CdaImportPayerCompanyTest extends TestCase
{
    // A synthetic patient (no patient_data row is needed) and the Source of Payment Typology code
    // 9999 (Unavailable / Unknown), which no insurance company in a test database carries.
    // Every row made here is removed.
    private const PID = 990653;
    private const SOP_CODE = '9999';
    private const COMPANY_NAME = 'QRDA Insurance Company Payer ' . self::SOP_CODE;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patient or company before the test');
    }

    protected function tearDown(): void
    {
        $company = 'SELECT `id` FROM `insurance_companies` WHERE `cqm_sop` = ?';
        QueryUtils::sqlStatementThrowException("DELETE FROM `addresses` WHERE `foreign_id` IN ($company)", [self::SOP_CODE]);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_companies' AND `uuid` IN"
            . ' (SELECT `uuid` FROM `insurance_companies` WHERE `cqm_sop` = ?)',
            [self::SOP_CODE]
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_data' AND `uuid` IN (SELECT `uuid` FROM `insurance_data` WHERE `pid` = ?)",
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `insurance_data` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `insurance_companies` WHERE `cqm_sop` = ?', [self::SOP_CODE]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testThePlaceholderCompanyHoldsOnlyThePayerCode(): void
    {
        $payer = ['code' => self::SOP_CODE, 'status' => 'active', 'low_date' => '20240115', 'high_date' => null];
        (new CdaTemplateImportDispose())->InsertPayers([1 => $payer], self::PID, $this->createMock(CarecoordinationTable::class), 0);

        $this->assertSame(
            [['name' => self::COMPANY_NAME, 'cqm_sop' => self::SOP_CODE, 'ins_type_code' => null, 'addresses' => '0']],
            QueryUtils::fetchRecords(
                // INT columns read as text, so the values compare the same with or without bound values
                'SELECT `c`.`name`, CAST(`c`.`cqm_sop` AS CHAR) AS `cqm_sop`, CAST(`c`.`ins_type_code` AS CHAR) AS `ins_type_code`,'
                . ' CAST((SELECT COUNT(*) FROM `addresses` AS `a` WHERE `a`.`foreign_id` = `c`.`id`) AS CHAR) AS `addresses`'
                . ' FROM `insurance_companies` AS `c` WHERE `c`.`cqm_sop` = ?',
                [self::SOP_CODE]
            ),
            'one placeholder, named for the code and holding it in cqm_sop, with no address and no insurance type'
        );
    }

    private function rowsLeft(): int
    {
        $count = 0;
        foreach (
            [
                'SELECT COUNT(*) AS n FROM `insurance_data` WHERE `pid` = ?' => [self::PID],
                'SELECT COUNT(*) AS n FROM `insurance_companies` WHERE `name` = ? OR `cqm_sop` = ?' => [self::COMPANY_NAME, self::SOP_CODE],
            ] as $sql => $binds
        ) {
            $value = QueryUtils::fetchSingleValue($sql, 'n', $binds);
            $count += is_numeric($value) ? (int) $value : 0;
        }
        return $count;
    }
}
