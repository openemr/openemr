<?php

/**
 * Isolated TypedPhoneNumber Test
 *
 * Tests TypedPhoneNumber value object that pairs a phone number with its type.
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
use OpenEMR\Common\ValueObjects\TypedPhoneNumber;
use OpenEMR\Services\PhoneType;
use PHPUnit\Framework\TestCase;

class TypedPhoneNumberTest extends TestCase
{
    public function testConstructorWithPhoneNumberAndType(): void
    {
        $phoneNumber = PhoneNumber::parse('555-123-4567', 'US');
        $typed = new TypedPhoneNumber($phoneNumber, PhoneType::WORK);

        $this->assertSame($phoneNumber, $typed->phoneNumber);
        $this->assertSame(PhoneType::WORK, $typed->type);
    }

    public function testConstructorDefaultsToHomeType(): void
    {
        $phoneNumber = PhoneNumber::parse('555-123-4567', 'US');
        $typed = new TypedPhoneNumber($phoneNumber);

        $this->assertSame(PhoneType::HOME, $typed->type);
    }

    public function testCreateWithValidNumber(): void
    {
        $typed = TypedPhoneNumber::create('555-123-4567', PhoneType::CELL, 'US');

        $this->assertSame(PhoneType::CELL, $typed->type);
        $this->assertSame('+15551234567', $typed->toE164());
    }

    public function testCreateDefaultsToHomeType(): void
    {
        $typed = TypedPhoneNumber::create('555-123-4567', defaultRegion: 'US');

        $this->assertSame(PhoneType::HOME, $typed->type);
    }

    public function testCreateThrowsOnInvalidNumber(): void
    {
        $this->expectException(NumberParseException::class);
        TypedPhoneNumber::create('not a phone number');
    }

    public function testTryCreateReturnsTypedPhoneNumber(): void
    {
        $result = TypedPhoneNumber::tryCreate('555-123-4567', PhoneType::FAX, 'US');

        $this->assertInstanceOf(TypedPhoneNumber::class, $result);
        $this->assertSame(PhoneType::FAX, $result->type);
    }

    public function testTryCreateReturnsNullOnInvalid(): void
    {
        $result = TypedPhoneNumber::tryCreate('not a phone number');

        $this->assertNull($result);
    }

    public function testTryCreateReturnsNullOnEmpty(): void
    {
        $result = TypedPhoneNumber::tryCreate('');

        $this->assertNull($result);
    }

    public function testFormatLocalDelegatesToPhoneNumber(): void
    {
        $typed = TypedPhoneNumber::create('555-123-4567', PhoneType::HOME, 'US');

        $this->assertSame('(555) 123-4567', $typed->formatLocal());
    }

    public function testFormatGlobalDelegatesToPhoneNumber(): void
    {
        $typed = TypedPhoneNumber::create('555-123-4567', PhoneType::HOME, 'US');

        $this->assertSame('+1 555-123-4567', $typed->formatGlobal());
    }

    public function testToE164DelegatesToPhoneNumber(): void
    {
        $typed = TypedPhoneNumber::create('555-123-4567', PhoneType::HOME, 'US');

        $this->assertSame('+15551234567', $typed->toE164());
    }

    public function testToHL7DelegatesToPhoneNumber(): void
    {
        $typed = TypedPhoneNumber::create('555-123-4567', PhoneType::HOME, 'US');

        $this->assertSame('555^1234567', $typed->toHL7());
    }

    public function testToPartsIncludesType(): void
    {
        $typed = TypedPhoneNumber::create('555-123-4567', PhoneType::WORK, 'US');
        $parts = $typed->toParts();

        $this->assertSame('555', $parts['area_code']);
        $this->assertSame('123', $parts['prefix']);
        $this->assertSame('4567', $parts['number']);
        $this->assertSame(PhoneType::WORK->value, $parts['type']);
    }

    public function testToPartsForEachPhoneType(): void
    {
        foreach (PhoneType::cases() as $phoneType) {
            $typed = TypedPhoneNumber::create('555-123-4567', $phoneType, 'US');
            $parts = $typed->toParts();

            $this->assertSame($phoneType->value, $parts['type']);
        }
    }

    public function testPhoneNumberPropertyIsReadonly(): void
    {
        $typed = TypedPhoneNumber::create('555-123-4567', PhoneType::HOME, 'US');

        $reflection = new \ReflectionClass($typed);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testAllPhoneTypesCanBeUsed(): void
    {
        $phoneTypes = [
            PhoneType::HOME,
            PhoneType::WORK,
            PhoneType::CELL,
            PhoneType::EMERGENCY,
            PhoneType::FAX,
        ];

        foreach ($phoneTypes as $type) {
            $typed = TypedPhoneNumber::create('555-123-4567', $type, 'US');
            $this->assertSame($type, $typed->type);
        }
    }
}
