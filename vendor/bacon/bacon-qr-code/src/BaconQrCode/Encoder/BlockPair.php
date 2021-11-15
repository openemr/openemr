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
 * Block pair.
 */
class BlockPair
{
    /**
     * Data bytes in the block.
     *
     * @var SplFixedArray
     */
    protected $dataBytes;

    /**
     * Error correction bytes in the block.
     *
     * @var SplFixedArray
     */
    protected $errorCorrectionBytes;

    /**
     * Creates a new block pair.
     *
     * @param SplFixedArray $data
     * @param SplFixedArray $errorCorrection
     */
    public function __construct(SplFixedArray $data, SplFixedArray $errorCorrection)
    {
        $this->dataBytes            = $data;
        $this->errorCorrectionBytes = $errorCorrection;
    }

    /**
     * Gets the data bytes.
     *
     * @return SplFixedArray
     */
    public function getDataBytes()
    {
        return $this->dataBytes;
    }

    /**
     * Gets the error correction bytes.
     *
     * @return SplFixedArray
     */
    public function getErrorCorrectionBytes()
    {
        return $this->errorCorrectionBytes;
    }
}
