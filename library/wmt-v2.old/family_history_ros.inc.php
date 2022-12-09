<?php
if(!isset($field_prefix)) $field_prefix = '';
include_once($GLOBALS['srcdir'].'/wmt-v2/ros_functions.inc');
if(!isset($dashboard)) {
	if(!class_exists('wmtDashboard')) include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
	$dashboard = wmtDashboard::getPidDashboard($pid);
}
if(!isset($fh_options_unused)) {
	$fh_options_unused = LoadList('Family_History_Choices','inactive','seq','',
	'AND (notes LIKE "%'.$frmdir.'%" || notes LIKE "%::all::%")');
}
if(!isset($fh_options)) {
	$fh_options = LoadList('Family_History_Choices','active','seq','',
		'AND (notes LIKE "%'.$frmdir.'%" || notes LIKE "%all%")');
}
$num_fh_options = count($fh_options);
if(!isset($fh_ros_position)) $fh_ros_position = 'bottom';
foreach($fh_options as $o) {
	if(!isset($dt['tmp_fh_rs_'.$o['option_id']])) 
										$dt['tmp_fh_rs_'.$o['option_id']] = '';
	if($first_pass) $dt['tmp_fh_rs_'.$o['option_id'].'_nt'] = 
		GetROSKeyComment($dashboard->id,'dashboard','fh_rs_'.$o['option_id'],$pid);	
}

if($first_pass) {
	if($dashboard->db_fh_extra_yes) {
		$fh_yes = explode('|', $dashboard->db_fh_extra_yes);
		foreach($fh_options as $opt) {
			if(in_array($opt['option_id'], $fh_yes)) $dt['tmp_fh_rs_'.$opt['option_id']] = 'y';
		}
	}
	if($dashboard->db_fh_extra_no) {
		$fh_no = explode('|', $dashboard->db_fh_extra_no);
		foreach($fh_options as $opt) {
			if(in_array($opt['option_id'], $fh_no)) $dt['tmp_fh_rs_'.$opt['option_id']] = 'n';
		}
	}
}

$cols = checkSettingMode('wmt::family_history_ros_cols','',$frmdir);
$use_fh_ros_note = checkSettingMode('wmt::family_history_ros_note','',$frmdir);
if(!$cols) $cols = 2;
$col1 = $col2 = $col3 = $col4 = '';

// FIX!  This needs to handle 3 and 4 columns for sure
$half = $num_fh_options / $cols;
$col1 = intval($half);
if($cols == 2) {
	$col2 = $col1;
	if($half != $col1) $col1++;
	$colwidth = '50%';
}
if($cols == 3) {
	$diff = intval(($half - $col1) * 10);
	$col3 = $col2 = $col1;
	if($half != $col1) $col1++;
	if($diff > 5) $col2++;
	$colwidth = '33%';
}
if($cols == 4) {
	$diff = intval(($half - $col1) * 10);
	$col4 = $col3 = $col2 = $col1;
	if($half != $col1) $col1++;
	if($diff > 3) $col2++;
	if($diff > 6) $col3++;
	$colwidth = '25%';
}
// echo "Columns ($cols)  And Count",count($fh_options),"<br>\n";
// echo "Diff <$diff>  1 ($col1)  2 [$col2]  3 <$col3>  4{$col4}<br>\n";
if(!isset($internal_style)) $internal_style = 'wmt';
if(count($fh_options)) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
		<td colspan="<?php echo $cols; ?>" <?php echo ($fh_ros_position == 'bottom') ? 'class="wmtBorder1T"' : ''; ?>>
			<div class="wmtLabel" style="float: left; margin-left: 20px;">Has anyone in your family ever been diagnosed with:</div>
			<div style="float: right; padding-right: 20px;"><a href="javascript:;" tabindex="-1" class="css_button_small" onclick="toggleFamilyExtraNo(); "><span>Set All to 'No'</span></a></div>
		</td>
	</tr>
    <td style="width: <?php echo $colwidth; ?>;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
			<?php
			$cnt = 0;
			while($cnt < $col1) {
				$o = $fh_options[$cnt];
				GenerateROSLine('tmp_fh_rs_'.$o['option_id'],$o['title'],
					$dt['tmp_fh_rs_'.$o['option_id']],$dt['tmp_fh_rs_'.$o['option_id'].
					'_nt'],'',false,$use_fh_ros_note,$internal_style);
				$cnt++;
			}
			?>
      </table>
    </td>
    <td style="width: <?php echo $colwidth; ?>;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
			<?php
			$cnt = 0;
			while($cnt < $col2) {
				$o = $fh_options[$cnt + $col1];
				GenerateROSLine('tmp_fh_rs_'.$o['option_id'],$o['title'],
					$dt['tmp_fh_rs_'.$o['option_id']],$dt['tmp_fh_rs_'.$o['option_id'].
					'_nt'],'',false,$use_fh_ros_note,$internal_style);
				$cnt++;
			}
			?>
				<?php if($col2 < $col1) { ?>
      	<tr><!-- For spacing only -->
        	<td>&nbsp;</td>
      	</tr>
				<?php } ?>
			</table>
		</td>
		<?php if($cols >= 3) { ?>
    <td style="width: <?php echo $colwidth; ?>;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
			<?php
			$cnt = 0;
			while($cnt < $col3) {
				$o = $fh_options[$cnt + $col1 + $col2];
				GenerateROSLine('tmp_fh_rs_'.$o['option_id'],$o['title'],
					$dt['tmp_fh_rs_'.$o['option_id']],$dt['tmp_fh_rs_'.$o['option_id'].
					'_nt'],'',false,$use_fh_ros_note,$internal_style);
				$cnt++;
			}
			?>
				<?php if($col3 < $col1) { ?>
      	<tr><!-- For spacing only -->
        	<td>&nbsp;</td>
      	</tr>
				<?php } ?>
			</table>
		</td>
		<?php } ?>
		<?php if($cols == 4) { ?>
    <td style="width: <?php echo $colwidth; ?>;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
			<?php
			$cnt = 0;
			while($cnt < $col4) {
				$o = $fh_options[$cnt + $col1 + $col2 + $col3];
				GenerateROSLine('tmp_fh_rs_'.$o['option_id'],$o['title'],
					$dt['tmp_fh_rs_'.$o['option_id']],$dt['tmp_fh_rs_'.$o['option_id'].
					'_nt'],'',false,$use_fh_ros_note,$internal_style);
				$cnt++;
			}
			?>
				<?php if($col4 < $col1) { ?>
      	<tr><!-- For spacing only -->
        	<td>&nbsp;</td>
      	</tr>
				<?php } ?>
			</table>
		</td>
		<?php } ?>
	</tr>
</table>
<script type="text/javascript">
function toggleFamilyExtraNo()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      if(document.forms[0].elements[i].name.indexOf('tmp_fh_rs_') != -1) {
        document.forms[0].elements[i].selectedIndex = '2';
      }
    }
  }
}
</script>
<?php } ?>
<div style="display: none;">
	<!-- This div is for all the 'DO NOT USE' keys to retain history -->
	<?php
	foreach($fh_options_unused as $o) {
		if(strpos(strtolower($o['notes']),'do not use') === false) continue;
		if(isset($rs[$o['option_id']])) {
			GenerateHiddenYesNo($field_prefix,$o['option_id'], $yes_choices, $no_choices);
		}
	}
?>
</div>
