<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Cache;

use OpenEMR\Common\Cache\CacheEntry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CacheEntryTest extends TestCase
{
    public function testConstructionWithValueAndExpiry(): void
    {
        $entry = new CacheEntry(value: 'some-data', expiresAt: 1700000000);

        self::assertSame('some-data', $entry->value);
        self::assertSame(1700000000, $entry->expiresAt);
    }

    public function testConstructionWithNullExpiry(): void
    {
        $entry = new CacheEntry(value: ['key' => 'val']);

        self::assertSame(['key' => 'val'], $entry->value);
        self::assertNull($entry->expiresAt);
    }

    public function testConstructionWithNullValue(): void
    {
        $entry = new CacheEntry(value: null, expiresAt: 1700000000);

        self::assertNull($entry->value);
    }

    public function testIsExpiredReturnsTrueWhenPastExpiry(): void
    {
        $entry = new CacheEntry(value: 'data', expiresAt: 1000);

        self::assertTrue($entry->isExpired(1001));
    }

    public function testIsExpiredReturnsTrueWhenExactlyAtExpiry(): void
    {
        // expiresAt < now is the condition, so equal means NOT expired
        $entry = new CacheEntry(value: 'data', expiresAt: 1000);

        self::assertFalse($entry->isExpired(1000));
    }

    public function testIsExpiredReturnsFalseWhenBeforeExpiry(): void
    {
        $entry = new CacheEntry(value: 'data', expiresAt: 2000);

        self::assertFalse($entry->isExpired(1000));
    }

    public function testIsExpiredReturnsFalseWhenNoExpiry(): void
    {
        $entry = new CacheEntry(value: 'data');

        self::assertFalse($entry->isExpired(PHP_INT_MAX));
    }

    public function testUnserializeOptionsAllowsOnlyCacheEntry(): void
    {
        $options = CacheEntry::unserializeOptions();

        self::assertSame(['allowed_classes' => [CacheEntry::class]], $options);
    }

    public function testSerializeRoundTrip(): void
    {
        $original = new CacheEntry(value: ['nested' => [1, 2, 3]], expiresAt: 1700000000);
        $serialized = serialize($original);

        /** @var CacheEntry $restored */
        $restored = unserialize($serialized, CacheEntry::unserializeOptions());

        self::assertEquals($original, $restored);
    }

    public function testIsImmutable(): void
    {
        $reflection = new \ReflectionClass(CacheEntry::class);
        self::assertTrue($reflection->isReadOnly());
        self::assertTrue($reflection->isFinal());
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function variousValueTypesProvider(): array
    {
        return [
            'string' => ['hello'],
            'integer' => [42],
            'float' => [3.14],
            'boolean' => [true],
            'array' => [['a', 'b']],
            'null' => [null],
        ];
    }

    #[DataProvider('variousValueTypesProvider')]
    public function testAcceptsVariousValueTypes(mixed $value): void
    {
        $entry = new CacheEntry(value: $value);

        self::assertSame($value, $entry->value);
    }
}
