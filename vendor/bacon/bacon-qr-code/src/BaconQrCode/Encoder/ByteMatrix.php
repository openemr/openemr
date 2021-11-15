<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use SplFixedArray;

/**
 * Byte matrix.
 */
class ByteMatrix
{
    /**
     * Bytes in the matrix, represented as array.
     *
     * @var SplFixedArray
     */
    protected $bytes;

    /**
     * Width of the matrix.
     *
     * @var integer
     */
    protected $width;

    /**
     * Height of the matrix.
     *
     * @var integer
     */
    protected $height;

    /**
     * Creates a new byte matrix.
     *
     * @param  integer $width
     * @param  integer $height
     */
    public function __construct($width, $height)
    {
        $this->height = $height;
        $this->width  = $width;
        $this->bytes  = new SplFixedArray($height);

        for ($y = 0; $y < $height; $y++) {
            $this->bytes[$y] = new SplFixedArray($width);
        }
    }

    /**
     * Gets the width of the matrix.
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Gets the height of the matrix.
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Gets the internal representation of the matrix.
     *
     * @return SplFixedArray
     */
    public function getArray()
    {
        return $this->bytes;
    }

    /**
     * Gets the byte for a specific position.
     *
     * @param  integer $x
     * @param  integer $y
     * @return integer
     */
    public function get($x, $y)
    {
        return $this->bytes[$y][$x];
    }

    /**
     * Sets the byte for a specific position.
     *
     * @param  integer $x
     * @param  integer $y
     * @param  integer $value
     * @return void
     */
    public function set($x, $y, $value)
    {
        $this->bytes[$y][$x] = (int) $value;
    }

    /**
     * Clears the matrix with a specific value.
     *
     * @param  integer $value
     * @return void
     */
    public function clear($value)
    {
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $this->bytes[$y][$x] = $value;
            }
        }
    }

    /**
     * Returns a string representation of the matrix.
     *
     * @return string
     */
    public function __toString()
    {
        $result = '';

        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                switch ($this->bytes[$y][$x]) {
                    case 0:
                        $result .= ' 0';
                        break;

                    case 1:
                        $result .= ' 1';
                        break;

                    default:
                        $result .= '  ';
                        break;
                }
            }

            $result .= "\n";
        }

        return $result;
    }
}
