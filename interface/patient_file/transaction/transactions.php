<?php


use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");
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
        dlgopen('../deleter.php?transaction=' + transactionId, '_blank', 500, 450);
        return false;
    }
</script>
</head>

<body class="body_top">
    <div class="page-header">
        <h1><?php echo xlt('Patient Transactions');?></h1>
    </div>
    <div class="btn-group">
        <a href="../summary/demographics.php" class="btn btn-default btn-back" onclick="top.restoreSession()">
            <?php echo xlt('Back to Patient'); ?></a>
        <a href="add_transaction.php" class="btn btn-default btn-add" onclick="top.restoreSession()">
            <?php echo xlt('Add'); ?></a>
        <a href="print_referral.php" class="btn btn-default btn-print" onclick="top.restoreSession()">
            <?php echo xlt('View Blank Referral Form'); ?></a>
    </div>
    <div class='text'>
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
                    $edit = xl('Edit');
                    $view = xl('View');
                    $delete = xl('Delete');
                    $title = xl($item['title']);
                    ?>
                    <tr>
                        <td>
                            <div class="btn-group pull-left">
                                <?php if ($item['title'] == 'LBTref') { ?>
                                    <a href='print_referral.php?transid=<?php echo attr($id); ?>' onclick='top.restoreSession();'
                                        class='btn btn-view btn-default'>
                                        <?php echo text($view); ?>
                                    </a>
                                <?php } ?>
                                <a href='add_transaction.php?transid=<?php echo attr($id); ?>&title=<?php echo attr($title); ?>&inmode=edit'
                                    onclick='top.restoreSession()'
                                    class='btn btn-default btn-edit'>
                                    <?php echo text($edit); ?>
                                </a>
                                <?php if (acl_check('admin', 'super')) { ?>
                                    <a href='#'
                                        onclick='deleteme(<?php echo attr($id); ?>)'
                                        class='btn btn-default btn-delete'>
                                        <?php echo text($delete); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </td>
                        <td><?php echo generate_display_field(['data_type' => 1, 'list_id' => 'transactions'], $item['title']); ?></td>
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
        <span class="text"><?php echo xlt('There are no transactions on file for this patient.'); ?></span>
        <?php
        }
        ?>
    </div>
</body>
</html>
