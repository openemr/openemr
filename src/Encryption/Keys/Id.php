<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

final readonly class Id
{
    public function __construct(public string $id)
    {
    }
}
