<?php
/**
 * Crypto library.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ensoftek, Inc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Ensoftek, Inc
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Function to AES256 encrypt a given string
 *
 * @param  string  $sValue          Raw data that will be encrypted.
 * @param  string  $customPassword  If null, then use standard key. If provide a password, then will derive key from this.
 * @param  string  $baseEncode      True if wish to base64_encode() encrypted data.
 * @return string                   returns the encrypted data.
 */
function aes256Encrypt($sValue, $customPassword = null, $baseEncode = true)
{
    if (!extension_loaded('openssl')) {
        error_log("OpenEMR Error : Encryption is not working because missing openssl extension.");
    }

    if (empty($customPassword)) {
        // Use the standard key
        if (empty($GLOBALS['key_for_encryption'])) {
            // Collect the key. If it does not exist, then create it
            $GLOBALS['key_for_encryption'] = aes256PrepKey();
        }
        $sSecretKey = $GLOBALS['key_for_encryption'];
    } else {
        // Turn the password into a hash to use as the key
        $sSecretKey = hash("sha256", $customPassword);
    }

    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));

    $processedValue = openssl_encrypt(
        $sValue,
        'AES-256-CBC',
        $sSecretKey,
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($sValue != "" && $processedValue == "") {
        error_log("OpenEMR Error : Encryption is not working.");
    }

    // prepend the encrypted value with the $iv
    $completedValue = $iv . $processedValue;

    if ($baseEncode) {
        return base64_encode($completedValue);
    } else {
        return $completedValue;
    }
}

/**
 * Function to AES256 decrypt a given string
 *
 * @param  string  $sValue          Encrypted data that will be decrypted.
 * @param  string  $customPassword  If null, then use standard key. If provide a password, then will derive key from this.
 * @param  string  $baseEncode      True if wish to base64_decode() encrypted data.
 * @return string                   returns the decrypted data.
 */
function aes256Decrypt($sValue, $customPassword = null, $baseEncode = true)
{
    if (!extension_loaded('openssl')) {
        error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
    }

    if (empty($customPassword)) {
        // Use the standard key
        if (empty($GLOBALS['key_for_encryption'])) {
            // Collect the key. If it does not exist, then create it
            $GLOBALS['key_for_encryption'] = aes256PrepKey();
        }
        $sSecretKey = $GLOBALS['key_for_encryption'];
    } else {
        // Turn the password into a hash to use as the key
        $sSecretKey = hash("sha256", $customPassword);
    }

    if ($baseEncode) {
        $raw = base64_decode($sValue);
    } else {
        $raw = $sValue;
    }

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
function aes256PrepKey()
{
    // Collect the key. If it does not exist, then create it
    if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/one")) {
        // Create a key file
        // Below will produce a 256bit key (32 bytes equals 256 bits)
        $newKey = base64_encode(openssl_random_pseudo_bytes(32));
        file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/one", $newKey);
    }

    // Collect key from file
    $key = base64_decode(rtrim(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/one")));

    if (empty($key)) {
        error_log("OpenEMR Error : Audit Log with encryption is not working. Unable to collect key information or key is empty.");
    }

    // Return the key
    return $key;
}
