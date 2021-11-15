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

class ModeTest extends TestCase
{
    public function testCreationThrowsNoException()
    {
        new Mode(Mode::TERMINATOR);
        new Mode(Mode::NUMERIC);
        new Mode(Mode::ALPHANUMERIC);
        new Mode(Mode::BYTE);
        new Mode(Mode::KANJI);
    }

    public function testBitsMatchConstants()
    {
        $this->assertEquals(0x0, Mode::TERMINATOR);
        $this->assertEquals(0x1, Mode::NUMERIC);
        $this->assertEquals(0x2, Mode::ALPHANUMERIC);
        $this->assertEquals(0x4, Mode::BYTE);
        $this->assertEquals(0x8, Mode::KANJI);
    }

    public function testInvalidModeThrowsException()
    {
        $this->setExpectedException(
            'BaconQrCode\Exception\UnexpectedValueException',
            'Value not a const in enum BaconQrCode\Common\Mode'
        );
        new Mode(10);
    }
}