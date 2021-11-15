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
 * A simple, fast array of bits.
 */
class BitArray
{
    /**
     * Bits represented as an array of integers.
     *
     * @var SplFixedArray
     */
    protected $bits;

    /**
     * Size of the bit array in bits.
     *
     * @var integer
     */
    protected $size;

    /**
     * Creates a new bit array with a given size.
     *
     * @param integer $size
     */
    public function __construct($size = 0)
    {
        $this->size = $size;
        $this->bits = new SplFixedArray(($this->size + 31) >> 3);
    }

    /**
     * Gets the size in bits.
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Gets the size in bytes.
     *
     * @return integer
     */
    public function getSizeInBytes()
    {
        return ($this->size + 7) >> 3;
    }

    /**
     * Ensures that the array has a minimum capacity.
     *
     * @param  integer $size
     * @return void
     */
    public function ensureCapacity($size)
    {
        if ($size > count($this->bits) << 5) {
            $this->bits->setSize(($size + 31) >> 5);
        }
    }

    /**
     * Gets a specific bit.
     *
     * @param  integer $i
     * @return boolean
     */
    public function get($i)
    {
        return ($this->bits[$i >> 5] & (1 << ($i & 0x1f))) !== 0;
    }

    /**
     * Sets a specific bit.
     *
     * @param  integer $i
     * @return void
     */
    public function set($i)
    {
        $this->bits[$i >> 5] = $this->bits[$i >> 5] | 1 << ($i & 0x1f);
    }

    /**
     * Flips a specific bit.
     *
     * @param  integer $i
     * @return void
     */
    public function flip($i)
    {
        $this->bits[$i >> 5] ^= 1 << ($i & 0x1f);
    }

    /**
     * Gets the next set bit position from a given position.
     *
     * @param  integer $from
     * @return integer
     */
    public function getNextSet($from)
    {
        if ($from >= $this->size) {
            return $this->size;
        }

        $bitsOffset  = $from >> 5;
        $currentBits = $this->bits[$bitsOffset];
        $bitsLength  = count($this->bits);

        $currentBits &= ~((1 << ($from & 0x1f)) - 1);

        while ($currentBits === 0) {
            if (++$bitsOffset === $bitsLength) {
                return $this->size;
            }

            $currentBits = $this->bits[$bitsOffset];
        }

        $result = ($bitsOffset << 5) + BitUtils::numberOfTrailingZeros($currentBits);

        return $result > $this->size ? $this->size : $result;
    }

    /**
     * Gets the next unset bit position from a given position.
     *
     * @param  integer $from
     * @return integer
     */
    public function getNextUnset($from)
    {
        if ($from >= $this->size) {
            return $this->size;
        }

        $bitsOffset  = $from >> 5;
        $currentBits = ~$this->bits[$bitsOffset];
        $bitsLength  = count($this->bits);

        $currentBits &= ~((1 << ($from & 0x1f)) - 1);

        while ($currentBits === 0) {
            if (++$bitsOffset === $bitsLength) {
                return $this->size;
            }

            $currentBits = ~$this->bits[$bitsOffset];
        }

        $result = ($bitsOffset << 5) + BitUtils::numberOfTrailingZeros($currentBits);

        return $result > $this->size ? $this->size : $result;
    }

    /**
     * Sets a bulk of bits.
     *
     * @param  integer $i
     * @param  integer $newBits
     * @return void
     */
    public function setBulk($i, $newBits)
    {
        $this->bits[$i >> 5] = $newBits;
    }

    /**
     * Sets a range of bits.
     *
     * @param  integer $start
     * @param  integer $end
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function setRange($start, $end)
    {
        if ($end < $start) {
            throw new Exception\InvalidArgumentException('End must be greater or equal to start');
        }

        if ($end === $start) {
            return;
        }

        $end--;

        $firstInt = $start >> 5;
        $lastInt  = $end >> 5;

        for ($i = $firstInt; $i <= $lastInt; $i++) {
            $firstBit = $i > $firstInt ?  0 : $start & 0x1f;
            $lastBit  = $i < $lastInt ? 31 : $end & 0x1f;

            if ($firstBit === 0 && $lastBit === 31) {
                $mask = 0x7fffffff;
            } else {
                $mask = 0;

                for ($j = $firstBit; $j < $lastBit; $j++) {
                    $mask |= 1 << $j;
                }
            }

            $this->bits[$i] = $this->bits[$i] | $mask;
        }
    }

    /**
     * Clears the bit array, unsetting every bit.
     *
     * @return void
     */
    public function clear()
    {
        $bitsLength = count($this->bits);

        for ($i = 0; $i < $bitsLength; $i++) {
            $this->bits[$i] = 0;
        }
    }

