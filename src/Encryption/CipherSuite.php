<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use SensitiveParameter;

final readonly class CipherSuite implements CipherSuiteInterface
{
    public function __construct(
        private Keys\KeychainInterface $keychain,
    ) {
    }

    public function decrypt(string $encodedMessage): string
    {
        $parsed = Message::parse($encodedMessage);
        $cipher = $this->keychain->getCipher($parsed->keyId);
        return $cipher->decrypt($parsed->ciphertext)->bytes;
    }

    public function encrypt(#[SensitiveParameter] string $plaintext): string
    {
        $currentKeyId = $this->keychain->getCurrentKeyId();
        $cipher = $this->keychain->getCipher($currentKeyId);

        $wrapped = new Plaintext($plaintext);
        $ciphertext = $cipher->encrypt($wrapped);
        $message = new Message(keyId: $currentKeyId, ciphertext: $ciphertext);
        return $message->encode();
    }
}
