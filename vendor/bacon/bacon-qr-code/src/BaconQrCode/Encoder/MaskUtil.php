<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\BitUtils;

/**
 * Mask utility.
 */
class MaskUtil
{
    /**#@+
     * Penalty weights from section 6.8.2.1
     */
    const N1 = 3;
    const N2 = 3;
    const N3 = 40;
    const N4 = 10;
    /**#@-*/

    /**
     * Applies mask penalty rule 1 and returns the penalty.
     *
     * Finds repetitive cells with the same color and gives penalty to them.
     * Example: 00000 or 11111.
     *
     * @param  ByteMatrix $matrix
     * @return integer
     */
    public static function applyMaskPenaltyRule1(ByteMatrix $matrix)
    {
        return (
            self::applyMaskPenaltyRule1Internal($matrix, true)
            + self::applyMaskPenaltyRule1Internal($matrix, false)
        );
    }

    /**
     * Applies mask penalty rule 2 and returns the penalty.
     *
     * Finds 2x2 blocks with the same color and gives penalty to them. This is
     * actually equivalent to the spec's rule, which is to find MxN blocks and
     * give a penalty proportional to (M-1)x(N-1), because this is the number of
     * 2x2 blocks inside such a block.
     *
     * @param  ByteMatrix $matrix
     * @return integer
     */
    public static function applyMaskPenaltyRule2(ByteMatrix $matrix)
    {
        $penalty = 0;
        $array   = $matrix->getArray();
        $width   = $matrix->getWidth();
        $height  = $matrix->getHeight();

        for ($y = 0; $y < $height - 1; $y++) {
            for ($x = 0; $x < $width - 1; $x++) {
                $value = $array[$y][$x];

                if ($value === $array[$y][$x + 1] && $value === $array[$y + 1][$x] && $value === $array[$y + 1][$x + 1]) {
                    $penalty++;
                }
            }
        }

        return self::N2 * $penalty;
    }

