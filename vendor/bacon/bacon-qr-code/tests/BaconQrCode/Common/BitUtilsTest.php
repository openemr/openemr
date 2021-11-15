<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

use PHPUnit_Framework_TestCase as TestCase;

class BitUtilsTest extends TestCase
{
    public function testUnsignedRightShift()
    {
        $this->assertEquals(1, BitUtils::unsignedRightShift(1, 0));
        $this->assertEquals(1, BitUtils::unsignedRightShift(10, 3));
        $this->assertEquals(536870910, BitUtils::unsignedRightShift(-10, 3));
    }

    public function testNumberOfTrailingZeros()
    {
        $this->assertEquals(32, BitUtils::numberOfTrailingZeros(0));
        $this->assertEquals(1, BitUtils::numberOfTrailingZeros(10));
        $this->assertEquals(0, BitUtils::numberOfTrailingZeros(15));
        $this->assertEquals(2, BitUtils::numberOfTrailingZeros(20));
    }
}