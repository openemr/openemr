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
        "SELECT mod_active, mod_ui_active, sql_run
         FROM modules
         WHERE mod_directory = 'oe-module-medex'
         ORDER BY mod_id DESC
         LIMIT 1",
        []
    ) ?: [];

    $sqlRun = (int)($row['sql_run'] ?? 0);
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
        'installed' => $installed,
        'manager_reloaded' => $managerReloaded,
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

        <div id="step-reload" class="step">
            <div class="icon">2</div>
            <div>
                <h3>Confirm status button updated</h3>
                <p>If the row still shows <strong>Install</strong> after install, reload Module Manager once.</p>
                <div class="state"></div>
            </div>
        </div>

        <div id="step-enable" class="step">
            <div class="icon">3</div>
            <div>
                <h3>Enable the module</h3>
                <p>Click <strong>Enable</strong> so MedEx is active.</p>
                <div class="state"></div>
                <div class="act">
                    <button type="button" class="act-btn" id="enableBtn">Run Enable</button>
                </div>
            </div>
        </div>

        <div id="step-dashboard" class="step">
            <div class="icon">4</div>
            <div>
                <h3>Open MedEx dashboard (gear icon)</h3>
                <p>Click the <strong>gear</strong> icon to launch onboarding/dashboard.</p>
                <div class="state"></div>
            </div>
        </div>
    </div>

    <div class="foot">This guide reflects current module state in real time.</div>
</div>

<script>
    const initStatus = <?php echo json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    function setStep(stepId, done, doneText, todoText) {
        const step = document.getElementById(stepId);
        if (!step) return;
        step.classList.toggle('done', !!done);
        const state = step.querySelector('.state');
        if (state) {
            state.textContent = done ? doneText : todoText;
        }
    }

    function render(status) {
        setStep('step-install', status.installed, 'Done', 'Pending');
        setStep('step-reload', status.manager_reloaded, 'Done', 'Pending');
        setStep('step-enable', status.enabled, 'Done', 'Pending');
        setStep('step-dashboard', status.dashboard_ready, 'Ready', 'Locked until enabled');

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

    function findMedexRow(doc) {
        const rows = Array.from(doc.querySelectorAll('tr'));
        return rows.find((tr) => {
            const t = (tr.textContent || '').toLowerCase();
            return t.includes('oe-module-medex') || t.includes('medex module');
        }) || null;
    }

    function clickModuleAction(actionName) {
        const topDoc = (window.top && window.top.document) ? window.top.document : document;
        const row = findMedexRow(topDoc);
        if (!row) return false;
        const action = actionName.toLowerCase();

        const candidates = Array.from(row.querySelectorAll('a,button,input[type="button"],input[type="submit"]'));
        const control = candidates.find((el) => {
            const txt = ((el.textContent || el.value || '') + ' ' + (el.getAttribute('title') || '') + ' ' + (el.getAttribute('onclick') || '')).toLowerCase();
            return txt.includes(action);
        });
        if (!control) return false;

        control.click();
        return true;
    }

    async function refresh() {
        try {
            const r = await fetch('show_help_setup.php?action=status&site=default', { cache: 'no-store' });
            const j = await r.json();
            if (j && j.success && j.status) {
                render(j.status);
            }
        } catch (e) {
            // Keep last known state visible.
        }
    }

    document.getElementById('installBtn').addEventListener('click', () => {
        const ok = clickModuleAction('install');
        if (!ok) {
            alert('Install action button not found in Module Manager row.');
            return;
        }
        setTimeout(refresh, 1200);
        setTimeout(refresh, 3000);
    });

    document.getElementById('enableBtn').addEventListener('click', () => {
        const ok = clickModuleAction('enable');
        if (!ok) {
            alert('Enable action button not found in Module Manager row.');
            return;
        }
        setTimeout(refresh, 1200);
        setTimeout(refresh, 3000);
    });

    render(initStatus);
    setInterval(refresh, 2500);
</script>
</body>
</html>
