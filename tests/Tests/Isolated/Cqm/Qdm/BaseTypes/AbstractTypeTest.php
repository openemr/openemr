<?php

/**
 * Isolated QDM Base Types Test
 *
 * Tests QDM base type classes (Code, DateTime, Interval, etc.)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Qdm\BaseTypes;

use OpenEMR\Cqm\Qdm\BaseTypes\Address;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use PHPUnit\Framework\TestCase;

class AbstractTypeTest extends TestCase
{
    public function testCodeWithDefaults(): void
    {
        $code = new Code();
        self::assertNull($code->code);
        self::assertNull($code->system);
        self::assertNull($code->display);
        self::assertNull($code->version);
    }

    public function testCodeWithNamedParameters(): void
    {
        $code = new Code(
            code: '12345',
            system: 'http://example.com/codes',
            display: 'Test Code',
        );

        self::assertSame('12345', $code->code);
        self::assertSame('http://example.com/codes', $code->system);
        self::assertSame('Test Code', $code->display);
        self::assertNull($code->version);
    }

    public function testCodeJsonSerialize(): void
    {
        $code = new Code(
            code: '12345',
            system: 'http://example.com',
        );

        $json = json_encode($code);
        self::assertIsString($json);

        $decoded = json_decode($json, true);
        self::assertIsArray($decoded);
        self::assertArrayHasKey('code', $decoded);
        self::assertArrayHasKey('system', $decoded);
        self::assertArrayHasKey('_type', $decoded);
        self::assertSame('12345', $decoded['code']);
        self::assertSame('QDM::Code', $decoded['_type']);
    }

    public function testAddressDefaults(): void
    {
        $address = new Address();
        self::assertSame('HP', $address->use);
        self::assertSame([], $address->street);
        self::assertNull($address->city);
        self::assertNull($address->state);
        self::assertNull($address->zip);
        self::assertNull($address->country);
    }

    public function testAddressWithNamedParameters(): void
    {
        $address = new Address(
            use: 'WP',
            street: ['123 Main St', 'Suite 100'],
            city: 'Boston',
            state: 'MA',
            zip: '02101',
            country: 'US',
        );

        self::assertSame('WP', $address->use);
        self::assertSame(['123 Main St', 'Suite 100'], $address->street);
        self::assertSame('Boston', $address->city);
        self::assertSame('MA', $address->state);
        self::assertSame('02101', $address->zip);
        self::assertSame('US', $address->country);
    }

    public function testIntervalWithNamedParameters(): void
    {
        $interval = new Interval(
            lowClosed: true,
            highClosed: false,
        );

        self::assertTrue($interval->lowClosed);
        self::assertFalse($interval->highClosed);
        self::assertNull($interval->low);
        self::assertNull($interval->high);
    }

    public function testIntervalWithDateTimes(): void
    {
        $interval = new Interval(
            low: new DateTime(date: '2024-01-01'),
            high: new DateTime(date: '2024-12-31'),
            lowClosed: true,
            highClosed: true,
        );

        self::assertInstanceOf(DateTime::class, $interval->low);
        self::assertInstanceOf(DateTime::class, $interval->high);
        self::assertSame('2024-01-01', $interval->low->date);
        self::assertSame('2024-12-31', $interval->high->date);
    }

    public function testQuantityWithNamedParameters(): void
    {
        $quantity = new Quantity(
            value: 100.5,
            unit: 'mg',
        );

        self::assertSame(100.5, $quantity->value);
        self::assertSame('mg', $quantity->unit);
    }

    public function testQuantityJsonSerializeIncludesType(): void
    {
        $quantity = new Quantity(value: 50, unit: 'kg');

        $json = json_encode($quantity);
        self::assertIsString($json);
        $decoded = json_decode($json, true);
        self::assertIsArray($decoded);
        self::assertSame('QDM::Quantity', $decoded['_type']);
    }

    public function testDateTimeJsonSerializesToFormattedString(): void
    {
        $dateTime = new DateTime(date: '2024-06-15 10:30:00');

        $json = json_encode($dateTime);
        self::assertIsString($json);
        // DateTime serializes to a formatted string, not an object
        $decoded = json_decode($json);
        self::assertIsString($decoded);
    }

    public function testIntervalJsonSerializeNestsDateTimes(): void
    {
        $interval = new Interval(
            low: new DateTime(date: '2024-01-01'),
            high: new DateTime(date: '2024-12-31'),
            lowClosed: true,
            highClosed: false,
        );

        $json = json_encode($interval);
        self::assertIsString($json);
        $decoded = json_decode($json, true);
        self::assertIsArray($decoded);

        // DateTime values are serialized as strings within the interval
        self::assertIsString($decoded['low']);
        self::assertIsString($decoded['high']);
        self::assertTrue($decoded['lowClosed']);
        self::assertFalse($decoded['highClosed']);
    }

    public function testAddressPartialParameters(): void
    {
        $address = new Address(city: 'New York');

        // Only city should change, others keep defaults
        self::assertSame('New York', $address->city);
        self::assertSame('HP', $address->use);
        self::assertSame([], $address->street);
    }
}
