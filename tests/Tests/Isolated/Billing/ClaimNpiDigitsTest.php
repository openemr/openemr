<?php

/**
 * Isolated tests for NPI digits sent in NM109.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ClaimNpiDigitsTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function digitProvider(): array
    {
        return [
            'digits' => ['1234567893', '1234567893'],
            'hyphen' => ['1234-567893', '1234567893'],
            'spaces' => ['123 456 7893', '1234567893'],
            'hyphen and space' => ['1234-567 893', '1234567893'],
            'edge spaces' => ['  1234567893  ', '1234567893'],
            'edge tab and newline' => ["\t1234567893\n", '1234567893'],
            'letter' => ['123456789A', ''],
            'punctuation' => ['1234567893.', ''],
            'empty' => ['', ''],
            'whitespace only' => [" \n\t", ''],
        ];
    }

    /**
     * Edge whitespace is trimmed, separators are removed, and any other character blanks the NPI.
     */
    #[DataProvider('digitProvider')]
    public function testX12NpiDigits(string $npi, string $digits): void
    {
        $this->assertSame($digits, $this->claim()->x12NpiDigits($npi));
    }

    /**
     * Each NM109 accessor sends the cleaned digits.
     */
    public function testAccessorsSendDigits(): void
    {
        $claim = $this->claim();
        $claim->billing_facility = ['facility_npi' => '1234-567893'];
        $claim->facility = ['facility_npi' => '123 456 7893'];
        $claim->referrer = ['npi' => "\t1234567893\n"];
        $claim->supervisor = ['npi' => '123456789A'];
        $claim->billing_prov_id = ['npi' => '1234-567 893'];
        $claim->orderer = ['npi' => ' 1234567893 '];
        $claim->provider = ['npi' => '1098765432'];
        $claim->procs = [
            0 => [
                'provider_id' => 4,
                'provider' => ['npi' => '1234-567893'],
            ],
        ];

        $this->assertSame('1234567893', $claim->billingFacilityNPI());
        $this->assertSame('1234567893', $claim->facilityNPI());
        $this->assertSame('1234567893', $claim->referrerNPI());
        $this->assertSame('', $claim->supervisorNPI());
        $this->assertSame('1234567893', $claim->billingProviderNPI());
        $this->assertSame('1234567893', $claim->ordererNPI());
        $this->assertSame('1098765432', $claim->providerNPI());
        $this->assertSame('1234567893', $claim->providerNPI(0));
    }

    private function claim(): Claim
    {
        return (new ReflectionClass(Claim::class))->newInstanceWithoutConstructor();
    }
}
