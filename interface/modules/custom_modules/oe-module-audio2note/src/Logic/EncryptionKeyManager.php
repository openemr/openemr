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

class EncryptionKeyManager
{
    private $keyFilePath;
    private $keyDir;

    public function __construct()
    {
        // Define the path to the module's root directory.
        // __DIR__ is Logic, up 1 is src, up 1 is openemrAudio2Note module root.
        $moduleRoot = dirname(__DIR__, 2);
        $this->keyDir = $moduleRoot . '/config';
        $this->keyFilePath = $this->keyDir . '/secret.key';
    }

    /**
     * Ensures the master encryption key exists, generating it if necessary.
     * Returns the key.
     *
     * @return string|false The encryption key, or false on failure.
     */
    public function getKey(): string|false
    {
        if (!file_exists($this->keyFilePath)) {
            return $this->generateAndStoreKey();
        }

        $key = file_get_contents($this->keyFilePath);
        if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            error_log("OpenemrAudio2Note EncryptionKeyManager: Invalid or unreadable key file at: " . $this->keyFilePath . ". Attempting to regenerate.");
            return $this->generateAndStoreKey();
        }
        return $key;
    }

    /**
     * Generates a new encryption key and stores it in the key file.
     *
     * @return string|false The new encryption key, or false on failure.
     */
    private function generateAndStoreKey(): string|false
    {
        // error_log("OpenemrAudio2Note EncryptionKeyManager: Generating new encryption key."); // Informative, but can be noisy
        try {
            if (!is_dir($this->keyDir)) {
                if (!mkdir($this->keyDir, 0750, true)) {
                    error_log("OpenemrAudio2Note EncryptionKeyManager: CRITICAL - Failed to create config directory: " . $this->keyDir);
                    return false;
                }
                chmod($this->keyDir, 0750); // Ensure permissions
                // error_log("OpenemrAudio2Note EncryptionKeyManager: Created config directory: " . $this->keyDir);
            }

            $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
            if (file_put_contents($this->keyFilePath, $key) === false) {
                error_log("OpenemrAudio2Note EncryptionKeyManager: CRITICAL - Failed to write key to file: " . $this->keyFilePath);
                return false;
            }

            if (!chmod($this->keyFilePath, 0600)) {
                error_log("OpenemrAudio2Note EncryptionKeyManager: WARNING - Failed to set restrictive permissions (0600) on key file: " . $this->keyFilePath . ". Please check file ownership and permissions.");
            }
            // error_log("OpenemrAudio2Note EncryptionKeyManager: New encryption key generated and stored successfully.");
            return $key;
        } catch (\Exception $e) {
            error_log("OpenemrAudio2Note EncryptionKeyManager: CRITICAL - Exception during key generation/storage: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets the directory path where the key is stored.
     * Used for deployment script to set permissions.
     * @return string
     */
    public function getKeyDirectory(): string
    {
        return $this->keyDir;
    }
}