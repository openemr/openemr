<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OutOfBoundsException;
use OpenEMR\Encryption\{
    Cipher\CipherInterface,
    KeyId,
    Storage,
};

/**
 * @phpstan-type Loader callable(): CipherInterface
 */
class LazyKeychain implements KeychainInterface
{
    private Keychain $keychain;

    /**
     * @var array<string, Loader>
     */
    private array $loaders = [];

    public function __construct()
    {
        $this->keychain = new Keychain();
    }

    /**
     * @param Loader $loader
     */
    public function registerLoader(KeyId $keyId, callable $loader): void
    {
        $this->loaders[$keyId->id] = $loader;
    }

    public function getCipher(KeyId $keyId): CipherInterface
    {
        if ($this->keychain->hasKey($keyId)) {
            return $this->keychain->getCipher($keyId);
        }

        if (!$this->hasKey($keyId)) {
            throw new OutOfBoundsException('Key id not registered');
        }

        $loader = $this->loaders[$keyId->id];
        $cipher = $loader();
        $this->keychain->registerCipher($keyId, $cipher);
        return $cipher;
    }

    public function getCurrentKeyId(): KeyId
    {
    }

    public function hasKey(KeyId $keyId): bool
    {
        // check the wrapped keychain too?
        return array_key_exists($keyId->id, $this->loaders);
    }
}
