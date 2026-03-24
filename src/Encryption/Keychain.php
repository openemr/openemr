<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

class Keychain
{
    /**
     * @var array<string, array{Cipher\Id, Keys\Storage\Id}>
     */
    private array $mappings = [];

    /**
     * @var array<string, Keys\Storage\KeyStorageInterface>
     */
    private array $storage = [];

    public function addKey(
        string $id,
        Cipher\Id $cipherId,
        Keys\Storage\Id $storageType,
    ): void {
        $this->mappings[$id] = [$cipherId, $storageType];
    }

    public function addStorage(
        Keys\Storage\Id $storageType,
        Keys\Storage\KeyStorageInterface $storage,
    ): void {
        $this->storage[$storageType->name] = $storage;
    }

    public function getCipher(string $keyId): Cipher\CipherInterface
    {
        [$cipherId, $storageType] = $this->mappings[$keyId];

        $storage = $this->storage[$storageType->name];

        return match ($cipherId) {
        };
    }
}
