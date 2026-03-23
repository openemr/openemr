<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys\Storage;

use OpenEMR\Encryption\Keys\KeyMaterial;

interface KeyStorageInterface
{
    public function getKey(string $identifier): KeyMaterial;
}
