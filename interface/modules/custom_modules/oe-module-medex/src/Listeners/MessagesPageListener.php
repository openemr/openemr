<?php

/**
 * MessagesPageListener - Handles injection of MedEx content into messages.php
 *
 * This listener injects navigation, content, and functionality for the MedEx module
 * into the OpenEMR messages.php page when the module is enabled.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Listeners;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent;
use OpenEMR\Modules\MedEx\Services\DisplayService;
use OpenEMR\Modules\MedEx\Services\PracticeService;
use OpenEMR\Modules\MedEx\Services\CampaignService;
use OpenEMR\Modules\MedEx\Services\EventsService;
use OpenEMR\Modules\MedEx\Services\CallbackService;
use OpenEMR\Modules\MedEx\Services\SetupService;
use OpenEMR\Modules\MedEx\Services\LoggingService;
use OpenEMR\Modules\MedEx\Services\DisplayService as MedExDisplayService;
use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Core\OEGlobalsBag;

class MessagesPageListener
{
    private $globalsBag;
    private $medexApi;

    public function __construct()
    {
        if (class_exists('OpenEMR\Core\OEGlobalsBag')) {
            $this->globalsBag = OEGlobalsBag::getInstance();
        }
        $this->medexApi = new MedExAPI();
    }

    /**
     * Handle page render events
     */
    public function onPageRender(MessagesPageRenderEvent $event): void
    {
        $injectionPoint = $event->getInjectionPoint();
        $request = $event->getRequest();
        $loggedIn = $event->getLoggedInUser();

        // Route to appropriate handler based on injection point
        switch ($injectionPoint) {
            case MessagesPageRenderEvent::INJECT_NAVIGATION:
                $this->handleNavigation($event, $loggedIn);
                break;

            case MessagesPageRenderEvent::INJECT_CONTENT:
                $this->handleContent($event, $loggedIn);
                break;

            case MessagesPageRenderEvent::INJECT_SMS_TAB:
                $this->handleSMSTab($event);
                break;

            case MessagesPageRenderEvent::INJECT_SMS_ZONE_CONTENT:
                $this->renderSMSZoneContent($event, $loggedIn);
                break;

            case MessagesPageRenderEvent::INJECT_SCRIPTS:
                $this->handleScripts($event);
                break;

            case MessagesPageRenderEvent::INJECT_STYLES:
                $this->handleStyles($event);
                break;

            case 'SMS_bot':
                $this->renderSMSBotPage($event, $loggedIn);
                break;

            case 'Recalls':
                $this->renderRecallsPage($event, $loggedIn);
                break;

            case 'Preferences':
                $this->renderPreferencesPage($event, $loggedIn);
                break;

            case 'icons':
                $this->renderIconsPage($event);
                break;

            case 'addRecall':
                $this->renderAddRecallPage($event, $loggedIn);
                break;
        }
    }

    /**
     * Handle navigation injection
     */
    private function handleNavigation(MessagesPageRenderEvent $event, $loggedIn): void
    {
        // Don't show navigation if nomenu is set or RCB is disabled
        if (!empty($event->getRequest()['nomenu']) || ($this->globalsBag && $this->globalsBag->get('disable_rcb') == '1')) {
            return;
        }

        $html = $this->renderNavigation($loggedIn);
        $event->setContent($html);
    }

    /**
     * Handle main content injection for MedEx-specific pages
     */
    private function handleContent(MessagesPageRenderEvent $event, $loggedIn): void
    {
        $request = $event->getRequest();
        $go = $request['go'] ?? '';

        // Determine which page to render based on 'go' parameter
        switch ($go) {
            case 'addRecall':
                $this->renderAddRecallPage($event, $loggedIn);
                break;

            case 'Recalls':
                $this->renderRecallsPage($event, $loggedIn);
                break;

            case 'Preferences':
                $this->renderPreferencesPage($event, $loggedIn);
                break;

            case 'icons':
                $this->renderIconsPage($event);
                break;

            case 'SMS_bot':
                $this->renderSMSBotPage($event, $loggedIn);
                break;

            case MessagesPageRenderEvent::INJECT_SMS_ZONE_CONTENT:
                $this->renderSMSZoneContent($event, $loggedIn);
                break;
        }
    }

    /**
     * Render navigation menu (public — called directly from messages.php)
     */
    public function renderNavigation($loggedIn): string
    {
        ob_start();
        include __DIR__ . '/../templates/navigation.php';
        return ob_get_clean();
    }

    /**
     * Inject end-of-page scripts (called from bootstrap shutdown function)
     */
    public function injectScripts(): void
    {
        error_log('[MedEx] MessagesPageListener::injectScripts() called - direct injection');
        
        // Direct script injection without event system dependencies
        echo '<script>' . "\n";
        echo 'console.log("[MedEx] Messages page MedEx scripts loaded");' . "\n";
        
        // Add MedEx-specific functionality based on request parameters
        $go = $_REQUEST['go'] ?? '';
        if ($go === 'SMS_bot') {
            echo 'console.log("[MedEx] SMS bot interface active");' . "\n";
            // Add SMS bot specific JavaScript here if needed
        }
        
        echo '</script>' . "\n";
    }

    /**
     * Render page content for ?go= routes (called directly from messages.php)
     * Replaces all legacy $MedEx->display->* and $MedEx->setup->* calls.
     *
     * @param array  $request   $_REQUEST array
     * @param mixed  $loggedIn  Login result from MedExAPI::login() or null
     */
    public function renderPageContent(array $request, $loggedIn): void
    {
        $go = $request['go'] ?? '';

        if ($go === 'setup' && !$loggedIn) {
            // Setup wizard — redirect to module admin (the old inline wizard is retired)
            echo "<title>" . xlt('MedEx Setup') . "</title>";
            $stage = $request['stage'] ?? '';
            if ($stage !== '' && !is_numeric($stage)) {
                echo "<br /><span class='title'>" . text($stage) . ' ' . xlt('Warning') . ': ' . xlt('This is not a valid request') . '.</span>';
            } else {
                $adminUrl = ($GLOBALS['webroot'] ?? '') . '/interface/modules/custom_modules/oe-module-medex/admin/index.php';
                echo '<div class="alert alert-info m-3">';
                echo '<h4>' . xlt('MedEx Setup') . '</h4>';
                echo '<p>' . xlt('Please complete MedEx setup from the Module Administration area.') . '</p>';
                echo '<a href="' . attr($adminUrl) . '" class="btn btn-primary">' . xlt('Open MedEx Admin') . '</a>';
                echo '</div>';
            }
            return;
        }

        if ($go === 'addRecall') {
            echo "<title>" . xlt('New Recall') . "</title>";
            $displayService = new \MedExApi\Services\DisplayService();
            $displayService->display_add_recall();
            return;
        }

        if (($go === 'setup' || $go === 'Preferences') && $loggedIn) {
            echo "<title>MedEx: " . xlt('Preferences') . "</title>";
            $displayService = new \MedExApi\Services\DisplayService();
            $displayService->preferences();
            return;
        }

        if ($go === 'icons') {
            echo "<title>MedEx: " . xlt('Icons') . "&#x24B8;</title>";
            $displayService = new \MedExApi\Services\DisplayService();
            $displayService->icon_template();
            return;
        }

        if ($go === 'SMS_bot') {
            echo "<title>MedEx: SMS Bot&#x24B8;</title>";
            $displayService = new \MedExApi\Services\DisplayService();
            $displayService->SMS_bot($loggedIn);
            return;
        }

        // Unknown go value
        echo "<title>" . xlt('MedEx Setup') . "</title>";
        echo xlt('Warning: Navigation error. Please refresh this page.');
    }

    /**
     * Render add recall page
     */
    private function renderAddRecallPage(MessagesPageRenderEvent $event, $loggedIn): void
    {
        if (!$loggedIn) {
            echo "<div class='alert alert-danger'>" . xlt('Not authorized') . "</div>";
            return;
        }

        $displayService = new MedExDisplayService();
        $displayService->display_add_recall();
    }

    /**
     * Render recalls page
     */
    private function renderRecallsPage(MessagesPageRenderEvent $event, $loggedIn): void
    {
        if (!$loggedIn) {
            echo "<div class='alert alert-danger'>" . xlt('Not authorized') . "</div>";
            return;
        }

        $displayService = new MedExDisplayService();
        $displayService->display_recalls($loggedIn);
    }

    /**
     * Render preferences page
     */
    private function renderPreferencesPage(MessagesPageRenderEvent $event, $loggedIn): void
    {
        if (!$loggedIn) {
            echo "<div class='alert alert-danger'>" . xlt('Not authorized') . "</div>";
            return;
        }

        $displayService = new MedExDisplayService();
        $displayService->preferences();
    }

    /**
     * Render icons page
     */
    private function renderIconsPage(MessagesPageRenderEvent $event): void
    {
        $displayService = new MedExDisplayService();
        $displayService->icon_template();
    }

    /**
     * Render SMS bot page
     */
    private function renderSMSBotPage(MessagesPageRenderEvent $event, $loggedIn): void
    {
        if ($loggedIn) {
            ob_start();
            $this->getMedExAPI()->renderSMSBot($loggedIn);
            $event->setContent(ob_get_clean());
        }
    }

    /**
     * Render SMS Zone content
     */
    private function renderSMSZoneContent(MessagesPageRenderEvent $event, $loggedIn): void
    {
        if (!$loggedIn) {
            return;
        }

        $html = '
            <div class="row tab-pane" role="tabpanel" id="sms-div">
                <div class="col-sm-4 col-md-4 col-lg-4">
                    <h4>' . xlt('SMS Zone') . '</h4>
                    <form id="smsForm" class="input-group">
                        <select id="SMS_patient" type="text" class="form-control m-0 w-100" placeholder="' . xla('Patient Name') . '"></select>
                        <span class="input-group-addon" onclick="SMS_direct();">&nbsp;&nbsp;<i id="open-sms-tooltip" class="fas fa-2x fa-phone"></i></span>
                        <input type="hidden" id="sms_pid" />
                        <input type="hidden" id="sms_mobile" value="" />
                        <input type="hidden" id="sms_allow" value="" />
                    </form>
                </div>
            </div>
        ';
        
        $event->setContent($html);
    }

    /**
     * Handle SMS tab injection
     */
    private function handleSMSTab(MessagesPageRenderEvent $event): void
    {
        // SMS tab content is only shown if user is logged in
        if (!$event->getLoggedInUser()) {
            return;
        }

        // Inject SMS Zone tab
        $event->setContent('
                    <li class="nav-item" id="li-sms" role="presentation">
                        <a href="#sms-div" id="sms-li" class="nav-link" data-toggle="pill" role="tab" aria-controls="SMS Zone" aria-selected="true">' . xlt('SMS Zone') . '</a>
                    </li>
        ');
    }

    /**
     * Handle JavaScript injection
     */
    private function handleScripts(MessagesPageRenderEvent $event): void
    {
        $webroot = $GLOBALS['webroot'] ?? '/openemr';
        
        $js = '
        // Override core SMS_direct function to use module entry point
        function SMS_direct() {
            var pid = $("#sms_pid").val();
            var m = $("#sms_mobile").val();
            var allow = $("#sms_allow").val();
            if ((pid === "") || (m === "")) {
                alert(' . xlj("MedEx needs a valid mobile number to send SMS messages...") . ');
            } else if (allow === "NO") {
                alert(' . xlj("This patient does not allow SMS messaging!") . ');
            } else {
                // Use direct module entry point to avoid session issues
                var url = "' . $webroot . '/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php' . '";
                var params = new URLSearchParams({
                    pid: pid,
                    m: m,
                    nomenu: "1"
                });
                var features = "width=450,height=800,resizable=1,scrollbars=1";
                var win = window.open(url + "?" + params.toString(), "_blank", features);
                if (win) {
                    win.focus();
                }
            }
        }

        // SMS Zone JavaScript
        $(function () {
            $("#SMS_patient").select2({
                ajax: {
                    url: "' . $webroot . '/interface/main/messages/save.php' . '",
                    dataType: "json",
                    data: function(params) {
                        return {
                            go: "sms_search",
                            term: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item, index) {
                                return {
                                    text: item.value,
                                    id: index,
                                    value: item.Label + " " + item.mobile,
                                    pid: item.pid,
                                    mobile: item.mobile,
                                    allow: item.allow
                                };
                            })
                        };
                    }
                },
                minimumInputLength: 2,
                dropdownAutoWidth: true,
                placeholder: "' . xl('Search for patient...') . '",
                theme: "bootstrap4"
            });

            $("#SMS_patient").on("select2:select", function (e) {
                e.preventDefault();
                $("#SMS_patient").val(e.params.data.value);
                $("#sms_pid").val(e.params.data.pid);
                $("#sms_mobile").val(e.params.data.mobile);
                $("#sms_allow").val(e.params.data.allow);
            });
        });

        $(function () {
            $("#open-sms-tooltip").attr({
                "title": ' . xlj("Click to open SMS for patient") . ', 
                "data-toggle": "tooltip", 
                "data-placement": "bottom"
            }).tooltip();
        });
        ';
        
        $event->setContent($js);
    }

    /**
     * Handle CSS injection
     */
    private function handleStyles(MessagesPageRenderEvent $event): void
    {
        $css = '
        /* MedEx SMS Zone Styles */
        #sms-div {
            padding: 20px 0;
        }
        
        #smsForm .input-group-addon {
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
        
        #smsForm .input-group-addon:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        
        #open-sms-tooltip {
            cursor: help;
        }
        ';
        
        $event->setContent($css);
    }

    /**
     * Get MedEx API instance
     */
    private function getMedExAPI(): MedExAPI
    {
        return $this->medexApi;
    }

    /**
     * JavaScript escape helper
     */
    private function jsEscape($value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
