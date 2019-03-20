<?php
/**
 * Crypto library.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ensoftek, Inc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Ensoftek, Inc
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


/**
 * Standard function to encrypt
 *
 * @param  string  $value           This is the data to encrypt.
 * @param  string  $customPassword  If provide a password, then will derive keys from this.(and will not use the standard keys)
 * @param  string  $keySource       This is the source of the standard keys. Options are 'drive' and 'database'
 *
 */
function encryptStandard($value, $customPassword = null, $keySource = 'drive')
{
    # This is the current encrypt/decrypt version
    # (this will always be a three digit number that we will
    #  increment when update the encrypt/decrypt methodology
    #  which allows being able to maintain backward compatibility
    #  to decrypt values from prior versions)
    # Remember to update cryptCheckStandard() when increment this.
    $encryptionVersion = "005";

    $encryptedValue = $encryptionVersion . coreEncrypt($value, $customPassword, $keySource, "five");

    return $encryptedValue;
}

/**
 * Standard function to decrypt
 *
 * @param  string  $value           This is the data to encrypt.
 * @param  string  $customPassword  If provide a password, then will derive keys from this.(and will not use the standard keys)
 * @param  string  $keySource       This is the source of the standard keys. Options are 'drive' and 'database'
 *
 */
function decryptStandard($value, $customPassword = null, $keySource = 'drive')
{
    if (empty($value)) {
        return "";
    }

    # Collect the encrypt/decrypt version and remove it from the value
    $encryptionVersion = intval(mb_substr($value, 0, 3, '8bit'));
    $trimmedValue = mb_substr($value, 3, null, '8bit');

    # Map the encrypt/decrypt version to the correct decryption function
    if ($encryptionVersion == 5) {
        return coreDecrypt($trimmedValue, $customPassword, $keySource, "five");
    } else if ($encryptionVersion == 4) {
        return coreDecrypt($trimmedValue, $customPassword, $keySource, "four");
    } else if (($encryptionVersion == 2) || ($encryptionVersion == 3)) {
        return aes256DecryptTwo($trimmedValue, $customPassword);
    } else if ($encryptionVersion == 1) {
        return aes256DecryptOne($trimmedValue, $customPassword);
    } else {
        error_log("OpenEMR Error : Decryption is not working because of unknown encrypt/decrypt version.");
        return false;
    }
}

/**
 * Check if a crypt block is valid to use for the standard method
 * (basically checks if correct values are used)
 */
