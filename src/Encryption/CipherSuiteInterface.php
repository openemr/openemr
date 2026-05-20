<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption;

use SensitiveParameter;

/**
 * Cryptography implementations.
 *
 * Unlike the lower-level cryptographic tools, this does not require wrapping
 * and unwrapping `Plaintext` structures (though you _may_ pass a `Plaintext`
 * to the `encrypt()` method).
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
     * Takes a secret value (as a raw string or a pre-wrapped `Plaintext`
     * object), encrypts it, and returns an opaque string holding the encrypted
     * data and a key identifier that can be used to decrypt it later. The
     * return value of this method is guaranteed compatible with `decrypt()` so
     * long as the key used to encrypt it is in the keychain.
     */
    public function encrypt(#[SensitiveParameter] Plaintext|string $plaintext): string;
}
