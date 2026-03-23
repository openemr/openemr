<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Common\Uuid;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;

#[Group('uuid')]
#[CoversClass(Uuid::class)]
#[CoversMethod(Uuid::class, 'isValid')]
class UuidTest extends TestCase
{
    #[Test]
    #[DataProvider('isValidDataProvider')]
    public function isValidTest(
        string $uuid,
        bool $expected,
    ): void {
        $this->assertEquals(
            $expected,
            Uuid::isValid($uuid),
        );
    }

    public static function isValidDataProvider(): iterable
    {
        $uuid = (new UuidFactory())->uuid4();

        yield 'Invalid - Empty' => [
            '',
            false,
        ];

        yield 'Invalid - Not UUID format' => [
            'invalid',
            false,
        ];

        yield 'Invalid - Byte UUID - Hardcoded' => [
            hex2bin('550e8400e29b41d4a716446655440000'), // Same as 550e8400-e29b-41d4-a716-446655440000
            false,
        ];

        yield 'Invalid - Byte UUID - Generated' => [
            $uuid->getBytes(),
            false,
        ];

        yield 'Valid - Zeroes' => [
            '00000000-0000-0000-0000-000000000000',
            true,
        ];

        yield 'Valid - String UUID - Hardcoded' => [
            '550e8400-e29b-41d4-a716-446655440000',
            true,
        ];

        yield 'Valid - String UUID - Generated' => [
            $uuid->toString(),
            true,
        ];
    }
}
