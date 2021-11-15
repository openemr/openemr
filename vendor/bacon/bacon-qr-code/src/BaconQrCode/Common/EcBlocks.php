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
 * Encapsulates a set of error-correction blocks in one symbol version. Most
 * versions will use blocks of differing sizes within one version, so, this
 * encapsulates the parameters for each set of blocks. It also holds the number
 * of error-correction codewords per block since it will be the same across all
 * blocks within one version.
 */
class EcBlocks
{
    /**
     * Number of EC codewords per block.
     *
     * @var integer
     */
    protected $ecCodewordsPerBlock;

    /**
     * List of EC blocks.
     *
     * @var SplFixedArray
     */
    protected $ecBlocks;

    /**
     * Creates a new EC blocks instance.
     *
     * @param integer      $ecCodewordsPerBlock
     * @param EcBlock      $ecb1
     * @param EcBlock|null $ecb2
     */
    public function __construct($ecCodewordsPerBlock, EcBlock $ecb1, EcBlock $ecb2 = null)
    {
        $this->ecCodewordsPerBlock = $ecCodewordsPerBlock;

        $this->ecBlocks = new SplFixedArray($ecb2 === null ? 1 : 2);
        $this->ecBlocks[0] = $ecb1;

        if ($ecb2 !== null) {
            $this->ecBlocks[1] = $ecb2;
        }
    }

    /**
     * Gets the number of EC codewords per block.
     *
     * @return integer
     */
    public function getEcCodewordsPerBlock()
    {
        return $this->ecCodewordsPerBlock;
    }

    /**
     * Gets the total number of EC block appearances.
     *
     * @return integer
     */
    public function getNumBlocks()
    {
        $total = 0;

        foreach ($this->ecBlocks as $ecBlock) {
            $total += $ecBlock->getCount();
        }

        return $total;
    }

    /**
     * Gets the total count of EC codewords.
     *
     * @return integer
     */
    public function getTotalEcCodewords()
    {
        return $this->ecCodewordsPerBlock * $this->getNumBlocks();
    }

    /**
     * Gets the EC blocks included in this collection.
     *
     * @return SplFixedArray
     */
    public function getEcBlocks()
    {
        return $this->ecBlocks;
    }
}
