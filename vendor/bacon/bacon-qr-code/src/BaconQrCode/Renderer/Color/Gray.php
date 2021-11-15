<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Color;

use BaconQrCode\Exception;

/**
 * Gray color.
 */
class Gray implements ColorInterface
{
    /**
     * Gray value.
     *
     * @var integer
     */
    protected $gray;

    /**
     * Creates a new gray color.
     *
     * A low gray value means black, while a high value means white.
     *
     * @param integer $gray
     */
    public function __construct($gray)
    {
        if ($gray < 0 || $gray > 100) {
            throw new Exception\InvalidArgumentException('Gray must be between 0 and 100');
        }

        $this->gray = (int) $gray;
    }

    /**
     * Returns the gray value.
     *
     * @return integer
     */
    public function getGray()
    {
        return $this->gray;
    }

    /**
     * toRgb(): defined by ColorInterface.
     *
     * @see    ColorInterface::toRgb()
     * @return Rgb
     */
    public function toRgb()
    {
        return new Rgb($this->gray * 2.55, $this->gray * 2.55, $this->gray * 2.55);
    }

    /**
     * toCmyk(): defined by ColorInterface.
     *
     * @see    ColorInterface::toCmyk()
     * @return Cmyk
     */
    public function toCmyk()
    {
        return new Cmyk(0, 0, 0, 100 - $this->gray);
    }

    /**
     * toGray(): defined by ColorInterface.
     *
     * @see    ColorInterface::toGray()
     * @return Gray
     */
    public function toGray()
    {
        return $this;
    }
}