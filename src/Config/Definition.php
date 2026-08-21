<?php

declare(strict_types=1);

namespace OpenEMR\Config;

/**
 * @template T
 */
readonly class Definition
{
    /**
     * @param Key<T> $key
     * @param T $defaultValue
     */
    public function __construct(
        public Key $key,
        public mixed $defaultValue,
        public string $titleKey,
        public string $descriptionKey,
    ) {
    }
}
