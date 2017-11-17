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

<div class="row">
    <div class="col-xs-12">
         <div class="page-header clearfix">
           <h2 class="clearfix"><span id='header_text'><?php echo out(xl('Rule Edit')); ?></span>  &nbsp;<a href="index.php?action=detail!view&id=<?php echo out($rule->id); ?>" onclick="top.restoreSession()"><i class="fa fa-arrow-circle-left fa-2x small" aria-hidden="true" title="<?php echo xla('Back to Rule Detail'); ?>"></i></a></a><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
        </div>
    </div>
</div>
<div class="row">
    <div class="rule_detail edit text">
        <div class="col-xs-12">
            <form action="index.php?action=edit!submit_action" method="post" id="frm_submit" onsubmit="return top.restoreSession()">
                <fieldset>
                    <legend><?php echo out(xl('Action')); ?></legend>
                    <div class="col-xs-6 col-lg-offset-3">
                        <div class="col-xs-12">
                        <input type="hidden" name="guid" value="<?php echo out($action->guid); ?>"/>
                        <input type="hidden" name="group_id" value="<?php echo out($action->groupId); ?>"/>
                        <input type="hidden" name="id" value="<?php echo out($action->id); ?>"/>
                        <input type="hidden" name="group_id" value="<?php echo out($action->groupId); ?>"/>

                        <!-- custom rules input -->

                        <!-- category -->
                       
                        <?php echo textfield_row(array("id" => "fld_category_lbl",
                                                       "name" => "fld_category_lbl",
                                                       "title" => xl("Category"),
                                                       "value" => out($action->getCategoryLabel()) )); ?>
                        <a href="javascript:;" id="change_category" onclick="top.restoreSession()">(change)</a>
                        <input type="hidden" id="fld_category" name="fld_category" value="<?php echo out($action->category); ?>" />
                       
                        <!-- item -->
                        <?php echo textfield_row(array("id" => "fld_item_lbl",
                                                       "name" => "fld_item_lbl",
                                                       "title" => xl("Item"),
                                                       "value" => out($action->getItemLabel()) )); ?>
                        <a href="javascript:;" id="change_item" onclick="top.restoreSession()">(change)</a>
                        <input type="hidden" id="fld_item" name="fld_item" value="<?php echo out($action->item); ?>" />

                        <!-- reminder link  -->
                        <?php echo textfield_row(array("id" => "fld_link",
                                                       "name" => "fld_link",
                                                       "title" => xl("Link"),
                                                       "value" => out($action->reminderLink) )); ?>
                        <br>
                        <!-- reminder message  -->
                        <?php echo textfield_row(array("id" => "fld_message",
                                                       "name" => "fld_message",
                                                       "title" => xl("Message"),
                                                       "value" => out($action->reminderMessage) )); ?>


                        <!-- custom rules input -->
                        <br>
                        <p class="row">
                            <span class="left_col colhead req" data-field="fld_custom_input"><?php echo out(xl('Custom input?')); ?></span>
                            <span class="end_col">
                                <select data-grp-tgt="" type="dropdown" name="fld_custom_input" id="">
                                    <option id="Yes" value="yes" <?php echo $action->customRulesInput ? "SELECTED" : "" ?>><?php echo out(xl('Yes')); ?></option>
                                    <option id="No" value="no" <?php echo !$action->customRulesInput ? "SELECTED" : "" ?>><?php echo out(xl('No')); ?></option>
                                </select>
                            </span>
                        </p>
                        </div>
                    </div>
                    
                </fieldset>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 position-override">
                            <div class="btn-group btn-group-pinch" role="group">
                                <a href="javascript:;" class="btn btn-default btn-save" id="btn_save" onclick="top.restoreSession()"><span><?php echo out(xl('Save')); ?></span></a>
                                <a href="index.php?action=detail!view&id=<?php echo out($rule->id); ?>" class="btn btn-link btn-cancel btn-separate-left" onclick="top.restoreSession()"><span><?php echo out(xl('Cancel')); ?></span></a>
                            </div>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>

<div id="required_msg" class="h6">
    <span class="required">*</span><?php echo out(xl('Required fields')); ?>
</div>
