<?php

/**
 * Isolated safe_href() Test
 *
 * Tests URL scheme validation in the safe_href() escaping function.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Allen
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../../library/htmlspecialchars.inc.php';

use PHPUnit\Framework\TestCase;

class SafeHrefTest extends TestCase
{
    /**
     * @dataProvider safeUrlProvider
     */
    public function testSafeUrlsAreAllowed(string $input, string $expected): void
    {
        $this->assertSame($expected, safe_href($input));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function safeUrlProvider(): array
    {
        return [
            'https URL' => ['https://example.com', 'https://example.com'],
            'http URL' => ['http://example.com/path', 'http://example.com/path'],
            'mailto URL' => ['mailto:user@example.com', 'mailto:user@example.com'],
            'tel URL' => ['tel:+1234567890', 'tel:+1234567890'],
            'ftp URL' => ['ftp://files.example.com', 'ftp://files.example.com'],
            'ftps URL' => ['ftps://secure.example.com', 'ftps://secure.example.com'],
            'relative path' => ['/interface/main/tabs.php', '/interface/main/tabs.php'],
            'query-only' => ['?page=2', '?page=2'],
            'fragment-only' => ['#section', '#section'],
            'bare fragment' => ['#', '#'],
            'no scheme relative' => ['controller.php?action=edit', 'controller.php?action=edit'],
            'empty string' => ['', ''],
        ];
    }

    /**
     * @dataProvider dangerousUrlProvider
     */
    public function testDangerousUrlsAreBlocked(string $input): void
    {
        $this->assertSame('#', safe_href($input));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dangerousUrlProvider(): array
    {
        return [
            'javascript lowercase' => ['javascript:alert(1)'],
            'javascript uppercase' => ['JAVASCRIPT:alert(1)'],
            'javascript mixed case' => ['JaVaScRiPt:alert(document.cookie)'],
            'data text/html' => ['data:text/html,<script>alert(1)</script>'],
            'data base64' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
            'vbscript' => ['vbscript:MsgBox("XSS")'],
            'blob URL' => ['blob:http://evil.com/abc-123'],
        ];
    }

    /**
     * Test that HTML special characters in safe URLs are escaped.
     */
    public function testHtmlEntitiesAreEscaped(): void
    {
        $this->assertSame(
            'https://example.com/path?a=1&amp;b=2',
            safe_href('https://example.com/path?a=1&b=2')
        );
        $this->assertSame(
            'https://example.com/?q=&quot;test&quot;',
            safe_href('https://example.com/?q="test"')
        );
        $this->assertSame(
            '/path?x=1&amp;y=2',
            safe_href('/path?x=1&y=2')
        );
    }

    /**
     * Test that null input is handled gracefully.
     */
    public function testNullInput(): void
    {
        $this->assertSame('', safe_href(null));
    }

    /**
     * Test that whitespace is trimmed before scheme detection.
     */
    public function testWhitespaceIsTrimmed(): void
    {
        $this->assertSame(
            'https://example.com',
            safe_href('  https://example.com  ')
        );
        $this->assertSame('#', safe_href('  javascript:alert(1)  '));
    }

    /**
     * Test that HTTPS URLs with uppercase scheme are allowed.
     */
    public function testCaseInsensitiveSchemeAllowlist(): void
    {
        $this->assertSame('HTTP://example.com', safe_href('HTTP://example.com'));
        $this->assertSame('HTTPS://example.com', safe_href('HTTPS://example.com'));
        $this->assertSame('Mailto:user@test.com', safe_href('Mailto:user@test.com'));
    }
}
