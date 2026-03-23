<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\Common;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Isolated\Validators\UserValidatorIsolatedTest;

trait UserApiPatchDataProviderAwareTrait
{
    /**
     * @todo Double-check we have same set of cases as at UserValidatorIsolatedTest
     *
     * @see UserValidatorIsolatedTest::validateDataProvider()
     */
    public static function patchFailedValidationDataProvider(): iterable
    {
        yield 'Too long fname' => [[
            'fname' => str_pad('', 256, '_'),
            'lname' => 'Correct',
            'username' => 'testuser0',
        ], 1, [
            'fname' => [
                'LengthBetween::TOO_LONG' => 'First Name must be 255 characters or shorter',
            ],
        ]];

        yield 'Too short lname' => [[
            'fname' => 'Correct',
            'lname' => str_pad('', 1, '_'),
            'username' => 'testuser0',
        ], 1, [
            'lname' => [
                'LengthBetween::TOO_SHORT' => 'Last Name must be 2 characters or longer',
            ],
        ]];

        yield 'Too long lname' => [[
            'fname' => 'Correct',
            'lname' => str_pad('', 256, '_'),
            'username' => 'testuser0',
        ], 1, [
            'lname' => [
                'LengthBetween::TOO_LONG' => 'Last Name must be 255 characters or shorter',
            ],
        ]];

        yield 'Invalid email' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'email' => 'invalid@examplecom',
            'username' => 'testuser0',
        ], 1, [
            'email' => [
                'Email::INVALID' => 'Email invalid@examplecom is not a valid email',
            ],
        ]];

        yield 'Too short username' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 2, '_'),
        ], 1, [
            'username' => [
                'LengthBetween::TOO_SHORT' => 'Username must be 3 characters or longer',
            ],
        ]];

        yield 'Too long username' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 33, '_'),
        ], 1, [
            'username' => [
                'LengthBetween::TOO_LONG' => 'Username must be 32 characters or shorter',
            ],
        ]];

        yield 'Too short password' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 8, '_'),
        ], 1, [
            'password' => [
                'LengthBetween::TOO_SHORT' => 'Password must be 9 characters or longer',
            ],
        ]];

        yield 'Too long password' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 73, '_'),
        ], 1, [
            'password' => [
                'LengthBetween::TOO_LONG' => 'Password must be 72 characters or shorter',
            ],
        ]];

        $globals = OEGlobalsBag::getInstance();
        if ($globals->getBoolean('secure_password')) {
            yield 'Not strong password passed' => [[
                'fname' => 'Correct',
                'lname' => 'Correct',
                'username' => 'testuser0',
                'password' => str_pad('aB', 9, '_'), // Valid length, but missing numerics - password is not secure
            ], 1, [
                'password' => [
                    'Password::TOO_WEAK' => 'Provided password is too weak',
                ],
            ]];
        }
    }

    public static function patchSucceededDataProvider(): iterable
    {
        yield 'Empty' => [[]];

        yield 'Fractional - First name' => [[
            'fname' => 'Igor',
        ]];

        yield 'Fractional - Last name' => [[
            'lname' => 'Mukhin',
        ]];

        yield 'Fractional - Username' => [[
            'username' => 'admin2',
        ]];

        yield 'Fractional - Password' => [[
            'password' => 'cOrrectPa$$w0rd',
        ]];

        yield 'Partial - Username & Password' => [[
            'username' => 'admin2',
            'password' => 'cOrrectPa$$w0rd',
        ]];

        yield 'Full' => [[
            'title' => 'Dr.',
            'fname' => 'Test',
            'mname' => 'M.',
            'lname' => 'User',
            'federaltaxid' => '999-99-9999',
            'federaldrugid' => 'EF2345678',
            'upin' => '',
            'facility_id' => '6',
            'facility' => 'Harmony Medical Group',
            'npi' => '1234567890',
            'email' => 'apatel@example.com',
            'specialty' => 'Cardiology',
            'billname' => null,
            'url' => null,
            'assistant' => null,
            'organization' => 'Harmony Medical',
            'valedictory' => 'MD',
            'street' => '321 Ocean Blvd',
            'streetb' => 'Suite 500',
            'city' => 'Miami',
            'state' => 'FL',
            'zip' => '33101',
            'phone' => '{305} 555-3310',
            'fax' => '{305} 555-3311',
            'phonew1' => '{305} 555-3312',
            'phonecell' => '{305} 555-3313',
            'notes' => null,
            'state_license_number' => 'FL778899',
            'username' => 'testuser3',
            'password' => 'testUser3Pa$$w0rd',
        ]];
    }
}
