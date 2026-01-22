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

    public function testIpAddressValidationWithValidIpv4(): void
    {
        $validIps = [
            '192.168.1.1',
            '10.0.0.1',
            '172.16.0.1',
            '8.8.8.8',
            '0.0.0.0',
            '255.255.255.255'
        ];

        foreach ($validIps as $ip) {
            $this->assertTrue(
                ValidationUtils::isValidIpAddress($ip),
                "IP should be valid: {$ip}"
            );
        }
    }

    public function testIpAddressValidationWithValidIpv6(): void
    {
        $validIps = [
            '::1',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            '2001:db8::1',
            'fe80::1'
        ];

        foreach ($validIps as $ip) {
            $this->assertTrue(
                ValidationUtils::isValidIpAddress($ip),
                "IPv6 should be valid: {$ip}"
            );
        }
    }

    public function testIpAddressValidationWithInvalidIps(): void
    {
        $invalidIps = [
            'not-an-ip',
            '256.256.256.256',
            '192.168.1',
            '192.168.1.1.1',
            '999.999.999.999',
            ''
        ];

        foreach ($invalidIps as $ip) {
            $this->assertFalse(
                ValidationUtils::isValidIpAddress($ip),
                "IP should be invalid: {$ip}"
            );
        }
    }

    public function testIpAddressValidationWithIpv4Flag(): void
    {
        // Valid IPv4
        $this->assertTrue(ValidationUtils::isValidIpAddress('192.168.1.1', FILTER_FLAG_IPV4));

        // IPv6 should fail with IPv4 flag
        $this->assertFalse(ValidationUtils::isValidIpAddress('::1', FILTER_FLAG_IPV4));
    }

    public function testIpAddressValidationWithIpv6Flag(): void
    {
        // Valid IPv6
        $this->assertTrue(ValidationUtils::isValidIpAddress('::1', FILTER_FLAG_IPV6));

        // IPv4 should fail with IPv6 flag
        $this->assertFalse(ValidationUtils::isValidIpAddress('192.168.1.1', FILTER_FLAG_IPV6));
    }

    public function testIpAddressValidationWithPrivateRangeFlag(): void
    {
        // Private IPs should fail with NO_PRIV_RANGE flag
        $this->assertFalse(ValidationUtils::isValidIpAddress('192.168.1.1', FILTER_FLAG_NO_PRIV_RANGE));
        $this->assertFalse(ValidationUtils::isValidIpAddress('10.0.0.1', FILTER_FLAG_NO_PRIV_RANGE));
        $this->assertFalse(ValidationUtils::isValidIpAddress('172.16.0.1', FILTER_FLAG_NO_PRIV_RANGE));

        // Public IPs should pass
        $this->assertTrue(ValidationUtils::isValidIpAddress('8.8.8.8', FILTER_FLAG_NO_PRIV_RANGE));
    }
}
