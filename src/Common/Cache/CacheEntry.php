<?php

/**
 * Typed envelope for serialized cache entries.
 *
 * All PSR-16 cache backends in this namespace serialize and deserialize
 * only this class. By restricting unserialize() to CacheEntry, we prevent
 * PHP object injection attacks — no arbitrary classes can be instantiated
 * from cached data.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Cache;

final readonly class CacheEntry
{
    /**
     * @param mixed    $value     The cached value (scalars, arrays, null).
     * @param int|null $expiresAt Unix timestamp when this entry expires, or null for no expiration.
     */
    public function __construct(
        public mixed $value,
        public ?int $expiresAt = null,
    ) {
    }

    public function isExpired(int $now): bool
    {
        return $this->expiresAt !== null && $this->expiresAt < $now;
    }

    /**
     * Allowed classes list for unserialize().
     *
     * @return array{allowed_classes: list<class-string>}
     */
    public static function unserializeOptions(): array
    {
        return ['allowed_classes' => [self::class]];
    }
}
