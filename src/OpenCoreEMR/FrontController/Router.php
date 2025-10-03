<?php

/**
 * Front Controller Router.
 *
 * Routes requests to appropriate PHP files, blocks .inc.php access,
 * preserves multisite selection. Target files handle auth/sessions/errors.
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

class Router
{
    private string $baseDir;
    private string $route;
    private string $siteId;
    private RouteConfig $config;

    public function __construct(string $baseDir, ?RouteConfig $config = null)
    {
        $this->baseDir = realpath($baseDir);
        $this->config = $config ?? new RouteConfig();
    }

    /**
     * Extract and clean route from request
     */
    public function extractRoute(): string
    {
        $route = $_GET['_ROUTE'] ?? '';

        // Remove query string
        if (($pos = strpos($route, '?')) !== false) {
            $route = substr($route, 0, $pos);
        }

        $this->route = $route;
        return $route;
    }

    /**
     * Determine multisite ID
     */
    public function determineSiteId(): string
    {
        if (!empty($_GET['site'])) {
            $this->siteId = $_GET['site'];
        } elseif (is_dir("sites/" . ($_SERVER['HTTP_HOST'] ?? 'default'))) {
            $this->siteId = ($_SERVER['HTTP_HOST'] ?? 'default');
        } else {
            $this->siteId = 'default';
        }

        $_GET['site'] = $this->siteId;
        return $this->siteId;
    }

    /**
     * Handle trailing slash redirect
     *
     * We always want there to be a trailing slash for consistency
     */
    public function handleTrailingSlash(): void
    {
        // Add trailing slash if missing (and route is not empty)
        if ($this->route !== '' && substr($this->route, -1) !== '/') {
            header('Location: ' . $this->route . '/', true, 301);
            exit;
        }
    }

    /**
     * Check if route is forbidden or deprecated
     */
    public function isForbiddenPath(): bool
    {
        // Check if route is deprecated (likely bugs - should return 404)
        if ($this->config->isDeprecated($this->route)) {
            return true;
        }

        // Check if route matches forbidden patterns
        return $this->config->isForbidden($this->route);
    }

    /**
     * Check if route requires admin access
     */
    public function requiresAdmin(): bool
    {
        return $this->config->requiresAdmin($this->route);
    }

    /**
     * Resolve target file path
     */
    public function resolveTargetFile(): ?string
    {
        $targetFile = realpath($this->baseDir . '/' . $this->route);

        // Path traversal prevention
        if ($targetFile === false || strpos($targetFile, $this->baseDir) !== 0) {
            return null;
        }

        // Verify file exists
        if (!file_exists($targetFile) || !is_file($targetFile)) {
            return null;
        }

        // Only route .php files
        if (pathinfo($targetFile, PATHINFO_EXTENSION) !== 'php') {
            return null;
        }

        return $targetFile;
    }

    /**
     * Get current route
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Get current site ID
     */
    public function getSiteId(): string
    {
        return $this->siteId;
    }
}
