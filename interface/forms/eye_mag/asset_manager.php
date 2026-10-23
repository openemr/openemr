<?php

/**
 * Manage the Eye Form provider signature and practice logo.
 */

require_once(__DIR__ . '/../../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$authUserId = (int)($session->get('authUserID') ?? 0);
$asset = (string)($_REQUEST['asset'] ?? 'signature');
$providerId = (int)($_REQUEST['provider_id'] ?? 0);
$isAdmin = AclMain::aclCheckCore('admin', 'super');
$isSignature = $asset === 'signature';
$isLogo = $asset === 'logo';

if (!$isSignature && !$isLogo) {
    http_response_code(400);
    die(xlt('Unknown asset type.'));
}

$provider = [];
if ($isSignature) {
    if ($providerId <= 0 || $providerId !== $authUserId) {
        http_response_code(403);
        die(xlt('Only the provider of record may change this signature.'));
    }
    $provider = sqlQuery(
        "SELECT id, fname, lname, suffix FROM users WHERE id = ? AND active = 1 LIMIT 1",
        [$providerId]
    );
    if (empty($provider['id'])) {
        http_response_code(404);
        die(xlt('Provider not found.'));
    }
} elseif (!$isAdmin) {
    http_response_code(403);
    die(xlt('Administrator access is required to change the practice logo.'));
}

$webroot = OEGlobalsBag::getInstance()->getWebRoot();
$signaturePath = OEGlobalsBag::getInstance()->getProjectDir() . '/interface/forms/eye_mag/images/sign_' . $providerId . '.jpg';
$signatureUrl = $webroot . '/interface/forms/eye_mag/images/sign_' . $providerId . '.jpg';
$logoDirectory = $GLOBALS['OE_SITE_DIR'] . '/images';
$logoPath = $logoDirectory . '/practice_logo.gif';
$siteName = basename((string)$GLOBALS['OE_SITE_DIR']);
$logoUrl = $webroot . '/sites/' . rawurlencode($siteName) . '/images/practice_logo.gif';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', session: $session)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => xlt('Invalid request token. Please reopen the editor.')]);
        exit;
    }

    $upload = $_FILES['asset_file'] ?? null;
    if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => xlt('No image received.')]);
        exit;
    }
    if ((int)($upload['size'] ?? 0) > 2 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => xlt('Image too large (2MB maximum).')]);
        exit;
    }

    $mimeDetector = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$mimeDetector->file($upload['tmp_name']);
    $allowed = $isSignature ? ['image/jpeg', 'image/png'] : ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime, $allowed, true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $isSignature ? xlt('Use a JPEG or PNG image.') : xlt('Use a JPEG, PNG, or GIF image.'),
        ]);
        exit;
    }

    $image = @imagecreatefromstring((string)file_get_contents($upload['tmp_name']));
    if ($image === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => xlt('The image could not be read.')]);
        exit;
    }

    if ($isSignature) {
        $saved = @imagejpeg($image, $signaturePath, 90);
        $savedPath = $signaturePath;
        $previewUrl = $signatureUrl;
    } else {
        if (!is_dir($logoDirectory)) {
            @mkdir($logoDirectory, 0755, true);
        }
        $saved = @imagegif($image, $logoPath);
        $savedPath = $logoPath;
        $previewUrl = $logoUrl;
    }
    imagedestroy($image);

    if (!$saved) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => xlt('The image could not be saved.')]);
        exit;
    }

    @chmod($savedPath, 0644);
    clearstatcache(true, $savedPath);
    echo json_encode([
        'success' => true,
        'message' => xlt('Saved.'),
        'preview' => $previewUrl . '?t=' . (int)@filemtime($savedPath),
    ]);
    exit;
}

