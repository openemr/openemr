<?php

/**
 * The outcome of an FPL calculation: a display percentage and a UDS band.
 *
 * `percent` is null exactly when `band` is Unknown (income could not be
 * determined). The percentage is rounded for display only; the band is computed
 * from the exact ratio so boundary cases (e.g. 150.4%) classify correctly.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

final readonly class FplResult
{
    public function __construct(
        public ?int $percent,
        public FplBand $band,
    ) {
    }
}
