<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

/**
 * General bit utilities.
 *
 * All utility methods are based on 32-bit integers and also work on 64-bit
 * systems.
 */
class BitUtils
{
    /**
     * Performs an unsigned right shift.
     *
     * This is the same as the unsigned right shift operator ">>>" in other
     * languages.
     *
     * @param  integer $a
     * @param  integer $b
     * @return integer
     */
    public static function unsignedRightShift($a, $b)
    {
        return (
            $a >= 0
            ? $a >> $b
            : (($a & 0x7fffffff) >> $b) | (0x40000000 >> ($b - 1))
        );
    }

    /**
     * Gets the number of trailing zeros.
     *
     * @param  integer $i
     * @return integer
     */
    public static function numberOfTrailingZeros($i)
    {
        $lastPos = strrpos(str_pad(decbin($i), 32, '0', STR_PAD_LEFT), '1');

        return $lastPos === false ? 32 : 31 - $lastPos;
    }
}