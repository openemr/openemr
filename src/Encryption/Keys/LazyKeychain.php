<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OutOfBoundsException;
use OpenEMR\Encryption\Cipher\{
    CipherInterface,
    SeparateHmacKeyCipherInterface,
    SingleKeyCipherInterface,
};
use OpenEMR\Encryption\Storage;

class LazyKeychain implements KeychainInterface
{
    private Keychain $keychain;

    /**
     * @var array<string, array{
     *   class: class-string<SingleKeyCipherInterface|SeparateHmacKeyCipherInterface>,
     *   storage: Storage\KeyStorageInterface,
     *   ids: string[],
     * }>
     */
    private array $registrations = [];

    public function __construct()
    {
        $this->keychain = new Keychain();
    }

    /**
     * @param class-string<SingleKeyCipherInterface> $cipherClass
     */
    public function registerSingleKeyCipher(
        Id $keyId,
        string $cipherClass,
        Storage\KeyStorageInterface $storage,
        string $keyMaterialId,
    ): void {
        $this->registrations[$keyId->id] = [
            'class' => $cipherClass,
            'storage' => $storage,
            'ids' => [$keyMaterialId],
        ];
    }

    /**
     * @param class-string<SeparateHmacKeyCipherInterface> $cipherClass
     */
    public function registerTwoKeyCipher(
        Id $keyId,
        string $cipherClass,
        Storage\KeyStorageInterface $storage,
        string $keyMaterialId,
        string $hmacKeyMaterialId,
    ): void {
        $this->registrations[$keyId->id] = [
            'class' => $cipherClass,
            'storage' => $storage,
            'ids' => [$keyMaterialId, $hmacKeyMaterialId],
        ];
    }

    public function getCipher(Id $keyId): CipherInterface
    {
        if ($this->keychain->hasKey($keyId)) {
            return $this->keychain->getCipher($keyId);
        }

        if (!$this->hasKey($keyId)) {
            throw new OutOfBoundsException('Key id not registered');
        }

        [
            'class' => $class,
            'storage' => $storage,
            'ids' => $ids,
        ] = $this->registrations[$keyId->id];
        $keys = array_map(fn ($id) => $storage->getKey($id), $ids);
        // This is a little sketchy but should work based on registration
        $cipher = new $class(...$keys);
        $this->keychain->registerCipher($keyId, $cipher);
        return $cipher;
    }

    public function hasKey(Id $keyId): bool
    {
        // check the wrapped keychain too?
        return array_key_exists($keyId->id, $this->registrations);
    }
}
