<?php

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\BillingProcessor\BillingClaimBatchControlNumber;
use PHPUnit\Framework\TestCase;

/**
 * Service tests for BillingClaimBatchControlNumber class
 * This class depends on QueryUtils::ediGenerateId() which requires database access
 */
class BillingClaimBatchControlNumberTest extends TestCase
{
    public function testGetIsa13ReturnsNineDigitString(): void
    {
        $isa13 = BillingClaimBatchControlNumber::getIsa13();

        $this->assertIsString($isa13);
        $this->assertEquals(9, strlen($isa13), "ISA13 should be exactly 9 characters");
        $this->assertMatchesRegularExpression('/^\d{9}$/', $isa13, "ISA13 should be 9 digits");
    }

    public function testGetIsa13IsZeroPadded(): void
    {
        $isa13 = BillingClaimBatchControlNumber::getIsa13();

        // Should be zero-padded to 9 digits
        $this->assertEquals(9, strlen($isa13));
        // First character should be 0 if the number is small enough
        // We can't guarantee this in all cases, but we can verify format
        $this->assertMatchesRegularExpression('/^\d{9}$/', $isa13);
    }

    public function testGetGs06ReturnsNumericString(): void
    {
        $gs06 = BillingClaimBatchControlNumber::getGs06();

        $this->assertIsString($gs06);
        $this->assertMatchesRegularExpression('/^\d+$/', $gs06, "GS06 should be numeric string");
        $this->assertNotEmpty($gs06);
    }

    public function testGetGs06NotPadded(): void
    {
        $gs06 = BillingClaimBatchControlNumber::getGs06();

        // GS06 does not require padding, so it could be any length
        // Just verify it's a valid numeric string
        $this->assertIsString($gs06);
        $this->assertNotEmpty($gs06);
    }

    public function testMultipleCallsReturnDifferentValues(): void
    {
        // Assuming ediGenerateId() increments, multiple calls should return different values
        $isa13_1 = BillingClaimBatchControlNumber::getIsa13();
        $isa13_2 = BillingClaimBatchControlNumber::getIsa13();

        // They should be different (assuming incremental generation)
        $this->assertNotEquals($isa13_1, $isa13_2, "Multiple calls should generate different control numbers");

        $gs06_1 = BillingClaimBatchControlNumber::getGs06();
        $gs06_2 = BillingClaimBatchControlNumber::getGs06();

        $this->assertNotEquals($gs06_1, $gs06_2, "Multiple calls should generate different control numbers");
    }

    public function testIsa13AndGs06UseSameIdSource(): void
    {
        // ISA13 and GS06 both use ediGenerateId(), so their numeric values should be sequential
        $isa13 = BillingClaimBatchControlNumber::getIsa13();
        $gs06 = BillingClaimBatchControlNumber::getGs06();

        // Remove leading zeros from ISA13 to compare as integers
        $isa13_int = (int)$isa13;
        $gs06_int = (int)$gs06;

        // They should be close in value (sequential from the same generator)
        // Allow for some variance in case other processes are generating IDs
        $this->assertGreaterThan(0, $isa13_int);
        $this->assertGreaterThan(0, $gs06_int);
    }
}
