<?php
declare(strict_types=1);
namespace ParagonIE\MultiFactor\OTP;

use ParagonIE\ConstantTime\{
    Binary,
    Hex
};

/**
 * Class HOTP
 * @package ParagonIE\MultiFactor\OTP
 */
class HOTP implements OTPInterface
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
     * HOTP constructor.
     *
     * @param int $length          How many digits should each HOTP be?
     * @param string $algo         Hash function to use
     */
    public function __construct(
        int $length = 6,
        string $algo = 'sha1'
    ) {
        $this->length = $length;
        $this->algo = $algo;
    }

    /**
     * Generate a HOTP secret in accordance with RFC 4226
     *
     * @ref https://tools.ietf.org/html/rfc4226
     * @param string $sharedSecret The key to use for determining the HOTP
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
     * Get the binary T value
     *
     * @param int $unixTimestamp
     * @param bool $rawOutput
     * @return string
     */
    protected function getTValue(
        int $counter,
        bool $rawOutput = false
    ): string {
        $hex = \str_pad(
            \dechex($counter),
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