<?php

/**
 * Translates the declarative routes of the legacy Laminas MVC zend_modules
 * into a Symfony RouteCollection.
 *
 * Routes are data: each module ships a `config/module.config.php` whose
 * `router.routes` tree describes Laminas Literal/Segment routes. This adapter
 * reads that data and produces the equivalent Symfony routes so the modern
 * OEHttpKernel can dispatch the same controllers during the strangler-pattern
 * migration off laminas-mvc (which has no PHP 8.5 support).
 *
 * This is a read-only adapter over the module config files. It connects to
 * nothing, touches no superglobals, and runs without a database — so it is
 * unit-testable in isolation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Routing;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final readonly class ZendModuleRouteLoader
{
    /**
     * Request attribute carrying the originating zend module name (e.g. "Acl").
     */
    public const ATTR_MODULE = '_zend_module';

    /**
     * Request attribute carrying the controller FQCN to dispatch.
     */
    public const ATTR_CONTROLLER = 'controller';

    /**
     * Request attribute carrying the action name (Laminas convention: the
     * "{action}" placeholder, falling back to the route default).
     */
    public const ATTR_ACTION = 'action';

    public function __construct(
        private string $zendModulesDir,
    ) {
    }

    /**
     * Build the combined RouteCollection for the enabled modules that declare
     * routes.
     *
     * Only modules in $enabledModules are considered — these are the modules the
     * legacy ModulesApplication actually loads (core_modules plus DB-enabled
     * plugins, from application.config.php's `modules` list). Disabled modules
     * are skipped entirely: their `module.config.php` is never required, so a
     * broken or side-effectful config in a module Laminas would never load
     * cannot break the seam, and route loading matches production enablement.
     *
     * @param list<string> $enabledModules module names (directory names) to load
     */
    public function load(array $enabledModules): RouteCollection
    {
        $enabled = array_flip($enabledModules);
        $collection = new RouteCollection();

        foreach ($this->moduleConfigFiles() as $moduleName => $configFile) {
            if (!isset($enabled[$moduleName])) {
                continue;
            }
            $config = require $configFile;
            if (!is_array($config)) {
                continue;
            }
            $this->loadModuleConfig($moduleName, $config, $collection);
        }

        return $collection;
    }

    /**
     * Translate one module's parsed config into routes, appending them to the
     * given collection. Exposed for isolated testing with literal config data.
     *
     * @param array<array-key, mixed> $config the parsed module.config.php
     */
    public function loadModuleConfig(string $moduleName, array $config, RouteCollection $collection): void
    {
        $router = $config['router'] ?? null;
        if (!is_array($router)) {
            return;
        }
        $routes = $router['routes'] ?? null;
        if (!is_array($routes)) {
            return;
        }

        foreach ($routes as $routeName => $routeNode) {
            if (!is_array($routeNode)) {
                continue;
            }
            $this->addRouteNode($collection, $moduleName, (string) $routeName, $routeNode, '', []);
        }
    }

    /**
     * Recursively translate a Laminas route node (and its child_routes) into
     * Symfony routes.
     *
     * @param array<array-key, mixed> $node       the Laminas route node
     * @param string               $parentPattern the accumulated raw Laminas
     *                                             route string of ancestors
     * @param array<string, scalar|null> $inheritedDefaults parent defaults that
     *                                             children inherit when unset
     */
    private function addRouteNode(
        RouteCollection $collection,
        string $moduleName,
        string $routeName,
        array $node,
        string $parentPattern,
        array $inheritedDefaults,
    ): void {
        $options = is_array($node['options'] ?? null) ? $node['options'] : [];
        $rawPattern = is_string($options['route'] ?? null) ? $options['route'] : '';
        $fullPattern = $parentPattern . $rawPattern;

        $laminasDefaults = $this->normalizeDefaults($options['defaults'] ?? null);
        $defaults = array_merge($inheritedDefaults, $laminasDefaults);

        $constraints = $this->normalizeConstraints($options['constraints'] ?? null);

        $children = is_array($node['child_routes'] ?? null) ? $node['child_routes'] : [];
        $hasChildren = $children !== [];
        // Laminas: a node is dispatchable on its own when it has no children, or
        // when it opts in with may_terminate.
        $mayTerminate = ($node['may_terminate'] ?? false) === true;
        $isDispatchable = !$hasChildren || $mayTerminate;

        if ($isDispatchable) {
            $route = $this->buildRoute($moduleName, $fullPattern, $defaults, $constraints);
            if ($route instanceof Route) {
                $collection->add($this->symfonyRouteName($moduleName, $routeName), $route);
            }
        }

        foreach ($children as $childName => $childNode) {
            if (!is_array($childNode)) {
                continue;
            }
            $this->addRouteNode(
                $collection,
                $moduleName,
                $routeName . '/' . (string) $childName,
                $childNode,
                $fullPattern,
                $defaults,
            );
        }
    }

    /**
     * Build a single Symfony Route from a fully-resolved Laminas pattern.
     *
     * @param array<string, scalar|null> $defaults
     * @param array<string, string>      $constraints
     */
    private function buildRoute(
        string $moduleName,
        string $rawPattern,
        array $defaults,
        array $constraints,
    ): ?Route {
        $translation = $this->translatePattern($rawPattern);
        if ($translation === null) {
            return null;
        }

        [$path, $placeholders] = $translation;

        $routeDefaults = $defaults;
        $routeDefaults[self::ATTR_MODULE] = $moduleName;

        // Every placeholder must carry a default to be optional in Symfony, and
        // all zend_module placeholders are trailing-optional. Supply null where
        // the Laminas config gave no explicit default.
        foreach ($placeholders as $name) {
            if (!array_key_exists($name, $routeDefaults)) {
                $routeDefaults[$name] = null;
            }
        }

        // Keep only the requirements whose placeholder survived into the path.
        $requirements = array_intersect_key($constraints, array_flip($placeholders));

        return new Route($path, $routeDefaults, $requirements);
    }

    /**
     * Convert a Laminas route string into a Symfony path plus the ordered list
     * of placeholder names it contains.
     *
     * Laminas syntax: `:name` is a placeholder; `[ ... ]` marks an optional
     * segment. The observed configs use only flat (non-nested) optional groups.
     * Duplicate placeholder names (present in malformed legacy config such as
     * `[/:id][/:val][/:id][/:val]`) are unreachable in Laminas too — PCRE
     * rejects duplicate named groups — so later repeats are dropped to produce
     * a valid Symfony path covering the same reachable URLs.
     *
     * @return array{string, list<string>}|null the Symfony path and placeholder
     *                                           names, or null if no usable path
     */
    private function translatePattern(string $pattern): ?array
    {
        $path = '';
        $placeholders = [];
        $seen = [];
        $length = strlen($pattern);

        for ($i = 0; $i < $length; $i++) {
            $char = $pattern[$i];

            if ($char === '[' || $char === ']') {
                // Optionality is expressed via defaults, not bracket syntax.
                continue;
            }

            if ($char === ':') {
                $name = '';
                $j = $i + 1;
                while ($j < $length && ($pattern[$j] === '_' || ctype_alnum($pattern[$j]))) {
                    $name .= $pattern[$j];
                    $j++;
                }
                if ($name === '') {
                    $path .= $char;
                    continue;
                }

                if (isset($seen[$name])) {
                    // Drop the duplicate placeholder and the separator that was
                    // just emitted for it (e.g. the "/" before "[/:id]").
                    if (str_ends_with($path, '/')) {
                        $path = substr($path, 0, -1);
                    }
                } else {
                    $seen[$name] = true;
                    $placeholders[] = $name;
                    $path .= '{' . $name . '}';
                }

                $i = $j - 1;
                continue;
            }

            $path .= $char;
        }

        if ($path === '' || $path[0] !== '/') {
            return null;
        }

        return [$path, $placeholders];
    }

    /**
     * @param mixed $defaults
     * @return array<string, scalar|null>
     */
    private function normalizeDefaults($defaults): array
    {
        if (!is_array($defaults)) {
            return [];
        }

        $normalized = [];
        foreach ($defaults as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if ($value !== null && !is_scalar($value)) {
                continue;
            }
            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * @param mixed $constraints
     * @return array<string, string>
     */
    private function normalizeConstraints($constraints): array
    {
        if (!is_array($constraints)) {
            return [];
        }

        $normalized = [];
        foreach ($constraints as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    private function symfonyRouteName(string $moduleName, string $routeName): string
    {
        return 'zend_module.' . $moduleName . '.' . $routeName;
    }

    /**
     * @return iterable<string, string> module name => absolute config file path
     */
    private function moduleConfigFiles(): iterable
    {
        $moduleRoot = $this->zendModulesDir . '/module';
        // Guard before handing the path to Finder: Finder throws
        // DirectoryNotFoundException (a \LogicException) on a missing root,
        // which would escape the front controller's \RuntimeException fallback
        // and abort the request instead of falling through to legacy.
        if (!is_dir($moduleRoot)) {
            return;
        }

        $finder = (new Finder())
            ->files()
            ->depth('== 2')
            ->name('module.config.php')
            ->sortByName()
            ->in($moduleRoot);

        foreach ($finder as $file) {
            // getRelativePath() is "<ModuleName>/config"; dirname strips the
            // trailing "config" leaving the module directory name.
            yield basename(dirname($file->getRelativePath())) => $file->getPathname();
        }
    }
}
