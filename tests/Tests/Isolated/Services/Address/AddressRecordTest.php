<?php

/**
 * AddressRecord Unit Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Address;

use OpenEMR\Services\Address\AddressRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AddressRecordTest extends TestCase
{
    public function testConstructor(): void
    {
        $address = new AddressRecord(
            street: '123 Main St',
            city: 'Springfield',
            state: 'IL',
            postalCode: '62701',
            countryCode: 'USA',
        );

        $this->assertSame('123 Main St', $address->street);
        $this->assertSame('Springfield', $address->city);
        $this->assertSame('IL', $address->state);
        $this->assertSame('62701', $address->postalCode);
        $this->assertSame('USA', $address->countryCode);
    }

    public function testConstructorDefaults(): void
    {
        $address = new AddressRecord();

        $this->assertSame('', $address->street);
        $this->assertSame('', $address->city);
        $this->assertSame('', $address->state);
        $this->assertSame('', $address->postalCode);
        $this->assertSame('', $address->countryCode);
    }

    public function testFromArray(): void
    {
        $data = [
            'street' => '456 Oak Ave',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60601',
            'country_code' => 'USA',
        ];

        $address = AddressRecord::fromArray($data);

        $this->assertSame('456 Oak Ave', $address->street);
        $this->assertSame('Chicago', $address->city);
        $this->assertSame('IL', $address->state);
        $this->assertSame('60601', $address->postalCode);
        $this->assertSame('USA', $address->countryCode);
    }

    public function testFromArrayWithMissingKeys(): void
    {
        $address = AddressRecord::fromArray([]);

        $this->assertSame('', $address->street);
        $this->assertSame('', $address->city);
        $this->assertSame('', $address->state);
        $this->assertSame('', $address->postalCode);
        $this->assertSame('', $address->countryCode);
    }

    public function testFromArrayWithNonStringValues(): void
    {
        $data = [
            'street' => 123,
            'city' => null,
            'state' => ['array'],
            'postal_code' => 62701,
            'country_code' => true,
        ];

        $address = AddressRecord::fromArray($data);

        // Non-string values should become empty strings
        $this->assertSame('', $address->street);
        $this->assertSame('', $address->city);
        $this->assertSame('', $address->state);
        $this->assertSame('', $address->postalCode);
        $this->assertSame('', $address->countryCode);
    }

    #[DataProvider('toStringProvider')]
    public function testToString(AddressRecord $address, string $expected): void
    {
        $this->assertSame($expected, $address->toString());
        $this->assertSame($expected, (string) $address);
    }

    /**
     * @return array<string, array{AddressRecord, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function toStringProvider(): array
    {
        return [
            'full address' => [
                new AddressRecord(
                    street: '123 Main St',
                    city: 'Springfield',
                    state: 'IL',
                    postalCode: '62701',
                    countryCode: 'USA',
                ),
                "123 Main St\nSpringfield, IL 62701 USA",
            ],
            'no country' => [
                new AddressRecord(
                    street: '123 Main St',
                    city: 'Springfield',
                    state: 'IL',
                    postalCode: '62701',
                ),
                "123 Main St\nSpringfield, IL 62701",
            ],
            'no street' => [
                new AddressRecord(
                    city: 'Springfield',
                    state: 'IL',
                    postalCode: '62701',
                    countryCode: 'USA',
                ),
                'Springfield, IL 62701 USA',
            ],
            'city and state only' => [
                new AddressRecord(
                    city: 'Springfield',
                    state: 'IL',
                ),
                'Springfield, IL',
            ],
            'state only' => [
                new AddressRecord(
                    state: 'IL',
                ),
                'IL',
            ],
            'empty address' => [
                new AddressRecord(),
                '',
            ],
            'street only' => [
                new AddressRecord(
                    street: '123 Main St',
                ),
                '123 Main St',
            ],
            'no city' => [
                new AddressRecord(
                    street: '123 Main St',
                    state: 'IL',
                    postalCode: '62701',
                ),
                "123 Main St\nIL 62701",
            ],
        ];
    }

    public function testImmutability(): void
    {
        $address = new AddressRecord();

        $reflection = new \ReflectionClass($address);
        $this->assertTrue($reflection->isReadOnly());
    }
}
