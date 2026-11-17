<?php

/**
 * The caller may not use the portal/patient route they asked for.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Portal\Routing;

final class PortalRouteDeniedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unauthorized');
    }
}
