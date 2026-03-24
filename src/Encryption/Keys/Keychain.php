<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Encryption\Cipher\CipherInterface;

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
        return $this->mappings[$keyId];
    }
}
