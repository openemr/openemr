<?php

/**
 * Isolated ValidationUtils Test
 *
 * Tests ValidationUtils functionality without requiring database connections.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\ValidationUtils;
use PHPUnit\Framework\TestCase;

class ValidationUtilsIsolatedTest extends TestCase
{
    public function testEmailValidationWithValidEmails(): void
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.com',
            'user+tag@example.org',
            'valid.email@subdomain.example.com'
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue(
                ValidationUtils::isValidEmail($email),
                "Email should be valid: {$email}"
            );
        }
    }

    public function testEmailValidationWithInvalidEmails(): void
    {
        $invalidEmails = [
            'invalid-email',
            '@domain.com',
            'user@',
            'user@localhost',
            'spaces in@email.com'
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse(
                ValidationUtils::isValidEmail($email),
                "Email should be invalid: {$email}"
            );
        }
    }
}
