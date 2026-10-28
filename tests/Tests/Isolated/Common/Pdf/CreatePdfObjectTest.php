<?php

/**
 * End-to-end composition test for PatientPortalPDFDocumentCreator::createPdfObject().
 *
 * The sanitizer-level correctness checks (what sanitizeCss strips, which URIs
 * SameOriginUriFilter rejects) are covered directly in SanitizeCssTest and
 * SameOriginUriFilterTest. This test exercises the enclosing composition
 * (sanitizeCss + HTMLPurifier + SameOriginUriFilter + mPDF) and locks in
 * three invariants that are not visible from the unit level:
 *
 *   - mPDF's whitelistStreamWrappers property is set from the
 *     pdf_allowed_host global (empty when blank; http/https only when
 *     configured; unaffected by HTTP_HOST).
 *   - The pipeline renders without crashing on inputs that reference
 *     local files via file:// / php:// / data:+SVG channels, and
 *     produces a valid non-empty PDF document.
 *   - Benign HTML renders successfully with a well-formed %PDF- prefix.
 *
 * The pipeline-doesn't-crash tests do not assert that the sanitizer
 * stripped anything from the final PDF byte stream, because mPDF does
 * not emit hostnames or discarded resource URLs into the output regardless
 * of whether sanitization ran. The strip assertions live in the unit
 * tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
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
use ReflectionClass;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

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

    private ?SessionInterface $priorSession = null;

    private ?SessionStorageInterface $priorStorage = null;

    private string $priorSessionName = '';

    private bool $priorSessionGlobalExisted = false;

    /** @var array<array-key, mixed> */
    private array $priorSessionGlobal = [];

    protected function setUp(): void
    {
        $this->originalGlobals = array_intersect_key($GLOBALS, self::PDF_GLOBALS);
        foreach (self::PDF_GLOBALS as $key => $value) {
            $GLOBALS[$key] = $value;
        }

        // Capture everything the ReadAndCloseNativeSessionStorage constructor
        // and $session->start() mutate so later tests see the state that was
        // in place before this test ran.
        $currentName = session_name();
        $this->priorSessionName = is_string($currentName) ? $currentName : '';
        $this->priorSessionGlobalExisted = array_key_exists('_SESSION', $GLOBALS);
        if ($this->priorSessionGlobalExisted) {
            $this->priorSessionGlobal = $_SESSION;
        }

        $factory = SessionWrapperFactory::getInstance();
        if ($factory->isSessionActive()) {
            $this->priorSession = $factory->getActiveSession();
            $this->priorStorage = $factory->getActiveStorage();
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
        $factory->setActiveSession($session, $storage);
    }

    protected function tearDown(): void
    {
        foreach (array_keys(self::PDF_GLOBALS) as $key) {
            unset($GLOBALS[$key]);
        }
        foreach ($this->originalGlobals as $key => $value) {
            $GLOBALS[$key] = $value;
        }

        // Restore the SessionWrapperFactory singleton's slots individually.
        // setActiveSession() leaves activeStorage untouched when the storage
        // argument is null, so when the prior state had no storage we must
        // null both slots via reflection to avoid leaking this test's storage.
        $factory = SessionWrapperFactory::getInstance();
        $reflection = new ReflectionClass(SessionWrapperFactory::class);
        $reflection->getProperty('activeSession')->setValue($factory, $this->priorSession);
        $reflection->getProperty('activeStorage')->setValue($factory, $this->priorStorage);
        $this->priorSession = null;
        $this->priorStorage = null;

        if ($this->priorSessionName !== '') {
            session_name($this->priorSessionName);
        }
        if ($this->priorSessionGlobalExisted) {
            $_SESSION = $this->priorSessionGlobal;
        } else {
            unset($_SESSION);
        }
        $this->priorSessionGlobal = [];
    }

    /**
     * The pipeline must accept unusual resource references (file://, php://,
     * data:-wrapped SVG with nested :/php://filter) without throwing and
     * produce a valid PDF document. Whether the referenced content was fetched
     * or stripped is a sanitizer-level property covered by SanitizeCssTest and
     * SameOriginUriFilterTest; here we only assert the composition does not
     * crash on these inputs.
     *
     * @param callable(string): string $htmlBuilder
     */
    #[DataProvider('unusualResourceReferenceProvider')]
    public function testPipelineAcceptsUnusualResourceReferencesWithoutCrashing(callable $htmlBuilder): void
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'pdf-render-test-');
        try {
            file_put_contents($path, 'placeholder');
            $html = $htmlBuilder($path);
            $pdfBytes = self::renderToBytes($html);
            $this->assertStringStartsWith('%PDF-', $pdfBytes);
            $this->assertGreaterThan(1000, strlen($pdfBytes));
        } finally {
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Each row: [callable htmlBuilder]. htmlBuilder receives an absolute path
     * to a temp file and returns HTML referencing it via a non-http(s) channel.
     *
     * @return array<string, array{callable(string): string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unusualResourceReferenceProvider(): array
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
            'img-src-cross-origin-http' => [
                static fn(string $path): string => '<p>x</p><img src="http://other.example/img.png">',
            ],
            'style-url-cross-origin-http' => [
                static fn(string $path): string => '<style>body { background: url("http://other.example/img.png"); }</style><p>x</p>',
            ],
        ];
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

    public function testPipelineConfigDerivesFromAllowedHostGlobalNotRequestHostHeader(): void
    {
        $GLOBALS['pdf_allowed_host'] = 'canonical.openemr.local';
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        $_SERVER['HTTP_HOST'] = 'other.example';
        try {
            $pdf = (new PatientPortalPDFDocumentCreator())->createPdfObject('<p>x</p>');
            $this->assertSame(['http', 'https'], $pdf->whitelistStreamWrappers);
            // basepath is the second observable channel that must flow from
            // pdf_allowed_host and not from HTTP_HOST. If the composition
            // regressed to reading HTTP_HOST it would show up here.
            $basepath = $pdf->basepath;
            $this->assertIsString($basepath);
            $this->assertStringContainsString('canonical.openemr.local', $basepath);
            $this->assertStringNotContainsString('other.example', $basepath);
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
