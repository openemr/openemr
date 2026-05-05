<?php
/**
 * Wrapper for add_edit_event.php that:
 * 1. Consumes MedEx template slots when creating appointments
 * 2. Fixes duration field for proper slot handling
 */

require_once(__DIR__ . "/../../../../../globals.php");

$eid = $_GET['eid'] ?? null;
$duration = $_GET['duration'] ?? null;
$catid = isset($_GET['catid']) ? (int)$_GET['catid'] : 0;

// Template slot consumption logic for new appointments
$consumedSlotEid = null;
$slotConsumed = false;

if (empty($eid) && !empty($_GET['date']) && !empty($_GET['starttimeh']) && !empty($_GET['userid'])) {
    // This is a new appointment being created in a template slot
    $provider_id = (int)$_GET['userid'];
    $event_date = substr((string)$_GET['date'], 0, 4) . '-' . substr((string)$_GET['date'], 4, 2) . '-' . substr((string)$_GET['date'], 6);
    $start_hour = (int)$_GET['starttimeh'];
    $start_min = (int)($_GET['starttimem'] ?? 0);
    $start_time = sprintf("%02d:%02d:00", $start_hour, $start_min);

    // Find matching MedEx template slot
    $slot_sql = "SELECT pc_eid, pc_endTime, pc_duration, pc_catid, pc_title
                 FROM openemr_postcalendar_events
                 WHERE pc_aid = ?
                   AND pc_eventDate = ?
                   AND pc_startTime = ?
                   AND (COALESCE(pc_pid,'') = '' OR pc_pid = '0')
                   AND pc_apptstatus = '-'
                   AND pc_title LIKE 'Open Slot%'
                 ORDER BY pc_eid DESC
                 LIMIT 1";
    $slot_row = sqlQuery($slot_sql, [$provider_id, $event_date, $start_time]);

    if (!empty($slot_row['pc_eid'])) {
        $consumedSlotEid = (int)$slot_row['pc_eid'];

        // Delete the template slot event (it will be replaced by the patient appointment)
        sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", [$consumedSlotEid]);

        // Ensure slot registry table exists
        $tableExists = sqlQuery("SHOW TABLES LIKE 'medex_slot_registry'");
        if (!empty($tableExists)) {
            // Check if there's already a slot record for this template slot
            $existing = sqlQuery(
                "SELECT slot_id FROM medex_slot_registry WHERE open_slot_eid = ?",
                [$consumedSlotEid]
            );

            if (empty($existing['slot_id'])) {
                // Create a pending slot registry entry - will be linked to patient appointment after save
                sqlStatement(
                    "INSERT INTO medex_slot_registry
                     (open_slot_eid, patient_pc_eid, provider_id, event_date, start_time, end_time,
                      category_id, slot_state, slot_source, reschedulable, consumed_at, created_at)
                     VALUES (?, NULL, ?, ?, ?, ?, ?, 'pending_consumption', 'medex', 1, NOW(), NOW())",
                    [
                        $consumedSlotEid,
                        $provider_id,
                        $event_date,
                        $start_time,
                        $slot_row['pc_endTime'],
                        (int)$slot_row['pc_catid']
                    ]
                );
            }
        }

        $slotConsumed = true;

        // Calculate slot duration from pc_duration (in seconds) to minutes
        $slotDurationMinutes = null;
        if (!empty($slot_row['pc_duration']) && (int)$slot_row['pc_duration'] > 0) {
            $slotDurationMinutes = (int)round((int)$slot_row['pc_duration'] / 60);
        }

        // Store in session for linking after appointment is saved
        $_SESSION['medex_pending_slot_consumption'] = [
            'open_slot_eid' => $consumedSlotEid,
            'provider_id' => $provider_id,
            'event_date' => $event_date,
            'start_time' => $start_time,
            'category_id' => (int)$slot_row['pc_catid'],
            'slot_duration' => $slotDurationMinutes, // Store for use when setting duration
            'timestamp' => time()
        ];
    }
}

$resolvedDuration = null;

// If we consumed a slot, use its duration first
if ($slotConsumed && !empty($_SESSION['medex_pending_slot_consumption']['slot_duration'])) {
    $resolvedDuration = $_SESSION['medex_pending_slot_consumption']['slot_duration'];
}

// Fall back to category duration
if ($resolvedDuration === null && $catid > 0) {
    $crow = sqlQuery("SELECT pc_duration FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1", [$catid]);
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

// Build URL to actual OpenEMR form
$params = $_GET;
unset($params['duration']); // Remove our custom parameter
$queryString = http_build_query($params);
$formUrl = $GLOBALS['webroot'] . '/interface/main/calendar/add_edit_event.php?' . $queryString;
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <iframe id="eventFrame" src="<?php echo attr($formUrl); ?>"></iframe>
    <script>
        // Wait for iframe to load, then inject duration if needed
        document.getElementById('eventFrame').addEventListener('load', function() {
            try {
                const iframe = this;
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                // Give the form a moment to initialize
                setTimeout(function() {
                    try {
                        const eventForm = iframeDoc.forms && iframeDoc.forms[0] ? iframeDoc.forms[0] : null;
                        if (!eventForm) {
                            return;
                        }

                        <?php if ($catid > 0): ?>
                        const categorySelect = iframeDoc.querySelector('select[name="form_category"]') ||
                            iframeDoc.querySelector('#form_category');
                        if (categorySelect) {
                            categorySelect.value = '<?php echo (int)$catid; ?>';
                            if (typeof iframe.contentWindow.setbycat === 'function') {
                                iframe.contentWindow.setbycat();
                            } else {
                                categorySelect.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                        <?php endif; ?>

                        <?php if ($resolvedDuration !== null): ?>
                        const durationInput = iframeDoc.querySelector('input[name="form_duration"]') ||
                            iframeDoc.querySelector('#form_duration');
                        if (durationInput) {
                            console.log('Setting duration to: <?php echo (int)$resolvedDuration; ?> minutes');
                            durationInput.value = '<?php echo (int)$resolvedDuration; ?>';
                            durationInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        <?php endif; ?>
                    } catch (e) {
                        console.error('Error setting duration:', e);
                    }
                }, 500);
            } catch (e) {
                // Cross-origin issues - can't access iframe content
                console.log('Cannot access iframe content (cross-origin)');
            }
        });
    </script>
</body>
</html>
