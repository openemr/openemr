<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys\Storage;

use OpenEMR\Encryption\Keys\KeyMaterial;

interface KeyStorageInterface
{
    public function getKey(string $identifier): KeyMaterial;

    /**
     * This should throw an exception if storing the key failed.
     *
     * It MUST NOT overwrite an existing key.
     */
    public function storeKey(string $identifier, KeyMaterial $key): void;
}
