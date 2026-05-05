<?php
/**
 * MedEx installer bridge.
 * If any path still lands here, do the work immediately and move into onboarding.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . '/../../../globals.php');
require_once(__DIR__ . '/src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\MedEx\MedExAPI;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

function medexSetupStatus(): array
{
    $row = QueryUtils::querySingleRow(
        "SELECT mod_id, mod_active, mod_ui_active, sql_run
         FROM modules
         WHERE mod_directory = 'oe-module-medex'
         ORDER BY mod_id DESC
         LIMIT 1",
        []
    ) ?: [];

    $sqlRun = (int) ($row['sql_run'] ?? 0);
    $modId = (int) ($row['mod_id'] ?? 0);
    $modActive = (int) ($row['mod_active'] ?? 0);
    $modUiActive = (int) ($row['mod_ui_active'] ?? 0);

    $installed = ($sqlRun === 1);
    $enabled = ($modActive === 1 || $modUiActive === 1);
    $configured = false;
    $active = false;

    try {
        $api = new MedExAPI();
        $configured = $api->isConfigured();
        $active = $api->isActive();
    } catch (\Throwable $e) {
    }

    return [
        'mod_id' => $modId,
        'installed' => $installed,
        'enabled' => $enabled,
        'configured' => $configured,
        'active' => $active,
        'dashboard_ready' => $configured,
        'next_action' => $configured ? 'dashboard' : (!$installed ? 'install' : (!$enabled ? 'enable' : 'configure')),
    ];
}

if (($_GET['action'] ?? '') === 'status') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'status' => medexSetupStatus()]);
    exit;
}

$status = medexSetupStatus();
$siteId = (string) ($_GET['site'] ?? 'default');
$webroot = (string) ($GLOBALS['webroot'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Starting MedEx</title>
    <style>
        :root {
            --bg1: #f8fbff;
            --bg2: #eaf3ff;
            --ink: #0f172a;
            --muted: #475569;
            --line: #bfdbfe;
            --brand: #1d4ed8;
            --brand2: #0ea5e9;
            --ok: #15803d;
            --err: #b91c1c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            background: radial-gradient(circle at top, #ffffff 0%, var(--bg1) 45%, var(--bg2) 100%);
            color: var(--ink);
        }
        .shell {
            width: min(640px, 100%);
            background: rgba(255,255,255,.92);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(15, 75, 143, 0.12);
            padding: 28px 26px 24px;
        }
        .eyebrow {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--brand);
            margin-bottom: 10px;
        }
        h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.15;
            color: #0f3f75;
        }
        p {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.6;
        }
        .progress {
            margin-top: 20px;
            height: 14px;
            border-radius: 999px;
            overflow: hidden;
            background: #dbeafe;
            border: 1px solid #c7ddff;
        }
        .progress-bar {
            width: 10%;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--brand) 0%, var(--brand2) 55%, #38bdf8 100%);
            transition: width .9s ease;
        }
        .status {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 18px;
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
        }
        .status::before {
            content: "";
            width: 20px;
            height: 20px;
            border-radius: 999px;
            border: 3px solid #93c5fd;
            border-top-color: var(--brand);
            animation: spin .8s linear infinite;
            flex: 0 0 auto;
        }
        .status.done::before {
            animation: none;
            border-color: var(--ok);
            background: var(--ok);
            box-shadow: inset 0 0 0 4px #dcfce7;
        }
        .status.error::before {
            animation: none;
            border-color: var(--err);
            background: var(--err);
            box-shadow: inset 0 0 0 4px #fee2e2;
        }
        .notes {
            margin-top: 8px;
            font-size: 14px;
            color: var(--muted);
            min-height: 22px;
        }
        .retry {
            display: none;
            margin-top: 18px;
            border: 1px solid var(--brand);
            background: #eff6ff;
            color: var(--brand);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }
        .retry.show { display: inline-flex; }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="shell">
    <div class="eyebrow">MedEx</div>
    <h1>Starting onboarding</h1>
    <p>MedEx is finishing installation in the background. Onboarding will open automatically.</p>
    <div class="progress"><div class="progress-bar" id="medex-progress-bar"></div></div>
    <div class="status" id="medex-status">Preparing MedEx...</div>
    <div class="notes" id="medex-notes">Please wait while MedEx installs, enables, and moves into onboarding.</div>
    <button type="button" class="retry" id="retry-btn">Try again</button>
</div>
<script>
    const currentStatus = <?php echo json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const setupSiteId = <?php echo json_encode($siteId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const setupStatusUrl = <?php echo json_encode($webroot . '/interface/modules/custom_modules/oe-module-medex/show_help_setup.php', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const onboardingUrl = <?php echo json_encode($webroot . '/interface/modules/custom_modules/oe-module-medex/admin/onboarding.php?step=1&force_onboarding=1&site=' . urlencode($siteId), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const dashboardUrl = <?php echo json_encode($webroot . '/interface/modules/custom_modules/oe-module-medex/admin/index.php?site=' . urlencode($siteId), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const retryBtn = document.getElementById('retry-btn');
    const progressBar = document.getElementById('medex-progress-bar');
    const statusEl = document.getElementById('medex-status');
    const notesEl = document.getElementById('medex-notes');
    let running = false;

    function setStatus(text, notes, state, percent) {
        statusEl.textContent = text;
        statusEl.classList.remove('done', 'error');
        if (state === 'done' || state === 'error') {
            statusEl.classList.add(state);
        }
        notesEl.textContent = notes || '';
        if (typeof percent === 'number' && progressBar) {
            progressBar.style.width = Math.max(8, Math.min(100, percent)) + '%';
        }
        retryBtn.classList.toggle('show', state === 'error');
    }

    function keepSessionAlive() {
        try {
            if (window.top && typeof window.top.restoreSession === 'function') {
                window.top.restoreSession();
                return;
            }
        } catch (e) {}
        try {
            if (window.parent && typeof window.parent.restoreSession === 'function') {
                window.parent.restoreSession();
            }
        } catch (e) {}
    }

    function getModuleId() {
        const qsId = parseInt(new URLSearchParams(window.location.search).get('mod_id') || '0', 10);
        if (qsId > 0) {
            return qsId;
        }
        const statusId = parseInt(currentStatus && currentStatus.mod_id ? currentStatus.mod_id : 0, 10);
        return statusId > 0 ? statusId : 0;
    }

    async function fetchStatus() {
        const url = setupStatusUrl + '?action=status&site=' + encodeURIComponent(setupSiteId || 'default');
        const response = await fetch(url, { cache: 'no-store', credentials: 'same-origin' });
        if (!response.ok) {
            throw new Error('Unable to read MedEx module status.');
        }
        const payload = await response.json();
        if (!payload || !payload.success || !payload.status) {
            throw new Error('Invalid MedEx status response.');
        }
        return payload.status;
    }

    async function runManageAction(action) {
        const moduleId = getModuleId();
        if (!moduleId) {
            throw new Error('MedEx module ID is unavailable.');
        }
        keepSessionAlive();
        const body = new URLSearchParams({
            modId: String(moduleId),
            modAction: action,
            mod_enc_menu: '',
            mod_nick_name: ''
        });
        const response = await fetch('../../zend_modules/public/Installer/manage?site=' + encodeURIComponent(setupSiteId || 'default'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        });
        if (!response.ok) {
            throw new Error('MedEx ' + action + ' request failed.');
        }
        const raw = await response.text();
        let parsed = null;
        try {
            parsed = JSON.parse(raw);
        } catch (e) {}
        if (parsed && String(parsed.status || '').toUpperCase() !== 'SUCCESS') {
            throw new Error(parsed.status || ('MedEx ' + action + ' failed.'));
        }
    }

    async function waitFor(checkFn, attempts, delayMs) {
        for (let i = 0; i < attempts; i++) {
            const status = await fetchStatus();
            if (checkFn(status)) {
                return status;
            }
            await new Promise((resolve) => window.setTimeout(resolve, delayMs));
        }
        throw new Error('Timed out waiting for MedEx to finish setup.');
    }

    function redirectWithinMed(url, navId) {
        keepSessionAlive();
        try {
            if (window.top && typeof window.top.navigateTab === 'function') {
                window.top.navigateTab(url, 'med', function () {
                    try {
                        if (typeof window.top.activateTabByName === 'function') {
                            window.top.activateTabByName('med', true);
                        }
                    } catch (e) {}
                });
                return;
            }
        } catch (e) {}
        try {
            if (window.top && window.top.frames && window.top.frames.med) {
                window.top.frames.med.location.href = url;
                try {
                    if (typeof window.top.activateTabByName === 'function') {
                        window.top.activateTabByName('med', true);
                    }
                } catch (e) {}
                return;
            }
        } catch (e) {}
        window.location.href = url;
    }

    function redirectToOnboarding() {
        redirectWithinMed(onboardingUrl, 'medex_onboarding');
    }

    function redirectToDashboard() {
        redirectWithinMed(dashboardUrl, 'medex_admin');
    }

    async function runFlow() {
        if (running) {
            return;
        }
        running = true;
        retryBtn.classList.remove('show');
        try {
            let status = await fetchStatus();
            if (status.dashboard_ready) {
                setStatus('Opening MedEx Dashboard...', 'This practice is already onboarded. Loading the dashboard now.', 'done', 100);
                window.setTimeout(redirectToDashboard, 1400);
                return;
            }
            if (!status.installed) {
                setStatus('Installing MedEx...', 'Module files and database objects are being prepared now.', '', 26);
                await runManageAction('install');
                status = await waitFor((value) => !!value.installed, 12, 1200);
                setStatus('Installed', 'MedEx install is complete. Starting enable next.', '', 58);
                await new Promise((resolve) => window.setTimeout(resolve, 550));
            }
            if (!status.enabled) {
                setStatus('Enabling MedEx...', 'Install is complete. Activating MedEx now.', '', 78);
                await runManageAction('enable');
                status = await waitFor((value) => !!value.enabled, 12, 1200);
                setStatus('Enabled', 'Module activation is complete. Preparing onboarding.', '', 92);
                await new Promise((resolve) => window.setTimeout(resolve, 650));
            }
            status = await fetchStatus();
            if (status.dashboard_ready) {
                setStatus('Opening MedEx Dashboard...', 'Install and enable are complete. Loading the dashboard now.', 'done', 100);
                window.setTimeout(redirectToDashboard, 1600);
                return;
            }
            setStatus('Opening onboarding...', 'MedEx is ready. Moving into onboarding now.', 'done', 100);
            window.setTimeout(redirectToOnboarding, 1600);
        } catch (error) {
            running = false;
            setStatus('MedEx setup needs attention.', error && error.message ? error.message : 'The automatic setup did not complete.', 'error', 100);
        }
    }

    retryBtn.addEventListener('click', runFlow);
    window.addEventListener('load', runFlow);
</script>
</body>
</html>
