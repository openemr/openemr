<?php

/**
 * OAuth2KeyMissing.php is responsible for tracking missing OAuth2 keys.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020-2025 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

class OAuth2KeyMissing
{
    private bool $encryptionKeyMissing = false;
    private bool $passphraseMissing = false;
    private bool $publicKeyMissing = false;
    private bool $privateKeyMissing = false;

    public function setEncryptionKeyMissing(): void
    {
        $this->encryptionKeyMissing = true;
    }

    public function setPassphraseMissing(): void
    {
        $this->passphraseMissing = true;
    }

    public function setPublicKeyMissing(): void
    {
        $this->publicKeyMissing = true;
    }

    public function setPrivateKeyMissing(): void
    {
        $this->privateKeyMissing = true;
    }

    public function isMissingAll(): bool
    {
        return $this->encryptionKeyMissing && $this->passphraseMissing && $this->publicKeyMissing && $this->privateKeyMissing;
    }

    public function isMissingToString(): string
    {
        $missing = '';
        if ($this->encryptionKeyMissing) {
            $missing .= 'encryption key, ';
        }
        if ($this->passphraseMissing) {
            $missing .= 'passphrase, ';
        }
        if ($this->publicKeyMissing) {
            $missing .= 'public key, ';
        }
        if ($this->privateKeyMissing) {
            $missing .= 'private key, ';
        }
        return rtrim($missing, ', ');
    }

    public function reset(): void
    {
        $this->encryptionKeyMissing = false;
        $this->passphraseMissing = false;
        $this->publicKeyMissing = false;
        $this->privateKeyMissing = false;
    }
}
