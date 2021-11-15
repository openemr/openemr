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

class FormatInformationTest extends TestCase
{
    protected $maskedTestFormatInfo = 0x2bed;
    protected $unmaskedTestFormatInfo;

    public function setUp()
    {
        $this->unmaskedTestFormatInfo = $this->maskedTestFormatInfo ^ 0x5412;
    }


    public function testBitsDiffering()
    {
        $this->assertEquals(0, FormatInformation::numBitsDiffering(1, 1));
        $this->assertEquals(1, FormatInformation::numBitsDiffering(0, 2));
        $this->assertEquals(2, FormatInformation::numBitsDiffering(1, 2));
        $this->assertEquals(32, FormatInformation::numBitsDiffering(-1, 0));
    }

    public function testDecode()
    {
        $expected = FormatInformation::decodeFormatInformation(
            $this->maskedTestFormatInfo,
            $this->maskedTestFormatInfo
        );

        $this->assertNotNull($expected);
        $this->assertEquals(7, $expected->getDataMask());
        $this->assertEquals(ErrorCorrectionLevel::Q, $expected->getErrorCorrectionLevel()->get());

        $this->assertEquals(
            $expected,
            FormatInformation::decodeFormatInformation(
                $this->unmaskedTestFormatInfo,
                $this->maskedTestFormatInfo
            )
        );
    }

    public function testDecodeWithBitDifference()
    {
        $expected = FormatInformation::decodeFormatInformation(
            $this->maskedTestFormatInfo,
            $this->maskedTestFormatInfo
        );

        $this->assertEquals(
            $expected,
            FormatInformation::decodeFormatInformation(
                $this->maskedTestFormatInfo ^ 0x1,
                $this->maskedTestFormatInfo ^ 0x1
            )
        );
        $this->assertEquals(
            $expected,
            FormatInformation::decodeFormatInformation(
                $this->maskedTestFormatInfo ^ 0x3,
                $this->maskedTestFormatInfo ^ 0x3
            )
        );
        $this->assertEquals(
            $expected,
            FormatInformation::decodeFormatInformation(
                $this->maskedTestFormatInfo ^ 0x7,
                $this->maskedTestFormatInfo ^ 0x7
            )
        );
        $this->assertNull(
            FormatInformation::decodeFormatInformation(
                $this->maskedTestFormatInfo ^ 0xf,
                $this->maskedTestFormatInfo ^ 0xf
            )
        );
    }

    public function testDecodeWithMisRead()
    {
        $expected = FormatInformation::decodeFormatInformation(
            $this->maskedTestFormatInfo,
            $this->maskedTestFormatInfo
        );

        $this->assertEquals(
            $expected,
            FormatInformation::decodeFormatInformation(
                $this->maskedTestFormatInfo ^ 0x3,
                $this->maskedTestFormatInfo ^ 0xf
            )
        );
    }
}