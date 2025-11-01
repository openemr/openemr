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
use OpenEMR\Common\Utils\RandomGenUtils;

class CryptoGen implements CryptoInterface
{
    /**
     * This is the current key version. When updating
     * the encrypt/decrypt methodology, add a new value
     * to the enum and increment this const to enable
     * backwards compatibility.
     */
    public const CURRENT_KEY_VERSION = KeyVersion::SEVEN;

    /**
     * Key cache to optimize key collection, which avoids numerous repeat
     * calls to collect the key sets (and repeat decryption of the key set
     * from the drive).
     */
    private array $keyCache = [];

    /**
     * Encrypts data using the standard encryption method
     *
     * @param  ?string $value          The data to encrypt
     * @param  ?string $customPassword If provided, keys will be derived from this password (standard keys will not be used)
     * @param  string  $keySource      The source of the standard keys. Options are 'drive' and 'database'
     * @return string The encrypted data
     */
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive'): string
    {
        return self::CURRENT_KEY_VERSION->toPaddedString() . $this->coreEncrypt($value, $customPassword, KeySource::from($keySource), self::CURRENT_KEY_VERSION);
    }

    /**
     * Decrypts data using the standard decryption method
     *
     * @param  ?string $value          The data to decrypt
     * @param  ?string $customPassword If provided, keys will be derived from this password (standard keys will not be used)
     * @param  string  $keySource      The source of the standard keys. Options are 'drive' and 'database'
     * @param  ?int    $minimumVersion The minimum encryption version supported (useful when accepting encrypted data
     *                                 from outside OpenEMR to prevent bad actors from using older versions)
     * @return false|string The decrypted data, or false if decryption fails
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
    {
        if (empty($value)) {
            return "";
        }

        // Collect the encrypt/decrypt version and remove it from the value
        try {
            $encryptionVersion = KeyVersion::fromPrefix($value);
        } catch (\ValueError) {
            error_log("Invalid encryption version prefix");
            return false;
        }
        $trimmedValue = mb_substr($value, 3, null, '8bit');

        if (!empty($minimumVersion)) {
            try {
                $minimumKeyVersion = KeyVersion::from($minimumVersion);
            } catch (\ValueError) {
                error_log("Invalid minimum key version {$minimumVersion}");
                return false;
            }
            if ($encryptionVersion->value < $minimumKeyVersion->value) {
                error_log("OpenEMR Error : Decryption is not working because the encrypt/decrypt version is lower than allowed.");
                return false;
            }
        }

        $keySourceEnum = KeySource::from($keySource);

        // Map the encrypt/decrypt version to the correct decryption function
        return ($encryptionVersion->usesLegacyDecryption())
            ? $this->legacyDecrypt($trimmedValue, $customPassword, $keySourceEnum, $encryptionVersion)
            : $this->coreDecrypt($trimmedValue, $customPassword, $keySourceEnum, $encryptionVersion);
    }

    /**
     * Legacy decryption method for older encryption versions.
     * Keep the signature the same between coreDecrypt and legacyDecrypt for simplicity.
     * Note that aes256DecryptTwo is used for KeyVersion::THREE as well as KeyVersion::TWO.
     *
     * @param  string     $trimmedValue
     * @param  ?string    $customPassword
     * @param  KeySource  $keySourceEnum
     * @param  KeyVersion $encryptionVersion
     * @return false|string
     */
    protected function legacyDecrypt(string $trimmedValue, ?string $customPassword, KeySource $keySourceEnum, KeyVersion $encryptionVersion): false|string
    {
        return $encryptionVersion === KeyVersion::ONE
            ? $this->aes256DecryptOne($trimmedValue, $customPassword)
            : $this->aes256DecryptTwo($trimmedValue, $customPassword);
    }

    /**
     * Checks if a crypt block is valid for use with the standard method
     *
     * @param  ?string $value The data to validate
     * @return bool True if valid, false otherwise
     */
    public function cryptCheckStandard(?string $value): bool
    {
        try {
            KeyVersion::fromPrefix($value ?? '');
            return true;
        } catch (\ValueError) {
            return false;
        }
    }

