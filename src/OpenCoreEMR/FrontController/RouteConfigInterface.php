<?php

/**
 * Interface for OpenEMR Front Controller routing configuration.
 *
 * Defines contract for routing configuration implementations.
 * Enables dependency injection and supports custom routing strategies.
 *
 * AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    OpenCoreEMR, Inc.
 * @copyright Copyright (c) 2025 OpenCoreEMR, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenCoreEMR\FrontController;

interface RouteConfigInterface
{
    /**
     * Add a forbidden path pattern.
     *
     * @param string $pattern Regular expression pattern
     */
    public function addForbiddenPath(string $pattern): void;

    /**
     * Add an admin-only path pattern.
     *
     * @param string $pattern Regular expression pattern
     */
    public function addAdminPath(string $pattern): void;

    /**
     * Add a deprecated path pattern.
     *
     * @param string $pattern Regular expression pattern
     */
    public function addDeprecatedPath(string $pattern): void;

    /**
     * Check if route matches any forbidden pattern.
     *
     * @param string $route The route to check
     * @return bool True if forbidden
     */
    public function isForbidden(string $route): bool;

    /**
     * Check if route matches any admin-only pattern.
     *
     * @param string $route The route to check
     * @return bool True if admin-only
     */
    public function requiresAdmin(string $route): bool;

    /**
     * Check if route is a deprecated path.
     *
     * @param string $route The route to check
     * @return bool True if deprecated
     */
    public function isDeprecated(string $route): bool;

    /**
     * Load routes from configuration file.
     *
     * @param string $configPath Path to configuration file
     * @return bool True if loaded successfully
     */
    public function loadFromFile(string $configPath): bool;
}