    /**
     * Checks if a range of bits is set or not set.
     *
     * @param  integer $start
     * @param  integer $end
     * @param  integer $value
     * @return boolean
     * @throws Exception\InvalidArgumentException
     */
    public function isRange($start, $end, $value)
    {
        if ($end < $start) {
            throw new Exception\InvalidArgumentException('End must be greater or equal to start');
        }

        if ($end === $start) {
            return;
        }

        $end--;

        $firstInt = $start >> 5;
        $lastInt  = $end >> 5;

        for ($i = $firstInt; $i <= $lastInt; $i++) {
            $firstBit = $i > $firstInt ?  0 : $start & 0x1f;
            $lastBit  = $i < $lastInt ? 31 : $end & 0x1f;

            if ($firstBit === 0 && $lastBit === 31) {
                $mask = 0x7fffffff;
            } else {
                $mask = 0;

                for ($j = $firstBit; $j <= $lastBit; $j++) {
                    $mask |= 1 << $j;
                }
            }

            if (($this->bits[$i] & $mask) !== ($value ? $mask : 0)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Appends a bit to the array.
     *
     * @param  boolean $bit
     * @return void
     */
    public function appendBit($bit)
    {
        $this->ensureCapacity($this->size + 1);

        if ($bit) {
            $this->bits[$this->size >> 5] = $this->bits[$this->size >> 5] | (1 << ($this->size & 0x1f));
        }

        $this->size++;
    }

    /**
     * Appends a number of bits (up to 32) to the array.
     *
     * @param  integer $value
     * @param  integer $numBits
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function appendBits($value, $numBits)
    {
        if ($numBits < 0 || $numBits > 32) {
            throw new Exception\InvalidArgumentException('Num bits must be between 0 and 32');
        }

        $this->ensureCapacity($this->size + $numBits);

        for ($numBitsLeft = $numBits; $numBitsLeft > 0; $numBitsLeft--) {
            $this->appendBit((($value >> ($numBitsLeft - 1)) & 0x01) === 1);
        }
    }

    /**
     * Appends another bit array to this array.
     *
     * @param  BitArray $other
     * @return void
     */
    public function appendBitArray(self $other)
    {
        $otherSize = $other->getSize();
        $this->ensureCapacity($this->size + $other->getSize());

        for ($i = 0; $i < $otherSize; $i++) {
            $this->appendBit($other->get($i));
        }
    }

    /**
     * Makes an exclusive-or comparision on the current bit array.
     *
     * @param  BitArray $other
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function xorBits(self $other)
    {
        $bitsLength = count($this->bits);
        $otherBits  = $other->getBitArray();

        if ($bitsLength !== count($otherBits)) {
            throw new Exception\InvalidArgumentException('Sizes don\'t match');
        }

        for ($i = 0; $i < $bitsLength; $i++) {
            $this->bits[$i] = $this->bits[$i] ^ $otherBits[$i];
        }
    }

    /**
     * Converts the bit array to a byte array.
     *
     * @param  integer $bitOffset
     * @param  integer $numBytes
     * @return SplFixedArray
     */
    public function toBytes($bitOffset, $numBytes)
    {
        $bytes = new SplFixedArray($numBytes);

        for ($i = 0; $i < $numBytes; $i++) {
            $byte = 0;

            for ($j = 0; $j < 8; $j++) {
                if ($this->get($bitOffset)) {
                    $byte |= 1 << (7 - $j);
                }

                $bitOffset++;
            }

            $bytes[$i] = $byte;
        }

        return $bytes;
    }

    /**
     * Gets the internal bit array.
     *
     * @return SplFixedArray
     */
    public function getBitArray()
    {
        return $this->bits;
    }

    /**
     * Reverses the array.
     *
     * @return void
     */
    public function reverse()
    {
        $newBits = new SplFixedArray(count($this->bits));

        for ($i = 0; $i < $this->size; $i++) {
            if ($this->get($this->size - $i - 1)) {
                $newBits[$i >> 5] = $newBits[$i >> 5] | (1 << ($i & 0x1f));
            }
        }

        $this->bits = newBits;
    }

    /**
     * Returns a string representation of the bit array.
     *
     * @return string
     */
    public function __toString()
    {
        $result = '';

        for ($i = 0; $i < $this->size; $i++) {
            if (($i & 0x07) === 0) {
                $result .= ' ';
            }

            $result .= $this->get($i) ? 'X' : '.';
        }

        return $result;
    }
}
