<?php

/**
 * Totp class used to generated MultiFactor App Based 2FA
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Anthony Zullo <anthonykzullo@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Anthony Zullo <anthonykzullo@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */

use OpenEMR\Common\Utils\RandomGenUtils;
use ParagonIE\MultiFactor\Vendor\GoogleAuth;

/**
 * Class Totp
 */
class Totp
{

    /** @var bool|GoogleAuth  */
    private $_googleAuth = false;
    /** @var bool|string - totp hashed secret */
    private $_secret = false;
    /** @var string - issuer mentioned in the QR App  */
    private $_issuer = "OpenEMR";
    /** @var  string - user name of user stored in QR App */
    private $_username;

    /**
     * @param bool $secret - user secret or false to generate
     * @param string $username - username to store in QR App
     */
    public function __construct($secret = false, $username = '')
    {
        $this->_username = $username;

        if ($secret) {
            $this->_secret = $secret;
        } else {
            // Shared key (per rfc6238 and rfc4226) should be 20 bytes (160 bits) and encoded in base32, which should
            //   be 32 characters in base32
            // Would be nice to use the OpenEMR\Common\Utils\RandomGenUtils\produceRandomBytes() function and then encode to base32,
            //   but does not appear to be a standard way to encode binary to base32 in php.
            $this->_secret = RandomGenUtils::produceRandomString(32, "234567ABCDEFGHIJKLMNOPQRSTUVWXYZ");
            if (empty($this->_secret)) {
                error_log('OpenEMR Error : Random String error - exiting');
                die();
            }
        }
    }

    /**
     * Generates a QR code code
     * @return bool|string|void
     */
    public function generateQrCode()
    {
        if (class_exists('ParagonIE\MultiFactor\Vendor\GoogleAuth')) {
            // Generates a file with a PNG of the qr code
            if (!empty($GLOBALS['temporary_files_dir'])) {
                $tempFilePath = tempnam($GLOBALS['temporary_files_dir'], "oer");
            } else {
                $tempFilePath = tempnam(sys_get_temp_dir(), 'oer');
            }
            $this->_getGoogleAuth()->makeQRCode(null, $tempFilePath, $this->_username, $this->_issuer);

            // Gets the image file data to return
            $data = base64_encode(file_get_contents($tempFilePath));
            $image = sprintf('data:%s;base64,%s', 'image/png', $data);

            // Delete image file before returning
            unlink($tempFilePath);

            return $image;
        }
        return false;
    }

    /**
     * Validates a TOTP
     * @param $totp : unencrypted
     * @return bool
     */
    public function validateCode($totp)
    {
        if (class_exists('ParagonIE\MultiFactor\Vendor\GoogleAuth') && (!empty($this->_secret))) {
            return $this->_getGoogleAuth()->validateCode($totp, strtotime("now"));
        }
        return false;
    }

    /**
     * Gets the encrypted value of the secret
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    /**
     * Gets the GoogleAuth object related this Totp
     * @return bool|GoogleAuth
     */
    private function _getGoogleAuth()
    {
        if (!$this->_googleAuth) {
            $this->_googleAuth = new GoogleAuth($this->getSecret());
        }
        return $this->_googleAuth;
    }
}
