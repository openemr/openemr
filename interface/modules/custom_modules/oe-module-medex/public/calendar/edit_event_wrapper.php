<?php
/**
 * Wrapper for add_edit_event.php that fixes duration field
 * This is needed because OpenEMR's core form doesn't always populate duration correctly
 */

require_once(__DIR__ . "/../../../../../globals.php");

$eid = $_GET['eid'] ?? null;
$duration = $_GET['duration'] ?? null;

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

                <?php if ($duration !== null): ?>
                // Give the form a moment to initialize
                setTimeout(function() {
                    try {
                        // Try to set duration field
                        const durationInput = iframeDoc.querySelector('input[name="form_duration"]') ||
                                            iframeDoc.querySelector('#form_duration');

                        if (durationInput && (!durationInput.value || parseInt(durationInput.value) <= 0)) {
                            console.log('Setting duration to: <?php echo (int)$duration; ?> minutes');
                            durationInput.value = '<?php echo (int)$duration; ?>';

                            // Trigger change event
                            const event = new Event('change', { bubbles: true });
                            durationInput.dispatchEvent(event);
                        }
                    } catch (e) {
                        console.error('Error setting duration:', e);
                    }
                }, 500);
                <?php endif; ?>
            } catch (e) {
                // Cross-origin issues - can't access iframe content
                console.log('Cannot access iframe content (cross-origin)');
            }
        });
    </script>
</body>
</html>
