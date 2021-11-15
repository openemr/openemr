<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\BitArray;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Version;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use ReflectionMethod;

class MatrixUtilTest extends TestCase
{
    protected $methods = array();

    public function setUp()
    {
        // Hack to be able to test protected methods
        $reflection = new ReflectionClass('BaconQrCode\Encoder\MatrixUtil');

        foreach ($reflection->getMethods(ReflectionMethod::IS_STATIC) as $method) {
            $method->setAccessible(true);
            $this->methods[$method->getName()] = $method;
        }
    }

    public function testToString()
    {
        $matrix= new ByteMatrix(3, 3);
        $matrix->set(0, 0, 0);
        $matrix->set(1, 0, 1);
        $matrix->set(2, 0, 0);
        $matrix->set(0, 1, 1);
        $matrix->set(1, 1, 0);
        $matrix->set(2, 1, 1);
        $matrix->set(0, 2, -1);
        $matrix->set(1, 2, -1);
        $matrix->set(2, 2, -1);

        $expected = " 0 1 0\n 1 0 1\n      \n";
        $this->assertEquals($expected, $matrix->__toString());
    }

    public function testClearMatrix()
    {
        $matrix = new ByteMatrix(2, 2);
        MatrixUtil::clearMatrix($matrix);

        $this->assertEquals(-1, $matrix->get(0, 0));
        $this->assertEquals(-1, $matrix->get(1, 0));
        $this->assertEquals(-1, $matrix->get(0, 1));
        $this->assertEquals(-1, $matrix->get(1, 1));
    }

    public function testEmbedBasicPatterns1()
    {
        $matrix = new ByteMatrix(21, 21);
        MatrixUtil::clearMatrix($matrix);
        $this->methods['embedBasicPatterns']->invoke(
            null,
            Version::getVersionForNumber(1),
            $matrix
        );
        $expected = " 1 1 1 1 1 1 1 0           0 1 1 1 1 1 1 1\n"
                  . " 1 0 0 0 0 0 1 0           0 1 0 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0           0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0           0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0           0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0           0 1 0 0 0 0 0 1\n"
                  . " 1 1 1 1 1 1 1 0 1 0 1 0 1 0 1 1 1 1 1 1 1\n"
                  . " 0 0 0 0 0 0 0 0           0 0 0 0 0 0 0 0\n"
                  . "             1                            \n"
                  . "             0                            \n"
                  . "             1                            \n"
                  . "             0                            \n"
                  . "             1                            \n"
                  . " 0 0 0 0 0 0 0 0 1                        \n"
                  . " 1 1 1 1 1 1 1 0                          \n"
                  . " 1 0 0 0 0 0 1 0                          \n"
                  . " 1 0 1 1 1 0 1 0                          \n"
                  . " 1 0 1 1 1 0 1 0                          \n"
                  . " 1 0 1 1 1 0 1 0                          \n"
                  . " 1 0 0 0 0 0 1 0                          \n"
                  . " 1 1 1 1 1 1 1 0                          \n";

        $this->assertEquals($expected, $matrix->__toString());
    }

    public function testEmbedBasicPatterns2()
    {
        $matrix = new ByteMatrix(25, 25);
        MatrixUtil::clearMatrix($matrix);
        $this->methods['embedBasicPatterns']->invoke(
            null,
            Version::getVersionForNumber(2),
            $matrix
        );
        $expected = " 1 1 1 1 1 1 1 0                   0 1 1 1 1 1 1 1\n"
                  . " 1 0 0 0 0 0 1 0                   0 1 0 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0                   0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0                   0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0                   0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0                   0 1 0 0 0 0 0 1\n"
                  . " 1 1 1 1 1 1 1 0 1 0 1 0 1 0 1 0 1 0 1 1 1 1 1 1 1\n"
                  . " 0 0 0 0 0 0 0 0                   0 0 0 0 0 0 0 0\n"
                  . "             1                                    \n"
                  . "             0                                    \n"
                  . "             1                                    \n"
                  . "             0                                    \n"
                  . "             1                                    \n"
                  . "             0                                    \n"
                  . "             1                                    \n"
                  . "             0                                    \n"
                  . "             1                   1 1 1 1 1        \n"
                  . " 0 0 0 0 0 0 0 0 1               1 0 0 0 1        \n"
                  . " 1 1 1 1 1 1 1 0                 1 0 1 0 1        \n"
                  . " 1 0 0 0 0 0 1 0                 1 0 0 0 1        \n"
                  . " 1 0 1 1 1 0 1 0                 1 1 1 1 1        \n"
                  . " 1 0 1 1 1 0 1 0                                  \n"
                  . " 1 0 1 1 1 0 1 0                                  \n"
                  . " 1 0 0 0 0 0 1 0                                  \n"
                  . " 1 1 1 1 1 1 1 0                                  \n";

        $this->assertEquals($expected, $matrix->__toString());
    }

