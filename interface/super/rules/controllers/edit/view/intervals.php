<?php require_once($GLOBALS["srcdir"] . "/../interface/super/rules/controllers/edit/helper/common.php"); ?>
<?php $rule = $viewBean->rule ?>
<?php $intervals = $rule->reminderIntervals ?>
<script language="javascript" src="<?php js_src('edit.js') ?>"></script>
<script type="text/javascript">
    var edit = new rule_edit( {});
    edit.init();
</script>
<div class="row">
    <div class="col-xs-12">
         <div class="page-header clearfix">
           <h2 class="clearfix"><span id='header_text'><?php echo out(xl('Rule Edit')); ?></span><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
        </div>
    </div>
</div>
<div class="row">
    <div class="edit text">
        <div class="col-xs-12">
        <form class="form-inline" action="index.php?action=edit!submit_intervals" method="post" id="frm_submit" onsubmit="return top.restoreSession()">
            <fieldset>
                <legend><?php echo out(xl('Reminder intervals')); ?></legend>
                <input type="hidden" name="id" value="<?php echo out($rule->id); ?>"/>

                <div>
                    <div class="col-xs-12 form-group clearfix">
                            <div class="col-xs-1 col-lg-offset-3">
                            <span class="h5"><u><?php echo out(xl('Type')); ?></u></span>
                            </div>
                            <div class="col-xs-1">
                                <span class="h5"><u><?php echo out(xl('Detail')); ?></u></span>
                            </div>
                    </div>
                    <?php 
                    foreach (ReminderIntervalType::values() as $type) { 
                        foreach (ReminderIntervalRange::values() as $range) { 
                            $first = true;
                            $detail = $intervals->getDetailFor($type, $range); 
                    ?>
                        <div class="col-xs-12 form-group clearfix">
                            <div class="col-xs-1 col-lg-offset-3">
                                <span class="<?php echo $first ? "req" : ""?>" data-grp="<?php echo out($type->code); ?>"><?php echo out($type->lbl); ?></span>
                            </div>
                            <div class="col-xs-1">
                                <span><?php echo out(xl($range->lbl)); ?></span>
                            </div>
                            <div class="col-xs-2">
                                <input data-grp-tgt="<?php echo out($type->code) ?>"
                                   type="text"
                                   class="form-control"
                                   name="<?php echo out($type->code); ?>-<?php echo out($range->code); ?>"
                                   value="<?php echo is_null($detail) ? "" : out($detail->amount); ?>" />
                            </div>
                            <div class="col-xs-2">
                                 <?php echo timeunit_select(array( "context"=>"rule_reminder_intervals", "target"=>$type->code, "name"=>$type->code."-".$range->code."-timeunit", "value" => $detail->timeUnit )); ?>
                            </div>
                        </div>

                    <?php $first = false; 
                        }
                    }
                    ?>
                </div>
            </fieldset>
            <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
            
                <div class="col-sm-12 text-left position-override">
                    <div class="btn-group btn-group-pinch" role="group">
                        <a href="javascript:;" class="btn btn-default btn-save" id="btn_save" onclick="top.restoreSession()"><span><?php echo out(xl('Save')); ?></span></a>
                        <a href="index.php?action=detail!view&id=<?php echo out($rule->id); ?>" class="btn btn-link btn-cancel btn-separate-left" onclick="top.restoreSession()"><span><?php echo out(xl('Cancel')); ?></span></a>
                    </div>
                </div>
           
       </form>
    </div>
    </div>
</div>
<div id="required_msg" class="h6">
    <span class="required">*</span><?php echo out(xl('Required fields')); ?>
</div>
