<?php

/**
 * PaginationUtils provides HTML pagination link generation for legacy UI pages.
 * This utility follows RFC 8288 (Web Linking) principles for pagination.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

use OpenEMR\Common\Csrf\CsrfUtils;

/**
 * Utility class for generating HTML pagination links in legacy UI pages.
 */
class PaginationUtils implements PaginationInterface
{
    /**
     * @param ?array $defaultRequestParams Default request parameters (defaults to $_REQUEST)
     */
    public function __construct(private ?array $defaultRequestParams = null)
    {
        $this->defaultRequestParams ??= $_REQUEST;
    }

    /**
     * Generates HTML pagination links for a given result set.
     *
     * This method follows RFC 8288 (Web Linking) principles by providing
     * 'prev' and 'next' relations, and displays the current page position.
     *
     * @param int $offset Current starting position (0-indexed)
     * @param int $pageSize Number of items per page
     * @param int $totalCount Total number of items
     * @param string $filename Target PHP file for pagination links
     * @param array|null $requestParams Request parameters to preserve (null uses default from constructor)
     * @param array $excludeParams Parameters to exclude from links (e.g., ['mode', 'action'])
     * @param string|null $onclick JavaScript onclick handler (e.g., 'top.restoreSession()')
     * @param string $separator Separator between prev/current/next (defaults to '&nbsp;&nbsp;'). Will be HTML-escaped unless it consists only of &nbsp; entities.
     * @param string $prevText Text for the previous link (defaults to '<<'). Will be HTML-escaped.
     * @param string $nextText Text for the next link (defaults to '>>'). Will be HTML-escaped.
     *
     * @return string HTML string containing pagination links
     *
     * @example
     * ```php
     * $paginator = new PaginationUtils();
     * echo $paginator->render(
     *     $fstart,
     *     $MAXSHOW,
     *     $count,
     *     'patient_select.php',
     *     excludeParams: ['mode'],
     *     onclick: 'top.restoreSession()'
     * );
     * ```
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
    ): string
    {
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
        $requestParams ??= $this->defaultRequestParams;

        // Calculate end position
        $end = min($totalCount, $offset + $pageSize);

        // Build query parameters
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

        // Current position display
        $countStatement = " - " . $end . " " . xl('of') . " " . $totalCount;
        $html .= text(($offset + 1) . $countStatement);

        // Next link
        if ($totalCount > $end) {
            $nextOffset = $offset + $pageSize;
            $nextParams = $this->buildQueryString($queryParams, ['fstart' => $nextOffset]);
            $onclickAttr = $onclick ? ' onclick="' . attr($onclick) . '"' : '';
            // Only escape separator if it contains something other than &nbsp; entities
            $html .= $cleanSeparator;
            $html .= "<a{$onclickAttr} href='" . attr($filename . '?' . $nextParams) . "' rel='next'>" . text($nextText) . "</a>";
        }

        return $html;
    }

    /**
     * Builds query parameters array, excluding specified parameters and adding CSRF token.
     *
     * @param array $requestParams The request parameters to start with
     * @param array $excludeParams Parameters to exclude
     * @return array Filtered parameters with CSRF token
     */
    private function buildQueryParams(array $requestParams, array $excludeParams): array
    {
        $params = $requestParams;

        // Remove excluded parameters
        foreach ($excludeParams as $param) {
            unset($params[$param]);
        }

        // Add CSRF token
        $params['csrf_token_form'] = CsrfUtils::collectCsrfToken();

        return $params;
    }

    /**
     * Builds query string from parameters, merging in additional parameters.
     *
     * @param array $baseParams Base parameters
     * @param array $additionalParams Additional parameters to merge
     * @return string URL-encoded query string
     */
    private function buildQueryString(array $baseParams, array $additionalParams = []): string
    {
        return http_build_query(array_merge($baseParams, $additionalParams));
    }
}
