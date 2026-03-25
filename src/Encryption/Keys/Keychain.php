<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Encryption\Cipher\CipherInterface;
use OutOfBoundsException;

class Keychain implements KeychainInterface
{
    /**
     * @var array<string, CipherInterface>
     */
    private array $mappings = [];

    public function addCipher(
        string $id,
        CipherInterface $cipher,
    ): void {
        $this->mappings[$id] = $cipher;
    }

    // addLoader to defer key loading?

    public function getCipher(string $keyId): CipherInterface
    {
        if (array_key_exists($keyId, $this->mappings)) {
            return $this->mappings[$keyId];
        }
        throw new OutOfBoundsException('Key id not registered');
    }
}
