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
require_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;
?>
<html>
<head>
    <title><?php echo xlt('Patient Transactions');?></title>
    <?php Header::setupHeader('common'); ?>

<script type="text/javascript">
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

<body class="body_top">
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
        <div class="row">
            <div class="col-sm-12">
                <?php require_once("$include_root/patient_file/summary/dashboard_header.php");?>
            </div>
        </div>
        <div class="row" >
            <div class="col-sm-12">
                <?php
                $list_id = "transactions"; // to indicate nav item is active, count and give correct id
                // Collect the patient menu then build it
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="btn-group">
                    <!--<a href="../summary/demographics.php" class="btn btn-default btn-back" onclick="top.restoreSession()">
                        <?php echo xlt('Back to Patient'); ?></a>-->
                    <a href="add_transaction.php" class="btn btn-default btn-add" onclick="top.restoreSession()">
                        <?php echo xlt('Create New Transaction'); ?></a>
                    <a href="print_referral.php" class="btn btn-default btn-print" onclick="top.restoreSession()">
                        <?php echo xlt('View/Print Blank Referral Form'); ?></a>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-sm-12 text">

                <?php
                if ($result = getTransByPid($pid)) {
                    ?>

                    <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th><?php echo xlt('Type'); ?></th>
                            <th><?php echo xlt('Date'); ?></th>
                            <th><?php echo xlt('User'); ?></th>
                            <th><?php echo xlt('Details'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $item) {
                            if (!isset($item['body'])) {
                                $item['body'] = '';
                            }

                            if (getdate() == strtotime($item['date'])) {
                                $date = "Today, " . date('D F ds', strtotime($item['date']));
                            } else {
                                $date = date('D F ds', strtotime($item['date']));
                            }

                            $date = oeFormatShortDate($item['refer_date']);
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
                                            class='btn btn-default btn-edit'>
                                            <?php echo text($edit); ?>
                                        </a>
                                        <?php if (acl_check('admin', 'super')) { ?>
                                            <a href='#'
                                                onclick='deleteme(<?php echo attr_js($id); ?>)'
                                                class='btn btn-default btn-delete'>
                                                <?php echo text($delete); ?>
                                            </a>
                                        <?php } ?>
                                        <?php if ($item['title'] == 'LBTref') { ?>
                                            <a href='print_referral.php?transid=<?php echo attr_url($id); ?>' onclick='top.restoreSession();'
                                                class='btn btn-print btn-default'>
                                                <?php echo text($view); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td><?php echo getLayoutTitle('Transactions', $item['title']); ?></td>
                                <td><?php echo text($date); ?></td>
                                <td><?php echo text($item['user']); ?></td>
                                <td><?php echo text($item['body']); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                    </table>

                    <?php
                } else {
                    ?>
                <span class="text"><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt('There are no transactions on file for this patient.'); ?></span>
                    <?php
                }
                ?>
            </div>
        </div>
    </div><!--end of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
    <script>
        var listId = '#' + <?php echo js_escape($list_id); ?>;
        $(document).ready(function(){
            $(listId).addClass("active");
        });
    </script>
</body>
</html>
