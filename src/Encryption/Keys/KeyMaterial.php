<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use SensitiveParameter;

/**
 * Wrapper that adds type safety and some amount of stacktrace protection to
 * raw key material.
 */
readonly class KeyMaterial
{
    public function __construct(
        #[SensitiveParameter] public string $key,
    ) {
    }

    public function __debugInfo(): array
    {
        return [
            'key' => '******',
        ];
    }
}
