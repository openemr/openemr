<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Color;

/**
 * Color interface.
 */
interface ColorInterface
{
    /**
     * Converts the color to RGB.
     *
     * @return Rgb
     */
    public function toRgb();

    /**
     * Converts the color to CMYK.
     *
     * @return Cmyk
     */
    public function toCmyk();

    /**
     * Converts the color to gray.
     *
     * @return Gray
     */
    public function toGray();
}