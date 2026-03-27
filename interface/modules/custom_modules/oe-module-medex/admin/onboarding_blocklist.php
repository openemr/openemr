<?php
/**
 * MedEx Module - Onboarding Email/Domain Blocklist Manager
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo xlt("Access denied");
    exit;
}

function medexEnsureOnboardingEmailBlocklistTable(): void
{
    QueryUtils::sqlStatementThrowException(
        "CREATE TABLE IF NOT EXISTS `medex_onboarding_email_blocklist` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `match_type` varchar(20) NOT NULL DEFAULT 'email',
            `match_value` varchar(190) NOT NULL,
            `reason` varchar(255) DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_match` (`match_type`, `match_value`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        []
    );
}

function medexNormalizeBlockValue(string $type, string $value): string
{
    $v = strtolower(trim($value));
    if ($type === 'email') {
        return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';
    }
    if ($type === 'domain') {
        return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $v) ? $v : '';
    }
    return '';
}

medexEnsureOnboardingEmailBlocklistTable();
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', 'default')) {
        $error = xlt("Invalid security token");
    } else {
        $action = trim((string)($_POST['action'] ?? ''));
        try {
            if ($action === 'add') {
                $type = trim((string)($_POST['match_type'] ?? 'email'));
                $rawValue = trim((string)($_POST['match_value'] ?? ''));
                $reason = trim((string)($_POST['reason'] ?? ''));
                if (!in_array($type, ['email', 'domain'], true)) {
                    throw new RuntimeException(xlt("Invalid block type"));
                }
                $value = medexNormalizeBlockValue($type, $rawValue);
                if ($value === '') {
                    throw new RuntimeException($type === 'email' ? xlt("Enter a valid email address") : xlt("Enter a valid domain"));
                }
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO medex_onboarding_email_blocklist (match_type, match_value, reason, is_active)
                     VALUES (?, ?, ?, 1)
                     ON DUPLICATE KEY UPDATE reason = VALUES(reason), is_active = 1",
                    [$type, $value, substr($reason, 0, 255)]
                );
                $notice = xlt("Blocklist entry saved");
            } elseif ($action === 'toggle') {
                $id = (int)($_POST['id'] ?? 0);
                $active = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
                QueryUtils::sqlStatementThrowException(
                    "UPDATE medex_onboarding_email_blocklist SET is_active = ? WHERE id = ?",
                    [$active, $id]
                );
                $notice = xlt("Blocklist entry updated");
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM medex_onboarding_email_blocklist WHERE id = ?",
                    [$id]
                );
                $notice = xlt("Blocklist entry deleted");
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$rows = QueryUtils::fetchRecords(
    "SELECT id, match_type, match_value, reason, is_active, updated_at
     FROM medex_onboarding_email_blocklist
     ORDER BY updated_at DESC, id DESC",
    []
);

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("MedEx Onboarding Blocklist"); ?></title>
    <?php Header::setupHeader(['jquery-min-3-7-1']); ?>
    <style>
        body { background:#f8fafc; font-family:'Segoe UI', Tahoma, sans-serif; }
        .wrap { max-width: 980px; margin: 24px auto; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; }
        h2 { margin:0 0 14px 0; color:#0f4b8f; }
        .toplinks { margin-bottom: 14px; }
        .toplinks a { color:#0f4b8f; text-decoration:none; margin-right:12px; }
        .row { display:flex; gap:10px; align-items:end; flex-wrap:wrap; margin-bottom: 14px; }
        .field { display:flex; flex-direction:column; gap:6px; }
        .field input, .field select { min-width:200px; padding:9px 10px; border:1px solid #d1d5db; border-radius:6px; }
        .field.reason input { min-width:320px; }
        .btn { border:0; border-radius:6px; padding:9px 12px; cursor:pointer; font-weight:600; }
        .btn-primary { background:#0f4b8f; color:#fff; }
        .btn-danger { background:#dc2626; color:#fff; }
        .btn-gray { background:#e5e7eb; color:#111827; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; border-bottom:1px solid #e5e7eb; padding:8px; font-size:13px; vertical-align:middle; }
        .pill { padding:2px 8px; border-radius:999px; font-size:11px; font-weight:600; }
        .pill.on { background:#dcfce7; color:#166534; }
        .pill.off { background:#f3f4f6; color:#6b7280; }
        .alert { padding:10px 12px; border-radius:6px; margin-bottom: 10px; font-size:13px; }
        .ok { background:#ecfdf5; color:#166534; border:1px solid #a7f3d0; }
        .err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .actions form { display:inline-block; margin-right:6px; }
    </style>
</head>
<body>
<div class="wrap">
    <h2><?php echo xlt("Onboarding Email/Domain Blocklist"); ?></h2>
    <div class="toplinks">
        <a href="onboarding.php"><?php echo xlt("Back to Onboarding"); ?></a>
        <a href="settings.php"><?php echo xlt("Module Settings"); ?></a>
    </div>

    <?php if ($notice !== ''): ?>
        <div class="alert ok"><?php echo text($notice); ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert err"><?php echo text($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr((string)CsrfUtils::collectCsrfToken(session: $session)); ?>">
        <input type="hidden" name="action" value="add">
        <div class="row">
            <div class="field">
                <label><?php echo xlt("Type"); ?></label>
                <select name="match_type">
                    <option value="email"><?php echo xlt("Email"); ?></option>
                    <option value="domain"><?php echo xlt("Domain"); ?></option>
                </select>
            </div>
            <div class="field">
                <label><?php echo xlt("Value"); ?></label>
                <input type="text" name="match_value" placeholder="user@example.com or example.com" required>
            </div>
            <div class="field reason">
                <label><?php echo xlt("Reason (Optional)"); ?></label>
                <input type="text" name="reason" maxlength="255" placeholder="abuse, repeated failed onboarding, etc.">
            </div>
            <button type="submit" class="btn btn-primary"><?php echo xlt("Add / Activate"); ?></button>
        </div>
    </form>

    <table>
        <thead>
        <tr>
            <th><?php echo xlt("Type"); ?></th>
            <th><?php echo xlt("Value"); ?></th>
            <th><?php echo xlt("Reason"); ?></th>
            <th><?php echo xlt("Status"); ?></th>
            <th><?php echo xlt("Updated"); ?></th>
            <th><?php echo xlt("Actions"); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="6"><?php echo xlt("No entries yet."); ?></td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?php echo text(strtoupper((string)$r['match_type'])); ?></td>
                <td><?php echo text((string)$r['match_value']); ?></td>
                <td><?php echo text((string)($r['reason'] ?? '')); ?></td>
                <td>
                    <span class="pill <?php echo ((int)$r['is_active'] === 1) ? 'on' : 'off'; ?>">
                        <?php echo ((int)$r['is_active'] === 1) ? xlt("ACTIVE") : xlt("INACTIVE"); ?>
                    </span>
                </td>
                <td><?php echo text((string)$r['updated_at']); ?></td>
                <td class="actions">
                    <form method="post">
                        <input type="hidden" name="csrf_token_form" value="<?php echo attr((string)CsrfUtils::collectCsrfToken(session: $session)); ?>">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?php echo attr((string)$r['id']); ?>">
                        <input type="hidden" name="is_active" value="<?php echo ((int)$r['is_active'] === 1) ? '0' : '1'; ?>">
                        <button class="btn btn-gray" type="submit"><?php echo ((int)$r['is_active'] === 1) ? xlt("Disable") : xlt("Enable"); ?></button>
                    </form>
                    <form method="post" onsubmit="return confirm('<?php echo xla('Delete this entry?'); ?>');">
                        <input type="hidden" name="csrf_token_form" value="<?php echo attr((string)CsrfUtils::collectCsrfToken(session: $session)); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo attr((string)$r['id']); ?>">
                        <button class="btn btn-danger" type="submit"><?php echo xlt("Delete"); ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
