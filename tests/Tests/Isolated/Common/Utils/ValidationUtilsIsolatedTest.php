<?php

/**
 * Isolated ValidationUtils Test
 *
 * Tests ValidationUtils functionality without requiring database connections.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Common\ValueObjects\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValidationUtilsIsolatedTest extends TestCase
{
    /**
     * Test phone numbers organized by region with expected outputs.
     *
     * Each entry contains:
     * - 'input': Various input formats for the same number
     * - 'e164': Expected E.164 format
     * - 'national': Expected national digits (10 digits for NANP)
     * - 'formatted': Expected formatLocal() output
     * - 'hl7': Expected HL7 format (for NANP numbers)
     * - 'parts': Expected toParts() output (for NANP numbers)
     * - 'region': Default region for parsing (defaults to 'US')
     */
    private const PHONE_NUMBERS = [
        'us_nyc' => [
            'input' => ['2125551234', '212-555-1234', '(212) 555-1234', '212.555.1234', '212 555 1234', '+1 212 555 1234', '+12125551234', '1-212-555-1234'],
            'e164' => '+12125551234',
            'national' => '2125551234',
            'formatted' => '212-555-1234',
            'hl7' => '212^5551234',
            'parts' => ['area_code' => '212', 'prefix' => '555', 'number' => '1234'],
        ],
        'us_la' => [
            'input' => ['3105551234', '310-555-1234', '(310) 555-1234', '310.555.1234', '310   555   1234', '+1 (310) 555-1234'],
            'e164' => '+13105551234',
            'national' => '3105551234',
            'formatted' => '310-555-1234',
            'hl7' => '310^5551234',
            'parts' => ['area_code' => '310', 'prefix' => '555', 'number' => '1234'],
        ],
        'us_jenny' => [
            // 867-5309 - the famous number from Tommy Tutone's 1981 hit
            'input' => ['2128675309', '212-867-5309', '(212) 867-5309', '+1-212-867-5309'],
            'e164' => '+12128675309',
            'national' => '2128675309',
            'formatted' => '212-867-5309',
            'hl7' => '212^8675309',
            'parts' => ['area_code' => '212', 'prefix' => '867', 'number' => '5309'],
        ],
        'us_fictional' => [
            // 555 prefix is reserved for fictional use - parses but isValid() returns false
            'input' => ['5551234567', '555-123-4567', '(555) 123-4567'],
            'e164' => '+15551234567',
            'national' => '5551234567',
            'formatted' => '555-123-4567',
            'valid' => false,
            'possible' => true,
        ],
        'uk_london' => [
            'input' => ['+44 20 7946 0958', '+442079460958'],
            'e164' => '+442079460958',
            'formatted' => '020 7946 0958',
            'region' => 'GB',
        ],
        'de_berlin' => [
            'input' => ['+49 30 12345678', '+493012345678'],
            'e164' => '+493012345678',
            'formatted' => '030 12345678',
            'region' => 'DE',
        ],
        'au_sydney' => [
            'input' => ['+61 2 1234 5678', '+61212345678'],
            'e164' => '+61212345678',
            'formatted' => '(02) 1234 5678',
            'region' => 'AU',
        ],
        'fr_paris' => [
            'input' => ['+33 1 23 45 67 89', '+33123456789'],
            'e164' => '+33123456789',
            'formatted' => '01 23 45 67 89',
            'region' => 'FR',
        ],
    ];

    /**
     * Invalid phone numbers that should fail validation.
     */
    private const INVALID_PHONES = [
        '123456789',            // 9 digits (too short)
        '5551234567',           // 555 is not a real area code (in strict mode)
        'abc-def-ghij',         // no digits
        '',                     // empty string
        '212-1234',             // 7 digits (missing area code)
        '99912345678901234567', // way too long
    ];

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

    public function testValidateIntWithValidIntegers(): void
    {
        $this->assertSame(42, ValidationUtils::validateInt(42));
        $this->assertSame(42, ValidationUtils::validateInt('42'));
        $this->assertSame(0, ValidationUtils::validateInt(0));
        $this->assertSame(0, ValidationUtils::validateInt('0'));
        $this->assertSame(-10, ValidationUtils::validateInt(-10));
        $this->assertSame(-10, ValidationUtils::validateInt('-10'));
    }

    public function testValidateIntWithInvalidValues(): void
    {
        $this->assertFalse(ValidationUtils::validateInt('not a number'));
        $this->assertFalse(ValidationUtils::validateInt(''));
        $this->assertFalse(ValidationUtils::validateInt('1.5'));
        $this->assertFalse(ValidationUtils::validateInt('1e5'));
        $this->assertFalse(ValidationUtils::validateInt(null));
        $this->assertFalse(ValidationUtils::validateInt([]));
    }

    public function testValidateIntWithMinRange(): void
    {
        $this->assertSame(5, ValidationUtils::validateInt(5, min: 1));
        $this->assertSame(1, ValidationUtils::validateInt(1, min: 1));
        $this->assertFalse(ValidationUtils::validateInt(0, min: 1));
        $this->assertFalse(ValidationUtils::validateInt(-5, min: 1));
    }

    public function testValidateIntWithMaxRange(): void
    {
        $this->assertSame(5, ValidationUtils::validateInt(5, max: 10));
        $this->assertSame(10, ValidationUtils::validateInt(10, max: 10));
        $this->assertFalse(ValidationUtils::validateInt(11, max: 10));
        $this->assertFalse(ValidationUtils::validateInt(100, max: 10));
    }

    public function testValidateIntWithMinAndMaxRange(): void
    {
        $this->assertSame(5, ValidationUtils::validateInt(5, min: 1, max: 10));
        $this->assertSame(1, ValidationUtils::validateInt(1, min: 1, max: 10));
        $this->assertSame(10, ValidationUtils::validateInt(10, min: 1, max: 10));
        $this->assertFalse(ValidationUtils::validateInt(0, min: 1, max: 10));
        $this->assertFalse(ValidationUtils::validateInt(11, min: 1, max: 10));
    }

    public function testValidateFloatWithValidFloats(): void
    {
        $this->assertSame(42.5, ValidationUtils::validateFloat(42.5));
        $this->assertSame(42.5, ValidationUtils::validateFloat('42.5'));
        $this->assertSame(0.0, ValidationUtils::validateFloat(0));
        $this->assertSame(0.0, ValidationUtils::validateFloat('0'));
        $this->assertSame(-10.5, ValidationUtils::validateFloat(-10.5));
        $this->assertSame(-10.5, ValidationUtils::validateFloat('-10.5'));
        $this->assertSame(42.0, ValidationUtils::validateFloat(42));
        $this->assertSame(1000.0, ValidationUtils::validateFloat('1e3'));
    }

    public function testValidateFloatWithInvalidValues(): void
    {
        $this->assertFalse(ValidationUtils::validateFloat('not a number'));
        $this->assertFalse(ValidationUtils::validateFloat(''));
        $this->assertFalse(ValidationUtils::validateFloat(null));
        $this->assertFalse(ValidationUtils::validateFloat([]));
    }

    public function testValidateFloatWithMinRange(): void
    {
        $this->assertSame(5.5, ValidationUtils::validateFloat(5.5, min: 1.0));
        $this->assertSame(1.0, ValidationUtils::validateFloat(1.0, min: 1.0));
        $this->assertFalse(ValidationUtils::validateFloat(0.5, min: 1.0));
        $this->assertFalse(ValidationUtils::validateFloat(-5.0, min: 1.0));
    }

    public function testValidateFloatWithMaxRange(): void
    {
        $this->assertSame(5.5, ValidationUtils::validateFloat(5.5, max: 10.0));
        $this->assertSame(10.0, ValidationUtils::validateFloat(10.0, max: 10.0));
        $this->assertFalse(ValidationUtils::validateFloat(10.5, max: 10.0));
        $this->assertFalse(ValidationUtils::validateFloat(100.0, max: 10.0));
    }

    public function testValidateFloatWithMinAndMaxRange(): void
    {
        $this->assertSame(5.5, ValidationUtils::validateFloat(5.5, min: 1.0, max: 10.0));
        $this->assertSame(1.0, ValidationUtils::validateFloat(1.0, min: 1.0, max: 10.0));
        $this->assertSame(10.0, ValidationUtils::validateFloat(10.0, min: 1.0, max: 10.0));
        $this->assertFalse(ValidationUtils::validateFloat(0.5, min: 1.0, max: 10.0));
        $this->assertFalse(ValidationUtils::validateFloat(10.5, min: 1.0, max: 10.0));
    }

    public function testNpiValidationWithValidNpis(): void
    {
        // These NPIs pass the Luhn check with 80840 prefix
        $validNpis = [
            '1234567893',
            '1245319599',
            '1003000126',
        ];

        foreach ($validNpis as $npi) {
            $this->assertTrue(
                ValidationUtils::isValidNPI($npi),
                "NPI should be valid: {$npi}"
            );
        }
    }

    public function testNpiValidationWithInvalidNpis(): void
    {
        $invalidNpis = [
            '1234567890',  // Invalid check digit
            '123456789',   // Too short (9 digits)
            '12345678901', // Too long (11 digits)
            'abcdefghij',  // Non-numeric
            '123456789a',  // Contains letter
            '',            // Empty string
            '0000000000',  // All zeros (invalid check)
        ];

        foreach ($invalidNpis as $npi) {
            $this->assertFalse(
                ValidationUtils::isValidNPI($npi),
                "NPI should be invalid: {$npi}"
            );
        }
    }

    public function testUSPostalCodeValidation(): void
    {
        // Valid US ZIP codes
        $this->assertTrue(ValidationUtils::isValidUSPostalCode('12345'));
        $this->assertTrue(ValidationUtils::isValidUSPostalCode('12345-6789'));
        $this->assertTrue(ValidationUtils::isValidUSPostalCode('00000'));
        $this->assertTrue(ValidationUtils::isValidUSPostalCode('99999-9999'));

        // Invalid US ZIP codes
        $this->assertFalse(ValidationUtils::isValidUSPostalCode('1234'));      // Too short
        $this->assertFalse(ValidationUtils::isValidUSPostalCode('123456'));    // Too long
        $this->assertFalse(ValidationUtils::isValidUSPostalCode('12345-678')); // ZIP+4 too short
        $this->assertFalse(ValidationUtils::isValidUSPostalCode('ABCDE'));     // Letters
        $this->assertFalse(ValidationUtils::isValidUSPostalCode(''));          // Empty
    }

    public function testCAPostalCodeValidation(): void
    {
        // Valid Canadian postal codes
        $this->assertTrue(ValidationUtils::isValidCAPostalCode('A1A 1A1'));
        $this->assertTrue(ValidationUtils::isValidCAPostalCode('A1A1A1'));
        $this->assertTrue(ValidationUtils::isValidCAPostalCode('K1A 0B1'));
        $this->assertTrue(ValidationUtils::isValidCAPostalCode('k1a0b1'));  // Lowercase

        // Invalid Canadian postal codes
        $this->assertFalse(ValidationUtils::isValidCAPostalCode('12345'));     // US format
        $this->assertFalse(ValidationUtils::isValidCAPostalCode('AAA 111'));   // Wrong pattern
        $this->assertFalse(ValidationUtils::isValidCAPostalCode('A1A'));       // Too short
        $this->assertFalse(ValidationUtils::isValidCAPostalCode(''));          // Empty
    }

    public function testPostalCodeValidationByCountry(): void
    {
        // US validation
        $this->assertTrue(ValidationUtils::isValidPostalCode('12345', 'US'));
        $this->assertFalse(ValidationUtils::isValidPostalCode('A1A 1A1', 'US'));

        // CA validation
        $this->assertTrue(ValidationUtils::isValidPostalCode('A1A 1A1', 'CA'));
        $this->assertFalse(ValidationUtils::isValidPostalCode('12345', 'CA'));

        // Default (other countries) - just checks non-empty
        $this->assertTrue(ValidationUtils::isValidPostalCode('12345', 'UK'));
        $this->assertTrue(ValidationUtils::isValidPostalCode('anything', 'DE'));
        $this->assertFalse(ValidationUtils::isValidPostalCode('', 'UK'));
    }

    public function testUrlValidationWithValidUrls(): void
    {
        $validUrls = [
            'http://example.com',
            'https://example.com',
            'http://www.example.com/path',
            'https://example.com/path?query=value',
            'ftp://ftp.example.com',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(
                ValidationUtils::isValidUrl($url),
                "URL should be valid: {$url}"
            );
        }
    }

    public function testUrlValidationWithInvalidUrls(): void
    {
        $invalidUrls = [
            'not-a-url',
            'example.com',       // Missing scheme
            '://missing-scheme',
            '',
            'http://',           // Missing host
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse(
                ValidationUtils::isValidUrl($url),
                "URL should be invalid: {$url}"
            );
        }
    }

    public function testUrlValidationWithHttpsRequirement(): void
    {
        // HTTPS URLs should pass when requireHttps is true
        $this->assertTrue(ValidationUtils::isValidUrl('https://example.com', requireHttps: true));
        $this->assertTrue(ValidationUtils::isValidUrl('HTTPS://EXAMPLE.COM', requireHttps: true));

        // HTTP URLs should fail when requireHttps is true
        $this->assertFalse(ValidationUtils::isValidUrl('http://example.com', requireHttps: true));
        $this->assertFalse(ValidationUtils::isValidUrl('ftp://example.com', requireHttps: true));

        // All valid URLs should pass when requireHttps is false
        $this->assertTrue(ValidationUtils::isValidUrl('http://example.com', requireHttps: false));
        $this->assertTrue(ValidationUtils::isValidUrl('https://example.com', requireHttps: false));
    }

    public function testUuidValidationWithValidUuids(): void
    {
        $validUuids = [
            '550e8400-e29b-41d4-a716-446655440000', // Version 4
            '6ba7b810-9dad-11d1-80b4-00c04fd430c8', // Version 1
            '6ba7b811-9dad-11d1-80b4-00c04fd430c8',
            '00000000-0000-0000-0000-000000000000', // Nil UUID
        ];

        foreach ($validUuids as $uuid) {
            $this->assertTrue(
                ValidationUtils::isValidUuid($uuid),
                "UUID should be valid: {$uuid}"
            );
        }
    }

    public function testUuidValidationWithInvalidUuids(): void
    {
        $invalidUuids = [
            'not-a-uuid',
            '550e8400-e29b-41d4-a716-44665544000', // Too short
            '550e8400-e29b-41d4-a716-4466554400000', // Too long
            '550e8400-e29b-41d4-a716', // Incomplete
            '',
            'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', // Invalid characters
        ];

        foreach ($invalidUuids as $uuid) {
            $this->assertFalse(
                ValidationUtils::isValidUuid($uuid),
                "UUID should be invalid: {$uuid}"
            );
        }
    }

    // =========================================================================
    // Phone Number Data Providers
    // =========================================================================

    /**
     * Provides valid phone numbers for validation testing.
     */
    public static function validPhoneProvider(): iterable
    {
        foreach (self::PHONE_NUMBERS as $name => $data) {
            // Skip fictional numbers in strict mode
            if (($data['valid'] ?? true) === false) {
                continue;
            }
            foreach ($data['input'] as $i => $input) {
                yield "{$name}_{$i}" => [$input, $data['region'] ?? 'US'];
            }
        }
    }

    /**
     * Provides invalid phone numbers for validation testing.
     */
    public static function invalidPhoneProvider(): iterable
    {
        foreach (self::INVALID_PHONES as $i => $phone) {
            yield "invalid_{$i}" => [$phone];
        }
    }

    /**
     * Provides phone numbers with expected E.164 output.
     */
    public static function e164Provider(): iterable
    {
        foreach (self::PHONE_NUMBERS as $name => $data) {
            if (!isset($data['e164'])) {
                continue;
            }
            foreach ($data['input'] as $i => $input) {
                yield "{$name}_{$i}" => [$input, $data['region'] ?? 'US', $data['e164']];
            }
        }
    }

    /**
     * Provides phone numbers with expected national digits.
     */
    public static function nationalDigitsProvider(): iterable
    {
        foreach (self::PHONE_NUMBERS as $name => $data) {
            if (!isset($data['national'])) {
                continue;
            }
            yield $name => [$data['input'][0], $data['region'] ?? 'US', $data['national']];
        }
    }

    /**
     * Provides phone numbers with expected formatLocal() output.
     */
    public static function formatLocalProvider(): iterable
    {
        foreach (self::PHONE_NUMBERS as $name => $data) {
            if (!isset($data['formatted'])) {
                continue;
            }
            yield $name => [$data['input'][0], $data['region'] ?? 'US', $data['formatted']];
        }
    }

    /**
     * Provides phone numbers with expected HL7 output.
     */
    public static function hl7Provider(): iterable
    {
        foreach (self::PHONE_NUMBERS as $name => $data) {
            if (!isset($data['hl7'])) {
                continue;
            }
            yield $name => [$data['input'][0], $data['region'] ?? 'US', $data['hl7']];
        }
    }

    /**
     * Provides phone numbers with expected parts.
     */
    public static function partsProvider(): iterable
    {
        foreach (self::PHONE_NUMBERS as $name => $data) {
            if (!isset($data['parts'])) {
                continue;
            }
            yield $name => [$data['input'][0], $data['region'] ?? 'US', $data['parts']];
        }
    }

    /**
     * Provides fictional phone numbers for non-strict mode testing.
     */
    public static function fictionalPhoneProvider(): iterable
    {
        $fictional = self::PHONE_NUMBERS['us_fictional'];
        foreach ($fictional['input'] as $i => $input) {
            yield "fictional_{$i}" => [$input];
        }
    }

    // =========================================================================
    // Phone Number Tests
    // =========================================================================

    #[DataProvider('validPhoneProvider')]
    public function testPhoneNumberValidationWithValidPhones(string $phone, string $region): void
    {
        $this->assertTrue(ValidationUtils::isValidPhoneNumber($phone, $region));
    }

    #[DataProvider('invalidPhoneProvider')]
    public function testPhoneNumberValidationWithInvalidPhones(string $phone): void
    {
        $this->assertFalse(ValidationUtils::isValidPhoneNumber($phone));
    }

    #[DataProvider('e164Provider')]
    public function testPhoneNumberToE164(string $input, string $region, string $expectedE164): void
    {
        $phone = PhoneNumber::tryParse($input, $region);
        $this->assertNotNull($phone);
        $this->assertSame($expectedE164, $phone->toE164());
    }

    public function testPhoneNumberTryParseReturnsNullForInvalidInput(): void
    {
        $this->assertNull(PhoneNumber::tryParse(''));
        $this->assertNull(PhoneNumber::tryParse('invalid'));
    }

    #[DataProvider('nationalDigitsProvider')]
    public function testPhoneNumberGetNationalDigits(string $input, string $region, string $expectedNational): void
    {
        $phone = PhoneNumber::parse($input, $region);
        $this->assertSame($expectedNational, $phone->getNationalDigits());
    }

    #[DataProvider('formatLocalProvider')]
    public function testPhoneNumberFormatLocal(string $input, string $region, string $expectedFormatted): void
    {
        $phone = PhoneNumber::parse($input, $region);
        $this->assertSame($expectedFormatted, $phone->formatLocal());
    }

    #[DataProvider('hl7Provider')]
    public function testPhoneNumberToHL7(string $input, string $region, string $expectedHL7): void
    {
        $phone = PhoneNumber::parse($input, $region);
        $this->assertSame($expectedHL7, $phone->toHL7());
    }

    #[DataProvider('partsProvider')]
    public function testPhoneNumberToParts(string $input, string $region, array $expectedParts): void
    {
        $phone = PhoneNumber::parse($input, $region);
        $parts = $phone->toParts();
        $this->assertSame($expectedParts['area_code'], $parts['area_code']);
        $this->assertSame($expectedParts['prefix'], $parts['prefix']);
        $this->assertSame($expectedParts['number'], $parts['number']);
    }

    public function testPhoneNumberIsValidVsIsPossible(): void
    {
        // Valid real number - both isValid() and isPossible() return true
        $nyc = self::PHONE_NUMBERS['us_nyc'];
        $validPhone = PhoneNumber::tryParse($nyc['input'][0]);
        $this->assertNotNull($validPhone);
        $this->assertTrue($validPhone->isValid());
        $this->assertTrue($validPhone->isPossible());

        // Fictional 555 area code - isPossible() true but isValid() false
        $fictional = self::PHONE_NUMBERS['us_fictional'];
        $fakePhone = PhoneNumber::tryParse($fictional['input'][0]);
        $this->assertNotNull($fakePhone);
        $this->assertFalse($fakePhone->isValid());
        $this->assertTrue($fakePhone->isPossible());
    }

    #[DataProvider('fictionalPhoneProvider')]
    public function testNonStrictModeAcceptsFictionalNumbers(string $phone): void
    {
        // In non-strict mode, fake numbers like 555-xxx-xxxx are accepted
        // This is useful for synthetic/demo patient data
        $this->assertTrue(ValidationUtils::isValidPhoneNumber($phone, 'US', strict: false));
    }

    public function testNonStrictModeStillRejectsCompletelyInvalidNumbers(): void
    {
        $this->assertFalse(ValidationUtils::isValidPhoneNumber('123', 'US', strict: false));
        $this->assertFalse(ValidationUtils::isValidPhoneNumber('', 'US', strict: false));
    }

    public function testFictionalNumberE164(): void
    {
        $fictional = self::PHONE_NUMBERS['us_fictional'];
        $phone = PhoneNumber::tryParse($fictional['input'][0]);
        $this->assertNotNull($phone);
        $this->assertTrue($phone->isPossible());
        $this->assertSame($fictional['e164'], $phone->toE164());
    }
}
