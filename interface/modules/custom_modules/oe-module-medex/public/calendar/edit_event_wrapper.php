<?php
/**
 * Wrapper for add_edit_event.php that:
 * 1. Consumes MedEx template slots when creating appointments
 * 2. Fixes duration field for proper slot handling
 * 3. Shows a modern, role-aware dialog for template category mismatches
 */

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

$eid = $_GET['eid'] ?? null;
$duration = $_GET['duration'] ?? null;
$catid = isset($_GET['catid']) ? (int)$_GET['catid'] : 0;
$provId = 0;
$rawUserId = trim((string)($_GET['userid'] ?? ''));

if ($rawUserId !== '') {
    if (ctype_digit($rawUserId)) {
        $provId = (int)$rawUserId;
    } else {
        $urow = sqlQuery("SELECT id FROM users WHERE username = ? LIMIT 1", [$rawUserId]);
        $provId = (int)($urow['id'] ?? 0);
    }
}

if ($provId <= 0 && !empty($eid)) {
    $prow = sqlQuery("SELECT pc_aid FROM openemr_postcalendar_events WHERE pc_eid = ? LIMIT 1", [(int)$eid]);
    $provId = (int)($prow['pc_aid'] ?? 0);
}

// Look up the template slot at this time to get the preferred category and duration.
// The slot is NOT deleted — it stays in the calendar permanently as Chip 1.
// Only a template re-deploy should remove or replace slots.
$slot_row = [];
$effectiveCatid = $catid;

if (empty($eid) && !empty($_GET['date']) && !empty($_GET['starttimeh']) && $provId > 0) {
    $provider_id = $provId;
    $event_date  = substr((string)$_GET['date'], 0, 4) . '-' . substr((string)$_GET['date'], 4, 2) . '-' . substr((string)$_GET['date'], 6);
    $start_hour  = (int)$_GET['starttimeh'];
    $start_min   = (int)($_GET['starttimem'] ?? 0);
    $start_time  = sprintf("%02d:%02d:00", $start_hour, $start_min);

    // Read the slot metadata (category, duration) without deleting.
    $slot_row = sqlQuery(
        "SELECT pc_eid, pc_endTime, pc_duration, pc_catid, pc_prefcatid, pc_title
         FROM openemr_postcalendar_events
         WHERE pc_aid = ? AND pc_eventDate = ?
           AND (pc_startTime = ? OR (pc_startTime < ? AND pc_endTime > ?))
           AND (COALESCE(pc_pid,'') = '' OR pc_pid = '0')
           AND (COALESCE(pc_location,'') LIKE 'MEDEX_%' OR pc_title LIKE 'Open Slot%')
         ORDER BY pc_startTime DESC LIMIT 1",
        [$provider_id, $event_date, $start_time, $start_time, $start_time]
    );

    if (!empty($slot_row['pc_prefcatid'])) {
        $effectiveCatid = (int)$slot_row['pc_prefcatid'];
    } elseif (!empty($slot_row['pc_catid'])) {
        $effectiveCatid = (int)$slot_row['pc_catid'];
    }
}

// Gather data for template-context banner
$slotCategoryId   = 0;
$slotCategoryName = '';
$isEnforcedStrict = false;
$isAdminUser      = false;

if (empty($eid)) {
    $srRow = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_scheduling_rules' LIMIT 1");
    $schedulingRules  = json_decode((string)($srRow['gl_value'] ?? ''), true);
    $isEnforcedStrict = ((string)($schedulingRules['template_enforcement'] ?? 'guideline')) === 'strict';
    $isAdminUser      = AclMain::aclCheckCore('admin', 'users') || AclMain::aclCheckCore('admin', 'super');
    if (!empty($slot_row['pc_prefcatid'] ?: ($slot_row['pc_catid'] ?? 0))) {
        $slotCategoryId = (int)($slot_row['pc_prefcatid'] ?: $slot_row['pc_catid']);
        $catNameRow     = sqlQuery(
            "SELECT pc_catname FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1",
            [$slotCategoryId]
        );
        $slotCategoryName = trim((string)($catNameRow['pc_catname'] ?? ''));
    }
}

