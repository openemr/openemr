<?php

/**
 * Isolated tests for Eye Form provider drawing backgrounds, saved overlays, and cross-user sharing.
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
use InvalidArgumentException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_csrf_token;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_csrf_verify;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_default;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_key_valid;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_manage;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_metadata;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_name;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_options;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_save;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_save_bytes;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_scope;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_options;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_paths;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_preference_storage;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_share;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_upload;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_uploaded_image;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_webroot;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_zone_valid;

require_once dirname(__DIR__, 5) . '/library/translation.inc.php';
require_once dirname(__DIR__, 5) . '/interface/forms/eye_mag/php/drawing_backgrounds.php';

final class DrawingBackgroundsTest extends TestCase
{
    private string $tempRoot;

    private string $siteDir;

    /** @var array<string, array{present: bool, value: mixed}> */
    private array $savedGlobals = [];

    protected function setUp(): void
    {
        $this->tempRoot = sys_get_temp_dir() . '/eye-drawing-bg-' . bin2hex(random_bytes(5));
        $this->siteDir = $this->tempRoot . '/sites/eyes';
        mkdir($this->siteDir . '/images/eye_mag', 0755, true);

        $bag = OEGlobalsBag::getInstance();
        foreach (['disable_translation', 'OE_SITE_DIR', 'OE_SITE_WEBROOT', 'webroot', 'fileroot'] as $key) {
            $this->savedGlobals[$key] = [
                'present' => $bag->has($key),
                'value' => $bag->get($key),
            ];
        }
        $bag->set('disable_translation', true);
        $bag->set('OE_SITE_DIR', $this->siteDir);
        $bag->set('OE_SITE_WEBROOT', '/openemr/sites/eyes');
        $bag->set('webroot', '/openemr/');
        $bag->set('fileroot', dirname(__DIR__, 5));
    }

    protected function tearDown(): void
    {
        $bag = OEGlobalsBag::getInstance();
        foreach ($this->savedGlobals as $key => $state) {
            if ($state['present']) {
                $bag->set($key, $state['value']);
            } else {
                $bag->remove($key);
            }
        }
        $this->removeDirectory($this->tempRoot);
    }

    #[Test]
    public function csrfHelpersIssueAndVerifySessionScopedTokens(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $this->assertFalse(eye_mag_drawing_background_csrf_verify('', $session));
        $this->assertFalse(eye_mag_drawing_background_csrf_verify(['bad'], $session));
        $this->assertFalse(eye_mag_drawing_background_csrf_verify('missing-session-key', $session));

        CsrfUtils::setupCsrfKey($session);
        $token = eye_mag_drawing_background_csrf_token($session);
        $this->assertNotSame('', $token);
        $this->assertTrue(eye_mag_drawing_background_csrf_verify($token, $session));
        $this->assertFalse(eye_mag_drawing_background_csrf_verify($token . 'x', $session));
    }

    #[Test]
    public function zoneKeyScopeAndPreferenceHelpersEnforceBoundaries(): void
    {
        foreach (['HPI', 'PMH', 'EXT', 'ANTSEG', 'RETINA', 'SDRETINA', 'NEURO', 'IMPPLAN'] as $zone) {
            $this->assertTrue(eye_mag_drawing_background_zone_valid($zone));
        }
        $this->assertFalse(eye_mag_drawing_background_zone_valid('../RETINA'));
        $this->assertFalse(eye_mag_drawing_background_zone_valid(123));

        $this->assertSame('large-retina', eye_mag_drawing_background_default('SDRETINA'));
        $this->assertSame('default', eye_mag_drawing_background_default('RETINA'));
        $this->assertSame('/openemr', eye_mag_drawing_background_webroot());

        $this->assertTrue(eye_mag_drawing_background_key_valid('default', 'EXT'));
        $this->assertTrue(eye_mag_drawing_background_key_valid('stock-retina', 'EXT'));
        $this->assertTrue(eye_mag_drawing_background_key_valid('custom-0123456789abcdef', 'EXT'));
        $this->assertTrue(eye_mag_drawing_background_key_valid('saved-0123456789abcdef', 'EXT'));
        $this->assertTrue(eye_mag_drawing_background_key_valid('x-practice', 'RETINA'));
        $this->assertTrue(eye_mag_drawing_background_key_valid('x-saved-0123456789abcdef', 'SDRETINA'));
        $this->assertFalse(eye_mag_drawing_background_key_valid('x-practice', 'EXT'));
        $this->assertFalse(eye_mag_drawing_background_key_valid('saved-NOTHEX0123456789', 'RETINA'));

        $this->assertSame(['RETINA', 'saved-0123456789abcdef'], eye_mag_drawing_background_library_scope('RETINA', 'saved-0123456789abcdef'));
        $this->assertSame(['SDRETINA', 'saved-0123456789abcdef'], eye_mag_drawing_background_library_scope('RETINA', 'x-saved-0123456789abcdef'));
        $this->assertSame(['RETINA', 'custom-0123456789abcdef'], eye_mag_drawing_background_library_scope('SDRETINA', 'x-custom-0123456789abcdef'));

        $this->assertSame(['blank', ''], eye_mag_drawing_background_preference_storage('blank'));
        $this->assertSame(['@image', 'saved-0123456789abcdef'], eye_mag_drawing_background_preference_storage('saved-0123456789abcdef'));
    }

    #[Test]
    public function invalidLibraryScopeAndPathsThrowExpectedExceptions(): void
    {
        try {
            eye_mag_drawing_background_library_scope('EXT', 'x-saved-0123456789abcdef');
            $this->fail('Expected cross-zone prefix on EXT to fail.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('Invalid drawing library scope', $exception->getMessage());
        }

        try {
            eye_mag_drawing_background_library_scope('RETINA', 'default');
            $this->fail('Expected stock key to be rejected as library scope.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('Choose a saved drawing image', $exception->getMessage());
        }

        try {
            eye_mag_drawing_background_paths('RETINA', '07');
            $this->fail('Expected zero-padded provider ID to fail.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('Invalid drawing background scope', $exception->getMessage());
        }

        OEGlobalsBag::getInstance()->set('OE_SITE_DIR', $this->tempRoot . '/missing-site');
        $this->expectException(RuntimeException::class);
        eye_mag_drawing_background_paths('RETINA', 5);
    }

    #[Test]
    public function libraryNameNormalizesValidLabelsAndRejectsControlOrOversizeInput(): void
    {
        $this->assertSame('Lattice degeneration', eye_mag_drawing_background_library_name('  Lattice degeneration  '));

        foreach (['   ', "Bad\nName", str_repeat('a', 81), "\xFF\xFE"] as $invalid) {
            try {
                eye_mag_drawing_background_library_name($invalid);
                $this->fail('Expected invalid library name to throw.');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('1 to 80 characters', $exception->getMessage());
            }
        }
    }

    #[Test]
    public function uploadNormalizesImagesDeduplicatesAndExposesCrossRetinaOptions(): void
    {
        // Practice background in RETINA should appear directly in RETINA and as x-practice in SDRETINA.
        touch($this->siteDir . '/images/eye_mag/OU_RETINA_BASE.jpg');

        $pngFile = $this->tempImageFile('png', 80, 60);
        $uploaded = new UploadedFile($pngFile, 'lattice.png', 'image/png', null, true);
        [$decoded, $width, $height] = eye_mag_drawing_background_uploaded_image($uploaded);
        imagedestroy($decoded);
        $this->assertSame(80, $width);
        $this->assertSame(60, $height);

        $customKey = eye_mag_drawing_background_upload('RETINA', 11, new UploadedFile($pngFile, 'lattice.png', 'image/png', null, true));
        $this->assertMatchesRegularExpression('/\Acustom-[a-f0-9]{16}\z/', $customKey);
        // Re-uploading the identical image returns the same content-addressed key.
        $this->assertSame($customKey, eye_mag_drawing_background_upload('RETINA', 11, new UploadedFile($pngFile, 'copy.png', 'image/png', null, true)));

        // Save a named reusable overlay in RETINA, rename it, and verify natural ordering and SDRETINA visibility.
        $savedKey = eye_mag_drawing_background_library_save(
            'RETINA',
            11,
            new UploadedFile($pngFile, 'overlay.png', 'image/png', null, true),
            ' Horseshoe tear '
        );
        $this->assertMatchesRegularExpression('/\Asaved-[a-f0-9]{16}\z/', $savedKey);

        eye_mag_drawing_background_library_manage('RETINA', 11, 'rename', $savedKey, ' Lattice tear 2 ');
        $paths = eye_mag_drawing_background_paths('RETINA', 11);
        $metadata = eye_mag_drawing_background_library_metadata($paths, $savedKey);
        $this->assertSame('Lattice tear 2', $metadata->label);

        // Saving identical bytes + label reuses the existing key; a new label stores a second entry.
        $secondKey = eye_mag_drawing_background_library_save_bytes($paths, 'png-raw-10', 'Lattice tear 10');
        $this->assertSame($secondKey, eye_mag_drawing_background_library_save_bytes($paths, 'png-raw-10', 'Lattice tear 10'));
        $libraryList = eye_mag_drawing_background_library_options($paths);
        $this->assertSame(['Lattice tear 2', 'Lattice tear 10'], array_map(static fn($opt): string => $opt->label, $libraryList));

        // SDRETINA sees ROP, its own default, and RETINA's practice/saved/custom items with the x- prefix.
        $sdOptions = eye_mag_drawing_background_options('SDRETINA', 11, 'OU');
        $sdKeys = array_map(static fn($opt): string => $opt->key, $sdOptions);
        $this->assertContains('large-retina', $sdKeys);
        $this->assertContains('retina', $sdKeys);
        $this->assertContains('x-practice', $sdKeys);
        $this->assertContains('x-' . $savedKey, $sdKeys);
        $this->assertContains('x-' . $customKey, $sdKeys);
        $this->assertContains('blank', $sdKeys);

        // Share both the saved overlay (via cross-zone x- key) and uploaded custom background to provider 22.
        $sharedOverlayKey = eye_mag_drawing_background_share('SDRETINA', 11, 22, 'x-' . $savedKey);
        $this->assertMatchesRegularExpression('/\Asaved-[a-f0-9]{16}\z/', $sharedOverlayKey);
        $recipientPaths = eye_mag_drawing_background_paths('RETINA', 22);
        $this->assertSame('Lattice tear 2', eye_mag_drawing_background_library_metadata($recipientPaths, $sharedOverlayKey)->label);

        $sharedCustomKey = eye_mag_drawing_background_share('RETINA', 11, 22, $customKey);
        $this->assertSame($customKey, $sharedCustomKey);

        // Sharing to oneself is rejected.
        $this->expectException(InvalidArgumentException::class);
        eye_mag_drawing_background_share('RETINA', 11, 11, $customKey);
    }

    #[Test]
    public function uploadedImageRejectsNonImageAndInvalidPayloads(): void
    {
        try {
            eye_mag_drawing_background_uploaded_image('not-a-file');
            $this->fail('Expected non-UploadedFile to throw.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('JPEG or PNG', $exception->getMessage());
        }

        $textPath = $this->tempRoot . '/plain.txt';
        file_put_contents($textPath, 'not image bytes');
        $this->expectException(InvalidArgumentException::class);
        eye_mag_drawing_background_uploaded_image(new UploadedFile($textPath, 'plain.txt', 'text/plain', null, true));
    }

    private function tempImageFile(string $format, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        $this->assertInstanceOf(GdImage::class, $image);
        $color = imagecolorallocate($image, 40, 120, 200);
        $this->assertIsInt($color);
        imagefill($image, 0, 0, $color);
        $path = $this->tempRoot . '/sample-' . bin2hex(random_bytes(4)) . '.' . $format;
        if ($format === 'png') {
            imagepng($image, $path);
        } else {
            imagejpeg($image, $path, 90);
        }
        imagedestroy($image);
        return $path;
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
