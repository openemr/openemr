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
use BaconQrCode\Common\Mode;
use BaconQrCode\Common\Version;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use ReflectionMethod;
use SplFixedArray;

class EncoderTest extends TestCase
{
    protected $methods = array();

    public function setUp()
    {
        // Hack to be able to test protected methods
        $reflection = new ReflectionClass('BaconQrCode\Encoder\Encoder');

        foreach ($reflection->getMethods(ReflectionMethod::IS_STATIC) as $method) {
            $method->setAccessible(true);
            $this->methods[$method->getName()] = $method;
        }
    }

    public function testGetAlphanumericCode()
    {
        // The first ten code points are numbers.
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($i, $this->methods['getAlphanumericCode']->invoke(null, ord('0') + $i));
        }

        // The next 26 code points are capital alphabet letters.
        for ($i = 10; $i < 36; $i++) {
            // The first ten code points are numbers
            $this->assertEquals($i, $this->methods['getAlphanumericCode']->invoke(null, ord('A') + $i - 10));
        }

        // Others are symbol letters.
        $this->assertEquals(36, $this->methods['getAlphanumericCode']->invoke(null, ' '));
        $this->assertEquals(37, $this->methods['getAlphanumericCode']->invoke(null, '$'));
        $this->assertEquals(38, $this->methods['getAlphanumericCode']->invoke(null, '%'));
        $this->assertEquals(39, $this->methods['getAlphanumericCode']->invoke(null, '*'));
        $this->assertEquals(40, $this->methods['getAlphanumericCode']->invoke(null, '+'));
        $this->assertEquals(41, $this->methods['getAlphanumericCode']->invoke(null, '-'));
        $this->assertEquals(42, $this->methods['getAlphanumericCode']->invoke(null, '.'));
        $this->assertEquals(43, $this->methods['getAlphanumericCode']->invoke(null, '/'));
        $this->assertEquals(44, $this->methods['getAlphanumericCode']->invoke(null, ':'));

