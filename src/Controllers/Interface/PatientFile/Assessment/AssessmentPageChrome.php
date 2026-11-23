<?php

/**
 * The parts of the assessments page drawn by legacy helpers, already rendered: the OemrUI
 * container class and expand script, and the patient menu bar, which carries the OemrUI
 * heading controls at its right-hand end in place of a page title.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

final readonly class AssessmentPageChrome
{
    public function __construct(
        public string $containerClass,
        public string $belowContainer,
        public string $patientMenu,
    ) {
    }
}
