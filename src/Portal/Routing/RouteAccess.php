<?php

/**
 * Who may reach a portal/patient route. These are the old route map's `p_acl` levels.
 *
 * - All (p_all): portal patients and staff. The controller checks the record.
 * - Staff (p_staff): staff only.
 * - None (p_none): nobody, unless the route is open to registration.
 *
 * Whether a self-registering visitor may use a route is a separate flag on the route.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Portal\Routing;

enum RouteAccess
{
    case All;
    case Staff;
    case None;
}