    /**
     * Core encryption function
     *
     * @param  ?string    $sValue         Raw data to be encrypted
     * @param  ?string    $customPassword If null, standard keys are used. If provided, keys are derived from this password
     * @param  KeySource  $keySource      The source of the keys. Options are 'drive' and 'database'
     * @param  KeyVersion $keyVersion     The key number/version
     * @return string The encrypted data
     * @throws CryptoGenException If encryption fails due to critical errors
     */
    protected function coreEncrypt(?string $sValue, ?string $customPassword = null, KeySource $keySource = KeySource::DRIVE, KeyVersion $keyVersion = self::CURRENT_KEY_VERSION): string
    {
        if (!$this->isOpenSSLExtensionLoaded()) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because missing openssl extension.");
        }

        if (empty($customPassword)) {
            // Collect the encryption keys. If they do not exist, then create them
            // The first key is for encryption. Then second key is for the HMAC hash
            $sSecretKey = $this->collectCryptoKey($keyVersion, "a", $keySource);
            $sSecretKeyHmac = $this->collectCryptoKey($keyVersion, "b", $keySource);
        } else {
            // customPassword mode, so turn the password into keys
            $sSalt = $this->getRandomBytes(32);
            if (empty($sSalt)) {
                throw new CryptoGenException("OpenEMR Error: Random Bytes error - exiting");
            }
            $sPreKey = $this->hashPbkdf2('sha384', $customPassword, $sSalt, 100000, 32, true);
            $sSecretKey = $this->hashHkdf('sha384', $sPreKey, 32, 'aes-256-encryption', $sSalt);
            $sSecretKeyHmac = $this->hashHkdf('sha384', $sPreKey, 32, 'sha-384-authentication', $sSalt);
        }

