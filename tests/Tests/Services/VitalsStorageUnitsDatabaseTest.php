<?php

/**
 * VitalsStorageUnits::convertSql() gives, in the database, the value convert() gives in
 * PHP. The QRDA import stores a converted copy with convert(), and the eCQM vitals query
 * finds that copy again with convertSql(), so the two must agree at the precision
 * form_vitals keeps.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\VitalsStorageUnits;
use PHPUnit\Framework\TestCase;

class VitalsStorageUnitsDatabaseTest extends TestCase
{
    public function testTheDatabaseConvertsAsPhpDoes(): void
    {
        // [storage unit, unit sent, first value, last value, step], in tenths and hundredths as
        // documents send them, plus the units that stay as they are.
        $sweeps = [
            ['lb', 'kg', 0.5, 300.0, 0.1],
            ['lb', 'g', 400.0, 9000.0, 7.0],
            ['in', 'cm', 20.0, 250.0, 0.1],
            ['in', 'm', 0.3, 2.5, 0.01],
            ['degF', 'Cel', 30.0, 43.0, 0.01],
            ['lb', '[lb_av]', 1.0, 400.0, 3.3],
        ];
        $compared = 0;
        $converted = 0;
        foreach ($sweeps as [$storageUnit, $unit, $first, $last, $step]) {
            $values = [];
            for ($n = 0; $first + $n * $step <= $last + 1e-9; $n++) {
                $values[] = number_format($first + $n * $step, 2, '.', '');
            }
            $rows = $this->convertInDatabase($values, $unit, $storageUnit);
            foreach ($values as $value) {
                $expected = VitalsStorageUnits::convert((float)$value, $unit, $storageUnit);
                $this->assertNotNull($expected);
                $this->assertSame(number_format($expected, 6, '.', ''), $rows[$value], "$value $unit to $storageUnit");
                $compared++;
                if ($rows[$value] !== number_format((float)$value, 6, '.', '')) {
                    $converted++;
                }
            }
        }
        // A sweep that compared nothing, or a CASE that never converted, would pass the loop above.
        $this->assertGreaterThan(8000, $compared);
        $this->assertGreaterThan(7900, $converted);
    }

    /**
     * @param list<string> $values
     * @return array<string, string> each value => convertSql()'s result at form_vitals' precision
     */
    private function convertInDatabase(array $values, string $unit, string $storageUnit): array
    {
        $sql = VitalsStorageUnits::convertSql('p.`val`', 'p.`unit`', $storageUnit);
        $rows = [];
        foreach (array_chunk($values, 500) as $chunk) {
            $select = implode(' UNION ALL ', array_fill(0, count($chunk), 'SELECT ? AS `val`, ? AS `unit`'));
            $binds = [];
            foreach ($chunk as $value) {
                $binds[] = $value;
                $binds[] = $unit;
            }
            $result = QueryUtils::sqlStatementThrowException("SELECT p.`val`, CAST($sql AS DECIMAL(12,6)) AS `stored` FROM ($select) AS p", $binds);
            while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
                $this->assertIsString($row['val']);
                $this->assertIsString($row['stored']);
                $rows[$row['val']] = $row['stored'];
            }
        }
        $this->assertCount(count($values), $rows);
        return $rows;
    }
}
