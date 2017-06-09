<?php
/**
 * Drop-in datetimepicker control
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This script should be included after </body> tag.
 * Main form(s) should specify date fields with one of the following classes :
 * "oemr_datetime" : date and time (default)
 * "oemr_date" : only date
 * "oemr_time" : only time
 * Additional settings should be specified with data-dttm-* attributes of the field.
**/
$oemr_dttm_dir = $GLOBALS['assets_static_relative']."/jquery-datetimepicker-2-5-4/build";
?>

<link rel="stylesheet" type="text/css" href="<?php echo $oemr_dttm_dir ?>/jquery.datetimepicker.min.css" type="text/css">
<script type="text/javascript" src="<?php echo $oemr_dttm_dir ?>/jquery.datetimepicker.full.min.js"></script>

<script type="text/javascript">
jQuery('.oemr_date, .oemr_datetime, .oemr_time').each(function() {
	var ctl = jQuery(this);
	var ctl_opts = {};
	if (ctl.hasClass('oemr_date')) {
		ctl_opts['timepicker'] = false;
		ctl_opts['format'] = 'Y-m-d';
	}
	if (ctl.hasClass('oemr_time')) {
		ctl_opts['datepicker'] = false;
		ctl_opts['format'] = 'H:i';
	}
	<?php // TBD: Set inherited values from application logic (e.g. user/session settings) ?>
	
	<?php // Other properties (without validation) ?>
	var ctl_data = ctl.data();
	for (var ctl_prop in ctl_data) {
		if (ctl_prop.toString().substr(0,5) == "dttm-") {
			var ctl_prop_val = ctl_data[ctl_prop];
			if (ctl_prop_val.toString() == 'true') {ctl_prop_val=true;}
			if (ctl_prop_val.toString() == 'false') {ctl_prop_val=false;}
			ctl_opts[ctl_prop.toString().slice(5)] = ctl_prop_val;
		}
	}
	ctl.datetimepicker(ctl_opts);
});
</script>
