<?php
/**
 * OpenEMR Mobile Shell (responsive, app-like wrapper)
 *
 * @package OpenEMR
 */

require_once dirname(__FILE__, 2) . '/globals.php';

use OpenEMR\Core\OEGlobalsBag;

$defaultBaseUrl = OEGlobalsBag::getInstance()->get('web_root') ?? '/openemr';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="theme-color" content="#0b57d0" />
    <title><?php echo xlt('OpenEMR Mobile'); ?></title>
    <link rel="manifest" href="./manifest.webmanifest" />
    <link rel="stylesheet" href="./mobile.css" />
</head>
<body>
<div class="app" id="appRoot">
    <header class="app-header">
        <div class="app-title"><?php echo xlt('OpenEMR'); ?></div>
        <button class="icon-btn" id="settingsBtn" aria-label="<?php echo xla('Settings'); ?>">⚙</button>
    </header>

    <main class="viewport">
        <iframe id="openemrFrame" title="OpenEMR" loading="eager"></iframe>
    </main>

    <nav class="tabbar" id="tabbar">
        <button data-target="/interface/login/login.php"><?php echo xlt('Home'); ?></button>
        <button data-target="/interface/main/calendar/index.php"><?php echo xlt('Calendar'); ?></button>
        <button data-target="/interface/main/finder/patient_select.php"><?php echo xlt('Patients'); ?></button>
        <button data-target="/interface/main/messages/messages.php"><?php echo xlt('Messages'); ?></button>
        <button data-target="/interface/super/main/main.php"><?php echo xlt('Admin'); ?></button>
    </nav>
</div>

<div class="sheet hidden" id="settingsSheet" role="dialog" aria-modal="true">
    <div class="sheet-card">
        <h2><?php echo xlt('Mobile Settings'); ?></h2>
        <label for="baseUrlInput"><?php echo xlt('OpenEMR Installation URL'); ?></label>
        <input id="baseUrlInput" type="url" placeholder="https://example.com/openemr" />
        <div class="row">
            <button id="saveSettings"><?php echo xlt('Save'); ?></button>
            <button id="closeSettings" class="btn-secondary"><?php echo xlt('Close'); ?></button>
        </div>
        <p class="hint"><?php echo xlt('Tip: Use your full install URL, for example https://your-domain/openemr'); ?></p>
    </div>
</div>

<script>
(() => {
    const STORAGE_KEY = 'openemr.mobile.baseUrl';
    const DEFAULT_BASE = <?php echo json_encode($defaultBaseUrl); ?>;

    const frame = document.getElementById('openemrFrame');
    const settingsSheet = document.getElementById('settingsSheet');
    const settingsBtn = document.getElementById('settingsBtn');
    const baseUrlInput = document.getElementById('baseUrlInput');

    function normalizeBaseUrl(url) {
        if (!url) {
            return DEFAULT_BASE;
        }
        return url.replace(/\/$/, '');
    }

    function getBaseUrl() {
        return normalizeBaseUrl(localStorage.getItem(STORAGE_KEY) || DEFAULT_BASE);
    }

    function setBaseUrl(url) {
        localStorage.setItem(STORAGE_KEY, normalizeBaseUrl(url));
    }

    function navigate(path) {
        const base = getBaseUrl();
        frame.src = base + path;
    }

    document.querySelectorAll('.tabbar button').forEach((button) => {
        button.addEventListener('click', () => navigate(button.dataset.target));
    });

    settingsBtn.addEventListener('click', () => {
        baseUrlInput.value = getBaseUrl();
        settingsSheet.classList.remove('hidden');
    });

    document.getElementById('closeSettings').addEventListener('click', () => {
        settingsSheet.classList.add('hidden');
    });

    document.getElementById('saveSettings').addEventListener('click', () => {
        setBaseUrl(baseUrlInput.value);
        settingsSheet.classList.add('hidden');
        navigate('/interface/login/login.php');
    });

    navigate('/interface/login/login.php');

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('./sw.js').catch(() => {});
    }
})();
</script>
</body>
</html>
