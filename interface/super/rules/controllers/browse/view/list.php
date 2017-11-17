<?php
// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
?>

<script language="javascript" src="<?php js_src('list.js') ?>"></script>
<script language="javascript" src="<?php js_src('jQuery.fn.sortElements.js') ?>"></script>

<script type="text/javascript">
    var list = new list_rules();
    list.init();
</script>

<div class="row">
    <div class="col-xs-12">
         <div class="page-header clearfix">
           <h2 class="clearfix"><span id='header_text'><?php echo xlt("Plans and Rules Configuration"); ?></span><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
        </div>
    </div>
</div>
<div class='row'>
    <div class='col-xs-12'>
        <div class="form-group clearfix">
            <div class="text-left">
                <a href="index.php?action=browse!plans_config" class="btn btn-default btn-save"><?php echo xlt('Configure Plans'); ?></a>
                <a href="index.php?action=edit!summary" class="btn btn-default btn-add" onclick="top.restoreSession()"><?php echo xlt('Add New Rule'); ?></a>
            </div>
        </div>
            
    </div>
</div>
<div class='row'>
    <div class='col-xs-12'>
        <fieldset>
            <div class="rule_container text">
                <div class="rule_row header">
                    <div class="rule_type header_type"><?php echo out(xl('Type')); ?></div>
                    <div class="rule_title header_title"><?php echo out(xl('Name')); ?></div>
                </div>
            </div>

            <!-- template -->
            <div class="rule_row data template">
                <div class="rule_type"><a href="index.php?action=detail!view" onclick="top.restoreSession()"></a></div>
                <div class="rule_title"><a href="index.php?action=detail!view" onclick="top.restoreSession()"></a></div>
            </div>
        </fieldset>
    </div>
</div>
