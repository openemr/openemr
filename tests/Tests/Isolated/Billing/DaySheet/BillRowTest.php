<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\DaySheet;

use OpenEMR\Billing\DaySheet\BillRow;
use PHPUnit\Framework\TestCase;

final class BillRowTest extends TestCase
{
    public function testNumericStringFieldsAreCoercedToString(): void
    {
        $row = BillRow::fromArray([
            'user' => 42,
            'provider_id' => 7.5,
            'code_type' => 'CPT4',
            'paytype' => 'cash',
        ]);

        $this->assertSame('42', $row->user);
        $this->assertSame('7.5', $row->providerId);
    }

    public function testNonScalarStringFieldsFallBackToEmptyString(): void
    {
        $row = BillRow::fromArray([
            'user' => ['unexpected'],
            'provider_id' => null,
        ]);

        $this->assertSame('', $row->user);
        $this->assertSame('', $row->providerId);
    }

    public function testNonNumericFloatFieldsFallBackToZero(): void
    {
        $row = BillRow::fromArray([
            'fee' => 'not-a-number',
            'ins_code' => null,
        ]);

        $this->assertSame(0.0, $row->fee);
        $this->assertSame(0.0, $row->insCode);
    }
}
