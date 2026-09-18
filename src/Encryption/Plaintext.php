<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use SensitiveParameter;

final readonly class Plaintext
{
    public function __construct(
        #[SensitiveParameter] public string $bytes,
    ) {
    }

    public function __debugInfo(): array
    {
        return [
            'bytes' => '****',
        ];
    }
}
