// copilotPanelGate — Per-physician demographics access gate.
// NOTE: no <?php opener — this fragment is awk-injected mid-file into
// demographics.php, which is already inside an open PHP block.
// Even if a clinician URL-pokes a patient ID outside their panel
// (bypassing the patient-finder restriction), this aborts the chart
// render with a 403. 'admin' bypasses. When the patient has no
// providerID assigned (legacy data), we fall through unchanged so the
// stock behavior is preserved.
$copilotPanelGateUser = $_SESSION['authUser'] ?? '';
$copilotPanelGatePid = $_GET['set_pid']
    ?? $_GET['pid']
    ?? $_SESSION['pid']
    ?? null;
if (
    $copilotPanelGateUser !== ''
    && $copilotPanelGateUser !== 'admin'
    && !empty($copilotPanelGatePid)
) {
    $copilotPanelGateUserRow = sqlQuery(
        "SELECT id FROM users WHERE username = ?",
        [$copilotPanelGateUser]
    );
    $copilotPanelGatePatRow = sqlQuery(
        "SELECT providerID FROM patient_data WHERE pid = ?",
        [intval($copilotPanelGatePid)]
    );
    if (
        !empty($copilotPanelGateUserRow['id'])
        && !empty($copilotPanelGatePatRow)
        && !empty($copilotPanelGatePatRow['providerID'])
        && intval($copilotPanelGatePatRow['providerID'])
            !== intval($copilotPanelGateUserRow['id'])
    ) {
        http_response_code(403);
        echo '<!doctype html><html><head><meta charset="utf-8">'
            . '<title>Patient not in your panel</title>'
            . '<style>body{font-family:-apple-system,system-ui,sans-serif;'
            . 'background:#0e1116;color:#e6edf3;padding:60px;text-align:center;}'
            . 'h2{color:#f85149;}a{color:#58a6ff;}</style></head><body>'
            . '<h2>Patient not in your panel</h2>'
            . '<p>This patient is assigned to a different physician. '
            . 'Contact the practice admin to request a transfer.</p>'
            . '<p><a href="/interface/main/main_screen.php?auth=login&site=default">'
            . 'Back to dashboard</a></p>'
            . '</body></html>';
        exit;
    }
}
