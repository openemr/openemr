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
    public function testLocalUsersAndCompaniesSkipTheGuard(): void
    {
        $this->assertFalse(AddressBookReferrerFields::isExternalPerson('admin', '1'));
        $this->assertFalse(AddressBookReferrerFields::isExternalPerson('', '3'));
        $this->assertTrue(AddressBookReferrerFields::isExternalPerson('', '1'));
        $this->assertTrue(AddressBookReferrerFields::isExternalPerson('', ''));
        $this->assertTrue(AddressBookReferrerFields::isExternalPerson(null, '2'));
    }

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

    public function testListFlagsEmptyNpiNotInvalidFormat(): void
    {
        $this->assertTrue(AddressBookReferrerFields::npiMissingOnList(''));
        $this->assertTrue(AddressBookReferrerFields::npiMissingOnList(null));
        $this->assertFalse(AddressBookReferrerFields::npiMissingOnList('123'));
        $this->assertFalse(AddressBookReferrerFields::npiMissingOnList('1234567890'));
    }

    public function testAsStringDropsNonStrings(): void
    {
        $this->assertSame('', AddressBookReferrerFields::asString(null));
        $this->assertSame('', AddressBookReferrerFields::asString(10));
        $this->assertSame('npi', AddressBookReferrerFields::asString(' npi '));
    }
}
