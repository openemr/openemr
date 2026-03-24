<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

class Keychain
{
    /**
     * @var array<string, Cipher\CipherInterface>
     */
    private array $mappings = [];

    public function addCipher(
        string $id,
        Cipher\CipherInterface $cipher,
    ): void {
        $this->mappings[$id] = $cipher;
    }

    // addLoader to defer key loading?

    public function getCipher(string $keyId): Cipher\CipherInterface
    {
        return $this->mappings[$keyId];
    }
}
