<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * Isolated tests for how the box 17 qualifier routes the misc billing options
 * provider to the referring (loop 2310A) or ordering (loop 2420E) provider.
 *
 * DN or an empty qualifier routes the provider to the referring loop, DK to the
 * ordering loop, and DQ to neither. When the qualifier does not match, each
 * getter falls through to its own source.
 */
class ClaimProviderRoutingTest extends TestCase
{
    private const MBO_PROVIDER_ID = 530;
    private const ENCOUNTER_REFERRER_ID = 741;
    private const ENCOUNTER_ORDERER_ID = 852;
    private const DEMOGRAPHICS_REFERRER_ID = 963;

    /**
     * A Claim whose real constructor (which hits the database) is bypassed, with
     * only the properties the provider getters read seeded.
     *
     * Claim::$encounterService has no type declaration, so the fallthrough
     * branches are satisfied by a plain anonymous double. Doubling the real
     * EncounterService is not an option here: autoloading it pulls in BaseService,
     * which includes code_types.inc.php and calls sqlStatement() at file scope.
     */
    private function makeClaim(string $qualifier, int|string $mboProviderId = self::MBO_PROVIDER_ID): Claim
    {
        $stub = new class extends Claim {
            public function __construct()
            {
            }
        };

        $stub->billing_options = [
            'provider_id' => $mboProviderId,
            'provider_qualifier_code' => $qualifier,
        ];
        // provider_number_type is anything other than 1C so getReferrerId() takes
        // the demographics branch rather than the Medicare renderer branch.
        $stub->insurance_numbers = ['provider_number_type' => '1B'];
        $stub->patient_data = ['ref_providerID' => self::DEMOGRAPHICS_REFERRER_ID];
        $stub->encounter = ['provider_id' => 0];

        $encounterService = new class (self::ENCOUNTER_REFERRER_ID, self::ENCOUNTER_ORDERER_ID) {
            public function __construct(private readonly int $referrerId, private readonly int $ordererId)
            {
            }

            public function getReferringProviderID(mixed $pid, mixed $encounterId): int
            {
                return $this->referrerId;
            }

            public function getOrderingProviderID(mixed $pid, mixed $encounterId): int
            {
                return $this->ordererId;
            }
        };

        $property = new ReflectionProperty(Claim::class, 'encounterService');
        $property->setValue($stub, $encounterService);

        return $stub;
    }

    public function testBox17QualifierIsEmptyWhenNotSet(): void
    {
        $claim = $this->makeClaim('');
        $this->assertSame('', $claim->box17Qualifier());
    }

    public function testBox17QualifierReturnsStoredValue(): void
    {
        $claim = $this->makeClaim('DK');
        $this->assertSame('DK', $claim->box17Qualifier());
    }

    public function testEmptyQualifierRoutesProviderToReferring(): void
    {
        $claim = $this->makeClaim('');
        $this->assertSame(self::MBO_PROVIDER_ID, $claim->getReferrerId());
    }

    public function testDnRoutesProviderToReferring(): void
    {
        $claim = $this->makeClaim('DN');
        $this->assertSame(self::MBO_PROVIDER_ID, $claim->getReferrerId());
    }

    public function testDkDoesNotRouteProviderToReferring(): void
    {
        $claim = $this->makeClaim('DK');
        $this->assertSame(self::ENCOUNTER_REFERRER_ID, $claim->getReferrerId());
    }

    public function testDqDoesNotRouteProviderToReferring(): void
    {
        $claim = $this->makeClaim('DQ');
        $this->assertSame(self::ENCOUNTER_REFERRER_ID, $claim->getReferrerId());
    }

    public function testDkRoutesProviderToOrdering(): void
    {
        $claim = $this->makeClaim('DK');
        $this->assertSame(self::MBO_PROVIDER_ID, $claim->getOrdererId());
    }

    public function testEmptyQualifierDoesNotRouteProviderToOrdering(): void
    {
        $claim = $this->makeClaim('');
        $this->assertSame(self::ENCOUNTER_ORDERER_ID, $claim->getOrdererId());
    }

    public function testDnDoesNotRouteProviderToOrdering(): void
    {
        $claim = $this->makeClaim('DN');
        $this->assertSame(self::ENCOUNTER_ORDERER_ID, $claim->getOrdererId());
    }

    public function testDqDoesNotRouteProviderToOrdering(): void
    {
        $claim = $this->makeClaim('DQ');
        $this->assertSame(self::ENCOUNTER_ORDERER_ID, $claim->getOrdererId());
    }

    public function testUnsetProviderFallsThroughRegardlessOfQualifier(): void
    {
        $claim = $this->makeClaim('DN', 0);
        $this->assertSame(self::ENCOUNTER_REFERRER_ID, $claim->getReferrerId());

        $claim = $this->makeClaim('DK', 0);
        $this->assertSame(self::ENCOUNTER_ORDERER_ID, $claim->getOrdererId());
    }

    /**
     * Provider ids arrive as strings from the data layer and as ints from code
     * paths that cast, and both getters pass the value through untouched, so the
     * seeded type is the expected type.
     *
     * @return array<string, array{int|string}>
     */
    public static function mboProviderIdProvider(): array
    {
        return [
            'int provider id' => [self::MBO_PROVIDER_ID],
            'string provider id' => [(string) self::MBO_PROVIDER_ID],
        ];
    }

    #[DataProvider('mboProviderIdProvider')]
    public function testDnRoutesProviderToReferringPreservingType(int|string $providerId): void
    {
        $claim = $this->makeClaim('DN', $providerId);
        $this->assertSame($providerId, $claim->getReferrerId());
    }

    #[DataProvider('mboProviderIdProvider')]
    public function testDkRoutesProviderToOrderingPreservingType(int|string $providerId): void
    {
        $claim = $this->makeClaim('DK', $providerId);
        $this->assertSame($providerId, $claim->getOrdererId());
    }

    /**
     * An unset int column reads back as the string '0', which is falsy in PHP, so
     * it must fall through rather than being treated as a selected provider.
     */
    public function testStringZeroProviderIdFallsThrough(): void
    {
        $claim = $this->makeClaim('DN', '0');
        $this->assertSame(self::ENCOUNTER_REFERRER_ID, $claim->getReferrerId());

        $claim = $this->makeClaim('DK', '0');
        $this->assertSame(self::ENCOUNTER_ORDERER_ID, $claim->getOrdererId());
    }
}
