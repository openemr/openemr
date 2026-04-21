<?php

/**
 * Tests for ORDataObject::_load_enum
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\ORDataObject;

use OpenEMR\Common\ORDataObject\ORDataObject;
use PHPUnit\Framework\TestCase;

class ORDataObjectLoadEnumTest extends TestCase
{
    protected function setUp(): void
    {
        $this->clearEnumCache();
    }

    public function testLoadEnumWithBlankTrue(): void
    {
        $db = $this->createDbStub([
            $this->createColumnObject('status', 'enum', ["'active'", "'inactive'", "'pending'"]),
        ]);

        $obj = $this->createTestableObject('test_table', $db);

        /** @phpstan-ignore method.notFound (loadEnum added by anonymous subclass) */
        $result = $obj->loadEnum('status', true);

        self::assertSame([
            ' ' => 0,
            'active' => 1,
            'inactive' => 2,
            'pending' => 3,
        ], $result);
    }

    public function testLoadEnumWithBlankFalse(): void
    {
        $db = $this->createDbStub([
            $this->createColumnObject('status', 'enum', ["'active'", "'inactive'"]),
        ]);

        $obj = $this->createTestableObject('test_table', $db);

        /** @phpstan-ignore method.notFound (loadEnum added by anonymous subclass) */
        $result = $obj->loadEnum('status', false);

        self::assertSame([
            'active' => 1,
            'inactive' => 2,
        ], $result);
    }

    public function testLoadEnumReturnsEmptyArrayWhenTableNotSet(): void
    {
        $obj = $this->createTestableObject(null, null);

        /** @phpstan-ignore method.notFound (loadEnum added by anonymous subclass) */
        $result = $obj->loadEnum('status');

        self::assertSame([], $result);
    }

    public function testLoadEnumReturnsEmptyArrayWhenFieldNotFound(): void
    {
        $db = $this->createDbStub([
            $this->createColumnObject('other_field', 'enum', ["'value'"]),
        ]);

        $obj = $this->createTestableObject('test_table', $db);

        /** @phpstan-ignore method.notFound (loadEnum added by anonymous subclass) */
        $result = $obj->loadEnum('nonexistent');

        self::assertSame([], $result);
    }

    public function testLoadEnumCachesResults(): void
    {
        $callCount = 0;
        $db = new class ($callCount) {
            private int $callCount;

            public function __construct(int &$callCount)
            {
                $this->callCount = &$callCount;
            }

            /**
             * @return list<object>
             */
            public function MetaColumns(string $table): array
            {
                $this->callCount++;
                $col = new \stdClass();
                $col->name = 'status';
                $col->type = 'enum';
                $col->enums = ["'active'"];
                return [$col];
            }
        };

        $obj = $this->createTestableObject('test_table', $db);

        // Call twice
        /** @phpstan-ignore method.notFound (loadEnum added by anonymous subclass) */
        $obj->loadEnum('status');
        /** @phpstan-ignore method.notFound (loadEnum added by anonymous subclass) */
        $obj->loadEnum('status');

        self::assertSame(1, $callCount);
    }

    public function testLoadEnumCacheIsSeparateForBlankParameter(): void
    {
        $db = $this->createDbStub([
            $this->createColumnObject('status', 'enum', ["'active'"]),
        ]);

        $obj = $this->createTestableObject('test_table', $db);

        /** @phpstan-ignore method.notFound (loadEnum added by anonymous subclass) */
        $withBlank = $obj->loadEnum('status', true);
        /** @phpstan-ignore method.notFound (loadEnum added by anonymous subclass) */
        $withoutBlank = $obj->loadEnum('status', false);

        self::assertSame([' ' => 0, 'active' => 1], $withBlank);
        self::assertSame(['active' => 1], $withoutBlank);
    }

    private function createTestableObject(?string $table, ?object $db): ORDataObject
    {
        return new class ($table, $db) extends ORDataObject {
            public function __construct(?string $table, ?object $db)
            {
                // Skip parent constructor to avoid OEGlobalsBag dependency
                $this->_table = $table;
                $this->_db = $db;
            }

            /**
             * @return array<string, int>
             */
            public function loadEnum(string $fieldName, bool $blank = true): array
            {
                return $this->_load_enum($fieldName, $blank);
            }
        };
    }

    /**
     * @param list<object> $columns
     */
    private function createDbStub(array $columns): object
    {
        return new class ($columns) {
            /** @param list<object> $columns */
            public function __construct(private readonly array $columns)
            {
            }

            /** @return list<object> */
            public function MetaColumns(string $table): array
            {
                return $this->columns;
            }
        };
    }

    /**
     * @param list<string> $enums
     */
    private function createColumnObject(string $name, string $type, array $enums): object
    {
        $col = new \stdClass();
        $col->name = $name;
        $col->type = $type;
        $col->enums = $enums;
        return $col;
    }

    private function clearEnumCache(): void
    {
        $reflection = new \ReflectionClass(ORDataObject::class);
        $property = $reflection->getProperty('enumCache');
        $property->setValue(null, []);
    }
}
