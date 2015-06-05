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

<table class="header">
  <tr>
        <td class="title"><?php echo out( xl( 'Plans Configuration' ) ); ?></td>
        <td>
            <a href="index.php?action=browse!plans_config" class="iframe_medium css_button">
                <span><?php echo out( xl( 'Go' ) ); ?></span>
            </a>
        </td>
  </tr> 
  <tr>
        <td class="title"><?php echo out( xl( 'Rules Configuration' ) ); ?></td>
        <td>
            <a href="index.php?action=edit!summary" class="iframe_medium css_button" onclick="top.restoreSession()">
                <span><?php echo out( xl( 'Add new' ) ); ?></span>
            </a>
        </td>
  </tr> 
</table>

<div class="rule_container text">
    <div class="rule_row header">
        <span class="rule_title header_title"><?php echo out( xl( 'Name' ) ); ?></span>
        <span class="rule_type header_type"><?php echo out( xl( 'Type' ) ); ?></span>
    </div>
</div>

<!-- template -->
<div class="rule_row data template">
    <span class="rule_title"><a href="index.php?action=detail!view" onclick="top.restoreSession()"></a></span>
    <span class="rule_type"><a href="index.php?action=detail!view" onclick="top.restoreSession()"></a></span>
</div>

