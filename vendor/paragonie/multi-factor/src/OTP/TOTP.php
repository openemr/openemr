<?php
declare(strict_types=1);
namespace ParagonIE\MultiFactor\OTP;

use ParagonIE\ConstantTime\{
    Binary,
    Hex
};

/**
 * Class TOTP
 * @package ParagonIE\MultiFactor\OTP
 */
class TOTP implements OTPInterface
{
    /**
     * @var string
     */
    protected $algo;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $timeStep;

    /**
     * @var int
     */
    protected $timeZero;

    /**
     * TOTP constructor.
     *
     * @param int $timeZero        The start time for calculating the TOTP
     * @param int $timeStep        How many seconds should each TOTP live?
     * @param int $length          How many digits should each TOTP be?
     * @param string $algo         Hash function to use
     */
    public function __construct(
        int $timeZero = 0,
        int $timeStep = 30,
        int $length = 6,
        string $algo = 'sha1'
    ) {
        $this->timeZero = $timeZero;
        $this->timeStep = $timeStep;
        $this->length = $length;
        $this->algo = $algo;
    }

    /**
     * Generate a TOTP secret in accordance with RFC 6238
     *
     * @ref https://tools.ietf.org/html/rfc6238
     * @param string $sharedSecret The key to use for determining the TOTP
     * @param int $counterValue    Current time or HOTP counter
     * @return string
     * @throws \OutOfRangeException
     */
    public function getCode(
        string $sharedSecret,
        int $counterValue
    ): string {
        if ($this->length < 1 || $this->length > 10) {
            throw new \OutOfRangeException(
                'Length must be between 1 and 10, as a consequence of RFC 6238.'
            );
        }
        $msg = $this->getTValue($counterValue, true);
        $bytes = \hash_hmac($this->algo, $msg, $sharedSecret, true);

        $byteLen = Binary::safeStrlen($bytes);

        // Per the RFC
        $offset = \unpack('C', $bytes[$byteLen - 1])[1];
        $offset &= 0x0f;

        $unpacked = \array_values(
            \unpack('C*', Binary::safeSubstr($bytes, $offset, 4))
        );

        $intValue = (
            (($unpacked[0] & 0x7f) << 24)
            | (($unpacked[1] & 0xff) << 16)
            | (($unpacked[2] & 0xff) <<  8)
            | (($unpacked[3] & 0xff)      )
        );

        $intValue %= 10 ** $this->length;

        return \str_pad(
            (string) $intValue,
            $this->length,
            '0',
            \STR_PAD_LEFT
        );
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getTimeStep(): int
    {
        return $this->timeStep;
    }

    /**
     * Get the binary T value
     *
     * @param int $unixTimestamp
     * @param bool $rawOutput
     * @return string
     */
    protected function getTValue(
        int $unixTimestamp,
        bool $rawOutput = false
    ): string {
        $value = \intdiv(
            $unixTimestamp - $this->timeZero,
            $this->timeStep !== 0
                ? $this->timeStep
                : 1
        );
        $hex = \str_pad(
            \dechex($value),
            16,
            '0',
            STR_PAD_LEFT
        );
        if ($rawOutput) {
            return Hex::decode($hex);
        }
        return $hex;
    }
}