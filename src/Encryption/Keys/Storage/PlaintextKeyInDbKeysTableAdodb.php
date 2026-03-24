<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys\Storage;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Encryption\Keys\KeyMaterial;

readonly class PlaintextKeyInDbKeysTableAdodb implements KeyStorageInterface
{
    public function getKey(string $identifier): KeyMaterial
    {
        $result = QueryUtils::fetchSingleValue(
            'SELECT value FROM `keys` WHERE name = ?',
            'value',
            [$identifier],
        );

        if (!is_string($result)) {
            throw new \Exception('No key found');
        }

        $key = base64_decode($result, strict: true);
        if ($key === false) {
            throw new \Exception('Malformed key');
        }

        return new KeyMaterial($key);
    }
}
