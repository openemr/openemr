<?php

/**
 * One portal/patient route: an HTTP method and path pattern, the controller action that
 * handles it, and who may use it.
 *
 * Patterns are the old route map's: literal segments plus `(:num)` (digits) and `(:any)`
 * (anything) wildcards, for example `api/onsitedocument/(:num)`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Portal\Routing;

final readonly class PortalRoute
{
    /**
     * @param string $method GET, POST, PUT or DELETE
     * @param string $pattern the path after portal/patient/, without leading or trailing slash
     * @param class-string $controller
     * @param non-empty-string $action the controller method
     * @param bool $registration a self-registering visitor may use it
     * @param array<string, int> $params URL parameter name => path segment index
     */
    public function __construct(
        public string $method,
        public string $pattern,
        public string $controller,
        public string $action,
        public RouteAccess $access,
        public bool $registration = false,
        public array $params = [],
    ) {
    }

    /**
     * The old route map key, such as `GET:api/onsitedocument/(:num)`.
     */
    public function key(): string
    {
        return $this->method . ':' . $this->pattern;
    }

    public function isLiteral(): bool
    {
        return !str_contains($this->pattern, '(:');
    }

    /**
     * The regular expression the pattern stands for, matched against `METHOD:path`. Each
     * wildcard is a capture group.
     */
    public function regex(): string
    {
        $key = preg_quote($this->key(), '#');
        $key = str_replace(['\\(\\:num\\)', '\\(\\:any\\)'], ['([0-9]+)', '(.+)'], $key);

        return '#^' . $key . '$#';
    }
}
