<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

interface KeyManagerInterface
{
    public function getKey(string $identifier): KeyMaterial;
}
