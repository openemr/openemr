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

namespace OpenEMR\Tests\Unit\Rx;

use OpenEMR\Rx\RxList;
use PHPUnit\Framework\TestCase;

class RxListTest extends TestCase
{
    /**
     * Test that parseToTokens properly initializes the tokens array
     * and doesn't produce undefined array key warnings
     */
    public function testParseToTokensInitializesArray(): void
    {
        $rxList = new RxList();
        
        // Test with XML-like content
        $page = '<name>Aspirin</name><rxcui>1191</rxcui>';
        $result = $rxList->parseToTokens($page);
        
        // Should return an array
        $this->assertIsArray($result);
        
        // Should have parsed the content into tokens
        $this->assertNotEmpty($result);
        $this->assertCount(4, $result);
        
        // Verify token content
        $this->assertEquals('<name>', $result[0]);
        $this->assertEquals('Aspirin</name>', $result[1]);
        $this->assertEquals('<rxcui>', $result[2]);
        $this->assertEquals('1191</rxcui>', $result[3]);
    }

    /**
     * Test parseToTokens with empty string
     */
    public function testParseToTokensWithEmptyString(): void
    {
        $rxList = new RxList();
        $result = $rxList->parseToTokens('');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test parseToTokens with plain text (no tags)
     */
    public function testParseToTokensWithPlainText(): void
    {
        $rxList = new RxList();
        $result = $rxList->parseToTokens('plain text');
        
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
        $result = $rxList->parseToTokens($page);
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Should have tokens for each tag and content
        $this->assertContains('<name>', $result);
        $this->assertContains('<synonym>', $result);
        $this->assertContains('<rxcui>', $result);
    }
}
