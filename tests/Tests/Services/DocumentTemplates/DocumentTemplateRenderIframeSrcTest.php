<?php

/**
 * Covers the iframe-src sanitizer used in the `{EncounterForm:…}` and
 * `{Questionnaire:…}` directive substitutions. Verifies that same-origin
 * relative paths and http/https URLs flow through unchanged and that
 * other URL schemes return an empty string.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\DocumentTemplates;

use OpenEMR\Services\DocumentTemplates\DocumentTemplateRender;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DocumentTemplateRenderIframeSrcTest extends TestCase
{
    public function testStripsJavascriptScheme(): void
    {
        $this->assertSame('', $this->sanitize('javascript:alert(1)'));
    }

    public function testStripsJavascriptSchemeMixedCase(): void
    {
        $this->assertSame('', $this->sanitize('JavaScript:alert(1)'));
    }

    public function testStripsDataScheme(): void
    {
        $this->assertSame('', $this->sanitize('data:text/html,<script>alert(1)</script>'));
    }

    public function testStripsVbscriptScheme(): void
    {
        $this->assertSame('', $this->sanitize('vbscript:msgbox(1)'));
    }

    public function testStripsFileScheme(): void
    {
        $this->assertSame('', $this->sanitize('file:///etc/passwd'));
    }

    public function testAllowsHttpsUrl(): void
    {
        $this->assertSame('https://example.com/x', $this->sanitize('https://example.com/x'));
    }

    public function testAllowsHttpUrl(): void
    {
        $this->assertSame('http://example.com/x', $this->sanitize('http://example.com/x'));
    }

    public function testAllowsAbsolutePath(): void
    {
        $this->assertSame('/interface/forms/foo.php', $this->sanitize('/interface/forms/foo.php'));
    }

    public function testAllowsRelativeDotSlash(): void
    {
        $this->assertSame('./form.php', $this->sanitize('./form.php'));
    }

    public function testAllowsRelativeDotDotSlash(): void
    {
        $this->assertSame('../other/form.php', $this->sanitize('../other/form.php'));
    }

    public function testAllowsBareSegmentWithQuery(): void
    {
        $this->assertSame('form.php?x=1', $this->sanitize('form.php?x=1'));
    }

    public function testReturnsEmptyForEmptyInput(): void
    {
        $this->assertSame('', $this->sanitize(''));
    }

    public function testReturnsEmptyForNonString(): void
    {
        $this->assertSame('', $this->sanitize(null));
        $this->assertSame('', $this->sanitize(42));
    }

    public function testRejectsProtocolRelativeDoubleSlash(): void
    {
        // Browsers resolve `//host/x` as `<scheme>://host/x` — cross-origin.
        $this->assertSame('', $this->sanitize('//other.example/x'));
    }

    public function testRejectsProtocolRelativeSlashBackslash(): void
    {
        // Browsers normalize `\` to `/` in special schemes, so `/\host`
        // reaches the same cross-origin resolution as `//host`.
        $this->assertSame('', $this->sanitize('/\\other.example'));
    }

    public function testRejectsEmbeddedTabInScheme(): void
    {
        // Browsers strip control chars from URLs before parsing, so
        // `java\tscript:alert(1)` reaches the parser as `javascript:...`.
        $this->assertSame('', $this->sanitize("java\tscript:alert(1)"));
    }

    public function testRejectsEmbeddedTabInRelativePath(): void
    {
        // `/\t/other.example` → browser → `//other.example` (cross-origin).
        $this->assertSame('', $this->sanitize("/\t/other.example"));
    }

    public function testRejectsEmbeddedNewlineInUrl(): void
    {
        $this->assertSame('', $this->sanitize("foo?\njavascript:x"));
    }

    private function sanitize(mixed $input): string
    {
        $render = new DocumentTemplateRender(1, 1);
        // PHP 8.1+ private methods are accessible to ReflectionMethod without
        // the deprecated-in-8.5 setAccessible() call.
        $method = new ReflectionMethod($render, 'sanitizeIframeSrc');
        $out = $method->invoke($render, $input);
        return is_string($out) ? $out : '';
    }
}
