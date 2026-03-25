<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Encryption\Cipher\CipherInterface;
use OutOfBoundsException;

use function array_key_exists;

class Keychain implements KeychainInterface
{
    /**
     * @var array<string, CipherInterface>
     */
    private array $mappings = [];

    public function addCipher(
        Id $id,
        CipherInterface $cipher,
    ): void {
        $this->mappings[$id->id] = $cipher;
    }

    // addLoader to defer key loading?

    public function getCipher(Id $keyId): CipherInterface
    {
        if ($this->hasKey($keyId)) {
            return $this->mappings[$keyId->id];
        }
        throw new OutOfBoundsException('Key id not registered');
    }

    public function hasKey(Id $keyId): bool
    {
        return array_key_exists($keyId->id, $this->mappings);
    }
}
