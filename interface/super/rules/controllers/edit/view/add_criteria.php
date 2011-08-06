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

<table class="header">
  <tr>
        <td class="title"><?php echo out( xl( 'Rule Edit' ) ); ?></td>
        <td>
            <a href="index.php?action=detail!view&id=<?php echo out( $ruleId ); ?>" class="iframe_medium css_button" onclick="top.restoreSession()">
                <span><?php echo out( xl( 'Cancel' ) ); ?></span>
            </a>
        </td>
  </tr>
</table>

<div class="rule_detail edit text">
    <p class="header"><?php echo out( xl( 'Add criteria' ) ); ?> </p>
    <ul>
    <?php foreach ( $allowed as $type ) { ?>
        <li>
        <a href="index.php?action=edit!choose_criteria&id=<?php echo out( $ruleId ); ?>&group_id=<?php echo out( $groupId ); ?>&type=<?php echo out( $viewBean->type ); ?>&criteriaType=<?php echo out( $type->code ); ?>" onclick="top.restoreSession()">
            <?php echo out( xl( $type->lbl ) ); ?>
        </a>
        </li>
    <?php } ?>
    </ul>
</div>
