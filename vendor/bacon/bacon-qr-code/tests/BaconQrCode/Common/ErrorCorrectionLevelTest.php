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

class ErrorCorrectionLevelTest extends TestCase
{
    public function testCreationThrowsNoException()
    {
        new ErrorCorrectionLevel(ErrorCorrectionLevel::M);
        new ErrorCorrectionLevel(ErrorCorrectionLevel::L);
        new ErrorCorrectionLevel(ErrorCorrectionLevel::H);
        new ErrorCorrectionLevel(ErrorCorrectionLevel::Q);
    }

    public function testBitsMatchConstants()
    {
        $this->assertEquals(0x0, ErrorCorrectionLevel::M);
        $this->assertEquals(0x1, ErrorCorrectionLevel::L);
        $this->assertEquals(0x2, ErrorCorrectionLevel::H);
        $this->assertEquals(0x3, ErrorCorrectionLevel::Q);
    }

    public function testInvalidErrorCorrectionLevelThrowsException()
    {
        $this->setExpectedException(
            'BaconQrCode\Exception\UnexpectedValueException',
            'Value not a const in enum BaconQrCode\Common\ErrorCorrectionLevel'
        );
        new ErrorCorrectionLevel(4);
    }
}