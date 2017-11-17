<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
 $rule = $viewBean->rule ?>

<script language="javascript" src="<?php js_src('edit.js') ?>"></script>
<script type="text/javascript">
    var edit = new rule_edit( {});
    edit.init();
</script>

<div class="row">
    <div class="col-xs-12">
         <div class="page-header clearfix">
           <h2 class="clearfix"><span id='header_text'><?php echo $rule->id ? out(xl('Rule Edit')) : out(xl('Rule Add')); ?></span><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
        </div>
    </div>
</div>
<div class="row">
    <div class="rule_detail edit summry text">
    <div class="col-xs-12">
        <form  action="index.php?action=edit!submit_summary" method="post" id="frm_submit" onsubmit="return top.restoreSession()">
            
                <fieldset>
                    <legend><?php echo out(xl('Summary')); ?></legend>
                    <input type="hidden" name="id" value="<?php echo out($rule->id); ?>"/>
                    
                        <div class="form-group clearfix">
                            <div class="col-xs-2 text-right col-lg-offset-2">
                                <label for="fld_title" class="text-right req" data-fld="fld_title"><?php echo out(xl('Title')); ?></label>
                            </div>
                            <div class="col-xs-6">
                              <input type="text" name="fld_title" class="field form-control" id="fld_title" value="<?php echo out($rule->title); ?>">
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <div class="col-xs-2 text-right col-lg-offset-2">
                                    <label  class="text-right" data-fld="fld_ruleTypes[]" ><?php echo out(xl('Type')); ?></label>
                            </div>
                            <div class="col-xs-6">
                                 <?php foreach (RuleType::values() as $type) {?>
                                    <input name="fld_ruleTypes[]"
                                       class="checkbox-inline"
                                       value="<?php echo out($type); ?>"
                                       type="checkbox" <?php echo $rule->hasRuleType(RuleType::from($type)) ? "CHECKED": "" ?>>
                                <?php echo out(RuleType::from($type)->lbl); ?>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <div class="col-xs-2 text-right col-lg-offset-2">
                                <label for="fld_developer" class="text-right" data-fld="fld_developer"><?php echo out(xl('Developer')); ?></label>
                            </div>
                            <div class="col-xs-6">
                              <input type="text" name="fld_developer" class="form-control" id="fld_developer" value="<?php echo out($rule->developer); ?>" maxlength="255">
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <div class="col-xs-2 text-right col-lg-offset-2">
                                <label for="fld_funding_source" class="text-right" data-fld="fld_funding_source"><?php echo out(xl('Funding Source')); ?></label>
                            </div>
                            <div class="col-xs-6">
                              <input type="text" name="fld_funding_source" class="form-control" id="fld_funding_source" value="<?php echo out($rule->funding_source); ?>" maxlength="255">
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <div class="col-xs-2 text-right col-lg-offset-2">
                                <label for="fld_release" class="text-right" data-fld="fld_release"><?php echo out(xl('Release')); ?></label>
                            </div>
                            <div class="col-xs-6">
                              <input type="text" name="fld_release" class="fform-control" id="fld_release" value="<?php echo out($rule->release); ?>" maxlength="255">
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <div class="col-xs-2 text-right col-lg-offset-2">
                                <label for="fld_web_reference" class="text-right" data-fld="fld_web_reference"><?php echo out(xl('Web Reference')); ?></label>
                            </div>
                            <div class="col-xs-6">
                              <input type="text" name="fld_web_reference" class="form-control" id="fld_web_reference" value="<?php echo out($rule->web_ref); ?>" maxlength="255">
                            </div>
                        </div>
                </fieldset>
            
            <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
            <div class="form-group clearfix">
                <div class="col-sm-12 text-left position-override">
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
    <span >*</span><?php echo out(xl('Required fields')); ?>
</div>
