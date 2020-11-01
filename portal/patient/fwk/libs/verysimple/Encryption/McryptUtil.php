<?php

/** @package    verysimple::Encryption */

/**
 * require supporting files
 */

/**
 * A static utility class for encryption/decrytion using the php mcrypt functionality
 *
 * @package verysimple::Encryption::McryptUtil
 */
class McryptUtil
{
    static $IV;
    static $CIPHER = MCRYPT_RIJNDAEL_256;
    static $MODE = MCRYPT_MODE_ECB;

    /**
     * Creates the initialization vector.
     * This method is called automatically
     * when encrypt/decrypt are called and does not need to be explicitly called
     */
    static function Init()
    {
        if (! McryptUtil::$IV) {
            if (! function_exists("mcrypt_get_iv_size")) {
                throw new Exception("The mcrypt extension does not appear to be enabled.");
            }

            $iv_size = mcrypt_get_iv_size(McryptUtil::$CIPHER, McryptUtil::$MODE);
            McryptUtil::$IV = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        }
    }

    /**
     * Encrypts data
     *
     * @param
     *          string data to be encrypted
     * @param
     *          string encryption key/passphrase
     * @param
     *          bool [optional] true to base64 encode the result
     * @return string encrypted data
     */
    static function Encrypt($data, $key, $encode = true)
    {
        McryptUtil::Init();

        $encrypted = mcrypt_encrypt(McryptUtil::$CIPHER, $key, $data, McryptUtil::$MODE, McryptUtil::$IV);

        return ($encode) ? base64_encode($encrypted) : $encrypted;
    }

    /**
     * Decrypts data that was previously encrypted
     *
     * @param
     *          string data to be decrypted
     * @param
     *          string encryption key/passphrase
     * @param
     *          bool [optional] true if the encrypted string is base64 encoded
     * @param
     *          bool [optional] true to strip the null character padding at the end
     * @return string decrypted data
     */
    static function Decrypt($data, $key, $decode = true, $strip_nulls = true)
    {
        McryptUtil::Init();

        if ($decode) {
            $data = base64_decode($data);
        }

        $decrypted = mcrypt_decrypt(McryptUtil::$CIPHER, $key, $data, McryptUtil::$MODE, McryptUtil::$IV);

        // mcrypt pads the end of the block with null chars, so we need to strip them
        return $strip_nulls ? rtrim($decrypted, "\0") : $decrypted;
    }
}
