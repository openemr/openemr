<?php

/**
 * End-to-end test for PatientPortalPDFDocumentCreator::createPdfObject().
 *
 * Exercises the full render pipeline (sanitizeCss + HTMLPurifier +
 * SameOriginUriFilter + mPDF) on inputs that pull remote or local
 * resources, and verifies that:
 *   - the resulting PDF byte stream never contains the contents of
 *     a local file referenced via file:// or php:// stream wrappers,
 *   - cross-origin http(s) resource references are stripped before
 *     mPDF processes the HTML,
 *   - the mPDF whitelistStreamWrappers property reflects the
 *     pdf_allowed_host configuration (empty when blank; http/https
 *     only when a trusted host is set).
 *
 * The sanitizer-only properties are covered separately in
 * SanitizeCssTest and the URI-filter decisions in
 * SameOriginUriFilterTest. This test locks in that the full pipeline
 * composition carries those properties through to the rendered PDF.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Pdf;

use OpenEMR\Common\Session\SessionConfiguration;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Session\Storage\ReadAndCloseNativeSessionStorage;
use OpenEMR\Common\Session\WriteThroughSession;
use OpenEMR\Pdf\PatientPortalPDFDocumentCreator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class CreatePdfObjectTest extends TestCase
{
    /**
     * Globals Config_Mpdf and PatientPortalPDFDocumentCreator read through
     * OEGlobalsBag (which sources values from $GLOBALS).
     */
    private const PDF_GLOBALS = [
        'pdf_language' => 'en',
        'pdf_size' => 'LETTER',
        'pdf_font_size' => '10',
        'pdf_left_margin' => '5',
        'pdf_right_margin' => '5',
        'pdf_top_margin' => '5',
        'pdf_bottom_margin' => '8',
        'pdf_layout' => 'P',
        'pdf_allowed_host' => '',
    ];

    /** @var array<string, mixed> */
    private array $originalGlobals = [];

    private string $markerFile = '';

    private string $marker = '';

    protected function setUp(): void
    {
        $this->originalGlobals = array_intersect_key($GLOBALS, self::PDF_GLOBALS);
        foreach (self::PDF_GLOBALS as $key => $value) {
            $GLOBALS[$key] = $value;
        }

        $storage = new ReadAndCloseNativeSessionStorage(new SessionConfiguration(
            name: 'CreatePdfObjectTest',
            cookiePath: '/',
            useCookies: false,
            useOnlyCookies: false,
            readAndClose: true,
        ));
        $session = new WriteThroughSession($storage, new AttributeBag('CreatePdfObjectTest'));
        $session->start();
        SessionWrapperFactory::getInstance()->setActiveSession($session, $storage);

        // Marker file read via file:// / php:// in the attack shapes. Written to a
        // temp path so the test is deterministic across hosts.
        $this->markerFile = (string) tempnam(sys_get_temp_dir(), 'pdf-render-test-');
        $this->marker = 'OPENEMR-PDF-RENDER-TEST-MARKER-' . bin2hex(random_bytes(8));
        file_put_contents($this->markerFile, $this->marker);
    }

    protected function tearDown(): void
    {
        foreach (array_keys(self::PDF_GLOBALS) as $key) {
            unset($GLOBALS[$key]);
        }
        foreach ($this->originalGlobals as $key => $value) {
            $GLOBALS[$key] = $value;
        }
        if ($this->markerFile !== '' && file_exists($this->markerFile)) {
            @unlink($this->markerFile);
        }
    }

    /**
     * Each row builds HTML that references the marker file through a channel
     * the renderer must not resolve. The assertion is that the rendered PDF
     * byte stream contains neither the raw marker nor its base64 encoding.
     *
     * @param callable(string): string $htmlBuilder
     */
    #[DataProvider('localFileReferenceProvider')]
    public function testPipelineDoesNotLeakLocalFileContents(callable $htmlBuilder): void
    {
        $html = $htmlBuilder($this->markerFile);
        $pdfBytes = self::renderToBytes($html);

        $this->assertStringNotContainsString(
            $this->marker,
            $pdfBytes,
            'Raw marker from local file leaked into rendered PDF'
        );
        $this->assertStringNotContainsString(
            base64_encode($this->marker),
            $pdfBytes,
            'Base64-encoded marker from local file leaked into rendered PDF'
        );
    }

    /**
     * Each row: [callable htmlBuilder]. htmlBuilder receives the absolute
     * path to the marker file and returns HTML referencing it via a channel
     * the renderer must not resolve.
     *
     * @return array<string, array{callable(string): string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function localFileReferenceProvider(): array
    {
        return [
            'at-import-url-file-scheme' => [
                static fn(string $path): string => '<style>@import url("file://' . $path . '");</style><p>x</p>',
            ],
            'at-import-quoted-file-scheme' => [
                static fn(string $path): string => '<style>@import "file://' . $path . '";</style><p>x</p>',
            ],
            'at-import-bare-file-scheme' => [
                static fn(string $path): string => '<style>@import file://' . $path . ';</style><p>x</p>',
            ],
            'style-url-file-scheme' => [
                static fn(string $path): string => '<style>body { background: url("file://' . $path . '"); }</style><p>x</p>',
            ],
            'img-src-file-scheme' => [
                static fn(string $path): string => '<p>x</p><img src="file://' . $path . '">',
            ],
            'img-src-php-filter' => [
                static fn(string $path): string => '<p>x</p><img src="php://filter/convert.base64-encode/resource=' . $path . '">',
            ],
            'img-src-data-svg-wrapping-php-filter' => [
                static function (string $path): string {
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">'
                        . '<image xlink:href=":/php://filter/convert.base64-encode/resource=' . $path . '" width="20" height="20"/>'
                        . '</svg>';
                    return '<p>x</p><img src="data:image/svg+xml;base64,' . base64_encode($svg) . '">';
                },
            ],
        ];
    }

    public function testCrossOriginImgSrcIsStrippedBeforeRender(): void
    {
        $GLOBALS['pdf_allowed_host'] = 'openemr.local';
        $html = '<p>x</p><img src="http://other.example/img.png">';
        $pdfBytes = self::renderToBytes($html);
        $this->assertStringNotContainsString('other.example', $pdfBytes);
    }

    public function testCrossOriginStyleUrlIsStrippedBeforeRender(): void
    {
        $GLOBALS['pdf_allowed_host'] = 'openemr.local';
        $html = '<style>body { background: url("http://other.example/img.png"); }</style><p>x</p>';
        $pdfBytes = self::renderToBytes($html);
        $this->assertStringNotContainsString('other.example', $pdfBytes);
    }

    public function testStreamWrappersAreDisabledWhenAllowedHostIsBlank(): void
    {
        $GLOBALS['pdf_allowed_host'] = '';
        $pdf = (new PatientPortalPDFDocumentCreator())->createPdfObject('<p>x</p>');
        $this->assertSame([], $pdf->whitelistStreamWrappers);
    }

    public function testHttpStreamWrappersAreEnabledWhenAllowedHostIsSet(): void
    {
        $GLOBALS['pdf_allowed_host'] = 'openemr.local';
        $pdf = (new PatientPortalPDFDocumentCreator())->createPdfObject('<p>x</p>');
        $this->assertSame(['http', 'https'], $pdf->whitelistStreamWrappers);
    }

    public function testStreamWrapperConfigDoesNotDependOnRequestHostHeader(): void
    {
        $GLOBALS['pdf_allowed_host'] = 'canonical.openemr.local';
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        $_SERVER['HTTP_HOST'] = 'other.example';
        try {
            $pdf = (new PatientPortalPDFDocumentCreator())->createPdfObject('<p>x</p>');
            $this->assertSame(['http', 'https'], $pdf->whitelistStreamWrappers);
        } finally {
            if ($originalHost === null) {
                unset($_SERVER['HTTP_HOST']);
            } else {
                $_SERVER['HTTP_HOST'] = $originalHost;
            }
        }
    }

    public function testBenignHtmlRendersSuccessfully(): void
    {
        $pdfBytes = self::renderToBytes('<p>Patient Name: Example</p>');
        $this->assertGreaterThan(1000, strlen($pdfBytes));
        $this->assertStringStartsWith('%PDF-', $pdfBytes);
    }

    private static function renderToBytes(string $html): string
    {
        $output = (new PatientPortalPDFDocumentCreator())
            ->createPdfObject($html)
            ->Output('', 'S');
        if (!is_string($output)) {
            self::fail('mPDF Output(S) returned non-string: ' . get_debug_type($output));
        }
        return $output;
    }
}
