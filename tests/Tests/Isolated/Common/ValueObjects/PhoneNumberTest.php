<?php

/**
 * Isolated PhoneNumber Test
 *
 * Tests PhoneNumber value object parsing and formatting.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\ValueObjects;

use libphonenumber\NumberParseException;
use OpenEMR\Common\ValueObjects\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function testParseValidUSNumber(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertTrue($phone->isPossible());
    }

    public function testParseWithCountryCode(): void
    {
        $phone = PhoneNumber::parse('+1 555-123-4567');
        $this->assertSame(1, $phone->getCountryCode());
    }

    public function testParseThrowsOnInvalidNumber(): void
    {
        $this->expectException(NumberParseException::class);
        PhoneNumber::parse('not a phone number');
    }

    public function testTryParseReturnsNullOnInvalid(): void
    {
        $result = PhoneNumber::tryParse('not a phone number');
        $this->assertNull($result);
    }

    public function testTryParseReturnsNullOnEmpty(): void
    {
        $result = PhoneNumber::tryParse('');
        $this->assertNull($result);
    }

    public function testTryParseReturnsPhoneNumberOnValid(): void
    {
        $result = PhoneNumber::tryParse('555-123-4567', 'US');
        $this->assertInstanceOf(PhoneNumber::class, $result);
    }

    public function testToE164Format(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('+15551234567', $phone->toE164());
    }

    public function testToNationalFormat(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('(555) 123-4567', $phone->toNational());
    }

    public function testToInternationalFormat(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('+1 555-123-4567', $phone->toInternational());
    }

    public function testToRFC3966Format(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('tel:+1-555-123-4567', $phone->toRFC3966());
    }

    public function testFormatLocalReturnsNational(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame($phone->toNational(), $phone->formatLocal());
    }

    public function testFormatGlobalReturnsInternational(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame($phone->toInternational(), $phone->formatGlobal());
    }

    public function testToHL7Format(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('555^1234567', $phone->toHL7());
    }

    public function testGetNationalDigits(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('5551234567', $phone->getNationalDigits());
    }

    public function testGetNationalDigitsReturnsNullForShortNumber(): void
    {
        // Short number - less than 10 digits
        $phone = PhoneNumber::parse('+49 30 12345');
        $this->assertNull($phone->getNationalDigits());
    }

    public function testGetCountryCode(): void
    {
        $phone = PhoneNumber::parse('+1 555-123-4567');
        $this->assertSame(1, $phone->getCountryCode());

        $phone = PhoneNumber::parse('+44 20 7946 0958');
        $this->assertSame(44, $phone->getCountryCode());
    }

    public function testGetRegionCode(): void
    {
        $phone = PhoneNumber::parse('+1 202-555-0123');
        $this->assertSame('US', $phone->getRegionCode());
    }

    public function testGetAreaCode(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('555', $phone->getAreaCode());
    }

    public function testGetPrefix(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('123', $phone->getPrefix());
    }

    public function testGetSubscriberNumber(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('4567', $phone->getSubscriberNumber());
    }

    public function testToParts(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $parts = $phone->toParts();

        $this->assertSame('555', $parts['area_code']);
        $this->assertSame('123', $parts['prefix']);
        $this->assertSame('4567', $parts['number']);
    }

    public function testToStringReturnsE164(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertSame('+15551234567', (string) $phone);
    }

    public function testIsValidWithValidNumber(): void
    {
        // Real US number format
        $phone = PhoneNumber::parse('202-555-0123', 'US');
        $this->assertTrue($phone->isPossible());
    }

    public function testIsPossibleWithPossibleNumber(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertTrue($phone->isPossible());
    }

    public function testParseVariousFormats(): void
    {
        // All should parse to the same number
        $formats = [
            '5551234567',
            '555-123-4567',
            '(555) 123-4567',
            '555.123.4567',
            '+1 555 123 4567',
        ];

        $expected = '+15551234567';
        foreach ($formats as $format) {
            $phone = PhoneNumber::parse($format, 'US');
            $this->assertSame($expected, $phone->toE164(), "Failed for format: $format");
        }
    }

    public function testInternationalNumbers(): void
    {
        // UK number
        $uk = PhoneNumber::parse('+44 20 7946 0958');
        $this->assertSame(44, $uk->getCountryCode());
        $this->assertSame('GB', $uk->getRegionCode());

        // German number
        $de = PhoneNumber::parse('+49 30 123456');
        $this->assertSame(49, $de->getCountryCode());
        $this->assertSame('DE', $de->getRegionCode());
    }

    public function testGetExtension(): void
    {
        $phone = PhoneNumber::parse('555-123-4567 ext 123', 'US');
        $this->assertSame('123', $phone->getExtension());
    }

    public function testGetExtensionReturnsNullWhenNone(): void
    {
        $phone = PhoneNumber::parse('555-123-4567', 'US');
        $this->assertNull($phone->getExtension());
    }
}
