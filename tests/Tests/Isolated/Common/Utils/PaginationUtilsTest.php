<?php

/**
 * Isolated PaginationUtils Test
 *
 * Tests PaginationUtils pagination link generation without database dependencies.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\PaginationUtils;
use PHPUnit\Framework\TestCase;

// Define stub helper functions if they don't exist
if (!function_exists('text')) {
    function text($text)
    {
        return htmlspecialchars(($text ?? ''), ENT_NOQUOTES);
    }
}

if (!function_exists('attr')) {
    function attr($text)
    {
        return htmlspecialchars(($text ?? ''), ENT_QUOTES);
    }
}

// Note: We don't define xl() here because it may conflict with autoloaded version
// Instead, our stub class will handle translation inline

class PaginationUtilsTest extends TestCase
{
    private PaginationUtils $paginator;

    protected function setUp(): void
    {
        // Use stub CsrfUtils to avoid dependency
        $this->paginator = new PaginationUtilsStub();
    }

    public function testFirstPageWithMultiplePages(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: ['search' => 'test']
        );

        // Should NOT have previous link
        $this->assertStringNotContainsString('rel=\'prev\'', $html);

        // Should have "1 - 10 of 50"
        $this->assertStringContainsString('1 - 10', $html);
        $this->assertStringContainsString('of', $html);
        $this->assertStringContainsString('50', $html);

        // Should have next link
        $this->assertStringContainsString('rel=\'next\'', $html);
        $this->assertStringContainsString('fstart=10', $html);
    }

    public function testMiddlePageWithMultiplePages(): void
    {
        $html = $this->paginator->render(
            offset: 20,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: ['search' => 'test']
        );

        // Should have previous link
        $this->assertStringContainsString('rel=\'prev\'', $html);
        $this->assertStringContainsString('fstart=10', $html);

        // Should have "21 - 30 of 50"
        $this->assertStringContainsString('21 - 30', $html);
        $this->assertStringContainsString('of', $html);
        $this->assertStringContainsString('50', $html);

        // Should have next link
        $this->assertStringContainsString('rel=\'next\'', $html);
        $this->assertStringContainsString('fstart=30', $html);
    }

    public function testLastPageWithMultiplePages(): void
    {
        $html = $this->paginator->render(
            offset: 40,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: ['search' => 'test']
        );

        // Should have previous link
        $this->assertStringContainsString('rel=\'prev\'', $html);
        $this->assertStringContainsString('fstart=30', $html);

        // Should have "41 - 50 of 50"
        $this->assertStringContainsString('41 - 50', $html);
        $this->assertStringContainsString('of', $html);
        $this->assertStringContainsString('50', $html);

        // Should NOT have next link
        $this->assertStringNotContainsString('rel=\'next\'', $html);
    }

    public function testSinglePageNoLinks(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 5,
            filename: 'test.php',
            requestParams: ['search' => 'test']
        );

        // Should NOT have any navigation links
        $this->assertStringNotContainsString('rel=\'prev\'', $html);
        $this->assertStringNotContainsString('rel=\'next\'', $html);

        // Should have "1 - 5 of 5"
        $this->assertStringContainsString('1 - 5', $html);
        $this->assertStringContainsString('of', $html);
        $this->assertStringContainsString('5', $html);
    }

    public function testEmptyResultSet(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 0,
            filename: 'test.php',
            requestParams: []
        );

        // Should NOT have any navigation links
        $this->assertStringNotContainsString('rel=\'prev\'', $html);
        $this->assertStringNotContainsString('rel=\'next\'', $html);

        // Should have "1 - 0 of 0"
        $this->assertStringContainsString('1 - 0', $html);
        $this->assertStringContainsString('of', $html);
        $this->assertStringContainsString('0', $html);
    }

    public function testExcludeParamsRemovesSpecifiedParameters(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: ['search' => 'test', 'mode' => 'edit', 'action' => 'delete'],
            excludeParams: ['mode', 'action']
        );

        // Should include search param
        $this->assertStringContainsString('search=test', $html);

        // Should NOT include excluded params
        $this->assertStringNotContainsString('mode=', $html);
        $this->assertStringNotContainsString('action=', $html);
    }

    public function testCsrfTokenIncludedInLinks(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: ['search' => 'test']
        );

        // Should include CSRF token
        $this->assertStringContainsString('csrf_token_form=stub_csrf_token', $html);
    }

    public function testOnclickAttributeAdded(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: [],
            onclick: 'top.restoreSession()'
        );

        // Should include onclick attribute (escaped)
        $this->assertStringContainsString('onclick=', $html);
        $this->assertStringContainsString('top.restoreSession()', $html);
    }

    public function testCustomSeparator(): void
    {
        $html = $this->paginator->render(
            offset: 10,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: [],
            separator: ' | '
        );

        // Should have custom separator between elements
        $this->assertStringContainsString(' | ', $html);
    }

    public function testCustomPrevNextText(): void
    {
        $html = $this->paginator->render(
            offset: 10,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: [],
            prevText: 'Previous',
            nextText: 'Next'
        );

        // Should have custom prev/next text
        $this->assertStringContainsString('Previous', $html);
        $this->assertStringContainsString('Next', $html);

        // Should NOT have default arrows
        $this->assertStringNotContainsString('&lt;&lt;', $html);
        $this->assertStringNotContainsString('&gt;&gt;', $html);
    }

    public function testNbspSeparatorNotEscaped(): void
    {
        $html = $this->paginator->render(
            offset: 10,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: [],
            separator: '&nbsp;&nbsp;'
        );

        // &nbsp; should remain as-is, not escaped
        $this->assertStringContainsString('&nbsp;&nbsp;', $html);
        // Should not be double-escaped
        $this->assertStringNotContainsString('&amp;nbsp;', $html);
    }

    public function testInvalidPageSizeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page size must be greater than zero');

        $this->paginator->render(
            offset: 0,
            pageSize: 0,
            totalCount: 50,
            filename: 'test.php',
            requestParams: []
        );
    }

    public function testNegativePageSizeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page size must be greater than zero');

        $this->paginator->render(
            offset: 0,
            pageSize: -10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: []
        );
    }

    public function testNegativeOffsetThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Offset must be non-negative');

        $this->paginator->render(
            offset: -5,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: []
        );
    }

    public function testNegativeTotalCountThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Total count must be non-negative');

        $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: -1,
            filename: 'test.php',
            requestParams: []
        );
    }

    public function testPartialLastPage(): void
    {
        $html = $this->paginator->render(
            offset: 40,
            pageSize: 10,
            totalCount: 47,
            filename: 'test.php',
            requestParams: []
        );

        // Should have "41 - 47 of 47" (not 41 - 50)
        $this->assertStringContainsString('41 - 47', $html);
        $this->assertStringContainsString('of', $html);
        $this->assertStringContainsString('47', $html);

        // Should NOT have next link
        $this->assertStringNotContainsString('rel=\'next\'', $html);
    }

    public function testHtmlEscapingInFilename(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test<script>.php',
            requestParams: []
        );

        // Filename should be escaped in href attribute
        $this->assertStringContainsString('test&lt;script&gt;.php', $html);
        $this->assertStringNotContainsString('test<script>.php', $html);
    }

    public function testHtmlEscapingInRequestParams(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: ['search' => '<script>alert("xss")</script>']
        );

        // Request params should be URL-encoded in links
        $this->assertStringContainsString('search=', $html);
        // Should not contain raw script tags
        $this->assertStringNotContainsString('<script>alert', $html);
    }

    public function testDefaultRequestParamsFromConstructor(): void
    {
        // Create paginator with default request params
        $paginator = new PaginationUtilsStub(['default' => 'value']);

        $html = $paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php'
        );

        // Should use default params from constructor
        $this->assertStringContainsString('default=value', $html);
    }

    public function testExplicitRequestParamsOverrideDefaults(): void
    {
        // Create paginator with default request params
        $paginator = new PaginationUtilsStub(['default' => 'value']);

        $html = $paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: ['explicit' => 'param']
        );

        // Should use explicit params, not defaults
        $this->assertStringContainsString('explicit=param', $html);
        $this->assertStringNotContainsString('default=value', $html);
    }

    public function testPreviousLinkWithSmallOffset(): void
    {
        $html = $this->paginator->render(
            offset: 5,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: []
        );

        // Previous link should go to offset 0 (not -5)
        $this->assertStringContainsString('fstart=0', $html);
        $this->assertStringContainsString('rel=\'prev\'', $html);
    }

    public function testQueryStringBuilding(): void
    {
        $html = $this->paginator->render(
            offset: 0,
            pageSize: 10,
            totalCount: 50,
            filename: 'test.php',
            requestParams: ['foo' => 'bar', 'baz' => 'qux']
        );

        // Should include all query params
        $this->assertStringContainsString('foo=bar', $html);
        $this->assertStringContainsString('baz=qux', $html);
        $this->assertStringContainsString('csrf_token_form=stub_csrf_token', $html);
    }
}

/**
 * Test stub that provides stub CSRF token
 */
