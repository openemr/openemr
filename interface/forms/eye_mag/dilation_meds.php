<?php

/** Practice-wide display names for the Eye Form's six fixed dilation slots. */

require_once(__DIR__ . '/../../globals.php');
require_once(__DIR__ . '/php/eye_mag_functions.php');

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\EyeFormData;

use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_dilation_med_defaults;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_dilation_med_options;

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate('ACL check failed for admin/super: Dilation Medications', xl('Dilation Medications'));
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();
$posted = $request->request->all();
$listId = 'Eye_Dilation_Meds';
$defaults = eye_mag_dilation_med_defaults();
$saved = false;

if ($request->isMethod('POST')) {
    if (!CsrfUtils::verifyCsrfToken(EyeFormData::text($posted['csrf_token_form'] ?? ''), session: $session)) {
        CsrfUtils::csrfNotVerified();
    }

    $submittedTitles = EyeFormData::row($posted['title'] ?? []);
    QueryUtils::inTransaction(static function () use ($submittedTitles, $listId, $defaults): void {
        QueryUtils::sqlStatementThrowException('DELETE FROM list_options WHERE list_id = ?', [$listId]);
        $sequence = 10;
        foreach ($defaults as $field => [$defaultTitle, $storedValue]) {
            $title = trim(EyeFormData::text($submittedTitles[$field] ?? ''));
            if ($title === '') {
                $title = $defaultTitle;
            }
            $title = mb_substr($title, 0, 80, 'UTF-8');
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO list_options
                    (list_id, option_id, title, seq, option_value, mapping, notes, codes, activity)
                 VALUES (?, ?, ?, ?, 0, '', ?, '', 1)",
                [$listId, $field, $title, $sequence, $storedValue]
            );
            $sequence += 10;
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT IGNORE INTO list_options
                (list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, activity)
             VALUES ('lists', ?, ?, 0, 1, 0, '', '', '', 0)",
            [$listId, 'Eye Dilation Medications']
        );
    });
    $saved = true;
}

$titles = [];
foreach (eye_mag_dilation_med_options() as $option) {
    $titles[$option['field']] = $option['title'];
}

// The Eye Form opens this page in a modal and reads the titles back as JSON, both after a
// save and when the modal closes, so its labels update without reloading the form.
if (($request->query->all()['format'] ?? $posted['format'] ?? '') === 'json') {
    header('Content-Type: application/json');
    echo json_encode(['saved' => $saved, 'titles' => $titles]);
    exit;
}

$csrfToken = CsrfUtils::collectCsrfToken(session: $session);
?>
<!doctype html>
<html lang="<?php echo attr(OEGlobalsBag::getInstance()->getString('language_iso_code', 'en')); ?>">
<head>
    <?php Header::setupHeader(['opener']); ?>
    <title><?php echo xlt('Dilation Medications'); ?></title>
    <style>
        body { margin: 0; padding: 18px; }
        .dilation-editor { margin: 0 auto; max-width: 560px; }
        .dilation-card { border: 1px solid var(--gray-400, #adb5bd); border-radius: 8px; padding: 18px; }
        .dilation-help { color: var(--gray-700, #58636f); margin: 0 0 16px; }
        .dilation-row { align-items: center; display: grid; gap: 12px; grid-template-columns: 72px 1fr; margin-bottom: 10px; }
        .dilation-row label { font-weight: 600; margin: 0; }
        .dilation-row input { width: 100%; }
        .dilation-status { color: #176b36; font-weight: 600; min-height: 24px; }
        .dilation-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
    </style>
</head>
<body>
<main class="dilation-editor">
    <p class="dilation-help"><?php echo xlt('Enter the medication names shown in the Eye Form.'); ?></p>
    <form method="post" class="dilation-card">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>">
        <?php $slot = 1; ?>
        <?php foreach ($defaults as $field => [$defaultTitle, $storedValue]) { ?>
            <div class="dilation-row">
                <label for="drug_<?php echo attr((string)$slot); ?>"><?php echo xlt('Drug'); ?> <?php echo text((string)$slot); ?></label>
                <input type="text" class="form-control" id="drug_<?php echo attr((string)$slot); ?>"
                       name="title[<?php echo attr($field); ?>]" maxlength="80"
                       value="<?php echo attr($titles[$field] ?? $defaultTitle); ?>">
            </div>
            <?php ++$slot; ?>
        <?php } ?>
        <div class="dilation-status" role="status" aria-live="polite">
            <?php echo $saved ? xlt('Saved.') : ''; ?>
        </div>
        <div class="dilation-actions">
            <button type="button" class="btn btn-secondary" onclick="dlgclose();"><?php echo xlt('Close'); ?></button>
            <button type="submit" class="btn btn-primary"><?php echo xlt('Save'); ?></button>
        </div>
    </form>
</main>
<script>
    // Save without leaving the modal, then hand the new titles to every open Eye Form.
    // Closing (X or Close) makes the form re-read them as well, so nothing depends on this
    // message arriving.
    (function () {
        var form = document.querySelector('form.dilation-card');
        var status = document.querySelector('.dilation-status');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var data = new FormData(form);
            data.append('format', 'json');
            status.textContent = '';
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
            fetch(window.location.pathname, {method: 'POST', body: data, credentials: 'same-origin'})
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (result) {
                    status.textContent = <?php echo xlj('Saved.'); ?>;
                    if ('BroadcastChannel' in window) {
                        new BroadcastChannel('eye_mag_dilation_meds').postMessage(result.titles);
                    }
                })
                .catch(function () {
                    status.textContent = <?php echo xlj('Not saved. Please try again.'); ?>;
                });
        });
    })();
</script>
</body>
</html>
