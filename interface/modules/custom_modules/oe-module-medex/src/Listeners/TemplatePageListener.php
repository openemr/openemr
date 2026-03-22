<?php

/**
 * TemplatePageListener - Generic listener for TemplatePageEvent
 *
 * This listener responds to OpenEMR's generic TemplatePageEvent and determines
 * which page is being rendered, then delegates to the appropriate injection logic.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Listeners;

use OpenEMR\Events\Core\TemplatePageEvent;

class TemplatePageListener
{
    private MessagesPageInjectionListener $messagesListener;
    private PatientTrackerInjectionListener $patientTrackerListener;

    public function __construct()
    {
        $this->messagesListener = new MessagesPageInjectionListener();
        $this->patientTrackerListener = new PatientTrackerInjectionListener();
    }

    /**
     * Handle generic TemplatePageEvent - detect page and inject appropriately
     */
    public function onTemplatePageRender(TemplatePageEvent $event): void
    {
        $pageName = $event->getPageName();
        error_log('[MedEx] TemplatePageListener received event for page: ' . $pageName);

        switch ($pageName) {
            case 'recalls':
                error_log('[MedEx] Detected Recall Board - injecting MedEx enhancements');
                $this->handleRecallsPage($event);
                break;

            case 'patient-tracker':
                error_log('[MedEx] Detected Patient Tracker - injecting MedEx enhancements');
                $this->handlePatientTrackerPage($event);
                break;

            default:
                // Not a page we handle - do nothing
                error_log('[MedEx] Unknown page: ' . $pageName . ' - skipping');
                break;
        }
    }

    /**
     * Inject MedEx functionality into Recall Board
     */
    private function handleRecallsPage(TemplatePageEvent $event): void
    {
        // Use the existing messages page injection listener
        $this->messagesListener->handleScriptsFromTemplateEvent($event);
    }

    /**
     * Inject MedEx functionality into Patient Tracker
     */
    private function handlePatientTrackerPage(TemplatePageEvent $event): void
    {
        // Use the existing patient tracker injection listener
        $this->patientTrackerListener->handleScriptsFromTemplateEvent($event);
    }
}