$resolvedDuration = null;
if ($effectiveCatid > 0) {
    $crow = sqlQuery("SELECT pc_duration FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1", [$effectiveCatid]);
    $catSeconds = (int)($crow['pc_duration'] ?? 0);
    if ($catSeconds > 0) {
        $resolvedDuration = (int)round($catSeconds / 60);
    }
}
if ($resolvedDuration === null && $duration !== null) {
    $d = (int)$duration;
    if ($d > 0) {
        $resolvedDuration = $d;
    }
}

$params = $_GET;
unset($params['duration']);
unset($params['prov']);
// Pass form_category so OpenEMR pre-selects the right category at load time,
// not just via the 500ms JS injection.
if ($effectiveCatid > 0) {
    $params['form_category'] = $effectiveCatid;
}
$queryString = http_build_query($params);
$formUrl     = $GLOBALS['webroot'] . '/interface/main/calendar/add_edit_event.php?' . $queryString;

$hasBanner  = (!empty($slot_row['pc_eid']) && $slotCategoryName !== '');
?>
<!DOCTYPE html>
<html style="margin:0;padding:0;width:100%;height:100%;">
<head>
<style>
body {
    margin:0; padding:0; width:100%; height:100%;
    display:flex; flex-direction:column; overflow:hidden;
    box-sizing:border-box;
}
#medex-context-banner {
    display:flex; align-items:center; gap:8px; flex-shrink:0;
    padding:6px 12px; font-size:0.82rem; font-family:sans-serif;
    background:#eef6ff; border-bottom:1px solid #93c5fd; color:#1e3a5f;
    height:40px; box-sizing:border-box;
}
#medex-context-banner .mx-icon { font-size:1rem; flex-shrink:0; }
#medex-context-banner.mx-warn  { background:#fef9c3; border-color:#fbbf24; color:#713f12; }
#medex-context-banner.mx-block { background:#fee2e2; border-color:#f87171; color:#7f1d1d; }
#medex-context-banner.mx-ok    { background:#dcfce7; border-color:#4ade80; color:#14532d; }
#eventFrame { flex:1; min-height:0; width:100%; border:none; display:block; }
</style>
</head>
<body>

<?php if ($hasBanner): ?>
<div id="medex-context-banner">
    <span class="mx-icon">🗓️</span>
    <span id="mx-banner-msg">
        Booking into <strong><?php echo attr($slotCategoryName); ?></strong> slot
        <?php if ($isEnforcedStrict && !$isAdminUser): ?>
        &mdash; <em>strict template: select <strong><?php echo attr($slotCategoryName); ?></strong> to proceed</em>
        <?php elseif ($isEnforcedStrict && $isAdminUser): ?>
        &mdash; <em>strict template (admin override available)</em>
        <?php else: ?>
        &mdash; <em>template is a guide; you may select a different type</em>
        <?php endif; ?>
    </span>
</div>
<?php endif; ?>

<iframe id="eventFrame" src="<?php echo attr($formUrl); ?>"></iframe>

