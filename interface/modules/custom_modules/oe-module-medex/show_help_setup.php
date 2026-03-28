<?php
/**
 * MedEx Setup Help (pre-install / pre-enable)
 * Rendered inside the Module Manager help modal iframe.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . '/../../../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;

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

    $sqlRun = (int)($row['sql_run'] ?? 0);
    $modId = (int)($row['mod_id'] ?? 0);
    $modActive = (int)($row['mod_active'] ?? 0);
    $modUiActive = (int)($row['mod_ui_active'] ?? 0);

    $installed = ($sqlRun === 1);
    $enabled = ($modActive === 1);
    $managerReloaded = $installed && ($enabled || $modUiActive === 1);
    $dashboardReady = $enabled;

    $nextAction = 'install';
    if (!$installed) {
        $nextAction = 'install';
    } elseif (!$enabled) {
        $nextAction = 'enable';
    } else {
        $nextAction = 'configure';
    }

    return [
        'mod_id' => $modId,
        'installed' => $installed,
        'enabled' => $enabled,
        'dashboard_ready' => $dashboardReady,
        'next_action' => $nextAction,
    ];
}

if (($_GET['action'] ?? '') === 'status') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'status' => medexSetupStatus()]);
    exit;
}

$status = medexSetupStatus();
$siteId = (string)($_GET['site'] ?? 'default');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MedEx Setup</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #dbeafe;
            --ok: #047857;
            --todo: #0f4b8f;
            --bg: #f8fbff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
        }
        .wrap {
            padding: 16px;
            max-width: 900px;
            margin: 0 auto;
        }
        .hdr {
            margin-bottom: 14px;
        }
        .hdr h2 {
            margin: 0 0 6px;
            font-size: 24px;
            color: #0f4b8f;
        }
        .hdr p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }
        .next {
            margin: 12px 0 14px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 14px;
            font-weight: 700;
        }
        .steps {
            display: grid;
            gap: 10px;
        }
        .step {
            display: grid;
            grid-template-columns: 28px 1fr;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            padding: 12px;
        }
        .icon {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 800;
            margin-top: 2px;
            border: 1px solid #93c5fd;
            color: var(--todo);
            background: #eff6ff;
        }
        .step.done .icon {
            border-color: #86efac;
            color: var(--ok);
            background: #ecfdf5;
        }
        .step h3 {
            margin: 0 0 4px;
            font-size: 16px;
        }
        .step p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }
        .step .state {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #1d4ed8;
        }
        .step.done .state {
            color: var(--ok);
        }
        .step .act {
            margin-top: 8px;
        }
        .act-btn {
            border: 1px solid #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 10px;
            cursor: pointer;
        }
        .act-btn:hover {
            background: #dbeafe;
        }
        .foot {
            margin-top: 14px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hdr">
        <h2>MedEx Setup Checklist</h2>
        <p>Follow these four steps. This status updates automatically while you work in Module Manager.</p>
    </div>

    <div id="nextAction" class="next"></div>

    <div class="steps">
        <div id="step-install" class="step">
            <div class="icon">1</div>
            <div>
                <h3>Install the module</h3>
                <p>Click <strong>Install</strong> for <strong>oe-module-medex</strong> in Module Manager.</p>
                <div class="state"></div>
                <div class="act">
                    <button type="button" class="act-btn" id="installBtn">Run Install</button>
                </div>
            </div>
        </div>

        <div id="step-enable" class="step">
            <div class="icon">2</div>
            <div>
                <h3>Enable the module</h3>
                <p>Click <strong>Enable</strong> to activate MedEx.</p>
                <div class="state"></div>
                <div class="act">
                    <button type="button" class="act-btn" id="enableBtn">Run Enable</button>
                </div>
            </div>
        </div>

        <div id="step-configure" class="step">
            <div class="icon">3</div>
            <div>
                <h3>Open onboarding</h3>
                <p>After Enable succeeds, onboarding opens automatically.</p>
                <div class="state"></div>
                <div class="act">
                    <button type="button" class="act-btn" id="onboardingBtn">Open Onboarding</button>
                </div>
            </div>
        </div>

        <div id="step-dashboard" class="step">
            <div class="icon">4</div>
            <div>
                <h3>Complete onboarding</h3>
                <p>Finish signup and service selection in onboarding.</p>
                <div class="state"></div>
            </div>
        </div>
    </div>

    <div class="foot">This guide reflects current module state in real time.</div>
</div>

<script>
    const initStatus = <?php echo json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const setupSiteId = <?php echo json_encode($siteId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    function setStep(stepId, done, doneText) {
        const step = document.getElementById(stepId);
        if (!step) return;
        step.classList.toggle('done', !!done);
        const state = step.querySelector('.state');
        if (state) {
            state.textContent = done ? doneText : '';
        }
    }

    function render(status) {
        setStep('step-install', status.installed, 'Done');
        setStep('step-enable', status.enabled, 'Done');
        setStep('step-configure', status.enabled, 'Ready');
        setStep('step-dashboard', status.dashboard_ready, 'Ready');

        const installBtn = document.getElementById('installBtn');
        const enableBtn = document.getElementById('enableBtn');
        const onboardingBtn = document.getElementById('onboardingBtn');
        if (installBtn) {
            installBtn.style.display = status.installed ? 'none' : 'inline-flex';
        }
        if (enableBtn) {
            enableBtn.style.display = (status.installed && !status.enabled) ? 'inline-flex' : 'none';
        }
        if (onboardingBtn) {
            onboardingBtn.style.display = status.enabled ? 'inline-flex' : 'none';
        }

        const msg = document.getElementById('nextAction');
        if (!msg) return;
        if (status.next_action === 'install') {
            msg.textContent = 'Next action: Install the module in Module Manager.';
        } else if (status.next_action === 'enable') {
            msg.textContent = 'Next action: Enable the module.';
        } else {
            msg.textContent = 'Next action: Click the gear icon to continue onboarding.';
        }
    }

    function openOnboardingNow() {
        const topWin = window.top || window;
        const url = '/interface/modules/custom_modules/oe-module-medex/admin/splash.php?minimal=1&site=' + encodeURIComponent(setupSiteId || 'default');
        topWin.location.href = url;
    }

    async function runManageAction(actionName) {
        if (!initStatus.mod_id || initStatus.mod_id <= 0) {
            throw new Error('Module ID not available');
        }
        const body = new URLSearchParams({
            modId: String(initStatus.mod_id),
            modAction: actionName,
            mod_enc_menu: '',
            mod_nick_name: ''
        });
        const res = await fetch('../../zend_modules/public/Installer/manage', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        });
        if (!res.ok) {
            throw new Error('Installer/manage request failed');
        }
        const txt = await res.text();
        let parsed = null;
        try {
            parsed = JSON.parse(txt);
        } catch (e) {
            // Some installs may emit plain text; status polling is source of truth.
        }
        if (parsed && parsed.status && String(parsed.status).toLowerCase() !== 'success') {
            throw new Error(String(parsed.status));
        }
    }

    function inferStatusFromModuleRow() {
        const topDoc = (window.top && window.top.document) ? window.top.document : document;
        const rows = Array.from(topDoc.querySelectorAll('tr'));
        const row = rows.find((tr) => {
            const t = (tr.textContent || '').toLowerCase();
            return t.includes('oe-module-medex') || t.includes('medex module');
        });
        if (!row) {
            return null;
        }

        const text = (row.textContent || '').toLowerCase();
        const hasInstall = text.includes('install');
        const hasEnable = text.includes('enable');
        const hasDisable = text.includes('disable');

        let installed = true;
        let enabled = false;
        if (hasInstall && !hasEnable && !hasDisable) {
            installed = false;
            enabled = false;
        } else if (hasEnable && !hasDisable) {
            installed = true;
            enabled = false;
        } else if (hasDisable) {
            installed = true;
            enabled = true;
        }

        const nextAction = !installed ? 'install' : (!enabled ? 'enable' : 'configure');
        return {
            mod_id: initStatus.mod_id || 0,
            installed,
            enabled,
            dashboard_ready: enabled,
            next_action: nextAction
        };
    }

    async function refresh() {
        const inferred = inferStatusFromModuleRow();
        if (inferred) {
            render(inferred);
            return;
        }
        // Fallback once if row is not found.
        try {
            const r = await fetch('show_help_setup.php?action=status&site=default', { cache: 'no-store', credentials: 'same-origin' });
            if (r.status === 401) {
                return;
            }
            const j = await r.json();
            if (j && j.success && j.status) {
                render(j.status);
            }
        } catch (e) {
            // Keep last known state visible.
        }
    }

    document.getElementById('installBtn').addEventListener('click', async () => {
        try {
            await runManageAction('install');
            setTimeout(refresh, 1000);
            setTimeout(refresh, 2500);
            setTimeout(refresh, 5000);
        } catch (e) {
            alert('Install failed: ' + (e && e.message ? e.message : 'request error'));
        }
    });

    document.getElementById('enableBtn').addEventListener('click', async () => {
        try {
            await runManageAction('enable');
            setTimeout(refresh, 1000);
            setTimeout(refresh, 2500);
            setTimeout(() => {
                refresh().finally(() => {
                    const inferred = inferStatusFromModuleRow();
                    if (inferred && inferred.enabled) {
                        openOnboardingNow();
                    }
                });
            }, 5000);
        } catch (e) {
            alert('Enable failed: ' + (e && e.message ? e.message : 'request error'));
        }
    });

    document.getElementById('onboardingBtn').addEventListener('click', () => {
        openOnboardingNow();
    });

    render(initStatus);
    setInterval(refresh, 2500);
</script>
</body>
</html>
