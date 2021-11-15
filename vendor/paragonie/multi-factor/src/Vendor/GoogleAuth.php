<?php
declare(strict_types=1);
namespace ParagonIE\MultiFactor\Vendor;

use \BaconQrCode\Writer;
use \ParagonIE\ConstantTime\Base32;
use \ParagonIE\MultiFactor\FIDOU2F;
use \ParagonIE\MultiFactor\OTP\{
    HOTP,
    TOTP
};

/**
 * Class GoogleAuth
 * @package ParagonIE\MultiFactor\Vendor
 */
class GoogleAuth extends FIDOU2F
{

    /**
     * @var int
     */
    public $defaultQRCodeHeight = 384;

    /**
     * @var int
     */
    public $defaultQRCodeWidth = 384;

    /**
     * Create a QR code to load the key onto the device
     *
     * @param Writer $qrCodeWriter
     * @param string $outFile        Where to store the QR code?
     * @param string $username       Username or email address
     * @param string $issuer         Optional
     * @param string $label          Optional
     * @param int $initialCounter    Initial counter value
     * @throws \Exception
     */
    public function makeQRCode(
        Writer $qrCodeWriter = null,
        string $outFile = 'php://output',
        string $username = '',
        string $issuer = '',
        string $label = '',
        int $initialCounter = 0
    ) {

        // Sane default; You can dependency-inject a replacement:
        if (!$qrCodeWriter) {
            $renderer = new \BaconQrCode\Renderer\Image\Png();
            $renderer->setHeight($this->defaultQRCodeWidth);
            $renderer->setWidth($this->defaultQRCodeHeight);
            $qrCodeWriter = new \BaconQrCode\Writer($renderer);
        }

        if ($this->otp instanceof TOTP) {
            $message = 'otpauth://totp/';
        } elseif ($this->otp instanceof HOTP) {
            $message = 'otpauth://hotp/';
        } else {
            throw new \Exception('Not implemented');
        }
        if ($label) {
            $message .= \urlencode(
                \str_replace(':', '', $label)
            );
            $message .= ':';
        }
        $message .= \urlencode($username);
        $args = [
            'secret' => Base32::encode($this->secretKey)
        ];
        if ($issuer) {
            $args['issuer'] = $issuer;
        }
        $args['digits'] = $this->otp->getLength();
        if ($this->otp instanceof TOTP) {
            $args['period'] = $this->otp->getTimeStep();
        } elseif ($this->otp instanceof HOTP) {
            $args['counter'] = $initialCounter;
        }
        $message .= '?' . \http_build_query($args);

        $qrCodeWriter->writeFile($message, $outFile);
    }
}
