<?php

/**
 * Database-backed tests for pricing a new fee sheet line from the prices table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use FeeSheet;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

require_once __DIR__ . '/../../../../library/FeeSheet.class.php';

class FeeSheetUnitPriceTest extends TestCase
{
    // Not a real HCPCS code, so the test owns its codes and prices rows.
    private const CODE = 'ZZ902';

    private int $codeId;

    protected function setUp(): void
    {
        $codeTypeId = QueryUtils::fetchSingleValue("SELECT ct_id FROM code_types WHERE ct_key = 'HCPCS'", 'ct_id');
        $this->codeId = (int) QueryUtils::sqlInsert(
            "INSERT INTO codes (code_text, code, code_type, modifier, units, active) VALUES (?, ?, ?, '', NULL, 1)",
            ['ZZTEST injection, 1 mg', self::CODE, $codeTypeId]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO prices (pr_id, pr_selector, pr_level, pr_price) VALUES (?, '', 'standard', 2.00)",
            [$this->codeId]
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM prices WHERE pr_id = ? AND pr_selector = ''", [$this->codeId]);
        QueryUtils::sqlStatementThrowException("DELETE FROM codes WHERE id = ?", [$this->codeId]);
    }

    /**
     * A FeeSheet without its constructor, which needs a patient, encounter and
     * session. addServiceLineItem() only needs the patient's price level here.
     */
    private function feeSheet(): FeeSheet
    {
        $fs = (new ReflectionClass(FeeSheet::class))->newInstanceWithoutConstructor();
        $fs->patient_pricelevel = 'standard';
        return $fs;
    }

    /**
     * @param array<string, mixed> $args
     * @return array<mixed>
     */
    private function addLine(array $args): array
    {
        $fs = $this->feeSheet();
        $fs->addServiceLineItem(['codetype' => 'HCPCS', 'code' => self::CODE, 'provider_id' => 1] + $args);
        $items = $fs->serviceitems;
        $this->assertIsArray($items);
        $line = end($items);
        $this->assertIsArray($line);
        return $line;
    }

    public function testNewLineFeeIsPriceTimesUnits(): void
    {
        $line = $this->addLine(['units' => 40]);

        $this->assertSame(40, $line['units']);
        $this->assertSame('80.00', $line['fee']);
        $this->assertEquals(2.0, $line['price']);
    }

    public function testNewLineWithoutUnitsUsesTheCodeDefault(): void
    {
        QueryUtils::sqlStatementThrowException("UPDATE codes SET units = 20 WHERE id = ?", [$this->codeId]);

        $line = $this->addLine([]);

        $this->assertSame(20, $line['units']);
        $this->assertSame('40.00', $line['fee']);
    }

    public function testNewLineWithNoDefaultIsOneUnitAtThePrice(): void
    {
        $line = $this->addLine([]);

        $this->assertSame(1, $line['units']);
        $this->assertSame('2.00', $line['fee']);
    }

    public function testSavedLineFeeIsNotMultipliedAgain(): void
    {
        // A saved line passes its stored line total.
        $line = $this->addLine(['units' => 40, 'fee' => '80.00', 'id' => 1]);

        $this->assertSame('80.00', $line['fee']);
        $this->assertEquals(2.0, $line['price']);
    }
}