        // Should return -1 for other letters.
        $this->assertEquals(-1, $this->methods['getAlphanumericCode']->invoke(null, 'a'));
        $this->assertEquals(-1, $this->methods['getAlphanumericCode']->invoke(null, '#'));
        $this->assertEquals(-1, $this->methods['getAlphanumericCode']->invoke(null, "\0"));
    }

    public function testChooseMode()
    {
        // Numeric mode
        $this->assertSame(Mode::NUMERIC, $this->methods['chooseMode']->invoke(null, '0')->get());
        $this->assertSame(Mode::NUMERIC, $this->methods['chooseMode']->invoke(null, '0123456789')->get());

        // Alphanumeric mode
        $this->assertSame(Mode::ALPHANUMERIC, $this->methods['chooseMode']->invoke(null, 'A')->get());
        $this->assertSame(Mode::ALPHANUMERIC, $this->methods['chooseMode']->invoke(null, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:')->get());

        // 8-bit byte mode
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, 'a')->get());
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, '#')->get());
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, '')->get());

        // AIUE in Hiragana in SHIFT-JIS
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, "\x8\xa\x8\xa\x8\xa\x8\xa6")->get());

        // Nihon in Kanji in SHIFT-JIS
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, "\x9\xf\x9\x7b")->get());

        // Sou-Utso-Byou in Kanji in SHIFT-JIS
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, "\xe\x4\x9\x5\x9\x61")->get());
    }

    public function testEncode()
    {
        $qrCode   = Encoder::encode('ABCDEF', new ErrorCorrectionLevel(ErrorCorrectionLevel::H));
        $expected = "<<\n"
                  . " mode: ALPHANUMERIC\n"
                  . " ecLevel: H\n"
                  . " version: 1\n"
                  . " maskPattern: 0\n"
                  . " matrix:\n"
                  . " 1 1 1 1 1 1 1 0 1 1 1 1 0 0 1 1 1 1 1 1 1\n"
                  . " 1 0 0 0 0 0 1 0 0 1 1 1 0 0 1 0 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 0 1 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 1 1 1 0 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 1 1 0 0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0 0 1 0 0 0 0 1 0 0 0 0 0 1\n"
                  . " 1 1 1 1 1 1 1 0 1 0 1 0 1 0 1 1 1 1 1 1 1\n"
                  . " 0 0 0 0 0 0 0 0 0 0 1 0 1 0 0 0 0 0 0 0 0\n"
                  . " 0 0 1 0 1 1 1 0 1 1 0 0 1 1 0 0 0 1 0 0 1\n"
                  . " 1 0 1 1 1 0 0 1 0 0 0 1 0 1 0 0 0 0 0 0 0\n"
                  . " 0 0 1 1 0 0 1 0 1 0 0 0 1 0 1 0 1 0 1 1 0\n"
                  . " 1 1 0 1 0 1 0 1 1 1 0 1 0 1 0 0 0 0 0 1 0\n"
                  . " 0 0 1 1 0 1 1 1 1 0 0 0 1 0 1 0 1 1 1 1 0\n"
                  . " 0 0 0 0 0 0 0 0 1 0 0 1 1 1 0 1 0 1 0 0 0\n"
                  . " 1 1 1 1 1 1 1 0 0 0 1 0 1 0 1 1 0 0 0 0 1\n"
                  . " 1 0 0 0 0 0 1 0 1 1 1 1 0 1 0 1 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 1 0 1 1 0 1 0 1 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 1 0 1 1 1 1 0 1 0 1 0\n"
                  . " 1 0 1 1 1 0 1 0 1 0 0 0 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0 0 1 1 0 1 1 0 1 0 0 0 1 1\n"
                  . " 1 1 1 1 1 1 1 0 0 0 0 0 0 0 0 0 1 0 1 0 1\n"
                  . ">>\n";

        $this->assertEquals($expected, $qrCode->__toString());
    }

    public function testSimpleUtf8Eci()
    {
        $qrCode   = Encoder::encode('hello', new ErrorCorrectionLevel(ErrorCorrectionLevel::H), 'utf-8');
        $expected = "<<\n"
                  . " mode: BYTE\n"
                  . " ecLevel: H\n"
                  . " version: 1\n"
                  . " maskPattern: 3\n"
                  . " matrix:\n"
                  . " 1 1 1 1 1 1 1 0 0 0 0 0 0 0 1 1 1 1 1 1 1\n"
                  . " 1 0 0 0 0 0 1 0 0 0 1 0 1 0 1 0 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 0 1 0 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 1 0 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 1 0 1 0 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 0 1 0 1 0 0 0 0 0 1\n"
                  . " 1 1 1 1 1 1 1 0 1 0 1 0 1 0 1 1 1 1 1 1 1\n"
                  . " 0 0 0 0 0 0 0 0 1 1 1 0 0 0 0 0 0 0 0 0 0\n"
                  . " 0 0 1 1 0 0 1 1 1 1 0 0 0 1 1 0 1 0 0 0 0\n"
                  . " 0 0 1 1 1 0 0 0 0 0 1 1 0 0 0 1 0 1 1 1 0\n"
                  . " 0 1 0 1 0 1 1 1 0 1 0 1 0 0 0 0 0 1 1 1 1\n"
                  . " 1 1 0 0 1 0 0 1 1 0 0 1 1 1 1 0 1 0 1 1 0\n"
                  . " 0 0 0 0 1 0 1 1 1 1 0 0 0 0 0 1 0 0 1 0 0\n"
                  . " 0 0 0 0 0 0 0 0 1 1 1 1 0 0 1 1 1 0 0 0 1\n"
                  . " 1 1 1 1 1 1 1 0 1 1 1 0 1 0 1 1 0 0 1 0 0\n"
                  . " 1 0 0 0 0 0 1 0 0 0 1 0 0 1 1 1 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 0 0 0 0 1 1 0 0 0 0 0\n"
                  . " 1 0 1 1 1 0 1 0 1 1 1 0 1 0 0 0 1 1 0 0 0\n"
                  . " 1 0 1 1 1 0 1 0 1 1 0 0 0 1 0 0 1 0 0 0 0\n"
                  . " 1 0 0 0 0 0 1 0 0 0 0 1 1 0 1 0 1 0 1 1 0\n"
                  . " 1 1 1 1 1 1 1 0 0 1 0 1 1 1 0 1 1 0 0 0 0\n"
                  . ">>\n";

        $this->assertEquals($expected, $qrCode->__toString());
    }

    public function testAppendModeInfo()
    {
        $bits = new BitArray();
        $this->methods['appendModeInfo']->invoke(null, new Mode(Mode::NUMERIC), $bits);
        $this->assertEquals(' ...X', $bits->__toString());
    }

    public function testAppendLengthInfo()
    {
        // 1 letter (1/1), 10 bits.
        $bits = new BitArray();
        $this->methods['appendLengthInfo']->invoke(
            null,
            1,
            Version::getVersionForNumber(1),
            new Mode(Mode::NUMERIC),
            $bits
        );
        $this->assertEquals(' ........ .X', $bits->__toString());

        // 2 letters (2/1), 11 bits.
        $bits = new BitArray();
        $this->methods['appendLengthInfo']->invoke(
            null,
            2,
            Version::getVersionForNumber(10),
            new Mode(Mode::ALPHANUMERIC),
            $bits
        );
        $this->assertEquals(' ........ .X.', $bits->__toString());

        // 255 letters (255/1), 16 bits.
        $bits = new BitArray();
        $this->methods['appendLengthInfo']->invoke(
            null,
            255,
            Version::getVersionForNumber(27),
            new Mode(Mode::BYTE),
            $bits
        );
        $this->assertEquals(' ........ XXXXXXXX', $bits->__toString());

        // 512 letters (1024/2), 12 bits.
        $bits = new BitArray();
        $this->methods['appendLengthInfo']->invoke(
            null,
            512,
            Version::getVersionForNumber(40),
            new Mode(Mode::KANJI),
            $bits
        );
        $this->assertEquals(' ..X..... ....', $bits->__toString());
    }

    public function testAppendBytes()
    {
        // Should use appendNumericBytes.
        // 1 = 01 = 0001 in 4 bits.
        $bits = new BitArray();
        $this->methods['appendBytes']->invoke(
            null,
            '1',
            new Mode(Mode::NUMERIC),
            $bits,
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->assertEquals(' ...X', $bits->__toString());

        // Should use appendAlphaNumericBytes.
        // A = 10 = 0xa = 001010 in 6 bits.
        $bits = new BitArray();
        $this->methods['appendBytes']->invoke(
            null,
            'A',
            new Mode(Mode::ALPHANUMERIC),
            $bits,
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->assertEquals(' ..X.X.', $bits->__toString());

        // Should use append8BitBytes.
        // 0x61, 0x62, 0x63
        $bits = new BitArray();
        $this->methods['appendBytes']->invoke(
            null,
            'abc',
            new Mode(Mode::BYTE),
            $bits,
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->assertEquals(' .XX....X .XX...X. .XX...XX', $bits->__toString());

        // Should use appendKanjiBytes.
        // 0x93, 0x5f
        $bits = new BitArray();
        $this->methods['appendBytes']->invoke(
            null,
            "\x93\x5f",
            new Mode(Mode::KANJI),
            $bits,
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->assertEquals(' .XX.XX.. XXXXX', $bits->__toString());

        // Lower letters such as 'a' cannot be encoded in alphanumeric mode.
        $this->setExpectedException(
            'BaconQrCode\Exception\WriterException',
            'Invalid alphanumeric code'
        );
        $this->methods['appendBytes']->invoke(
            null,
            "a",
            new Mode(Mode::ALPHANUMERIC),
            $bits,
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
    }

    public function testTerminateBits()
    {
        $bits = new BitArray();
        $this->methods['terminateBits']->invoke(null, 0, $bits);
        $this->assertEquals('', $bits->__toString());

        $bits = new BitArray();
        $this->methods['terminateBits']->invoke(null, 1, $bits);
        $this->assertEquals(' ........', $bits->__toString());

        $bits = new BitArray();
        $bits->appendBits(0, 3);
        $this->methods['terminateBits']->invoke(null, 1, $bits);
        $this->assertEquals(' ........', $bits->__toString());

        $bits = new BitArray();
        $bits->appendBits(0, 5);
        $this->methods['terminateBits']->invoke(null, 1, $bits);
        $this->assertEquals(' ........', $bits->__toString());

        $bits = new BitArray();
        $bits->appendBits(0, 8);
        $this->methods['terminateBits']->invoke(null, 1, $bits);
        $this->assertEquals(' ........', $bits->__toString());

        $bits = new BitArray();
        $this->methods['terminateBits']->invoke(null, 2, $bits);
        $this->assertEquals(' ........ XXX.XX..', $bits->__toString());

        $bits = new BitArray();
        $bits->appendBits(0, 1);
        $this->methods['terminateBits']->invoke(null, 3, $bits);
        $this->assertEquals(' ........ XXX.XX.. ...X...X', $bits->__toString());
    }

    public function testGetNumDataBytesAndNumEcBytesForBlockId()
    {
        // Version 1-H.
        list($numDataBytes, $numEcBytes) = $this->methods['getNumDataBytesAndNumEcBytesForBlockId']->invoke(null, 26, 9, 1, 0);
        $this->assertEquals(9, $numDataBytes);
        $this->assertEquals(17, $numEcBytes);

        // Version 3-H.  2 blocks.
        list($numDataBytes, $numEcBytes) = $this->methods['getNumDataBytesAndNumEcBytesForBlockId']->invoke(null, 70, 26, 2, 0);
        $this->assertEquals(13, $numDataBytes);
        $this->assertEquals(22, $numEcBytes);
        list($numDataBytes, $numEcBytes) = $this->methods['getNumDataBytesAndNumEcBytesForBlockId']->invoke(null, 70, 26, 2, 1);
        $this->assertEquals(13, $numDataBytes);
        $this->assertEquals(22, $numEcBytes);

        // Version 7-H. (4 + 1) blocks.
        list($numDataBytes, $numEcBytes) = $this->methods['getNumDataBytesAndNumEcBytesForBlockId']->invoke(null, 196, 66, 5, 0);
        $this->assertEquals(13, $numDataBytes);
        $this->assertEquals(26, $numEcBytes);
        list($numDataBytes, $numEcBytes) = $this->methods['getNumDataBytesAndNumEcBytesForBlockId']->invoke(null, 196, 66, 5, 4);
        $this->assertEquals(14, $numDataBytes);
        $this->assertEquals(26, $numEcBytes);

        // Version 40-H. (20 + 61) blocks.
        list($numDataBytes, $numEcBytes) = $this->methods['getNumDataBytesAndNumEcBytesForBlockId']->invoke(null, 3706, 1276, 81, 0);
        $this->assertEquals(15, $numDataBytes);
        $this->assertEquals(30, $numEcBytes);
        list($numDataBytes, $numEcBytes) = $this->methods['getNumDataBytesAndNumEcBytesForBlockId']->invoke(null, 3706, 1276, 81, 20);
        $this->assertEquals(16, $numDataBytes);
        $this->assertEquals(30, $numEcBytes);
        list($numDataBytes, $numEcBytes) = $this->methods['getNumDataBytesAndNumEcBytesForBlockId']->invoke(null, 3706, 1276, 81, 80);
        $this->assertEquals(16, $numDataBytes);
        $this->assertEquals(30, $numEcBytes);
    }

    public function testInterleaveWithEcBytes()
    {
        $dataBytes = SplFixedArray::fromArray(array(32, 65, 205, 69, 41, 220, 46, 128, 236), false);
        $in        = new BitArray();

        foreach ($dataBytes as $dataByte) {
            $in->appendBits($dataByte, 8);
        }

        $outBits  = $this->methods['interleaveWithEcBytes']->invoke(null, $in, 26, 9, 1);
        $expected = SplFixedArray::fromArray(array(
            // Data bytes.
            32, 65, 205, 69, 41, 220, 46, 128, 236,
            // Error correction bytes.
            42, 159, 74, 221, 244, 169, 239, 150, 138, 70, 237, 85, 224, 96, 74, 219, 61,
        ), false);

        $out = $outBits->toBytes(0, count($expected));

        $this->assertEquals($expected, $out);
    }

    public function testAppendNumericBytes()
    {
        // 1 = 01 = 0001 in 4 bits.
        $bits = new BitArray();
        $this->methods['appendNumericBytes']->invoke(null, '1', $bits);
        $this->assertEquals(' ...X', $bits->__toString());

        // 12 = 0xc = 0001100 in 7 bits.
        $bits = new BitArray();
        $this->methods['appendNumericBytes']->invoke(null, '12', $bits);
        $this->assertEquals(' ...XX..', $bits->__toString());

        // 123 = 0x7b = 0001111011 in 10 bits.
        $bits = new BitArray();
        $this->methods['appendNumericBytes']->invoke(null, '123', $bits);
        $this->assertEquals(' ...XXXX. XX', $bits->__toString());

        // 1234 = "123" + "4" = 0001111011 + 0100 in 14 bits.
        $bits = new BitArray();
        $this->methods['appendNumericBytes']->invoke(null, '1234', $bits);
        $this->assertEquals(' ...XXXX. XX.X..', $bits->__toString());

        // Empty
        $bits = new BitArray();
        $this->methods['appendNumericBytes']->invoke(null, '', $bits);
        $this->assertEquals('', $bits->__toString());
    }

    public function testAppendAlphanumericBytes()
    {
        $bits = new BitArray();
        $this->methods['appendAlphanumericBytes']->invoke(null, 'A', $bits);
        $this->assertEquals(' ..X.X.', $bits->__toString());

        $bits = new BitArray();
        $this->methods['appendAlphanumericBytes']->invoke(null, 'AB', $bits);
        $this->assertEquals(' ..XXX..X X.X', $bits->__toString());

        $bits = new BitArray();
        $this->methods['appendAlphanumericBytes']->invoke(null, 'ABC', $bits);
        $this->assertEquals(' ..XXX..X X.X..XX. .', $bits->__toString());

        // Empty
        $bits = new BitArray();
        $this->methods['appendAlphanumericBytes']->invoke(null, '', $bits);
        $this->assertEquals('', $bits->__toString());

        // Invalid data
        $this->setExpectedException('BaconQrCode\Exception\WriterException', 'Invalid alphanumeric code');
        $bits = new BitArray();
        $this->methods['appendAlphanumericBytes']->invoke(null, 'abc', $bits);
    }

    public function testAppend8BitBytes()
    {
        // 0x61, 0x62, 0x63
        $bits = new BitArray();
        $this->methods['append8BitBytes']->invoke(null, 'abc', $bits, Encoder::DEFAULT_BYTE_MODE_ECODING);
        $this->assertEquals(' .XX....X .XX...X. .XX...XX', $bits->__toString());

        // Empty
        $bits = new BitArray();
        $this->methods['append8BitBytes']->invoke(null, '', $bits, Encoder::DEFAULT_BYTE_MODE_ECODING);
        $this->assertEquals('', $bits->__toString());
    }

    public function testAppendKanjiBytes()
    {
        // Numbers are from page 21 of JISX0510:2004
        $bits = new BitArray();
        $this->methods['appendKanjiBytes']->invoke(null, "\x93\x5f", $bits);
        $this->assertEquals(' .XX.XX.. XXXXX', $bits->__toString());

        $this->methods['appendKanjiBytes']->invoke(null, "\xe4\xaa", $bits);
        $this->assertEquals(' .XX.XX.. XXXXXXX. X.X.X.X. X.', $bits->__toString());
    }

    public function testGenerateEcBytes()
    {
        // Numbers are from http://www.swetake.com/qr/qr3.html and
        // http://www.swetake.com/qr/qr9.html
        $dataBytes = SplFixedArray::fromArray(array(32, 65, 205, 69, 41, 220, 46, 128, 236), false);
        $ecBytes   = $this->methods['generateEcBytes']->invoke(null, $dataBytes, 17);
        $expected  = SplFixedArray::fromArray(array(42, 159, 74, 221, 244, 169, 239, 150, 138, 70, 237, 85, 224, 96, 74, 219, 61), false);
        $this->assertEquals($expected, $ecBytes);

        $dataBytes = SplFixedArray::fromArray(array(67, 70, 22, 38, 54, 70, 86, 102, 118, 134, 150, 166, 182, 198, 214), false);
        $ecBytes   = $this->methods['generateEcBytes']->invoke(null, $dataBytes, 18);
        $expected  = SplFixedArray::fromArray(array(175, 80, 155, 64, 178, 45, 214, 233, 65, 209, 12, 155, 117, 31, 140, 214, 27, 187), false);
        $this->assertEquals($expected, $ecBytes);

        // High-order zero coefficient case.
        $dataBytes = SplFixedArray::fromArray(array(32, 49, 205, 69, 42, 20, 0, 236, 17), false);
        $ecBytes   = $this->methods['generateEcBytes']->invoke(null, $dataBytes, 17);
        $expected  = SplFixedArray::fromArray(array(0, 3, 130, 179, 194, 0, 55, 211, 110, 79, 98, 72, 170, 96, 211, 137, 213), false);
        $this->assertEquals($expected, $ecBytes);
    }
}