<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<html>
<head>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?= $GLOBALS['css_header'] ?>" type="text/css">
    <script type="text/javascript" src="<?= $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>" type="text/css">

</head>

<body class='body_top'>
<?php $rule = $viewBean->rule ?>
<?php $criteria = $viewBean->criteria ?>

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

    <form action="index.php?action=edit!submit_criteria" method="post" id="frm_submit">
    <input type="hidden" name="id" value="<?php echo out( $rule->id ); ?>"/>
    <input type="hidden" name="group_id" value="<?php echo out( $criteria->groupId ); ?>"/>
    <input type="hidden" name="guid" value="<?php echo out( $criteria->guid ); ?>"/>
    <input type="hidden" name="type" value="<?php echo out( $viewBean->type ); ?>"/>
    <input type="hidden" name="criteriaTypeCode" value="<?php echo out( $criteria->criteriaType->code ); ?>"/>

    <!-- ----------------- -->
    <?php
    if ( file_exists($viewBean->_view_body) ) {
        require_once($viewBean->_view_body);
    }
    ?>
    <!-- ----------------- -->

    </form>
    
</div>

<div id="required_msg" class="small">
    <span class="required">*</span><?php echo out( xl( 'Required fields' ) ); ?>
</div>

</body>

</html>