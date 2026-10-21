<?php

/**
 * Thrown when the calculation engine cannot calculate a measure, for example
 * a measure whose logic uses CQL the engine (like cqm-execution) cannot run.
 * The message names only the measure, so it can be shown to the user; the
 * engine's own error is the previous exception.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Qdm;

final class MeasureCalculationException extends \RuntimeException
{
    public function __construct(public readonly string $measureId, \Throwable $previous)
    {
        parent::__construct("Measure $measureId cannot be calculated by the eCQM calculation engine", 0, $previous);
    }
}
