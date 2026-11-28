<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\TestCase;

class ClaimNpiNotSentLogTest extends TestCase
{
    /**
     * @dataProvider npiNotSentProvider
     */
    public function testNpiNotSentLog(?string $rawNpi, string $expected): void
    {
        $this->assertSame($expected, Claim::npiNotSentLog('Test provider', $rawNpi));
    }

    public static function npiNotSentProvider(): array
    {
        return [
            'null NPI' => [null, "*** Test provider has no NPI.\n"],
            'empty string' => ['', "*** Test provider has no NPI.\n"],
            'whitespace only' => ['   ', "*** Test provider has no NPI.\n"],
            'valid NPI' => ['1234567890', "*** Test provider NPI 1234567890 is not valid.\n"],
            'invalid NPI with letters' => ['INVALID123', "*** Test provider NPI INVALID123 is not valid.\n"],
            'too short NPI' => ['12345', "*** Test provider NPI 12345 is not valid.\n"],
            'too long NPI' => ['12345678901', "*** Test provider NPI 12345678901 is not valid.\n"],
            'NPI with spaces' => ['123 456 7890', "*** Test provider NPI 123 456 7890 is not valid.\n"],
        ];
    }

    public function testProviderRawNPIWithValidProckey(): void
    {
        $claim = new Claim();
        $result = $claim->providerRawNPI(-1);
        $this->assertIsString($result);
    }

    public function testProviderRawNPIWithProckey(): void
    {
        $claim = new Claim();
        $result = $claim->providerRawNPI(0);
        $this->assertIsString($result);
    }

    public function testProviderRawNPIWithNegativeProckey(): void
    {
        $claim = new Claim();
        $result = $claim->providerRawNPI(-2);
        $this->assertIsString($result);
    }

    public function testNpiNotSentLogWithDifferentProviderTypes(): void
    {
        $types = ['Rendering provider', 'Referring provider', 'Operating physician', 'Facility'];
        foreach ($types as $type) {
            $result = Claim::npiNotSentLog($type, '1234567890');
            $this->assertStringContainsString($type, $result);
            $this->assertStringContainsString('1234567890', $result);
        }
    }

    public function testNpiNotSentLogEmptyWithDifferentProviderTypes(): void
    {
        $types = ['Rendering provider', 'Referring provider', 'Operating physician', 'Facility'];
        foreach ($types as $type) {
            $result = Claim::npiNotSentLog($type, null);
            $this->assertStringContainsString($type, $result);
            $this->assertStringContainsString('has no NPI', $result);
        }
    }
}