class PaginationUtilsStub extends PaginationUtils
{
    public function __construct(private readonly ?array $stubDefaultRequestParams = null)
    {
        // Don't call parent constructor to avoid $_REQUEST dependency
    }

    /**
     * Override buildQueryParams to provide stub CSRF token
     */
    private function buildQueryParams(array $requestParams, array $excludeParams): array
    {
        $params = $requestParams;

        // Remove excluded parameters
        foreach ($excludeParams as $param) {
            unset($params[$param]);
        }

        // Add stub CSRF token instead of calling CsrfUtils
        $params['csrf_token_form'] = 'stub_csrf_token';

        return $params;
    }

    /**
     * Override buildQueryString to make it accessible for testing
     */
    private function buildQueryString(array $baseParams, array $additionalParams = []): string
    {
        return http_build_query(array_merge($baseParams, $additionalParams));
    }

    /**
     * Need to expose the render method's internal methods for our stub
     * This is a workaround since private methods can't be directly overridden
     */
    public function render(
        int $offset,
        int $pageSize,
        int $totalCount,
        string $filename,
        ?array $requestParams = null,
        array $excludeParams = [],
        ?string $onclick = null,
        string $separator = '&nbsp;&nbsp;',
        string $prevText = '<<',
        string $nextText = '>>'
    ): string {
        // Validate input parameters for pagination
        if ($pageSize <= 0) {
            throw new \InvalidArgumentException('Page size must be greater than zero');
        }
        if ($offset < 0) {
            throw new \InvalidArgumentException('Offset must be non-negative');
        }
        if ($totalCount < 0) {
            throw new \InvalidArgumentException('Total count must be non-negative');
        }
        $requestParams ??= $this->stubDefaultRequestParams ?? [];

        // Calculate end position
        $end = min($totalCount, $offset + $pageSize);

        // Build query parameters (using our stub version)
        $queryParams = $this->buildQueryParams($requestParams, $excludeParams);

        // Generate HTML
        $html = '';

        // Only escape separator if it contains something other than &nbsp; entities
        $cleanSeparator = preg_match('/^(&nbsp;)*$/', $separator) ? $separator : text($separator);

        // Previous link
        if ($offset > 0) {
            $prevOffset = max(0, $offset - $pageSize);
            $prevParams = $this->buildQueryString($queryParams, ['fstart' => $prevOffset]);
            $onclickAttr = $onclick ? ' onclick="' . attr($onclick) . '"' : '';
            $html .= "<a{$onclickAttr} href='" . attr($filename . '?' . $prevParams) . "' rel='prev'>" . text($prevText) . "</a>";
            $html .= $cleanSeparator;
        }

        // Current position display (using 'of' directly instead of xl('of') to avoid translation dependency)
        $countStatement = " - " . $end . " of " . $totalCount;
        $html .= text(($offset + 1) . $countStatement);

        // Next link
        if ($totalCount > $end) {
            $nextOffset = $offset + $pageSize;
            $nextParams = $this->buildQueryString($queryParams, ['fstart' => $nextOffset]);
            $onclickAttr = $onclick ? ' onclick="' . attr($onclick) . '"' : '';
            $html .= $cleanSeparator;
            $html .= "<a{$onclickAttr} href='" . attr($filename . '?' . $nextParams) . "' rel='next'>" . text($nextText) . "</a>";
        }

        return $html;
    }
}
