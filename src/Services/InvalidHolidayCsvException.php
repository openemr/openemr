<?php

/**
 * Raised when a holiday CSV fails validation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use RuntimeException;
use Throwable;

final class InvalidHolidayCsvException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $rowNumber = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
