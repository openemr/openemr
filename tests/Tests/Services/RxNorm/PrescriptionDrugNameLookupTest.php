<?php

/**
 * A case-sensitive server still finds a drug when the RxNorm table is uppercase.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\RxNorm;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class PrescriptionDrugNameLookupTest extends TestCase
{
    private bool $created = false;

    protected function setUp(): void
    {
        $existing = QueryUtils::querySingleRow(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND LOWER(table_name) = 'rxnconso'"
        );
        if ($existing === false) {
            QueryUtils::sqlStatementThrowException(
                "CREATE TABLE `RXNCONSO` (
                    `RXCUI` varchar(8) NOT NULL,
                    `SAB` varchar(40) NOT NULL,
                    `str` varchar(1000) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            $this->created = true;
            QueryUtils::clearSchemaCache();
        }

        QueryUtils::sqlStatementThrowException(
            'INSERT INTO ' . escape_table_name('RXNCONSO') . ' (`RXCUI`, `SAB`, `str`) VALUES (?, ?, ?)',
            ['202433', 'RXNORM', 'Tylenol']
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM ' . escape_table_name('RXNCONSO') . ' WHERE `RXCUI` = ? AND `str` = ?',
            ['202433', 'Tylenol']
        );
        if ($this->created) {
            QueryUtils::sqlStatementThrowException('DROP TABLE ' . escape_table_name('RXNCONSO'));
            QueryUtils::clearSchemaCache();
        }
    }

    public function testAnUppercaseRxNormTableStillReturnsTheDrug(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../library/ajax/prescription_drugname_lookup.php');
        $this->assertIsString($source);
        $this->assertStringContainsString("escape_table_name('RXNCONSO')", $source);
        $this->assertStringNotContainsString('FROM `rxnconso`', $source);

        $sql = "SELECT `str` as name, `RXCUI` as `rxnorm` FROM "
            . escape_table_name('RXNCONSO')
            . " WHERE `SAB` = 'RXNORM' AND `str` LIKE ? GROUP BY `RXCUI` ORDER BY `str` LIMIT 100";
        $row = QueryUtils::querySingleRow($sql, ['Tyl%']);
        $this->assertIsArray($row);
        $name = $row['name'] ?? null;
        $rxnorm = $row['rxnorm'] ?? null;
        $this->assertSame('Tylenol', $name);
        $this->assertSame('202433', $rxnorm);
    }
}
