<?php

/**
 * Totp class used to generated MultiFactor App Based 2FA
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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

    /**
     * @param bool $secret - user secret or false to generate
     * @param string $_username - username to store in QR App
     */
    public function __construct($secret = false, private $_username = '')
    {
        if (!empty($secret)) {
            $this->_secret = $secret;
        } else {
            $tfa = new TwoFactorAuth($this->getQrProvider());
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

        $tfa = new TwoFactorAuth($this->getQrProvider(), $this->_issuer, 6, 30, Algorithm::Sha1);
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
        $tfa = new TwoFactorAuth($this->getQrProvider());
        return $tfa->verifyCode($this->_secret, $totp);
    }

    /**
     * Validates a TOTP and returns the RFC 6238 time slice
     * (floor(unix_ts / period)) that matched, or 0 on no match.
     *
     * Used by MfaUtils::checkTOTP for A-B-A replay protection: the
     * caller stores the returned slice alongside the registration and
     * rejects any subsequent code whose slice is not strictly greater.
     * RobThree's verifyCode accepts codes for slices T-1, T, T+1 (with
     * default $discrepancy = 1) — storing the matched slice and
     * requiring monotonic-forward progression is the RFC 6238-recommended
     * replay defense.
     *
     * @param $totp : unencrypted
     * @return int matched slice number, or 0 if the code did not verify
     */
    public function validateCodeAndGetSlice($totp): int
    {
        if (empty($totp) || empty($this->_secret)) {
            return 0;
        }
        $tfa = new TwoFactorAuth($this->getQrProvider());
        $slice = 0;
        // Only return the slice when verifyCode itself returns true.
        // Current RobThree implementation leaves $slice at 0 on
        // failure, so this is equivalent today — but treating the
        // return value as the source of truth (and $slice as a
        // secondary datum) hardens against any future RobThree API
        // drift where the by-ref parameter might be populated on
        // paths that don't match.
        $ok = $tfa->verifyCode($this->_secret, $totp, 1, null, $slice);
        return $ok ? $slice : 0;
    }

    /**
     * Gets the encrypted value of the secret
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    private function getQrProvider(): BaconQrCodeProvider
    {
        return new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg');
    }
}
