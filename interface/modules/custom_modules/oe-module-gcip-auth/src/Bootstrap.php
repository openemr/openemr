<?php

/**
 * GCIP Authentication Module Bootstrap
 * 
 * <!-- AI-Generated Content Start -->
 * This class handles the initialization and event subscription for the GCIP
 * Authentication module. It sets up event listeners for authentication events,
 * registers module services, and integrates with OpenEMR's core authentication
 * system through the event dispatcher.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR\Modules\GcipAuth
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\GcipAuth;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OpenEMR\Events\Core\ScriptFilterEvent;
use OpenEMR\Events\Core\StyleFilterEvent;
use OpenEMR\Events\Authentication\LoginEvent;
use OpenEMR\Events\Authentication\LogoutEvent;
use OpenEMR\Modules\GcipAuth\Services\GcipAuthService;
use OpenEMR\Modules\GcipAuth\Services\GcipConfigService;
use OpenEMR\Modules\GcipAuth\Services\GcipAuditService;

/**
 * Bootstrap class for GCIP Authentication module
 */
class Bootstrap
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var GcipAuthService
     */
    private $authService;

    /**
     * @var GcipConfigService
     */
    private $configService;

    /**
     * @var GcipAuditService
     */
    private $auditService;

    /**
     * Bootstrap constructor
     * 
     * <!-- AI-Generated Content Start -->
     * Initializes the bootstrap with the event dispatcher and sets up
     * the core services required for GCIP authentication functionality.
     * <!-- AI-Generated Content End -->
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->configService = new GcipConfigService();
        $this->authService = new GcipAuthService($this->configService);
        $this->auditService = new GcipAuditService();
    }

    /**
     * Subscribe to OpenEMR events
     * 
     * <!-- AI-Generated Content Start -->
     * Registers event listeners for authentication, style, and script events
     * to integrate GCIP authentication functionality into OpenEMR's core
     * authentication flow and user interface.
     * <!-- AI-Generated Content End -->
     */
    public function subscribeToEvents(): void
    {
        // Authentication event listeners - AI-Generated
        $this->eventDispatcher->addListener(LoginEvent::EVENT_HANDLE, [$this, 'onLoginEvent']);
        $this->eventDispatcher->addListener(LogoutEvent::EVENT_HANDLE, [$this, 'onLogoutEvent']);
        
        // UI integration event listeners - AI-Generated
        $this->eventDispatcher->addListener(ScriptFilterEvent::EVENT_HANDLE, [$this, 'onScriptFilter']);
        $this->eventDispatcher->addListener(StyleFilterEvent::EVENT_HANDLE, [$this, 'onStyleFilter']);
    }

    /**
     * Handle login events
     * 
     * <!-- AI-Generated Content Start -->
     * Processes login events to integrate GCIP authentication validation,
     * audit logging, and user session management with OpenEMR's core
     * authentication system.
     * <!-- AI-Generated Content End -->
     *
     * @param LoginEvent $event
     */
    public function onLoginEvent(LoginEvent $event): void
    {
        // Only process if GCIP is enabled - AI-Generated
        if (!$this->configService->isGcipEnabled()) {
            return;
        }

        $username = $event->getUsername();
        
        // Log authentication attempt - AI-Generated
        $this->auditService->logAuthenticationAttempt($username, 'login', 'GCIP authentication event processed');
    }

    /**
     * Handle logout events
     * 
     * <!-- AI-Generated Content Start -->
     * Processes logout events to clean up GCIP session data, invalidate
     * tokens, and ensure proper audit logging for security compliance.
     * <!-- AI-Generated Content End -->
     *
     * @param LogoutEvent $event
     */
    public function onLogoutEvent(LogoutEvent $event): void
    {
        // Only process if GCIP is enabled - AI-Generated
        if (!$this->configService->isGcipEnabled()) {
            return;
        }

        $username = $event->getUsername();
        
        // Clean up GCIP session data - AI-Generated
        $this->authService->cleanupUserSession($username);
        
        // Log logout event - AI-Generated
        $this->auditService->logAuthenticationAttempt($username, 'logout', 'GCIP logout processed');
    }

    /**
     * Add GCIP authentication scripts
     * 
     * <!-- AI-Generated Content Start -->
     * Injects JavaScript files required for GCIP authentication functionality
     * into OpenEMR pages when the module is enabled and configured.
     * <!-- AI-Generated Content End -->
     *
     * @param ScriptFilterEvent $event
     */
    public function onScriptFilter(ScriptFilterEvent $event): void
    {
        // Only add scripts if GCIP is enabled - AI-Generated
        if (!$this->configService->isGcipEnabled()) {
            return;
        }

        // Add GCIP authentication JavaScript - AI-Generated
        $modulePath = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-gcip-auth';
        $event->addScript($modulePath . '/public/js/gcip-auth.js');
    }

    /**
     * Add GCIP authentication styles
     * 
     * <!-- AI-Generated Content Start -->
     * Injects CSS files required for GCIP authentication UI components
     * into OpenEMR pages when the module is enabled and configured.
     * <!-- AI-Generated Content End -->
     *
     * @param StyleFilterEvent $event
     */
    public function onStyleFilter(StyleFilterEvent $event): void
    {
        // Only add styles if GCIP is enabled - AI-Generated
        if (!$this->configService->isGcipEnabled()) {
            return;
        }

        // Add GCIP authentication CSS - AI-Generated
        $modulePath = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-gcip-auth';
        $event->addStyleSheet($modulePath . '/public/css/gcip-auth.css');
    }
}