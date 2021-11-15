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
 * Encapsualtes the parameters for one error-correction block in one symbol
 * version. This includes the number of data codewords, and the number of times
 * a block with these parameters is used consecutively in the QR code version's
 * format.
 */
class EcBlock
{
    /**
     * How many times the block is used.
     *
     * @var integer
     */
    protected $count;

    /**
     * Number of data codewords.
     *
     * @var integer
     */
    protected $dataCodewords;

    /**
     * Creates a new EC block.
     *
     * @param integer $count
     * @param integer $dataCodewords
     */
    public function __construct($count, $dataCodewords)
    {
        $this->count         = $count;
        $this->dataCodewords = $dataCodewords;
    }

    /**
     * Returns how many times the block is used.
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Returns the number of data codewords.
     *
     * @return integer
     */
    public function getDataCodewords()
    {
        return $this->dataCodewords;
    }
}
