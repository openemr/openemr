<?php

/**
 * Module-Only SMS Zone Implementation
 *
 * This file provides a complete SMS Zone implementation that can be
 * included in messages.php without modifying core files.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Only proceed if MedEx module is enabled and user is logged in
if ($GLOBALS['medex_enable'] == '1' && $logged_in) {
    
    // SMS Zone Tab Injection
    function injectSMSTab() {
        echo '
                    <li class="nav-item" id="li-sms" role="presentation">
                        <a href="#sms-div" id="sms-li" class="nav-link" data-toggle="pill" role="tab" aria-controls="SMS Zone" aria-selected="true">' . xlt('SMS Zone') . '</a>
                    </li>
        ';
    }
    
    // SMS Zone Content Injection
    function injectSMSZoneContent() {
        echo '
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
    }
    
    // SMS Zone JavaScript Injection
    function injectSMSZoneScripts() {
        $webroot = $GLOBALS['webroot'] ?? '/openemr';
        
        echo '
        <script>
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
                    url: "' . $webroot . '/interface/modules/custom_modules/oe-module-medex/public/save.php' . '",
                    dataType: "json",
                    data: function(params) {
                        return {
                            go: "sms_search",
                            term: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.items
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
                "title": "' . xlj("Click to open SMS for patient") . '", 
                "data-toggle": "tooltip", 
                "data-placement": "bottom"
            }).tooltip();
        });
        </script>
        ';
    }
    
    // SMS Zone CSS Injection
    function injectSMSZoneStyles() {
        echo '
        <style>
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
        </style>
        ';
    }
    
    // Check if we're in the right context and inject
    if (!empty($_REQUEST['go'])) {
        switch ($_REQUEST['go']) {
            case 'SMS_bot':
                // Handle SMS_bot redirect to module
                $webroot = $GLOBALS['webroot'] ?? '/openemr';
                $redirectUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php';
                
                // Pass request parameters
                $params = $_REQUEST;
                unset($params['go']);
                if (!empty($params)) {
                    $redirectUrl .= '?' . http_build_query($params);
                }
                
                header('Location: ' . $redirectUrl);
                exit;
                break;
        }
    }
}
