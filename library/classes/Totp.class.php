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

use RobThree\Auth\Algorithm;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

/**
 * Class Totp
 */
class Totp
{
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

        if (!empty($secret)) {
            $this->_secret = $secret;
        } else {
            $tfa = new TwoFactorAuth();
            // Shared key (per rfc6238 and rfc4226) should be 20 bytes (160 bits) and encoded in base32, which should
            //   be 32 characters in base32 (below line does all this)
            $this->_secret = $tfa->createSecret(160);
            if (empty($this->_secret)) {
                error_log('OpenEMR Error : MFA was unable to create secret - exiting');
                die();
            }
        }
    }

    /**
     * Generates a QR code code
     * @return bool|string
     */
    public function generateQrCode()
    {
        if (empty($this->_issuer) || empty($this->_username) || empty($this->_secret)) {
            return false;
        }

        $qrCodeProvider = new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg');
        $tfa = new TwoFactorAuth($this->_issuer, 6, 30, Algorithm::Sha1, $qrCodeProvider);
        $qr = $tfa->getQRCodeImageAsDataUri($this->_username, $this->_secret);
        if (empty($qr)) {
            return false;
        }
        return $qr;
    }

    /**
     * Validates a TOTP
     * @param $totp : unencrypted
     * @return bool
     */
    public function validateCode($totp)
    {
        if (empty($totp) || empty($this->_secret)) {
            return false;
        }
        $tfa = new TwoFactorAuth();
        return $tfa->verifyCode($this->_secret, $totp);
    }

    /**
     * Gets the encrypted value of the secret
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }
}
