<?php

/**
 * Database-backed tests for finding the inventory drug related to a HCPCS code.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\HcpcsDrugDefaults;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class HcpcsDrugDefaultsServiceTest extends TestCase
{
    // Not a real HCPCS code, so no existing drug can be related to it.
    private const CODE = 'ZZ901';

    /** @var list<int> */
    private array $drugIds = [];

    protected function tearDown(): void
    {
        foreach ($this->drugIds as $drugId) {
            QueryUtils::sqlStatementThrowException("DELETE FROM drug_inventory WHERE drug_id = ?", [$drugId]);
            QueryUtils::sqlStatementThrowException("DELETE FROM drugs WHERE drug_id = ?", [$drugId]);
        }
        $this->drugIds = [];
    }

    private function addDrug(string $name, string $relatedCode, string $ndc, ?int $billingUnits, int $active = 1): int
    {
        $drugId = (int) QueryUtils::sqlInsert(
            "INSERT INTO drugs (name, ndc_number, related_code, billing_units, ndc_uom, ndc_quantity, active) " .
            "VALUES (?, ?, ?, ?, 'ML', 1, ?)",
            [$name, $ndc, $relatedCode, $billingUnits, $active]
        );
        $this->drugIds[] = $drugId;
        return $drugId;
    }

    private function addStock(int $drugId, int $onHand, ?string $destroyDate = null): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO drug_inventory (drug_id, lot_number, on_hand, destroy_date) VALUES (?, 'TESTLOT', ?, ?)",
            [$drugId, $onHand, $destroyDate]
        );
    }

    public function testNoRelatedDrugReturnsNull(): void
    {
        $this->assertNull(HcpcsDrugDefaults::forCode(self::CODE));
    }

    public function testCodeIsFoundAmongSeveralRelatedCodes(): void
    {
        $this->addDrug('ZZTEST methylprednisolone 40 mg/mL', 'CPT4:96372;HCPCS:' . self::CODE, '0009-3073-01', 40);

        $defaults = HcpcsDrugDefaults::forCode(self::CODE);

        $this->assertNotNull($defaults);
        $this->assertSame('N40009-3073-01   ML1', $defaults->ndcInfo);
        $this->assertSame(40, $defaults->units);
    }

    public function testAnotherCodeStartingWithTheSameCharactersDoesNotMatch(): void
    {
        $this->addDrug('ZZTEST other drug', 'HCPCS:' . self::CODE . '0', '0009-0000-01', 10);

        $this->assertNull(HcpcsDrugDefaults::forCode(self::CODE));
    }

    public function testInactiveDrugIsIgnored(): void
    {
        $this->addDrug('ZZTEST inactive', 'HCPCS:' . self::CODE, '0009-3073-01', 40, active: 0);

        $this->assertNull(HcpcsDrugDefaults::forCode(self::CODE));
    }

    public function testDrugWithStockOnHandWinsOverFirstByName(): void
    {
        $this->addDrug('ZZTEST Aaa 80 mg/mL', 'HCPCS:' . self::CODE, '0009-3475-03', 80);
        $inStock = $this->addDrug('ZZTEST Bbb 40 mg/mL', 'HCPCS:' . self::CODE, '0009-3073-01', 40);
        $this->addStock($inStock, 10);

        $defaults = HcpcsDrugDefaults::forCode(self::CODE);

        $this->assertNotNull($defaults);
        $this->assertSame(40, $defaults->units);
    }

    public function testDestroyedLotDoesNotCountAsStock(): void
    {
        $this->addDrug('ZZTEST Aaa 80 mg/mL', 'HCPCS:' . self::CODE, '0009-3475-03', 80);
        $destroyed = $this->addDrug('ZZTEST Bbb 40 mg/mL', 'HCPCS:' . self::CODE, '0009-3073-01', 40);
        $this->addStock($destroyed, 10, '2026-01-15');

        $defaults = HcpcsDrugDefaults::forCode(self::CODE);

        $this->assertNotNull($defaults);
        $this->assertSame(80, $defaults->units, 'With no stock anywhere, the first drug by name wins.');
    }

    public function testNamedDrugIsUsedInsteadOfTheDefault(): void
    {
        // Two products share the code; the default would be the one in stock.
        $inStock = $this->addDrug('ZZTEST Aaa 40 mg/mL', 'HCPCS:' . self::CODE, '0009-3073-01', 40);
        $this->addStock($inStock, 10);
        $named = $this->addDrug('ZZTEST Bbb 80 mg/mL', 'HCPCS:' . self::CODE, '0009-3475-03', 80);

        $defaults = HcpcsDrugDefaults::forDrug($named, self::CODE);

        $this->assertNotNull($defaults);
        $this->assertSame('N40009-3475-03   ML1', $defaults->ndcInfo);
        $this->assertSame(80, $defaults->units);
    }

    public function testNamedDrugNotRelatedToTheCodeIsIgnored(): void
    {
        $other = $this->addDrug('ZZTEST other code', 'HCPCS:' . self::CODE . '0', '0009-0000-01', 10);

        $this->assertNull(HcpcsDrugDefaults::forDrug($other, self::CODE));
    }

    public function testNamedInactiveDrugIsIgnored(): void
    {
        $inactive = $this->addDrug('ZZTEST inactive', 'HCPCS:' . self::CODE, '0009-3073-01', 40, active: 0);

        $this->assertNull(HcpcsDrugDefaults::forDrug($inactive, self::CODE));
    }

    public function testChoicesListActiveRelatedDrugsByName(): void
    {
        $second = $this->addDrug('ZZTEST Bbb 80 mg/mL', 'HCPCS:' . self::CODE, '0009-3475-03', 80);
        $first = $this->addDrug('ZZTEST Aaa 40 mg/mL', 'CPT4:96372;HCPCS:' . self::CODE, '0009-3073-01', 40);
        $this->addDrug('ZZTEST inactive', 'HCPCS:' . self::CODE, '0009-0000-01', 10, active: 0);
        $this->addDrug('ZZTEST other code', 'HCPCS:' . self::CODE . '0', '0009-0000-02', 10);
        // A token with a stray space isn't matched by forDrug(), so it isn't offered either.
        $spaced = $this->addDrug('ZZTEST Ccc spaced', 'CPT4:96372; HCPCS:' . self::CODE, '0009-0000-03', 10);

        $choices = HcpcsDrugDefaults::choicesByCode();

        $this->assertSame([
            ['id' => $first, 'label' => 'ZZTEST Aaa 40 mg/mL (0009-3073-01)'],
            ['id' => $second, 'label' => 'ZZTEST Bbb 80 mg/mL (0009-3475-03)'],
        ], $choices[self::CODE] ?? null);
        $this->assertNull(HcpcsDrugDefaults::forDrug($spaced, self::CODE));
    }

}
