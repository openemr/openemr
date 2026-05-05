<?php

/**
 * MessagesPageRenderEvent - Event for injecting MedEx content into messages.php
 *
 * This event is dispatched at various points in the messages.php page to allow
 * the MedEx module to inject navigation, content, and functionality when enabled.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Events;

// Ensure Event base class is loaded
if (!class_exists('OpenEMR\\Modules\\MedEx\\Events\\Event')) {
    require_once __DIR__ . '/Event.php';
}

class MessagesPageRenderEvent extends Event
{
    /**
     * Event name for messages page rendering
     */
    const EVENT_RENDER = 'medex.messages.render';

    /**
     * Injection point constants
     */
    const INJECT_NAVIGATION = 'navigation';      // After <head>, before main content
    const INJECT_CONTENT = 'content';            // Main content area for MedEx pages
    const INJECT_SMS_TAB = 'sms_tab';           // Inside SMS Zone tab
    const INJECT_SMS_ZONE_CONTENT = 'sms_zone_content';  // SMS Zone tab content area
    const INJECT_SCRIPTS = 'scripts';            // JavaScript at end of page
    const INJECT_STYLES = 'styles';              // CSS in head section

    /**
     * @var string The injection point identifier
     */
    private string $injectionPoint;

    /**
     * @var array Request parameters from $_REQUEST
     */
    private array $request;

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
     * @var array Additional data for context
     */
    private array $contextData = [];

    /**
     * @var array Audit data for action logging
     */
    private array $auditData = [];

    /**
     * Constructor
     *
     * @param string $injectionPoint Where to inject content
     * @param array $request Request parameters
     */
    public function __construct(
        string $injectionPoint,
        array $request = []
    ) {
        $this->injectionPoint = $injectionPoint;
        $this->request = $request;
        // Module determines if it should inject based on its own configuration
        $this->medexEnabled = $this->checkMedExEnabled();
        $this->loggedInUser = null;
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
     * Check if MedEx module is properly configured
     * Module handles its own validation - not core's responsibility
     */
    private function checkMedExEnabled(): bool
    {
        try {
            // Check if medex_prefs table exists and has API key
            // Using QueryUtils instead of sqlQuery for robustness
            $prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT ME_api_key FROM medex_prefs LIMIT 1", []);
            return !empty($prefs['ME_api_key']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the injection point
     */
    public function getInjectionPoint(): string
    {
        return $this->injectionPoint;
    }

    /**
     * Get request parameters
     */
    public function getRequest(): array
    {
        return $this->request;
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
