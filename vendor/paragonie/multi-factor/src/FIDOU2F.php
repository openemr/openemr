<?php
declare(strict_types=1);
namespace ParagonIE\MultiFactor;

use ParagonIE\MultiFactor\OTP\{
    OTPInterface,
    TOTP
};

/**
 * Class FIDOU2F
 *
 * Implementation for the FIDO Alliance's U2F standard
 *
 * @package ParagonIE\MultiFactor
 */
class FIDOU2F implements MultiFactorInterface
{
    /**
     * @var OTPInterface
     */
    protected $otp;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * FIDOU2F constructor.
     *
     * @param string $secretKey
     * @param OTPInterface $otp
     */
    public function __construct(
        string $secretKey = '',
        OTPInterface $otp = null
    ) {
        $this->secretKey = $secretKey;
        if (!$otp) {
            $otp = new TOTP();
        }
        $this->otp = $otp;
    }

    /**
     * Generate a TOTP code for 2FA
     *
     * @param int $counterValue
     * @return string
     */
    public function generateCode(int $counterValue = 0): string
    {
        return $this->otp->getCode(
            $this->secretKey,
            $counterValue
        );
    }

    /**
     * Validate a user-provided code
     *
     * @param string $code
     * @param int $counterValue
     * @return bool
     */
    public function validateCode(string $code, int $counterValue = 0): bool
    {
        $expected = $this->generateCode($counterValue);
        return \hash_equals($code, $expected);
    }
}
