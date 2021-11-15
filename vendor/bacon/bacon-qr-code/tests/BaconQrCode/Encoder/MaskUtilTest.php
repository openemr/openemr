<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use PHPUnit_Framework_TestCase as TestCase;

class MaskUtilTest extends TestCase
{
    public static function dataMaskBitProvider()
    {
        return array(
            array(
                0,
                array(
                    array(1, 0, 1, 0, 1, 0),
                    array(0, 1, 0, 1, 0, 1),
                    array(1, 0, 1, 0, 1, 0),
                    array(0, 1, 0, 1, 0, 1),
                    array(1, 0, 1, 0, 1, 0),
                    array(0, 1, 0, 1, 0, 1),
                )
            ),
            array(
                1,
                array(
                    array(1, 1, 1, 1, 1, 1),
                    array(0, 0, 0, 0, 0, 0),
                    array(1, 1, 1, 1, 1, 1),
                    array(0, 0, 0, 0, 0, 0),
                    array(1, 1, 1, 1, 1, 1),
                    array(0, 0, 0, 0, 0, 0),
                )
            ),
            array(
                2,
                array(
                    array(1, 0, 0, 1, 0, 0),
                    array(1, 0, 0, 1, 0, 0),
                    array(1, 0, 0, 1, 0, 0),
                    array(1, 0, 0, 1, 0, 0),
                    array(1, 0, 0, 1, 0, 0),
                    array(1, 0, 0, 1, 0, 0),
                )
            ),
            array(
                3,
                array(
                    array(1, 0, 0, 1, 0, 0),
                    array(0, 0, 1, 0, 0, 1),
                    array(0, 1, 0, 0, 1, 0),
                    array(1, 0, 0, 1, 0, 0),
                    array(0, 0, 1, 0, 0, 1),
                    array(0, 1, 0, 0, 1, 0),
                )
            ),
            array(
                4,
                array(
                    array(1, 1, 1, 0, 0, 0),
                    array(1, 1, 1, 0, 0, 0),
                    array(0, 0, 0, 1, 1, 1),
                    array(0, 0, 0, 1, 1, 1),
                    array(1, 1, 1, 0, 0, 0),
                    array(1, 1, 1, 0, 0, 0),
                )
            ),
            array(
                5,
                array(
                    array(1, 1, 1, 1, 1, 1),
                    array(1, 0, 0, 0, 0, 0),
                    array(1, 0, 0, 1, 0, 0),
                    array(1, 0, 1, 0, 1, 0),
                    array(1, 0, 0, 1, 0, 0),
                    array(1, 0, 0, 0, 0, 0),
                )
            ),
            array(
                6,
                array(
                    array(1, 1, 1, 1, 1, 1),
                    array(1, 1, 1, 0, 0, 0),
                    array(1, 1, 0, 1, 1, 0),
                    array(1, 0, 1, 0, 1, 0),
                    array(1, 0, 1, 1, 0, 1),
                    array(1, 0, 0, 0, 1, 1),
                )
            ),
            array(
                7,
                array(
                    array(1, 0, 1, 0, 1, 0),
                    array(0, 0, 0, 1, 1, 1),
                    array(1, 0, 0, 0, 1, 1),
                    array(0, 1, 0, 1, 0, 1),
                    array(1, 1, 1, 0, 0, 0),
                    array(0, 1, 1, 1, 0, 0),
                )
            ),
        );
    }

    /**
     * @dataProvider dataMaskBitProvider
     * @param        integer $maskPattern
     * @param        array   $expected
     * @return       void
     */
    public function testGetDatMaskBit($maskPattern, array $expected)
    {
        for ($x = 0; $x < 6; $x++) {
            for ($y = 0; $y < 6; $y++) {
                if (($expected[$y][$x] === 1) !== MaskUtil::getDataMaskBit($maskPattern, $x, $y)) {
                    $this->fail('Data mask bit did not match');
                }
            }
        }
    }

    public function testApplyMaskPenaltyRule1()
    {
        $matrix = new ByteMatrix(4, 1);
        $matrix->set(0, 0, 0);
        $matrix->set(1, 0, 0);
        $matrix->set(2, 0, 0);
        $matrix->set(3, 0, 0);

        $this->assertEquals(0, MaskUtil::applyMaskPenaltyRule1($matrix));

        // Horizontal
        $matrix = new ByteMatrix(6, 1);
        $matrix->set(0, 0, 0);
        $matrix->set(1, 0, 0);
        $matrix->set(2, 0, 0);
        $matrix->set(3, 0, 0);
        $matrix->set(4, 0, 0);
        $matrix->set(5, 0, 1);
        $this->assertEquals(3, MaskUtil::applyMaskPenaltyRule1($matrix));
        $matrix->set(5, 0, 0);
        $this->assertEquals(4, MaskUtil::applyMaskPenaltyRule1($matrix));

        // Vertical
        $matrix = new ByteMatrix(1, 6);
        $matrix->set(0, 0, 0);
        $matrix->set(0, 1, 0);
        $matrix->set(0, 2, 0);
        $matrix->set(0, 3, 0);
        $matrix->set(0, 4, 0);
        $matrix->set(0, 5, 1);
        $this->assertEquals(3, MaskUtil::applyMaskPenaltyRule1($matrix));
        $matrix->set(0, 5, 0);
        $this->assertEquals(4, MaskUtil::applyMaskPenaltyRule1($matrix));
    }

