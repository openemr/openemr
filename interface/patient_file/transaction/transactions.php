<?php

/**
 * transactions.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/transactions.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;

?>
<html>
<head>
    <title><?php echo xlt('Patient Transactions');?></title>
    <?php Header::setupHeader('common'); ?>

<script>
    // Called by the deleteme.php window on a successful delete.
    function imdeleted() {
        top.restoreSession();
        location.href = '../../patient_file/transaction/transactions.php';
    }
    // Process click on Delete button.
    function deleteme(transactionId) {
        top.restoreSession();
        dlgopen('../deleter.php?transaction=' + encodeURIComponent(transactionId) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450);
        return false;
    }
<?php require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal ?>
</script>
<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Patient Transactions'),
    'include_patient_name' => true,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "transactions_dashboard_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>

<body>
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?> mt-3">
        <div class="row">
            <div class="col-sm-12">
                <?php require_once("$include_root/patient_file/summary/dashboard_header.php");?>
            </div>
        </div>
        <?php
        $list_id = "transactions"; // to indicate nav item is active, count and give correct id
        // Collect the patient menu then build it
        $menuPatient = new PatientMenuRole();
        $menuPatient->displayHorizNavBarMenu();
        ?>
        <div class="row mt-3">
            <div class="col-sm-12">
                <div class="btn-group">
                    <a href="add_transaction.php" class="btn btn-primary btn-add" onclick="top.restoreSession()">
                        <?php echo xlt('Create New Transaction'); ?></a>
                    <a href="print_referral.php" class="btn btn-primary btn-print" onclick="top.restoreSession()">
                        <?php echo xlt('View/Print Blank Referral Form'); ?></a>
                </div>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-12 text jumbotron py-4">
                <?php
                if ($result = getTransByPid($pid)) {
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">&nbsp;</th>
                                    <th scope="col"><?php echo xlt('Type'); ?></th>
                                    <th scope="col"><?php echo xlt('Date'); ?></th>
                                    <th scope="col"><?php echo xlt('User'); ?></th>
                                    <th scope="col"><?php echo xlt('Details'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($result as $item) {
                                    if (!isset($item['body'])) {
                                        $item['body'] = '';
                                    }

                                    // Collect date
                                    if (!empty($item['refer_date'])) {
                                        // Special case for referrals, which uses refer_date stored in lbt_data table
                                        //  rather than date in transactions table.
                                        //  (note this only contains a date without a time)
                                        $date = oeFormatShortDate($item['refer_date']);
                                    } else {
                                        $date = oeFormatDateTime($item['date']);
                                    }

                                    $id = $item['id'];
                                    $edit = xl('View/Edit');
                                    $view = xl('Print'); //actually prints or displays ready to print
                                    $delete = xl('Delete');
                                    $title = xl($item['title']);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="btn-group oe-pull-toward">
                                                <a href='add_transaction.php?transid=<?php echo attr_url($id); ?>&title=<?php echo attr_url($title); ?>&inmode=edit'
                                                    onclick='top.restoreSession()'
                                                    class='btn btn-primary btn-edit'>
                                                    <?php echo text($edit); ?>
                                                </a>
                                                <?php if (AclMain::aclCheckCore('admin', 'super')) { ?>
                                                    <a href='#'
                                                        onclick='deleteme(<?php echo attr_js($id); ?>)'
                                                        class='btn btn-danger btn-delete'>
                                                        <?php echo text($delete); ?>
                                                    </a>
                                                <?php } ?>
                                                <?php if ($item['title'] == 'LBTref') { ?>
                                                    <a href='print_referral.php?transid=<?php echo attr_url($id); ?>' onclick='top.restoreSession();'
                                                        class='btn btn-print btn-primary'>
                                                        <?php echo text($view); ?>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td><?php echo text(getLayoutTitle('Transactions', $item['title'])); ?></td>
                                        <td><?php echo text($date); ?></td>
                                        <td><?php echo text($item['user']); ?></td>
                                        <td><?php echo text($item['body']); ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                } else {
                    ?>
                <span class="text">
                    <i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i> <?php echo xlt('There are no transactions on file for this patient.'); ?>
                </span>
                    <?php } ?>
            </div>
        </div>
    </div><!--end of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
    <script>
        var listId = '#' + <?php echo js_escape($list_id); ?>;
        $(function () {
            $(listId).addClass("active");
        });
    </script>
</body>
</html>
