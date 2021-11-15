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
 * Enum representing the four error correction levels.
 */
class ErrorCorrectionLevel extends AbstractEnum
{
    /**
     * Level L, ~7% correction.
     */
    const L = 0x1;

    /**
     * Level M, ~15% correction.
     */
    const M = 0x0;

    /**
     * Level Q, ~25% correction.
     */
    const Q = 0x3;

    /**
     * Level H, ~30% correction.
     */
    const H = 0x2;

    /**
     * Gets the ordinal of this enumeration constant.
     *
     * @return integer
     */
    public function getOrdinal()
    {
        switch ($this->value) {
            case self::L:
                return 0;
                break;

            case self::M:
                return 1;
                break;

            case self::Q:
                return 2;
                break;

            case self::H:
                return 3;
                break;
        }
    }
}
