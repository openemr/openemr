<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

use PHPUnit_Framework_TestCase as TestCase;
use SplFixedArray;

class ReedSolomonTest extends TestCase
{
    public static function tabProvider()
    {
        return array(
            array(2, 0x7,   1, 1, 1),
            array(3, 0xb,   1, 1, 2),
            array(4, 0x13,  1, 1, 4),
            array(5, 0x25,  1, 1, 6),
            array(6, 0x43,  1, 1, 8),
            array(7, 0x89,  1, 1, 10),
            array(8, 0x11d, 1, 1, 32),
        );
    }

    /**
     * @dataProvider tabProvider
     * @param        integer $symbolSize
     * @param        integer $generatorPoly
     * @param        integer $firstRoot
     * @param        integer $primitive
     * @param        integer $numRoots
     * @return       void
     */
    public function testCodec($symbolSize, $generatorPoly, $firstRoot, $primitive, $numRoots)
    {
        if (defined('MT_RAND_PHP')) {
            mt_srand(0xdeadbeef, MT_RAND_PHP);
        } else {
            mt_srand(0xdeadbeef);
        }

        $blockSize = (1 << $symbolSize) - 1;
        $dataSize  = $blockSize - $numRoots;
        $codec     = new ReedSolomonCodec($symbolSize, $generatorPoly, $firstRoot, $primitive, $numRoots, 0);

        for ($errors = 0; $errors <= $numRoots / 2; $errors++) {
            // Load block with random data and encode
            $block = SplFixedArray::fromArray(array_fill(0, $blockSize, 0), false);

            for ($i = 0; $i < $dataSize; $i++) {
                $block[$i] = mt_rand(0, $blockSize);
            }

            // Make temporary copy
            $tBlock         = clone $block;
            $parity         = SplFixedArray::fromArray(array_fill(0, $numRoots, 0), false);
            $errorLocations = SplFixedArray::fromArray(array_fill(0, $blockSize, 0), false);
            $erasures       = array();

            // Create parity
            $codec->encode($block, $parity);

            // Copy parity into test blocks
            for ($i = 0; $i < $numRoots; $i++) {
                $block[$i + $dataSize] = $parity[$i];
                $tBlock[$i + $dataSize] = $parity[$i];
            }

            // Seed with errors
            for ($i = 0; $i < $errors; $i++) {
                $errorValue = mt_rand(1, $blockSize);

                do {
                    $errorLocation = mt_rand(0, $blockSize);
                } while ($errorLocations[$errorLocation] !== 0);

                $errorLocations[$errorLocation] = 1;

                if (mt_rand(0, 1)) {
                    $erasures[] = $errorLocation;
                }

                $tBlock[$errorLocation] ^= $errorValue;
            }

            $erasures = SplFixedArray::fromArray($erasures, false);

            // Decode the errored block
            $foundErrors = $codec->decode($tBlock, $erasures);

            if ($errors > 0 && $foundErrors === null) {
                $this->assertEquals($block, $tBlock, 'Decoder failed to correct errors');
            }

            $this->assertEquals($errors, $foundErrors, 'Found errors do not equal expected errors');

            for ($i = 0; $i < $foundErrors; $i++) {
                if ($errorLocations[$erasures[$i]] === 0) {
                    $this->fail(sprintf('Decoder indicates error in location %d without error', $erasures[$i]));
                }
            }

            $this->assertEquals($block, $tBlock, 'Decoder did not correct errors');
        }
    }
}