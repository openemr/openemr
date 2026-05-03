<?php
/**
 * MedEx Communications Center
 *
 * Unified replacement for OpenEMR's Messages page — merges patient notes (pnotes),
 * MedEx Secure Chat active sessions, SMS activity, and admin statistics into one view.
 *
 * Role logic:
 *   admin/super  → sees everything: all pnotes, all chat sessions, all SMS stats
 *   provider     → sees own assigned pnotes, own patients' chat sessions
 *   staff        → same scoping as provider
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';
require_once $GLOBALS['srcdir'] . '/pnotes.inc.php';
require_once $GLOBALS['srcdir'] . '/patient.inc.php';
require_once $GLOBALS['srcdir'] . '/options.inc.php';
require_once __DIR__ . '/../src/MedExAPI.php';
require_once __DIR__ . '/../src/MedExConfig.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\MedExAPI;

// ── Auth ─────────────────────────────────────────────────────────────────────
if (!AclMain::aclCheckCore('patients', 'notes')) {
    echo '<div class="alert alert-danger m-3">' . xlt('Access denied.') . '</div>';
    exit;
}

$authUser  = $_SESSION['authUser'] ?? '';
$authUID   = (int)($_SESSION['authUserID'] ?? 0);
$isAdmin   = AclMain::aclCheckCore('admin', 'super');
$showAll   = $isAdmin ? 'yes' : 'no';

// Resolve current user's full name for display
$currentUserRow = sqlQuery("SELECT fname, lname, authorized FROM users WHERE id = ?", [$authUID]);
$currentUserName = trim(($currentUserRow['fname'] ?? '') . ' ' . ($currentUserRow['lname'] ?? ''));

// ── MedEx API init ────────────────────────────────────────────────────────────
$medex = new MedExAPI();
$medexActive     = $medex->isActive();
$hasChatService  = $medexActive && $medex->hasServiceEntitlement('secure_chat');
$hasSMSService   = $medexActive && $medex->hasAnyServiceEntitlement(['appointment_reminders', 'medex_messages']);
$medexBaseUrl    = rtrim(\OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl(), '/');

// ── CSRF ─────────────────────────────────────────────────────────────────────
$csrf = CsrfUtils::collectCsrfToken();

// ── POST handling ─────────────────────────────────────────────────────────────
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $task = $_POST['task'] ?? '';

    if ($task === 'compose') {
        $notePid    = (int)($_POST['note_pid'] ?? 0);
        $noteBody   = $_POST['note_body'] ?? '';
        $noteTitle  = $_POST['note_title'] ?? 'Unassigned';
        $noteStatus = $_POST['note_status'] ?? 'New';
        $noteAssign = $_POST['assigned_to'] ?? $authUser;
        if ($noteBody !== '') {
            $authorized = (int)($currentUserRow['authorized'] ?? 0);
            addPnote($notePid, $noteBody, $authorized, '1', $noteTitle, $noteAssign, '', $noteStatus);
            $flash = xlt('Message created.');
        }
    } elseif ($task === 'delete') {
        $deleteIds = $_POST['delete_id'] ?? [];
        foreach ((array)$deleteIds as $did) {
            $did = (int)$did;
            if ($did > 0 && (checkPnotesNoteId($did, $authUser) || $isAdmin)) {
                deletePnote($did);
            }
        }
        $flash = xlt('Selected messages deleted.');
    } elseif ($task === 'status') {
        $noteId     = (int)($_POST['note_id'] ?? 0);
        $newStatus  = $_POST['new_status'] ?? '';
        if ($noteId > 0 && $newStatus !== '') {
            sqlStatement(
                "UPDATE pnotes SET message_status = ? WHERE id = ?",
                [$newStatus, $noteId]
            );
            $flash = xlt('Status updated.');
        }
    }
}

// ── Pnotes data ───────────────────────────────────────────────────────────────
$sortby    = in_array($_GET['sortby'] ?? '', ['pnotes.date','patient_data.lname','pnotes.title','pnotes.message_status'], true)
           ? $_GET['sortby'] : 'pnotes.date';
$sortorder = (($_GET['sortorder'] ?? '') === 'asc') ? 'asc' : 'desc';
$begin     = max(0, (int)($_GET['begin'] ?? 0));
$pageSize  = 25;
$activity  = $_GET['activity'] ?? '1'; // 1=active, 0=done, all=all
if (!in_array($activity, ['1', '0', 'all'], true)) {
    $activity = '1';
}

$noteCount = getPnotesByUser($activity, $showAll, $authUser, true);
$noteRows  = getPnotesByUser($activity, $showAll, $authUser, false, $sortby, $sortorder, $begin, $pageSize);

// ── Active Secure Chat sessions (from synced data in OpenEMR DB) ───────────────
$chatSessions = [];
$chatSyncTableExists = (bool)sqlQuery("SHOW TABLES LIKE 'medex_chat_sync'");
if ($hasChatService && $chatSyncTableExists) {
    $chatQuery = $isAdmin
        ? "SELECT mcs.pid, mcs.practice_id,
                  MAX(mcs.sync_date) AS last_activity,
                  COUNT(mcs.id) AS msg_count,
                  pd.fname, pd.lname
           FROM medex_chat_sync mcs
           LEFT JOIN patient_data pd ON mcs.pid = pd.pid
           GROUP BY mcs.pid, mcs.practice_id
           ORDER BY last_activity DESC
           LIMIT 30"
        : "SELECT mcs.pid, mcs.practice_id,
                  MAX(mcs.sync_date) AS last_activity,
                  COUNT(mcs.id) AS msg_count,
                  pd.fname, pd.lname
           FROM medex_chat_sync mcs
           LEFT JOIN patient_data pd ON mcs.pid = pd.pid
           LEFT JOIN onsite_mail om ON mcs.openemr_mail_id = om.id
           WHERE om.owner = ? OR mcs.pid IN (
               SELECT pid FROM patient_data WHERE pid IN (
                   SELECT reply_to FROM pnotes WHERE user = ? AND deleted != 1
               )
           )
           GROUP BY mcs.pid, mcs.practice_id
           ORDER BY last_activity DESC
           LIMIT 30";
    $chatBinds = $isAdmin ? [] : [$authUser, $authUser];
    $chatResult = sqlStatement($chatQuery, $chatBinds);
    while ($row = sqlFetchArray($chatResult)) {
        $chatSessions[] = $row;
    }
}

// ── SMS summary counts ─────────────────────────────────────────────────────────
$smsSentToday = 0;
$smsSentWeek  = 0;
if ($hasSMSService && $chatSyncTableExists) {
    // We read from onsite_mail as proxy; actual SMS stats live in MedEx DB
    $smsTodayRow = sqlQuery(
        "SELECT COUNT(*) AS cnt FROM medex_chat_sync WHERE DATE(sync_date) = CURDATE()"
    );
    $smsSentToday = (int)($smsTodayRow['cnt'] ?? 0);
    $smsWeekRow = sqlQuery(
        "SELECT COUNT(*) AS cnt FROM medex_chat_sync WHERE sync_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
    $smsSentWeek = (int)($smsWeekRow['cnt'] ?? 0);
}

// ── Unread pnotes count ───────────────────────────────────────────────────────
$unreadRow = sqlQuery(
    "SELECT COUNT(*) AS cnt FROM pnotes
     WHERE deleted != 1 AND activity = 1 AND message_status = 'New'
     AND (assigned_to LIKE ? OR assigned_to LIKE ?)",
    [$authUser, '_%']
);
// For non-admin restrict to own unread
$unreadSql = $isAdmin
    ? "SELECT COUNT(*) AS cnt FROM pnotes WHERE deleted != 1 AND activity = 1 AND message_status = 'New'"
    : "SELECT COUNT(*) AS cnt FROM pnotes WHERE deleted != 1 AND activity = 1 AND message_status = 'New' AND assigned_to LIKE ?";
$unreadRow   = sqlQuery($unreadSql, $isAdmin ? [] : [$authUser]);
$unreadCount = (int)($unreadRow['cnt'] ?? 0);

// ── Portal message counts (onsite_mail = patient-initiated portal secure messages) ─
// These are stored in OpenEMR core tables and persist whether or not MedEx is active.
$portalMailNew      = 0;
$portalMailMessages = [];
$portalChatNew      = 0;
if ($hasPortalAccess) {
    $s_user = '%' . $authUser . '%';
    // New unread secure messages from patients
    $pmNewRow = sqlQuery(
        "SELECT COUNT(*) AS cnt FROM onsite_mail
         WHERE owner LIKE ? AND recipient_id LIKE ? AND message_status LIKE '%new%' AND deleted=0",
        [$s_user, $s_user]
    );
    $portalMailNew = (int)($pmNewRow['cnt'] ?? 0);

    // onsite_messages = portal live-chat (separate from secure messaging)
    $pcNewRow = sqlQuery(
        "SELECT COUNT(*) AS cnt FROM onsite_messages WHERE recip_id LIKE ? AND `date` > DATE_SUB(NOW(), INTERVAL 3 DAY)",
        [$s_user]
    );
    $portalChatNew = (int)($pcNewRow['cnt'] ?? 0);

    // Recent portal secure messages for inline display
    $pmQuery = $isAdmin
        ? "SELECT om.id, om.date, om.sender_name, om.recipient_name, om.title, om.body,
                  om.message_status, om.sender_id, pd.pid, pd.fname, pd.lname
           FROM onsite_mail om
           LEFT JOIN patient_data pd ON om.sender_id = pd.pid
           WHERE om.deleted = 0
           ORDER BY om.date DESC LIMIT 50"
        : "SELECT om.id, om.date, om.sender_name, om.recipient_name, om.title, om.body,
                  om.message_status, om.sender_id, pd.pid, pd.fname, pd.lname
           FROM onsite_mail om
           LEFT JOIN patient_data pd ON om.sender_id = pd.pid
           WHERE om.owner LIKE ? AND om.deleted = 0
           ORDER BY om.date DESC LIMIT 50";
    $pmResult = sqlStatement($pmQuery, $isAdmin ? [] : [$s_user]);
    while ($r = sqlFetchArray($pmResult)) {
        $portalMailMessages[] = $r;
    }
}
$portalUnread = $portalMailNew + $portalChatNew;

// ── Appointment stats — last 3 days ──────────────────────────────────────────
$apptStats   = [];
$apptTotal3d = 0;
$apptResult  = sqlStatement(
    "SELECT pc_apptstatus AS status, COUNT(*) AS cnt
     FROM openemr_postcalendar_events
     WHERE pc_eventDate >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
       AND pc_eventDate <= CURDATE()
       AND pc_pid != '' AND pc_pid IS NOT NULL
     GROUP BY pc_apptstatus ORDER BY cnt DESC"
);
while ($r = sqlFetchArray($apptResult)) {
    $apptStats[$r['status']] = (int)$r['cnt'];
    $apptTotal3d += (int)$r['cnt'];
}
// Human-readable labels for appt status codes
$apptStatLabels = [
    '-'     => 'Pending',   '*' => 'Reminder Done', '+' => 'Chart Pulled',
    'x'     => 'Canceled',  '%' => 'Canceled <24h', '?' => 'No Show',
    '@'     => 'Arrived',   '~' => 'Arrived Late',  '!' => 'Left w/o Visit',
    '#'     => 'Ins/Fin',   '<' => 'In Exam Room',  '>' => 'Checked Out',
    '$'     => 'Coded',     '^' => 'Pending',
    'AVM'   => 'AVM Conf',  'SMS' => 'SMS Conf',    'EMAIL' => 'Email Conf',
    'CALL'  => 'Callback',
];
// Colour map for appt status badges
$apptStatColors = [
    '-' => 'secondary', '*' => 'info', '+' => 'info',
    'x' => 'danger',    '%' => 'danger', '?' => 'danger',
    '@' => 'success',   '~' => 'warning', '!' => 'warning',
    '#' => 'warning',   '<' => 'primary', '>' => 'success',
    '$' => 'secondary', '^' => 'secondary',
    'AVM' => 'success', 'SMS' => 'success', 'EMAIL' => 'success', 'CALL' => 'info',
];

// ── Admin: portal messages and pnotes-by-user breakdown ────────────────────────
$adminStats = [];
if ($isAdmin) {
    $statResult = sqlStatement(
        "SELECT u.fname, u.lname, u.username,
                COUNT(p.id) AS total,
                SUM(IF(p.message_status='New',1,0)) AS unread
         FROM users u
         LEFT JOIN pnotes p ON p.assigned_to = u.username AND p.deleted != 1 AND p.activity = 1
         WHERE u.username != '' AND u.active = 1
         GROUP BY u.username
         ORDER BY unread DESC, total DESC"
    );
    while ($row = sqlFetchArray($statResult)) {
        $adminStats[] = $row;
    }
}

// ── All users list for "assign to" dropdown ───────────────────────────────────
$allUsers = [];
$userResult = sqlStatement(
    "SELECT username, fname, lname FROM users WHERE username != '' AND active = 1
     AND (info IS NULL OR info NOT LIKE '%Inactive%') ORDER BY lname, fname"
);
while ($row = sqlFetchArray($userResult)) {
    $allUsers[] = $row;
}

// ── Status badge helper ───────────────────────────────────────────────────────
function statusBadge(string $status): string
{
    $map = [
        'New'      => 'badge-danger',
        'Open'     => 'badge-warning',
        'Done'     => 'badge-success',
        'Pending'  => 'badge-info',
    ];
    $cls = $map[$status] ?? 'badge-secondary';
    return '<span class="badge ' . attr($cls) . '">' . text($status) . '</span>';
}

// ── MedEx SSO token for iframe embeds ─────────────────────────────────────────
$ssoToken = null;
if ($medexActive) {
    try {
        $loginData = $medex->login();
        $ssoToken  = $loginData['token'] ?? null;
    } catch (\Throwable $e) {
        // Non-fatal — iframes just won't embed if no token
    }
}

$hasPortalAccess = AclMain::aclCheckCore('patients', 'portal');
$portalSyncTableExists = (bool)sqlQuery("SHOW TABLES LIKE 'medex_chat_sync'");
$portalTabVisible = $hasPortalAccess || $portalSyncTableExists;
$portalManagerUrl = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/public/portal_messages.php';
$portalInboxUrl = $GLOBALS['webroot'] . '/portal/messaging/messages.php';

$activeTab = htmlspecialchars($_GET['tab'] ?? 'messages', ENT_QUOTES, 'UTF-8');
if (!in_array($activeTab, ['messages', 'chat', 'sms', 'portal', 'admin'], true) || ($activeTab === 'portal' && !$portalTabVisible)) {
    $activeTab = 'messages';
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'opener', 'select2']); ?>
    <title><?php echo xlt('Communications Center'); ?></title>
    <style>
        body { font-size: 0.875rem; }
        .comm-card { border-left: 4px solid #007bff; border-radius: 4px; padding: 12px 16px; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .comm-card.danger  { border-color: #dc3545; }
        .comm-card.success { border-color: #28a745; }
        .comm-card.warning { border-color: #ffc107; }
        .comm-card .comm-num  { font-size: 2rem; font-weight: 700; line-height: 1; color: #343a40; }
        .comm-card .comm-lbl  { font-size: 0.75rem; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; margin-top: 2px; }
        .comm-tabs .nav-link { font-size: 0.85rem; padding: .35rem .75rem; }
        .comm-tabs .nav-link.active { font-weight: 600; }
        .pnotes-table th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: .04em; background: #f8f9fa; }
        .pnotes-table td { vertical-align: middle; }
        .chat-row:hover { background: #f1f9ff; cursor: pointer; }
        .chat-badge { font-size: 0.7rem; }
        .section-toolbar { background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 8px 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .admin-stat-table th { background: #343a40; color: #fff; font-size: 0.75rem; }
        .embedded-pane { height: 760px; border: 1px solid #dee2e6; border-radius: .25rem; overflow: hidden; background: #fff; }
        .embedded-pane iframe { width: 100%; height: 100%; border: 0; }
        .flash-msg { position: sticky; top: 0; z-index: 200; }
        .pager-bar { display: flex; gap: 6px; align-items: center; }
        @media (max-width: 576px) {
            .comm-card .comm-num { font-size: 1.4rem; }
            .hide-xs { display: none !important; }
        }
    </style>
</head>
<body>

<?php if ($flash): ?>
<div class="alert alert-success alert-dismissible flash-msg mb-0 rounded-0" role="alert">
    <?php echo $flash; ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>

<div class="container-fluid px-3 pt-2">

    <!-- ── Page header ─────────────────────────────────────────────────────── -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0"><?php echo xlt('Communications Center'); ?></h5>
        <small class="text-muted"><?php echo text($currentUserName); ?><?php echo $isAdmin ? ' &nbsp;<span class="badge badge-dark">Admin</span>' : ''; ?></small>
    </div>

    <!-- ── Summary cards ───────────────────────────────────────────────────── -->
    <div class="row mb-2 g-2">
        <div class="col-6 col-md-3 mb-2">
            <div class="comm-card <?php echo $unreadCount > 0 ? 'danger' : ''; ?>">
                <div class="comm-num"><?php echo (int)$unreadCount; ?></div>
                <div class="comm-lbl"><?php echo xlt('Unread Messages'); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="comm-card <?php echo $portalUnread > 0 ? 'danger' : ''; ?>">
                <div class="comm-num"><?php echo (int)$portalUnread; ?></div>
                <div class="comm-lbl"><?php echo xlt('Portal Unread'); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="comm-card <?php echo count($chatSessions) > 0 ? 'success' : ''; ?>">
                <div class="comm-num"><?php echo count($chatSessions); ?></div>
                <div class="comm-lbl"><?php echo xlt('Secure Chat Sessions'); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="comm-card">
                <div class="comm-num"><?php echo $smsSentToday; ?></div>
                <div class="comm-lbl"><?php echo xlt('Chat Messages Today'); ?></div>
            </div>
        </div>
    </div>

    <!-- ── Appointment activity — last 3 days ──────────────────────────────── -->
    <?php if ($apptTotal3d > 0): ?>
    <div class="mb-3 p-2 border rounded bg-white" style="font-size:0.8rem;">
        <div class="d-flex align-items-center flex-wrap" style="gap:6px 10px;">
            <span class="text-uppercase font-weight-bold text-muted mr-1" style="font-size:0.7rem;letter-spacing:.05em;"><?php echo xlt('Appts — 3 days'); ?></span>
            <?php foreach ($apptStats as $code => $cnt):
                $label = $apptStatLabels[$code] ?? $code;
                $color = $apptStatColors[$code] ?? 'secondary';
            ?>
            <span class="badge badge-<?php echo attr($color); ?> py-1 px-2" title="<?php echo attr($label); ?>" style="font-size:0.75rem;">
                <?php echo text($label); ?>: <?php echo (int)$cnt; ?>
            </span>
            <?php endforeach; ?>
            <span class="ml-auto text-muted"><?php echo xlt('Total'); ?>: <?php echo (int)$apptTotal3d; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Tab nav ─────────────────────────────────────────────────────────── -->
    <ul class="nav nav-tabs comm-tabs" id="commTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'messages' ? 'active' : ''; ?>"
               href="?tab=messages" id="tab-messages" role="tab">
                <?php echo xlt('Messages'); ?>
                <?php if ($unreadCount > 0): ?>
                    <span class="badge badge-danger ml-1"><?php echo (int)$unreadCount; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php if ($hasChatService): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'chat' ? 'active' : ''; ?>"
               href="?tab=chat" id="tab-chat" role="tab">
                <?php echo xlt('Secure Chat'); ?>
                <?php if (count($chatSessions) > 0): ?>
                    <span class="badge badge-success ml-1"><?php echo count($chatSessions); ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($hasSMSService): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'sms' ? 'active' : ''; ?>"
               href="?tab=sms" id="tab-sms" role="tab">
                <?php echo xlt('SMS Bot'); ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($portalTabVisible): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'portal' ? 'active' : ''; ?>"
               href="?tab=portal" id="tab-portal" role="tab">
                <?php echo xlt('Portal Messages'); ?>
                <?php if ($portalUnread > 0): ?>
                    <span class="badge badge-danger ml-1"><?php echo (int)$portalUnread; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
        <li class="nav-item ml-auto">
            <a class="nav-link <?php echo $activeTab === 'admin' ? 'active' : ''; ?>"
               href="?tab=admin" id="tab-admin" role="tab">
                <?php echo xlt('Admin Stats'); ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white">

        <!-- ═══════════════════════════════════════════════════════════════════
             TAB 1 — MESSAGES (pnotes)
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane <?php echo $activeTab === 'messages' ? 'show active' : ''; ?>" id="pane-messages">

            <!-- Toolbar -->
            <div class="section-toolbar">
                <button class="btn btn-primary btn-sm" type="button"
                        data-toggle="modal" data-target="#composeModal"
                        data-bs-toggle="modal" data-bs-target="#composeModal"
                        onclick="openComposeModal(); return false;">
                    <?php echo xlt('Add New'); ?>
                </button>
                <form class="form-inline mb-0 ml-2" method="get" action="">
                    <input type="hidden" name="tab" value="messages">
                    <select name="activity" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                        <option value="1" <?php echo $activity === '1' ? 'selected' : ''; ?>><?php echo xlt('Active'); ?></option>
                        <option value="0" <?php echo $activity === '0' ? 'selected' : ''; ?>><?php echo xlt('Done'); ?></option>
                        <option value="all" <?php echo $activity === 'all' ? 'selected' : ''; ?>><?php echo xlt('All'); ?></option>
                    </select>
                    <?php if ($isAdmin): ?>
                    <span class="text-muted small ml-2"><?php echo xlt('Showing all users'); ?></span>
                    <?php else: ?>
                    <span class="text-muted small ml-2"><?php echo xlt('Showing your messages'); ?></span>
                    <?php endif; ?>
                </form>
                <!-- pager -->
                <div class="pager-bar ml-auto">
                    <span class="text-muted small">
                        <?php
                        $rangeFrom = $begin + 1;
                        $rangeTo   = min($begin + $pageSize, $noteCount);
                        echo text($rangeFrom . '–' . $rangeTo . ' / ' . $noteCount);
                        ?>
                    </span>
                    <?php if ($begin > 0): ?>
                    <a href="?tab=messages&activity=<?php echo attr_url($activity); ?>&sortby=<?php echo attr_url($sortby); ?>&sortorder=<?php echo attr_url($sortorder); ?>&begin=<?php echo max(0, $begin - $pageSize); ?>"
                       class="btn btn-outline-secondary btn-sm">&laquo;</a>
                    <?php endif; ?>
                    <?php if ($rangeTo < $noteCount): ?>
                    <a href="?tab=messages&activity=<?php echo attr_url($activity); ?>&sortby=<?php echo attr_url($sortby); ?>&sortorder=<?php echo attr_url($sortorder); ?>&begin=<?php echo $begin + $pageSize; ?>"
                       class="btn btn-outline-secondary btn-sm">&raquo;</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($noteCount === 0): ?>
                <p class="text-muted p-3 mb-0"><?php echo xlt('No messages found.'); ?></p>
            <?php else: ?>
            <!-- Bulk delete form wraps the table -->
            <form method="post" id="bulkDeleteForm">
                <input type="hidden" name="csrf_token" value="<?php echo attr($csrf); ?>">
                <input type="hidden" name="task" value="delete">

                <div class="table-responsive">
                <table class="table table-sm table-hover pnotes-table mb-0">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" title="<?php echo xla('Select all'); ?>"></th>
                            <th>
                                <a href="?tab=messages&activity=<?php echo attr_url($activity); ?>&sortby=pnotes.date&sortorder=<?php echo $sortby === 'pnotes.date' && $sortorder === 'asc' ? 'desc' : 'asc'; ?>">
                                    <?php echo xlt('Date'); ?>
                                    <?php echo $sortby === 'pnotes.date' ? ($sortorder === 'asc' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?tab=messages&activity=<?php echo attr_url($activity); ?>&sortby=patient_data.lname&sortorder=<?php echo $sortby === 'patient_data.lname' && $sortorder === 'asc' ? 'desc' : 'asc'; ?>">
                                    <?php echo xlt('Patient'); ?>
                                </a>
                            </th>
                            <th class="hide-xs">
                                <a href="?tab=messages&activity=<?php echo attr_url($activity); ?>&sortby=pnotes.title&sortorder=<?php echo $sortby === 'pnotes.title' && $sortorder === 'asc' ? 'desc' : 'asc'; ?>">
                                    <?php echo xlt('Type'); ?>
                                </a>
                            </th>
                            <th><?php echo xlt('Subject / Preview'); ?></th>
                            <th class="hide-xs"><?php echo xlt('From'); ?></th>
                            <th>
                                <a href="?tab=messages&activity=<?php echo attr_url($activity); ?>&sortby=pnotes.message_status&sortorder=<?php echo $sortby === 'pnotes.message_status' && $sortorder === 'asc' ? 'desc' : 'asc'; ?>">
                                    <?php echo xlt('Status'); ?>
                                </a>
                            </th>
                            <th><?php echo xlt('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($note = sqlFetchArray($noteRows)): ?>
                    <?php
                        $nid  = (int)$note['id'];
                        $npid = (int)$note['pid'];
                        $patName = trim(($note['patient_data_fname'] ?? '') . ' ' . ($note['patient_data_lname'] ?? ''));
                        if ($patName === '') {
                            $patName = trim(($note['users_fname'] ?? '') . ' ' . ($note['users_lname'] ?? ''));
                        }
                        $fromName = trim(($note['users_fname'] ?? '') . ' ' . ($note['users_lname'] ?? ''));
                        $preview  = mb_substr(strip_tags((string)($note['body'] ?? '')), 0, 80);
                        $isNew    = ($note['message_status'] === 'New');
                    ?>
                    <tr class="<?php echo $isNew ? 'font-weight-bold' : ''; ?>">
                        <td><input type="checkbox" name="delete_id[]" value="<?php echo attr($nid); ?>"></td>
                        <td class="text-nowrap">
                            <?php echo text(date('M j g:ia', strtotime((string)($note['date'] ?? '')))); ?>
                        </td>
                        <td>
                            <?php if ($npid > 0): ?>
                                <a href="#" onclick="top.restoreSession();window.top.RTop.location='<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=<?php echo attr_url($npid); ?>';return false;"><?php echo text($patName); ?></a>
                            <?php else: ?>
                                <span class="text-muted"><?php echo xlt('General'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="hide-xs"><small><?php echo text($note['title'] ?? ''); ?></small></td>
                        <td>
                            <a href="#" class="text-dark" onclick="openNoteModal(<?php echo attr_js($nid); ?>, <?php echo attr_js($npid); ?>, <?php echo attr_js((string)($note['title'] ?? '')); ?>, <?php echo attr_js((string)($note['message_status'] ?? '')); ?>, <?php echo attr_js((string)($note['body'] ?? '')); ?>); return false;">
                                <?php echo text($preview ?: '—'); ?>
                            </a>
                        </td>
                        <td class="hide-xs"><small><?php echo text($fromName); ?></small></td>
                        <td><?php echo statusBadge((string)($note['message_status'] ?? '')); ?></td>
                        <td class="text-nowrap">
                            <div class="dropdown d-inline">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo xlt('Set'); ?>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <?php foreach (['New','Open','Pending','Done'] as $st): ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo attr($csrf); ?>">
                                        <input type="hidden" name="task" value="status">
                                        <input type="hidden" name="note_id" value="<?php echo attr($nid); ?>">
                                        <input type="hidden" name="new_status" value="<?php echo attr($st); ?>">
                                        <button type="submit" class="dropdown-item"><?php echo xlt($st); ?></button>
                                    </form>
                                    <?php endforeach; ?>
                                    <?php if ($isAdmin || checkPnotesNoteId($nid, $authUser)): ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#"
                                       onclick="if(confirm('<?php echo xla('Delete this message?'); ?>')){
                                           var f=document.getElementById('bulkDeleteForm');
                                           var inp=document.createElement('input');
                                           inp.type='hidden'; inp.name='delete_id[]'; inp.value='<?php echo attr_js($nid); ?>';
                                           f.appendChild(inp); f.submit();} return false;">
                                        <?php echo xlt('Delete'); ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                </div>

                <!-- bulk delete bar -->
                <div class="section-toolbar justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('<?php echo xla('Delete selected messages?'); ?>')">
                            <?php echo xlt('Delete Selected'); ?>
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div><!-- /pane-messages -->

        <!-- ═══════════════════════════════════════════════════════════════════
             TAB 2 — SECURE CHAT
             ═══════════════════════════════════════════════════════════════════ -->
        <?php if ($hasChatService): ?>
        <div class="tab-pane <?php echo $activeTab === 'chat' ? 'show active' : ''; ?>" id="pane-chat">
            <div class="section-toolbar">
                <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php"
                   class="btn btn-primary btn-sm" onclick="top.restoreSession()">
                    <?php echo xlt('New Chat / Send Link'); ?>
                </a>
                <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php"
                   class="btn btn-outline-secondary btn-sm" target="_blank" onclick="top.restoreSession()">
                    <?php echo xlt('Open in New Window'); ?>
                </a>
                <span class="text-muted small ml-2"><?php echo xlt('Patients with recent Secure Chat activity'); ?></span>
                <span class="ml-auto text-muted small"><?php echo xlt('Synced from MedEx portal mailbox'); ?></span>
            </div>

            <div class="embedded-pane m-3 mt-0">
                <iframe src="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php"
                        title="<?php echo xla('Secure Chat'); ?>"></iframe>
            </div>

            <?php if (empty($chatSessions)): ?>
                <p class="text-muted p-3 mb-0"><?php echo xlt('No secure chat sessions found. Send a chat link to a patient to get started.'); ?></p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th style="font-size:.78rem;text-transform:uppercase;background:#f8f9fa"><?php echo xlt('Patient'); ?></th>
                        <th style="font-size:.78rem;text-transform:uppercase;background:#f8f9fa" class="hide-xs"><?php echo xlt('Last Activity'); ?></th>
                        <th style="font-size:.78rem;text-transform:uppercase;background:#f8f9fa"><?php echo xlt('Messages'); ?></th>
                        <th style="font-size:.78rem;text-transform:uppercase;background:#f8f9fa"><?php echo xlt('Status'); ?></th>
                        <th style="font-size:.78rem;text-transform:uppercase;background:#f8f9fa"><?php echo xlt('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($chatSessions as $cs):
                    $csName     = trim(($cs['fname'] ?? '') . ' ' . ($cs['lname'] ?? ''));
                    if ($csName === '') {
                        $csName = 'PID ' . (int)$cs['pid'];
                    }
                    $lastAct    = $cs['last_activity'] ?? '';
                    $isRecent   = $lastAct && strtotime($lastAct) > strtotime('-24 hours');
                    $isToday    = $lastAct && date('Y-m-d', strtotime($lastAct)) === date('Y-m-d');
                    $chatUrl    = attr($GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php?pid=' . (int)$cs['pid']);
                ?>
                <tr class="chat-row" onclick="top.restoreSession();window.location='<?php echo $chatUrl; ?>'">
                    <td>
                        <strong><?php echo text($csName); ?></strong>
                        <small class="text-muted d-block">PID <?php echo (int)$cs['pid']; ?></small>
                    </td>
                    <td class="hide-xs text-nowrap">
                        <?php
                        if ($lastAct) {
                            echo text(date('M j, Y g:ia', strtotime($lastAct)));
                        }
                        ?>
                    </td>
                    <td>
                        <span class="badge badge-secondary chat-badge"><?php echo (int)$cs['msg_count']; ?></span>
                    </td>
                    <td>
                        <?php if ($isToday): ?>
                            <span class="badge badge-success">Today</span>
                        <?php elseif ($isRecent): ?>
                            <span class="badge badge-info"><?php echo xlt('Recent'); ?></span>
                        <?php else: ?>
                            <span class="badge badge-light"><?php echo xlt('Past'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo $chatUrl; ?>" class="btn btn-outline-primary btn-sm"
                           onclick="top.restoreSession(); event.stopPropagation();">
                            <?php echo xlt('Open Chat'); ?>
                        </a>
                        <?php if ($cs['pid'] > 0): ?>
                        <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/patient_file/summary/demographics.php?set_pid=<?php echo attr_url((int)$cs['pid']); ?>"
                           class="btn btn-outline-secondary btn-sm hide-xs"
                           onclick="top.restoreSession(); event.stopPropagation();">
                            <?php echo xlt('Chart'); ?>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div><!-- /pane-chat -->
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════════════
             TAB 3 — SMS BOT
             ═══════════════════════════════════════════════════════════════════ -->
        <?php if ($hasSMSService): ?>
        <div class="tab-pane <?php echo $activeTab === 'sms' ? 'show active' : ''; ?>" id="pane-sms">
            <div class="section-toolbar">
                <span class="text-muted small"><?php echo xlt('MedEx SMS Bot — select a patient in the main chart to launch their SMS thread, or open the full SMS dashboard below.'); ?></span>
                <?php if ($ssoToken): ?>
                <span class="ml-auto">
                    <a href="<?php echo attr($medexBaseUrl . '/cart/upload/index.php?route=information/sms_zone&token=' . urlencode($ssoToken)); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <?php echo xlt('Open SMS Bot in New Window'); ?>
                    </a>
                </span>
                <?php endif; ?>
            </div>
            <?php if ($ssoToken): ?>
            <?php
            $smsDashUrl = $medexBaseUrl . '/cart/upload/index.php?route=information/sms_zone&token=' . urlencode($ssoToken);
            ?>
            <div style="height:600px;overflow:hidden;">
                <iframe src="<?php echo attr($smsDashUrl); ?>"
                        style="width:100%;height:100%;border:none;"
                        title="<?php echo xla('MedEx SMS Dashboard'); ?>"></iframe>
            </div>
            <?php else: ?>
            <div class="p-3">
                <p class="text-muted"><?php echo xlt('MedEx session unavailable. Please check your MedEx connection in Admin → Settings.'); ?></p>
                <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/admin/settings.php"
                   class="btn btn-secondary btn-sm"><?php echo xlt('MedEx Settings'); ?></a>
            </div>
            <?php endif; ?>
        </div><!-- /pane-sms -->
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════════════
             TAB 4 — PORTAL MESSAGES
             Both types persist without MedEx:
             • onsite_mail  = patient-initiated secure portal messages (OpenEMR core)
             • medex_secure_chat_tokens/log = provider-initiated secure chat (module)
             ═══════════════════════════════════════════════════════════════════ -->
        <?php if ($portalTabVisible): ?>
        <div class="tab-pane <?php echo $activeTab === 'portal' ? 'show active' : ''; ?>" id="pane-portal">
            <div class="section-toolbar">
                <strong><?php echo xlt('Portal Messaging'); ?></strong>
                <?php if ($portalUnread > 0): ?>
                    <span class="badge badge-danger ml-1"><?php echo (int)$portalUnread; ?> <?php echo xlt('unread'); ?></span>
                <?php endif; ?>
                <span class="ml-auto" style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php if ($hasPortalAccess): ?>
                    <a href="<?php echo attr($portalInboxUrl); ?>" target="_blank" class="btn btn-outline-secondary btn-sm" onclick="top.restoreSession()">
                        <?php echo xlt('Open Portal Inbox'); ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($hasChatService): ?>
                    <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php"
                       target="_blank" class="btn btn-outline-primary btn-sm" onclick="top.restoreSession()">
                        <?php echo xlt('Send Secure Chat Link'); ?>
                    </a>
                    <?php endif; ?>
                </span>
            </div>

            <?php if (!$portalSyncTableExists && !$hasPortalAccess): ?>
            <div class="p-3">
                <div class="alert alert-warning mb-0">
                    <?php echo xlt('Portal messaging requires either portal access (Patients → Portal ACL) or an active portal sync table. Neither is available on this instance.'); ?>
                </div>
            </div>
            <?php else: ?>

            <!-- ── Section A: Patient-Initiated Portal Messages (onsite_mail) ── -->
            <?php if ($hasPortalAccess): ?>
            <div class="p-3 pb-1">
                <h6 class="mb-2 d-flex align-items-center">
                    <?php echo xlt('Patient Secure Messages'); ?>
                    <span class="text-muted small ml-2" style="font-weight:normal;font-size:0.75rem;">
                        <?php echo xlt('(patient-initiated via portal — stored in OpenEMR, persist without MedEx)'); ?>
                    </span>
                    <?php if ($portalMailNew > 0): ?>
                    <span class="badge badge-danger ml-2"><?php echo (int)$portalMailNew; ?> <?php echo xlt('new'); ?></span>
                    <?php endif; ?>
                </h6>
                <?php if (empty($portalMailMessages)): ?>
                <p class="text-muted small"><?php echo xlt('No portal messages yet.'); ?></p>
                <?php else: ?>
                <div class="table-responsive">
                <table class="table table-sm table-hover pnotes-table mb-3">
                    <thead>
                        <tr>
                            <th><?php echo xlt('Date'); ?></th>
                            <th><?php echo xlt('From'); ?></th>
                            <th><?php echo xlt('To'); ?></th>
                            <th><?php echo xlt('Subject'); ?></th>
                            <th><?php echo xlt('Status'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($portalMailMessages as $pm):
                        $pmDate    = $pm['date'] ? date('M j Y g:ia', strtotime($pm['date'])) : '—';
                        $pmFrom    = text($pm['sender_name'] ?: (trim(($pm['fname'] ?? '') . ' ' . ($pm['lname'] ?? '')) ?: 'Patient'));
                        $pmTo      = text($pm['recipient_name'] ?: '—');
                        $pmTitle   = text($pm['title'] ?: '(no subject)');
                        $pmStatus  = $pm['message_status'];
                        $pmBadge   = ($pmStatus === 'New' || stripos($pmStatus, 'new') !== false) ? 'badge-danger' : 'badge-secondary';
                        $pmPid     = (int)($pm['pid'] ?? 0);
                    ?>
                    <tr class="<?php echo ($pmStatus === 'New' || stripos($pmStatus, 'new') !== false) ? 'table-warning' : ''; ?>">
                        <td class="text-nowrap" style="font-size:0.78rem;"><?php echo text($pmDate); ?></td>
                        <td><?php echo $pmFrom; ?></td>
                        <td><?php echo $pmTo; ?></td>
                        <td><?php echo $pmTitle; ?></td>
                        <td><span class="badge <?php echo $pmBadge; ?>"><?php echo text($pmStatus); ?></span></td>
                        <td>
                            <?php if ($pmPid > 0): ?>
                            <a href="<?php echo attr($GLOBALS['webroot']); ?>/portal/messaging/messages.php?pid=<?php echo (int)$pmPid; ?>"
                               target="_blank" class="btn btn-outline-primary btn-xs" style="font-size:0.72rem;padding:1px 6px;" onclick="top.restoreSession()">
                                <?php echo xlt('Reply'); ?>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- ── Section B: Provider-Initiated MedEx Secure Chat Tokens ── -->
            <?php if ($hasChatService): ?>
            <?php
            $chatTokenResult = sqlStatement(
                "SELECT t.pid, t.created_at, t.expires_at, t.used_at, t.method, t.user_initials,
                        t.is_provider, pd.fname, pd.lname,
                        (SELECT COUNT(*) FROM medex_secure_chat_log l WHERE l.pid = t.pid
                         AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS msg_count
                 FROM medex_secure_chat_tokens t
                 LEFT JOIN patient_data pd ON t.pid = pd.pid
                 WHERE t.expires_at > NOW() OR t.used_at IS NOT NULL
                 ORDER BY t.created_at DESC LIMIT 50"
            );
            $chatTokens = [];
            while ($r = sqlFetchArray($chatTokenResult)) {
                $chatTokens[] = $r;
            }
            ?>
            <div class="p-3 pt-1">
                <h6 class="mb-2 d-flex align-items-center">
                    <?php echo xlt('Provider-Initiated Secure Chat'); ?>
                    <span class="text-muted small ml-2" style="font-weight:normal;font-size:0.75rem;">
                        <?php echo xlt('(provider-sent link — stored in MedEx module tables)'); ?>
                    </span>
                </h6>
                <?php if (empty($chatTokens)): ?>
                <p class="text-muted small"><?php echo xlt('No secure chat sessions found.'); ?></p>
                <?php else: ?>
                <div class="table-responsive">
                <table class="table table-sm table-hover pnotes-table mb-2">
                    <thead>
                        <tr>
                            <th><?php echo xlt('Patient'); ?></th>
                            <th><?php echo xlt('Sent'); ?></th>
                            <th><?php echo xlt('Via'); ?></th>
                            <th><?php echo xlt('Msgs'); ?></th>
                            <th><?php echo xlt('Status'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($chatTokens as $ct):
                        $ctName    = text(trim(($ct['fname'] ?? '') . ' ' . ($ct['lname'] ?? '')) ?: 'PID ' . (int)$ct['pid']);
                        $ctSent    = $ct['created_at'] ? date('M j g:ia', strtotime($ct['created_at'])) : '—';
                        $ctVia     = text(strtoupper($ct['method'] ?? '—'));
                        $ctActive  = strtotime($ct['expires_at']) > time();
                        $ctBadge   = $ctActive ? 'badge-success' : 'badge-secondary';
                        $ctLabel   = $ctActive ? xlt('Active') : xlt('Expired');
                        $ctUsed    = $ct['used_at'] ? date('M j g:ia', strtotime($ct['used_at'])) : null;
                    ?>
                    <tr>
                        <td><?php echo $ctName; ?> <small class="text-muted">PID <?php echo (int)$ct['pid']; ?></small></td>
                        <td class="text-nowrap" style="font-size:0.78rem;"><?php echo text($ctSent); ?></td>
                        <td><?php echo $ctVia; ?></td>
                        <td><?php echo (int)$ct['msg_count']; ?></td>
                        <td>
                            <span class="badge <?php echo $ctBadge; ?>"><?php echo $ctLabel; ?></span>
                            <?php if ($ctUsed): ?>
                                <small class="text-muted d-block" style="font-size:0.7rem;"><?php echo xlt('Used'); ?>: <?php echo text($ctUsed); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php?pid=<?php echo (int)$ct['pid']; ?>"
                               target="_blank" class="btn btn-outline-primary btn-xs" style="font-size:0.72rem;padding:1px 6px;" onclick="top.restoreSession()">
                                <?php echo xlt('Chat'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!$hasPortalAccess && !$hasChatService): ?>
            <div class="p-3">
                <div class="alert alert-info mb-0">
                    <?php echo xlt('No messaging services are active. Grant Patients → Portal ACL for portal messages, or subscribe to Secure Chat for provider-initiated chat.'); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div><!-- /pane-portal -->
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════════════
             TAB 5 — ADMIN STATS
             ═══════════════════════════════════════════════════════════════════ -->
        <?php if ($isAdmin): ?>
        <div class="tab-pane <?php echo $activeTab === 'admin' ? 'show active' : ''; ?>" id="pane-admin">
            <div class="section-toolbar">
                <strong><?php echo xlt('Provider Message Load'); ?></strong>
                <span class="ml-auto">
                    <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/public/dashboard.php"
                       class="btn btn-outline-secondary btn-sm" onclick="top.restoreSession()">
                        <?php echo xlt('MedEx Dashboard'); ?>
                    </a>
                </span>
            </div>

            <div class="p-3">
                <?php if (!$portalSyncTableExists): ?>
                <div class="alert alert-info py-2 mb-3" style="font-size:0.85rem;">
                    <strong><?php echo xlt('Portal Sync not active'); ?></strong> —
                    <?php echo xlt('The Portal Messages tab is hidden from all users until portal sync is enabled for this tenant. To enable it, provision the medex_chat_sync table on this OpenEMR instance.'); ?>
                </div>
                <?php endif; ?>

                <!-- Appointment activity — last 3 days -->
                <?php if ($apptTotal3d > 0): ?>
                <h6 class="mb-2"><?php echo xlt('Appointment Activity — Last 3 Days'); ?></h6>
                <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered admin-stat-table">
                    <thead><tr>
                        <th><?php echo xlt('Status'); ?></th>
                        <th><?php echo xlt('Count'); ?></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($apptStats as $code => $cnt):
                        $label = $apptStatLabels[$code] ?? $code;
                        $color = $apptStatColors[$code] ?? 'secondary';
                    ?>
                    <tr>
                        <td><span class="badge badge-<?php echo attr($color); ?>"><?php echo text($label); ?></span></td>
                        <td><?php echo (int)$cnt; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="font-weight-bold">
                        <td><?php echo xlt('Total'); ?></td>
                        <td><?php echo (int)$apptTotal3d; ?></td>
                    </tr>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>

                <!-- Portal message summary -->
                <?php if ($hasPortalAccess && ($portalMailNew > 0 || !empty($portalMailMessages))): ?>
                <h6 class="mb-2"><?php echo xlt('Portal Secure Messages — All Users'); ?></h6>
                <?php
                $pmAdminResult = sqlStatement(
                    "SELECT om.recipient_name, COUNT(*) AS total,
                            SUM(IF(om.message_status LIKE '%new%',1,0)) AS unread
                     FROM onsite_mail om
                     WHERE om.deleted = 0
                     GROUP BY om.recipient_name ORDER BY unread DESC, total DESC LIMIT 20"
                );
                ?>
                <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered admin-stat-table">
                    <thead><tr>
                        <th><?php echo xlt('Assigned To'); ?></th>
                        <th><?php echo xlt('Total'); ?></th>
                        <th><?php echo xlt('Unread'); ?></th>
                    </tr></thead>
                    <tbody>
                    <?php while ($pmr = sqlFetchArray($pmAdminResult)): ?>
                    <tr>
                        <td><?php echo text($pmr['recipient_name'] ?: '—'); ?></td>
                        <td><?php echo (int)$pmr['total']; ?></td>
                        <td><?php echo (int)$pmr['unread'] > 0
                            ? '<span class="badge badge-danger">' . (int)$pmr['unread'] . '</span>'
                            : '<span class="text-muted">0</span>'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>

                <h6><?php echo xlt('Messages by User'); ?></h6>
                <div class="table-responsive">
                <table class="table table-sm table-bordered admin-stat-table mb-4">
                    <thead>
                        <tr>
                            <th><?php echo xlt('User'); ?></th>
                            <th><?php echo xlt('Assigned'); ?></th>
                            <th><?php echo xlt('Unread'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($adminStats as $stat): ?>
                    <tr>
                        <td><?php echo text(trim(($stat['fname'] ?? '') . ' ' . ($stat['lname'] ?? '')) ?: $stat['username']); ?></td>
                        <td><?php echo (int)$stat['total']; ?></td>
                        <td>
                            <?php if ((int)$stat['unread'] > 0): ?>
                                <span class="badge badge-danger"><?php echo (int)$stat['unread']; ?></span>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($adminStats)): ?>
                    <tr><td colspan="3" class="text-muted"><?php echo xlt('No data.'); ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                </div>

                <!-- Secure Chat session summary -->
                <?php if ($hasChatService): ?>
                <h6><?php echo xlt('Secure Chat — All Active Sessions'); ?></h6>
                <?php
                $allChatResult = sqlStatement(
                    "SELECT mcs.pid, COUNT(mcs.id) AS msg_count, MAX(mcs.sync_date) AS last_activity,
                            pd.fname, pd.lname
                     FROM medex_chat_sync mcs
                     LEFT JOIN patient_data pd ON mcs.pid = pd.pid
                     GROUP BY mcs.pid
                     ORDER BY last_activity DESC
                     LIMIT 50"
                );
                $allChats = [];
                while ($r = sqlFetchArray($allChatResult)) {
                    $allChats[] = $r;
                }
                ?>
                <div class="table-responsive">
                <table class="table table-sm table-bordered admin-stat-table mb-4">
                    <thead>
                        <tr>
                            <th><?php echo xlt('Patient'); ?></th>
                            <th><?php echo xlt('Messages Synced'); ?></th>
                            <th><?php echo xlt('Last Activity'); ?></th>
                            <th><?php echo xlt('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allChats as $ac):
                        $acName = trim(($ac['fname'] ?? '') . ' ' . ($ac['lname'] ?? '')) ?: 'PID ' . (int)$ac['pid'];
                    ?>
                    <tr>
                        <td><?php echo text($acName); ?> <small class="text-muted">PID <?php echo (int)$ac['pid']; ?></small></td>
                        <td><?php echo (int)$ac['msg_count']; ?></td>
                        <td><?php echo text($ac['last_activity'] ? date('M j Y g:ia', strtotime($ac['last_activity'])) : '—'); ?></td>
                        <td>
                            <a href="<?php echo attr($GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php?pid=' . (int)$ac['pid']); ?>"
                               class="btn btn-outline-primary btn-sm" onclick="top.restoreSession()">
                                <?php echo xlt('Chat'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($allChats)): ?>
                    <tr><td colspan="4" class="text-muted"><?php echo xlt('No synced chat sessions found.'); ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>

                <!-- MedEx dashboard embed for full SMS stats -->
                <?php if ($ssoToken): ?>
                <h6><?php echo xlt('MedEx Admin Dashboard'); ?></h6>
                <?php
                $adminDashUrl = $medexBaseUrl . '/cart/upload/index.php?route=information/admin_dashboard&token=' . urlencode($ssoToken);
                ?>
                <div style="height:500px;overflow:hidden;border:1px solid #dee2e6;border-radius:4px;">
                    <iframe src="<?php echo attr($adminDashUrl); ?>"
                            style="width:100%;height:100%;border:none;"
                            title="<?php echo xla('MedEx Admin Dashboard'); ?>"></iframe>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /pane-admin -->
        <?php endif; ?>

    </div><!-- /tab-content -->
</div><!-- /container -->

<!-- ═══════════════════════════════════════════════════════════════════════════
     COMPOSE MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="composeModal" tabindex="-1" role="dialog" aria-labelledby="composeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="composeModalLabel"><?php echo xlt('Compose Message'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo attr($csrf); ?>">
        <input type="hidden" name="task" value="compose">
        <input type="hidden" name="note_pid" id="compose_pid" value="">
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label><?php echo xlt('Patient'); ?></label>
                    <input type="text" id="compose_patient_display" class="form-control"
                           placeholder="<?php echo xla('Start typing name or PID…'); ?>"
                           autocomplete="off">
                    <div id="patientSuggestions" class="list-group position-absolute" style="z-index:9999;max-height:160px;overflow-y:auto;display:none;width:100%;"></div>
                </div>
                <div class="col-sm-3 form-group">
                    <label><?php echo xlt('Type'); ?></label>
                    <select name="note_title" class="form-control">
                        <?php
                        $typeResult = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id='note_type' AND activity=1 ORDER BY seq, title");
                        while ($typeRow = sqlFetchArray($typeResult)) {
                            echo '<option value="' . attr($typeRow['option_id']) . '">' . text($typeRow['title']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-3 form-group">
                    <label><?php echo xlt('Status'); ?></label>
                    <select name="note_status" class="form-control">
                        <?php
                        $statusResult = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id='message_status' AND activity=1 ORDER BY seq, title");
                        while ($statusRow = sqlFetchArray($statusResult)) {
                            $sel = ($statusRow['option_id'] === 'New') ? ' selected' : '';
                            echo '<option value="' . attr($statusRow['option_id']) . '"' . $sel . '>' . text($statusRow['title']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label><?php echo xlt('Assign To'); ?></label>
                <select name="assigned_to" class="form-control">
                    <?php foreach ($allUsers as $u): ?>
                    <option value="<?php echo attr($u['username']); ?>"
                        <?php echo ($u['username'] === $authUser) ? ' selected' : ''; ?>>
                        <?php echo text(trim(($u['lname'] ?? '') . ', ' . ($u['fname'] ?? ''))); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?php echo xlt('Message'); ?></label>
                <textarea name="note_body" class="form-control" rows="5" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
            <button type="submit" class="btn btn-primary"><?php echo xlt('Send'); ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- NOTE VIEWER MODAL -->
<div class="modal fade" id="noteViewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo xlt('Message Detail'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
          <p class="mb-1"><strong id="nv_type"></strong> &nbsp; <span id="nv_status"></span></p>
          <hr class="mt-1">
          <p id="nv_body" style="white-space:pre-wrap;"></p>
      </div>
      <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal"><?php echo xlt('Close'); ?></button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
    function showModalById(id) {
        var el = document.getElementById(id);
        if (!el) {
            return;
        }
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.modal) {
            window.jQuery('#' + id).modal('show');
        }
    }

    window.openComposeModal = function () {
        showModalById('composeModal');
    };

    // ── Select all checkbox ──────────────────────────────────────────────────
    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('input[name="delete_id[]"]').forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
        });
    }

    // ── Note viewer modal ────────────────────────────────────────────────────
    window.openNoteModal = function (nid, pid, type, status, body) {
        document.getElementById('nv_type').textContent = type;
        document.getElementById('nv_status').textContent = status;
        document.getElementById('nv_body').textContent = body;
        showModalById('noteViewModal');
    };

    // ── Patient autocomplete in compose modal ────────────────────────────────
    var patInput   = document.getElementById('compose_patient_display');
    var patPidHid  = document.getElementById('compose_pid');
    var suggestions = document.getElementById('patientSuggestions');

    if (patInput) {
        var debounceTimer;
        patInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var q = patInput.value.trim();
            if (q.length < 2) { suggestions.style.display = 'none'; return; }
            debounceTimer = setTimeout(function () {
                fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/api/patient_search.php?q=' + encodeURIComponent(q) + '&csrf_token=<?php echo attr_url($csrf); ?>')
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        suggestions.innerHTML = '';
                        if (!data.patients || !data.patients.length) { suggestions.style.display = 'none'; return; }
                        data.patients.slice(0, 8).forEach(function (p) {
                            var a = document.createElement('a');
                            a.href = '#';
                            a.className = 'list-group-item list-group-item-action py-1';
                            a.textContent = p.lname + ', ' + p.fname + ' (PID ' + p.pid + ')';
                            a.addEventListener('click', function (e) {
                                e.preventDefault();
                                patInput.value = p.lname + ', ' + p.fname;
                                patPidHid.value = p.pid;
                                suggestions.style.display = 'none';
                            });
                            suggestions.appendChild(a);
                        });
                        suggestions.style.display = 'block';
                    })
                    .catch(function () { suggestions.style.display = 'none'; });
            }, 280);
        });

        document.addEventListener('click', function (e) {
            if (!patInput.contains(e.target)) { suggestions.style.display = 'none'; }
        });
    }
})();
</script>

</body>
</html>
