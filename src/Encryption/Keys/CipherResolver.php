<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha256;
use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha384;
use OpenEMR\Encryption\Cipher\Aes256CbcNoHmac;
use OpenEMR\Encryption\Cipher\CipherInterface;

readonly class CipherResolver // implements KeyResolverInterface
{
    public function __construct(
        private KeyManagerInterface $keyManager,
    ) {
    }

    public function resolve(string $keyReference): CipherInterface
    {
        return match ($keyReference) {
            'one' => new Aes256CbcNoHmac(
                key: $this->keyManager->getKey('one'),
            ),
            // v3 uses 'two' keys for historic reasons (there was no v3 key)
            'two' => new Aes256CbcHmacSha256(
                key: $this->keyManager->getKey('twoa'),
                hmacKey: $this->keyManager->getKey('twob'),
            ),
            'four', 'five', 'six', 'seven' => new Aes256CbcHmacSha384(
                key: $this->keyManager->getKey($keyReference . 'a'),
                hmacKey: $this->keyManager->getKey($keyReference . 'b'),
            ),
            default => throw new \InvalidArgumentException("Unknown key reference: $keyReference"),
        };
    }
}
