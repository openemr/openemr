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
 * RGB color.
 */
class Rgb implements ColorInterface
{
    /**
     * Red value.
     *
     * @var integer
     */
    protected $red;

    /**
     * Green value.
     *
     * @var integer
     */
    protected $green;

    /**
     * Blue value.
     *
     * @var integer
     */
    protected $blue;

    /**
     * Creates a new RGB color.
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     */
    public function __construct($red, $green, $blue)
    {
        if ($red < 0 || $red > 255) {
            throw new Exception\InvalidArgumentException('Red must be between 0 and 255');
        }

        if ($green < 0 || $green > 255) {
            throw new Exception\InvalidArgumentException('Green must be between 0 and 255');
        }

        if ($blue < 0 || $blue > 255) {
            throw new Exception\InvalidArgumentException('Blue must be between 0 and 255');
        }

        $this->red   = (int) $red;
        $this->green = (int) $green;
        $this->blue  = (int) $blue;
    }

    /**
     * Returns the red value.
     *
     * @return integer
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * Returns the green value.
     *
     * @return integer
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * Returns the blue value.
     *
     * @return integer
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * Returns a hexadecimal string representation of the RGB value.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%02x%02x%02x', $this->red, $this->green, $this->blue);
    }

    /**
     * toRgb(): defined by ColorInterface.
     *
     * @see    ColorInterface::toRgb()
     * @return Rgb
     */
    public function toRgb()
    {
        return $this;
    }

    /**
     * toCmyk(): defined by ColorInterface.
     *
     * @see    ColorInterface::toCmyk()
     * @return Cmyk
     */
    public function toCmyk()
    {
        $c = 1 - ($this->red / 255);
        $m = 1 - ($this->green / 255);
        $y = 1 - ($this->blue / 255);
        $k = min($c, $m, $y);

        return new Cmyk(
            100 * ($c - $k) / (1 - $k),
            100 * ($m - $k) / (1 - $k),
            100 * ($y - $k) / (1 - $k),
            100 * $k
        );
    }

    /**
     * toGray(): defined by ColorInterface.
     *
     * @see    ColorInterface::toGray()
     * @return Gray
     */
    public function toGray()
    {
        return new Gray(($this->red * 0.21 + $this->green * 0.71 + $this->blue * 0.07) / 2.55);
    }
}