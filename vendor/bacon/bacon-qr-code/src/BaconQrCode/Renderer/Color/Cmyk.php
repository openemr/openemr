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
 * CMYK color.
 */
class Cmyk implements ColorInterface
{
    /**
     * Cyan value.
     *
     * @var integer
     */
    protected $cyan;

    /**
     * Magenta value.
     *
     * @var integer
     */
    protected $magenta;

    /**
     * Yellow value.
     *
     * @var integer
     */
    protected $yellow;

    /**
     * Black value.
     *
     * @var integer
     */
    protected $black;

    /**
     * Creates a new CMYK color.
     *
     * @param integer $cyan
     * @param integer $magenta
     * @param integer $yellow
     * @param integer $black
     */
    public function __construct($cyan, $magenta, $yellow, $black)
    {
        if ($cyan < 0 || $cyan > 100) {
            throw new Exception\InvalidArgumentException('Cyan must be between 0 and 100');
        }

        if ($magenta < 0 || $magenta > 100) {
            throw new Exception\InvalidArgumentException('Magenta must be between 0 and 100');
        }

        if ($yellow < 0 || $yellow > 100) {
            throw new Exception\InvalidArgumentException('Yellow must be between 0 and 100');
        }

        if ($black < 0 || $black > 100) {
            throw new Exception\InvalidArgumentException('Black must be between 0 and 100');
        }

        $this->cyan    = (int) $cyan;
        $this->magenta = (int) $magenta;
        $this->yellow  = (int) $yellow;
        $this->black   = (int) $black;
    }

    /**
     * Returns the cyan value.
     *
     * @return integer
     */
    public function getCyan()
    {
        return $this->cyan;
    }

    /**
     * Returns the magenta value.
     *
     * @return integer
     */
    public function getMagenta()
    {
        return $this->magenta;
    }

    /**
     * Returns the yellow value.
     *
     * @return integer
     */
    public function getYellow()
    {
        return $this->yellow;
    }

    /**
     * Returns the black value.
     *
     * @return integer
     */
    public function getBlack()
    {
        return $this->black;
    }

    /**
     * toRgb(): defined by ColorInterface.
     *
     * @see    ColorInterface::toRgb()
     * @return Rgb
     */
    public function toRgb()
    {
        $k = $this->black / 100;
        $c = (-$k * $this->cyan + $k * 100 + $this->cyan) / 100;
        $m = (-$k * $this->magenta + $k * 100 + $this->magenta) / 100;
        $y = (-$k * $this->yellow + $k * 100 + $this->yellow) / 100;

        return new Rgb(
            -$c * 255 + 255,
            -$m * 255 + 255,
            -$y * 255 + 255
        );
    }

    /**
     * toCmyk(): defined by ColorInterface.
     *
     * @see    ColorInterface::toCmyk()
     * @return Cmyk
     */
    public function toCmyk()
    {
        return $this;
    }

    /**
     * toGray(): defined by ColorInterface.
     *
     * @see    ColorInterface::toGray()
     * @return Gray
     */
    public function toGray()
    {
        return $this->toRgb()->toGray();
    }
}