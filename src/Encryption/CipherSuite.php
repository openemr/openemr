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

use function is_string;

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

    public function encrypt(#[SensitiveParameter] Plaintext|string $plaintext): string
    {
        $currentKeyId = $this->keychain->getCurrentKeyId();
        $cipher = $this->keychain->getCipher($currentKeyId);

        if (is_string($plaintext)) {
            $plaintext = new Plaintext($plaintext);
        }
        $ciphertext = $cipher->encrypt($plaintext);
        $message = new Message(keyId: $currentKeyId, ciphertext: $ciphertext);
        return $message->encode();
    }
}
