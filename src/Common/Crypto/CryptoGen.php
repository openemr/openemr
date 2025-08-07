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
 * @copyright Copyright (c) 2015 Ensoftek, Inc
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
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
        return 1 === preg_match('/^00[1-6]/', $value ?? '');
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

        if (!extension_loaded('openssl')) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because missing openssl extension.");
        }

        if ($customPassword === '') {
            // Collect the encryption keys. If they do not exist, then create them
            // The first key is for encryption. Then second key is for the HMAC hash
            $secretKey = $this->collectCryptoKey($keyVersion, "a", $keySource);
            $secretKeyHmac = $this->collectCryptoKey($keyVersion, "b", $keySource);
        } else {
            // customPassword mode, so turn the password into keys
            $salt = RandomGenUtils::produceRandomBytes(32);
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

        $iv = RandomGenUtils::produceRandomBytes(openssl_cipher_iv_length('aes-256-cbc'));
        if (empty($iv)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }

        $processedValue = openssl_encrypt(
            $value,
            'aes-256-cbc',
            $secretKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmacHash = hash_hmac('sha384', $iv . $processedValue, $secretKeyHmac, true);

        if ($value !== "" && ($processedValue === "" || $hmacHash === "")) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working (encrypted value is blank or hmac hash is blank).");
        }

        $completedValue = $hmacHash . $iv . $processedValue;
        if (isset($salt)) {
            // customPassword mode, so prepend the encrypted value with the salt
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

        if (!extension_loaded('openssl')) {
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
            $preKey = hash_pbkdf2('sha384', $customPassword, $salt, 100000, 32, true);
            $secretKey = hash_hkdf('sha384', $preKey, 32, 'aes-256-encryption', $salt);
            $secretKeyHmac = hash_hkdf('sha384', $preKey, 32, 'sha-384-authentication', $salt);
        }

        if (empty($secretKey) || empty($secretKeyHmac)) {
            $errorMessage = "OpenEMR Error : Decryption is not working because key(s) is blank.";
            error_log($errorMessage);
            throw new CryptoGenException($errorMessage);
        }

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 48, '8bit');
        $iv = mb_substr($raw, 48, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 48), null, '8bit');

        $calculatedHmacHash = hash_hmac('sha384', $iv . $encrypted_data, $secretKeyHmac, true);

        if (hash_equals($hmacHash, $calculatedHmacHash)) {
            return openssl_decrypt(
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
        if (!extension_loaded('openssl')) {
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
            $secretKey = hash("sha256", $customPassword, true);
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

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 32, '8bit');
        $iv = mb_substr($raw, 32, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 32), null, '8bit');

        $calculatedHmacHash = hash_hmac('sha256', $iv . $encrypted_data, $secretKeyHmac, true);

        if (hash_equals($hmacHash, $calculatedHmacHash)) {
            return openssl_decrypt(
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
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        // Collect the key or use custom password
        $secretKey = empty($customPassword)
            ? $this->collectCryptoKey("one", "", "drive")  // Collect the key. If it does not exist, then create it
            : hash("sha256", $customPassword);             // Turn the password into a hash to use as the key

        if (empty($secretKey)) {
            error_log("OpenEMR Error : Decryption is not working because key is blank.");
            return false;
        }

        $value ??= '';

        $raw = base64_decode($value);

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');

        $iv = substr($raw, 0, $ivLength);
        $encrypted_data = substr($raw, $ivLength);

        return openssl_decrypt(
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
        $secretKey = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $initializationVector = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $decodedValue = base64_decode($value);
        $decryptedData = mcrypt_decrypt(
            MCRYPT_RIJNDAEL_256,
            $secretKey,
            $decodedValue,
            MCRYPT_MODE_ECB,
            $initializationVector
        );
        return rtrim($decryptedData, "\0");
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
        $sqlValue = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
        if (empty($sqlValue['value'])) {
            // Create a new key and place in database
            // Produce a 256bit key (32 bytes equals 256 bits)
            $newKey = RandomGenUtils::produceRandomBytes(32);
            if (empty($newKey)) {
                throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
            }
            sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)", [$label, base64_encode($newKey)]);
        }
        $sqlKey = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
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
        $newKey = RandomGenUtils::produceRandomBytes(32);
        if (empty($newKey)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }
        $usesLegacyStorage = in_array($keyVersion, ["one", "two", "three", "four"]);
        $fileContents = $usesLegacyStorage
            ? base64_encode($newKey) // older key versions that did not encrypt the key on the drive
            : $this->encryptStandard($newKey, null, 'database');
        file_put_contents($keyPath, $fileContents);
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
        if (file_exists($keyPath)) {
            $fileContents = file_get_contents($keyPath);
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
        if (file_exists($keyPath)) {
            throw new CryptoGenException("OpenEMR Error : Key in drive is not compatible (ie. can not be decrypted) with key in database - Exiting.");
        }
        throw new CryptoGenException("OpenEMR Error : Key creation in drive is not working - Exiting.");
    }
}
