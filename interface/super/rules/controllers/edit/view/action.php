<?php

/**
 * interface/super/rules/controllers/edit/view/action.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<?php $action = $viewBean->action ?>
<?php $rule = $viewBean->rule ?>

<script src="<?php js_src('edit.js') ?>"></script>
<script src="<?php js_src('bucket.js') ?>"></script>
<script>
    var edit = new rule_edit({});
    edit.init();

    var bucket = new bucket({});
    bucket.init();
</script>

<table class="table header">
    <tr>
        <td class="title"><?php echo xlt('Rule Edit'); ?></td>
        <td>
            <a href="index.php?action=detail!view&id=<?php echo attr_url($action->id); ?>" class="iframe_medium btn btn-secondary" onclick="top.restoreSession()">
                <span><?php echo xlt('Cancel'); ?></span>
            </a>
            <a href="javascript:;" class="iframe_medium btn btn-primary" id="btn_save" onclick="top.restoreSession()"><span><?php echo xlt('Save'); ?></span></a>
        </td>
    </tr>
</table>

<div class="rule_detail edit">
    <p class="header"><?php echo xlt('Action'); ?> </p>

    <form action="index.php?action=edit!submit_action" method="post" id="frm_submit" onsubmit="return top.restoreSession()">
        <input type="hidden" name="guid" value="<?php echo attr($action->guid); ?>" />
        <input type="hidden" name="group_id" value="<?php echo attr($action->groupId); ?>" />
        <input type="hidden" name="id" value="<?php echo attr($action->id); ?>" />
        <input type="hidden" name="group_id" value="<?php echo attr($action->groupId); ?>" />

        <!-- custom rules input -->

        <!-- category -->
        <?php
        $change_link = '<a href="javascript:;" id="change_category" onclick="top.restoreSession();">(' . xlt('Change') . ')</a>';
        echo textfield_row(array("id" => "fld_category_lbl",
            "name" => "fld_category_lbl",
            "title" => xl("Category"),
            "value" => $action->getCategoryLabel(),
            "render_link" => $change_link)); ?>
        <input type="hidden" id="fld_category" name="fld_category" value="<?php echo attr($action->category); ?>" />

        <!-- item -->
        <?php
        $change_link = '<a href="javascript:;" id="change_item" onclick="top.restoreSession();">(' . xlt('Change') . ')</a>';
        echo textfield_row(array("id" => "fld_item_lbl",
            "name" => "fld_item_lbl",
            "title" => xl("Item"),
            "value" => $action->getItemLabel(),
            "render_link" => $change_link)); ?>
        <input type="hidden" id="fld_item" name="fld_item" value="<?php echo attr($action->item); ?>" />

        <!-- reminder link  -->
        <?php echo textfield_row(array("id" => "fld_link",
            "name" => "fld_link",
            "title" => xl("Link"),
            "value" => $action->reminderLink)); ?>

        <!-- reminder message  -->
        <?php echo textfield_row(array("id" => "fld_message",
            "name" => "fld_message",
            "title" => xl("Message"),
            "value" => $action->reminderMessage)); ?>


        <!-- custom rules input -->
        <p class="form-row">
            <span class="left_col colhead req" data-field="fld_custom_input"><?php echo xlt('Custom input?'); ?></span>
            <span class="end_col">
            <select class="form-control" data-grp-tgt="" type="dropdown" name="fld_custom_input" id="">
                <option id="Yes" value="yes" <?php echo $action->customRulesInput ? "SELECTED" : "" ?>><?php echo xlt('Yes'); ?></option>
                <option id="No" value="no" <?php echo !$action->customRulesInput ? "SELECTED" : "" ?>><?php echo xlt('No'); ?></option>
            </select>
        </span>
        </p>

    </form>

</div>

<div id="required_msg" class="small">
    <span class="required">*</span><?php echo xlt('Required fields'); ?>
</div>
