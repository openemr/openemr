<?php

/**
 * Immunizations read from the database as the eCQMs read them (ImmunizationAdministeredService):
 * a dose marked Entered in Error on the immunization screen (added_erroneously = 1) is left out.
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
use OpenEMR\Services\Qdm\Services\ImmunizationAdministeredService;
use PHPUnit\Framework\TestCase;

class ImmunizationAdministeredServiceDatabaseTest extends TestCase
{
    // A synthetic patient; no patient_data row is needed, and every row made here is removed.
    private const PID = 990616;
    private const OTHER_PID = 990617;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patients before the test');
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `immunizations` WHERE `patient_id` IN (?, ?)', [self::PID, self::OTHER_PID]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testADoseEnteredInErrorIsNotRead(): void
    {
        $this->dose(self::PID, '08', 0);
        $this->dose(self::PID, '10', 1);
        $this->dose(self::PID, '20', null);
        // Control: another patient's dose is not this patient's.
        $this->dose(self::OTHER_PID, '03', 0);

        $result = (new ImmunizationAdministeredService(new QdmRequestOne((string)self::PID), new CodeTypesService()))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $codes = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $code = $row['cvx_code'] ?? null;
            $this->assertIsString($code);
            $codes[] = $code;
        }
        sort($codes);

        $this->assertSame(['08', '20'], $codes, 'the dose entered in error left out; a dose saved without the flag set (its default 0) kept');
    }

    /** @param int|null $erroneous null leaves the column to its default */
    private function dose(int $pid, string $cvx, ?int $erroneous): void
    {
        if ($erroneous === null) {
            QueryUtils::sqlInsert(
                "INSERT INTO `immunizations` (`patient_id`, `administered_date`, `cvx_code`, `update_date`) VALUES (?, '2026-02-01 10:00:00', ?, NOW())",
                [$pid, $cvx],
            );
            return;
        }
        QueryUtils::sqlInsert(
            "INSERT INTO `immunizations` (`patient_id`, `administered_date`, `cvx_code`, `update_date`, `added_erroneously`) VALUES (?, '2026-02-01 10:00:00', ?, NOW(), ?)",
            [$pid, $cvx, $erroneous],
        );
    }

    private function rowsLeft(): int
    {
        $n = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM `immunizations` WHERE `patient_id` IN (?, ?)', 'n', [self::PID, self::OTHER_PID]);
        $this->assertIsNumeric($n);
        return (int)$n;
    }
}
