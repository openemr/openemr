<?php

/**
 * ValidationUtils is intended for validation methods that are used in OpenEMR.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Utils;

use OpenEMR\Common\Utils\ValidationUtils;
use PHPUnit\Framework\TestCase;

class ValidationUtilsTest extends TestCase
{
    public function testIsValidEmailAcceptsValidEmails()
    {
        // these are emails that have commonly failed in the past that we want to make sure work
        // in our validation
        $validEmails = [
            'example@example.com'
            ,'example+t1@gmail.com'
            ,'example+t1@example.com'
            ,'example.t1@gmail.com'
            ,'example.t1@example.com'
            ,'example.t1+t1@gmail.com'
            ,'ñoñó1234@server.com'
        ];
        foreach ($validEmails as $email) {
            $this->assertTrue(ValidationUtils::isValidEmail($email), "Email should be valid but was invalid - " . $email);
        }

        $invalidEmails = [
            'example@example' // this is a valid email per RFC but we DO NOT allow it in OpenEMR
            ,'localonly' // not valid as there is no domain specifier
            ,'@domain.com' // not valid as there is no local part
            ,'a@a'
            ,'root@localhost'
            ,'ñoñó1234@ñoñó1234example.com'
        ];
        foreach ($invalidEmails as $email) {
            $this->assertFalse(ValidationUtils::isValidEmail($email), "Email should be invalid but was valid- " . $email);
        }
    }
}
