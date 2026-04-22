<?php

namespace OpenEMR\Tests\Isolated\Common\Uuid;

use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidFactory;

/**
 * Uuid Registry Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
     */
    public function testUuidConversions(): void
    {
        $stringValue = $this->uuidFactory->uuid4()->toString();
        $byteValue = UuidRegistry::uuidToBytes($stringValue);
        $this->assertEquals(UuidRegistry::uuidToBytes($stringValue), $byteValue);
        $this->assertEquals($stringValue, UuidRegistry::uuidToString($byteValue));
    }

    public function testCreateMissingUuidForRowRejectsUnknownTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/unknown table/');
        UuidRegistry::createMissingUuidForRow('not_a_real_table', 'id', 1);
    }

    #[DataProvider('invalidIdColumns')]
    public function testCreateMissingUuidForRowRejectsInvalidIdColumn(string $idColumn): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/invalid id column/');
        UuidRegistry::createMissingUuidForRow('users', $idColumn, 1);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidIdColumns(): array
    {
        return [
            'sql injection attempt' => ['id`; DROP TABLE users; --'],
            'leading digit' => ['1id'],
            'hyphen' => ['user-id'],
            'space' => ['user id'],
            'empty string' => [''],
        ];
    }
}
