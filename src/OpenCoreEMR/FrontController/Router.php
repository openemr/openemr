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
    private readonly string $baseDir;
    private string $route;
    private string $siteId;
    private readonly RouteConfigInterface $config;

    public function __construct(string $baseDir, ?RouteConfigInterface $config = null)
    {
        $this->baseDir = realpath($baseDir);
        // Ensure baseDir is valid
        if ($this->baseDir === false || $this->baseDir === '') {
            throw new \RuntimeException('Invalid base directory provided to Router');
        }
        $this->config = $config ?? new RouteConfig();
    }

    /**
     * Extract and clean route from request
     */
    public function extractRoute(): string
    {
        $route = $_GET['_ROUTE'] ?? '';

        // Remove query string
        $route = strtok($route, '?');

        $this->route = $route;
        return $route;
    }

    /**
     * Determine multisite ID
     */
    public function determineSiteId(): string
    {
        // explicit site parameter or session-based detection.
        $this->siteId = $_GET['site'] ?? 'default';

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
        // Skip trailing slash redirect for .php files
        if ($this->route !== '' && !str_ends_with($this->route, '/') && pathinfo($this->route, PATHINFO_EXTENSION) !== 'php') {
            // Ensure proper relative redirect with query string preservation
            $queryString = $_SERVER['QUERY_STRING'] ?? '';
            $redirectUrl = $this->route . '/' . ($queryString ? '?' . $queryString : '');
            header('Location: ' . $redirectUrl, true, 301);
            exit;
        }
    }

    /**
     * Check if route is forbidden or deprecated
     */
    public function isForbiddenPath(): bool
    {
        return $this->config->isDeprecated($this->route) || $this->config->isForbidden($this->route);
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
        if ($targetFile === false || !str_starts_with($targetFile, $this->baseDir . DIRECTORY_SEPARATOR)) {
            return null;
        }

        // Verify file exists
        if (!is_file($targetFile)) {
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
