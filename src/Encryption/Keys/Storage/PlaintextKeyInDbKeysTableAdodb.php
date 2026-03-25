<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys\Storage;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Encryption\Keys\KeyMaterial;

readonly class PlaintextKeyInDbKeysTableAdodb implements KeyStorageInterface
{
    public function getKey(string $identifier): KeyMaterial
    {
        $row = QueryUtils::querySingleRow(
            'SELECT value FROM `keys` WHERE name = ?',
            // 'value',
            [$identifier],
            log: false,
        );
        if ($row === false) {
            throw new \Exception('No key found');
        }
        $result = $row['value'];

        if (!is_string($result)) {
            throw new \Exception('No key found');
        }

        $key = base64_decode($result, strict: true);
        if ($key === false) {
            throw new \Exception('Malformed key');
        }

        return new KeyMaterial($key);
    }

    public function storeKey(string $identifier, KeyMaterial $key): void
    {
        // keys table is (currently) `unique(name)` so we can skip checking if it
        // already exists
        $encoded = base64_encode($key->key);
        // Cannot use sqlInsert, it doesn't have a noLog path
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `keys` (name, value) VALUES (?, ?)',
            [$identifier, $encoded],
            noLog: true,
        );
    }
}
