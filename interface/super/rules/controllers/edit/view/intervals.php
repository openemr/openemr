<?php

/**
 * interface/super/rules/controllers/edit/view/intervals.php
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
<?php require_once($GLOBALS["srcdir"] . "/../interface/super/rules/controllers/edit/helper/common.php"); ?>
<?php $rule = $viewBean->rule ?>
<?php $intervals = $rule->reminderIntervals ?>
<script src="<?php js_src('edit.js') ?>"></script>
<script>
    var edit = new rule_edit( {});
    edit.init();
</script>

<table class="table header">
  <tr>
        <td class="title"><?php echo xlt('Rule Edit'); ?></td>
        <td>
            <a href="index.php?action=detail!view&id=<?php echo attr_url($rule->id); ?>" class="iframe_medium btn btn-secondary" onclick="top.restoreSession()">
                <span><?php echo xlt('Cancel'); ?></span>
            </a>
            <a href="javascript:;" class="iframe_medium btn btn-primary" id="btn_save" onclick="top.restoreSession()"><span><?php echo xlt('Save'); ?></span></a>
        </td>
  </tr>
</table>

<div class="rule_detail edit">
    <p class="header"><?php echo xlt('Reminder intervals'); ?> </p>

    <form action="index.php?action=edit!submit_intervals" method="post" id="frm_submit" onsubmit="return top.restoreSession()">
    <input type="hidden" name="id" value="<?php echo attr($rule->id); ?>"/>

    <div class="intervals">
        <p>
            <span class="left_col colhead"><u><?php echo xlt('Type'); ?></u></span>
            <span class="end_col colhead"><u><?php echo xlt('Detail'); ?></u></span>
        </p>

    <?php foreach (ReminderIntervalType::values() as $type) { ?>
        <?php foreach (ReminderIntervalRange::values() as $range) { ?>
            <?php $first = true;
            $detail = $intervals->getDetailFor($type, $range); ?>
        <p>
            <span class="left_col <?php echo $first ? "req" : ""?>" data-grp="<?php echo attr($type->code); ?>"><?php echo text($type->lbl); ?></span>
            <span class="mid_col"><?php echo xlt($range->lbl); ?></span>
            <span class="mid_col">
                <input class="form-control" data-grp-tgt="<?php echo attr($type->code) ?>" type="text" name="<?php echo attr($type->code); ?>-<?php echo attr($range->code); ?>" value="<?php echo is_null($detail) ? "" : attr($detail->amount); ?>" />
            </span>
            <span class="end_col">
            <?php echo timeunit_select(array( "context" => "rule_reminder_intervals", "target" => $type->code, "name" => $type->code . "-" . $range->code . "-timeunit", "value" => $detail->timeUnit )); ?>
            </span>
        </p>
            <?php $first = false; ?>
    <?php } ?>
    <?php } ?>

    </div>

    </form>

</div>

<div id="required_msg" class="small">
    <span class="required">*</span><?php echo xlt('Required fields'); ?>
</div>
