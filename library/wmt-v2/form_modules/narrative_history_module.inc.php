<?php
?>
<table style='width:100%;border-collapse:collapse'>
	<tr class='wmtColorHeader'>
		<th class="wmtLabel wmtBorder1B" style="width:100px">
			Date
		</th>
		<th class="wmtLabel wmtBorder1B wmtBorder1L" style="width:100px">
			Start
		</th>
		<th class="wmtLabel wmtBorder1B wmtBorder1L" style="width:100px">
			End
		</th>
		<th class="wmtLabel wmtBorder1B wmtBorder1L">
			Counselor
		</th>
	</tr>
					
<?php 
	if (!$id) $id = 0;
	$query = "SELECT * FROM form_psyc_progress WHERE pid='$pid' AND id <> $id ORDER BY date DESC ";
	$result = sqlStatement($query);
	
	if (sqlNumRows($result) > 0) {
		while ($row = sqlFetchArray($result)) {
			$notes = '';
			if ($row['progress_flag']) $notes = '<span style="color:green">[CASE NOTE]</span> ';
			if ($row['late_flag']) $notes .= '<span style="color:red">[LATE ENTRY]</span> ';
			$notes .= $row['progress_notes'];
			$start = '';
			if ($row['start_hour'] || $row['start_min'])
				$start = $row['start_hour'] . ":" . $row['start_min'] . " " . $row['start_apm'];
			$end = '';
			if ($row['end_hour'] || $row['end_min'])
				$end = $row['end_hour'] . ":" . $row['end_min'] . " " . $row['end_apm'];
?>
	<tr>
		<td class="wmtOutput wmtBorder1B" style="width:100px">
		<?php echo date('Y-m-d',strtotime($row['progress_date'])) ?>
		</td>
		<td class="wmtOutput wmtBorder1B wmtBorder1L" style="width:100px">
			<?php echo $start ?>
		</td>
		<td class="wmtOutput wmtBorder1B wmtBorder1L" style="width:100px">
			<?php echo $end ?>
		</td>
		<td class="wmtOutput wmtBorder1B wmtBorder1L">
			<?php echo UserNameFromID($row['counselor']) ?>
		</td>
	</tr>
	<tr>
		<td colspan="4" class="wmtOutput wmtBorder1B" style="white-space:pre-wrap"><?php echo $notes ?></td>
	</tr>
<?php 
			$spacer = "margin-top:10px";
		} 
	} 
	else { 
?>
	<tr>
		<td colspan="4">
			<b>No previous progress notes available</b>	
		</td>
	</tr>
<?php } ?>
</table>
