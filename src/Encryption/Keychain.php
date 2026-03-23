<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

class Keychain
{
    /**
     * @var array<string, array{Cipher\Id}>
     */
    private array $mappings = [];

    public function addKey(
        string $id,
        Cipher\Id $cipherId,
    ): void {
        $this->mappings[$id] = [$cipherId];
    }

    public function getCipher(string $keyId): Cipher\CipherInterface
    {
        [$cipherId] = $this->mappings[$keyId];

        return match ($cipherId) {
        };
    }
}
