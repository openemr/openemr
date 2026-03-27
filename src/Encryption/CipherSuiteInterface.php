<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use SensitiveParameter;

/**
 * Cryptography implementations.
 *
 * Unlike the lower-level cryptographic tools, this does not require wrapping
 * and unwrapping `Plaintext` structures.
 *
 * Note: This is the replacement for `CryptoInterface` (and `CryptoGen`), and
 * should be preferred whenever possible.
 */
interface CipherSuiteInterface
{
    /**
     * Takes an encrypted and encoded message (the result of `encrypt()`) and
     * decrypts it, returning the originally-encrypted data as a string.
     */
    public function decrypt(string $encodedMessage): string;


    /**
     * Takes a sensitive value as a string and encrypts it, wrapping the
     * encrypted data in a format that includes enough metadata to later
     * decrypt it using the `decrypt()` method.
     */
    public function encrypt(#[SensitiveParameter] string $plaintext): string;
}
