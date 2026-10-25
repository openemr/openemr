<?php

/**
 * A surface as the grid header and the rule forms present it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability;

final readonly class SurfaceOption
{
    /**
     * @param string $scopesJson JSON list of the scope columns this surface can apply,
     *                            read by the form script to disable the others
     * @param string $hint one line saying what the surface can be limited by
     */
    public function __construct(
        public string $value,
        public string $label,
        public string $scopesJson,
        public string $hint,
        public bool $isSmart,
    ) {
    }
}
