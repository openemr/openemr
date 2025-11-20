<?php

/**
 * RxListTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    GitHub Copilot <copilot@github.com>
 * @copyright Copyright (c) 2025 GitHub Copilot <copilot@github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Start of AI-generated code by GitHub Copilot
namespace OpenEMR\Tests\Unit\Rx;

use OpenEMR\Rx\RxList;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RxListTest extends TestCase
{
    /**
     * Helper method to access protected parseToTokens method
     */
    private function invokeParseToTokens(RxList $rxList, string $page): array
    {
        $reflection = new ReflectionClass($rxList);
        $method = $reflection->getMethod('parseToTokens');
        $method->setAccessible(true);
        return $method->invoke($rxList, $page);
    }

    /**
     * Test that parseToTokens properly splits content by tags
     */
    public function testParseToTokensSplitsByTags(): void
    {
        $rxList = new RxList();
        
        // Test with XML-like content
        $page = '<name>Aspirin</name><rxcui>1191</rxcui>';
        $result = $this->invokeParseToTokens($rxList, $page);
        
        // Should return an array
        $this->assertIsArray($result);
        
        // Should have parsed the content into tokens (tags and content separated)
        $this->assertNotEmpty($result);
        $this->assertCount(6, $result);
        
        // Verify token content - tags and content are now separated
        $this->assertEquals('<name>', $result[0]);
        $this->assertEquals('Aspirin', $result[1]);
        $this->assertEquals('</name>', $result[2]);
        $this->assertEquals('<rxcui>', $result[3]);
        $this->assertEquals('1191', $result[4]);
        $this->assertEquals('</rxcui>', $result[5]);
    }

    /**
     * Test parseToTokens with empty string
     */
    public function testParseToTokensWithEmptyString(): void
    {
        $rxList = new RxList();
        $result = $this->invokeParseToTokens($rxList, '');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test parseToTokens with plain text (no tags)
     */
    public function testParseToTokensWithPlainText(): void
    {
        $rxList = new RxList();
        $result = $this->invokeParseToTokens($rxList, 'plain text');
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('plain text', $result[0]);
    }

    /**
     * Test parseToTokens with multiple tags
     */
    public function testParseToTokensWithMultipleTags(): void
    {
        $rxList = new RxList();
        $page = '<name>Drug A</name><synonym>Generic A</synonym><rxcui>12345</rxcui>';
        $result = $this->invokeParseToTokens($rxList, $page);
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Should have tokens for each tag and content
        $this->assertContains('<name>', $result);
        $this->assertContains('</name>', $result);
        $this->assertContains('<synonym>', $result);
        $this->assertContains('</synonym>', $result);
        $this->assertContains('<rxcui>', $result);
        $this->assertContains('</rxcui>', $result);
    }

    /**
     * Test that tokensToHash works correctly with the new tokenization
     */
    public function testTokensToHashIntegration(): void
    {
        $rxList = new RxList();
        $page = '<name>Aspirin</name><synonym>Acetylsalicylic acid</synonym><rxcui>1191</rxcui>';
        $tokens = $this->invokeParseToTokens($rxList, $page);
        $hash = $rxList->tokensToHash($tokens);
        
        $this->assertIsArray($hash);
        $this->assertCount(1, $hash);
        $this->assertEquals('Aspirin', $hash[0]['name']);
        $this->assertEquals('Acetylsalicylic acid', $hash[0]['synonym']);
    }
}
// End of AI-generated code by GitHub Copilot
