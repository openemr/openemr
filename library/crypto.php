<?php
/**
 * Crypto library.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
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
 * @param  string  $customPassword  Do not use this unless supporting legacy stuff(ie. the download encrypted file feature).
 *
 */
function encryptStandard($value, $customPassword = null)
{
    # This is the current encrypt/decrypt version
    # (this will always be a three digit number that we will
    #  increment when update the encrypt/decrypt methodology
    #  which allows being able to maintain backward compatibility
    #  to decrypt values from prior versions)
    $encryptionVersion = "003";

    $encryptedValue = $encryptionVersion . aes256Encrypt($value, $customPassword);

    return $encryptedValue;
}

/**
 * Standard function to decrypt
 *
 * @param  string  $value           This is the data to encrypt.
 * @param  string  $customPassword  Do not use this unless supporting legacy stuff(ie. the download encrypted file feature).
 *
 */
function decryptStandard($value, $customPassword = null)
{
    # Collect the encrypt/decrypt version and remove it from the value
    $encryptionVersion = intval(mb_substr($value, 0, 3, '8bit'));
    $trimmedValue = mb_substr($value, 3, null, '8bit');

    # Map the encrypt/decrypt version to the correct decryption function
    if (($encryptionVersion == 2) || ($encryptionVersion == 3)) {
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
 * (basically checks if first 3 values are numbers)
 */
function cryptCheckStandard($value)
{
    if (preg_match('/^\d\d\d/', $value)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Function to AES256 encrypt a given string
 *
 * @param  string  $sValue          Raw data that will be encrypted.
 * @param  string  $customPassword  If null, then use standard key. If provide a password, then will derive key from this.
 * @return string                   returns the encrypted data.
 */
function aes256Encrypt($sValue, $customPassword = null)
{
    if (!extension_loaded('openssl')) {
        error_log("OpenEMR Error : Encryption is not working because missing openssl extension.");
    }

    if (empty($customPassword)) {
        // Collect the encryption keys. If they does not exist, then create them
        // The first key is for encryption. Then second key is for the HMAC hash
        $sSecretKey = aes256PrepKey("two", "a");
        $sSecretKeyHmac = aes256PrepKey("two", "b");
    } else {
        // Turn the password into a hash(note use binary) to use as the keys
        $sSecretKey = hash("sha256", $customPassword, true);
        $sSecretKeyHmac = $sSecretKey;
    }

    if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
        error_log("OpenEMR Error : Encryption is not working because key(s) is blank.");
    }

    try {
        $iv = random_bytes(openssl_cipher_iv_length('AES-256-CBC'));
    } catch (Error $e) {
        error_log('OpenEMR Error : Encryption is not working because of random_bytes() Error: ' . $e->getMessage());
    } catch (Exception $e) {
        error_log('OpenEMR Error : Encryption is not working because of random_bytes() Exception: ' . $e->getMessage());
    }

    $processedValue = openssl_encrypt(
        $sValue,
        'AES-256-CBC',
        $sSecretKey,
        OPENSSL_RAW_DATA,
        $iv
    );

    $hmacHash = hash_hmac('sha256', $iv.$processedValue, $sSecretKeyHmac, true);

    if ($sValue != "" && ($processedValue == "" || $hmacHash == "")) {
        error_log("OpenEMR Error : Encryption is not working.");
    }

    // prepend the encrypted value with the $hmacHash and $iv
    $completedValue = $hmacHash . $iv . $processedValue;

    return base64_encode($completedValue);
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
        $sSecretKey = aes256PrepKey("two", "a");
        $sSecretKeyHmac = aes256PrepKey("two", "b");
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


    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    $hmacHash = mb_substr($raw, 0, 32, '8bit');
    $iv = mb_substr($raw, 32, $ivLength, '8bit');
    $encrypted_data = mb_substr($raw, ($ivLength+32), null, '8bit');

    $calculatedHmacHash = hash_hmac('sha256', $iv.$encrypted_data, $sSecretKeyHmac, true);

    if (hash_equals($hmacHash, $calculatedHmacHash)) {
        return openssl_decrypt(
            $encrypted_data,
            'AES-256-CBC',
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
        $sSecretKey = aes256PrepKey();
    } else {
        // Turn the password into a hash to use as the key
        $sSecretKey = hash("sha256", $customPassword);
    }

    if (empty($sSecretKey)) {
        error_log("OpenEMR Error : Encryption is not working.");
    }

    $raw = base64_decode($sValue);

    $ivLength = openssl_cipher_iv_length('AES-256-CBC');

    $iv = substr($raw, 0, $ivLength);
    $encrypted_data = substr($raw, $ivLength);

    return openssl_decrypt(
        $encrypted_data,
        'AES-256-CBC',
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

// Function to collect (and create, if needed) the standard key
// The key is stored at sites/<site-dir>/documents/logs_and_misc/methods/one
//  This mechanism will allow easy migration to new keys/ciphers in the future while
//  also maintaining backward compatibility of encrypted data (for example, if upgrade
//  to another cipher/mode, then could place the new key for this in
//  sites/<site-dir>/documents/logs_and_misc/methods/two and then adjust pertinent code).
function aes256PrepKey($version = "one", $sub = "")
{
    // Build the label
    $label = $version.$sub;

    // Collect the key. If it does not exist, then create it
    if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
        // Create a key file

        try {
            // Produce a 256bit key (32 bytes equals 256 bits)
            $newKey = random_bytes(32);
        } catch (Error $e) {
            error_log('OpenEMR Error : Encryption is not working because of random_bytes() Error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log('OpenEMR Error : Encryption is not working because of random_bytes() Exception: ' . $e->getMessage());
        }

        file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/". $label, base64_encode($newKey));
    }

    // Collect key from file
    $key = base64_decode(rtrim(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)));

    if (empty($key)) {
        error_log("OpenEMR Error : Key creation is not working.");
    }

    // Return the key
    return $key;
}
