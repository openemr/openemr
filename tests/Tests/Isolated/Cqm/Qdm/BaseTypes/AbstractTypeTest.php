<?php

/**
 * Isolated AbstractType Test
 *
 * Tests QDM AbstractType base class functionality through concrete implementations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Qdm\BaseTypes;

use OpenEMR\Cqm\Qdm\BaseTypes\Address;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use PHPUnit\Framework\TestCase;

class AbstractTypeTest extends TestCase
{
    public function testConstructorWithEmptyArray(): void
    {
        $code = new Code([]);
        $this->assertNull($code->code);
        $this->assertNull($code->system);
    }

    public function testConstructorSetsProperties(): void
    {
        $code = new Code([
            'code' => '12345',
            'system' => 'http://example.com/codes',
            'display' => 'Test Code'
        ]);

        $this->assertSame('12345', $code->code);
        $this->assertSame('http://example.com/codes', $code->system);
        $this->assertSame('Test Code', $code->display);
    }

    public function testConstructorThrowsOnInvalidProperty(): void
    {
        $this->expectException(\Exception::class);
        new Code(['nonexistent_property' => 'value']);
    }

    public function testPropertyExistsReturnsTrueForExistingProperty(): void
    {
        $code = new Code();
        $this->assertTrue($code->propertyExists('code'));
        $this->assertTrue($code->propertyExists('system'));
        $this->assertTrue($code->propertyExists('display'));
        $this->assertTrue($code->propertyExists('version'));
    }

    public function testPropertyExistsReturnsFalseForNonExistingProperty(): void
    {
        $code = new Code();
        $this->assertFalse($code->propertyExists('nonexistent'));
        $this->assertFalse($code->propertyExists(''));
    }

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $code = new Code([
            'code' => '12345',
            'system' => 'http://example.com'
        ]);

        $json = json_encode($code);
        $this->assertIsString($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('code', $decoded);
        $this->assertArrayHasKey('system', $decoded);
        $this->assertArrayHasKey('_type', $decoded);
        $this->assertSame('12345', $decoded['code']);
    }

    public function testAddressDefaultValues(): void
    {
        $address = new Address();
        $this->assertSame('HP', $address->use);
        $this->assertSame([], $address->street);
        $this->assertNull($address->city);
        $this->assertNull($address->state);
        $this->assertNull($address->zip);
        $this->assertNull($address->country);
    }

    public function testAddressConstructorSetsValues(): void
    {
        $address = new Address([
            'use' => 'WP',
            'street' => ['123 Main St', 'Suite 100'],
            'city' => 'Boston',
            'state' => 'MA',
            'zip' => '02101',
            'country' => 'US'
        ]);

        $this->assertSame('WP', $address->use);
        $this->assertSame(['123 Main St', 'Suite 100'], $address->street);
        $this->assertSame('Boston', $address->city);
        $this->assertSame('MA', $address->state);
        $this->assertSame('02101', $address->zip);
        $this->assertSame('US', $address->country);
    }

    public function testIntervalProperties(): void
    {
        $interval = new Interval([
            'lowClosed' => true,
            'highClosed' => false
        ]);

        $this->assertTrue($interval->lowClosed);
        $this->assertFalse($interval->highClosed);
        $this->assertNull($interval->low);
        $this->assertNull($interval->high);
    }

    public function testQuantityProperties(): void
    {
        $quantity = new Quantity([
            'value' => 100.5,
            'unit' => 'mg'
        ]);

        $this->assertSame(100.5, $quantity->value);
        $this->assertSame('mg', $quantity->unit);
    }

    public function testCodeTypeProperty(): void
    {
        $code = new Code();
        $this->assertSame('QDM::Code', $code->_type);
    }

    public function testJsonSerializeIncludesNullValues(): void
    {
        $code = new Code(['code' => '123']);

        $json = json_encode($code);
        $this->assertIsString($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('display', $decoded);
        $this->assertNull($decoded['display']);
    }

    public function testPartialPropertyAssignment(): void
    {
        $address = new Address([
            'city' => 'New York'
        ]);

        // Only city should change, others keep defaults
        $this->assertSame('New York', $address->city);
        $this->assertSame('HP', $address->use);
        $this->assertSame([], $address->street);
    }
}
