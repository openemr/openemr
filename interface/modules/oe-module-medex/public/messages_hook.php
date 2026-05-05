<?php

/**
 * MedEx Event Dispatcher Hook for messages.php
 *
 * This file provides a simple event dispatcher that can be included
 * in messages.php to dispatch MedEx events without modifying core files.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Only proceed if MedEx module is enabled
if ($GLOBALS['medex_enable'] == '1') {
    // Check if the event dispatcher is available
    if (class_exists('Symfony\Component\EventDispatcher\EventDispatcher')) {
        try {
            // Get the event dispatcher
            $eventDispatcher = $GLOBALS['kernel']?->getEventDispatcher();
            
            if ($eventDispatcher) {
                // Import required classes
                require_once __DIR__ . '/../Events/MessagesPageRenderEvent.php';
                require_once __DIR__ . '/../Listeners/MessagesPageListener.php';
                
                // Create the event listener
                $listener = new \OpenEMR\Modules\MedEx\Listeners\MessagesPageListener();
                
                // Register the listener for all injection points
                $eventDispatcher->addListener(
                    \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::EVENT_RENDER,
                    [$listener, 'onPageRender']
                );
                
                // Dispatch navigation injection
                $navigationEvent = new \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent(
                    \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::INJECT_NAVIGATION,
                    $_REQUEST,
                    $GLOBALS['medex_enable'] == '1' ? $logged_in : null,
                    true
                );
                $eventDispatcher->dispatch($navigationEvent, \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::EVENT_RENDER);
                
                // Dispatch SMS tab injection
                $smsTabEvent = new \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent(
                    \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::INJECT_SMS_TAB,
                    $_REQUEST,
                    $GLOBALS['medex_enable'] == '1' ? $logged_in : null,
                    true
                );
                $eventDispatcher->dispatch($smsTabEvent, \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::EVENT_RENDER);
                
                // Dispatch SMS Zone content injection
                $smsZoneEvent = new \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent(
                    \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::INJECT_SMS_ZONE_CONTENT,
                    $_REQUEST,
                    $GLOBALS['medex_enable'] == '1' ? $logged_in : null,
                    true
                );
                $eventDispatcher->dispatch($smsZoneEvent, \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::EVENT_RENDER);
                
                // Dispatch scripts injection
                $scriptsEvent = new \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent(
                    \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::INJECT_SCRIPTS,
                    $_REQUEST,
                    $GLOBALS['medex_enable'] == '1' ? $logged_in : null,
                    true
                );
                $eventDispatcher->dispatch($scriptsEvent, \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::EVENT_RENDER);
                
                // Dispatch styles injection
                $stylesEvent = new \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent(
                    \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::INJECT_STYLES,
                    $_REQUEST,
                    $GLOBALS['medex_enable'] == '1' ? $logged_in : null,
                    true
                );
                $eventDispatcher->dispatch($stylesEvent, \OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::EVENT_RENDER);
            }
        } catch (Exception $e) {
            // Log error but don't break the page
            error_log('MedEx Event Dispatcher Error: ' . $e->getMessage());
        }
    }
}
