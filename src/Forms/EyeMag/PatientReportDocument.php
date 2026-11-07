<?php

/**
 * Encounter document snapshot used in an Eye Form report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

final readonly class PatientReportDocument
{
    /** Capture the selected document's identity, display name, type and bytes. */
    public function __construct(
        public int $documentId,
        public string $name,
        public string $mimeType,
        public string $data,
    ) {
    }
}
