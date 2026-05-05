<?php
/**
 * Wrapper for add_edit_event.php that fixes duration field
 * This is needed because OpenEMR's core form doesn't always populate duration correctly
 */

require_once(__DIR__ . "/../../../../../globals.php");

$eid = $_GET['eid'] ?? null;
$duration = $_GET['duration'] ?? null;
$catid = isset($_GET['catid']) ? (int)$_GET['catid'] : 0;
$resolvedDuration = null;
if ($catid > 0) {
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
