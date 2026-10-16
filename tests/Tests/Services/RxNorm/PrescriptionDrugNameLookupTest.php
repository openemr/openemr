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
    private const RXCUI = 'ZZ99401';

    private const SAB = 'RXNORM';

    private const NAME = 'ZZ RxNorm Lookup';

    private bool $created = false;

    /**
     * The fixture uses the uppercase table name, and only that test row.
     */
    protected function setUp(): void
    {
        if (!$this->hasUppercaseTable()) {
            QueryUtils::sqlStatementThrowException(
                "CREATE TABLE `RXNCONSO` (
                    `RXCUI` varchar(8) NOT NULL,
                    `SAB` varchar(40) NOT NULL,
                    `str` varchar(1000) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            QueryUtils::clearSchemaCache();
            if (!$this->hasUppercaseTable()) {
                $this->markTestSkipped('This server cannot keep an uppercase RXNCONSO table.');
            }
            $this->created = true;
        }

        QueryUtils::sqlStatementThrowException(
            'INSERT INTO ' . escape_table_name('RXNCONSO') . ' (`RXCUI`, `SAB`, `str`) VALUES (?, ?, ?)',
            [self::RXCUI, self::SAB, self::NAME]
        );
    }

    /**
     * Remove the inserted row, and the table only when this test created it.
     */
    protected function tearDown(): void
    {
        if ($this->hasUppercaseTable()) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM ' . escape_table_name('RXNCONSO')
                . ' WHERE `RXCUI` = ? AND `SAB` = ? AND `str` = ?',
                [self::RXCUI, self::SAB, self::NAME]
            );
        }
        if ($this->created) {
            QueryUtils::sqlStatementThrowException('DROP TABLE `RXNCONSO`');
            QueryUtils::clearSchemaCache();
        }
    }

    /**
     * A drug is found when the installed table name is RXNCONSO.
     */
    public function testAnUppercaseRxNormTableStillReturnsTheDrug(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../library/ajax/prescription_drugname_lookup.php');
        $this->assertIsString($source);
        $this->assertStringContainsString("escape_table_name('RXNCONSO')", $source);
        $this->assertStringNotContainsString('FROM `rxnconso`', $source);

        $sql = "SELECT `str` as name, `RXCUI` as `rxnorm` FROM "
            . escape_table_name('RXNCONSO')
            . " WHERE `SAB` = 'RXNORM' AND `str` LIKE ? GROUP BY `RXCUI` ORDER BY `str` LIMIT 100";
        $row = QueryUtils::querySingleRow($sql, ['ZZ Rx%']);
        $this->assertIsArray($row);
        $name = $row['name'] ?? null;
        $rxnorm = $row['rxnorm'] ?? null;
        $this->assertSame(self::NAME, $name);
        $this->assertSame(self::RXCUI, $rxnorm);
    }

    /**
     * True when information_schema stores the table name as RXNCONSO.
     */
    private function hasUppercaseTable(): bool
    {
        $existing = QueryUtils::querySingleRow(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'RXNCONSO'"
        );
        if (!is_array($existing)) {
            return false;
        }

        return ($existing['table_name'] ?? '') === 'RXNCONSO';
    }
}
