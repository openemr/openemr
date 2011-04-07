<table class="header">
  <tr>
        <td class="title"><?php echo out( xl('Clinical Decision Rules Alert Manager') ); ?></td>
        
  </tr>
  <tr>
        <td>
        	<a href="javascript:document.cdralertmgr.submit();" class="css_button"><span><?php echo out( xl('Save') ); ?></span></a><a href="javascript:document.cdralertmgr.reset();" class="css_button" ><span><?php echo out( xl('Reset') ); ?></span></a>
        </td>
  </tr>        
</table>

&nbsp;

<form name="cdralertmgr" method="post" action="index.php?action=alerts!submitactmgr" >
<table cellpadding="1" cellspacing="0" class="showborder">
        <tr class="showborder_head">
                <th width="250px"><?php echo out( xl('Title') ); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo out( xl('Active Alert') ); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo out( xl('Passive Alert') ); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo out( xl('Patient Reminder') ); ?></th>
                <th></th>
        </tr>
        <?php $index = -1; ?>
        <?php foreach($viewBean->rules as $rule) {?>
        <?php $index++; ?>
        <tr height="22">
                <td><?php echo out( xl($rule->get_rule()) );?></td>
				<td>&nbsp;</td>
				<?php if ($rule->active_alert_flag() == "1"){  ?>
                	<td><input type="checkbox" name="active[<?php echo $index ?>]" checked="yes"></td>
                <?php }else {?>
                	<td><input type="checkbox" name="active[<?php echo $index ?>]" ></td>
				<?php } ?>                
				<td>&nbsp;</td>
                <?php if ($rule->passive_alert_flag() == "1"){ ?>
                	<td><input type="checkbox" name="passive[<?php echo $index ?>]]" checked="yes"></td>
                <?php }else {?>
	                <td><input type="checkbox" name="passive[<?php echo $index ?>]]"></td>
				<?php } ?>                
				<td>&nbsp;</td>
                <?php if ($rule->patient_reminder_flag() == "1"){ ?>
                	<td><input type="checkbox" name="reminder[<?php echo $index ?>]]" checked="yes"></td>
                <?php }else {?>
	                <td><input type="checkbox" name="reminder[<?php echo $index ?>]]"></td>
				<?php } ?>                
                <td><input style="display:none" name="id[<?php echo $index ?>]]" value=<?php echo out($rule->get_id()); ?> /></td>								
        </tr>
		<?php }?>
</table>
</form>


