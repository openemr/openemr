<?php

/**
 * Isolated tests for AddressBookReferrerFields.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\AddressBookReferrerFields;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class AddressBookReferrerFieldsIsolatedTest extends TestCase
{
    /**
     * Local login users and company types skip the referring-provider save guard.
     */
    public function testLocalUsersAndCompaniesSkipTheGuard(): void
    {
        $this->assertFalse(AddressBookReferrerFields::isExternalPerson('admin', '1'));
        $this->assertFalse(AddressBookReferrerFields::isExternalPerson('', '3'));
        $this->assertTrue(AddressBookReferrerFields::isExternalPerson('', '1'));
        $this->assertTrue(AddressBookReferrerFields::isExternalPerson('', ''));
        $this->assertTrue(AddressBookReferrerFields::isExternalPerson(null, '2'));
    }

    /**
     * NPI checks use ValidationUtils::isValidNPI (Luhn), not a 10-digit shape.
     */
    public function testNpiUsesValidationUtils(): void
    {
        $this->assertTrue(AddressBookReferrerFields::npiIsValid('1234567893'));
        $this->assertTrue(AddressBookReferrerFields::npiIsValid('1245319599'));
        $this->assertTrue(AddressBookReferrerFields::npiIsValid(' 1003000126'));
        $this->assertFalse(AddressBookReferrerFields::npiIsValid('1234567890'));
        $this->assertFalse(AddressBookReferrerFields::npiIsValid('123456789'));
        $this->assertFalse(AddressBookReferrerFields::npiIsValid('12345678901'));
        $this->assertFalse(AddressBookReferrerFields::npiIsValid('123456789a'));
        $this->assertFalse(AddressBookReferrerFields::npiIsValid(''));
        $this->assertFalse(AddressBookReferrerFields::npiIsValid(null));
    }

    /**
     * Street, city, state, and postal code are all required.
     */
    public function testMailingAddressNeedsEveryLine(): void
    {
        $this->assertTrue(
            AddressBookReferrerFields::mailingAddressComplete('1 Main', 'Fargo', 'ND', '58103')
        );
        $this->assertFalse(
            AddressBookReferrerFields::mailingAddressComplete('', 'Fargo', 'ND', '58103')
        );
        $this->assertFalse(
            AddressBookReferrerFields::mailingAddressComplete('1 Main', '', 'ND', '58103')
        );
        $this->assertFalse(
            AddressBookReferrerFields::mailingAddressComplete('1 Main', 'Fargo', '', '58103')
        );
        $this->assertFalse(
            AddressBookReferrerFields::mailingAddressComplete('1 Main', 'Fargo', 'ND', '')
        );
    }

    /**
     * A save needs both a valid NPI and a complete mailing address.
     */
    public function testSaveAllowedRequiresNpiAndAddress(): void
    {
        $this->assertTrue(AddressBookReferrerFields::saveAllowed(
            '1234567893',
            '1 Main',
            'Fargo',
            'ND',
            '58103'
        ));
        $this->assertFalse(AddressBookReferrerFields::saveAllowed(
            '1234567890',
            '1 Main',
            'Fargo',
            'ND',
            '58103'
        ));
        $this->assertFalse(AddressBookReferrerFields::saveAllowed(
            '1234567893',
            '',
            'Fargo',
            'ND',
            '58103'
        ));
    }

    /**
     * The list flags an empty NPI, not an invalid format.
     */
    public function testListFlagsEmptyNpiNotInvalidFormat(): void
    {
        $this->assertTrue(AddressBookReferrerFields::npiMissingOnList(''));
        $this->assertTrue(AddressBookReferrerFields::npiMissingOnList(null));
        $this->assertFalse(AddressBookReferrerFields::npiMissingOnList('123'));
        $this->assertFalse(AddressBookReferrerFields::npiMissingOnList('1234567890'));
    }

    /**
     * asString trims strings and turns anything else into ''.
     */
    public function testAsStringDropsNonStrings(): void
    {
        $this->assertSame('', AddressBookReferrerFields::asString(null));
        $this->assertSame('', AddressBookReferrerFields::asString(10));
        $this->assertSame('npi', AddressBookReferrerFields::asString(' npi '));
    }
}
