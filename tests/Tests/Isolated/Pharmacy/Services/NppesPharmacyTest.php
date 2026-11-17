<?php

/**
 * Isolated NppesPharmacy Test
 *
 * The fixtures follow the shape of real NPPES version 2.1 results: a result
 * has no 'name' key, its two addresses arrive in either order, and the
 * telephone and fax keys are missing when the registry holds no number.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Pharmacy\Services;

use OpenEMR\Pharmacy\Services\NppesPharmacy;
use PHPUnit\Framework\TestCase;

final class NppesPharmacyTest extends TestCase
{
    /**
     * A chain pharmacy whose mailing address is a head office in another
     * state, and which the registry lists mailing-first.
     *
     * @return array<array-key, mixed>
     */
    private static function mailingFirstResult(): array
    {
        return [
            'number' => '1234567890',
            'basic' => ['organization_name' => 'WALGREEN CO', 'status' => 'A'],
            'addresses' => [
                [
                    'address_purpose' => 'MAILING',
                    'address_1' => '1901 E VOORHEES ST',
                    'city' => 'DANVILLE',
                    'state' => 'IL',
                    'postal_code' => '618341234',
                    'telephone_number' => '217-709-2351',
                    'fax_number' => '217-709-2344',
                ],
                [
                    'address_purpose' => 'LOCATION',
                    'address_1' => '1024 NORTH AVE',
                    'city' => 'BURLINGTON',
                    'state' => 'VT',
                    'postal_code' => '054011234',
                    'telephone_number' => '802-865-7822',
                    'fax_number' => '802-865-2014',
                ],
            ],
            'identifiers' => [
                ['desc' => 'Other', 'identifier' => '0123456'],
            ],
        ];
    }

    public function testUsesThePracticeLocationNotTheMailingAddress(): void
    {
        $record = NppesPharmacy::fromResult(self::mailingFirstResult());
        self::assertNotNull($record);
        self::assertSame('1024 NORTH AVE', $record->addressLine1);
        self::assertSame('BURLINGTON', $record->city);
        self::assertSame('VT', $record->state);
        self::assertSame('802-865-7822', $record->phone);
        self::assertSame('802-865-2014', $record->fax);
    }

    public function testNameComesFromTheOrganisationName(): void
    {
        // There is no 'name' key in a version 2.1 result.
        $record = NppesPharmacy::fromResult(self::mailingFirstResult());
        self::assertNotNull($record);
        self::assertSame('WALGREEN CO', $record->name);
    }

    public function testNameFallsBackToAnIndividualsName(): void
    {
        $record = NppesPharmacy::fromResult([
            'number' => '1999999999',
            'basic' => ['first_name' => 'RYAN', 'last_name' => 'QUINN'],
            'addresses' => [['address_purpose' => 'LOCATION', 'postal_code' => '05401']],
        ]);
        self::assertNotNull($record);
        self::assertSame('QUINN, RYAN', $record->name);
    }

    public function testNineDigitPostalCodeIsStoredAsFiveDigits(): void
    {
        $record = NppesPharmacy::fromResult(self::mailingFirstResult());
        self::assertNotNull($record);
        self::assertSame('05401', $record->zip);
    }

    public function testFiveDigitPostalCodeIsKept(): void
    {
        $record = NppesPharmacy::fromResult([
            'number' => '1222222222',
            'basic' => ['organization_name' => 'SHAWS SUPERMARKETS INC'],
            'addresses' => [[
                'address_purpose' => 'LOCATION',
                'address_1' => '570 SHELBURNE RD',
                'city' => 'BURLINGTON',
                'state' => 'VT',
                'postal_code' => '05401',
            ]],
        ]);
        self::assertNotNull($record);
        self::assertSame('05401', $record->zip);
    }

    public function testMissingPhoneAndFaxBecomeNull(): void
    {
        $record = NppesPharmacy::fromResult([
            'number' => '1333333333',
            'basic' => ['organization_name' => 'FLETCHER ALLEN HEALTH CARE, INC'],
            'addresses' => [[
                'address_purpose' => 'LOCATION',
                'address_1' => '111 COLCHESTER AVE',
                'city' => 'BURLINGTON',
                'state' => 'VT',
                'postal_code' => '05401',
            ]],
        ]);
        self::assertNotNull($record);
        self::assertNull($record->phone);
        self::assertNull($record->fax);
    }

    public function testNcpdpComesFromTheOtherIdentifier(): void
    {
        $record = NppesPharmacy::fromResult(self::mailingFirstResult());
        self::assertNotNull($record);
        self::assertSame('0123456', $record->ncpdp);
    }

    public function testNcpdpIsNullWithoutAnOtherIdentifier(): void
    {
        $result = self::mailingFirstResult();
        $result['identifiers'] = [['desc' => 'MEDICAID', 'identifier' => '999']];
        $record = NppesPharmacy::fromResult($result);
        self::assertNotNull($record);
        self::assertNull($record->ncpdp);
    }

    public function testFallsBackToTheFirstAddressWhenNoneIsALocation(): void
    {
        $record = NppesPharmacy::fromResult([
            'number' => '1234567890',
            'basic' => ['organization_name' => 'WALGREEN CO'],
            'addresses' => [[
                'address_purpose' => 'MAILING',
                'address_1' => '1901 E VOORHEES ST',
                'city' => 'DANVILLE',
                'state' => 'IL',
                'postal_code' => '618341234',
            ]],
        ]);
        self::assertNotNull($record);
        self::assertSame('1901 E VOORHEES ST', $record->addressLine1);
    }

    public function testAResultWithoutAnNpiIsSkipped(): void
    {
        self::assertNull(NppesPharmacy::fromResult([]));
        self::assertNull(NppesPharmacy::fromResult(['number' => '']));
        self::assertNull(NppesPharmacy::fromResult(['number' => '   ']));
    }

    public function testAnNpiGivenAsAnIntegerIsAccepted(): void
    {
        $record = NppesPharmacy::fromResult(['number' => 1234567890, 'addresses' => []]);
        self::assertNotNull($record);
        self::assertSame('1234567890', $record->npi);
    }

    public function testAMalformedResultDoesNotFail(): void
    {
        $record = NppesPharmacy::fromResult([
            'number' => '1444444444',
            'basic' => 'not an array',
            'addresses' => 'not an array',
            'identifiers' => 'not an array',
        ]);
        self::assertNotNull($record);
        self::assertSame('', $record->name);
        self::assertSame('', $record->addressLine1);
        self::assertSame('', $record->zip);
        self::assertNull($record->phone);
        self::assertNull($record->ncpdp);
    }
}
