<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys\Storage;

use Doctrine\DBAL\Connection;
use OpenEMR\Encryption\Keys\KeyMaterial;

readonly class PlaintextKeyInDbKeysTable implements KeyStorageInterface
{
    public function __construct(
        private Connection $conn,
    ) {
    }

    public function getKey(string $identifier): KeyMaterial
    {
        $result = $this->conn->fetchOne(
            'SELECT value FROM `keys` WHERE name = ?',
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
