<?php require_once( $GLOBALS["srcdir"] . "/../interface/super/rules/controllers/edit/helper/common.php" ); ?>
<?php $rule = $viewBean->rule ?>
<?php $intervals = $rule->reminderIntervals ?>
<script language="javascript" src="<?php js_src('edit.js') ?>"></script>
<script type="text/javascript">
    var edit = new rule_edit( {});
    edit.init();
</script>

<table class="header">
  <tr>
        <td class="title"><?php echo out( xl( 'Rule Edit' ) ); ?></td>
        <td>
            <a href="index.php?action=detail!view&id=<?php echo out( $rule->id ); ?>" class="iframe_medium css_button">
                <span><?php echo out( xl( 'Cancel' ) ); ?></span>
            </a>
            <a href="javascript:;" class="iframe_medium css_button" id="btn_save"><span><?php echo out( xl( 'Save' ) ); ?></span></a>
        </td>
  </tr>
</table>

<div class="rule_detail edit text">
    <p class="header"><?php echo out( xl( 'Reminder intervals' ) ); ?> </p>

    <form action="index.php?action=edit!submit_intervals" method="post" id="frm_submit">
    <input type="hidden" name="id" value="<?php echo out( $rule->id ); ?>"/>

    <div class="intervals">
        <p>
            <span class="left_col colhead"><u><?php echo out( xl( 'Type' ) ); ?></u></span>
            <span class="end_col colhead"><u><?php echo out( xl( 'Detail' ) ); ?></u></span>
        </p>

    <?php foreach( ReminderIntervalType::values() as $type ) { ?>
    <?php foreach( ReminderIntervalRange::values() as $range ) { ?>
    <?php $first = true; $detail = $intervals->getDetailFor( $type, $range ); ?>
        <p>
            <span class="left_col <?php echo $first ? "req" : ""?>" data-grp="<?php echo out( $type->code ); ?>"><?php echo out( $type->lbl ); ?></span>
            <span class="mid_col"><?php echo out( xl( $range->lbl ) ); ?></span>
            <span class="mid_col">
                <input data-grp-tgt="<?php echo out( $type->code ) ?>"
                       type="text"
                       name="<?php echo out( $type->code ); ?>-<?php echo out( $range->code ); ?>"
                       value="<?php echo is_null( $detail ) ? "" : out( $detail->amount ); ?>" />
            </span>
            <span class="end_col">
            <?php echo timeunit_select( array( "context"=>"rule_reminder_intervals", "target"=>$type->code, "name"=>$type->code."-".$range->code."-timeunit", "value" => $detail->timeUnit ) ); ?>
            </span>
        </p>
    <?php $first = false; ?>
    <?php } ?>
    <?php } ?>

    </div>

    </form>
    
</div>

<div id="required_msg" class="small">
    <span class="required">*</span><?php echo out( xl( 'Required fields' ) ); ?>
</div>
