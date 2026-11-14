<?php

/**
 * A registered SMART app a questionnaire can be launched into: its client id and name.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

final readonly class SmartApp
{
    public function __construct(
        public string $clientId,
        public string $name,
    ) {
    }
}
