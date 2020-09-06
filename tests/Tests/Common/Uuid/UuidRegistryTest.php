<?php

namespace OpenEMR\Tests\Common\Uuid;

use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\UuidFactory;

/**
 * Uuid Registry Tests
 * @coversDefaultClass OpenEMR\Common\UuidRegistry
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class UuidRegistryTest extends TestCase
{
    private $uuidFactory;

    protected function setUp(): void
    {
        $this->uuidFactory = new UuidFactory();
    }

    /**
     * Tests bi-directional uuid conversions
     * @covers ::uuidToBytes
     * @covers ::uuidToString
     */
    public function testUuidConversions()
    {
        $stringValue = $this->uuidFactory->uuid4()->toString();
        $byteValue = UuidRegistry::uuidToBytes($stringValue);
        $this->assertEquals(UuidRegistry::uuidToBytes($stringValue), $byteValue);
        $this->assertEquals($stringValue, UuidRegistry::uuidToString($byteValue));
    }
}
