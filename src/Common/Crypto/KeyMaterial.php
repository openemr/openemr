<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

use SensitiveParameter;

readonly class KeyMaterial
{
    public function __construct(
        #[SensitiveParameter] public string $key,
        #[SensitiveParameter] public ?string $hmacKey,
    ) {
    }

    public function __debugInfo(): array
    {
        return [
            'key' => '******',
            'hmacKey' => $this->hmacKey === null ? null : '******',
        ];
    }
}
