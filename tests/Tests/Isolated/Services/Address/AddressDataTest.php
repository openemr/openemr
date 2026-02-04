<?php

/**
 * AddressData Unit Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Address;

use OpenEMR\Services\Address\AddressData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AddressDataTest extends TestCase
{
    public function testConstructor(): void
    {
        $address = new AddressData(
            line1: '123 Main St',
            line2: 'Apt 4',
            city: 'Springfield',
            state: 'IL',
            zip: '62701',
            country: 'USA',
            plusFour: '1234',
        );

        $this->assertSame('123 Main St', $address->line1);
        $this->assertSame('Apt 4', $address->line2);
        $this->assertSame('Springfield', $address->city);
        $this->assertSame('IL', $address->state);
        $this->assertSame('62701', $address->zip);
        $this->assertSame('USA', $address->country);
        $this->assertSame('1234', $address->plusFour);
    }

    public function testConstructorWithNullPlusFour(): void
    {
        $address = new AddressData(
            line1: '123 Main St',
            line2: '',
            city: 'Springfield',
            state: 'IL',
            zip: '62701',
            country: 'USA',
        );

        $this->assertNull($address->plusFour);
    }

    public function testFromArray(): void
    {
        $data = [
            'line1' => '456 Oak Ave',
            'line2' => 'Suite 100',
            'city' => 'Chicago',
            'state' => 'IL',
            'zip' => '60601',
            'country' => 'USA',
            'plus_four' => '5678',
        ];

        $address = AddressData::fromArray($data);

        $this->assertSame('456 Oak Ave', $address->line1);
        $this->assertSame('Suite 100', $address->line2);
        $this->assertSame('Chicago', $address->city);
        $this->assertSame('IL', $address->state);
        $this->assertSame('60601', $address->zip);
        $this->assertSame('USA', $address->country);
        $this->assertSame('5678', $address->plusFour);
    }

    public function testFromArrayWithMissingKeys(): void
    {
        $address = AddressData::fromArray([]);

        $this->assertSame('', $address->line1);
        $this->assertSame('', $address->line2);
        $this->assertSame('', $address->city);
        $this->assertSame('', $address->state);
        $this->assertSame('', $address->zip);
        $this->assertSame('', $address->country);
        $this->assertNull($address->plusFour);
    }

    public function testFromArrayWithNonStringValues(): void
    {
        $data = [
            'line1' => 123,
            'line2' => null,
            'city' => ['not', 'a', 'string'],
            'state' => true,
            'zip' => 62701,
            'country' => new \stdClass(),
            'plus_four' => 1234,
        ];

        $address = AddressData::fromArray($data);

        // Non-string values should become empty strings
        $this->assertSame('', $address->line1);
        $this->assertSame('', $address->line2);
        $this->assertSame('', $address->city);
        $this->assertSame('', $address->state);
        $this->assertSame('', $address->zip);
        $this->assertSame('', $address->country);
        $this->assertNull($address->plusFour);
    }

    public function testToArray(): void
    {
        $address = new AddressData(
            line1: '789 Pine Rd',
            line2: '',
            city: 'Boston',
            state: 'MA',
            zip: '02101',
            country: 'USA',
            plusFour: '9999',
        );

        $expected = [
            'line1' => '789 Pine Rd',
            'line2' => '',
            'city' => 'Boston',
            'state' => 'MA',
            'zip' => '02101',
            'country' => 'USA',
            'plus_four' => '9999',
        ];

        $this->assertSame($expected, $address->toArray());
    }

    public function testToArrayWithNullPlusFour(): void
    {
        $address = new AddressData(
            line1: '789 Pine Rd',
            line2: '',
            city: 'Boston',
            state: 'MA',
            zip: '02101',
            country: 'USA',
        );

        $result = $address->toArray();

        $this->assertNull($result['plus_four']);
    }

    public function testImmutability(): void
    {
        $address = new AddressData(
            line1: '123 Main St',
            line2: '',
            city: 'Springfield',
            state: 'IL',
            zip: '62701',
            country: 'USA',
        );

        // Verify the class is readonly by checking reflection
        $reflection = new \ReflectionClass($address);
        $this->assertTrue($reflection->isReadOnly());
    }
}
