<?php

/**
 * Thrown while building the NCScript XML when the launch cannot go ahead.
 *
 * interface/eRx.php shows the user a message for the reason; the exception
 * message is for logs only.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

use RuntimeException;

final class ErxLaunchBlockedException extends RuntimeException
{
    public function __construct(public readonly ErxLaunchBlock $reason)
    {
        parent::__construct('Ensora eRx launch blocked: ' . $reason->name);
    }
}
