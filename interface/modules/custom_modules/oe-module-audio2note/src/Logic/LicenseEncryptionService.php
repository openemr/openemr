<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\OpenemrAudio2Note\Logic;

use OpenEMR\Common\System\System;

class LicenseEncryptionService
{
    // Defines the path to the master encryption key file.
    // Assumes this 'Logic' directory is within 'src', and 'config' is a sibling to 'src'.
    private const KEY_FILE = __DIR__ . '/../../config/secret.key';
    private ?string $masterKey = null;

    public function __construct()
    {
        $this->loadMasterKey();
    }

    private function loadMasterKey(): void
    {
        if (file_exists(self::KEY_FILE)) {
            $this->masterKey = file_get_contents(self::KEY_FILE);
        } else {
            System::logError("Audio2Note LicenseEncryptionService: Master key file not found at " . self::KEY_FILE . ". Generating new key.");
            $this->generateMasterKey();
        }

        if ($this->masterKey === false || strlen($this->masterKey) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
             System::logError("Audio2Note LicenseEncryptionService: Invalid or unreadable master encryption key file. Regenerating.");
             $this->generateMasterKey();
        }
    }

    private function generateMasterKey(): void
    {
        try {
            $this->masterKey = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
            $configDir = dirname(self::KEY_FILE);

            if (!is_dir($configDir)) {
                if (!mkdir($configDir, 0700, true)) { // Create with restrictive permissions.
                    System::logError("Audio2Note LicenseEncryptionService: CRITICAL - Failed to create config directory: " . $configDir);
                    $this->masterKey = null; // Invalidate key
                    return;
                }
            }

            if (file_put_contents(self::KEY_FILE, $this->masterKey) === false) {
                System::logError("Audio2Note LicenseEncryptionService: CRITICAL - Could not write master encryption key file to " . self::KEY_FILE);
                $this->masterKey = null; // Invalidate key if saving failed.
            } else {
                // Set strict permissions on the key file (owner read/write only).
                if (!chmod(self::KEY_FILE, 0600)) {
                    System::logError("Audio2Note LicenseEncryptionService: WARNING - Failed to set restrictive permissions (0600) on key file: " . self::KEY_FILE);
                }
            }
        } catch (\Exception $e) {
            System::logError("Audio2Note LicenseEncryptionService: CRITICAL - Exception during master key generation: " . $e->getMessage());
            $this->masterKey = null;
        }
    }

    private function getKey(): ?string
    {
        return $this->masterKey;
    }

    public function encrypt(string $data): ?string
    {
        $key = $this->getKey();
        if ($key === null) {
            System::logError("Audio2Note LicenseEncryptionService: Encryption failed. Master key not available.");
            return null;
        }

        try {
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $encrypted = sodium_crypto_secretbox($data, $nonce, $key);

            if ($encrypted === false) {
                 System::logError("Audio2Note LicenseEncryptionService: sodium_crypto_secretbox encryption failed.");
                 return null;
            }
            // Prepend nonce to the ciphertext for decryption.
            return base64_encode($nonce . $encrypted);
        } catch (\Exception $e) {
            System::logError("Audio2Note LicenseEncryptionService: Encryption exception: " . $e->getMessage());
            return null;
        }
    }

    public function decrypt(string $encryptedData): ?string
    {
        $key = $this->getKey();
        if ($key === null) {
            System::logError("Audio2Note LicenseEncryptionService: Decryption failed. Master key not available.");
            return null;
        }

        $decoded = base64_decode($encryptedData);
        if ($decoded === false) {
             // System::logError("Audio2Note LicenseEncryptionService: Decryption failed. Base64 decode failed."); // Can be noisy
             return null;
        }

        // Check if decoded data is long enough to contain nonce and MAC.
        if (strlen($decoded) < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
             // System::logError("Audio2Note LicenseEncryptionService: Decryption failed. Encrypted data too short."); // Can be noisy
             return null;
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        if ($ciphertext === false || $ciphertext === '') {
            // System::logError("Audio2Note LicenseEncryptionService: Decryption failed. Ciphertext is empty."); // Can be noisy
            return null;
        }

        $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($decrypted === false) {
            // This is an expected failure if data is tampered or key is wrong.
            // System::logError("Audio2Note LicenseEncryptionService: sodium_crypto_secretbox_open decryption failed. Data may be tampered or key is incorrect.");
            return null;
        }

        return $decrypted;
    }
}