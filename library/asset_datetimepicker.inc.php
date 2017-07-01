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

// Performance considerations -
// 1. Add an option that dictates if standard english should be translated (default: no)
// 2. Move this logic to login and store json string in $_SESSION
//
$mm = array('January','February','March','April','May','June','July ', 
		'August', 'September', 'October', 'November', 'December');
$dttm_ix = count($mm);
while ($dttm_ix-- > 0) {
	$mm[$dttm_ix] = xla($mm[$dttm_ix]);
}
$dws = array();
$dd = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
$dttm_ix = count($dd);
while ($dttm_ix-- > 0) {
	array_unshift($dws, xla(substr($dd[$dttm_ix], 0, 3)));
	$dd[$dttm_ix] = xla($dd[$dttm_ix]);
}
// Ready to construct json translatable structure
$dttm_wrk = array('months' => $mm, 'dayOfWeekShort' => $dws, 'dayOfWeek' => $dd);
// Initialize default settings needed for dttm object
$dttm_settings = array('i18n'=>array('en'=>$dttm_wrk), 
		'rtl' => ($_SESSION['language_direction'] == 'rtl') ? 'true' : 'false'
);
// Add additional values from application logic below (e.g. user/session settings)

// -- Bring in components if needed --
//if (class_exists('OpenEMR\Core\Header')) {
	// Datepicker won't work unless added to the Header::setupHeader list
//} else {
	// This will fail if base script has not brought in jQ
	$oemr_dttm_dir = $GLOBALS['assets_static_relative']."/jquery-datetimepicker-2-5-4/build";
	printf('<link rel="stylesheet" type="text/css" href="%s/jquery.datetimepicker.min.css">
			<script type="text/javascript" src="%s/jquery.datetimepicker.full.min.js"></script>',
			$oemr_dttm_dir, $oemr_dttm_dir);
//}
?>

<script type="text/javascript">
var dttm_sel_map = {
	"cls": {
		"oemr_date": {'timepicker':false, 'format': 'Y-m-d'},
		"oemr_time": {'datepicker':false, 'format': 'H:i'},
		"oemr_datetime": {'format': 'Y-m-d H:i'},
	},
	"inp": {
		"date": {'timepicker':false, 'format': 'Y-m-d'},
		"time": {'datepicker':false, 'format': 'H:i'},
		"datetime-local": {'format': 'Y-m-d H:i'},
	}
};
var jq_sel = [];
Object.keys(dttm_sel_map.cls).forEach(function(dttm_cls) {
	jq_sel.push("."+dttm_cls);
});
Object.keys(dttm_sel_map.inp).forEach(function(dttm_type) {
	jq_sel.push("input[type="+dttm_type+"]");
});
jQuery(jq_sel.join(",")).each(function() {
    var ctl = jQuery(this);
    var ctl_opts = <?php echo json_encode($dttm_settings); ?>;
    Object.keys(dttm_sel_map.cls).forEach(function(dttm_cls) {
        if (ctl.hasClass(dttm_cls)) {Object.assign(ctl_opts, dttm_sel_map.cls[dttm_cls]);}
    });
    Object.keys(dttm_sel_map.inp).forEach(function(dttm_typ) {
        if (ctl.attr("type") == dttm_typ) {Object.assign(ctl_opts, dttm_sel_map.inp[dttm_typ]);}
    });
    <?php // Other properties (without validation) ?>
    var ctl_data = ctl.data();
    for (var ctl_prop in ctl_data) {
        if (ctl_prop.toString().substr(0,5) == "dttm_") { // "_" needed to avoid jQ 3 case conversion
            ctl_opts[ctl_prop.toString().slice(5)] = ctl_data[ctl_prop];
        }
    }
    ctl.datetimepicker(ctl_opts);
});
</script>
