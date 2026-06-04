<?php

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

$provider = trim((string)($_GET['pc_username'] ?? ''));
if ($provider !== '') {
    $medexParams['providers'] = $provider;
}

$facility = trim((string)($_GET['pc_facility'] ?? ''));
if ($facility !== '') {
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

        return parsed.pathname + parsed.search + parsed.hash;
    }

    function hideTemplateSlots(iframeDoc) {
        if (!iframeDoc || iframeDoc.getElementById('medex-hide-template-slots')) {
            return;
        }

        // CSS :has() approach — far more reliable than JS DOM walking.
        // PostCalendar wraps every event in <span class="appointment">.
        // Patient appointments contain <i class="fas fa-user"> inside.
        // Template slots have NO fa-user → hidden by the :has() rule.
        // Lunch/Vacation/Reserved are plain text (no .appointment span), unaffected.
        var style = iframeDoc.createElement('style');
        style.id = 'medex-hide-template-slots';
        style.textContent = [
            /* Hide template slot spans — no patient icon means not a real appointment */
            'span.appointment:not(:has(.fa-user)) { display: none !important; height: 0 !important; overflow: hidden !important; }',
            /* Also collapse their parent container divs so no blank space remains */
            'div:has(> span.appointment:not(:has(.fa-user))):not(:has(> span.appointment:has(.fa-user))) { display: none !important; }',
            'td:has(> div > span.appointment:not(:has(.fa-user))):not(:has(.fa-user)) { height: 0 !important; overflow: hidden !important; padding: 0 !important; }'
        ].join(' ');
        if (iframeDoc.head) { iframeDoc.head.appendChild(style); } else if (iframeDoc.body) { iframeDoc.body.insertBefore(style, iframeDoc.body.firstChild); }
        return;
        var script = iframeDoc.createElement('script');
        script.id = 'medex-hide-template-slots-DISABLED';
        script.textContent = '(function() {' +
            'var MEDEX_PATTERNS = [' +
            '  /^open\\s+slot/i,' +
            '  /^\\[ai\\]/i,' +
            '  /^in\\s+office$/i,' +
            '  /^out\\s+of\\s+office$/i' +
            '];' +
            'function isMedexTemplate(text) {' +
            '  var t = text.replace(/^\\d{1,2}:\\d{2}\\s*[-–]\\s*/,"").trim();' +
            '  return MEDEX_PATTERNS.some(function(p){ return p.test(t); });' +
            '}' +
            'function hideTemplates() {' +
            '  document.querySelectorAll("td a[href*=pc_eid]").forEach(function(a) {' +
            '    var text = a.textContent || "";' +
            '    if (isMedexTemplate(text)) {' +
            '      var cell = a.closest("td") || a.parentNode;' +
            '      if (cell) { cell.style.visibility = "hidden"; cell.style.pointerEvents = "none"; }' +
            '    }' +
            '  });' +
            '}' +
            'hideTemplates();' +
            'new MutationObserver(hideTemplates).observe(document.body,{childList:true,subtree:true});' +
            '})();';

        if (iframeDoc.body) {
            iframeDoc.body.appendChild(script);
        } else if (iframeDoc.head) {
            iframeDoc.head.appendChild(script);
        }
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
    }

    frame.addEventListener('load', refreshIframeEnhancements);
})();
</script>
</body>
</html>
