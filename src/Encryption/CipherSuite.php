<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use SensitiveParameter;

class CipherSuite implements CipherSuiteInterface
{
    public function __construct(
        private Keys\KeychainInterface $keychain,
        // Future: current/preferred key moves to KeychainInterface
        private Keys\Id $currentKeyId,
    ) {
    }

    public function decrypt(string $encodedMessage): string
    {
        $parsed = Message::parse($encodedMessage);
        $cipher = $this->keychain->getCipher($parsed->keyId);
        return $cipher->decrypt($parsed->ciphertext)->wrapped;
    }

    public function encrypt(#[SensitiveParameter] string $plaintext): string
    {
        $cipher = $this->keychain->getCipher($this->currentKeyId);

        $wrapped = new Plaintext($plaintext);
        $ciphertext = $cipher->encrypt($wrapped);
        $message = new Message(keyId: $this->currentKeyId, ciphertext: $ciphertext);
        return $message->encode();
    }
}
