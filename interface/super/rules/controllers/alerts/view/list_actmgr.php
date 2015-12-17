<?php
/**
 * Script to configure the Rules.
 *
 * Copyright (C) 2015 Brady Miller <brady@sparmy.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__)."/../../../../../../library/acl.inc"); 
global $phpgacl_location;
require_once("$phpgacl_location/gacl_api.class.php");
?>

<table class="header">
  <tr>
        <td class="title"><?php echo out( xl('Clinical Decision Rules Alert Manager') ); ?></td>
        
  </tr>
  <tr>
        <td>
        	<a href="javascript:document.cdralertmgr.submit();" class="css_button" onclick="top.restoreSession()"><span><?php echo out( xl('Save') ); ?></span></a><a href="javascript:document.cdralertmgr.reset();" class="css_button" onclick="top.restoreSession()"><span><?php echo out( xl('Reset') ); ?></span></a>
        </td>
  </tr>        
</table>

&nbsp;

<form name="cdralertmgr" method="post" action="index.php?action=alerts!submitactmgr" onsubmit="return top.restoreSession()">
<table cellpadding="1" cellspacing="0" class="showborder">
        <tr class="showborder_head">
                <th width="250px"><?php echo out( xl('Title') ); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo out( xl('Active Alert') ); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo out( xl('Passive Alert') ); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo out( xl('Patient Reminder') ); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="100px"><?php echo out( xl('Access Control') ); ?> <span title='<?php echo out( xl('User is required to have this access control for Active Alerts and Passive Alerts') ); ?>'>?</span></th>
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
                 <td>&nbsp;</td>
                 <td>
                        <?php //Place the ACO selector here
                        $gacl_temp = new gacl_api(); 
                        $list_aco_objects = $gacl_temp->get_objects(NULL, 0, 'ACO');
                        foreach ($list_aco_objects as $key => $value) {
                          asort($list_aco_objects[$key]);
                        }
                        echo "<select name='access_control[" . $index . "]'>";
                        foreach ($list_aco_objects as $section => $array_acos) {
                          $aco_section_data = $gacl_temp->get_section_data($section, 'ACO');
                          $aco_section_title = $aco_section_data[3];
                          foreach ($array_acos as $aco) {
                            $aco_id = $gacl_temp->get_object_id($section, $aco,'ACO');
                            $aco_data = $gacl_temp->get_object_data($aco_id, 'ACO');
                            $aco_title = $aco_data[0][3];
                            $select = '';
                            if ($rule->access_control() == $section.":".$aco) {
                              $select = 'selected';
                            }
                            echo "<option value='" . attr($section) . ":" . attr($aco) . "' " . $select . ">" . xlt($aco_section_title) . ": " . xlt($aco_title)  . "</option>";
                          }
                        }
                        echo "</select>";
                        ?>
                 </td> 
                <td><input style="display:none" name="id[<?php echo $index ?>]]" value=<?php echo out($rule->get_id()); ?> /></td>								
        </tr>
		<?php }?>
</table>
</form>


