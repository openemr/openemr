<?php
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($dt['yes_no_nt'])) $dt['yes_no_nt'] = '';
if(!isset($yes_choices)) $yes_choices = '';
if(!isset($no_choices)) $no_choices = '';
// echo "In the module with yes choices [$yes_choices]<br>\n";
// echo "In the module with No choices [$no_choices]<br>\n";
$option_count = count($yes_no_options);
$half = $option_count / 2;
$col1 = intval($half);
$col2 = $col1;
if($half != $col1) $col2++;
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php if($frmdir == 'acog_complete') { ?>
			<tr>
				<td class="wmtChapter" colspan="2">Includes patient, baby's father or anyone in either family with:</td>
			</tr>
			<?php } ?>
      <tr>
        <td style="width: 50%">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
						<tr>
							<td>&nbsp;</td>
        			<td class="wmtLabel">Please mark yes or no:</td>
        			<td class="wmtLabel">Yes</td>
        			<td class="wmtLabel">No</td>
      			</tr>
						<?php
						$cnt = 0;
						while($cnt < $col1) {
							$o = $yes_no_options[$cnt];
							GenerateYesNoLine($field_prefix,$o['option_id'],$o['title'],
																							$yes_choices,$no_choices);
							$cnt++;
						}
						?>
						<?php if($col1 < $col2) { ?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
          </table>
        </td>
        <td style="width: 50%">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
        			<td class="wmtLabel">Yes</td>
        			<td class="wmtLabel">No</td>
						</tr>
						<?php
						$cnt = 0;
						while($cnt < $col2) {
							$o = $yes_no_options[$cnt + $col1];
							GenerateYesNoLine($field_prefix,$o['option_id'], $o['title'],
																					$yes_choices, $no_choices);
							$cnt++;
						}
						?>
					</table>
				</td>
			</tr>
			<tr>
				<td class="wmtBody">&nbsp;&nbsp;Comments or Description of Other:</td>
        <td><div style="float: left; padding-left: 30px; "><a class="css_button" tabindex="-1" onclick="setChecks('<?php echo $field_prefix; ?>', '_no', true); setChecks('<?php echo $field_prefix; ?>', '_yes', false); " href="javascript:;"><span>Set All to 'NO'</span></a></div>
					<div style="float: right; padding-right: 35px; "><a class="css_button" tabindex="-1" onclick="setChecks('<?php echo $field_prefix; ?>', '_no', false); setChecks('<?php echo $field_prefix; ?>', '_yes', false);" href="javascript:;"><span>Clear All</span></a></div></td>
			</tr>
			<tr>
				<td colspan="2"><textarea name="<?php echo $field_prefix; ?>yes_no_nt" id="<?php echo $field_prefix; ?>yes_no_nt" rows="4" class="wmtFullInput"><?php echo htmlspecialchars($dt{$field_prefix.'yes_no_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>
    </table>
		<div style="display: none;">
			<!-- This div is for all the 'DO NOT USE' keys to retain history -->
			<?php
				foreach($ros_unused as $o) {
					if(strpos(strtolower($o['notes']),'do not use') === false) continue;
					if(isset($rs[$o['option_id']])) {
						GenerateHiddenYesNo($field_prefix,$o['option_id'], $yes_choices, $no_choices);
					}
				}
			?>
		</div>
<?php ?>