$csrfToken = (string)CsrfUtils::collectCsrfToken(session: $session);
$providerName = trim(
    (string)($provider['fname'] ?? '') . ' ' .
    (string)($provider['lname'] ?? '') . ' ' .
    (string)($provider['suffix'] ?? '')
);
$currentPath = $isSignature ? $signaturePath : $logoPath;
$currentUrl = $isSignature ? $signatureUrl : $logoUrl;
$hasCurrent = file_exists($currentPath);
$currentPreview = $hasCurrent ? $currentUrl . '?t=' . (int)@filemtime($currentPath) : '';
?>
<!doctype html>
<html lang="<?php echo attr($GLOBALS['language_iso_code'] ?? 'en'); ?>">
<head>
    <?php Header::setupHeader(['opener', 'pure']); ?>
    <title><?php echo $isSignature ? xlt('My Signature') : xlt('Practice Logo'); ?></title>
    <style>
        :root { color-scheme: light dark; }
        body { margin: 0; padding: 20px; }
        .asset-editor { margin: 0 auto; max-width: 760px; }
        .asset-heading { align-items: flex-start; display: flex; justify-content: space-between; margin-bottom: 16px; }
        .asset-heading h2 { margin: 0 0 4px; }
        .asset-heading p { color: var(--gray-700, #58636f); margin: 0; }
        .asset-card { background: var(--white, #fff); border: 1px solid var(--gray-400, #adb5bd); border-radius: 8px; padding: 18px; }
        .asset-preview { align-items: center; background: var(--gray-100, #f5f7f9); border: 1px solid var(--gray-300, #ced4da); border-radius: 6px; display: flex; justify-content: center; margin-bottom: 18px; min-height: 120px; padding: 14px; }
        .asset-preview img { max-height: 110px; max-width: 100%; object-fit: contain; }
        .asset-empty { color: var(--gray-600, #6c757d); }
        .asset-methods { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .asset-method { border-top: 1px solid var(--gray-300, #ced4da); padding-top: 14px; }
        .asset-method-full { grid-column: 1 / -1; }
        .asset-method h3 { font-size: 1rem; margin: 0 0 10px; }
        .asset-row { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
        .asset-row input[type="text"], .asset-row select { min-height: 36px; }
        #signature_pad { background: #fff; border: 1px solid #8a949e; border-radius: 4px; max-width: 100%; touch-action: none; }
        #typed_preview { color: #111; font-size: 42px; line-height: 1.2; min-height: 58px; overflow: hidden; padding: 8px 0; white-space: nowrap; }
        .asset-status { min-height: 24px; padding-top: 10px; }
        .asset-status.success { color: #176b36; }
        .asset-status.error { color: #b42318; }
        .asset-actions { display: flex; justify-content: flex-end; margin-top: 16px; }
        @media (max-width: 620px) { .asset-methods { grid-template-columns: 1fr; } .asset-method-full { grid-column: auto; } }
    </style>
</head>
<body>
<main class="asset-editor">
    <header class="asset-heading">
        <div>
            <h2><?php echo $isSignature ? xlt('My Signature') : xlt('Practice Logo'); ?></h2>
            <p><?php echo $isSignature
                ? xlt('Signature used for prescriptions written by') . ' ' . text($providerName)
                : xlt('Logo used on Eye Form reports and prescriptions.'); ?></p>
        </div>
    </header>

    <section class="asset-card">
        <div class="asset-preview">
            <img id="asset_preview" src="<?php echo attr($currentPreview); ?>" alt=""
                 <?php echo $hasCurrent ? '' : 'hidden'; ?>>
            <span id="asset_empty" class="asset-empty" <?php echo $hasCurrent ? 'hidden' : ''; ?>>
                <?php echo $isSignature ? xlt('No signature saved') : xlt('No practice logo saved'); ?>
            </span>
        </div>

        <div class="asset-methods">
            <section class="asset-method <?php echo $isLogo ? 'asset-method-full' : ''; ?>">
                <h3><?php echo $isSignature ? xlt('Upload a signature image') : xlt('Upload a logo'); ?></h3>
                <div class="asset-row">
                    <input type="file" id="asset_file" accept="<?php echo $isSignature ? 'image/jpeg,image/png' : 'image/jpeg,image/png,image/gif'; ?>">
                    <button type="button" class="btn btn-primary" id="upload_btn"><?php echo xlt('Save Image'); ?></button>
                </div>
            </section>

            <?php if ($isSignature) { ?>
            <section class="asset-method">
                <h3><?php echo xlt('Create from typed name'); ?></h3>
                <div class="asset-row">
                    <input type="text" id="typed_name" value="<?php echo attr($providerName); ?>" aria-label="<?php echo xla('Signature name'); ?>">
                    <select id="typed_font" aria-label="<?php echo xla('Signature style'); ?>">
                        <option value="'Brush Script MT','Segoe Script','Apple Chancery',cursive" data-style=""><?php echo xlt('Script'); ?></option>
                        <option value="'Segoe Print','Bradley Hand','Chalkboard SE',cursive" data-style=""><?php echo xlt('Handwritten'); ?></option>
                        <option value="Georgia,'Times New Roman',serif" data-style="italic 700 "><?php echo xlt('Formal'); ?></option>
                    </select>
                    <button type="button" class="btn btn-primary" id="typed_btn"><?php echo xlt('Save Typed Signature'); ?></button>
                </div>
                <div id="typed_preview"></div>
            </section>

            <section class="asset-method asset-method-full">
                <h3><?php echo xlt('Draw signature'); ?></h3>
                <canvas id="signature_pad" width="700" height="150"></canvas>
                <div class="asset-row mt-2">
                    <button type="button" class="btn btn-secondary" id="clear_btn"><?php echo xlt('Clear'); ?></button>
                    <button type="button" class="btn btn-primary" id="draw_btn"><?php echo xlt('Save Drawing'); ?></button>
                </div>
            </section>
            <?php } ?>
        </div>

        <div id="asset_status" class="asset-status" role="status" aria-live="polite"></div>
    </section>

    <div class="asset-actions">
        <button type="button" class="btn btn-secondary" onclick="dlgclose();"><?php echo xlt('Close'); ?></button>
    </div>
</main>

<canvas id="render_canvas" width="700" height="150" hidden></canvas>
<script>
(function () {
    'use strict';
    const csrf = <?php echo js_escape($csrfToken); ?>;
    const status = document.getElementById('asset_status');
    const preview = document.getElementById('asset_preview');
    const empty = document.getElementById('asset_empty');

    function showStatus(message, ok) {
        status.textContent = message || '';
        status.className = 'asset-status ' + (ok ? 'success' : 'error');
    }

    function saveBlob(blob, filename) {
        const body = new FormData();
        body.append('asset', <?php echo js_escape($asset); ?>);
        body.append('provider_id', <?php echo attr_js($providerId); ?>);
        body.append('csrf_token_form', csrf);
        body.append('asset_file', blob, filename);
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        fetch(window.location.href, {method: 'POST', body: body})
            .then(function (response) { return response.json(); })
            .then(function (result) {
                if (!result.success) throw new Error(result.error || <?php echo xlj('Save failed.'); ?>);
                preview.src = result.preview;
                preview.hidden = false;
                empty.hidden = true;
                showStatus(result.message || <?php echo xlj('Saved.'); ?>, true);
            })
            .catch(function (error) { showStatus(error.message || <?php echo xlj('Save failed.'); ?>, false); });
    }

    document.getElementById('upload_btn').addEventListener('click', function () {
        const input = document.getElementById('asset_file');
        if (!input.files || !input.files[0]) {
            showStatus(<?php echo xlj('Choose an image first.'); ?>, false);
            return;
        }
        saveBlob(input.files[0], input.files[0].name || 'asset.png');
    });

    <?php if ($isSignature) { ?>
    const typedName = document.getElementById('typed_name');
    const typedFont = document.getElementById('typed_font');
    const typedPreview = document.getElementById('typed_preview');
    function fontFamily() { return typedFont.options[typedFont.selectedIndex].value; }
    function fontStyle() { return typedFont.options[typedFont.selectedIndex].dataset.style || ''; }
    function updateTypedPreview() {
        typedPreview.textContent = typedName.value;
        typedPreview.style.font = (fontStyle() + '42px ' + fontFamily()).trim();
    }
    typedName.addEventListener('input', updateTypedPreview);
    typedFont.addEventListener('change', updateTypedPreview);
    updateTypedPreview();

    document.getElementById('typed_btn').addEventListener('click', function () {
        const name = typedName.value.trim();
        if (!name) { showStatus(<?php echo xlj('Enter the provider name first.'); ?>, false); return; }
        const canvas = document.getElementById('render_canvas');
        const context = canvas.getContext('2d');
        context.fillStyle = '#fff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.fillStyle = '#111';
        context.textBaseline = 'middle';
        let size = 64;
        function setFont() { context.font = (fontStyle() + size + 'px ' + fontFamily()).trim(); }
        setFont();
        while (context.measureText(name).width > canvas.width - 40 && size > 24) { size -= 4; setFont(); }
        context.fillText(name, 20, canvas.height / 2);
        canvas.toBlob(function (blob) { if (blob) saveBlob(blob, 'signature-typed.png'); }, 'image/png');
    });

    const pad = document.getElementById('signature_pad');
    const padContext = pad.getContext('2d');
    padContext.strokeStyle = '#111';
    padContext.lineWidth = 2.5;
    padContext.lineCap = 'round';
    padContext.lineJoin = 'round';
    let drawing = false;
    let hasInk = false;
    let lastX = 0;
    let lastY = 0;
    function point(event) {
        const box = pad.getBoundingClientRect();
        return [(event.clientX - box.left) * pad.width / box.width, (event.clientY - box.top) * pad.height / box.height];
    }
    pad.addEventListener('pointerdown', function (event) {
        drawing = true;
        const position = point(event);
        lastX = position[0]; lastY = position[1];
        if (pad.setPointerCapture) pad.setPointerCapture(event.pointerId);
        event.preventDefault();
    });
    pad.addEventListener('pointermove', function (event) {
        if (!drawing) return;
        const position = point(event);
        padContext.beginPath();
        padContext.moveTo(lastX, lastY);
        padContext.lineTo(position[0], position[1]);
        padContext.stroke();
        lastX = position[0]; lastY = position[1]; hasInk = true;
        event.preventDefault();
    });
    pad.addEventListener('pointerup', function () { drawing = false; });
    pad.addEventListener('pointercancel', function () { drawing = false; });
    document.getElementById('clear_btn').addEventListener('click', function () {
        padContext.clearRect(0, 0, pad.width, pad.height); hasInk = false;
    });
    document.getElementById('draw_btn').addEventListener('click', function () {
        if (!hasInk) { showStatus(<?php echo xlj('Draw the signature first.'); ?>, false); return; }
        const canvas = document.getElementById('render_canvas');
        const context = canvas.getContext('2d');
        context.fillStyle = '#fff'; context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(pad, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(function (blob) { if (blob) saveBlob(blob, 'signature-drawn.png'); }, 'image/png');
    });
    <?php } ?>
})();
</script>
</body>
</html>
