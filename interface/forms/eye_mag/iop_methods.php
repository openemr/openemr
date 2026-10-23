<?php

/** Practice-wide visibility and display name settings for Eye Form IOP methods. */

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

use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_iop_method_defaults;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_iop_method_options;

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate('ACL check failed for admin/super: IOP Measurement Types', xl('IOP Measurement Types'));
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();
$posted = $request->request->all();
$defaults = eye_mag_iop_method_defaults();
$saved = false;

if ($request->isMethod('POST')) {
    if (!CsrfUtils::verifyCsrfToken(EyeFormData::text($posted['csrf_token_form'] ?? ''), session: $session)) {
        CsrfUtils::csrfNotVerified();
    }

    $visible = EyeFormData::row($posted['visible'] ?? []);
    $submittedLabel = trim(EyeFormData::text($posted['tpn_label'] ?? ''));
    $tpnLabel = $submittedLabel !== '' ? mb_substr($submittedLabel, 0, 24, 'UTF-8') : $defaults['TPN'][0];

    QueryUtils::inTransaction(static function () use ($defaults, $tpnLabel, $visible): void {
        $sequence = 10;
        foreach ($defaults as $method => [$defaultTitle, $description]) {
            $title = $method === 'TPN' ? $tpnLabel : $defaultTitle;
            $activity = isset($visible[$method]) ? 1 : 0;
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO list_options
                    (list_id, option_id, title, seq, option_value, mapping, notes, codes, activity)
                 VALUES ('Eye_IOP_Methods', ?, ?, ?, 0, '', ?, '', ?)
                 ON DUPLICATE KEY UPDATE title = VALUES(title), seq = VALUES(seq),
                    notes = VALUES(notes), activity = VALUES(activity)",
                [$method, $title, $sequence, $description, $activity]
            );
            $sequence += 10;
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT IGNORE INTO list_options
                (list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, activity)
             VALUES ('lists', 'Eye_IOP_Methods', 'Eye IOP Measurement Types', 0, 1, 0, '', '', '', 0)"
        );
    });
    $saved = true;
}

$options = eye_mag_iop_method_options();
$csrfToken = CsrfUtils::collectCsrfToken(session: $session);
?>
<!doctype html>
<html lang="<?php echo attr(OEGlobalsBag::getInstance()->getString('language_iso_code', 'en')); ?>">
<head>
    <?php Header::setupHeader(['opener']); ?>
    <title><?php echo xlt('IOP Measurement Types'); ?></title>
    <style>
        body { margin: 0; padding: 18px; }
        .iop-editor { margin: 0 auto; max-width: 610px; }
        .iop-card { border: 1px solid var(--gray-400, #adb5bd); border-radius: 8px; padding: 18px; }
        .iop-heading { margin: 0 0 4px; }
        .iop-help { color: var(--gray-700, #58636f); margin: 0 0 16px; }
        .iop-row { align-items: center; display: grid; gap: 12px; grid-template-columns: 58px 90px 1fr; margin-bottom: 10px; }
        .iop-row label { font-weight: 600; margin: 0; }
        .iop-row input[type="text"] { width: 100%; }
        .iop-description { color: var(--gray-700, #58636f); }
        .iop-status { color: #176b36; font-weight: 600; min-height: 24px; }
        .iop-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
    </style>
</head>
<body>
<main class="iop-editor">
    <h2 class="iop-heading"><?php echo xlt('IOP Measurement Types'); ?></h2>
    <p class="iop-help"><?php echo xlt('Choose the measurement columns shown in the Tension panel. The TP slot may be renamed for the device your practice uses.'); ?></p>
    <form method="post" class="iop-card">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>">
        <?php foreach ($options as $method => $option) { ?>
            <div class="iop-row">
                <label>
                    <input type="checkbox" name="visible[<?php echo attr($method); ?>]" value="1"<?php echo $option['active'] ? ' checked' : ''; ?>>
                    <?php echo xlt('Show'); ?>
                </label>
                <?php if ($method === 'TPN') { ?>
                    <input type="text" class="form-control" name="tpn_label" maxlength="24"
                           aria-label="<?php echo xla('Display name for the TP measurement slot'); ?>"
                           value="<?php echo attr($option['title']); ?>" list="iop-label-suggestions">
                <?php } else { ?>
                    <strong><?php echo text($option['title']); ?></strong>
                <?php } ?>
                <span class="iop-description"><?php echo text($option['description']); ?></span>
            </div>
        <?php } ?>
        <datalist id="iop-label-suggestions">
            <option value="TP"></option>
            <option value="iCare"></option>
        </datalist>
        <div class="iop-status" role="status" aria-live="polite">
            <?php echo $saved ? xlt('Saved. Reopen the Eye Form to apply these practice settings.') : ''; ?>
        </div>
        <div class="iop-actions">
            <button type="button" class="btn btn-secondary" onclick="dlgclose();"><?php echo xlt('Close'); ?></button>
            <button type="submit" class="btn btn-primary"><?php echo xlt('Save'); ?></button>
        </div>
    </form>
</main>
</body>
</html>
