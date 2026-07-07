<?php

/**
 * Tests for the {headerTemplate} Smarty plugin / OpenEMR\Core\Header.
 *
 * Regression guard for the bug where a single {headerTemplate} tag emitted the
 * page header (jQuery, Bootstrap, etc.) twice: Header::setupHeader() defaults to
 * $echoOutput = true (echo + return), and a Smarty function plugin's return value
 * is printed at the tag, so the plugin must call setupHeader() in return-only mode.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Discover and Change AI Worker <ai-worker@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Core;

use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;
use Smarty;

class HeaderTest extends TestCase
{
    /** Assets requested in the tests; each maps to a script that must appear exactly once. */
    private const ASSET_MARKERS = [
        'jquery/dist/jquery.min.js',
        'js/bootstrap.bundle.min.js',
        'select2.full.min.js',
    ];

    private string $compileDir = '';

    private string $fileRoot = '';

    protected function setUp(): void
    {
        $this->fileRoot = OEGlobalsBag::getInstance()->getString('fileroot');
        // Ensure the plugin function is defined for the direct-call tests.
        require_once $this->fileRoot . '/library/smarty/plugins/function.headerTemplate.php';
    }

    protected function tearDown(): void
    {
        if ($this->compileDir !== '' && is_dir($this->compileDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->compileDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $path) {
                if (!$path instanceof \SplFileInfo) {
                    continue;
                }
                if ($path->isDir()) {
                    rmdir($path->getPathname());
                    continue;
                }
                unlink($path->getPathname());
            }

            rmdir($this->compileDir);
        }
    }

    /**
     * The plugin must RETURN its markup and NOT echo it. This is the direct guard
     * against the regression: if the plugin reverts to setupHeader($assets) (echo
     * defaulting to true), $echoed becomes non-empty and this fails.
     */
    public function testHeaderTemplatePluginReturnsMarkupWithoutEchoing(): void
    {
        $smarty = new Smarty();

        ob_start();
        $returned = smarty_function_headerTemplate(['assets' => 'datetime-picker|select2'], $smarty);
        $echoed = ob_get_clean();

        $this->assertSame('', $echoed, 'The {headerTemplate} plugin must not echo; it must only return markup.');
        $this->assertIsString($returned, 'The {headerTemplate} plugin must return a markup string.');
        $this->assertStringContainsString('js/bootstrap.bundle.min.js', $returned);
    }

    /**
     * Header::setupHeader($assets, false) is the contract the plugin relies on:
     * return the markup, echo nothing.
     */
    public function testSetupHeaderReturnOnlyDoesNotEcho(): void
    {
        ob_start();
        $returned = Header::setupHeader([], false);
        $echoed = ob_get_clean();

        $this->assertSame('', $echoed, 'setupHeader(..., false) must not echo.');
        $this->assertStringContainsString('js/bootstrap.bundle.min.js', $returned);
    }

    /**
     * With $echoOutput = true the markup is both echoed and returned (the default
     * used by direct PHP callers). Documents why the Smarty plugin must pass false.
     */
    public function testSetupHeaderEchoesAndReturnsWhenRequested(): void
    {
        ob_start();
        $returned = Header::setupHeader([], true);
        $echoed = ob_get_clean();

        $this->assertNotSame('', $echoed, 'setupHeader(..., true) must echo the markup.');
        $this->assertSame($echoed, $returned, 'Echoed and returned markup must be identical.');
    }

    /**
     * End-to-end: rendering a Smarty template that uses {headerTemplate} must emit
     * each asset exactly once. Pre-fix this produced two copies of the whole header.
     */
    public function testHeaderTemplateRendersAssetsExactlyOnce(): void
    {
        $this->compileDir = sys_get_temp_dir() . '/oe-headertest-' . bin2hex(random_bytes(8));
        $this->assertTrue(mkdir($this->compileDir), "Failed to create temp compile dir: {$this->compileDir}");

        $smarty = new Smarty();
        $smarty->setCompileDir($this->compileDir);
        $smarty->setPluginsDir([
            $this->fileRoot . '/library/smarty/plugins',
            $this->fileRoot . '/vendor/smarty/smarty/libs/plugins',
        ]);

        $html = $smarty->fetch("eval:{headerTemplate assets='datetime-picker|select2'}");

        foreach (self::ASSET_MARKERS as $marker) {
            $this->assertSame(
                1,
                substr_count($html, $marker),
                "Asset '$marker' must be included exactly once (duplicate header = the regression)."
            );
        }
    }
}