    /**
     * Applies mask penalty rule 3 and returns the penalty.
     *
     * Finds consecutive cells of 00001011101 or 10111010000, and gives penalty
     * to them. If we find patterns like 000010111010000, we give penalties
     * twice (i.e. 40 * 2).
     *
     * @param  ByteMatrix $matrix
     * @return integer
     */
    public static function applyMaskPenaltyRule3(ByteMatrix $matrix)
    {
        $penalty = 0;
        $array   = $matrix->getArray();
        $width   = $matrix->getWidth();
        $height  = $matrix->getHeight();

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (
                    $x + 6 < $width
                    && $array[$y][$x] === 1
                    && $array[$y][$x + 1] === 0
                    && $array[$y][$x + 2] === 1
                    && $array[$y][$x + 3] === 1
                    && $array[$y][$x + 4] === 1
                    && $array[$y][$x + 5] === 0
                    && $array[$y][$x + 6] === 1
                    && (
                        (
                            $x + 10 < $width
                            && $array[$y][$x + 7] === 0
                            && $array[$y][$x + 8] === 0
                            && $array[$y][$x + 9] === 0
                            && $array[$y][$x + 10] === 0
                        )
                        || (
                            $x - 4 >= 0
                            && $array[$y][$x - 1] === 0
                            && $array[$y][$x - 2] === 0
                            && $array[$y][$x - 3] === 0
                            && $array[$y][$x - 4] === 0
                        )
                    )
                ) {
                    $penalty += self::N3;
                }

                if (
                    $y + 6 < $height
                    && $array[$y][$x] === 1
                    && $array[$y + 1][$x] === 0
                    && $array[$y + 2][$x] === 1
                    && $array[$y + 3][$x] === 1
                    && $array[$y + 4][$x] === 1
                    && $array[$y + 5][$x] === 0
                    && $array[$y + 6][$x] === 1
                    && (
                        (
                            $y + 10 < $height
                            && $array[$y + 7][$x] === 0
                            && $array[$y + 8][$x] === 0
                            && $array[$y + 9][$x] === 0
                            && $array[$y + 10][$x] === 0
                        )
                        || (
                            $y - 4 >= 0
                            && $array[$y - 1][$x] === 0
                            && $array[$y - 2][$x] === 0
                            && $array[$y - 3][$x] === 0
                            && $array[$y - 4][$x] === 0
                        )
                    )
                ) {
                    $penalty += self::N3;
                }
            }
        }

        return $penalty;
    }

    /**
     * Applies mask penalty rule 4 and returns the penalty.
     *
     * Calculates the ratio of dark cells and gives penalty if the ratio is far
     * from 50%. It gives 10 penalty for 5% distance.
     *
     * @param  ByteMatrix $matrix
     * @return integer
     */
    public static function applyMaskPenaltyRule4(ByteMatrix $matrix)
    {
        $numDarkCells = 0;

        $array  = $matrix->getArray();
        $width  = $matrix->getWidth();
        $height = $matrix->getHeight();

        for ($y = 0; $y < $height; $y++) {
            $arrayY = $array[$y];

            for ($x = 0; $x < $width; $x++) {
                if ($arrayY[$x] === 1) {
                    $numDarkCells++;
                }
            }
        }

        $numTotalCells         = $height * $width;
        $darkRatio             = $numDarkCells / $numTotalCells;
        $fixedPercentVariances = (int) (abs($darkRatio - 0.5) * 20);

        return $fixedPercentVariances * self::N4;
    }

    /**
     * Returns the mask bit for "getMaskPattern" at "x" and "y".
     *
     * See 8.8 of JISX0510:2004 for mask pattern conditions.
     *
     * @param  integer $maskPattern
     * @param  integer $x
     * @param  integer $y
     * @return integer
     * @throws Exception\InvalidArgumentException
     */
    public static function getDataMaskBit($maskPattern, $x, $y)
    {
        switch ($maskPattern) {
            case 0:
                $intermediate = ($y + $x) & 0x1;
                break;

            case 1:
                $intermediate = $y & 0x1;
                break;

            case 2:
                $intermediate = $x % 3;
                break;

            case 3:
                $intermediate = ($y + $x) % 3;
                break;

            case 4:
                $intermediate = (BitUtils::unsignedRightShift($y, 1) + ($x / 3)) & 0x1;
                break;

            case 5:
                $temp         = $y * $x;
                $intermediate = ($temp & 0x1) + ($temp % 3);
                break;

            case 6:
                $temp         = $y * $x;
                $intermediate = (($temp & 0x1) + ($temp % 3)) & 0x1;
                break;

            case 7:
                $temp         = $y * $x;
                $intermediate = (($temp % 3) + (($y + $x) & 0x1)) & 0x1;
                break;

            default:
                throw new Exception\InvalidArgumentException('Invalid mask pattern: ' . $maskPattern);
        }

        return $intermediate === 0;
    }

    /**
     * Helper function for applyMaskPenaltyRule1.
     *
     * We need this for doing this calculation in both vertical and horizontal
     * orders respectively.
     *
     * @param  ByteMatrix $matrix
     * @param  boolean    $isHorizontal
     * @return integer
     */
    protected static function applyMaskPenaltyRule1Internal(ByteMatrix $matrix, $isHorizontal)
    {
        $penalty = 0;
        $iLimit  = $isHorizontal ? $matrix->getHeight() : $matrix->getWidth();
        $jLimit  = $isHorizontal ? $matrix->getWidth() : $matrix->getHeight();
        $array   = $matrix->getArray();

        for ($i = 0; $i < $iLimit; $i++) {
            $numSameBitCells = 0;
            $prevBit         = -1;

            for ($j = 0; $j < $jLimit; $j++) {
                $bit = $isHorizontal ? $array[$i][$j] : $array[$j][$i];

                if ($bit === $prevBit) {
                    $numSameBitCells++;
                } else {
                    if ($numSameBitCells >= 5) {
                        $penalty += self::N1 + ($numSameBitCells - 5);
                    }

                    $numSameBitCells = 1;
                    $prevBit         = $bit;
                }
            }

            if ($numSameBitCells >= 5) {
                $penalty += self::N1 + ($numSameBitCells - 5);
            }
        }

        return $penalty;
    }
}
