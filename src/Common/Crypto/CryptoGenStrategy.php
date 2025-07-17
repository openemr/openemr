<?php

/**
 * CryptoGenStrategy - Default encryption strategy implementation.
 *
 * Implements AES-256-CBC encryption with HMAC-SHA384 authentication.
 * Supports both standard key-based encryption and custom password-based encryption.
 * Uses PBKDF2 + HKDF for password-based key derivation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

use Exception;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\EncryptionStrategyInterface;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Common\Logging\SystemLogger;

/**
 * Default encryption strategy using AES-256-CBC with HMAC-SHA384.
 *
 * This strategy provides secure encryption with:
 * - AES-256-CBC for data encryption
 * - HMAC-SHA384 for authentication
 * - PBKDF2 + HKDF for password-based key derivation
 * - Support for multiple key versions for backward compatibility
 */
class CryptoGenStrategy implements EncryptionStrategyInterface
{
    # This is the current encrypt/decrypt version
    # (this will always be a three digit number that we will
    # increment when updating the encrypt/decrypt methodology
    # which allows being able to maintain backward compatibility
    # to decrypt values from prior versions)
    # Remember to update cryptCheckStandard() and decryptStandard()
    # when incrementing this.
    private string $encryptionVersion = "006";
    private string $keyVersion = "six";
    private array $keyCache = [];
    private SystemLogger $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->logger->debug("CryptoGenStrategy: Initialized", [
            'encryption_version' => $this->encryptionVersion,
            'key_version' => $this->keyVersion
        ]);
    }

    /**
     * Encrypt data using the current encryption version (see $encryptionVersion).
     *
     * @param string|null $value The data to encrypt
     * @param string|null $customPassword If provided, derives keys from password instead of standard keys
     * @param string $keySource Source for standard keys ('drive' or 'database')
     * @return string|null Encrypted data prefixed with $encryptionVersion, using $keyVersion for key selection, or null if input is null
     */
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
    {
        $this->logger->debug("CryptoGenStrategy: encryptStandard called", [
            'has_custom_password' => !is_null($customPassword),
            'key_source' => $keySource,
            'key_version' => $this->keyVersion
        ]);

        $result = $this->encryptionVersion . $this->coreEncrypt($value, $customPassword, $keySource, $this->keyVersion);

        $this->logger->debug("CryptoGenStrategy: encryptStandard completed");

        return $result;
    }

    /**
     * Decrypt data encrypted with any supported encryption version.
     *
     * @param string|null $value The encrypted data to decrypt
     * @param string|null $customPassword If provided, derives keys from password instead of standard keys
     * @param string $keySource Source for standard keys ('drive' or 'database')
     * @param int|null $minimumVersion Minimum encryption version required (for security validation)
     * @return false|string Decrypted data or false on failure
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
    {
        if (empty($value)) {
            return "";
        }

        $encryptionVersion = intval(mb_substr($value, 0, 3, '8bit'));
        $trimmedValue = mb_substr($value, 3, null, '8bit');

        if (!empty($minimumVersion) && ($encryptionVersion < $minimumVersion)) {
            error_log("OpenEMR Error : Decryption is not working because the encrypt/decrypt version is lower than allowed.");
            return false;
        }

        return match ($encryptionVersion) {
            6 => $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "six"),
            5 => $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "five"),
            4 => $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "four"),
            2, 3 => $this->aes256DecryptTwo($trimmedValue, $customPassword),
            1 => $this->aes256DecryptOne($trimmedValue, $customPassword),
            default => (function () {
                error_log("OpenEMR Error : Decryption is not working because of unknown encrypt/decrypt version.");
                return false;
            })(),
        };
    }

    /**
     * Check if a value was encrypted using a supported encryption version.
     *
     * @param string|null $value The value to check
     * @return bool True if the value has a valid encryption version prefix (001-006)
     */
    public function cryptCheckStandard(?string $value): bool
    {
        return !empty($value) && preg_match('/^00[1-6]/', $value);
    }

    private function coreEncrypt(?string $sValue, ?string $customPassword = null, string $keySource = 'drive', ?string $keyNumber = null): string
    {
        $keyNumber = isset($keyNumber) ? $keyNumber : $this->keyVersion;

        $this->validateOpenSSLExtension();

        [$sSecretKey, $sSecretKeyHmac, $sSalt] = $this->deriveKeys($customPassword, $keyNumber, $keySource);
        $this->validateKeys($sSecretKey, $sSecretKeyHmac);

        $iv = RandomGenUtils::produceRandomBytes(openssl_cipher_iv_length('aes-256-cbc'));
        if (empty($iv)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }

        $processedValue = openssl_encrypt(
            $sValue,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmacHash = hash_hmac('sha384', $iv . $processedValue, $sSecretKeyHmac, true);

        if ($sValue !== "" && ($processedValue === false || $hmacHash === "")) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working (encrypted value is blank or hmac hash is blank).");
        }

        $completedValue = $hmacHash . $iv . $processedValue;
        if (!empty($customPassword)) {
            $completedValue = $sSalt . $completedValue;
        }

        return base64_encode($completedValue);
    }

    private function coreDecrypt(string $sValue, ?string $customPassword = null, string $keySource = 'drive', ?string $keyNumber = null): false|string
    {
        $keyNumber = isset($keyNumber) ? $keyNumber : $this->keyVersion;

        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        $raw = base64_decode($sValue, true);
        if ($raw === false) {
            error_log("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
            return false;
        }

        if (empty($customPassword)) {
            $sSecretKey = $this->collectCryptoKey($keyNumber, "a", $keySource);
            $sSecretKeyHmac = $this->collectCryptoKey($keyNumber, "b", $keySource);
        } else {
            $sSalt = mb_substr($raw, 0, 32, '8bit');
            $raw = mb_substr($raw, 32, null, '8bit');
            $sPreKey = hash_pbkdf2('sha384', $customPassword, $sSalt, 100000, 32, true);
            $sSecretKey = hash_hkdf('sha384', $sPreKey, 32, 'aes-256-encryption', $sSalt);
            $sSecretKeyHmac = hash_hkdf('sha384', $sPreKey, 32, 'sha-384-authentication', $sSalt);
        }

        if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
            error_log("OpenEMR Error : Decryption is not working because key(s) is blank.");
            return false;
        }

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 48, '8bit');
        $iv = mb_substr($raw, 48, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 48), null, '8bit');

        $calculatedHmacHash = hash_hmac('sha384', $iv . $encrypted_data, $sSecretKeyHmac, true);

        if (hash_equals($hmacHash, $calculatedHmacHash)) {
            return openssl_decrypt(
                $encrypted_data,
                'aes-256-cbc',
                $sSecretKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        } else {
            try {
                throw new Exception("OpenEMR Error: Decryption failed HMAC Authentication!");
            } catch (Exception $e) {
                $stackTrace = debug_backtrace();
                $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
                error_log(errorLogEscape($e->getMessage()) . "\n" . errorLogEscape($formattedStackTrace));
                return false;
            }
        }
    }

    private function formatExceptionMessage($stackTrace): string
    {
        $formattedStackTrace = "Possibly Config Password or Token. Error Call Stack:\n";
        foreach ($stackTrace as $index => $call) {
            $formattedStackTrace .= "#" . $index . " ";
            if (isset($call['file'])) {
                $formattedStackTrace .= $call['file'] . " ";
                if (isset($call['line'])) {
                    $formattedStackTrace .= "(" . $call['line'] . "): ";
                }
            }
            if (isset($call['class'])) {
                $formattedStackTrace .= $call['class'] . $call['type'];
            }
            if (isset($call['function'])) {
                $formattedStackTrace .= $call['function'] . "()\n";
            }
        }
        return $formattedStackTrace;
    }

    public function aes256DecryptTwo(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            $sSecretKey = $this->collectCryptoKey("two", "a");
            $sSecretKeyHmac = $this->collectCryptoKey("two", "b");
        } else {
            $sSecretKey = hash("sha256", $customPassword, true);
            $sSecretKeyHmac = $sSecretKey;
        }

        if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
            error_log("OpenEMR Error : Decryption is not working because key(s) is blank.");
            return false;
        }

        $raw = base64_decode($sValue, true);
        if ($raw === false) {
            error_log("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
            return false;
        }

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 32, '8bit');
        $iv = mb_substr($raw, 32, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 32), null, '8bit');

        $calculatedHmacHash = hash_hmac('sha256', $iv . $encrypted_data, $sSecretKeyHmac, true);

        if (hash_equals($hmacHash, $calculatedHmacHash)) {
            return openssl_decrypt(
                $encrypted_data,
                'aes-256-cbc',
                $sSecretKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        } else {
            try {
                throw new Exception("OpenEMR Error: Decryption failed hmac authentication!");
            } catch (Exception $e) {
                $stackTrace = debug_backtrace();
                $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
                error_log(errorLogEscape($e->getMessage()) . "\n" . errorLogEscape($formattedStackTrace));
                return false;
            }
        }
    }

    public function aes256DecryptOne(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            $sSecretKey = $this->collectCryptoKey();
        } else {
            $sSecretKey = hash("sha256", $customPassword);
        }

        if (empty($sSecretKey)) {
            error_log("OpenEMR Error : Decryption is not working because key is blank.");
            return false;
        }

        $raw = base64_decode($sValue);

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');

        $iv = substr($raw, 0, $ivLength);
        $encrypted_data = substr($raw, $ivLength);

        return openssl_decrypt(
            $encrypted_data,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Validate that the OpenSSL extension is loaded.
     *
     * @throws CryptoGenException If OpenSSL extension is not available
     */
    private function validateOpenSSLExtension(): void
    {
        if (!extension_loaded('openssl')) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because missing openssl extension.");
        }
    }

    /**
     * Derive encryption and HMAC keys from either stored keys or custom password.
     *
     * @param string|null $customPassword If provided, uses PBKDF2+HKDF for key derivation
     * @param string $keyNumber The key version to use (e.g., 'six', 'five')
     * @param string $keySource Source for standard keys ('drive' or 'database')
     * @return array [encryptionKey, hmacKey, salt] - salt is null for standard keys
     */
    private function deriveKeys(?string $customPassword, string $keyNumber, string $keySource): array
    {
        if (empty($customPassword)) {
            return [
                $this->collectCryptoKey($keyNumber, "a", $keySource),
                $this->collectCryptoKey($keyNumber, "b", $keySource),
                null
            ];
        }

        $sSalt = RandomGenUtils::produceRandomBytes(32);
        if (empty($sSalt)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }

        $sPreKey = hash_pbkdf2('sha384', $customPassword, $sSalt, 100000, 32, true);
        return [
            hash_hkdf('sha384', $sPreKey, 32, 'aes-256-encryption', $sSalt),
            hash_hkdf('sha384', $sPreKey, 32, 'sha-384-authentication', $sSalt),
            $sSalt
        ];
    }

    /**
     * Validate that encryption keys are not empty.
     *
     * @param string $secretKey The encryption key
     * @param string $secretKeyHmac The HMAC key
     * @throws CryptoGenException If either key is empty
     */
    private function validateKeys(string $secretKey, string $secretKeyHmac): void
    {
        if (empty($secretKey) || empty($secretKeyHmac)) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because key(s) is blank.");
        }
    }

    private function collectCryptoKey(string $version = "one", string $sub = "", string $keySource = 'drive'): string
    {
        $cacheLabel = $version . $sub . $keySource;
        if (!empty($this->keyCache[$cacheLabel])) {
            return $this->keyCache[$cacheLabel];
        }

        $label = $version . $sub;

        if ($keySource == 'database') {
            $sqlValue = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
            if (empty($sqlValue['value'])) {
                $newKey = RandomGenUtils::produceRandomBytes(32);
                if (empty($newKey)) {
                    throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
                }
                sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)", [$label, base64_encode($newKey)]);
            }
        } else {
            if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
                $newKey = RandomGenUtils::produceRandomBytes(32);
                if (empty($newKey)) {
                    throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
                }
                if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                    file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, base64_encode($newKey));
                } else {
                    // For newer key versions, we need to encrypt using database keys
                    // Create a temporary instance to handle this without circular dependency
                    $tempStrategy = new self();
                    $encryptedKey = $tempStrategy->encryptUsingDatabaseKeys($newKey, $version);
                    file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, $encryptedKey);
                }
            }
        }

        if ($keySource == 'database') {
            $sqlKey = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
            $key = base64_decode($sqlKey['value']);
        } else {
            if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                $key = base64_decode(rtrim(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)));
            } else {
                $key = $this->decryptUsingDatabaseKeys(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label), $version);
            }
        }

        if (empty($key)) {
            if ($keySource == 'database') {
                throw new CryptoGenException("OpenEMR Error : Key creation in database is not working - Exiting.");
            } else {
                if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
                    throw new CryptoGenException("OpenEMR Error : Key creation in drive is not working - Exiting.");
                } else {
                    throw new CryptoGenException("OpenEMR Error : Key in drive is not compatible (ie. can not be decrypted) with key in database - Exiting.");
                }
            }
        }

        $this->keyCache[$cacheLabel] = $key;
        return $key;
    }

    private function encryptUsingDatabaseKeys(string $value, string $keyNumber): string
    {
        $sSecretKey = $this->collectCryptoKey($keyNumber, "a", 'database');
        $sSecretKeyHmac = $this->collectCryptoKey($keyNumber, "b", 'database');

        $iv = RandomGenUtils::produceRandomBytes(openssl_cipher_iv_length('aes-256-cbc'));
        if (empty($iv)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }

        $processedValue = openssl_encrypt(
            $value,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmacHash = hash_hmac('sha384', $iv . $processedValue, $sSecretKeyHmac, true);
        $completedValue = $hmacHash . $iv . $processedValue;

        return $this->encryptionVersion . base64_encode($completedValue);
    }

    private function decryptUsingDatabaseKeys(string $value, string $keyNumber): string
    {
        $encryptionVersion = intval(mb_substr($value, 0, 3, '8bit'));
        $trimmedValue = mb_substr($value, 3, null, '8bit');

        if ($encryptionVersion != 6) {
            throw new CryptoGenException("OpenEMR Error : Unexpected encryption version for database key decryption: " . $encryptionVersion);
        }

        $sSecretKey = $this->collectCryptoKey($keyNumber, "a", 'database');
        $sSecretKeyHmac = $this->collectCryptoKey($keyNumber, "b", 'database');

        $raw = base64_decode($trimmedValue, true);
        if ($raw === false) {
            throw new CryptoGenException("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
        }

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 48, '8bit');
        $iv = mb_substr($raw, 48, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 48), null, '8bit');

        $calculatedHmacHash = hash_hmac('sha384', $iv . $encrypted_data, $sSecretKeyHmac, true);

        if (!hash_equals($hmacHash, $calculatedHmacHash)) {
            throw new CryptoGenException("OpenEMR Error: Database key decryption failed HMAC Authentication!");
        }

        return openssl_decrypt(
            $encrypted_data,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Serialize the strategy for database storage.
     *
     * @return string Serialized strategy data
     */
    public function serialize(): string
    {
        return serialize([
            'encryptionVersion' => $this->encryptionVersion,
            'keyVersion' => $this->keyVersion
        ]);
    }

    /**
     * Unserialize the strategy from database storage.
     *
     * @param string $data Serialized strategy data
     */
    public function unserialize(string $data): void
    {
        $serializedData = unserialize($data);

        $this->encryptionVersion = $serializedData['encryptionVersion'] ?? '006';
        $this->keyVersion = $serializedData['keyVersion'] ?? 'six';
        $this->keyCache = [];
        $this->logger = new SystemLogger();
    }

    /**
     * Modern PHP serialization method.
     *
     * @return array Data to serialize
     */
    public function __serialize(): array
    {
        return [
            'encryptionVersion' => $this->encryptionVersion,
            'keyVersion' => $this->keyVersion
        ];
    }

    /**
     * Modern PHP unserialization method.
     *
     * @param array $data Serialized data
     */
    public function __unserialize(array $data): void
    {
        $this->encryptionVersion = $data['encryptionVersion'] ?? '006';
        $this->keyVersion = $data['keyVersion'] ?? 'six';
        $this->keyCache = [];
        $this->logger = new SystemLogger();
    }
}
