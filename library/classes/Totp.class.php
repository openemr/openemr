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
use ParagonIE\MultiFactor\Vendor\GoogleAuth;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;
use Defuse\Crypto\Crypto;

/**
 * Class Totp
 */
class Totp
{

    /** @var bool|GoogleAuth  */
    private $_googleAuth = false;
    /** @var bool|string  */
    private $_qrFileName = false;
    /** @var bool|string - user's hashed password */
    private $_hashedPass = false;
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
            $this->_secret = $this->_createRandString(16);
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
            $tempFilePath = $this->_getQrFilePath();
            $this->_getGoogleAuth()->makeQRCode(null, $tempFilePath, $this->_username, $this->_issuer);

            // Gets the image file data to return
            $imageInfo = getimagesize($tempFilePath);
            $data = base64_encode(file_get_contents($tempFilePath));
            $image = sprintf('data:%s;base64,%s', $imageInfo['mime'], $data);

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
        if (class_exists('ParagonIE\MultiFactor\Vendor\GoogleAuth')) {

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
     * Gets the file name of the string as a png
     * @return string
     */
    private function _getQrFilePath()
    {
        if (!$this->_qrFileName) {
            $this->_qrFileName = md5($this->getSecret());
        }
        return $this->_qrFileName.".png";
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

    /**
     * Creates a random string of given length
     * @param $len - length of string
     * @return string
     */
    private function _createRandString($len)
    {
        return substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", $len)), 0, $len);
    }

}
?>