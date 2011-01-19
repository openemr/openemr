<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<?php $action = $viewBean->action?>
<?php $rule = $viewBean->rule?>

<script language="javascript" src="<?php js_src('edit.js') ?>"></script>
<script language="javascript" src="<?php js_src('bucket.js') ?>"></script>
<script type="text/javascript">
    var edit = new rule_edit( {});
    edit.init();

    var bucket = new bucket( {} );
    bucket.init();
</script>

<table class="header">
  <tr>
        <td class="title"><?php echo out( xl( 'Rule Edit' ) ); ?></td>
        <td>
            <a href="index.php?action=detail!view&id=<?php echo $action->id ?>" class="iframe_medium css_button">
                <span><?php echo out( xl( 'Cancel' ) ); ?></span>
            </a>
            <a href="javascript:;" class="iframe_medium css_button" id="btn_save"><span><?php echo out( xl('Save' ) );  ?></span></a>
        </td>
  </tr>
</table>

<div class="rule_detail edit text">
    <p class="header"><?php echo out( xl( 'Action' ) ); ?> </p>

    <form action="index.php?action=edit!submit_action" method="post" id="frm_submit">
    <input type="hidden" name="guid" value="<?php echo out( $action->guid ); ?>"/>
    <input type="hidden" name="group_id" value="<?php echo out( $action->groupId ); ?>"/>
    <input type="hidden" name="id" value="<?php echo out( $action->id ); ?>"/>
    <input type="hidden" name="group_id" value="<?php echo out( $action->groupId ); ?>"/>

    <!-- custom rules input -->

    <!-- category -->
    <?php echo textfield_row(array("id" => "fld_category_lbl",
                                   "name" => "fld_category_lbl",
                                   "title" => xl("Category"),
                                   "value" => out( $action->getCategoryLabel() ) ) ); ?>
    <br/><a href="javascript:;" id="change_category">(change)</a>
    <input type="hidden" id="fld_category" name="fld_category" value="<?php echo out( $action->category ); ?>" />

    <!-- item -->
    <?php echo textfield_row(array("id" => "fld_item_lbl",
                                   "name" => "fld_item_lbl",
                                   "title" => xl("Item"),
                                   "value" => out( $action->getItemLabel() ) ) ); ?>
    <br/><a href="javascript:;" id="change_item">(change)</a>
    <input type="hidden" id="fld_item" name="fld_item" value="<?php echo out( $action->item ); ?>" />

    <!-- reminder link  -->
    <?php echo textfield_row(array("id" => "fld_link",
                                   "name" => "fld_link",
                                   "title" => xl("Link"),
                                   "value" => out( $action->reminderLink ) ) ); ?>

    <!-- reminder message  -->
    <?php echo textfield_row(array("id" => "fld_message",
                                   "name" => "fld_message",
                                   "title" => xl("Message"),
                                   "value" => out( $action->reminderMessage ) ) ); ?>


    <!-- custom rules input -->
    <p class="row">
        <span class="left_col colhead req" data-field="fld_custom_input"><?php echo out( xl( 'Custom input?' ) ); ?></span>
        <span class="end_col">
            <select data-grp-tgt="" type="dropdown" name="fld_custom_input" id="">
                <option id="" value="">--<?php echo out( xl( 'Select' ) ); ?>--</option>
                <option id="Yes" value="yes" <?php echo $action->customRulesInput ? "SELECTED" : "" ?>><?php echo out( xl( 'Yes' ) ); ?></option>
                <option id="No" value="no" <?php echo !$action->completed ? "SELECTED" : "" ?>><?php echo out( xl( 'No' ) ); ?></option>
            </select>
        </span>
    </p>

    </form>

</div>

<div id="required_msg" class="small">
    <span class="required">*</span><?php echo out( xl( 'Required fields' ) ); ?>
</div>