    public function testApplyMaskPenaltyRule2()
    {
        $matrix = new ByteMatrix(1, 1);
        $matrix->set(0, 0, 0);
        $this->assertEquals(0, MaskUtil::applyMaskPenaltyRule2($matrix));

        $matrix = new ByteMatrix(2, 2);
        $matrix->set(0, 0, 0);
        $matrix->set(1, 0, 0);
        $matrix->set(0, 1, 0);
        $matrix->set(1, 1, 1);
        $this->assertEquals(0, MaskUtil::applyMaskPenaltyRule2($matrix));

        $matrix = new ByteMatrix(2, 2);
        $matrix->set(0, 0, 0);
        $matrix->set(1, 0, 0);
        $matrix->set(0, 1, 0);
        $matrix->set(1, 1, 0);
        $this->assertEquals(3, MaskUtil::applyMaskPenaltyRule2($matrix));

        $matrix = new ByteMatrix(3, 3);
        $matrix->set(0, 0, 0);
        $matrix->set(1, 0, 0);
        $matrix->set(2, 0, 0);
        $matrix->set(0, 1, 0);
        $matrix->set(1, 1, 0);
        $matrix->set(2, 1, 0);
        $matrix->set(0, 2, 0);
        $matrix->set(1, 2, 0);
        $matrix->set(2, 2, 0);
        $this->assertEquals(3 * 4, MaskUtil::applyMaskPenaltyRule2($matrix));
    }

    public function testApplyMaskPenalty3()
    {
        // Horizontal 00001011101
        $matrix = new ByteMatrix(11, 1);
        $matrix->set(0, 0, 0);
        $matrix->set(1, 0, 0);
        $matrix->set(2, 0, 0);
        $matrix->set(3, 0, 0);
        $matrix->set(4, 0, 1);
        $matrix->set(5, 0, 0);
        $matrix->set(6, 0, 1);
        $matrix->set(7, 0, 1);
        $matrix->set(8, 0, 1);
        $matrix->set(9, 0, 0);
        $matrix->set(10, 0, 1);
        $this->assertEquals(40, MaskUtil::applyMaskPenaltyRule3($matrix));

        // Horizontal 10111010000
        $matrix = new ByteMatrix(11, 1);
        $matrix->set(0, 0, 1);
        $matrix->set(1, 0, 0);
        $matrix->set(2, 0, 1);
        $matrix->set(3, 0, 1);
        $matrix->set(4, 0, 1);
        $matrix->set(5, 0, 0);
        $matrix->set(6, 0, 1);
        $matrix->set(7, 0, 0);
        $matrix->set(8, 0, 0);
        $matrix->set(9, 0, 0);
        $matrix->set(10, 0, 0);
        $this->assertEquals(40, MaskUtil::applyMaskPenaltyRule3($matrix));

        // Vertical 00001011101
        $matrix = new ByteMatrix(1, 11);
        $matrix->set(0, 0, 0);
        $matrix->set(0, 1, 0);
        $matrix->set(0, 2, 0);
        $matrix->set(0, 3, 0);
        $matrix->set(0, 4, 1);
        $matrix->set(0, 5, 0);
        $matrix->set(0, 6, 1);
        $matrix->set(0, 7, 1);
        $matrix->set(0, 8, 1);
        $matrix->set(0, 9, 0);
        $matrix->set(0, 10, 1);
        $this->assertEquals(40, MaskUtil::applyMaskPenaltyRule3($matrix));

        // Vertical 10111010000
        $matrix = new ByteMatrix(1, 11);
        $matrix->set(0, 0, 1);
        $matrix->set(0, 1, 0);
        $matrix->set(0, 2, 1);
        $matrix->set(0, 3, 1);
        $matrix->set(0, 4, 1);
        $matrix->set(0, 5, 0);
        $matrix->set(0, 6, 1);
        $matrix->set(0, 7, 0);
        $matrix->set(0, 8, 0);
        $matrix->set(0, 9, 0);
        $matrix->set(0, 10, 0);
        $this->assertEquals(40, MaskUtil::applyMaskPenaltyRule3($matrix));
    }

    public function testApplyMaskPenaltyRule4()
    {
        // Dark cell ratio = 0%
        $matrix = new ByteMatrix(1, 1);
        $matrix->set(0, 0, 0);
        $this->assertEquals(100, MaskUtil::applyMaskPenaltyRule4($matrix));

        // Dark cell ratio = 5%
        $matrix = new ByteMatrix(2, 1);
        $matrix->set(0, 0, 0);
        $matrix->set(0, 0, 1);
        $this->assertEquals(0, MaskUtil::applyMaskPenaltyRule4($matrix));

        // Dark cell ratio = 66.67%
        $matrix = new ByteMatrix(6, 1);
        $matrix->set(0, 0, 0);
        $matrix->set(1, 0, 1);
        $matrix->set(2, 0, 1);
        $matrix->set(3, 0, 1);
        $matrix->set(4, 0, 1);
        $matrix->set(5, 0, 0);
        $this->assertEquals(30, MaskUtil::applyMaskPenaltyRule4($matrix));
    }
}