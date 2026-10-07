<?php

/**
 * Database-backed tests for the fee sheet review's procedure choices, which
 * come from the custom fee sheet lists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../interface/forms/fee_sheet/review/fee_sheet_options_queries.php';

class FeeSheetReviewOptionsTest extends TestCase
{
    // Not a real HCPCS code, and a category no real list uses.
    private const CODE = 'ZZ902';
    private const CATEGORY = 'ZZTEST review choices';

    private int $codeId = 0;

    protected function setUp(): void
    {
        $this->codeId = (int) QueryUtils::sqlInsert(
            "INSERT INTO codes (code_text, code, code_type, modifier, active) " .
            "VALUES ('ZZTEST injection', ?, (SELECT ct_id FROM code_types WHERE ct_key = 'HCPCS'), '', 1)",
            [self::CODE]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO prices (pr_id, pr_selector, pr_level, pr_price) VALUES (?, '', 'standard', 12.50)",
            [$this->codeId]
        );
        foreach (
            [
                'plain' => 'HCPCS|' . self::CODE . '|',
                'with modifier' => 'HCPCS|' . self::CODE . ':JW|',
                'with drug ID' => 'HCPCS|' . self::CODE . '|123',
                'several codes' => 'HCPCS|' . self::CODE . '|~CPT4|99213|',
            ] as $option => $codes
        ) {
            QueryUtils::sqlInsert(
                "INSERT INTO fee_sheet_options (fs_category, fs_option, fs_codes) VALUES (?, ?, ?)",
                [self::CATEGORY, $option, $codes]
            );
        }
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM fee_sheet_options WHERE fs_category = ?", [self::CATEGORY]);
        QueryUtils::sqlStatementThrowException("DELETE FROM prices WHERE pr_id = ?", [$this->codeId]);
        QueryUtils::sqlStatementThrowException("DELETE FROM codes WHERE id = ?", [$this->codeId]);
    }

    public function testEveryEntryShapeOffersItsFirstCodeWithItsPrice(): void
    {
        $choices = array_values(array_filter(
            load_fee_sheet_options('standard'),
            static fn(\fee_sheet_option $option): bool => $option->category === self::CATEGORY
        ));

        $this->assertCount(4, $choices);
        foreach ($choices as $choice) {
            $this->assertSame(self::CODE, $choice->code);
            $this->assertSame('HCPCS', $choice->code_type);
            $this->assertSame('ZZTEST injection', $choice->description);
            $this->assertEquals(12.50, $choice->price);
        }
    }
}
