<?php



include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");
?>

<html>
<head>
    <?php
    require_once "{$GLOBALS['srcdir']}/templates/standard_header_template.php";
    ?>
<script language="javascript">
// Called by the deleteme.php window on a successful delete.
function imdeleted() {
 top.restoreSession();
 location.href = 'transaction/transactions.php';
}
</script>
</head>
<body class="body_top">
<table class="table table-striped">
<?php
if ($result = getTransByPid($pid)): ?>
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
foreach ($result as $item):
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
            <?php if ($item['title'] == 'LBTref'): ?>
            <a href='print_referral.php?transid=<?php echo attr($id); ?>' onclick='top.restoreSession();'
               class='btn btn-view btn-default'>
                <?php echo text($view); ?>
            </a>
            <?php endif; ?>
            <a href='add_transaction.php?transid=<?php echo attr($id); ?>&title=<?php echo attr($title); ?>&inmode=edit'
               onclick='top.restoreSession()'
               class='btn btn-default btn-edit'>
                <?php echo text($edit); ?>
            </a>
            <?php if (acl_check('admin', 'super')): ?>
            <a href='../deleter.php?transaction=<?php echo attr($id); ?>'
               onclick='top.restoreSession()'
               class='btn btn-default btn-delete'>
                <?php echo text($delete); ?>
            </a>
            <?php endif; ?>
        </div>
    </td>
    <td><?php echo generate_display_field(['data_type' => 1, 'list_id' => 'transactions'], $item['title']);?></td>
    <td><?php echo text($date);?></td>
    <td><?php echo text($item['user']);?></td>
    <td><?php echo text($item['body']);?></td>
</tr>
<?php
endforeach;
endif;?>
</tbody>
</table>
</body>
</html>
