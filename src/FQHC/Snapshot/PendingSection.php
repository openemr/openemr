<?php

/**
 * A UDS Snapshot section whose data capture has not been built yet.
 *
 * Rendered as a titled card with an empty-state message pointing at the
 * pathway step that will implement it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Snapshot;

final readonly class PendingSection
{
    public function __construct(
        public string $title,
        public string $message,
    ) {
    }
}
