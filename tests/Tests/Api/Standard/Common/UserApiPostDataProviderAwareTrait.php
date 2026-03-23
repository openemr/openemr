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

trait UserApiPostDataProviderAwareTrait
{
    public static function postSucceededDataProvider(): iterable
    {
        yield 'Minimal' => [[
            'fname' => 'Test',
            'lname' => 'User',
            'username' => 'testuser1',
        ]];

        yield 'Minimal with password' => [[
            'fname' => 'Test',
            'lname' => 'User',
            'username' => 'testuser2',
            'password' => 'testUser2Pa$$w0rd',
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
