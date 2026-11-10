<?php

/**
 * Isolated tests for the 837P pay-to address (loop 2010AB): sent from the
 * billing facility's mailing address only when it differs from the physical
 * address used for the billing provider (loop 2010AA).
 *
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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[Group('isolated')]
class ClaimPayToAddressTest extends TestCase
{
    private const PHYSICAL = [
        'street' => '1421 Oread West St Ste A',
        'city' => 'Lawrence',
        'state' => 'KS',
        'postal_code' => '66049',
    ];

    /**
     * @param array<string, string> $mailing
     */
    #[DataProvider('mailingProvider')]
    public function testHasPayToAddressOnlyWhenMailingDiffers(array $mailing, bool $expected): void
    {
        $this->assertSame($expected, $this->claimFor($mailing + self::PHYSICAL)->hasPayToAddress());
    }

    /**
     * @return array<string, array{array<string, string>, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function mailingProvider(): array
    {
        $same = [
            'mail_street' => '1421 Oread West St Ste A',
            'mail_street2' => '',
            'mail_city' => 'Lawrence',
            'mail_state' => 'KS',
            'mail_zip' => '66049',
        ];
        return [
            'no mailing street' => [['mail_street' => ''] + $same, false],
            'same address' => [$same, false],
            'same apart from case and punctuation' => [['mail_street' => '1421 OREAD WEST ST., STE. A', 'mail_city' => 'LAWRENCE'] + $same, false],
            'suite in street 2' => [['mail_street' => '1421 Oread West St', 'mail_street2' => 'Ste A'] + $same, false],
            'mailing ZIP+4 of the physical ZIP' => [['mail_zip' => '66049-3844'] + $same, false],
            'PO box' => [['mail_street' => 'PO Box 442'] + $same, true],
            'other suite' => [['mail_street2' => 'Ste B', 'mail_street' => '1421 Oread West St'] + $same, true],
            'other city' => [['mail_city' => 'Topeka'] + $same, true],
            'other ZIP' => [['mail_zip' => '66044'] + $same, true],
            'other ZIP+4 extension' => [['mail_zip' => '66049-3844'] + ['postal_code' => '660491111'] + $same, true],
        ];
    }

    public function testNoPayToAddressWithoutABillingFacility(): void
    {
        $claim = (new ReflectionClass(Claim::class))->newInstanceWithoutConstructor();
        $claim->billing_facility = false;
        $this->assertFalse($claim->hasPayToAddress());
        $this->assertSame('', $claim->payToFacilityStreet());
    }

    public function testPayToFieldsAreTheCleanedMailingAddress(): void
    {
        $claim = $this->claimFor(self::PHYSICAL + [
            'mail_street' => ' P.O. Box 442 ',
            'mail_street2' => 'Attn: Billing',
            'mail_city' => 'Lawrence',
            'mail_state' => 'ks',
            'mail_zip' => '66044-0442',
        ]);
        $this->assertTrue($claim->hasPayToAddress());
        $this->assertSame(
            ['P.O. BOX 442', 'ATTN BILLING', 'LAWRENCE', 'KS', '660440442'],
            [
                $claim->payToFacilityStreet(),
                $claim->payToFacilityStreet2(),
                $claim->payToFacilityCity(),
                $claim->payToFacilityState(),
                $claim->payToFacilityZip(),
            ]
        );
    }

    /**
     * A Claim with only its billing facility set; the constructor needs a database.
     *
     * @param array<string, string> $billingFacility
     */
    private function claimFor(array $billingFacility): Claim
    {
        $claim = (new ReflectionClass(Claim::class))->newInstanceWithoutConstructor();
        $claim->billing_facility = $billingFacility;
        return $claim;
    }
}
