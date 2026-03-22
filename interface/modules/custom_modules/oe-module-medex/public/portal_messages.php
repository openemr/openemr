<?php

/**
 * Portal Messages Manager
 * 
 * View and manage portal messages synced from MedEx secure chat
 * Shows messages from onsite_mail table that originated from MedEx
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ray Magauran
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../globals.php");
require_once($GLOBALS['srcdir'] . "/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Require portal/messages ACL
if (!AclMain::aclCheckCore('patients', 'portal')) {
    echo "<html><body>";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>";
    echo "</body></html>";
    exit;
}

// Get stats
$syncStats = sqlQuery(
    "SELECT COUNT(*) as total_synced, 
            MAX(sync_date) as last_sync
     FROM medex_chat_sync"
);

$recentMessages = sqlStatement(
    "SELECT om.id, om.date, om.owner, om.sender_name, om.recipient_name, 
            LEFT(om.body, 100) as preview, om.message_status,
            mcs.medex_msg_uid, mcs.sync_date
     FROM onsite_mail om
     INNER JOIN medex_chat_sync mcs ON om.id = mcs.openemr_mail_id
     ORDER BY om.date DESC
     LIMIT 50"
);

$messages = [];
while ($row = sqlFetchArray($recentMessages)) {
    $messages[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Portal Messages'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .container-fluid { padding: 20px; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .stat-card h4 { margin-top: 0; color: #007bff; }
        .message-table { width: 100%; }
        .message-table th { background: #007bff; color: white; padding: 10px; text-align: left; }
        .message-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .message-table tr:hover { background: #f5f5f5; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; }
        .badge-new { background: #28a745; color: white; }
        .badge-read { background: #6c757d; color: white; }
        .btn-view { background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .btn-view:hover { background: #0056b3; }
        .empty-state { text-align: center; padding: 40px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2><?php echo xlt('MedEx Portal Messages'); ?></h2>
        <p class="text-muted"><?php echo xlt('Messages synced from MedEx secure chat to OpenEMR portal'); ?></p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <h4><i class="fa fa-sync"></i> <?php echo xlt('Sync Statistics'); ?></h4>
                    <p><strong><?php echo xlt('Total Messages Synced'); ?>:</strong> <?php echo text($syncStats['total_synced'] ?? 0); ?></p>
                    <p><strong><?php echo xlt('Last Sync'); ?>:</strong> 
                        <?php 
                        if (!empty($syncStats['last_sync'])) {
                            echo text(date('M j, Y g:i A', strtotime($syncStats['last_sync'])));
                        } else {
                            echo xlt('No messages synced yet');
                        }
                        ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <h4><i class="fa fa-info-circle"></i> <?php echo xlt('Integration Status'); ?></h4>
                    <p><?php echo xlt('Portal messaging integration is active. Messages sent via MedEx secure chat automatically sync to the portal.'); ?></p>
                    <p>
                        <a href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fa fa-external-link"></i> <?php echo xlt('View Portal Messages'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <h3><?php echo xlt('Recent Synced Messages'); ?></h3>
        
        <?php if (empty($messages)): ?>
            <div class="empty-state">
                <i class="fa fa-comments fa-3x"></i>
                <h4><?php echo xlt('No Messages Yet'); ?></h4>
                <p><?php echo xlt('Messages sent via MedEx secure chat will appear here once synced.'); ?></p>
                <p><a href="<?php echo $GLOBALS['web_root']; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php" class="btn btn-primary">
                    <i class="fa fa-paper-plane"></i> <?php echo xlt('Send Secure Chat'); ?>
                </a></p>
            </div>
        <?php else: ?>
            <table class="message-table table table-striped">
                <thead>
                    <tr>
                        <th><?php echo xlt('Date'); ?></th>
                        <th><?php echo xlt('From'); ?></th>
                        <th><?php echo xlt('To'); ?></th>
                        <th><?php echo xlt('Preview'); ?></th>
                        <th><?php echo xlt('Status'); ?></th>
                        <th><?php echo xlt('Synced'); ?></th>
                        <th><?php echo xlt('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?php echo text(date('m/d/Y H:i', strtotime($msg['date']))); ?></td>
                            <td><?php echo text($msg['sender_name']); ?></td>
                            <td><?php echo text($msg['recipient_name']); ?></td>
                            <td><?php echo text($msg['preview']); ?>...</td>
                            <td>
                                <span class="badge <?php echo ($msg['message_status'] === 'New') ? 'badge-new' : 'badge-read'; ?>">
                                    <?php echo text($msg['message_status']); ?>
                                </span>
                            </td>
                            <td><?php echo text(date('m/d H:i', strtotime($msg['sync_date']))); ?></td>
                            <td>
                                <a href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php?id=<?php echo attr($msg['id']); ?>" 
                                   class="btn-view" target="_blank">
                                    <?php echo xlt('View'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div class="mt-4">
            <h4><?php echo xlt('Quick Links'); ?></h4>
            <ul>
                <li><a href="<?php echo $GLOBALS['web_root']; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php"><?php echo xlt('Send Secure Chat Link'); ?></a></li>
                <li><a href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php" target="_blank"><?php echo xlt('OpenEMR Portal Messages'); ?></a></li>
                <li><a href="<?php echo $GLOBALS['web_root']; ?>/interface/modules/custom_modules/oe-module-medex/OPENEMR_PORTAL_INTEGRATION.md" target="_blank"><?php echo xlt('Integration Documentation'); ?></a></li>
            </ul>
        </div>
    </div>
</body>
</html>
