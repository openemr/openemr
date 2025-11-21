<?php

namespace OpenEMR\Tests\Unit\Common\Twig;

use OpenEMR\Common\Twig\TwigExtension;
use OpenEMR\Services\Globals\GlobalsService;
use PHPUnit\Framework\TestCase;

class TwigExtensionTest extends TestCase
{
    /**
     * Test that getGlobals() handles missing array keys gracefully
     * This test specifically addresses the "Undefined array key 'webroot'" warning
     */
    public function testGetGlobalsWithMissingKeys(): void
    {
        // Create a GlobalsService with an empty globals array
        // This simulates the scenario in unit tests where $GLOBALS may not have all required keys
        $globalsService = new GlobalsService([], [], []);
        
        // Create TwigExtension with the GlobalsService
        $twigExtension = new TwigExtension($globalsService, null);
        
        // Call getGlobals() - this should not throw any warnings about undefined array keys
        $globals = $twigExtension->getGlobals();
        
        // Verify that all expected keys are present with default empty string values
        $this->assertIsArray($globals);
        $this->assertArrayHasKey('webroot', $globals);
        $this->assertArrayHasKey('assets_dir', $globals);
        $this->assertArrayHasKey('srcdir', $globals);
        $this->assertArrayHasKey('rootdir', $globals);
        $this->assertArrayHasKey('assetVersion', $globals);
        $this->assertArrayHasKey('session', $globals);
        
        // Verify default values for missing keys
        $this->assertEquals('', $globals['webroot']);
        $this->assertEquals('', $globals['assets_dir']);
        $this->assertEquals('', $globals['srcdir']);
        $this->assertEquals('', $globals['rootdir']);
        $this->assertEquals('', $globals['assetVersion']);
    }
    
    /**
     * Test that getGlobals() works correctly when all keys are present
     */
    public function testGetGlobalsWithAllKeys(): void
    {
        // Create a GlobalsService with all expected keys
        $globalsMetadata = [
            'webroot' => '/openemr',
            'assets_static_relative' => '/public/assets',
            'srcdir' => '/var/www/openemr/library',
            'rootdir' => '/var/www/openemr',
            'v_js_includes' => '1234567890',
        ];
        $globalsService = new GlobalsService($globalsMetadata, [], []);
        
        // Create TwigExtension with the GlobalsService
        $twigExtension = new TwigExtension($globalsService, null);
        
        // Call getGlobals()
        $globals = $twigExtension->getGlobals();
        
        // Verify that all values are correctly passed through
        $this->assertEquals('/openemr', $globals['webroot']);
        $this->assertEquals('/public/assets', $globals['assets_dir']);
        $this->assertEquals('/var/www/openemr/library', $globals['srcdir']);
        $this->assertEquals('/var/www/openemr', $globals['rootdir']);
        $this->assertEquals('1234567890', $globals['assetVersion']);
    }
}
