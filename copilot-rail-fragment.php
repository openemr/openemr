<?php
// Clinical Co-Pilot iframe rail — injected by Railway build (see Dockerfile).
// Looks up the patient's FHIR UUID and the logged-in clinician's username
// and passes both via query string so the agent session opens scoped to
// the right physician AND the right patient. Compatible with the stock
// openemr/openemr:latest PHP — uses only sqlQuery() + UuidRegistry +
// $_SESSION['authUser'], all of which exist in upstream.
$copilotAgentUrl = 'https://copilot-production-b532.up.railway.app';
$copilotPatientUuid = '';
$copilotPhysicianUser = $_SESSION['authUser'] ?? '';
if (!empty($pid)) {
    $copilotRow = sqlQuery(
        "SELECT uuid FROM patient_data WHERE pid = ?",
        [$pid]
    );
    if (!empty($copilotRow['uuid'])) {
        $copilotPatientUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($copilotRow['uuid']);
    }
}
if (!empty($copilotPatientUuid)) :
    $copilotIframeSrc = $copilotAgentUrl
        . '/?patient_id=' . urlencode($copilotPatientUuid)
        . '&physician_user_id=' . urlencode($copilotPhysicianUser);
    ?>
    <style>
        body { transition: padding-right 0.2s ease; padding-right: 36px !important; }
        body.copilot-open { padding-right: 400px !important; }
        #copilot-toggle {
            position: fixed; top: 50%; right: 0; transform: translateY(-50%);
            z-index: 10000;
            background: #58a6ff; color: #0d1117;
            width: 36px; min-height: 110px;
            border: 0; border-top-left-radius: 8px; border-bottom-left-radius: 8px;
            cursor: pointer; font-weight: 600; font-size: 12px;
            writing-mode: vertical-rl; text-orientation: mixed;
            padding: 12px 4px;
            box-shadow: -2px 0 6px rgba(0,0,0,0.25);
            transition: right 0.2s ease;
        }
        body.copilot-open #copilot-toggle { right: 400px; }
        #copilot-rail {
            position: fixed; top: 0; right: 0;
            width: 400px; height: 100vh;
            border: 0; border-left: 1px solid #30363d;
            z-index: 9999; background: #0e1116;
            transform: translateX(100%);
            transition: transform 0.2s ease;
        }
        body.copilot-open #copilot-rail { transform: translateX(0); }
    </style>
    <button id="copilot-toggle" type="button"
            onclick="document.body.classList.toggle('copilot-open')"
            title="Toggle Clinical Co-Pilot">Co-Pilot &#9656;</button>
    <iframe id="copilot-rail"
            src="<?php echo attr($copilotIframeSrc); ?>"
            title="Clinical Co-Pilot"></iframe>
<?php endif; ?>
