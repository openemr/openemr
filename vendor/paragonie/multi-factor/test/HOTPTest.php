<?php
declare(strict_types=1);

use \ParagonIE\ConstantTime\Hex;
use \ParagonIE\MultiFactor\OTP\HOTP;

/**
 * Class HOTPTest
 */
class HOPTTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test vectors from RFC 6238
     */
    public function testTOTP()
    {
        $seed = Hex::decode(
            "3132333435363738393031323334353637383930"
        );
        $hotp = new HOTP();

        $this->assertSame('755224', $hotp->getCode($seed, 0));
        $this->assertSame('287082', $hotp->getCode($seed, 1));
        $this->assertSame('359152', $hotp->getCode($seed, 2));
        $this->assertSame('969429', $hotp->getCode($seed, 3));
        $this->assertSame('338314', $hotp->getCode($seed, 4));
        $this->assertSame('254676', $hotp->getCode($seed, 5));
        $this->assertSame('287922', $hotp->getCode($seed, 6));
        $this->assertSame('162583', $hotp->getCode($seed, 7));
        $this->assertSame('399871', $hotp->getCode($seed, 8));
        $this->assertSame('520489', $hotp->getCode($seed, 9));
    }
}
