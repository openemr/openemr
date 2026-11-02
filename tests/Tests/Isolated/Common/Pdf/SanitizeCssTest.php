<?php

/**
 * Isolated test for the CSS url()/@import sanitizer the patient-portal PDF
 * renderer applies to inline <style> blocks before mPDF processes them.
 *
 * Exercises the (private) `sanitizeCss()` helper on
 * PatientPortalPDFDocumentCreator via reflection so the sanitizer's behavior
 * is covered without standing up a full mPDF render.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Pdf;

use OpenEMR\Pdf\PatientPortalPDFDocumentCreator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SanitizeCssTest extends TestCase
{
    private static function callSanitizeCss(string $css, string $allowedHostSpec): string
    {
        $method = new ReflectionMethod(PatientPortalPDFDocumentCreator::class, 'sanitizeCss');
        $result = $method->invoke(null, $css, $allowedHostSpec);
        return is_string($result) ? $result : '';
    }

    #[DataProvider('urlFormProvider')]
    public function testUrlFormHandling(string $input, string $allowedSpec, bool $shouldSurvive, string $marker): void
    {
        $out = self::callSanitizeCss($input, $allowedSpec);
        if ($shouldSurvive) {
            $this->assertStringContainsString($marker, $out);
        } else {
            $this->assertStringNotContainsString($marker, $out);
        }
    }

    /**
     * Each row: [input CSS, allowedHostSpec, shouldMarkerSurvive, markerInInput].
     *
     * @return array<string, array{string, string, bool, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function urlFormProvider(): array
    {
        return [
            // Quoted url() form, same host preserved.
            'url-double-quoted-same-host' => [
                'body { background: url("http://allowed.example/img.png"); }',
                'allowed.example',
                true,
                'allowed.example/img.png',
            ],
            'url-single-quoted-same-host' => [
                "body { background: url('http://allowed.example/img.png'); }",
                'allowed.example',
                true,
                'allowed.example/img.png',
            ],
            // Unquoted url() form, same host preserved.
            'url-unquoted-same-host' => [
                'body { background: url(http://allowed.example/img.png); }',
                'allowed.example',
                true,
                'allowed.example/img.png',
            ],
            // Different-host url() stripped.
            'url-quoted-different-host' => [
                'body { background: url("http://other.example/img.png"); }',
                'allowed.example',
                false,
                'other.example',
            ],
            // Quoted value containing `)` is handled by the alternation regex.
            'url-quoted-with-parentheses-inside' => [
                'body { background: url("http://allowed.example/path)with)parens"); }',
                'allowed.example',
                true,
                'allowed.example/path)with)parens',
            ],
            'url-quoted-with-parens-different-host-stripped' => [
                'body { background: url("http://other.example/x)y"); }',
                'allowed.example',
                false,
                'other.example',
            ],
            // Mixed quoting with stray quote inside unquoted form is rejected.
            'url-unquoted-with-stray-quote-rejected' => [
                'body { background: url(http://other.example"/x); }',
                'allowed.example',
                false,
                'other.example',
            ],
            // URL authority with user:pass is rejected (keeps host-match unambiguous).
            // Case 1: userinfo spoofs the allowed host as the username — real host is different.
            'url-with-userinfo-rejected' => [
                'body { background: url("http://allowed.example@other.example/x"); }',
                'allowed.example',
                false,
                'other.example',
            ],
            // Case 2: userinfo is the only distinguishing feature — real host matches allow-list.
            // This isolates the credential rejection from the host check, so removing the
            // credential rejection would make this test fail even though the host check still
            // passes.
            'url-with-userinfo-matching-host-still-rejected' => [
                'body { background: url("http://other.example@allowed.example/x"); }',
                'allowed.example',
                false,
                'allowed.example/x',
            ],
            // Relative URL is preserved (resolves against the renderer's base path).
            'url-relative-path-preserved' => [
                'body { background: url("/barcode.php?text=X"); }',
                'allowed.example',
                true,
                '/barcode.php?text=X',
            ],
            // Protocol-relative URL to allowed host is preserved.
            'url-protocol-relative-same-host-preserved' => [
                'body { background: url("//allowed.example/img.png"); }',
                'allowed.example',
                true,
                '//allowed.example/img.png',
            ],
            'url-protocol-relative-different-host-stripped' => [
                'body { background: url("//other.example/img.png"); }',
                'allowed.example',
                false,
                'other.example',
            ],
            // @import quoted-string form, same host preserved.
            'import-double-quoted-same-host' => [
                '@import "http://allowed.example/style.css";',
                'allowed.example',
                true,
                'allowed.example/style.css',
            ],
            'import-single-quoted-same-host' => [
                "@import 'http://allowed.example/style.css';",
                'allowed.example',
                true,
                'allowed.example/style.css',
            ],
            // @import url() wrapper, same host preserved.
            'import-url-wrapper-same-host' => [
                '@import url("http://allowed.example/style.css");',
                'allowed.example',
                true,
                'allowed.example/style.css',
            ],
            // @import bare URL (no quotes, no url() wrapper), same host preserved.
            'import-unquoted-bare-same-host' => [
                '@import http://allowed.example/style.css;',
                'allowed.example',
                true,
                'allowed.example/style.css',
            ],
            // @import quoted different host stripped.
            'import-quoted-different-host-stripped' => [
                '@import "http://other.example/evil.css";',
                'allowed.example',
                false,
                'other.example',
            ],
            // @import bare different host stripped.
            'import-unquoted-different-host-stripped' => [
                '@import http://other.example/evil.css;',
                'allowed.example',
                false,
                'other.example',
            ],
            // Non-http(s) scheme in url() is rejected (file, php, etc.).
            'url-file-scheme-rejected' => [
                'body { background: url("file:///etc/hostname"); }',
                'allowed.example',
                false,
                'file://',
            ],
            'url-php-scheme-rejected' => [
                'body { background: url("php://filter/x"); }',
                'allowed.example',
                false,
                'php://',
            ],
            // @import of non-http(s) scheme stripped.
            'import-file-scheme-rejected' => [
                '@import url("file:///etc/hostname");',
                'allowed.example',
                false,
                'file://',
            ],
            // data: URIs are preserved (image rendering uses these).
            'url-data-uri-preserved' => [
                'body { background: url("data:image/png;base64,iVBORw0KGgoAAAANS"); }',
                'allowed.example',
                true,
                'data:image/png;base64,iVBORw0KGgoAAAANS',
            ],
            // Mixed input: relative preserved, cross-host stripped in same pass.
            'mixed-relative-preserved-cross-host-stripped' => [
                'a { background: url("/good.png"); } b { background: url("http://other.example/x"); }',
                'allowed.example',
                false, // marker is other.example, which should NOT survive
                'other.example',
            ],
            // Port handling in url(): same host different port stripped.
            'url-same-host-different-port-stripped' => [
                'body { background: url("http://allowed.example:6379/x"); }',
                'allowed.example',
                false,
                'allowed.example:6379',
            ],
            // Scheme-pinned config: scheme match required.
            'url-scheme-pinned-matching-preserved' => [
                'body { background: url("https://allowed.example/img.png"); }',
                'https://allowed.example',
                true,
                'allowed.example/img.png',
            ],
            'url-scheme-pinned-mismatched-stripped' => [
                'body { background: url("http://allowed.example/img.png"); }',
                'https://allowed.example',
                false,
                'allowed.example/img.png',
            ],
            // Protocol-relative uses configured scheme for port-default resolution.
            'url-protocol-relative-matches-pinned-https' => [
                'body { background: url("//allowed.example/img.png"); }',
                'https://allowed.example',
                true,
                '//allowed.example/img.png',
            ],
            // Blank config strips all remote references.
            'url-blank-config-strips-http' => [
                'body { background: url("http://allowed.example/x"); }',
                '',
                false,
                'allowed.example/x',
            ],
            'url-blank-config-preserves-data' => [
                'body { background: url("data:image/png;base64,abc"); }',
                '',
                true,
                'data:image/png;base64,abc',
            ],
            'url-blank-config-preserves-relative' => [
                'body { background: url("/barcode.php"); }',
                '',
                true,
                '/barcode.php',
            ],
        ];
    }

    public function testStripsAllUrlOccurrencesInOnePass(): void
    {
        $input = 'a { background: url("http://other.example/1"); } '
            . 'b { background: url("http://other.example/2"); } '
            . 'c { background: url("http://allowed.example/3"); }';
        $out = self::callSanitizeCss($input, 'allowed.example');
        $this->assertStringNotContainsString('other.example', $out);
        $this->assertStringContainsString('allowed.example/3', $out);
    }

    public function testEmptyInputReturnsEmpty(): void
    {
        $this->assertSame('', self::callSanitizeCss('', 'allowed.example'));
    }

    public function testNoUrlNoImportReturnsInputUnchanged(): void
    {
        $input = 'body { color: red; font-size: 14pt; }';
        $this->assertSame($input, self::callSanitizeCss($input, 'allowed.example'));
    }
}
