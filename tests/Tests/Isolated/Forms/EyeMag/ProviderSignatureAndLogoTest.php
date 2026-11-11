<?php

/**
 * Isolated tests for Eye Form site-scoped provider signatures and practice logos.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use GdImage;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\ProviderSignature;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function OpenEMR\Forms\EyeMag\Assets\eye_mag_logo_path;
use function OpenEMR\Forms\EyeMag\Assets\eye_mag_logo_preview;
use function OpenEMR\Forms\EyeMag\Assets\eye_mag_logo_replace;
use function OpenEMR\Forms\EyeMag\Assets\eye_mag_logo_site_path;
use function OpenEMR\Forms\EyeMag\Assets\eye_mag_save_practice_logo;

require_once dirname(__DIR__, 5) . '/interface/forms/eye_mag/php/practice_logo.php';

final class ProviderSignatureAndLogoTest extends TestCase
{
    private string $tempRoot;

    private string $siteDir;

    private string $projectDir;

    protected function setUp(): void
    {
        $this->tempRoot = sys_get_temp_dir() . '/eye-sig-logo-' . bin2hex(random_bytes(5));
        $this->siteDir = $this->tempRoot . '/sites/clinic';
        $this->projectDir = $this->tempRoot . '/project';
        mkdir($this->siteDir . '/documents', 0755, true);
        mkdir($this->siteDir . '/images/eye_mag/signatures', 0755, true);
        mkdir($this->projectDir . '/interface/forms/eye_mag/images', 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempRoot);
    }

    #[Test]
    public function providerSignaturePrefersRegularSiteFileAndFallsBackOtherwise(): void
    {
        $bag = new OEGlobalsBag([
            'OE_SITE_DIR' => $this->siteDir,
            'OE_SITE_WEBROOT' => '/openemr/sites/clinic',
            'fileroot' => $this->projectDir,
            'webroot' => '/openemr',
        ]);
        $signature = ProviderSignature::fromGlobals($bag);

        $this->assertSame(
            $this->siteDir . '/images/eye_mag/signatures/sign_7.jpg',
            $signature->sitePath(7)
        );
        $this->assertSame(
            '/openemr/sites/clinic/images/eye_mag/signatures/sign_7.jpg',
            $signature->siteUrl(7)
        );

        // Missing site signature falls back to legacy application directory.
        $this->assertSame(
            $this->projectDir . '/interface/forms/eye_mag/images/sign_7.jpg',
            $signature->path(7)
        );
        $this->assertSame(
            '/openemr/interface/forms/eye_mag/images/sign_7.jpg',
            $signature->url(7)
        );

        // Regular site signature takes precedence.
        file_put_contents($signature->sitePath(7), 'jpeg-bytes');
        $this->assertSame($signature->sitePath(7), $signature->path(7));
        $this->assertSame($signature->siteUrl(7), $signature->url(7));

        // Symlinked site signature is rejected in favor of legacy path.
        $outside = $this->tempRoot . '/outside.jpg';
        file_put_contents($outside, 'outside');
        symlink($outside, $signature->sitePath(8));
        $this->assertSame(
            $this->projectDir . '/interface/forms/eye_mag/images/sign_8.jpg',
            $signature->path(8)
        );
        $this->assertSame(
            '/openemr/interface/forms/eye_mag/images/sign_8.jpg',
            $signature->url(8)
        );
    }

    #[Test]
    public function practiceLogoSavesAtomicallyAndRendersPrivateDataUriOrLegacyUrl(): void
    {
        $this->assertSame('', eye_mag_logo_preview($this->siteDir, '/openemr'));

        // Legacy fallback under sites/<site>/images/practice_logo.gif uses a cache-busted URL.
        $legacyPath = $this->siteDir . '/images/practice_logo.gif';
        file_put_contents($legacyPath, 'GIF89a');
        $this->assertSame($legacyPath, eye_mag_logo_path($this->siteDir));
        $legacyPreview = eye_mag_logo_preview($this->siteDir, '/openemr/');
        $this->assertStringStartsWith('/openemr/sites/clinic/images/practice_logo.gif?t=', $legacyPreview);

        // Saving a site practice logo writes to documents/practice_assets and serves a data URI.
        $image = $this->sampleImage();
        $this->assertTrue(eye_mag_save_practice_logo($image, $this->siteDir));
        $siteLogo = eye_mag_logo_site_path($this->siteDir);
        $this->assertFileExists($siteLogo);
        $this->assertSame($siteLogo, eye_mag_logo_path($this->siteDir));
        $dataUri = eye_mag_logo_preview($this->siteDir, '/openemr');
        $this->assertStringStartsWith('data:image/gif;base64,', $dataUri);

        // Replacing an existing logo succeeds atomically.
        $this->assertTrue(eye_mag_save_practice_logo($this->sampleImage(), $this->siteDir));
    }

    #[Test]
    public function practiceLogoRefusesMissingDocumentsDirectoryAndSymlinkTargets(): void
    {
        $unprovisionedSite = $this->tempRoot . '/sites/unprovisioned';
        mkdir($unprovisionedSite, 0755, true);
        $this->assertFalse(eye_mag_save_practice_logo($this->sampleImage(), $unprovisionedSite));

        // Symlinked destination is refused by both eye_mag_logo_replace and eye_mag_save_practice_logo.
        $assetsDir = $this->siteDir . '/documents/practice_assets';
        mkdir($assetsDir, 0755, true);
        $outside = $this->tempRoot . '/outside.gif';
        file_put_contents($outside, 'GIF89a');
        $symlinkPath = eye_mag_logo_site_path($this->siteDir);
        symlink($outside, $symlinkPath);

        $tempCandidate = $this->tempRoot . '/candidate.gif';
        file_put_contents($tempCandidate, 'new-bytes');
        $this->assertFalse(eye_mag_logo_replace($tempCandidate, $symlinkPath));
        $this->assertFalse(eye_mag_save_practice_logo($this->sampleImage(), $this->siteDir));
        $this->assertSame('GIF89a', file_get_contents($outside));
    }

    private function sampleImage(): GdImage
    {
        $image = imagecreatetruecolor(12, 8);
        $this->assertInstanceOf(GdImage::class, $image);
        $blue = imagecolorallocate($image, 30, 90, 160);
        $this->assertIsInt($blue);
        imagefill($image, 0, 0, $blue);
        return $image;
    }

    private function removeDirectory(string $directory): void
    {
        if (!file_exists($directory) && !is_link($directory)) {
            return;
        }
        if (is_link($directory) || is_file($directory)) {
            unlink($directory);
            return;
        }
        $entries = scandir($directory);
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $this->removeDirectory($directory . '/' . $entry);
            }
        }
        rmdir($directory);
    }
}
