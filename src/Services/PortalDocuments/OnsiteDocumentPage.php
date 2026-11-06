<?php

/**
 * One page of portal documents and the paging figures the API reports with it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PortalDocuments;

final readonly class OnsiteDocumentPage
{
    /**
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(
        public array $rows,
        public int $totalResults,
        public int $totalPages,
        public int $pageSize,
        public int $currentPage,
    ) {
    }
}
