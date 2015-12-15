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

<table class="header">
  <tr>
        <td class="title"><?php echo $rule->id ? out( xl( 'Rule Edit' ) ) : out( xl( 'Rule Add' ) ); ?></td>
        <td>
            <a href="index.php?action=detail!view&id=<?php echo out( $rule->id ); ?>" class="iframe_medium css_button" onclick="top.restoreSession()">
                <span><?php echo out( xl( 'Cancel' ) ); ?></span>
            </a>
            <a href="javascript:;" class="iframe_medium css_button" id="btn_save" onclick="top.restoreSession()"><span><?php echo out( xl( 'Save' ) ); ?></span></a>
        </td>
  </tr>
</table>

<div class="rule_detail edit summry text">
    <p class="header"><?php echo out( xl( 'Summary' ) ); ?> </p>

    <form action="index.php?action=edit!submit_summary" method="post" id="frm_submit" onsubmit="return top.restoreSession()">
    <input type="hidden" name="id" value="<?php echo out( $rule->id ); ?>"/>

    <p class="row">
    <span class="left_col colhead req" data-fld="fld_title"><?php echo out( xl( 'Title' ) ); ?></span>
    <span class="end_col"><input type="text" name="fld_title" class="field" id="fld_title" value="<?php echo out( $rule->title ); ?>"></span>
    </p>
    
    <p class="row">
    <span class="left_col colhead" data-fld="fld_ruleTypes[]"><?php echo out( xl( 'Type' ) ); ?></span>
    <span class="end_col">
        <?php foreach ( RuleType::values() as $type ) {?>
        <input name="fld_ruleTypes[]"
               value="<?php echo out( $type ); ?>"
               type="checkbox" <?php echo $rule->hasRuleType(RuleType::from($type)) ? "CHECKED": "" ?>>
        <?php echo out( RuleType::from($type)->lbl ); ?>
        <?php } ?>
    </span>
    </p>

    <p class="row"> 
    <span class="left_col colhead" data-fld="fld_developer"><?php echo out( xl( 'Developer' ) ); ?></span>
    <span class="end_col"><input type="text" name="fld_developer" class="field" id="fld_developer" value="<?php echo out( $rule->developer ); ?>" maxlength="255"></span>
    </p>

    <p class="row">
    <span class="left_col colhead" data-fld="fld_funding_source"><?php echo out( xl( 'Funding Source' ) ); ?></span>
    <span class="end_col"><input type="text" name="fld_funding_source" class="field" id="fld_funding_source" value="<?php echo out( $rule->funding_source ); ?>" maxlength="255"></span>
    </p>

    <p class="row">
    <span class="left_col colhead" data-fld="fld_release"><?php echo out( xl( 'Release' ) ); ?></span>
    <span class="end_col"><input type="text" name="fld_release" class="field" id="fld_release" value="<?php echo out( $rule->release ); ?>" maxlength="255"></span>
    </p>

    <p class="row">
    <span class="left_col colhead" data-fld="fld_web_reference"><?php echo out( xl( 'Web Reference' ) ); ?></span>
    <span class="end_col"><input type="text" name="fld_web_reference" class="field" id="fld_web_reference" value="<?php echo out( $rule->web_ref ); ?>" maxlength="255"></span>
    </p>

    </form>
    
</div>

<div id="required_msg" class="small">
    <span class="required">*</span><?php echo out( xl( 'Required fields' ) ); ?>
</div>
