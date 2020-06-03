<?php

/**
 * Script to configure the Rules.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ensoftek
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Gacl\GaclApi;

?>

<table class="table header">
  <tr>
        <td class="title"><?php echo xlt('Clinical Decision Rules Alert Manager'); ?></td>
  </tr>
  <tr>
        <td>
            <a href="javascript:document.cdralertmgr.submit();" class="btn btn-primary" onclick="top.restoreSession()"><span><?php echo xlt('Save'); ?></span></a><a href="javascript:document.cdralertmgr.reset();" class="btn btn-secondary" onclick="top.restoreSession()"><span><?php echo xlt('Reset'); ?></span></a>
        </td>
  </tr>
</table>

&nbsp;

<form name="cdralertmgr" method="post" action="index.php?action=alerts!submitactmgr" onsubmit="return top.restoreSession()">
<table cellpadding="1" cellspacing="0" class="table table-bordered">
        <tr class="showborder_head">
                <th width="250px"><?php echo xlt('Title'); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo xlt('Active Alert'); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo xlt('Passive Alert'); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="10px"><?php echo xlt('Patient Reminder'); ?></th>
                <th width="40px">&nbsp;</th>
                <th width="100px"><?php echo xlt('Access Control'); ?> <span title='<?php echo xla('User is required to have this access control for Active Alerts and Passive Alerts'); ?>'>?</span></th>
                <th></th>
        </tr>
        <?php $index = -1; ?>
        <?php foreach ($viewBean->rules as $rule) {?>
            <?php $index++; ?>
        <tr height="22">
                <td><?php echo xlt($rule->get_rule());?></td>
                <td>&nbsp;</td>
                <?php if ($rule->active_alert_flag() == "1") {  ?>
                    <td><input type="checkbox" name="active[<?php echo attr($index); ?>]" checked="yes"></td>
                <?php } else {?>
                    <td><input type="checkbox" name="active[<?php echo attr($index); ?>]" ></td>
                <?php } ?>
                <td>&nbsp;</td>
                <?php if ($rule->passive_alert_flag() == "1") { ?>
                    <td><input type="checkbox" name="passive[<?php echo attr($index); ?>]]" checked="yes"></td>
                <?php } else {?>
                    <td><input type="checkbox" name="passive[<?php echo attr($index); ?>]]"></td>
                <?php } ?>
                <td>&nbsp;</td>
                <?php if ($rule->patient_reminder_flag() == "1") { ?>
                    <td><input type="checkbox" name="reminder[<?php echo attr($index); ?>]]" checked="yes"></td>
                <?php } else {?>
                    <td><input type="checkbox" name="reminder[<?php echo attr($index); ?>]]"></td>
                <?php } ?>
                 <td>&nbsp;</td>
                 <td>
                        <?php //Place the ACO selector here
                        $gacl_temp = new GaclApi();
                        $list_aco_objects = $gacl_temp->get_objects(null, 0, 'ACO');
                        foreach ($list_aco_objects as $key => $value) {
                            asort($list_aco_objects[$key]);
                        }

                        echo "<select name='access_control[" . $index . "]'>";
                        foreach ($list_aco_objects as $section => $array_acos) {
                            $aco_section_data = $gacl_temp->get_section_data($section, 'ACO');
                            $aco_section_title = $aco_section_data[3];
                            foreach ($array_acos as $aco) {
                                $aco_id = $gacl_temp->get_object_id($section, $aco, 'ACO');
                                $aco_data = $gacl_temp->get_object_data($aco_id, 'ACO');
                                $aco_title = $aco_data[0][3];
                                $select = '';
                                if ($rule->access_control() == $section . ":" . $aco) {
                                    $select = 'selected';
                                }

                                echo "<option value='" . attr($section) . ":" . attr($aco) . "' " . $select . ">" . xlt($aco_section_title) . ": " . xlt($aco_title)  . "</option>";
                            }
                        }

                        echo "</select>";
                        ?>
                 </td>
                <td><input style="display:none" name="id[<?php echo attr($index); ?>]]" value="<?php echo attr($rule->get_id()); ?>" /></td>
        </tr>
        <?php }?>
</table>
</form>
