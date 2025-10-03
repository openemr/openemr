<?php

/**
 * Extensible Routing Configuration for OpenEMR Front Controller.
 *
 * Provides configuration-based routing system following Symfony front controller patterns.
 * Routes can be defined programmatically or loaded from config files.
 *
 * AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    OpenCoreEMR, Inc.
 * @copyright Copyright (c) 2025 OpenCoreEMR, Inc.
 * @license   GPLv3
 */

namespace OpenCoreEMR\FrontController;

class RouteConfig
{
    /**
     * Forbidden path patterns that should return 404.
     *
     * @var array
     */
    private array $forbiddenPatterns = [];

    /**
     * Admin-only path patterns requiring elevated privileges.
     *
     * @var array
     */
    private array $adminPatterns = [];

    /**
     * Likely bugs - deprecated paths that should be blocked.
     *
     * @var array
     */
    private array $deprecatedPaths = [];

    public function __construct()
    {
        $this->initializeDefaultRoutes();
    }

    /**
     * Initialize default routing rules.
     */
    private function initializeDefaultRoutes(): void
    {
        // Forbidden paths - security restrictions
        $this->forbiddenPatterns = [
            '#^portal/patient/fwk/libs/#',  // Portal library files
            '#^sites/[^/]+/documents/#',     // Direct document access
        ];

        // Admin-only paths - require elevated privileges
        $this->adminPatterns = [
            '#^(admin|setup|rector|phpstan_panther_alias|acl_setup|acl_upgrade|' .
            'sl_convert|sql_upgrade|gacl/setup|ippf_upgrade|sql_patch)\.php$#',
        ];

        // Deprecated paths (likely bugs) - should return 404
        $this->deprecatedPaths = [
            '#^ccdaservice/packages/oe-cqm-service/index\.php$#',
            '#^contrib/util/dupecheck/index\.php$#',
        ];
    }

    /**
     * Add a forbidden path pattern.
     *
     * @param string $pattern Regular expression pattern
     */
    public function addForbiddenPattern(string $pattern): void
    {
        $this->forbiddenPatterns[] = $pattern;
    }

    /**
     * Add an admin-only path pattern.
     *
     * @param string $pattern Regular expression pattern
     */
    public function addAdminPattern(string $pattern): void
    {
        $this->adminPatterns[] = $pattern;
    }

    /**
     * Add a deprecated path pattern.
     *
     * @param string $pattern Regular expression pattern
     */
    public function addDeprecatedPath(string $pattern): void
    {
        $this->deprecatedPaths[] = $pattern;
    }

    /**
     * Check if route matches any forbidden pattern.
     *
     * @param string $route The route to check
     * @return bool True if forbidden
     */
    public function isForbidden(string $route): bool
    {
        foreach ($this->forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $route)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if route matches any admin-only pattern.
     *
     * @param string $route The route to check
     * @return bool True if admin-only
     */
    public function requiresAdmin(string $route): bool
    {
        foreach ($this->adminPatterns as $pattern) {
            if (preg_match($pattern, $route)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if route is a deprecated path.
     *
     * @param string $route The route to check
     * @return bool True if deprecated
     */
    public function isDeprecated(string $route): bool
    {
        foreach ($this->deprecatedPaths as $pattern) {
            if (preg_match($pattern, $route)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load routes from configuration file.
     *
     * Expected format:
     * [
     *     'forbidden' => ['pattern1', 'pattern2'],
     *     'admin' => ['pattern1', 'pattern2'],
     *     'deprecated' => ['pattern1', 'pattern2']
     * ]
     *
     * @param string $configPath Path to config file
     * @return bool True if loaded successfully
     */
    public function loadFromFile(string $configPath): bool
    {
        if (!file_exists($configPath)) {
            return false;
        }

        $config = require $configPath;

        if (isset($config['forbidden']) && is_array($config['forbidden'])) {
            foreach ($config['forbidden'] as $pattern) {
                $this->addForbiddenPattern($pattern);
            }
        }

        if (isset($config['admin']) && is_array($config['admin'])) {
            foreach ($config['admin'] as $pattern) {
                $this->addAdminPattern($pattern);
            }
        }

        if (isset($config['deprecated']) && is_array($config['deprecated'])) {
            foreach ($config['deprecated'] as $pattern) {
                $this->addDeprecatedPath($pattern);
            }
        }

        return true;
    }

    /**
     * Get all configured forbidden patterns.
     *
     * @return array
     */
    public function getForbiddenPatterns(): array
    {
        return $this->forbiddenPatterns;
    }

    /**
     * Get all configured admin patterns.
     *
     * @return array
     */
    public function getAdminPatterns(): array
    {
        return $this->adminPatterns;
    }

    /**
     * Get all configured deprecated paths.
     *
     * @return array
     */
    public function getDeprecatedPaths(): array
    {
        return $this->deprecatedPaths;
    }
}