    public function testEmbedTypeInfo()
    {
        $matrix = new ByteMatrix(21, 21);
        MatrixUtil::clearMatrix($matrix);
        $this->methods['embedTypeInfo']->invoke(
            null,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::M),
            5,
            $matrix
        );
        $expected = "                 0                        \n"
                  . "                 1                        \n"
                  . "                 1                        \n"
                  . "                 1                        \n"
                  . "                 0                        \n"
                  . "                 0                        \n"
                  . "                                          \n"
                  . "                 1                        \n"
                  . " 1 0 0 0 0 0   0 1         1 1 0 0 1 1 1 0\n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                 0                        \n"
                  . "                 0                        \n"
                  . "                 0                        \n"
                  . "                 0                        \n"
                  . "                 0                        \n"
                  . "                 0                        \n"
                  . "                 1                        \n";

        $this->assertEquals($expected, $matrix->__toString());
    }

    public function testEmbedVersionInfo()
    {
        $matrix = new ByteMatrix(21, 21);
        MatrixUtil::clearMatrix($matrix);
        $this->methods['maybeEmbedVersionInfo']->invoke(
            null,
            Version::getVersionForNumber(7),
            $matrix
        );
        $expected = "                     0 0 1                \n"
                  . "                     0 1 0                \n"
                  . "                     0 1 0                \n"
                  . "                     0 1 1                \n"
                  . "                     1 1 1                \n"
                  . "                     0 0 0                \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . " 0 0 0 0 1 0                              \n"
                  . " 0 1 1 1 1 0                              \n"
                  . " 1 0 0 1 1 0                              \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n"
                  . "                                          \n";

        $this->assertEquals($expected, $matrix->__toString());
    }

    public function testEmbedDataBits()
    {
        $matrix = new ByteMatrix(21, 21);
        MatrixUtil::clearMatrix($matrix);
        $this->methods['embedBasicPatterns']->invoke(
            null,
            Version::getVersionForNumber(1),
            $matrix
        );

        $bits = new BitArray();
        $this->methods['embedDataBits']->invoke(
            null,
            $bits,
            -1,
            $matrix
        );

        $expected = " 1 1 1 1 1 1 1 0 0 0 0 0 0 0 1 1 1 1 1 1 1\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 0 0 0 1 0 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 0 0 0 0 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 0 0 0 0 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 0 0 0 0 0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 0 0 0 1 0 0 0 0 0 1\n"
                  . " 1 1 1 1 1 1 1 0 1 0 1 0 1 0 1 1 1 1 1 1 1\n"
                  . " 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 0 0 0 0 0 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 0 0 0 0 0 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 0 0 0 0 0 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 0 0 0 0 0 0 0 0 1 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 1 1 1 1 1 1 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 1 0 1 1 1 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 1 0 1 1 1 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 1 0 1 1 1 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n"
                  . " 1 1 1 1 1 1 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0\n";

        $this->assertEquals($expected, $matrix->__toString());
    }

    public function testBuildMatrix()
    {
        $bytes = array(
            32, 65, 205, 69, 41, 220, 46, 128, 236, 42, 159, 74, 221, 244, 169,
            239, 150, 138, 70, 237, 85, 224, 96, 74, 219 , 61
        );
        $bits = new BitArray();

        foreach ($bytes as $byte) {
            $bits->appendBits($byte, 8);
        }

        $matrix = new ByteMatrix(21, 21);
        MatrixUtil::buildMatrix(
            $bits,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::H),
            Version::getVersionForNumber(1),
            3,
            $matrix
        );

        $expected = " 1 1 1 1 1 1 1 0 0 1 1 0 0 0 1 1 1 1 1 1 1\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 0 0 0 1 0 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 0 0 1 0 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 1 0 0 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 1 1 0 0 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0 0 0 1 1 1 0 1 0 0 0 0 0 1\n"
                  . " 1 1 1 1 1 1 1 0 1 0 1 0 1 0 1 1 1 1 1 1 1\n"
                  . " 0 0 0 0 0 0 0 0 1 1 0 1 1 0 0 0 0 0 0 0 0\n"
                  . " 0 0 1 1 0 0 1 1 1 0 0 1 1 1 1 0 1 0 0 0 0\n"
                  . " 1 0 1 0 1 0 0 0 0 0 1 1 1 0 0 1 0 1 1 1 0\n"
                  . " 1 1 1 1 0 1 1 0 1 0 1 1 1 0 0 1 1 1 0 1 0\n"
                  . " 1 0 1 0 1 1 0 1 1 1 0 0 1 1 1 0 0 1 0 1 0\n"
                  . " 0 0 1 0 0 1 1 1 0 0 0 0 0 0 1 0 1 1 1 1 1\n"
                  . " 0 0 0 0 0 0 0 0 1 1 0 1 0 0 0 0 0 1 0 1 1\n"
                  . " 1 1 1 1 1 1 1 0 1 1 1 1 0 0 0 0 1 0 1 1 0\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 1 0 1 1 1 0 0 0 0 0\n"
                  . " 1 0 1 1 1 0 1 0 0 1 0 0 1 1 0 0 1 0 0 1 1\n"
                  . " 1 0 1 1 1 0 1 0 1 1 0 1 0 0 0 0 0 1 1 1 0\n"
                  . " 1 0 1 1 1 0 1 0 1 1 1 1 0 0 0 0 1 1 1 0 0\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 0 0 0 0 0 1 0 1 0 0\n"
                  . " 1 1 1 1 1 1 1 0 0 0 1 1 1 1 1 0 1 0 0 1 0\n";

        $this->assertEquals($expected, $matrix->__toString());
    }

    public function testFindMsbSet()
    {
        $this->assertEquals(0, $this->methods['findMsbSet']->invoke(null, 0));
        $this->assertEquals(1, $this->methods['findMsbSet']->invoke(null, 1));
        $this->assertEquals(8, $this->methods['findMsbSet']->invoke(null, 0x80));
        $this->assertEquals(32, $this->methods['findMsbSet']->invoke(null, 0x80000000));
    }

    public function testCalculateBchCode()
    {
        // Encoding of type information.
        // From Appendix C in JISX0510:2004 (p 65)
        $this->assertEquals(0xdc, $this->methods['calculateBchCode']->invoke(null, 5, 0x537));
        // From http://www.swetake.com/qr/qr6.html
        $this->assertEquals(0x1c2, $this->methods['calculateBchCode']->invoke(null, 0x13, 0x537));
        // From http://www.swetake.com/qr/qr11.html
        $this->assertEquals(0x214, $this->methods['calculateBchCode']->invoke(null, 0x1b, 0x537));

        // Encoding of version information.
        // From Appendix D in JISX0510:2004 (p 68)
        $this->assertEquals(0xc94, $this->methods['calculateBchCode']->invoke(null, 7, 0x1f25));
        $this->assertEquals(0x5bc, $this->methods['calculateBchCode']->invoke(null, 8, 0x1f25));
        $this->assertEquals(0xa99, $this->methods['calculateBchCode']->invoke(null, 9, 0x1f25));
        $this->assertEquals(0x4d3, $this->methods['calculateBchCode']->invoke(null, 10, 0x1f25));
        $this->assertEquals(0x9a6, $this->methods['calculateBchCode']->invoke(null, 20, 0x1f25));
        $this->assertEquals(0xd75, $this->methods['calculateBchCode']->invoke(null, 30, 0x1f25));
        $this->assertEquals(0xc69, $this->methods['calculateBchCode']->invoke(null, 40, 0x1f25));
    }

    public function testMakeVersionInfoBits()
    {
        // From Appendix D in JISX0510:2004 (p 68)
        $bits = new BitArray();
        $this->methods['makeVersionInfoBits']->invoke(null, Version::getVersionForNumber(7), $bits);
        $this->assertEquals(' ...XXXXX ..X..X.X ..', $bits->__toString());
    }

    public function testMakeTypeInfoBits()
    {
        // From Appendix D in JISX0510:2004 (p 68)
        $bits = new BitArray();
        $this->methods['makeTypeInfoBits']->invoke(null, new ErrorCorrectionLevel(ErrorCorrectionLevel::M), 5, $bits);
        $this->assertEquals(' X......X X..XXX.', $bits->__toString());
    }
}