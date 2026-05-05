<?php

/**
 * PatientTrackerRenderEvent - Event for injecting MedEx content into patient tracker
 *
 * This event is dispatched at various points in the patient tracker rendering process,
 * allowing the MedEx module to inject campaign status icons and navigation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Events;

require_once __DIR__ . '/Event.php';

class PatientTrackerRenderEvent extends Event
{
    /**
     * Injection point constants
     */
    public const INJECT_SCRIPTS = 'scripts';
    public const INJECT_NAVIGATION = 'navigation';

    /**
     * @var string Content to be injected
     */
    private string $content = '';

    /**
     * @var string Injection point identifier
     */
    private string $injectionPoint;

    /**
     * @var array Request parameters (GET/POST)
     */
    private array $request;

    /**
     * @var mixed Logged in user data
     */
    private $loggedInUser;

    /**
     * @var bool Whether MedEx is enabled
     */
    private bool $medExEnabled;

    /**
     * Constructor
     */
    public function __construct(
        string $injectionPoint,
        array $request = [],
        $loggedInUser = null,
        bool $medExEnabled = true
    ) {
        $this->injectionPoint = $injectionPoint;
        $this->request = $request;
        $this->loggedInUser = $loggedInUser;
        $this->medExEnabled = $medExEnabled;
    }

    /**
     * Get injection point
     */
    public function getInjectionPoint(): string
    {
        return $this->injectionPoint;
    }

    /**
     * Get content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set content (replaces existing)
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Append content (adds to existing)
     */
    public function appendContent(string $content): void
    {
        $this->content .= $content;
    }

    /**
     * Get request parameters
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * Get logged in user
     */
    public function getLoggedInUser()
    {
        return $this->loggedInUser;
    }

    /**
     * Check if MedEx is enabled
     */
    public function isMedExEnabled(): bool
    {
        return $this->medExEnabled;
    }
}
