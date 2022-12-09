<?php
$ros_count = count($ros_options);
$half = $ros_count / 2;
$col1 = intval($half);
$col2 = $col1;
if($half != $col1) $col2++;
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td style="width: 50%">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
      			<tr>
        			<td class="wmtLabel" colspan="3">Please select yes or no:</td>
      			</tr>
						<?php
						$cnt = 0;
						while($cnt < $col1) {
							$o = $ros_options[$cnt];
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									$rs[$o['option_id'].'_nt'], '', false);
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
      			</tr>
						<?php
						$cnt = 0;
						while($cnt < $col2) {
							$o = $ros_options[$cnt + $col1];
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'],'', false);
							$cnt++;
						}
						?>
					</table>
				</td>
			</tr>
			<tr>
				<td class="wmtBody">Comments:</td>
       	<td><div style="float: left; padding-left: 30px; "><a class="css_button" tabindex="-1" onclick="toggleROStoNo();" href="javascript:;"><span>Set All to 'NO'</span></a></div><div style="float: right; padding-right: 30px; "><a class="css_button" tabindex="-1" onclick="toggleROStoNull();" href="javascript:;"><span>Clear All</span></a></div></td>
			</tr>
			<tr>
				<td colspan="2"><textarea name="ros_nt" id="ros_nt" rows="4" class="wmtFullInput"><?php echo htmlspecialchars($wmt_ros{'ros_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
				<td>&nbsp;</td>
			</tr>
    </table>
		<div style="display: none;">
			<!-- This div is for all the 'DO NOT USE' keys to retain history -->
			<?php
				foreach($ros_unused as $o) {
					if(strpos(strtolower($o['notes']),'do not use') === false) continue;
					if(isset($rs[$o['option_id']])) {
						GenerateHiddenROS($o['option_id'], $rs[$o['option_id']]);
					}
				}
			?>
		</div>
<?php ?>
