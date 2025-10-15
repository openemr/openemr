<?php

/**
 * FrontControllerEvent class is fired during front controller routing lifecycle.
 *
 * Provides extension points for custom modules to hook into the front controller
 * request processing pipeline for logging, authentication, rate limiting, and
 * other custom processing needs.
 *
 * AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenCoreEMR, Inc.
 * @copyright Copyright (c) 2025 OpenCoreEMR, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\FrontController;

use Symfony\Contracts\EventDispatcher\Event;

class FrontControllerEvent extends Event
{
    /**
     * Event fired early in front controller lifecycle, after feature flag check
     * and before routing validation. Use for request preprocessing, logging,
     * custom authentication, or rate limiting.
     */
    public const EVENT_EARLY = 'front_controller.early';

    /**
     * Event fired late in front controller lifecycle, after content is loaded
     * and at end of request lifecycle. Use for post-processing, performance
     * monitoring, or custom headers.
     */
    public const EVENT_LATE = 'front_controller.late';

    /**
     * FrontControllerEvent constructor.
     *
     * @param string $route The route being processed
     * @param string $siteId The multisite identifier
     * @param array $context Additional context data
     */
    public function __construct(
        /**
         * The route being processed
         */
        private readonly string $route = '',
        /**
         * The site ID (multisite identifier)
         */
        private readonly string $siteId = 'default',
        /**
         * Context data for the event
         */
        private array $context = []
    ) {
    }

    /**
     * Get the route being processed
     *
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Get the site ID
     *
     * @return string
     */
    public function getSiteId(): string
    {
        return $this->siteId;
    }

    /**
     * Get context data
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set context data
     *
     * @param array $context
     * @return FrontControllerEvent
     */
    public function setContext(array $context): FrontControllerEvent
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get a specific context value
     *
     * @param string $key
     * @return mixed|null
     */
    public function getContextValue(string $key)
    {
        return $this->context[$key] ?? null;
    }

    /**
     * Set a specific context value
     *
     * @param string $key
     * @param mixed $value
     * @return FrontControllerEvent
     */
    public function setContextValue(string $key, $value): FrontControllerEvent
    {
        $this->context[$key] = $value;
        return $this;
    }
}
