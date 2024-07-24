<?php

/**
 * Weno users id.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;


if (!AclMain::aclCheckCore('admin', 'super')) {
    // a recheck as was checked in setup script that calls this script in an iframe.
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Must be an Admin")]);
    exit;
}
if ($_POST) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$fetch = sqlStatement("SELECT id,username,lname,fname,weno_prov_id,facility,facility_id FROM `users` WHERE active = 1 and authorized = 1");
while ($row = sqlFetchArray($fetch)) {
    $usersData[] = $row;
}

if (($_POST['save'] ?? false) == 'true') {
    foreach ($_POST['weno_provider_id'] as $id => $weno_prov_id) {
        sqlStatement("UPDATE `users` SET weno_prov_id = ? WHERE id = ?", [$weno_prov_id, $id]);
        sqlQuery(
            "INSERT INTO `user_settings` (`setting_label`,`setting_value`, `setting_user`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value` = ?, `setting_user` = ?",
            array('global:weno_provider_uid', $weno_prov_id, $id, $weno_prov_id, $id)
        );
    }

    unset($_POST['save']);
    Header("Location: " . $GLOBALS['webroot'] . "/interface/modules/custom_modules/oe-module-weno/templates/weno_users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt("Prescriber Weno Ids"); ?></title>
    <?php Header::setupHeader(); ?>
    <script src="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/public/assets/js/synch.js"></script>
    <script>
        $(function () {
            const persistChange = document.querySelectorAll('.persist-uid');
            const successMsg = <?php echo xlj('Auto Saved!'); ?>;
            let isPersistEvent = false;
            persistChange.forEach(persist => {
                persist.addEventListener('change', () => {
                    top.restoreSession();
                    syncAlertMsg(successMsg, 750, 'success').then(() => {
                        isPersistEvent = true;
                        $("#form_save_users").click();
                    });
                });
            });
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <h6 class="text-center"><small><cite><?php echo xlt("Auto Save On for Weno UID."); ?></cite></small></h6>
        <form method="POST">
            <input type="hidden" id="csrf_token_form" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
            <table class="table table-sm table-hover table-striped table-borderless">
                <thead>
                <tr>
                    <th><?php echo xlt("ID"); ?></th>
                    <th><?php echo xlt("Username"); ?></th>
                    <th><?php echo xlt("Last"); ?></th>
                    <th><?php echo xlt("First"); ?></th>
                    <th><?php echo xlt("Weno User"); ?></th>
                    <th><?php echo xlt("Facility"); ?></th>
                    <th><?php echo xlt("Edit"); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($usersData as $user) {
                    if (empty($user['facility'])) {
                        $user['facility'] = xlt("Please add Users Default Facility");
                    }
                    ?>
                    <td><?php echo text($user['id']); ?></td>
                    <td><?php echo text($user['username']); ?></td>
                    <td><?php echo text($user['lname']); ?></td>
                    <td><?php echo text($user['fname']); ?></td>
                    <td><input class="persist-uid" type="text" name="weno_provider_id[<?php echo attr($user['id']); ?>]" placeholder="<?php echo xla("Weno User id Uxxxx"); ?>" value="<?php echo attr($user['weno_prov_id']); ?>"></td>
                    <td><?php echo text($user['facility']); ?></td>
                    <td><i onclick='renderDialog("users", <?php echo attr_js($user['id']); ?>, event)' role='button' class='fas fa-pen text-warning'></i></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <button type="submit" id="form_save_users" name="save" class="btn btn-primary float-right d-none" value="true"><?php echo xlt("Update Users Weno Location ID"); ?></button>
        </form>
    </div>
</body>
</html>
