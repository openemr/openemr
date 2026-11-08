<?php

/**
 * Matches a portal/patient request to its route and enforces the route's access rule.
 *
 * The rules are the old Phreeze GenericRouter's, kept exactly:
 * - A literal route (no wildcard) is matched first; then the wildcard routes, in table order.
 * - A portal patient may use `All` routes.
 * - Staff may use `All` and `Staff` routes.
 * - A registering visitor may use only routes open to registration, whatever their access.
 * - No match is the not-found page, not a refusal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Portal\Routing;

final readonly class PortalRouter
{
    /**
     * @param list<PortalRoute> $routes
     */
    public function __construct(private array $routes)
    {
    }

    /**
     * @param string $path the path after portal/patient/, without leading or trailing slash
     * @return ?RouteMatch null when no route matches
     * @throws PortalRouteDeniedException when the route matches but the caller may not use it
     */
    public function match(string $method, string $path, PortalPrincipal $principal): ?RouteMatch
    {
        $key = strtoupper($method) . ':' . $path;

        foreach ($this->routes as $route) {
            if ($route->isLiteral() && $route->key() === $key) {
                self::authorize($route, $principal);

                return new RouteMatch($route, self::params($route, $path));
            }
        }

        foreach ($this->routes as $route) {
            if (preg_match($route->regex(), $key, $captures) === 1) {
                self::authorize($route, $principal);

                return new RouteMatch($route, self::params($route, $path));
            }
        }

        return null;
    }

    /**
     * @throws PortalRouteDeniedException
     */
    private static function authorize(PortalRoute $route, PortalPrincipal $principal): void
    {
        $allowed = match ($principal->kind) {
            PrincipalKind::PortalPatient => match ($route->access) {
                RouteAccess::All => true,
                RouteAccess::Staff, RouteAccess::None => false,
            },
            PrincipalKind::Staff => match ($route->access) {
                RouteAccess::All, RouteAccess::Staff => true,
                RouteAccess::None => false,
            },
            PrincipalKind::Registering => $route->registration,
        };

        if (!$allowed) {
            throw new PortalRouteDeniedException();
        }
    }

    /**
     * @return array<string, string>
     */
    private static function params(PortalRoute $route, string $path): array
    {
        $segments = explode('/', $path);
        $params = [];
        foreach ($route->params as $name => $index) {
            if (isset($segments[$index])) {
                $params[$name] = $segments[$index];
            }
        }

        return $params;
    }
}