        if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because key(s) is blank.");
        }

        $iv = $this->getRandomBytes($this->getOpenSSLCipherIvLength('aes-256-cbc'));
        if (empty($iv)) {
            throw new CryptoGenException("OpenEMR Error: Random Bytes error - exiting");
        }

        $processedValue = $this->openSSLEncrypt(
            $sValue ?? '',
            'aes-256-cbc',
            $sSecretKey,
            $iv
        );

        $hmacHash = $this->hashHmac('sha384', $iv . $processedValue, $sSecretKeyHmac, true);

        if ($sValue != "" && ($processedValue == "" || $hmacHash == "")) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working (encrypted value is blank or hmac hash is blank).");
        }

        if (empty($customPassword)) {
            // prepend the encrypted value with the $hmacHash and $iv
            $completedValue = $hmacHash . $iv . $processedValue;
        } else {
            // customPassword mode, so prepend the encrypted value with the salts, $hmacHash and $iv
            $completedValue = $sSalt . $hmacHash . $iv . $processedValue;
        }

        return base64_encode($completedValue);
    }


    /**
     * Core decryption function
     *
     * @param  string     $sValue         Encrypted data to be decrypted
     * @param  ?string    $customPassword If null, standard keys are used. If provided, keys are derived from this password
     * @param  KeySource  $keySource      The source of the keys. Options are 'drive' and 'database'
     * @param  KeyVersion $keyVersion     The key version
     * @return false|string The decrypted data, or false if decryption fails
     */
    protected function coreDecrypt(string $sValue, ?string $customPassword = null, KeySource $keySource = KeySource::DRIVE, KeyVersion $keyVersion = self::CURRENT_KEY_VERSION): false|string
    {
        if (!$this->isOpenSSLExtensionLoaded()) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        $raw = base64_decode($sValue, true);
        if ($raw === false) {
            error_log("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
            return false;
        }

        if (empty($customPassword)) {
            // Collect the encryption keys.
            // The first key is for encryption. Then second key is for the HMAC hash
            $sSecretKey = $this->collectCryptoKey($keyVersion, "a", $keySource);
            $sSecretKeyHmac = $this->collectCryptoKey($keyVersion, "b", $keySource);
        } else {
            // customPassword mode, so turn the password keys
            // The first key is for encryption. Then second key is for the HMAC hash
            // First need to collect the salt from $raw (and then remove it from $raw)
            $sSalt = mb_substr($raw, 0, 32, '8bit');
            $raw = mb_substr($raw, 32, null, '8bit');
            // Now turn the password into keys
            $sPreKey = $this->hashPbkdf2('sha384', $customPassword, $sSalt, 100000, 32, true);
            $sSecretKey = $this->hashHkdf('sha384', $sPreKey, 32, 'aes-256-encryption', $sSalt);
            $sSecretKeyHmac = $this->hashHkdf('sha384', $sPreKey, 32, 'sha-384-authentication', $sSalt);
        }

        if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
            error_log("OpenEMR Error : Decryption is not working because key(s) is blank.");
            return false;
        }

        $ivLength = $this->getOpenSSLCipherIvLength('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 48, '8bit');
        $iv = mb_substr($raw, 48, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 48), null, '8bit');

        $calculatedHmacHash = $this->hashHmac('sha384', $iv . $encrypted_data, $sSecretKeyHmac, true);

        if ($this->hashEquals($hmacHash, $calculatedHmacHash)) {
            return $this->openSSLDecrypt(
                $encrypted_data,
                'aes-256-cbc',
                $sSecretKey,
                $iv
            );
        } else {
            try {
                // throw an exception
                throw new Exception("OpenEMR Error: Decryption failed HMAC Authentication!");
            } catch (Exception $e) {
                /**
                 * log the exception message and call stack then return legacy null as false for
                 * those evaluating the return value as $return == false which with legacy will eval as false.
                 * I've seen this in the codebase, and it's a bit of a hack, but it's a way to return false instead of null.
                 * Dev's should use empty() instead of == false to check return from this function.
                 * The goal here is so the call stack is exposed to track back to where the call originated.
                 */
                $stackTrace = debug_backtrace();
                $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
                error_log(errorLogEscape($e->getMessage()) . "\n" . errorLogEscape($formattedStackTrace));
                return false;
            }
        }
    }

    /**
     * Format exception message with stack trace
     *
     * @param  array $stackTrace Debug backtrace array
     * @return string Formatted stack trace string
     */
    protected function formatExceptionMessage(array $stackTrace): string
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
     * @param  ?string $sValue         Data to decrypt
     * @param  ?string $customPassword If null, uses standard key. If provided, derives key from this password
     * @return false|string The decrypted data, or false if decryption fails
     */
    public function aes256DecryptTwo(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!$this->isOpenSSLExtensionLoaded()) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            // Collect the encryption keys.
            // The first key is for encryption. Then second key is for the HMAC hash
            $sSecretKey = $this->collectCryptoKey(KeyVersion::TWO, "a");
            $sSecretKeyHmac = $this->collectCryptoKey(KeyVersion::TWO, "b");
        } else {
            // Turn the password into a hash(note use binary) to use as the keys
            $sSecretKey = $this->hash("sha256", $customPassword, true);
            $sSecretKeyHmac = $sSecretKey;
        }

        if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
            error_log("OpenEMR Error : Decryption is not working because key(s) is blank.");
            return false;
        }

        $raw = empty($sValue) ? '' : base64_decode($sValue, true);
        if ($raw === false) {
            error_log("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
            return false;
        }

        $ivLength = $this->getOpenSSLCipherIvLength('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 32, '8bit');
        $iv = mb_substr($raw, 32, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 32), null, '8bit');

        $calculatedHmacHash = $this->hashHmac('sha256', $iv . $encrypted_data, $sSecretKeyHmac, true);

        if ($this->hashEquals($hmacHash, $calculatedHmacHash)) {
            return $this->openSSLDecrypt(
                $encrypted_data,
                'aes-256-cbc',
                $sSecretKey,
                $iv
            );
        } else {
            try {
                // throw an exception
                throw new Exception("OpenEMR Error: Decryption failed hmac authentication!");
            } catch (Exception $e) {
                /**
                 * log the exception message and call stack then return legacy null as false for
                 * those evaluating the return value as $return == false which with legacy will eval as false.
                 * I've seen this in the codebase, and it's a bit of a hack, but it's a way to return false instead of null.
                 * Dev's should use empty() instead of == false to check return from this function.
                 * The goal here is so the call stack is exposed to track back to where the call originated.
                 */
                $stackTrace = debug_backtrace();
                $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
                error_log(errorLogEscape($e->getMessage()) . "\n" . errorLogEscape($formattedStackTrace));
                return false;
            }
        }
    }

    /**
     * Decrypts AES256 encrypted data using version 1 algorithm
     *
     * @param  ?string $sValue         Data to decrypt
     * @param  ?string $customPassword If null, uses standard key. If provided, derives key from this password
     * @return false|string The decrypted data
     */
    public function aes256DecryptOne(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!$this->isOpenSSLExtensionLoaded()) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        $sSecretKey = empty($customPassword)
            ? $this->collectCryptoKey(KeyVersion::ONE)
            : $this->hash("sha256", $customPassword);

        if (empty($sSecretKey)) {
            error_log("OpenEMR Error : Decryption is not working because key is blank.");
            return false;
        }

        $raw = empty($sValue) ? '' : base64_decode($sValue);

        $ivLength = $this->getOpenSSLCipherIvLength('aes-256-cbc');

        $iv = substr($raw, 0, $ivLength);
        $encrypted_data = substr($raw, $ivLength);

        return $this->openSSLDecrypt(
            $encrypted_data,
            'aes-256-cbc',
            $sSecretKey,
            $iv
        );
    }


    /**
     * Function to collect (and create, if needed) the standard keys
     * This mechanism will allow easy migration to new keys/ciphers in the future while
     * also maintaining backward compatibility of encrypted data.
     *
     * Note that to increase performance, it will store the key as a variable in this object in case
     * the key is used again (especially important when reading encrypted log entries where there
     * can be hundreds of decryption calls where it otherwise requires 5 steps to get the key; collect
     * key set from database, collect key set from drive, decrypt key set from drive using the database
     * key; caching the key will bypass all these steps).
     *
     * @param  KeyVersion $keyVersion The key number/version
     * @param  string     $sub        The key sublabel
     * @param  KeySource  $keySource  The source of the standard keys. Options are 'drive' and 'database'
     *                                The 'drive' keys are stored at
     *                                sites/<site-dir>/documents/logs_and_misc/methods The 'database'
     *                                keys are stored in the 'keys' sql table
     * @return string The key in raw form
     * @throws CryptoGenException If key collection fails due to critical errors
     */
    protected function collectCryptoKey(KeyVersion $keyVersion, string $sub = "", KeySource $keySource = KeySource::DRIVE): string
    {
        // Build the main label
        $label = $keyVersion->toString() . $sub;
        // Check if key is in the cache first (and return it if it is)
        $cacheLabel = $label . $keySource->value;
        if (!empty($this->keyCache[$cacheLabel])) {
            return $this->keyCache[$cacheLabel];
        }

        // If the key does not exist, then create it
        $key = $keySource === KeySource::DATABASE
            ? $this->collectDatabaseKey($label, $keyVersion)
            : $this->collectDriveKey($label, $keyVersion);

        // Store key in cache and then return the key
        $this->keyCache[$cacheLabel] = $key;
        return $key;
    }

    /**
     * Collect the crypto key from the database, creating it if necessary.
     *
     * @param  string     $label      the version and sub-version label
     * @param  KeyVersion $keyVersion the key encryption version (not used in this implementation, but important for future compatibility)
     * @return string the plaintext key value
     * @throws CryptoGenException if key collection fails
     */
    protected function collectDatabaseKey(string $label, KeyVersion $keyVersion): string
    {
        $sqlResult = $this->sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
        $key = (empty($sqlResult['value']))
            ? $this->createDatabaseKey($label, $keyVersion)
            : base64_decode((string) $sqlResult['value']);
        if (empty($key)) {
            throw new CryptoGenException("OpenEMR Error : Key creation in database is not working - Exiting.");
        }
        return $key;
    }

    /**
     * Create a new crypto key in the database.
     * Produce a 256bit key (32 bytes equals 256 bits)
     *
     * @param  string     $label      the version and sub-version label
     * @param  KeyVersion $keyVersion the key encryption version (not used in this implementation, but important for future compatibility)
     * @return string the plaintext key value
     * @throws CryptoGenException if key creation, storage, encoding or encryption fails
     */
    protected function createDatabaseKey(string $label, KeyVersion $keyVersion): string
    {
        // Produce a 256bit key (32 bytes equals 256 bits)
        $newKey = $this->getRandomBytes(32);
        if (empty($newKey)) {
            throw new CryptoGenException("OpenEMR Error: Random Bytes error - exiting");
        }
        $this->sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)", [$label, base64_encode($newKey)]);
        // round trip to be sure the newly created key is correctly stored and encoded
        // this shouldn't cause a performance problem because new keys aren't created too frequently.
        $sqlResult = $this->sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
        $sqlKey = base64_decode((string) $sqlResult['value']);
        if ($newKey === $sqlKey) {
            return $newKey;
        }
        throw new CryptoGenException("OpenEMR Error: The newly created key could not be stored or encoded correctly.");
    }

    /**
     * Collect the crypto key from the drive, creating it if necessary.
     *
     * @param  string     $label      the version and sub-version label
     * @param  KeyVersion $keyVersion the key encryption version
     * @return string the plaintext key value
     * @throws CryptoGenException if key collection fails
     */
    protected function collectDriveKey(string $label, KeyVersion $keyVersion): string
    {
        $keyPath = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label;
        if (!$this->fileExists($keyPath)) {
            return $this->createDriveKey($label, $keyVersion);
        }
        $fileContents = $this->fileGetContents($keyPath);
        $key = $keyVersion->usesLegacyStorage()
            ? base64_decode(rtrim($fileContents))
            : $this->decryptStandard($fileContents, null, KeySource::DATABASE->value);
        if (!empty($key)) {
            return $key;
        }
        throw new CryptoGenException("OpenEMR Error: Key in drive is not compatible (ie. can not be decrypted) with key in database - Exiting.");
    }

    /**
     * Create a new crypto key in the drive.
     * Produce a 256bit key (32 bytes equals 256 bits)
     *
     * @param  string     $label      the version and sub-version label
     * @param  KeyVersion $keyVersion the key encryption version
     * @return string the plaintext key value
     * @throws CryptoGenException if key creation, storage, encoding or encryption fails
     */
    protected function createDriveKey(string $label, KeyVersion $keyVersion): string
    {
        $keyPath = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label;
        $key = $this->getRandomBytes(32);
        if (empty($key)) {
            throw new CryptoGenException("OpenEMR Error: Random Bytes error - exiting");
        }
        $fileContents = $keyVersion->usesLegacyStorage()
            ? base64_encode($key)
            : $this->encryptStandard($key, null, KeySource::DATABASE->value);
        $this->filePutContents($keyPath, $fileContents);

        // round trip to be sure the newly created key is correctly stored, encoded and encrypted
        // this shouldn't cause a performance problem because new keys aren't created too frequently.
        if ($this->fileExists($keyPath)) {
            $storedFileContents = $this->fileGetContents($keyPath);
            $storedKey = $keyVersion->usesLegacyStorage()
                ? base64_decode(rtrim($storedFileContents))
                : $this->decryptStandard($storedFileContents, null, KeySource::DATABASE->value);
            if ($key === $storedKey) {
                return $key;
            }
        }
        throw new CryptoGenException("OpenEMR Error: The newly created key could not be stored, encoded or encrypted correctly.");
    }

    /**
     * Check if the OpenSSL extension is loaded.
     * This is a wrapper to enable better testing.
     *
     * @codeCoverageIgnore
     *
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
     *
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
     *
     * @param  string $algo       Algorithm to use for hashing
     * @param  string $password   The password to derive the key from
     * @param  string $salt       The salt to use for derivation
     * @param  int    $iterations Number of iterations
     * @param  int    $length     Length of output key
     * @param  bool   $rawOutput  Whether to return raw binary data
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
     *
     * @param  string $algo   Algorithm to use for hashing
     * @param  string $key    Input key material
     * @param  int    $length Length of output key
     * @param  string $info   Optional context and application specific information
     * @param  string $salt   Optional salt value
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
     *
     * @param  string $cipher The cipher method
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
     *
     * @param  string $data   The data to encrypt
     * @param  string $cipher The cipher method
     * @param  string $key    The encryption key
     * @param  string $iv     The initialization vector
     * @return string The encrypted data
     */
    protected function openSSLEncrypt(string $data, string $cipher, string $key, string $iv): string
    {
        // embed constants in these wrapper functions
        // so they don't trigger when the extension
        // isn't loaded.
        $options = OPENSSL_RAW_DATA;
        return openssl_encrypt($data, $cipher, $key, $options, $iv);
    }

    /**
     * Wrapper for openssl_decrypt to enable better testing.
     *
     * @codeCoverageIgnore
     *
     * @param  string $data   The data to decrypt
     * @param  string $cipher The cipher method
     * @param  string $key    The decryption key
     * @param  string $iv     The initialization vector
     * @return false|string The decrypted data or false on failure
     */
    protected function openSSLDecrypt(string $data, string $cipher, string $key, string $iv): false|string
    {
        // embed constants in these wrapper functions
        // so they don't trigger when the extension
        // isn't loaded.
        $options = OPENSSL_RAW_DATA;
        return openssl_decrypt($data, $cipher, $key, $options, $iv);
    }

    /**
     * Wrapper for hash_hmac to enable better testing.
     *
     * @codeCoverageIgnore
     *
     * @param  string $algo   The hashing algorithm
     * @param  string $data   The data to hash
     * @param  string $key    The key for HMAC
     * @param  bool   $binary Whether to return raw binary data
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
     *
     * @param  string $knownString The known string
     * @param  string $userString  The user-provided string
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
     *
     * @param  string $algo   The hashing algorithm
     * @param  string $data   The data to hash
     * @param  bool   $binary Whether to return raw binary data
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
     *
     * @param  string        $filename The file to write to
     * @param  mixed         $data     The data to write
     * @param  int           $flags    Optional flags
     * @param  resource|null $context  Optional context
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
     *
     * @param  string        $filename         The file to read from
     * @param  bool          $use_include_path Optional flag to search include path
     * @param  resource|null $context          Optional context
     * @param  int           $offset           Optional offset to start reading from
     * @param  int|null      $length           Optional maximum length to read
     * @return string|false The file contents or false on failure
     */
    protected function fileGetContents(string $filename, bool $use_include_path = false, $context = null, int $offset = 0, ?int $length = null): string|false
    {
        return file_get_contents($filename, $use_include_path, $context, $offset, $length);
    }


    /**
     * Wrapper for sqlQueryNoLog to enable better testing.
     *
     * @codeCoverageIgnore
     *
     * @param  string $statement The SQL query statement
     * @param  array  $binds     The parameter bindings for the query
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
     *
     * @param  string $statement The SQL statement to execute
     * @param  array  $binds     The parameter bindings for the statement
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
     *
     * @param  string $filename The file name to check
     * @return bool True if the file exists, false otherwise
     */
    protected function fileExists(string $filename): bool
    {
        return file_exists($filename);
    }
}
