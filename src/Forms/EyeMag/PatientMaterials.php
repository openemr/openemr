<?php

/**
 * Typed text and PDF material snapshot for one Eye Form report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

final readonly class PatientMaterials
{
    /**
     * Capture the instruction text, inclusion choice, and handouts used by one report.
     *
     * @param list<PatientHandout> $handouts
     */
    public function __construct(
        public string $content,
        public bool $includeText,
        public array $handouts,
    ) {
    }
}
