<?php

/**
 * CryptoGen class
 *
 * OpenEMR encryption/decryption strategy:
 * 1. Two separate private key sets are used, one key set in the database and one key set on the drive.
 * 2. The private database key set is stored in the keys mysql table
 * 3. The private drive key set is stored in sites/<site-name>/documents/logs_and_misc/methods/
 * 4. The private database key set is used when encrypting/decrypting data that is stored on the drive.
 * 5. The private drive key set is used when encrypting/decrypting data that is stored in the database.
 * 6. The private drive key set is encrypted by the private database key set
 * 7. Encryption/key versioning is used to support algorithm improvements while also ensuring
 *    backwards compatibility of decryption.
 * 8. To ensure performance, the CryptoGen class will cache the key sets that are used inside the object,
 *    which avoids numerous repeat calls to collect the key sets (and repeat decryption of the key set
 *    from the drive).
 * 9. There is also support for passphrase encryption/decryption (ie. no private keys are used).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ensoftek, Inc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2015 Ensoftek, Inc
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

use Exception;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Utils\RandomGenUtils;

class CryptoGen implements CryptoInterface
{
    /**
     * This is the current encrypt/decrypt version
     * (this will always be a three digit number that we will
     * increment when update the encrypt/decrypt methodology
     * which allows being able to maintain backward compatibility
     * to decrypt values from prior versions)
     * Remember to update cryptCheckStandard() and decryptStandard()
     * when increment this.
     */
    private string $encryptionVersion = "006";
    /**
     * This is the current key version. As above, will increment this
     * when update the encrypt/decrypt methodology to allow backward
     * compatibility.
     * Remember to update decryptStandard() when increment this.
     */
    private string $keyVersion = "six";

    /**
     * Key cache to optimize key collection, which avoids numerous repeat
     * calls to collect the key sets (and repeat decryption of the key set
     * from the drive).
     */
    private array $keyCache = [];

    /**
     * Encrypts data using the standard encryption method
     *
     * @param ?string $value           The data to encrypt
     * @param ?string $customPassword  If provided, keys will be derived from this password (standard keys will not be used)
     * @param string  $keySource       The source of the standard keys. Options are 'drive' and 'database'
     * @return string The encrypted data
     */
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive'): string
    {
        return $this->encryptionVersion . $this->coreEncrypt($value ?? '', $keySource, $this->keyVersion, $customPassword ?? '');
    }

    /**
     * Decrypts data using the standard decryption method
     *
     * @param ?string $value           The data to decrypt
     * @param ?string $customPassword  If provided, keys will be derived from this password (standard keys will not be used)
     * @param string  $keySource       The source of the standard keys. Options are 'drive' and 'database'
     * @param ?int    $minimumVersion  The minimum encryption version supported (useful when accepting encrypted data
     *                                 from outside OpenEMR to prevent bad actors from using older versions)
     * @return false|string The decrypted data, or false if decryption fails
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
    {
        if (empty($value)) {
            return "";
        }

        // Collect the encrypt/decrypt version and remove it from the value
        $encryptionVersion = intval(mb_substr($value, 0, 3, '8bit'));
        $trimmedValue = mb_substr($value, 3, null, '8bit');

        if (!empty($minimumVersion)) {
            if ($encryptionVersion < $minimumVersion) {
                error_log("OpenEMR Error : Decryption is not working because the encrypt/decrypt version is lower than allowed.");
                return false;
            }
        }

        $customPassword ??= '';

        // Map the encrypt/decrypt version to the correct decryption function
        try {
            return match ($encryptionVersion) {
                6 => $this->coreDecrypt($trimmedValue, $keySource, "six", $customPassword),
                5 => $this->coreDecrypt($trimmedValue, $keySource, "five", $customPassword),
                4 => $this->coreDecrypt($trimmedValue, $keySource, "four", $customPassword),
                2, 3 => $this->aes256DecryptTwo($trimmedValue, $customPassword),
                1 => $this->aes256DecryptOne($trimmedValue, $customPassword),
                default => (function () {
                    error_log("OpenEMR Error : Decryption is not working because of unknown encrypt/decrypt version.");
                    return false;
                })()
            };
        } catch (CryptoGenException $e) {
            // Log the exception message with call stack for debugging
            $stackTrace = debug_backtrace();
            $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
            error_log(errorLogEscape("OpenEMR Error: Decryption failed!") . "\n" . errorLogEscape($formattedStackTrace));
            return false;
        }
    }

    /**
     * Checks if a crypt block is valid for use with the standard method
     *
     * @param ?string $value The data to validate
     * @return bool True if valid, false otherwise
     */
    public function cryptCheckStandard(?string $value): bool
    {
        return preg_match('/^00[1-6]/', $value) === 1;
    }

    /**
     * Core encryption function
     *
     * @param string $value           Raw data to be encrypted
     * @param string $keySource       The source of the keys. Options are 'drive' and 'database'
     * @param string $keyVersion      The key number/version
     * @param string $customPassword  If empty, standard keys are used. If provided, keys are derived from this password
     * @return string The encrypted data
     * @throws CryptoGenException If encryption fails due to critical errors
     */
    private function coreEncrypt(string $value, string $keySource, string $keyVersion, string $customPassword = ''): string
    {
        if (!$this->isOpenSSLExtensionLoaded()) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because missing openssl extension.");
        }

        if ($customPassword === '') {
            // Collect the encryption keys. If they do not exist, then create them
            // The first key is for encryption. Then second key is for the HMAC hash
            $secretKey = $this->collectCryptoKey($keyVersion, "a", $keySource);
            $secretKeyHmac = $this->collectCryptoKey($keyVersion, "b", $keySource);
        } else {
            // customPassword mode, so turn the password into keys
            $salt = $this->getRandomBytes(32);
            if (empty($salt)) {
                throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
            }
            $preKey = hash_pbkdf2('sha384', $customPassword, $salt, 100000, 32, true);
            $secretKey = hash_hkdf('sha384', $preKey, 32, 'aes-256-encryption', $salt);
            $secretKeyHmac = hash_hkdf('sha384', $preKey, 32, 'sha-384-authentication', $salt);
        }

        if (empty($secretKey) || empty($secretKeyHmac)) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because key(s) is blank.");
        }

        $iv = $this->getRandomBytes($this->getOpenSSLCipherIvLength('aes-256-cbc'));
        if (empty($iv)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }

        $processedValue = empty($value) ? '' : $this->opensslEncrypt(
            $value,
            'aes-256-cbc',
            $secretKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmacHash = $this->hashHmac('sha384', $iv . $processedValue, $secretKeyHmac, true);

        if ($value !== "" && ($processedValue === "" || $hmacHash === "")) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working (encrypted value is blank or hmac hash is blank).");
        }

        $completedValue = $hmacHash . $iv . $processedValue;
        if (isset($salt)) {
            // customPassword mode, so prepend the completed value with the salt
            $completedValue = $salt . $completedValue;
        }

        return base64_encode($completedValue);
    }


    /**
     * Core decryption function
     *
     * @param string $value           Encrypted data to be decrypted
     * @param string $keySource       The source of the keys. Options are 'drive' and 'database'
     * @param string $keyVersion      The key number/version
     * @param string $customPassword  If empty, standard keys are used. If provided, keys are derived from this password
     * @return string The decrypted data, or false if decryption fails
     * @throws CryptoGenException If decryption fails due to critical errors
     */
    private function coreDecrypt(string $value, string $keySource, string $keyVersion, string $customPassword = ''): string
    {

        if (!$this->isOpenSSLExtensionLoaded()) {
            $errorMessage = "OpenEMR Error : Decryption is not working because missing openssl extension.";
            error_log($errorMessage);
            throw new CryptoGenException($errorMessage);
        }

        $raw = base64_decode($value, true);
        if ($raw === false) {
            $errorMessage = "OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.";
            error_log($errorMessage);
            throw new CryptoGenException($errorMessage);
        }

        if ($customPassword === '') {
            // Collect the encryption keys.
            // The first key is for encryption. Then second key is for the HMAC hash
            $secretKey = $this->collectCryptoKey($keyVersion, "a", $keySource);
            $secretKeyHmac = $this->collectCryptoKey($keyVersion, "b", $keySource);
        } else {
            // customPassword mode, so turn the password keys
            // The first key is for encryption. Then second key is for the HMAC hash
            // First need to collect the salt from $raw (and then remove it from $raw)
            $salt = mb_substr($raw, 0, 32, '8bit');
            $raw = mb_substr($raw, 32, null, '8bit');
            // Now turn the password into keys
            $preKey = $this->hashPbkdf2('sha384', $customPassword, $salt, 100000, 32, true);
            $secretKey = $this->hashHkdf('sha384', $preKey, 32, 'aes-256-encryption', $salt);
            $secretKeyHmac = $this->hashHkdf('sha384', $preKey, 32, 'sha-384-authentication', $salt);
        }

        if (empty($secretKey) || empty($secretKeyHmac)) {
            $errorMessage = "OpenEMR Error : Decryption is not working because key(s) is blank.";
            error_log($errorMessage);
            throw new CryptoGenException($errorMessage);
        }

        $ivLength = $this->getOpenSSLCipherIvLength('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 48, '8bit');
        $iv = mb_substr($raw, 48, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 48), null, '8bit');

        $calculatedHmacHash = $this->hashHmac('sha384', $iv . $encrypted_data, $secretKeyHmac, true);

        if ($this->hashEquals($hmacHash, $calculatedHmacHash)) {
            return $this->opensslDecrypt(
                $encrypted_data,
                'aes-256-cbc',
                $secretKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        }
        // Log the HMAC authentication failure with call stack for debugging
        $stackTrace = debug_backtrace();
        $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
        error_log(errorLogEscape("OpenEMR Error: Decryption failed HMAC Authentication!") . "\n" . errorLogEscape($formattedStackTrace));
        throw new CryptoGenException("OpenEMR Error : Decryption failed HMAC Authentication!");
    }

    /**
     * Format exception message with stack trace
     *
     * @param array $stackTrace Debug backtrace array
     * @return string Formatted stack trace string
     */
    private function formatExceptionMessage(array $stackTrace): string
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

    /**
     * Decrypts AES256 encrypted data using version 2 algorithm
     *
     * @param ?string $value           Data to decrypt
     * @param ?string $customPassword  If null, uses standard key. If provided, derives key from this password
     * @return false|string The decrypted data, or false if decryption fails
     */
    public function aes256DecryptTwo(?string $value, ?string $customPassword = null): false|string
    {
        if (!$this->isOpenSSLExtensionLoaded()) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            // Collect the encryption keys.
            // The first key is for encryption. Then second key is for the HMAC hash
            $secretKey = $this->collectCryptoKey("two", "a", "drive");
            $secretKeyHmac = $this->collectCryptoKey("two", "b", "drive");
        } else {
            // Turn the password into a hash(note use binary) to use as the keys
            $secretKey = $this->hash("sha256", $customPassword, true);
            $secretKeyHmac = $secretKey;
        }

        if (empty($secretKey) || empty($secretKeyHmac)) {
            error_log("OpenEMR Error : Decryption is not working because key(s) is blank.");
            return false;
        }

        $value ??= '';

        $raw = base64_decode($value, true);
        if ($raw === false) {
            error_log("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
            return false;
        }

        $ivLength = $this->getOpenSSLCipherIvLength('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 32, '8bit');
        $iv = mb_substr($raw, 32, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 32), null, '8bit');

        $calculatedHmacHash = $this->hashHmac('sha256', $iv . $encrypted_data, $secretKeyHmac, true);

        if ($this->hashEquals($hmacHash, $calculatedHmacHash)) {
            return $this->opensslDecrypt(
                $encrypted_data,
                'aes-256-cbc',
                $secretKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        }
        // Log the HMAC authentication failure with call stack for debugging
        $stackTrace = debug_backtrace();
        $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
        error_log(errorLogEscape("OpenEMR Error: Decryption failed HMAC Authentication!") . "\n" . errorLogEscape($formattedStackTrace));
        return false;
    }

    /**
     * Decrypts AES256 encrypted data using version 1 algorithm
     *
     * @param ?string $value           Data to decrypt
     * @param ?string $customPassword  If null, uses standard key. If provided, derives key from this password
     * @return false|string The decrypted data
     */
    public function aes256DecryptOne(?string $value, ?string $customPassword = null): false|string
    {
        if (!$this->isOpenSSLExtensionLoaded()) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        $secretKey = empty($customPassword)
            ? $this->collectCryptoKey("one", "", "drive")  // Collect the key. If it does not exist, then create it
            : $this->hash("sha256", $customPassword);      // Turn the password into a hash to use as the key

        if (empty($secretKey)) {
            error_log("OpenEMR Error : Decryption is not working because key is blank.");
            return false;
        }

        $value ??= '';
        $raw = base64_decode($value);
        $ivLength = $this->getOpenSSLCipherIvLength('aes-256-cbc');
        $iv = substr($raw, 0, $ivLength);
        $encrypted_data = substr($raw, $ivLength);

        return $this->opensslDecrypt(
            $encrypted_data,
            'aes-256-cbc',
            $secretKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Legacy decryption function using deprecated mcrypt
     * This function is only used for backward compatibility
     * TODO: Should be removed in the future
     *
     * @param string $value Encrypted data to decrypt
     * @return string Decrypted data
     */
    public function aes256Decrypt_mycrypt(string $value): string
    {
        if (!$this->isMcryptExtensionLoaded()) {
            throw new CryptoGenException('The obsolete mcrypt extension is required to decrypt legacy data');
        }
        $rawValue = base64_decode($value);
        $sSecretKey = $this->pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
        $ivSize = $this->mcryptGetIvSize();
        $ivValue = $this->mcryptCreateIv($ivSize);
        $data = $this->mcryptDecrypt($sSecretKey, $rawValue, $ivValue);
        return rtrim($data, "\0");
    }

    /**
     * Collect (and create, if needed) the standard keys
     * This mechanism will allow easy migration to new keys/ciphers in the future while
     * also maintaining backward compatibility of encrypted data.
     *
     * Note that to increase performance, it will store the key as a variable in this object in case
     * the key is used again (especially important when reading encrypted log entries where there
     * can be hundreds of decryption calls where it otherwise requires 5 steps to get the key; collect
     * key set from database, collect key set from drive, decrypt key set from drive using the database
     * key; caching the key will bypass all these steps).
     *
     * @param string $keyVersion  The key number/version
     * @param string $sub         The key sublabel
     * @param string $keySource   The source of the standard keys
     * @return string The key in raw form
     * @throws CryptoGenException If key collection fails due to critical errors
     */
    private function collectCryptoKey(string $keyVersion, string $sub, string $keySource): string
    {
        // Check if key is in the cache first (and return it if it is)
        $cacheLabel = $keyVersion . $sub . $keySource;
        if (!empty($this->keyCache[$cacheLabel])) {
            return $this->keyCache[$cacheLabel];
        }

        // Build the main label
        $label = $keyVersion . $sub;

        // If the key does not exist, then create it
        $key = ($keySource === "database")
            ? $this->collectDatabaseKey($label)
            : $this->collectDriveKey($label, $keyVersion);

        // Store key in cache and then return the key
        $this->keyCache[$cacheLabel] = $key;
        return $key;
    }

    /**
     * Collect key from database
     *
     * @param string $label Key label
     * @return string The key in raw form
     * @throws CryptoGenException If key creation fails
     */
    private function collectDatabaseKey(string $label): string
    {
        $sqlValue = $this->sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
        if (empty($sqlValue['value'])) {
            // Create a new key and place in database
            // Produce a 256bit key (32 bytes equals 256 bits)
            $newKey = $this->getRandomBytes(32);
            if (empty($newKey)) {
                throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
            }
            $this->sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)", [$label, base64_encode($newKey)]);
        }
        $sqlKey = $this->sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
        $key = base64_decode($sqlKey['value']);
        // Ensure have a key (if do not have key, then is critical error, and will exit)
        if (empty($key)) {
            throw new CryptoGenException("OpenEMR Error : Key creation in database is not working - Exiting.");
        }
        return $key;
    }

    /**
     * Creates a new encryption key file at the specified path.
     *
     * Generates a 256-bit (32-byte) cryptographically secure random key and saves it to the filesystem.
     * For older key versions (one through four), the key is stored as base64-encoded plaintext.
     * For newer versions, the key is encrypted using the standard encryption method before storage.
     *
     * @param string $keyPath     The file path where the key file should be created
     * @param string $keyVersion  The key version identifier that determines storage format
     * @throws CryptoGenException If random byte generation fails
     * @return string
     */
    private function createNewDriveKey(string $keyPath, string $keyVersion): string
    {
        $newKey = $this->getRandomBytes(32);
        if (empty($newKey)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }
        $usesLegacyStorage = in_array($keyVersion, ["one", "two", "three", "four"]);
        $fileContents = $usesLegacyStorage
            ? base64_encode($newKey) // older key versions that did not encrypt the key on the drive
            : $this->encryptStandard($newKey, null, 'database');

        $keyDirectory = dirname($keyPath);

        if ($this->filePutContents($keyPath, $fileContents) === false) {
            throw new CryptoGenException("Unable to create key in drive");
        }

        return $newKey;
    }

    /**
     * Collect key from drive
     *
     * @param string $label       Key label
     * @param string $keyVersion  Key version
     * @return string The key in raw form
     * @throws CryptoGenException If key creation fails
     */
    private function collectDriveKey(string $label, string $keyVersion): string
    {
        $keyPath = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label;
        if ($this->fileExists($keyPath)) {
            $fileContents = $this->fileGetContents($keyPath);
            $usesLegacyStorage = in_array($keyVersion, ["one", "two", "three", "four"]);
            $key = $usesLegacyStorage
                ? base64_decode(rtrim($fileContents)) // older key versions that did not encrypt the key on the drive
                : $this->decryptStandard($fileContents, null, 'database');
        } else {
            $key = $this->createNewDriveKey($keyPath, $keyVersion);
        }
        if (!empty($key)) {
            return $key;
        }
        if ($this->fileExists($keyPath)) {
            throw new CryptoGenException("OpenEMR Error : Key in drive is not compatible (ie. can not be decrypted) with key in database - Exiting.");
        }
        throw new CryptoGenException("OpenEMR Error : Key creation in drive is not working - Exiting.");
    }

    /**
     * Check if the OpenSSL extension is loaded.
     * This is a wrapper to enable better testing.
     *
     * @codeCoverageIgnore
     * @return bool
     */
    protected function isOpenSSLExtensionLoaded(): bool
    {
        return extension_loaded('openssl');
    }

    /**
     * Return random bytes.
     * This is a wrapper to enable better testing.
     *
     * @codeCoverageIgnore
     * @return string
     */
    protected function getRandomBytes(int $length): string
    {
        return RandomGenUtils::produceRandomBytes($length);
    }

    /**
     * Wrapper for hash_pbkdf2 to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $algo Algorithm to use for hashing
     * @param string $password The password to derive the key from
     * @param string $salt The salt to use for derivation
     * @param int $iterations Number of iterations
     * @param int $length Length of output key
     * @param bool $rawOutput Whether to return raw binary data
     * @return string The derived key
     */
    protected function hashPbkdf2(string $algo, string $password, string $salt, int $iterations, int $length, bool $rawOutput): string
    {
        return hash_pbkdf2($algo, $password, $salt, $iterations, $length, $rawOutput);
    }

    /**
     * Wrapper for hash_hkdf to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $algo Algorithm to use for hashing
     * @param string $key Input key material
     * @param int $length Length of output key
     * @param string $info Optional context and application specific information
     * @param string $salt Optional salt value
     * @return string The derived key
     */
    protected function hashHkdf(string $algo, string $key, int $length, string $info, string $salt): string
    {
        return hash_hkdf($algo, $key, $length, $info, $salt);
    }

    /**
     * Wrapper for openssl_cipher_iv_length to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $cipher The cipher method
     * @return int The length of the IV for the given cipher
     */
    protected function getOpenSSLCipherIvLength(string $cipher): int
    {
        return openssl_cipher_iv_length($cipher);
    }

    /**
     * Wrapper for openssl_encrypt to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $data The data to encrypt
     * @param string $cipher The cipher method
     * @param string $key The encryption key
     * @param int $options Options for the encryption
     * @param string $iv The initialization vector
     * @return string The encrypted data
     */
    protected function opensslEncrypt(string $data, string $cipher, string $key, int $options, string $iv): string
    {
        return openssl_encrypt($data, $cipher, $key, $options, $iv);
    }

    /**
     * Wrapper for openssl_decrypt to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $data The data to decrypt
     * @param string $cipher The cipher method
     * @param string $key The decryption key
     * @param int $options Options for the decryption
     * @param string $iv The initialization vector
     * @return false|string The decrypted data or false on failure
     */
    protected function opensslDecrypt(string $data, string $cipher, string $key, int $options, string $iv): false|string
    {
        return openssl_decrypt($data, $cipher, $key, $options, $iv);
    }

    /**
     * Wrapper for hash_hmac to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $algo The hashing algorithm
     * @param string $data The data to hash
     * @param string $key The key for HMAC
     * @param bool $binary Whether to return raw binary data
     * @return string The HMAC hash
     */
    protected function hashHmac(string $algo, string $data, string $key, bool $binary): string
    {
        return hash_hmac($algo, $data, $key, $binary);
    }

    /**
     * Wrapper for hash_equals to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $knownString The known string
     * @param string $userString The user-provided string
     * @return bool True if strings match, false otherwise
     */
    protected function hashEquals(string $knownString, string $userString): bool
    {
        return hash_equals($knownString, $userString);
    }

    /**
     * Wrapper for hash to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $algo The hashing algorithm
     * @param string $data The data to hash
     * @param bool $binary Whether to return raw binary data
     * @return string The hash
     */
    protected function hash(string $algo, string $data, bool $binary = false): string
    {
        return hash($algo, $data, $binary);
    }

    /**
     * Wrapper for file_put_contents to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $filename The file to write to
     * @param mixed $data The data to write
     * @param int $flags Optional flags
     * @param resource|null $context Optional context
     * @return int|false The number of bytes written or false on failure
     */
    protected function filePutContents(string $filename, mixed $data, int $flags = 0, $context = null): int|false
    {
        return file_put_contents($filename, $data, $flags, $context);
    }

    /**
     * Wrapper for file_get_contents to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $filename The file to read from
     * @param bool $useIncludePath Whether to search for the file in the include path
     * @param resource|null $context Optional context
     * @param int $offset The offset where reading starts
     * @param int|null $length Maximum length of data read
     * @return string|false The file contents or false on failure
     */
    protected function fileGetContents(string $filename, bool $useIncludePath = false, $context = null, int $offset = 0, ?int $length = null): string|false
    {
        return file_get_contents($filename, $useIncludePath, $context, $offset, $length);
    }

    /**
     * Wrapper for pack to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $format The format string
     * @param mixed ...$values The values to pack
     * @return string The packed binary string
     */
    protected function pack(string $format, mixed ...$values): string
    {
        return pack($format, ...$values);
    }

    /**
     * Wrapper for mcrypt_decrypt to enable better testing.
     * Since mcrypt is obsolete already, we avoid workarounds
     * for missing constants by hard-coding some of the arguments.
     *
     * @codeCoverageIgnore
     * @param string $key The encryption key
     * @param string $data The data to decrypt
     * @param string $iv The initialization vector
     * @return string The decrypted data
     */
    protected function mcryptDecrypt(string $key, string $data, string $iv): string
    {
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv);
    }

    /**
     * Wrapper for mcrypt_create_iv to enable better testing.
     * Since mcrypt is obsolete already, we avoid workarounds
     * for missing constants by hard-coding MCRYPT_RAND.
     *
     * @codeCoverageIgnore
     * @param int $size The size of the IV
     * @return string The initialization vector
     */
    protected function mcryptCreateIv(int $size): string
    {
        return mcrypt_create_iv($size, MCRYPT_RAND);
    }

    /**
     * Wrapper for mcrypt_get_iv_size to enable better testing.
     * Since mcrypt is obsolete already, we avoid having workarounds
     * for missing constants by hard-coding the arguments.
     *
     * @codeCoverageIgnore
     * @return int The IV size for the given cipher and mode
     */
    protected function mcryptGetIvSize(): int
    {
        return mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    }

    /**
     * Check if the mcrypt extension is loaded.
     * This is a wrapper to enable better testing.
     *
     * @codeCoverageIgnore
     * @return bool
     */
    protected function isMcryptExtensionLoaded(): bool
    {
        return extension_loaded('mcrypt');
    }

    /**
     * Wrapper for sqlQueryNoLog to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $statement The SQL query statement
     * @param array $binds The parameter bindings for the query
     * @return array|false The query result array or false on failure
     */
    protected function sqlQueryNoLog(string $statement, array $binds = []): array|false
    {
        return sqlQueryNoLog($statement, $binds);
    }

    /**
     * Wrapper for sqlStatementNoLog to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $statement The SQL statement to execute
     * @param array $binds The parameter bindings for the statement
     * @return mixed The result of the SQL statement execution
     */
    protected function sqlStatementNoLog(string $statement, array $binds = []): mixed
    {
        return sqlStatementNoLog($statement, $binds);
    }

    /**
     * Wrapper for file_exists to enable better testing.
     *
     * @codeCoverageIgnore
     * @param string $filename The file name to check
     * @return bool True if the file exists, false otherwise
     */
    protected function fileExists(string $filename): bool
    {
        return file_exists($filename);
    }
}
