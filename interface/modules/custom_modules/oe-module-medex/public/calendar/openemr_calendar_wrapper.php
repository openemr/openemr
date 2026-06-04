<?php
/**
 * PROPRIETARY AND CONFIDENTIAL
 * Copyright (c) 2024-2026 MedEx <support@MedExBank.com>
 * All Rights Reserved.
 *
 * This file is part of the MedEx SaaS platform and is NOT open-source software.
 * Unauthorized copying, distribution, modification, or use of this file, via any
 * medium, is strictly prohibited without the express written permission of MedEx.
 *
 * @package   MedEx
 * @copyright Copyright (c) 2024-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    exit('Access denied');
}

$webroot = (string)($GLOBALS['webroot'] ?? '');
$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));

$nativeParams = [
    'module' => trim((string)($_GET['module'] ?? '')) ?: 'PostCalendar',
    'func' => trim((string)($_GET['func'] ?? '')) ?: 'view',
    'viewtype' => trim((string)($_GET['viewtype'] ?? '')) ?: 'week',
    'site' => $siteId,
    'medex_wrapper' => '1',
    'medex_prefer' => 'openemr',
];

foreach (['Date', 'jumpdate', 'pc_username', 'pc_facility'] as $key) {
    $value = trim((string)($_GET[$key] ?? ''));
    if ($value !== '') {
        $nativeParams[$key] = $value;
    }
}

$nativeCalendarUrl = $webroot . '/interface/main/calendar/index.php?' . http_build_query($nativeParams);

$medexParams = ['site' => $siteId];
$jumpDate = trim((string)($_GET['jumpdate'] ?? ''));
$compactDate = trim((string)($_GET['Date'] ?? ''));
if ($jumpDate !== '') {
    $medexParams['date'] = $jumpDate;
} elseif (preg_match('/^\d{8}$/', $compactDate) === 1) {
    $medexParams['date'] = substr($compactDate, 0, 4) . '-' . substr($compactDate, 4, 2) . '-' . substr($compactDate, 6, 2);
}

$viewType = strtolower(trim((string)($_GET['viewtype'] ?? 'week')));
if ($viewType === 'month') {
    $medexParams['view'] = 'month';
} elseif ($viewType === 'day') {
    $medexParams['view'] = 'day';
} else {
    $medexParams['view'] = 'week';
}

// Restore all originally-selected providers when returning to FullCalendar
$medexProviders = trim((string)($_GET['medex_providers'] ?? ''));
$provider       = trim((string)($_GET['pc_username']    ?? ''));
if ($medexProviders !== '') {
    $medexParams['providers'] = $medexProviders; // all comma-separated usernames
} elseif ($provider !== '') {
    $medexParams['providers'] = $provider;
}

$medexFacilities = trim((string)($_GET['medex_facilities'] ?? ''));
$facility        = trim((string)($_GET['pc_facility']      ?? ''));
if ($medexFacilities !== '') {
    $medexParams['facilities'] = $medexFacilities;
} elseif ($facility !== '') {
    $medexParams['facilities'] = $facility;
}

$medexRedirectUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/public/calendar/index.php?' . http_build_query($medexParams);
$preferenceUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/public/calendar/set_calendar_preference.php?' . http_build_query([
    'site' => $siteId,
    'preference' => 'medex',
    'redirect' => $medexRedirectUrl,
]);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo xlt('OpenEMR Calendar'); ?></title>
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #ffffff;
        }

        #calendar-frame {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }
    </style>
</head>
<body>
<iframe
    id="calendar-frame"
    title="<?php echo attr(xl('OpenEMR Calendar')); ?>"
    src="<?php echo attr($nativeCalendarUrl); ?>"
></iframe>
<script>
(function () {
    var medexPreferenceUrl = <?php echo json_encode($preferenceUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    // All providers/facilities selected in FullCalendar — use medex_providers for full list
    var SAVED_PC_USERNAME = <?php
        $allProviders = trim((string)($_GET['medex_providers'] ?? '')) ?: trim((string)($_GET['pc_username'] ?? ''));
        echo json_encode($allProviders);
    ?>;
    var SAVED_PC_FACILITY = <?php
        $allFacilities = trim((string)($_GET['medex_facilities'] ?? '')) ?: trim((string)($_GET['pc_facility'] ?? ''));
        echo json_encode($allFacilities);
    ?>;
    var frame = document.getElementById('calendar-frame');

    function restoreSessionThen(run) {
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
            top.restoreSession();
            window.setTimeout(run, 100);
            return;
        }

        run();
    }

    function normalizeCalendarUrl(rawUrl, baseUrl) {
        var parsed;
        try {
            parsed = new URL(rawUrl, baseUrl || window.location.href);
        } catch (error) {
            return rawUrl;
        }

        if (parsed.pathname.indexOf('/interface/main/calendar/') === -1) {
            return rawUrl;
        }

        parsed.searchParams.set('medex_wrapper', '1');
        parsed.searchParams.set('medex_prefer', 'openemr');
        // Preserve provider and facility so navigation within native calendar keeps the same filter
        if (SAVED_PC_USERNAME) { parsed.searchParams.set('pc_username', SAVED_PC_USERNAME); }
        if (SAVED_PC_FACILITY) { parsed.searchParams.set('pc_facility', SAVED_PC_FACILITY); }

        return parsed.pathname + parsed.search + parsed.hash;
    }

    function hideTemplateSlots(iframeDoc) {
        if (!iframeDoc || iframeDoc.getElementById('medex-hide-template-slots')) {
            return;
        }

        // PostCalendar gives every event a div with id="event_NNN".
        // Patient appointments have .fa-user inside; template slots do not.
        // JS hides the entire container div so no blank colored rows remain.
        // CSS hides the appointment span text as a fast first pass.
        var style = iframeDoc.createElement('style');
        style.id = 'medex-hide-template-slots';
        style.textContent = 'span.appointment:not(:has(.fa-user)){display:none!important}';
        (iframeDoc.head || iframeDoc.body).appendChild(style);

        // PostCalendar pre-calculates left/width % for ALL events per time slot.
        // Hiding template events leaves gaps because surviving patient appointments
        // keep their original positions. After hiding, redistribute widths so
        // remaining visible patient appointments fill the full column width.
        var script = iframeDoc.createElement('script');
        script.textContent = '(function(){' +
            'function run(){' +
            'document.querySelectorAll("div[data-eid]").forEach(function(div){' +
            'if(!div.querySelector(".fa-user")){' +
            'div.style.display="none";div.style.height="0";' +
            'div.style.overflow="hidden";div.style.minHeight="0";}' +
            '});' +
            'document.querySelectorAll(".calendar_day").forEach(function(col){' +
            'var visible=Array.from(col.querySelectorAll("div[data-eid]")).filter(function(d){return d.style.display!=="none";});' +
            'var byTop={};' +
            'visible.forEach(function(d){var top=Math.round(parseFloat(d.style.top)||0);if(!byTop[top])byTop[top]=[];byTop[top].push(d);});' +
            'Object.values(byTop).forEach(function(group){' +
            'if(group.length<2)return;' +
            'var w=100/group.length;' +
            'group.forEach(function(d,i){d.style.width=w+"%";d.style.left=(i*w)+"%";});' +
            '});' +
            '});' +
            '}' +
            'run();' +
            'new MutationObserver(function(){run();}).observe(document.body,{childList:true,subtree:true});' +
            '})()';
        (iframeDoc.body || iframeDoc.head).appendChild(script);
    }

    function injectContainmentScript(iframeDoc) {
        if (!iframeDoc || !iframeDoc.head || iframeDoc.getElementById('medex-wrapper-containment')) {
            return;
        }

        var script = iframeDoc.createElement('script');
        script.id = 'medex-wrapper-containment';
        script.textContent = "(" + function () {
            var realTop = window.top;
            var realRestoreSession = realTop && typeof realTop.restoreSession === 'function'
                ? realTop.restoreSession.bind(realTop)
                : null;

            window.restoreSession = function () {
                if (realRestoreSession) {
                    realRestoreSession();
                }

                return true;
            };
        }.toString() + ")();";

        iframeDoc.head.insertBefore(script, iframeDoc.head.firstChild);
    }

    function normalizeCalendarNavigation(iframeDoc) {
        if (!iframeDoc) {
            return;
        }

        var forms = iframeDoc.querySelectorAll('form');
        forms.forEach(function (form) {
            var wrapperField = form.querySelector('input[name=\"medex_wrapper\"]');
            if (!wrapperField) {
                wrapperField = iframeDoc.createElement('input');
                wrapperField.type = 'hidden';
                wrapperField.name = 'medex_wrapper';
                form.appendChild(wrapperField);
            }
            wrapperField.value = '1';

            var preferField = form.querySelector('input[name=\"medex_prefer\"]');
            if (!preferField) {
                preferField = iframeDoc.createElement('input');
                preferField.type = 'hidden';
                preferField.name = 'medex_prefer';
                form.appendChild(preferField);
            }
            preferField.value = 'openemr';

            form.removeAttribute('target');

            if (form.getAttribute('action')) {
                form.setAttribute('action', normalizeCalendarUrl(form.getAttribute('action'), iframeDoc.location.href));
            }
        });

        var links = iframeDoc.querySelectorAll('a[href]');
        links.forEach(function (link) {
            link.setAttribute('href', normalizeCalendarUrl(link.getAttribute('href'), iframeDoc.location.href));
            // Ensure session is restored before any calendar navigation to prevent session errors
            if (!link.getAttribute('onclick') || link.getAttribute('onclick').indexOf('restoreSession') === -1) {
                link.setAttribute('onclick', 'if(typeof top!=="undefined"&&typeof top.restoreSession==="function")top.restoreSession();');
            }
        });
    }

    function buildSwitcher(iframeDoc) {
        if (!iframeDoc || !iframeDoc.head) {
            return;
        }

        var existing = iframeDoc.getElementById('medex-native-calendar-switcher');
        if (existing) {
            existing.remove();
        }

        if (!iframeDoc.getElementById('medex-native-calendar-switcher-style')) {
            var style = iframeDoc.createElement('style');
            style.id = 'medex-native-calendar-switcher-style';
            style.textContent = ''
                + '#medex-native-calendar-switcher{margin:12px auto 14px auto;padding:0;box-sizing:border-box;max-width:180px;width:100%;}'
                + '#medex-native-calendar-switcher-label{font-size:10px;color:#666;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;}'
                + '#medex-native-calendar-switcher .view-selector{display:flex;flex-direction:column;gap:1px;border:1px solid #0099cc;border-radius:3px;overflow:hidden;background:#fff;}'
                + '#medex-native-calendar-switcher .view-option{padding:8px;font-size:11px;border:none;text-align:left;transition:background .2s;}'
                + '#medex-native-calendar-switcher .view-option.active{background:#0099cc;color:#fff;cursor:default;font-weight:500;}'
                + '#medex-native-calendar-switcher .view-option.link{background:#fff;color:#0099cc;cursor:pointer;}'
                + '#medex-native-calendar-switcher .view-option.link:hover{background:#e8f4f8 !important;}';
            iframeDoc.head.appendChild(style);
        }

        var sidebar = iframeDoc.getElementById('bottomLeft');
        if (!sidebar) {
            return;
        }

        var host = iframeDoc.createElement('div');
        host.id = 'medex-native-calendar-switcher';

        var label = iframeDoc.createElement('div');
        label.id = 'medex-native-calendar-switcher-label';
        label.textContent = 'Calendar View';

        var selector = iframeDoc.createElement('div');
        selector.className = 'view-selector';

        var medexButton = iframeDoc.createElement('button');
        medexButton.type = 'button';
        medexButton.className = 'view-option link';
        medexButton.textContent = 'Full Calendar';
        medexButton.addEventListener('click', function (event) {
            event.preventDefault();
            restoreSessionThen(function () {
                window.location.href = medexPreferenceUrl;
            });
        });

        var nativeButton = iframeDoc.createElement('button');
        nativeButton.type = 'button';
        nativeButton.className = 'view-option active';
        nativeButton.textContent = 'OpenEMR Calendar';
        nativeButton.disabled = true;

        selector.appendChild(medexButton);
        selector.appendChild(nativeButton);
        host.appendChild(label);
        host.appendChild(selector);

        sidebar.insertBefore(host, sidebar.firstChild);
    }

    function refreshIframeEnhancements() {
        var iframeDoc = frame.contentDocument || (frame.contentWindow ? frame.contentWindow.document : null);
        if (!iframeDoc) {
            return;
        }

        injectContainmentScript(iframeDoc);
        normalizeCalendarNavigation(iframeDoc);
        buildSwitcher(iframeDoc);
        hideTemplateSlots(iframeDoc);
        syncNativeSelections(iframeDoc);
    }

    // Sync the native calendar's facility dropdown and provider multi-select
    // to match what was selected in FullCalendar before switching.
    function syncNativeSelections(iframeDoc) {
        if (!iframeDoc) { return; }

        // Facility: select#pc_facility
        if (SAVED_PC_FACILITY) {
            var facSel = iframeDoc.getElementById('pc_facility');
            if (facSel && facSel.value !== SAVED_PC_FACILITY) {
                facSel.value = SAVED_PC_FACILITY;
            }
        }

        // Providers: select#pc_username (multi-select)
        // Set the matching options as selected so the sidebar reflects the right providers.
        var provSel = iframeDoc.getElementById('pc_username');
        if (provSel && SAVED_PC_USERNAME) {
            var savedList = SAVED_PC_USERNAME.split(',').map(function(s){ return s.trim(); });
            Array.from(provSel.options).forEach(function(opt) {
                opt.selected = savedList.indexOf(opt.value) !== -1;
            });
        }
    }

    frame.addEventListener('load', refreshIframeEnhancements);
})();
</script>
</body>
</html>
