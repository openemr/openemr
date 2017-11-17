<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<?php $allowed = $viewBean->allowed?>
<?php $ruleId = $viewBean->id;?>
<?php $groupId = $viewBean->groupId;?>

<script type="text/javascript">
</script>
<div class="row">
    <div class="col-xs-12">
         <div class="page-header clearfix">
           <h2 class="clearfix"><span id='header_text'><?php echo out(xl('Rule Edit')); ?></span>  &nbsp;<a href="index.php?action=detail!view&id=<?php echo out($ruleId); ?>" onclick="top.restoreSession()"><i class="fa fa-arrow-circle-left fa-2x small" aria-hidden="true" title="<?php echo xla('Back to Rule Detail'); ?>"></i></a><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
        </div>
    </div>
</div>
<div class="row">
    <div class="rule_detail edit text">
        <div class="col-xs-12">
            <fieldset>
                <legend><?php echo out(xl('Add criteria')); ?></legend>
                <ul>
                <?php foreach ($allowed as $type) { ?>
                    <li>
                    <a href="index.php?action=edit!choose_criteria&id=<?php echo out($ruleId); ?>&group_id=<?php echo out($groupId); ?>&type=<?php echo out($viewBean->type); ?>&criteriaType=<?php echo out($type->code); ?>" onclick="top.restoreSession()">
                        <?php echo out(xl($type->lbl)); ?>
                    </a>
                    </li>
                <?php } ?>
                </ul>
            </fieldset>
        </div>
    </div>
</div>
