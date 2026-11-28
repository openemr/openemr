<?php

/**
 * Isolated tests for Claim::hasPayerAddress(): the 837P payer N3/N4 segments (loops 2010BB and 2330B) are only
 * written when the payer has a street, a city, a state and a 5 or 9 digit ZIP, because a segment without data
 * elements is invalid X12.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[Group('isolated')]
class ClaimPayerAddressTest extends TestCase
{
    /**
     * @param array{street: string, city: string, state: string, zip: string} $address
     */
    #[DataProvider('addressProvider')]
    public function testHasPayerAddress(array $address, bool $expected): void
    {
        $this->assertSame($expected, $this->claimWithPayer($address)->hasPayerAddress());
    }

    /**
     * @return array<string, array{array{street: string, city: string, state: string, zip: string}, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function addressProvider(): array
    {
        $full = ['street' => '1 Main St', 'city' => 'Albany', 'state' => 'NY', 'zip' => '12207'];
        return [
            'complete, 5 digit zip' => [$full, true],
            'complete, 9 digit zip' => [['zip' => '122071234'] + $full, true],
            'blank entry' => [['street' => '', 'city' => '', 'state' => '', 'zip' => ''], false],
            'street missing' => [['street' => ''] + $full, false],
            'city missing' => [['city' => ''] + $full, false],
            'state missing' => [['state' => ''] + $full, false],
            'zip missing' => [['zip' => ''] + $full, false],
            'zip too short' => [['zip' => '1220'] + $full, false],
            'only blanks' => [['street' => '  ', 'city' => '  ', 'state' => ' ', 'zip' => ' '] + $full, false],
        ];
    }

    public function testClaimWithoutThePayerLevelHasNoAddress(): void
    {
        $claim = (new ReflectionClass(Claim::class))->newInstanceWithoutConstructor();
        $claim->payers = [];
        $this->assertFalse($claim->hasPayerAddress());
        $this->assertFalse($claim->hasPayerAddress(1));
    }

    /**
     * @param array{street: string, city: string, state: string, zip: string} $address
     */
    private function claimWithPayer(array $address): Claim
    {
        $stub = new class ($address) {
            /** @param array{street: string, city: string, state: string, zip: string} $address */
            public function __construct(private readonly array $address)
            {
            }

            public function get_address(): object
            {
                return new class ($this->address) {
                    /** @param array{street: string, city: string, state: string, zip: string} $a */
                    public function __construct(private readonly array $a)
                    {
                    }

                    public function get_line1(): string
                    {
                        return $this->a['street'];
                    }

                    public function get_city(): string
                    {
                        return $this->a['city'];
                    }

                    public function get_state(): string
                    {
                        return $this->a['state'];
                    }

                    public function get_zip(): string
                    {
                        return $this->a['zip'];
                    }
                };
            }
        };
        $claim = (new ReflectionClass(Claim::class))->newInstanceWithoutConstructor();
        $claim->payers = [0 => ['object' => $stub]];
        return $claim;
    }
}
