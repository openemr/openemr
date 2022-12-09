<?php

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtcase.class.php');

use OpenEMR\Core\Header;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : '';
$field_id = isset($_REQUEST['field_id']) ? $_REQUEST['field_id'] : '';
$form_name = isset($_REQUEST['form_name']) ? $_REQUEST['form_name'] : '';

$logsData = wmtCase::fetchAlertLogsByParam(array(
									'field_id' => $field_id,
									'form_name' => $form_name,
									'pid' => $pid,
									'form_id' => $form_id
								));

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Logs'), ENT_NOQUOTES); ?></title>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
</head>
<body>
<div class="table-responsive table-container datatable-container c-table-bordered o-overlay">
	<table id="results_table" class="table text table-bordered table-striped table-sm">
		<thead class="thead-dark">
			<tr class="showborder_head">
				<th width="25" align="center"><?php echo xl('Sr.'); ?></th>
				<th><?php echo xl('New Value'); ?></th>
				<th><?php echo xl('Old Value'); ?></th>
				<th><?php echo xl('Username'); ?></th>
				<th><?php echo xl('DateTime'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
			$ci = 1;
			foreach ($logsData as $key => $item) {
				?>
				<tr>
					<td align="center" valign="top"><?php echo $ci; ?></td>
					<td style="vertical-align: text-top;"><div style="white-space: pre;"><?php echo $item['new_value']; ?></div></td>
					<td style="vertical-align: text-top;"><div style="white-space: pre;"><?php echo $item['old_value']; ?></div></td>
					<td valign="top"><?php echo $item['user_name']; ?></td>
					<td valign="top"><?php echo date('d-m-Y h:i:s',strtotime($item['date'])); ?></td>
				</tr>
				<?php
				$ci++;
			}
		?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		
		table = jQuery('#results_table').DataTable({
			'initComplete': function(settings){
				//Handle Footer
				handleDataTable(this);
		    },
			'order': [[1, 'asc']],
			'pageLength': 50,
			'bLengthChange': true,
			'ordering': false,
			'autoWidth':true,
			'scrollY': '100vh',
    		'scrollCollapse': true,
    		'responsive': {
			    details: false
			}
		});
		
	});
</script>
</body>
</html>