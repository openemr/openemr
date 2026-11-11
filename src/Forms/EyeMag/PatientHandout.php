<?php

/**
 * Patient handout snapshot used consistently throughout one Eye Form report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

final readonly class PatientHandout
{
    /** Capture the document identity, display name, and PDF bytes for one report handout. */
    public function __construct(
        public int $documentId,
        public string $name,
        public string $pdfData,
    ) {
    }
}
