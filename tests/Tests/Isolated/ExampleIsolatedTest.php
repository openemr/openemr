<?php

/**
 * Example Isolated Test
 *
 * Demonstrates how to write tests that run without database dependencies.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated;

use PHPUnit\Framework\TestCase;

class ExampleIsolatedTest extends TestCase
{
    public function testBasicFunctionality(): void
    {
        // Simple test that doesn't require any database or service dependencies
        $result = $this->addNumbers(2, 3);
        $this->assertEquals(5, $result);
    }

    public function testStringManipulation(): void
    {
        $input = "Hello World";
        $result = strtoupper($input);
        $this->assertEquals("HELLO WORLD", $result);
    }

    public function testArrayOperations(): void
    {
        $data = ['apple', 'banana', 'cherry'];
        $this->assertCount(3, $data);
        $this->assertContains('banana', $data);
    }

    public function testComposerAutoloadWorks(): void
    {
        // Test that we can instantiate classes from vendor
        $this->assertTrue(class_exists(\PHPUnit\Framework\TestCase::class));
    }

    private function addNumbers(int $a, int $b): int
    {
        return $a + $b;
    }
}
