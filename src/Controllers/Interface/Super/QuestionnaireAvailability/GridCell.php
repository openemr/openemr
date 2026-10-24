<?php

/**
 * One questionnaire-by-surface box in the availability grid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability;

final readonly class GridCell
{
    /**
     * @param string $key "questionnaire|surface", posted back so an unticked box is still known
     * @param string $fieldName checkbox name, offer[questionnaire][surface]
     * @param bool $checked offered everywhere on the surface (an active unscoped rule)
     * @param bool $limited a limited rule also offers it here
     * @param bool $viaCategory a category rule also offers it here
     * @param bool $notRegistered encounter column only: not an encounter form, so no effect yet
     */
    public function __construct(
        public string $key,
        public string $fieldName,
        public string $ariaLabel,
        public bool $checked,
        public bool $limited,
        public bool $viaCategory,
        public bool $notRegistered,
    ) {
    }
}
