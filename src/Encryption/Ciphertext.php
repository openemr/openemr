<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

final readonly class Ciphertext
{
    public function __construct(public string $wrapped)
    {
    }
}
