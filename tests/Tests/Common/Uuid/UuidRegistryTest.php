<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Common\Uuid;

use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\UuidFactory;

#[Group('uuid')]
#[CoversClass(UuidRegistry::class)]
#[CoversMethod(UuidRegistry::class, 'isValidStringUUID')]
#[CoversMethod(UuidRegistry::class, 'uuidToBytes')]
#[CoversMethod(UuidRegistry::class, 'uuidToString')]
class UuidRegistryTest extends TestCase
{
    #[Test]
    public function isValidStringUuidTest(): void
    {
        $uuid = (new UuidFactory())->uuid4();

        $this->assertTrue(UuidRegistry::isValidStringUUID($uuid->toString()));
        $this->assertFalse(UuidRegistry::isValidStringUUID($uuid->getBytes()));
    }

    #[Test]
    public function uuidToBytesTest(): void
    {
        $uuid = (new UuidFactory())->uuid4();

        $this->assertEquals(
            UuidRegistry::uuidToBytes(
                $uuid->toString(),
            ),
            $uuid->getBytes(),
        );
    }

    #[Test]
    public function uuidToStringTest(): void
    {
        $uuid = (new UuidFactory())->uuid4();

        $this->assertEquals(
            $uuid->toString(),
            UuidRegistry::uuidToString(
                $uuid->getBytes(),
            ),
        );
    }
}
