<?php

/**
 * What a portal document list asks for: one patient's documents, optionally narrowed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PortalDocuments;

final readonly class OnsiteDocumentListQuery
{
    public const DEFAULT_PAGE_SIZE = 25;

    /**
     * @param int $pid the patient whose documents are listed; always required
     * @param ?int $recordId only this document
     * @param bool $includeLocked include documents whose denial reason marks them Locked
     * @param string $filter free text matched against every column but the template data
     * @param ?positive-int $page the page to return, or null for every document
     * @param positive-int $pageSize
     */
    public function __construct(
        public int $pid,
        public ?int $recordId = null,
        public bool $includeLocked = false,
        public string $filter = '',
        public OnsiteDocumentSort $orderBy = OnsiteDocumentSort::DenialReason,
        public bool $orderDesc = false,
        public ?int $page = null,
        public int $pageSize = self::DEFAULT_PAGE_SIZE,
    ) {
        if ($pid <= 0) {
            throw new \InvalidArgumentException('A patient is required');
        }
    }
}
