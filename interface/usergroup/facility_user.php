<?php

/**
 * Facility user-specific settings.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Scott Wakefield <scott@npclinics.com.au>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 NP Clinics <info@npclinics.com.au>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Ensure authorized
if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Facility Specific User Information")]);
    exit;
}

$alertmsg = '';

if (isset($_POST["mode"]) && $_POST["mode"] == "facility_user_id" && isset($_POST["user_id"]) && isset($_POST["fac_id"])) {
    // Inserting/Updating new facility specific user information
    $fres = sqlStatement("SELECT * FROM `layout_options` " .
        "WHERE `form_id` = 'FACUSR' AND `uor` > 0 AND `field_id` != '' " .
        "ORDER BY `group_id`, `seq`");
    while ($frow = sqlFetchArray($fres)) {
        $value = get_layout_form_value($frow);
        $entry_id = sqlQuery("SELECT `id` FROM `facility_user_ids` WHERE `uid` = ? AND `facility_id` = ? AND `field_id` =?", array($_POST["user_id"], $_POST["fac_id"], $frow['field_id']));
        if (empty($entry_id)) {
            // Insert new entry
            sqlStatement("INSERT INTO `facility_user_ids` (`uid`, `facility_id`, `field_id`, `field_value`) VALUES (?,?,?,?)", array($_POST["user_id"], $_POST["fac_id"], $frow['field_id'], $value));
        } else {
            // Update existing entry
            sqlStatement("UPDATE `facility_user_ids` SET `field_value` = ? WHERE `id` = ?", array($value, $entry_id['id']));
        }
    }
}

?>
<html>

<head>

    <title><?php echo xlt("Facility Specific User Information"); ?></title>

    <?php Header::setupHeader(['common']); ?>

    <script>
        function refreshme() {
            top.restoreSession();
            document.location.reload();
        }

        $(function() {
            $(".small_modal").on('click', function(e) {
                e.preventDefault();e.stopPropagation();
                dlgopen('', '', 550, 550, '', '', {
                    //onClosed: 'refreshme',
                    sizeHeight: 'auto',
                    allowResize: true,
                    allowDrag: true,
                    dialogId: '',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });
        });
    </script>
</head>

<body>
    <?php
    // Collect all users
    $u_res = sqlStatement("select * from `users` WHERE `username` != '' AND `active` = 1 order by `username`");

    // Collect all facilities and store them in an array
    $f_res = sqlStatement("select * from `facility` order by `name`");
    $f_arr = array();
    for ($i = 0; $row = sqlFetchArray($f_res); $i++) {
        $f_arr[$i] = $row;
    }

    // Collect layout information and store them in an array
    $l_res = sqlStatement("SELECT * FROM layout_options " .
        "WHERE form_id = 'FACUSR' AND uor > 0 AND field_id != '' " .
        "ORDER BY group_id, seq");
    $l_arr = array();
    for ($i = 0; $row = sqlFetchArray($l_res); $i++) {
        $l_arr[$i] = $row;
    }
    ?>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title">
                    <h2><?php echo xlt('Facility Specific User Information'); ?></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="btn-group">
                    <a href="usergroup_admin.php" class="btn btn-secondary btn-back" onclick="top.restoreSession()"><?php echo xlt('Back to Users'); ?></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><b><?php echo xlt('Username'); ?></b></th>
                            <th><b><?php echo xlt('Full Name'); ?></b></th>
                            <th><b><span class="bold"><?php echo xlt('Facility'); ?></span></b></th>
                            <?php
                            foreach ($l_arr as $layout_entry) {
                                echo "<th>" . text(xl_layout_label($layout_entry['title'])) . "&nbsp;</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($user = sqlFetchArray($u_res)) {
                            foreach ($f_arr as $facility) { ?>
                                <tr>
                                    <td><a href="facility_user_admin.php?user_id=<?php echo attr_url($user['id']); ?>&fac_id=<?php echo attr_url($facility['id']); ?>" class="small_modal" onclick="top.restoreSession()"><b><?php echo text($user['username']); ?></b></a>&nbsp;</td>
                                    <td><?php echo text($user['fname'] . " " . $user['lname']); ?></td>
                                    <td><?php echo text($facility['name']); ?>&nbsp;</td>
                                    <?php
                                    foreach ($l_arr as $layout_entry) {
                                        $entry_data = sqlQuery("SELECT `field_value` FROM `facility_user_ids` " .
                                                               "WHERE `uid` = ? AND `facility_id` = ? AND `field_id` = ?", array($user['id'],$facility['id'],$layout_entry['field_id']));
                                        echo "<td>" . generate_display_field($layout_entry, ($entry_data['field_value'] ?? '')) . "&nbsp;</td>";
                                    }
                                    ?>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
