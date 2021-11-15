<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

use SplFixedArray;

/**
 * Bit matrix.
 *
 * Represents a 2D matrix of bits. In function arguments below, and throughout
 * the common module, x is the column position, and y is the row position. The
 * ordering is always x, y. The origin is at the top-left.
 */
class BitMatrix
{
    /**
     * Width of the bit matrix.
     *
     * @var integer
     */
    protected $width;

    /**
     * Height of the bit matrix.
     *
     * @var integer
     */
    protected $height;

    /**
     * Size in bits of each individual row.
     *
     * @var integer
     */
    protected $rowSize;

    /**
     * Bits representation.
     *
     * @var SplFixedArray
     */
    protected $bits;

    /**
     * Creates a new bit matrix with given dimensions.
     *
     * @param  integer      $width
     * @param  integer|null $height
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($width, $height = null)
    {
        if ($height === null) {
            $height = $width;
        }

        if ($width < 1 || $height < 1) {
            throw new Exception\InvalidArgumentException('Both dimensions must be greater than zero');
        }

        $this->width   = $width;
        $this->height  = $height;
        $this->rowSize = ($width + 31) >> 5;
        $this->bits    = new SplFixedArray($this->rowSize * $height);
    }

    /**
     * Gets the requested bit, where true means black.
     *
     * @param  integer $x
     * @param  integer $y
     * @return boolean
     */
    public function get($x, $y)
    {
        $offset = $y * $this->rowSize + ($x >> 5);
        return (BitUtils::unsignedRightShift($this->bits[$offset], ($x & 0x1f)) & 1) !== 0;
    }

    /**
     * Sets the given bit to true.
     *
     * @param  integer $x
     * @param  integer $y
     * @return void
     */
    public function set($x, $y)
    {
        $offset = $y * $this->rowSize + ($x >> 5);
        $this->bits[$offset] = $this->bits[$offset] | (1 << ($x & 0x1f));
    }

    /**
     * Flips the given bit.
     *
     * @param  integer $x
     * @param  integer $y
     * @return void
     */
    public function flip($x, $y)
    {
        $offset = $y * $this->rowSize + ($x >> 5);
        $this->bits[$offset] = $this->bits[$offset] ^ (1 << ($x & 0x1f));
    }

    /**
     * Clears all bits (set to false).
     *
     * @return void
     */
    public function clear()
    {
        $max = count($this->bits);

        for ($i = 0; $i < $max; $i++) {
            $this->bits[$i] = 0;
        }
    }

    /**
     * Sets a square region of the bit matrix to true.
     *
     * @param  integer $left
     * @param  integer $top
     * @param  integer $width
     * @param  integer $height
     * @return void
     */
    public function setRegion($left, $top, $width, $height)
    {
        if ($top < 0 || $left < 0) {
            throw new Exception\InvalidArgumentException('Left and top must be non-negative');
        }

        if ($height < 1 || $width < 1) {
            throw new Exception\InvalidArgumentException('Width and height must be at least 1');
        }

        $right  = $left + $width;
        $bottom = $top + $height;

        if ($bottom > $this->height || $right > $this->width) {
            throw new Exception\InvalidArgumentException('The region must fit inside the matrix');
        }

        for ($y = $top; $y < $bottom; $y++) {
            $offset = $y * $this->rowSize;

            for ($x = $left; $x < $right; $x++) {
                $index = $offset + ($x >> 5);
                $this->bits[$index] = $this->bits[$index] | (1 << ($x & 0x1f));
            }
        }
    }

    /**
     * A fast method to retrieve one row of data from the matrix as a BitArray.
     *
     * @param  integer  $y
     * @param  BitArray $row
     * @return BitArray
     */
    public function getRow($y, BitArray $row = null)
    {
        if ($row === null || $row->getSize() < $this->width) {
            $row = new BitArray($this->width);
        }

        $offset = $y * $this->rowSize;

        for ($x = 0; $x < $this->rowSize; $x++) {
            $row->setBulk($x << 5, $this->bits[$offset + $x]);
        }

        return $row;
    }

    /**
     * Sets a row of data from a BitArray.
     *
     * @param  integer  $y
     * @param  BitArray $row
     * @return void
     */
    public function setRow($y, BitArray $row)
    {
        $bits = $row->getBitArray();

        for ($i = 0; $i < $this->rowSize; $i++) {
            $this->bits[$y * $this->rowSize + $i] = $bits[$i];
        }
    }

    /**
     * This is useful in detecting the enclosing rectangle of a 'pure' barcode.
     *
     * @return SplFixedArray
     */
    public function getEnclosingRectangle()
    {
        $left   = $this->width;
        $top    = $this->height;
        $right  = -1;
        $bottom = -1;

        for ($y = 0; $y < $this->height; $y++) {
            for ($x32 = 0; $x32 < $this->rowSize; $x32++) {
                $bits = $this->bits[$y * $this->rowSize + $x32];

                if ($bits !== 0) {
                    if ($y < $top) {
                        $top = $y;
                    }

                    if ($y > $bottom) {
                        $bottom = $y;
                    }

                    if ($x32 * 32 < $left) {
                        $bit = 0;

                        while (($bits << (31 - $bit)) === 0) {
                            $bit++;
                        }

                        if (($x32 * 32 + $bit) < $left) {
                            $left = $x32 * 32 + $bit;
                        }
                    }
                }

                if ($x32 * 32 + 31 > $right) {
                    $bit = 31;

                    while (BitUtils::unsignedRightShift($bits, $bit) === 0) {
                        $bit--;
                    }

                    if (($x32 * 32 + $bit) > $right) {
                        $right = $x32 * 32 + $bit;
                    }
                }
            }
        }

        $width  = $right - $left;
        $height = $bottom - $top;

        if ($width < 0 || $height < 0) {
            return null;
        }

        return SplFixedArray::fromArray(array($left, $top, $width, $height), false);
    }

    /**
     * Gets the most top left set bit.
     *
     * This is useful in detecting a corner of a 'pure' barcode.
     *
     * @return SplFixedArray
     */
    public function getTopLeftOnBit()
    {
        $bitsOffset = 0;

        while ($bitsOffset < count($this->bits) && $this->bits[$bitsOffset] === 0) {
            $bitsOffset++;
        }

        if ($bitsOffset === count($this->bits)) {
            return null;
        }

        $x = intval($bitsOffset / $this->rowSize);
        $y = ($bitsOffset % $this->rowSize) << 5;

        $bits = $this->bits[$bitsOffset];
        $bit  = 0;

        while (($bits << (31 - $bit)) === 0) {
            $bit++;
        }

        $x += $bit;

        return SplFixedArray::fromArray(array($x, $y), false);
    }

    /**
     * Gets the most bottom right set bit.
     *
     * This is useful in detecting a corner of a 'pure' barcode.
     *
     * @return SplFixedArray
     */
    public function getBottomRightOnBit()
    {
        $bitsOffset = count($this->bits) - 1;

        while ($bitsOffset >= 0 && $this->bits[$bitsOffset] === 0) {
            $bitsOffset--;
        }

        if ($bitsOffset < 0) {
            return null;
        }

        $x = intval($bitsOffset / $this->rowSize);
        $y = ($bitsOffset % $this->rowSize) << 5;

        $bits = $this->bits[$bitsOffset];
        $bit  = 0;

        while (BitUtils::unsignedRightShift($bits, $bit) === 0) {
            $bit--;
        }

        $x += $bit;

        return SplFixedArray::fromArray(array($x, $y), false);
    }

    /**
     * Gets the width of the matrix,
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
}
