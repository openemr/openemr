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

class EncryptionService
{
    private $encryptionKeyManager;

    public function __construct(EncryptionKeyManager $encryptionKeyManager = null)
    {
        $this->encryptionKeyManager = $encryptionKeyManager ?? new EncryptionKeyManager();
    }

    /**
     * Encrypts data using sodium_crypto_secretbox.
     * The nonce is prepended to the ciphertext.
     *
     * @param string $data The plaintext data to encrypt.
     * @return string|false The base64 encoded nonce+ciphertext, or false on failure.
     */
    public function encrypt(string $data): string|false
    {
        $key = $this->encryptionKeyManager->getKey();
        if ($key === false) {
            error_log("OpenemrAudio2Note EncryptionService: CRITICAL - Failed to retrieve encryption key for encryption.");
            return false;
        }

        try {
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = sodium_crypto_secretbox($data, $nonce, $key);
            return base64_encode($nonce . $ciphertext);
        } catch (\Exception $e) {
            error_log("OpenemrAudio2Note EncryptionService: Encryption failed - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrypts data encrypted with sodium_crypto_secretbox.
     * Expects the nonce to be prepended to the ciphertext.
     *
     * @param string $base64EncryptedDataWithNonce The base64 encoded nonce+ciphertext.
     * @return string|false The decrypted plaintext data, or false on failure (e.g., bad key, tampered).
     */
    public function decrypt(string $base64EncryptedDataWithNonce): string|false
    {
        $key = $this->encryptionKeyManager->getKey();
        if ($key === false) {
            error_log("OpenemrAudio2Note EncryptionService: CRITICAL - Failed to retrieve encryption key for decryption.");
            return false;
        }

        $decodedData = base64_decode($base64EncryptedDataWithNonce, true);
        if ($decodedData === false) {
            // error_log("OpenemrAudio2Note EncryptionService: Failed to base64 decode data for decryption."); // Potentially noisy if invalid data is common
            return false;
        }

        if (strlen($decodedData) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            // error_log("OpenemrAudio2Note EncryptionService: Encrypted data is too short to contain a nonce."); // Potentially noisy
            return false;
        }

        $nonce = substr($decodedData, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decodedData, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        if ($ciphertext === false || $ciphertext === '') {
            // error_log("OpenemrAudio2Note EncryptionService: Ciphertext is empty after extracting nonce."); // Potentially noisy
            return false;
        }

        try {
            $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
            if ($decrypted === false) {
                // This is an expected failure case for bad key or tampered message, so a simple log might be too noisy.
                // Consider logging only if debugging is enabled or if this indicates a systemic issue.
                // error_log("OpenemrAudio2Note EncryptionService: Decryption failed (e.g., bad key, tampered message).");
            }
            return $decrypted;
        } catch (\Exception $e) {
            error_log("OpenemrAudio2Note EncryptionService: Decryption exception - " . $e->getMessage());
            return false;
        }
    }
}