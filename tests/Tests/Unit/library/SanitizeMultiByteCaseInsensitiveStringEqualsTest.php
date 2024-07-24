<?php

/**
 * Test cases for the sanitize.inc.php mb_is_string_equal_ci function
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\library;

use PHPUnit\Framework\TestCase;

class SanitizeMultiByteCaseInsensitiveStringEqualsTest extends TestCase
{
    public function testIdenticalStrings()
    {
        $this->assertTrue(mb_is_string_equal_ci('Hello', 'Hello'));
    }

    public function testComposedCharacters()
    {
        // Composed characters that normalize to the same form under NFKC
        $this->assertTrue(mb_is_string_equal_ci('é', 'é')); // e + combining acute accent vs é
        $this->assertTrue(mb_is_string_equal_ci('ö', 'ö')); // o + combining diaeresis vs ö
    }
    public function testDecomposedCharacters()
    {
        // Decomposed form of Ä: A + combining diaeresis (U+00C4 -> U+0041 U+0308)
        $this->assertTrue(mb_is_string_equal_ci('Ä', 'Ä'));
    }
    public function testCaseInsensitivity()
    {
        // Characters that are different in case but should be equal after case folding
        $this->assertTrue(mb_is_string_equal_ci('abc', 'ABC'));
        $this->assertTrue(mb_is_string_equal_ci('ß', 'SS')); // German eszett (ß) vs SS
        $this->assertTrue(mb_is_string_equal_ci('Ä', 'ä'));
    }

    public function testDifferentStrings()
    {
        $this->assertFalse(mb_is_string_equal_ci('hello', 'world'));
    }

    public function testEmptyStrings()
    {
        $this->assertTrue(mb_is_string_equal_ci('', ''));
    }

    public function testLargeStrings()
    {
        // Generate a large string
        $string1 = str_repeat('a', 100000);
        $string2 = str_repeat('A', 100000);
        $this->assertTrue(mb_is_string_equal_ci($string1, $string2));
    }
}
