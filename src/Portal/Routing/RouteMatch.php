<?php

/**
 * A request matched to a portal/patient route, with its URL parameters.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Portal\Routing;

final readonly class RouteMatch
{
    /**
     * @param array<string, string> $params URL parameter name => value
     */
    public function __construct(
        public PortalRoute $route,
        public array $params = [],
    ) {
    }

    /**
     * A URL parameter, such as the `id` in `api/onsitedocument/(:num)`.
     */
    public function param(string $name): ?string
    {
        return $this->params[$name] ?? null;
    }
}