<script>
(function() {
    const SLOT_CAT_ID   = <?php echo (int)$slotCategoryId; ?>;
    const SLOT_CAT_NAME = <?php echo json_encode($slotCategoryName); ?>;
    const IS_STRICT     = <?php echo $isEnforcedStrict ? 'true' : 'false'; ?>;
    const IS_ADMIN      = <?php echo $isAdminUser ? 'true' : 'false'; ?>;
    const HAS_BANNER    = <?php echo $hasBanner ? 'true' : 'false'; ?>;
    const EFFECTIVE_CATID = <?php echo (int)$effectiveCatid; ?>;

    // Proxy dlgclose so add_edit_event.php can close this dialog.
    window.dlgclose = function() {
        try {
            if (parent && typeof parent.dlgclose === 'function') {
                parent.dlgclose();
            }
        } catch (e) { /* cross-origin guard */ }
    };

    const banner    = document.getElementById('medex-context-banner');
    const bannerMsg = document.getElementById('mx-banner-msg');

    function setBanner(icon, cls, html) {
        if (!banner) return;
        banner.className = cls ? ('mx-' + cls) : '';
        const iconEl = banner.querySelector('.mx-icon');
        if (iconEl) iconEl.textContent = icon;
        if (bannerMsg) bannerMsg.innerHTML = html;
    }

    function updateBannerForCategory(selectedCatId) {
        if (!HAS_BANNER || SLOT_CAT_ID <= 0) return;
        const selId = parseInt(selectedCatId || 0, 10);
        if (selId === SLOT_CAT_ID || selId === 0) {
            setBanner('🗓️', '', 'Booking into <strong>' + SLOT_CAT_NAME + '</strong> slot');
            return;
        }
        const opt = document.getElementById('eventFrame')
            ?.contentDocument?.querySelector('select[name="form_category"] option[value="' + selId + '"]');
        const selName = opt ? opt.textContent.trim() : ('Category #' + selId);

        if (!IS_STRICT) {
            setBanner('ℹ️', 'warn', 'Booking <strong>' + selName + '</strong> into a <strong>' + SLOT_CAT_NAME + '</strong> slot &mdash; template is a guide, proceeding is allowed');
        } else if (IS_ADMIN) {
            setBanner('⚠️', 'warn', 'Booking <strong>' + selName + '</strong> into a <strong>' + SLOT_CAT_NAME + '</strong> slot &mdash; strict template, but admin override is available');
        } else {
            setBanner('🚫', 'block', 'Cannot book <strong>' + selName + '</strong> here &mdash; strict template requires <strong>' + SLOT_CAT_NAME + '</strong>');
        }
    }

    function interceptSubmit(iframeDoc, iframeWin) {
        const form = iframeDoc.forms && iframeDoc.forms[0];
        if (!form || form.__medexHooked) return;
        form.__medexHooked = true;

        const catSel = iframeDoc.querySelector('select[name="form_category"]') || iframeDoc.querySelector('#form_category');
        if (catSel) {
            catSel.addEventListener('change', function() { updateBannerForCategory(this.value); });
            if (HAS_BANNER) updateBannerForCategory(catSel.value);
        }

        form.addEventListener('submit', function(e) {
            // Off-hours guard
            try {
                const hourSel = iframeDoc.querySelector('select[name="form_hour"]') || iframeDoc.querySelector('#form_hour');
                const ampmSel = iframeDoc.querySelector('select[name="form_ampm"]') || iframeDoc.querySelector('#form_ampm');
                if (hourSel) {
                    let h = parseInt(hourSel.value, 10) || 0;
                    const ampm = ampmSel ? (ampmSel.value || '').toUpperCase() : '';
                    if (ampm === 'AM' && h === 12) h = 0;
                    if (ampm === 'PM' && h !== 12) h += 12;
                    if (h < 6 || h >= 20) {
                        const timeStr = hourSel.value + ':00 ' + ampm;
                        const ok = iframeWin.confirm(
                            '⚠️ Off-hours appointment\n\nThe time is set to ' + timeStr +
                            ', which is outside normal business hours.\nThe appointment will not be visible in the standard calendar view.\n\nIs this intentional? Click Cancel to go back and fix the time.'
                        );
                        if (!ok) { e.preventDefault(); e.stopImmediatePropagation(); return; }
                    }
                }
            } catch (offHoursErr) { /* non-fatal */ }

            if (!HAS_BANNER || SLOT_CAT_ID <= 0) return;
            const catSel2 = iframeDoc.querySelector('select[name="form_category"]') || iframeDoc.querySelector('#form_category');
            const selectedCatId = parseInt(catSel2 ? catSel2.value : 0, 10);
            if (selectedCatId === 0 || selectedCatId === SLOT_CAT_ID) return;

            const selName = (catSel2 && catSel2.options[catSel2.selectedIndex])
                ? catSel2.options[catSel2.selectedIndex].text.trim()
                : ('Category #' + selectedCatId);

            if (IS_STRICT && !IS_ADMIN) {
                e.preventDefault(); e.stopImmediatePropagation();
                iframeWin.alert('⛔ Cannot schedule ' + selName + ' here.\n\nThis slot is strictly reserved for ' + SLOT_CAT_NAME + '.\nPlease change the appointment type or contact an administrator.');
                return;
            }
            if (IS_STRICT && IS_ADMIN) {
                const ok = iframeWin.confirm('⚠️ Template Override\n\nYou are scheduling ' + selName + ' into a ' + SLOT_CAT_NAME + ' slot.\n\nThis slot is strictly reserved for ' + SLOT_CAT_NAME + '.\nAs administrator, you can override — do you want to proceed?');
                if (!ok) { e.preventDefault(); e.stopImmediatePropagation(); }
                return;
            }
            const ok = iframeWin.confirm('Template Note\n\nYou are scheduling ' + selName + ' into a ' + SLOT_CAT_NAME + ' slot.\n\nThe template is a guide — you may proceed. Continue?');
            if (!ok) { e.preventDefault(); e.stopImmediatePropagation(); }
        }, true);
    }

    let _frameLoadCount = 0;

    document.getElementById('eventFrame').addEventListener('load', function() {
        _frameLoadCount++;
        const iframe = this;

        // Any load after the first means add_edit_event.php saved or deleted.
        // Close the outer dlgopen dialog; the calendar refresh callback handles the rest.
        if (_frameLoadCount > 1) {
            // If this was editing an existing appointment, check whether it was deleted.
            // If deleted, restore the template Open Slot from the registry so Chip 1
            // reappears in the FullCalendar. Only template re-deploys should remove slots.
            const existingEid = <?php echo (int)($eid ?? 0); ?>;
            if (existingEid > 0) {
                const restoreUrl = <?php echo json_encode(
                    ($GLOBALS['webroot'] ?? '') .
                    '/interface/modules/custom_modules/oe-module-medex/public/calendar/api/restore_slot.php'
                ); ?>;
                try {
                    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                        top.restoreSession();
                    }
                    fetch(restoreUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ appointment_eid: existingEid })
                    }).catch(function() { /* fire-and-forget — non-blocking */ });
                } catch (fetchErr) { /* non-fatal */ }
            }

            try {
                if (parent && typeof parent.dlgclose === 'function') {
                    parent.dlgclose();
                }
            } catch (e) { /* cross-origin guard */ }
            return;
        }

        try {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            const iframeWin = iframe.contentWindow;

            setTimeout(function() {
                try {
                    const eventForm = iframeDoc.forms && iframeDoc.forms[0] ? iframeDoc.forms[0] : null;
                    if (!eventForm) { return; }

                    // Suppress OpenEMR's "already used / provider not available" confirm.
                    // This fires when another event occupies the slot (e.g. a template slot that
                    // edit_event_wrapper already deleted, or an existing patient at the same time).
                    // Auto-accept it so staff can proceed without the spurious warning.
                    try {
                        const _origConfirm = iframeWin.confirm.bind(iframeWin);
                        iframeWin.confirm = function(msg) {
                            if (typeof msg === 'string' &&
                                /already\s+used|already\s+occupied|slot.*not\s+avail|time.*not\s+avail|provider.*not\s+avail|not\s+available|is\s+not\s+available/i.test(msg)) {
                                return true;
                            }
                            return _origConfirm(msg);
                        };
                    } catch (e) { /* override failed — non-fatal */ }

                    // Set the appointment category to the slot's category
                    if (EFFECTIVE_CATID > 0) {
                        const categorySelect = iframeDoc.querySelector('select[name="form_category"]') ||
                            iframeDoc.querySelector('#form_category');
                        if (categorySelect) {
                            categorySelect.value = String(EFFECTIVE_CATID);
                            if (typeof iframeWin.setbycat === 'function') {
                                iframeWin.setbycat();
                            } else {
                                categorySelect.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    }

                    <?php if ($resolvedDuration !== null): ?>
                    const durationInput = iframeDoc.querySelector('input[name="form_duration"]') ||
                        iframeDoc.querySelector('#form_duration');
                    if (durationInput) {
                        durationInput.value = '<?php echo (int)$resolvedDuration; ?>';
                        durationInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    <?php endif; ?>

                    interceptSubmit(iframeDoc, iframeWin);

                } catch (e) {
                    console.error('MedEx wrapper inner error:', e);
                }
            }, 500);
        } catch (e) {
            console.log('Cannot access iframe content (cross-origin):', e);
        }
    });
})();
</script>
</body>
</html>