function cryptCheckStandard($value)
{
    if (empty($value)) {
        return false;
    }

    if (preg_match('/^00[1-5]/', $value)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Function to encrypt data
 * Should not be called directly (only called by encryptStandard() function)
 *
 * @param  string  $sValue          Raw data that will be encrypted.
 * @param  string  $customPassword  If null, then use standard keys. If provide a password, then will derive key from this.
 * @param  string  $keySource       This is the source of the keys. Options are 'drive' and 'database'
 * @param  string  $keyNumber       This is the key number/version.
 * @return string                   returns the encrypted data.
 */
function coreEncrypt($sValue, $customPassword = null, $keySource = 'drive', $keyNumber = "five")
{
    if (!extension_loaded('openssl')) {
        error_log("OpenEMR Error : Encryption is not working because missing openssl extension.");
    }

    if (empty($customPassword)) {
        // Collect the encryption keys. If they do not exist, then create them
        // The first key is for encryption. Then second key is for the HMAC hash
        $sSecretKey = collectCryptoKey($keyNumber, "a", $keySource);
        $sSecretKeyHmac = collectCryptoKey($keyNumber, "b", $keySource);
    } else {
        // customPassword mode, so turn the password into keys
        $sSalt = produceRandomBytes(32);
        if (empty($sSalt)) {
            error_log('OpenEMR Error : Random Bytes error - exiting');
            die();
        }
        $sPreKey = hash_pbkdf2('sha384', $customPassword, $sSalt, 100000, 32, true);
        $sSecretKey = hash_hkdf('sha384', $sPreKey, 32, 'aes-256-encryption', $sSalt);
        $sSecretKeyHmac = hash_hkdf('sha384', $sPreKey, 32, 'sha-384-authentication', $sSalt);
    }

    if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
        error_log("OpenEMR Error : Encryption is not working because key(s) is blank.");
    }

    $iv = produceRandomBytes(openssl_cipher_iv_length('aes-256-cbc'));
    if (empty($iv)) {
        error_log('OpenEMR Error : Random Bytes error - exiting');
        die();
    }

    $processedValue = openssl_encrypt(
        $sValue,
        'aes-256-cbc',
        $sSecretKey,
        OPENSSL_RAW_DATA,
        $iv
    );

    $hmacHash = hash_hmac('sha384', $iv.$processedValue, $sSecretKeyHmac, true);

    if ($sValue != "" && ($processedValue == "" || $hmacHash == "")) {
        error_log("OpenEMR Error : Encryption is not working.");
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
 * @param  string  $sValue          Encrypted data that will be decrypted.
 * @param  string  $customPassword  If null, then use standard keys. If provide a password, then will derive key from this.
 * @param  string  $keySource       This is the source of the keys. Options are 'drive' and 'database'
 * @param  string  $keyNumber       This is the key number/version.
 * @return string or false          returns the decrypted data or false if failed.
 */
function coreDecrypt($sValue, $customPassword = null, $keySource = 'drive', $keyNumber = "five")
{
    if (!extension_loaded('openssl')) {
        error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
        return false;
    }

    $raw = base64_decode($sValue, true);
    if ($raw === false) {
        error_log("OpenEMR Error : Encryption did not work because illegal characters were noted in base64_encoded data.");
        return false;
    }

    if (empty($customPassword)) {
        // Collect the encryption keys.
        // The first key is for encryption. Then second key is for the HMAC hash
        $sSecretKey = collectCryptoKey($keyNumber, "a", $keySource);
        $sSecretKeyHmac = collectCryptoKey($keyNumber, "b", $keySource);
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
        error_log("OpenEMR Error : Encryption is not working because key(s) is blank.");
        return false;
    }

    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $hmacHash = mb_substr($raw, 0, 48, '8bit');
    $iv = mb_substr($raw, 48, $ivLength, '8bit');
    $encrypted_data = mb_substr($raw, ($ivLength+48), null, '8bit');

    $calculatedHmacHash = hash_hmac('sha384', $iv.$encrypted_data, $sSecretKeyHmac, true);

    if (hash_equals($hmacHash, $calculatedHmacHash)) {
        return openssl_decrypt(
            $encrypted_data,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    } else {
        error_log("OpenEMR Error : Decryption failed authentication.");
    }
}


/**
 * Function to AES256 decrypt a given string, version 2
 *
 * @param  string  $sValue          Encrypted data that will be decrypted.
 * @param  string  $customPassword  If null, then use standard key. If provide a password, then will derive key from this.
 * @return string or false          returns the decrypted data or false if failed.
 */
function aes256DecryptTwo($sValue, $customPassword = null)
{
    if (!extension_loaded('openssl')) {
        error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
        return false;
    }

    if (empty($customPassword)) {
        // Collect the encryption keys.
        // The first key is for encryption. Then second key is for the HMAC hash
        $sSecretKey = collectCryptoKey("two", "a");
        $sSecretKeyHmac = collectCryptoKey("two", "b");
    } else {
        // Turn the password into a hash(note use binary) to use as the keys
        $sSecretKey = hash("sha256", $customPassword, true);
        $sSecretKeyHmac = $sSecretKey;
    }

    if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
        error_log("OpenEMR Error : Encryption is not working because key(s) is blank.");
        return false;
    }

    $raw = base64_decode($sValue, true);
    if ($raw === false) {
        error_log("OpenEMR Error : Encryption did not work because illegal characters were noted in base64_encoded data.");
        return false;
    }


    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $hmacHash = mb_substr($raw, 0, 32, '8bit');
    $iv = mb_substr($raw, 32, $ivLength, '8bit');
    $encrypted_data = mb_substr($raw, ($ivLength+32), null, '8bit');

    $calculatedHmacHash = hash_hmac('sha256', $iv.$encrypted_data, $sSecretKeyHmac, true);

    if (hash_equals($hmacHash, $calculatedHmacHash)) {
        return openssl_decrypt(
            $encrypted_data,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    } else {
        error_log("OpenEMR Error : Decryption failed authentication.");
    }
}


/**
 * Function to AES256 decrypt a given string, version 1
 *
 * @param  string  $sValue          Encrypted data that will be decrypted.
 * @param  string  $customPassword  If null, then use standard key. If provide a password, then will derive key from this.
 * @return string                   returns the decrypted data.
 */
function aes256DecryptOne($sValue, $customPassword = null)
{
    if (!extension_loaded('openssl')) {
        error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
    }

    if (empty($customPassword)) {
        // Collect the key. If it does not exist, then create it
        $sSecretKey = collectCryptoKey();
    } else {
        // Turn the password into a hash to use as the key
        $sSecretKey = hash("sha256", $customPassword);
    }

    if (empty($sSecretKey)) {
        error_log("OpenEMR Error : Encryption is not working.");
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
function aes256Decrypt_mycrypt($sValue)
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
 * @param  string  $version   This is the number/version of they key.
 * @param  string  $sub       This is the sublabel of the key
 * @param  string  $keySource This is the source of the standard keys. Options are 'drive' and 'database'
 *                            The 'drive' keys are stored at sites/<site-dir>/documents/logs_and_misc/methods
 *                            The 'database' keys are stored in the 'keys' sql table
 * @return string             Returns the key in raw form.
 */
function collectCryptoKey($version = "one", $sub = "", $keySource = 'drive')
{
    // Build the label
    $label = $version.$sub;

    // If the key does not exist, then create it
    if ($keySource == 'database') {
        $sqlValue = sqlQuery("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
        if (empty($sqlValue['value'])) {
            // Create a new key and place in database
            // Produce a 256bit key (32 bytes equals 256 bits)
            $newKey = produceRandomBytes(32);
            if (empty($newKey)) {
                error_log('OpenEMR Error : Random Bytes error - exiting');
                die();
            }
            sqlInsert("INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)", [$label, base64_encode($newKey)]);
        }
    } else { //$keySource == 'drive'
        if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
            // Create a key and place in drive
            // Produce a 256bit key (32 bytes equals 256 bits)
            $newKey = produceRandomBytes(32);
            if (empty($newKey)) {
                error_log('OpenEMR Error : Random Bytes error - exiting');
                die();
            }
            if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                // older key versions that did not encrypt the key on the drive
                file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, base64_encode($newKey));
            } else {
                file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, encryptStandard($newKey, null, 'database'));
            }
        }
    }

    // Collect key
    if ($keySource == 'database') {
        $sqlKey = sqlQuery("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
        $key = base64_decode($sqlKey['value']);
    } else { //$keySource == 'drive'
        if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
            // older key versions that did not encrypt the key on the drive
            $key = base64_decode(rtrim(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)));
        } else {
            $key = decryptStandard(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label), null, 'database');
        }
    }

    if (empty($key)) {
        error_log("OpenEMR Error : Key creation is not working - Exiting.");
        die();
    }

    // Return the key
    return $key;
}

// Produce random bytes (uses random_bytes with error checking)
function produceRandomBytes($length)
{
    try {
        $randomBytes = random_bytes($length);
    } catch (Error $e) {
        error_log('OpenEMR Error : Encryption is not working because of random_bytes() Error: ' . $e->getMessage());
        return '';
    } catch (Exception $e) {
        error_log('OpenEMR Error : Encryption is not working because of random_bytes() Exception: ' . $e->getMessage());
        return '';
    }

    return $randomBytes;
}

// Produce random string (uses random_int with error checking)
function produceRandomString($length = 26, $alphabet = 'abcdefghijklmnopqrstuvwxyz234567')
{
    $str = '';
    $alphamax = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; ++$i) {
        try {
            $str .= $alphabet[random_int(0, $alphamax)];
        } catch (Error $e) {
            error_log('OpenEMR Error : Encryption is not working because of random_int() Error: ' . $e->getMessage());
            return '';
        } catch (Exception $e) {
            error_log('OpenEMR Error : Encryption is not working because of random_int() Exception: ' . $e->getMessage());
            return '';
        }
    }
    return $str;
}
