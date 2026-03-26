<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use SensitiveParameter;

readonly class Plaintext
{
    public function __construct(
        #[SensitiveParameter] public string $wrapped,
    ) {
    }

    public function __debugInfo(): array
    {
        return [
            'wrapped' => '****',
        ];
    }
}
