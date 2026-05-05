<?php

/**
 * PatientTrackerPageRenderEvent - Event for injecting MedEx content into patient_tracker.php
 *
 * This event is dispatched at various points in the patient_tracker.php page to allow
 * the MedEx module to inject navigation, reminder icons, and communication status.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Events;

require_once __DIR__ . '/Event.php';

class PatientTrackerPageRenderEvent extends Event
{
    /**
     * Event name for patient tracker page rendering
     */
    const EVENT_RENDER = 'medex.patient_tracker.render';

    /**
     * Injection point constants
     */
    const INJECT_NAVIGATION = 'navigation';      // After <head>, before main content
    const INJECT_STATUS_ICONS = 'status_icons';  // In "Current Status" column
    const INJECT_MODALITIES = 'modalities';      // Show possible communication methods
    const INJECT_SCRIPTS = 'scripts';            // JavaScript functions
    const INJECT_ONLINE_STATUS = 'online_status'; // MedEx online/offline status

    /**
     * @var string The injection point identifier
     */
    private string $injectionPoint;

    /**
     * @var array Appointment data
     */
    private array $appointment;

    /**
     * @var bool Whether MedEx is enabled in globals
     */
    private bool $medexEnabled;

    /**
     * @var mixed Logged in user data from MedEx API
     */
    private $loggedInUser;

    /**
     * @var string Content to inject into the page
     */
    private string $content = '';

    /**
     * @var array MedEx icon data
     */
    private array $icons = [];

    /**
     * @var array Additional context data
     */
    private array $contextData = [];

    /**
     * @var array Audit data for status change events
     */
    private array $auditData = [];

    /**
     * Constructor
     *
     * @param string $injectionPoint Where to inject content
     * @param array $appointment Appointment data (for row-specific injections)
     * @param bool $medexEnabled Whether MedEx is enabled
     * @param mixed $loggedInUser Logged in user data
     */
    public function __construct(
        string $injectionPoint,
        array $appointment = [],
        bool $medexEnabled = false,
        $loggedInUser = null
    ) {
        $this->injectionPoint = $injectionPoint;
        $this->appointment = $appointment;
        $this->medexEnabled = $medexEnabled;
        $this->loggedInUser = $loggedInUser;
    }

    /**
     * Set audit data
     *
     * @param array $data
     * @return self
     */
    public function setAuditData(array $data): self
    {
        $this->auditData = $data;
        return $this;
    }

    /**
     * Get audit data
     *
     * @return array
     */
    public function getAuditData(): array
    {
        return $this->auditData;
    }

    /**
     * Get the injection point
     */
    public function getInjectionPoint(): string
    {
        return $this->injectionPoint;
    }

    /**
     * Get appointment data
     */
    public function getAppointment(): array
    {
        return $this->appointment;
    }

    /**
     * Check if MedEx is enabled
     */
    public function isMedExEnabled(): bool
    {
        return $this->medexEnabled;
    }

    /**
     * Get logged in user
     */
    public function getLoggedInUser()
    {
        return $this->loggedInUser;
    }

    /**
     * Get the content to inject
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set the content to inject
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Append to existing content
     */
    public function appendContent(string $content): self
    {
        $this->content .= $content;
        return $this;
    }

    /**
     * Get icon data
     */
    public function getIcons(): array
    {
        return $this->icons;
    }

    /**
     * Set icon data
     */
    public function setIcons(array $icons): self
    {
        $this->icons = $icons;
        return $this;
    }

    /**
     * Get context data
     */
    public function getContextData(?string $key = null)
    {
        if ($key === null) {
            return $this->contextData;
        }
        return $this->contextData[$key] ?? null;
    }

    /**
     * Set context data
     */
    public function setContextData(string $key, $value): self
    {
        $this->contextData[$key] = $value;
        return $this;
    }

    /**
     * Check if this is a specific injection point
     */
    public function isInjectionPoint(string $point): bool
    {
        return $this->injectionPoint === $point;
    }

    /**
     * Check if content has been set
     */
    public function hasContent(): bool
    {
        return !empty($this->content);
    }
}
