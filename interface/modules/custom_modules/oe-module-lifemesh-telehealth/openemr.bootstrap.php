<?php

/*
 *
 * @package     OpenEMR Telehealth Module
 * @link        https://lifemesh.ai/telehealth/
 *
 * @author      Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright   Copyright (c) 2021 Lifemesh Corp <telehealth@lifemesh.ai>
 * @license     GNU General Public License 3
 *
 */

require_once dirname(__FILE__) . "/controller/Container.php";
require_once dirname(__FILE__) . "/controller/AppointmentSubscriber.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Events\Appointments\AppointmentRenderEvent;
use OpenEMR\Modules\LifeMesh\Container;
use OpenEMR\Modules\LifeMesh\AppointmentSubscriber;

/**
 * @var EventDispatcherInterface $eventDispatcher
 * register subscriber to the appointment event
 */

$subscriber = new AppointmentSubscriber();
$eventDispatcher->addSubscriber($subscriber);

function oe_module_lifemesh_telehealth_render_javascript(AppointmentRenderEvent $event)
{
    $appt = $event->getAppt();
    $isSession = false;
    $isCancelled = false;
    $isNotFound = false;

    if ((!empty($appt['pc_title'])) && (stristr($appt['pc_title'], 'telehealth'))) {
        $providersession =  ((new Container())->getDatabase())->getStoredSession($appt['pc_eid']);
        if (!empty($providersession)) {
            $isSession = true;
            $isCancelled = !empty($providersession['cancelled']);
            $code = $providersession['provider_code'];
            $uri = $providersession['provider_uri'];
        } else {
            $isNotFound = true;
        }

        if ($isSession && !$isCancelled) {
            ?>
            function cancel_telehealth() {
                if (confirm(<?php echo xlj('Are you sure you want to cancel the Telehealth session?'); ?>)) {
                    document.getElementById("lifehealth-start").classList.remove("d-inline");
                    document.getElementById("lifehealth-start").classList.add("d-none");
                    document.getElementById("lifehealth-cancel").classList.remove("d-inline");
                    document.getElementById("lifehealth-cancel").classList.add("d-none");
                    document.getElementById("lifehealth-cancel-text").classList.remove("d-none");
                    document.getElementById("lifehealth-cancel-text").classList.add("d-inline");
                    let title = <?php echo xlj('Cancel Telehealth Appt'); ?>;
                    let eid = <?php echo js_escape($appt['pc_eid']); ?>;
                    dlgopen('../../modules/custom_modules/oe-module-lifemesh-telehealth/cancel_telehealth_session.php?eid=' + encodeURIComponent(eid) + '&csrf_token=' + <?php echo js_url(CsrfUtils::collectCsrfToken('lifemesh')); ?>, '', 650, 300, '', title);
                }
            }

            function startSession() {
                window.open(<?php echo js_escape($uri); ?>, '_blank', 'location=yes');
            }

            function pollPatientSignon() {
                let lifemeshDataPoll = new FormData();
                lifemeshDataPoll.append("csrf_token", <?php echo js_escape(CsrfUtils::collectCsrfToken('lifemesh')); ?>);
                lifemeshDataPoll.append("eid", <?php echo js_escape($appt['pc_eid']); ?>);
                lifemeshDataPoll.append("skip_timeout_reset", 1);
                fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-lifemesh-telehealth/account/ajaxPoll.php', {
                    credentials: 'same-origin',
                    method: 'POST',
                    body: lifemeshDataPoll
                })
                .then(response => response.json())
                .then(data => {
                    if (data.patientSignon == 'yes') {
                        if (document.getElementById("lifehealth-patient-on").classList.contains("d-none")) {
                            document.getElementById("lifehealth-patient-on").classList.remove("d-none");
                            document.getElementById("lifehealth-patient-on").classList.add("d-block");
                        }
                    } else {
                        if (document.getElementById("lifehealth-patient-on").classList.contains("d-block")) {
                            document.getElementById("lifehealth-patient-on").classList.remove("d-block");
                            document.getElementById("lifehealth-patient-on").classList.add("d-none");
                        }
                    }
                })
                .catch(error => {
                    console.error('There has been a problem with your lifemesh patient poll operation:', error);
                });
            }
            <?php
        }
    }

    ?>
    let lifemeshData = new FormData();
    lifemeshData.append("csrf_token", <?php echo js_escape(CsrfUtils::collectCsrfToken('lifemesh')); ?>);
    <?php if ($isSession && !$isCancelled) { ?>
        lifemeshData.append("eid", <?php echo js_escape($appt['pc_eid']); ?>);
    <?php } ?>
    document.addEventListener("DOMContentLoaded", function(){
        fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-lifemesh-telehealth/account/ajaxCheck.php', {
            credentials: 'same-origin',
            method: 'POST',
            body: lifemeshData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status == 'ok') {
                document.getElementById("lifemesh-logo").classList.remove("bg-info");
                document.getElementById("lifemesh-logo").classList.add("bg-success");
                document.getElementById("lifemesh-logo").title = "Lifemesh Telehealth Module status: " + jsAttr(data.statusMessage);
                document.getElementById("lifemesh-icon").classList.remove("fa-pulse");
                document.getElementById("lifemesh-icon").classList.remove("fa-spinner");
                document.getElementById("lifemesh-icon").classList.add("fa-check-square");
                <?php if ($isSession && !$isCancelled) { ?>
                    document.getElementById("lifehealth-start").classList.remove("d-none");
                    document.getElementById("lifehealth-start").classList.add("d-inline");
                    document.getElementById("lifehealth-cancel").classList.remove("d-none");
                    document.getElementById("lifehealth-cancel").classList.add("d-inline");
                    if (data.patientSignon == 'yes') {
                        document.getElementById("lifehealth-patient-on").classList.remove("d-none");
                        document.getElementById("lifehealth-patient-on").classList.add("d-block");
                    }
                    // Poll to see if patient has signed on (or signed off) every 20 seconds.
                    setInterval(pollPatientSignon, 20000);
                <?php } else if ($isSession && $isCancelled) { ?>
                    document.getElementById("lifehealth-cancel-text").classList.remove("d-none");
                    document.getElementById("lifehealth-cancel-text").classList.add("d-inline");
                <?php } else if ($isNotFound) { ?>
                    document.getElementById("lifehealth-notfound-text").classList.remove("d-none");
                    document.getElementById("lifehealth-notfound-text").classList.add("d-inline");
                <?php } ?>
            } else { // data.status == 'no'
                document.getElementById("lifemesh-logo").classList.remove("bg-info");
                document.getElementById("lifemesh-logo").classList.add("bg-danger");
                document.getElementById("lifemesh-logo").title = "Lifemesh Telehealth Module status: " + jsAttr(data.statusMessage);
                document.getElementById("lifemesh-icon").classList.remove("fa-pulse");
                document.getElementById("lifemesh-icon").classList.remove("fa-spinner");
                document.getElementById("lifemesh-icon").classList.add("fa-exclamation-triangle");
            }
        })
        .catch(error => {
            document.getElementById("lifemesh-logo").classList.remove("bg-info");
            document.getElementById("lifemesh-logo").classList.add("bg-danger");
            document.getElementById("lifemesh-logo").title = "Lifemesh Telehealth Module status: Connection Error!";
            document.getElementById("lifemesh-icon").classList.remove("fa-pulse");
            document.getElementById("lifemesh-icon").classList.remove("fa-spinner");
            document.getElementById("lifemesh-icon").classList.add("fa-exclamation-triangle");
            console.error('There has been a problem with your lifemesh fetch operation:', error);
        });
    });
    <?php
}

