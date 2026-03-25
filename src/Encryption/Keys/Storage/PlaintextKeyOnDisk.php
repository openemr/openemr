<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys\Storage;

use OpenEMR\Encryption\Keys\KeyMaterial;

/**
 * @deprecated For backwards compatibility only.
 */
readonly class PlaintextKeyOnDisk implements KeyStorageInterface
{
    public function __construct(private string $storageDir)
    {
    }

    public function getKey(string $identifier): KeyMaterial
    {
        $path = sprintf('%s/%s', $this->storageDir, $identifier);
        if (!file_exists($path)) {
            throw new \Exception('Key not found');
        }
        $encoded = file_get_contents($path);
        if ($encoded === false) {
            throw new \Exception('Could not read key');
        }
        $decoded = base64_decode($encoded, strict: true);
        if ($decoded === false) {
            throw new \Exception('Could not decode key');
        }
        return new KeyMaterial(key: $decoded);
    }

    public function storeKey(string $identifier, KeyMaterial $key): void
    {
        $path = sprintf('%s/%s', $this->storageDir, $identifier);
        if (file_exists($path)) {
            throw new \Exception('Key exists, will not overwrite');
        }
        $encoded = base64_encode($key->key);
        $result = file_put_contents($path, $encoded);
        if ($result === false) {
            throw new \Exception('Key writing failed');
        }
    }
}
