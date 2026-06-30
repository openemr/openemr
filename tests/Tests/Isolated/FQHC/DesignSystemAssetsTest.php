<?php

/**
 * Smoke test for the FQHC design-system asset bundle (Step 1).
 *
 * Verifies the design-system foundation is wired and present: the asset
 * resolver reports an intact bundle, the bundle files exist on disk, tokens
 * load before component styles, and the token/component contracts the UI
 * depends on are actually defined. Runs in isolation (no DB/Docker).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC;

use OpenEMR\FQHC\DesignSystem\DesignSystemAssets;
use PHPUnit\Framework\TestCase;

final class DesignSystemAssetsTest extends TestCase
{
    private const MODULE_PUBLIC_REL =
        '/interface/modules/custom_modules/oe-module-fqhc/public';

    private string $publicRoot;

    protected function setUp(): void
    {
        $repoRoot = dirname(__DIR__, 4);
        $this->publicRoot = $repoRoot . self::MODULE_PUBLIC_REL;
    }

    public function testBundleFilesAllExist(): void
    {
        $assets = new DesignSystemAssets($this->publicRoot, '/base');

        self::assertSame(
            [],
            $assets->missingFiles(),
            'Every design-system asset declared in the bundle must exist on disk.',
        );
    }

    public function testMissingFilesAreReportedWhenRootIsWrong(): void
    {
        $assets = new DesignSystemAssets($this->publicRoot . '/does-not-exist', '/base');

        self::assertNotEmpty(
            $assets->missingFiles(),
            'A bad public root must surface as missing files, not silently pass.',
        );
    }

    public function testTokensStylesheetLoadsFirst(): void
    {
        // tokens.css defines the custom properties the rest of the system needs,
        // so it must be the first stylesheet in load order.
        self::assertStringContainsString('tokens.css', DesignSystemAssets::STYLES[0]);
    }

    public function testStyleUrlsAreBasedAndCacheBusted(): void
    {
        $assets = new DesignSystemAssets($this->publicRoot, 'https://example.test/base/');
        $urls = $assets->styleUrls();

        self::assertNotEmpty($urls);
        foreach ($urls as $url) {
            self::assertStringStartsWith('https://example.test/base/assets/css/', $url);
            self::assertMatchesRegularExpression('/\?v=\d+$/', $url, 'Assets must be cache-busted by mtime.');
        }
    }

    public function testTokensFileDefinesCorePropertiesTheUiDependsOn(): void
    {
        $tokens = (string) file_get_contents($this->publicRoot . '/assets/css/tokens.css');

        foreach ([
            '--fqhc-color-primary',
            '--fqhc-surface-card',
            '--fqhc-text',
            '--fqhc-space-4',
            '--fqhc-radius-lg',
            '--fqhc-focus-ring',
        ] as $token) {
            self::assertStringContainsString($token, $tokens, "Design token {$token} must be defined.");
        }
    }

    public function testComponentScriptDefinesEveryDeclaredElement(): void
    {
        $script = (string) file_get_contents($this->publicRoot . '/assets/js/fqhc-components.js');

        foreach ([
            'fqhc-page-header',
            'fqhc-card',
            'fqhc-field-row',
            'fqhc-status-badge',
            'fqhc-empty-state',
        ] as $element) {
            self::assertStringContainsString(
                "customElements.define('{$element}'",
                $script,
                "Web Component <{$element}> must be registered.",
            );
        }
    }
}
