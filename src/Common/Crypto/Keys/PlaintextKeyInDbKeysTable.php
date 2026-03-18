<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto\Keys;

use Doctrine\DBAL\Connection;

class PlaintextKeyInDbKeysTable implements KeyManagerInterface
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
