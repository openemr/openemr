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

class BitMatrixTest extends TestCase
{
    public function testGetSet()
    {
        $matrix = new BitMatrix(33);
        $this->assertEquals(33, $matrix->getHeight());

        for ($y = 0; $y < 33; $y++) {
            for ($x = 0; $x < 33; $x++) {
                if ($y * $x % 3 === 0) {
                    $matrix->set($x, $y);
                }
            }
        }

        for ($y = 0; $y < 33; $y++) {
            for ($x = 0; $x < 33; $x++) {
                $this->assertEquals($x * $y % 3 === 0, $matrix->get($x, $y));
            }
        }
    }

    public function testSetRegion()
    {
        $matrix = new BitMatrix(5);
        $matrix->setRegion(1, 1, 3, 3);

        for ($y = 0; $y < 5; $y++) {
            for ($x = 0; $x < 5; $x++) {
                $this->assertEquals($y >= 1 && $y <= 3 && $x >= 1 && $x <= 3, $matrix->get($x, $y));
            }
        }
    }

    public function testRectangularMatrix()
    {
        $matrix = new BitMatrix(75, 20);
        $this->assertEquals(75, $matrix->getWidth());
        $this->assertEquals(20, $matrix->getHeight());

        $matrix->set(10, 0);
        $matrix->set(11, 1);
        $matrix->set(50, 2);
        $matrix->set(51, 3);
        $matrix->flip(74, 4);
        $matrix->flip(0, 5);

        $this->assertTrue($matrix->get(10, 0));
        $this->assertTrue($matrix->get(11, 1));
        $this->assertTrue($matrix->get(50, 2));
        $this->assertTrue($matrix->get(51, 3));
        $this->assertTrue($matrix->get(74, 4));
        $this->assertTrue($matrix->get(0, 5));

        $matrix->flip(50, 2);
        $matrix->flip(51, 3);

        $this->assertFalse($matrix->get(50, 2));
        $this->assertFalse($matrix->get(51, 3));
    }

    public function testRectangularSetRegion()
    {
        $matrix = new BitMatrix(320, 240);
        $this->assertEquals(320, $matrix->getWidth());
        $this->assertEquals(240, $matrix->getHeight());

        $matrix->setRegion(105, 22, 80, 12);

        for ($y = 0; $y < 240; $y++) {
            for ($x = 0; $x < 320; $x++) {
                $this->assertEquals($y >= 22 && $y < 34 && $x >= 105 && $x < 185, $matrix->get($x, $y));
            }
        }
    }

    public function testGetRow()
    {
        $matrix = new BitMatrix(102, 5);

        for ($x = 0; $x < 102; $x++) {
            if ($x & 3 === 0) {
                $matrix->set($x, 2);
            }
        }

        $array1 = $matrix->getRow(2, null);
        $this->assertEquals(102, $array1->getSize());

        $array2 = new BitArray(60);
        $array2 = $matrix->getRow(2, $array2);
        $this->assertEquals(102, $array2->getSize());

        $array3 = new BitArray(200);
        $array3 = $matrix->getRow(2, $array3);
        $this->assertEquals(200, $array3->getSize());

        for ($x = 0; $x < 102; $x++) {
            $on = ($x & 3 === 0);

            $this->assertEquals($on, $array1->get($x));
            $this->assertEquals($on, $array2->get($x));
            $this->assertEquals($on, $array3->get($x));
        }
    }
}