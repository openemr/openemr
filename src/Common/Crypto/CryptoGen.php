<?php

/**
 * CryptoGen class.
 *
 *   OpenEMR encryption/decryption strategy:
 *    1. Two separate private key sets are used, one key set in the database and one key set on the drive.
 *    2. The private database key set is stored in the keys mysql table
 *    3. The private drive key set is stored in sites/<site-name>/documents/logs_and_misc/methods/
 *    4. The private database key set is used when encrypting/decrypting data that is stored on the drive.
 *    5. The private drive key set is used when encrypting/decrypting data that is stored in the database.
 *    6. The private drive key set is encrypted by the private database key set
 *    7. Encryption/key versioning is used to support algorithm improvements while also ensuring
 *       backwards compatibility of decryption.
 *    8. To ensure performance, the CryptoGen class will cache the key sets that are used inside the object,
 *       which avoids numerous repeat calls to collect the key sets (and repeat decryption of the key set
 *       from the drive).
 *    9. There is also support for passphrase encryption/decryption (ie. no private keys are used).
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
use OpenEMR\Common\Utils\RandomGenUtils;

class CryptoGen
{
    # This is the current encrypt/decrypt version
    # (this will always be a three digit number that we will
    #  increment when update the encrypt/decrypt methodology
    #  which allows being able to maintain backward compatibility
    #  to decrypt values from prior versions)
    # Remember to update cryptCheckStandard() and decryptStandard()
    #  when increment this.
    private string $encryptionVersion = "006";
    # This is the current key version. As above, will increment this
    #  when update the encrypt/decrypt methodology to allow backward
    #  compatibility.
    # Remember to update decryptStandard() when increment this.
    private string $keyVersion = "six";

    # Key cache to optimize key collection, which avoids numerous repeat
    #  calls to collect the key sets (and repeat decryption of the key set
    #  from the drive).
    private array $keyCache = [];

    public function __construct()
    {
    }

    /**
     * Standard function to encrypt
     *
     * @param string|null $value          This is the data to encrypt.
     * @param string|null $customPassword If provide a password, then will derive keys from this.(and will not use the standard keys)
     * @param string      $keySource      This is the source of the standard keys. Options are 'drive' and 'database'
     *
     */
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
    {
        $encryptedValue = $this->encryptionVersion . $this->coreEncrypt($value, $customPassword, $keySource, $this->keyVersion);

        return $encryptedValue;
    }

    /**
     * Standard function to decrypt
     *
     * @param string|null $value          This is the data to decrypt.
     * @param string|null $customPassword If provide a password, then will derive keys from this.(and will not use the standard keys)
     * @param string      $keySource      This is the source of the standard keys. Options are 'drive' and 'database'
     * @param int|null    $minimumVersion This is the minimum encryption version supported (useful if accepting encrypted data
     *                                    from outside OpenEMR to ensure bad actor is not trying to use an older version).
     * @return false|string
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
    {
        if (empty($value)) {
            return "";
        }

        # Collect the encrypt/decrypt version and remove it from the value
        $encryptionVersion = intval(mb_substr($value, 0, 3, '8bit'));
        $trimmedValue = mb_substr($value, 3, null, '8bit');

        if (!empty($minimumVersion)) {
            if ($encryptionVersion < $minimumVersion) {
                error_log("OpenEMR Error : Decryption is not working because the encrypt/decrypt version is lower than allowed.");
                return false;
            }
        }

        # Map the encrypt/decrypt version to the correct decryption function
        if ($encryptionVersion == 6) {
            return $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "six");
        } elseif ($encryptionVersion == 5) {
            return $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "five");
        } elseif ($encryptionVersion == 4) {
            return $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "four");
        } elseif (($encryptionVersion == 2) || ($encryptionVersion == 3)) {
            return $this->aes256DecryptTwo($trimmedValue, $customPassword);
        } elseif ($encryptionVersion == 1) {
            return $this->aes256DecryptOne($trimmedValue, $customPassword);
        } else {
            error_log("OpenEMR Error : Decryption is not working because of unknown encrypt/decrypt version.");
            return false;
        }
    }

    /**
     * Check if a crypt block is valid to use for the standard method
     * (basically checks if correct values are used)
     */
    public function cryptCheckStandard(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        if (preg_match('/^00[1-6]/', $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to encrypt data
     * Should not be called directly (only called by encryptStandard() function)
     *
     * @param string|null $sValue          Raw data that will be encrypted.
     * @param string|null $customPassword  If null, then use standard keys. If provide a password, then will derive key from this.
     * @param string      $keySource       This is the source of the keys. Options are 'drive' and 'database'
     * @param string|null $keyNumber       This is the key number/version.
     * @return string                      returns the encrypted data.
     * @throws CryptoGenException          if fails, which are critical errors requiring die of script
     */
    private function coreEncrypt(?string $sValue, ?string $customPassword = null, string $keySource = 'drive', ?string $keyNumber = null): string
    {
        $keyNumber = isset($keyNumber) ? $keyNumber : $this->keyVersion;

        if (!extension_loaded('openssl')) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because missing openssl extension.");
        }

        if (empty($customPassword)) {
            // Collect the encryption keys. If they do not exist, then create them
            // The first key is for encryption. Then second key is for the HMAC hash
            $sSecretKey = $this->collectCryptoKey($keyNumber, "a", $keySource);
            $sSecretKeyHmac = $this->collectCryptoKey($keyNumber, "b", $keySource);
        } else {
            // customPassword mode, so turn the password into keys
            $sSalt = RandomGenUtils::produceRandomBytes(32);
            if (empty($sSalt)) {
                throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
            }
            $sPreKey = hash_pbkdf2('sha384', $customPassword, $sSalt, 100000, 32, true);
            $sSecretKey = hash_hkdf('sha384', $sPreKey, 32, 'aes-256-encryption', $sSalt);
            $sSecretKeyHmac = hash_hkdf('sha384', $sPreKey, 32, 'sha-384-authentication', $sSalt);
        }

        if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because key(s) is blank.");
        }

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
     * Function to decrypt data
     * Should not be called directly (only called by decryptStandard() function)
     *
     * @param string      $sValue         Encrypted data that will be decrypted.
     * @param string|null $customPassword If null, then use standard keys. If provide a password, then will derive key from this.
     * @param string      $keySource      This is the source of the keys. Options are 'drive' and 'database'
     * @param string|null $keyNumber      This is the key number/version.
     * @return false|string or false      returns the decrypted data or false if failed.
     */
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
            // Collect the encryption keys.
            // The first key is for encryption. Then second key is for the HMAC hash
            $sSecretKey = $this->collectCryptoKey($keyNumber, "a", $keySource);
            $sSecretKeyHmac = $this->collectCryptoKey($keyNumber, "b", $keySource);
        } else {
            // customPassword mode, so turn the password keys
            // The first key is for encryption. Then second key is for the HMAC hash
            // First need to collect the salt from $raw (and then remove it from $raw)
            $sSalt = mb_substr($raw, 0, 32, '8bit');
            $raw = mb_substr($raw, 32, null, '8bit');
            // Now turn the password into keys
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
                // throw an exception
                throw new Exception("OpenEMR Error: Decryption failed HMAC Authentication!");
            } catch (Exception $e) {
                // log the exception message and call stack then return legacy null as false for
                // those evaluating the return value as $return == false which with legacy will eval as false.
                // I've seen this in the codebase, and it's a bit of a hack, but it's a way to return false instead of null.
                // Dev's should use empty() instead of == false to check return from this function.
                // The goal here is so the call stack is exposed to track back to where the call originated.
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

    /**
     * Function to AES256 decrypt a given string, version 2
     *
     * @param string|null $sValue              Encrypted data that will be decrypted.
     * @param string|null $customPassword If null, then use standard key. If provide a password, then will derive key from this.
     * @return false|string alse          returns the decrypted data or false if failed.
     */
    public function aes256DecryptTwo(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            // Collect the encryption keys.
            // The first key is for encryption. Then second key is for the HMAC hash
            $sSecretKey = $this->collectCryptoKey("two", "a");
            $sSecretKeyHmac = $this->collectCryptoKey("two", "b");
        } else {
            // Turn the password into a hash(note use binary) to use as the keys
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
                // throw an exception
                throw new Exception("OpenEMR Error: Decryption failed hmac authentication!");
            } catch (Exception $e) {
                // log the exception message and call stack then return legacy null as false for
                // those evaluating the return value as $return == false which with legacy will eval as false.
                // I've seen this in the codebase, and it's a bit of a hack, but it's a way to return false instead of null.
                // Dev's should use empty() instead of == false to check return from this function.
                // The goal here is so the call stack is exposed to track back to where the call originated.
                $stackTrace = debug_backtrace();
                $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
                error_log(errorLogEscape($e->getMessage()) . "\n" . errorLogEscape($formattedStackTrace));
                return false;
            }
        }
    }

    /**
     * Function to AES256 decrypt a given string, version 1
     *
     * @param string|null $sValue              Encrypted data that will be decrypted.
     * @param string|null $customPassword If null, then use standard key. If provide a password, then will derive key from this.
     * @return false|string               returns the decrypted data.
     */
    public function aes256DecryptOne(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            // Collect the key. If it does not exist, then create it
            $sSecretKey = $this->collectCryptoKey();
        } else {
            // Turn the password into a hash to use as the key
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

    // Function to decrypt a given string
    // This specific function is only used for backward compatibility
    // TODO: Should be removed in the future.
    public function aes256Decrypt_mycrypt($sValue)
    {
        $sSecretKey = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
        return rtrim(
            mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                $sSecretKey,
                base64_decode($sValue),
                MCRYPT_MODE_ECB,
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256,
                        MCRYPT_MODE_ECB
                    ),
                    MCRYPT_RAND
                )
            ),
            "\0"
        );
    }

    /**
     * Function to collect (and create, if needed) the standard keys
     *  This mechanism will allow easy migration to new keys/ciphers in the future while
     *  also maintaining backward compatibility of encrypted data.
     *
     * Note that to increase performance, it will store the key as a variable in this object in case
     *  the key is used again (especially important when reading encrypted log entries where there
     *  can be hundreds of decryption calls where it otherwise requires 5 steps to get the key; collect
     *  key set from database, collect key set from drive, decrypt key set from drive using the database
     *  key; caching the key will bypass all these steps).
     *
     * @param string $version     This is the number/version of they key.
     * @param string $sub         This is the sublabel of the key
     * @param string $keySource   This is the source of the standard keys. Options are 'drive' and 'database'
     *                            The 'drive' keys are stored at sites/<site-dir>/documents/logs_and_misc/methods
     *                            The 'database' keys are stored in the 'keys' sql table
     * @return string             Returns the key in raw form.
     * @throws CryptoGenException if fails, which are critical errors requiring die of script
     */
    private function collectCryptoKey(string $version = "one", string $sub = "", string $keySource = 'drive'): string
    {
        // Check if key is in the cache first (and return it if it is)
        $cacheLabel = $version . $sub . $keySource;
        if (!empty($this->keyCache[$cacheLabel])) {
            return $this->keyCache[$cacheLabel];
        }

        // Build the main label
        $label = $version . $sub;

        // If the key does not exist, then create it
        if ($keySource == 'database') {
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
        } else { //$keySource == 'drive'
            if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
                // Create a key and place in drive
                // Produce a 256bit key (32 bytes equals 256 bits)
                $newKey = RandomGenUtils::produceRandomBytes(32);
                if (empty($newKey)) {
                    throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
                }
                if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                    // older key versions that did not encrypt the key on the drive
                    file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, base64_encode($newKey));
                } else {
                    file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, $this->encryptStandard($newKey, null, 'database'));
                }
            }
        }

        // Collect key
        if ($keySource == 'database') {
            $sqlKey = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
            $key = base64_decode($sqlKey['value']);
        } else { //$keySource == 'drive'
            if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                // older key versions that did not encrypt the key on the drive
                $key = base64_decode(rtrim(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)));
            } else {
                $key = $this->decryptStandard(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label), null, 'database');
            }
        }

        // Ensure have a key (if do not have key, then is critical error, and will exit)
        if (empty($key)) {
            if ($keySource == 'database') {
                throw new CryptoGenException("OpenEMR Error : Key creation in database is not working - Exiting.");
            } else { //$keySource == 'drive'
                if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
                    throw new CryptoGenException("OpenEMR Error : Key creation in drive is not working - Exiting.");
                } else {
                    throw new CryptoGenException("OpenEMR Error : Key in drive is not compatible (ie. can not be decrypted) with key in database - Exiting.");
                }
            }
        }

        // Store key in cache and then return the key
        $this->keyCache[$cacheLabel] = $key;
        return $key;
    }
}