function oe_module_lifemesh_telehealth_render_below_patient(AppointmentRenderEvent $event)
{
    ?>
    <div>
        <style>
            .gray-background { background-color: darkgray; }
            .white {color: #ffffff; }
        </style>
        <div class="d-inline-block ml-2 mt-2">
            <div id="lifemesh-logo" class="d-inline-block bg-info" data-toggle="tooltip" data-placement="right" title="Lifemesh Telehealth Module status check is in process">
                <img src="<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-lifemesh-telehealth/account/images/lifemesh-white-wordmark-transp-271x70-1.png" style="width:135px; height:35px;">
                <i id="lifemesh-icon" class="mr-1 fa fa-spinner fa-pulse" aria-hidden="true"></i>
            </div>
            <button type="button" class="ml-4 btn btn-primary gray-background white d-none" id="lifehealth-start" onclick="startSession()"><?php echo xlt("Start Session"); ?></button>
            <button type="button" class="ml-2 btn btn-primary gray-background white d-none" id="lifehealth-cancel" onclick="cancel_telehealth()"><?php echo xlt("Cancel Telehealth"); ?></button>
            <div id="lifehealth-patient-on" class="d-none ml-4 mt-2">
                <span class="text-left"><?php echo xlt("Your patient has signed into the Telehealth session."); ?></span>
            </div>
            <span id="lifehealth-cancel-text" class="text-left ml-4 d-none"><?php echo xlt("This Telehealth session has been cancelled."); ?></span>
            <span id="lifehealth-notfound-text" class="text-left ml-4 d-none"><?php echo xlt("No Telehealth session was found for this appointment."); ?></span>
        </div>
    </div>
    <?php
}

$eventDispatcher->addListener(AppointmentRenderEvent::RENDER_JAVASCRIPT, 'oe_module_lifemesh_telehealth_render_javascript');
$eventDispatcher->addListener(AppointmentRenderEvent::RENDER_BELOW_PATIENT, 'oe_module_lifemesh_telehealth_render_below_patient');
