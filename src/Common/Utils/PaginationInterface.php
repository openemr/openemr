<?php

/**
 * PaginationInterface defines the contract for pagination link generation.
 * This interface follows RFC 8288 (Web Linking) principles for pagination.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

/**
 * Interface for pagination link generators.
 *
 * Implementations of this interface provide different rendering strategies
 * for pagination controls (e.g., HTML links, JSON API responses, etc.)
 */
interface PaginationInterface
{
    /**
     * Generates pagination links/controls for a given result set.
     *
     * @param int $offset Current starting position (0-indexed)
     * @param int $pageSize Number of items per page
     * @param int $totalCount Total number of items
     * @param string $filename Target filename for pagination links
     * @param array|null $requestParams Request parameters to preserve (null uses default from constructor)
     * @param array $excludeParams Parameters to exclude from links (e.g., ['mode', 'action'])
     * @param string|null $onclick JavaScript onclick handler (e.g., 'top.restoreSession()')
     * @param string $separator Separator between prev/current/next
     * @param string $prevText Text for the previous link
     * @param string $nextText Text for the next link
     * @return string Rendered pagination output (format depends on implementation)
     * @throws \InvalidArgumentException If parameters are invalid
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
        string $prevText = '&lt;&lt;',
        string $nextText = '&gt;&gt;'
    ): string;
}
