<?php
if(!isset($dt['bd_date'])) $dt['bd_date'] = '';
if(!isset($dt['bd_result'])) $dt['bd_result'] = '';
if(!isset($dt['bd_comm'])) $dt['bd_comm'] = '';
if(!isset($dt['bd_rev'])) $dt['bd_rev'] = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($bone)) $bone=array();
echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
echo "		<tr>\n";
echo "			<td class='LabelCenterBorderB' style='width: 90px'>Date</td>\n";
echo "			<td class='LabelCenterBorderLB'>Result</td>\n";
echo "			<td class='LabelCenterBorderLB'>Notes</td>\n";
echo "			<td class='LabelCenterBorderLB' style='width: 90px'>Reviewed</td>\n";
if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','uper') && $unlink_allow) {
	echo "			<td class='LabelBorderLB' style='width: 175px'>&nbsp;</td>\n";
} else  if($unlink_allow) {
	echo "			<td class='LabelBorderLB' style='width: 115px'>&nbsp;</td>\n";
} else  if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super')) {
	echo "			<td class='LabelBorderLB' style='width: 115px'>&nbsp;</td>\n";
} else {
	echo "			<td class='LabelBorderLB' style='width: 65px'>&nbsp;</td>\n";
}
echo "		</tr>\n";
$cnt=1;
if(isset($bone) && (count($bone) > 0)) {
	foreach($bone as $prev) {
		echo "<tr>\n";
		echo "<td class='BodyBorderB'><input name='bd_id_".$cnt."' id='bd_id_".$cnt."' type='hidden' readonly='readonly' value='".$prev['id']."' /><input name='bd_num_links_$cnt' id='bd_num_links_$cnt' type='hidden' tabindex='-1' value='".$prev['num_links']."' /><input name='bd_dt_".$cnt."' id='bd_dt_".$cnt."' class='FullInput' tabindex='-1' type='text' value='".$prev['begdate']."' title='YYYY-MM-DD' /></td>\n";
		echo "<td class='BodyBorderLB'><input name='bd_result_".$cnt."' id='bd_result_".$cnt."' class='FullInput' tabindex='-1' value='".$prev['extrainfo']."' /></td>\n";
		echo "<td class='BodyBorderLB'><input name='bd_comm_".$cnt."' id='bd_comm_".$cnt."' class='FullInput' type='text' tabindex='-1' value='".$prev['comments']."' /></td>\n";
		echo "<td class='BodyBorderLB'><input name='bd_rev_".$cnt."' id='bd_rev_".$cnt."' class='FullInput' type='text' tabindex='-1' value='".$prev['referredby']."' /></td>\n";
		echo "<td class='BodyBorderLB'><div style='float: left; padding-left: 2px;'><a class='css_button_small' tabindex='-1' onClick='return UpdateBoneDensity(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\");' href='javascript:;'><span>Update</span></a></div>";
		if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super')) {
			echo "<div style='float: left; padding-left: 2px;'><a class='css_button_small' tabindex='-1' onClick='return DeleteBoneDensity(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\");' href='javascript:;'><span>Delete</span></a></div>\n";
		}
		if($unlink_allow) {
			echo "<div style='float: left; padding-left: 2px;'><a class='css_button_small' tabindex='-1' onClick='return UnlinkBoneDensity(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\");' href='javascript:;'><span>Un-Link</span></a></div>\n";
		}
		echo "</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
}
$cnt--;
echo "		<tr>\n";
echo "			<td class='BodyBorderB'><input name='bd_date' id='bd_date' class='FullInput' type='text' value='",$dt['bd_date'],"' /></td>\n";
echo "			<td class='BodyBorderLB'><input name='bd_result' id='bd_result' class='FullInput' type='text' value='".$dt['bd_result']."' /></td>\n";
echo "			<td class='BodyBorderLB'><input name='bd_comm' id='bd_comm' class='FullInput' type='text' value='",$dt['bd_comm'],"' /></td>\n";
echo "			<td class='BodyBorderLB'><input name='bd_rev' id='bd_rev' class='FullInput' type='text' value='",$dt['bd_rev'],"' /></td>\n";
echo "			<td class='BodyBorderLB'>&nbsp;</td>\n";
echo "		</tr>\n";
echo "		</tr>\n";
echo "			<td class='",(($frmn=='form_acog_antepartum_D')?'wmtCollapseBarBlack':'wmtCollapseBar'),"'><a class='css_button' onClick='return SubmitBoneDensity(\"$base_action\",\"$wrap_mode\",\"$id\");' href='javascript:;'><span>Add Another</span></a></td>\n";
echo "			<td class='",(($frmn=='form_acog_antepartum_D')?'wmtCollapseBarBlack':'wmtCollapseBar'),"' colspan='4'><input name='tmp_bd_cnt' id='tmp_bd_cnt' type='hidden' tabindex='-1' value='$cnt' />&nbsp;</td>\n";
echo "		</tr>\n";
echo "		</table>\n";
?>
