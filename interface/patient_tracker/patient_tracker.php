<?php
/**
 *  Patient Tracker (Patient Flow Board)
 *
 *  This program displays the information entered in the Calendar program ,
 *  allowing the user to change status and view those changed here and in the Calendar
 *  Will allow the collection of length of time spent in each status
 *
 * @package OpenEMR
 * @link http://www.open-emr.org
 * @author  Terry Hill <terry@lilysystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient_tracker.inc.php");
require_once("$srcdir/user.inc");

// Source PHP documentation (@author - jimpoz at jimpoz dot com)
function array_orderby() {
	$args = func_get_args();
	$data = array_shift($args);
	foreach ($args as $n => $field) {
		if (is_string($field)) {
			$tmp = array();
			foreach ($data as $key => $row)
				$tmp[$key] = $row[$field];
				$args[$n] = $tmp;
		}
	}
	$args[] = &$data;
	call_user_func_array('array_multisort', $args);
	return array_pop($args);
}
// mdsupport - user_settings prefix
$uspfx = substr(__FILE__, strlen($webserver_root)) . '.';
$setting_new_window = prevSetting($uspfx, 'setting_new_window', 'form_new_window', ' ');

#define variables, future enhancement allow changing the to_date and from_date
#to allow picking a date to review

// For auth users (with appts), check if allowed to see all appointments
// They can also choose to filter their own appointments
$provider = null;
if (!is_null($_POST['form_provider'])) {
	$provider = $_POST['form_provider'];
}
else if ($_SESSION['userauthorized']) {
	$provider = $_SESSION['authUserID'];
}
$facility  = !is_null($_POST['form_facility']) ? $_POST['form_facility'] : null;
$form_apptstatus = !is_null($_POST['form_apptstatus']) ? $_POST['form_apptstatus'] : null;
$form_apptcat=null;
if(isset($_POST['form_apptcat']))
{
	if($form_apptcat!="ALL")
	{
		$form_apptcat=intval($_POST['form_apptcat']);
	}
}

$appointments = array();
$today = date("Y-m-d");

# go get the information and process it
$appointments = fetch_Patient_Tracker_Events($today, $today, $provider, $facility, $form_apptstatus, $form_apptcat);
$appointments = sortAppointments( $appointments, 'time' );

// Appointment status mappings
$apt_stats = array();
$li_stats_dropdown = '';
$lres = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = ? AND activity=1
						ORDER BY seq", array('apptstat'));
while ( $lrow = sqlFetchArray ( $lres ) ) {
	// if exists, remove the legend character
	$title = trim($lrow['title']);
	$ttok = explode(' ', $title, 2);
	if ($ttok[0] == $lrow['option_id']) { $title = $ttok[1]; }
	$apt_stats[$lrow['option_id']] = xl($title);
	$li_stats_dropdown .= sprintf('<li data-selected="%s" style="display:none;"><a href="#">%s</a></li>', 
			htmlspecialchars($lrow['option_id']), $apt_stats[$lrow['option_id']]);
}
// Allowed status transitions : should be moved to apptstat
$apt_stats_next = array(
	'-' => '@x~%*+',
	'*' => '@x~%',
	'+' => '@x~%',
	'@' => '!#<>',
	'~' => '!#<>',
	'<' => '>',
	'x' => '$',
	'?' => '$',
	'!' => '$',
	'#' => '$',
	'>' => '$',
	'%' => '$',
	'$' => '',
);

// List of rooms - currently relying on description to specify facility
$trk_rooms = array();
$li_rooms_dropdown = '';
$lres = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = ? AND activity=1
						ORDER BY seq", array('patient_flow_board_rooms'));
while ( $lrow = sqlFetchArray ( $lres ) ) {
	$title = trim($lrow['title']);
	$trk_rooms[$lrow['option_id']] = xl($title);
	$li_rooms_dropdown .= sprintf('<li data-selected="%s"><a href="#">%s</a></li>',
			htmlspecialchars($lrow['option_id']), $trk_rooms[$lrow['option_id']]);
}

// Specify data to be tagged for each row
$appt_tr_data = array('timer-in', 'timer-out', 'timer-hglt', 'timer-cur', 'edit-status', 'edit-room');

// Map data display
$trkr_cols = array(
		'PID'  => array('show' => $GLOBALS['ptkr_show_pid'], 'class'=>'xable'),
		'Patient' => array('class' => 'link-pt'),
		'Visit Type' => array('class' => 'chk_single'),
		'Facility' => array ('class' => 'chk_single', single_icon => 'hospital-o'),
		'Provider' => array('class' => 'chk_single', single_icon => 'user-md'),
		'Reason'  => array('show' => $GLOBALS['ptkr_visit_reason'], 'class'=>'xable'),
		'Encounter'  => array('show' => $GLOBALS['ptkr_show_encounter'], 'class'=>'xable'),
		'Appt' => array(),
		'Arrived' => array('class' => 'timer-in timer-hglt', 'data' => array('hglt-min' => 5, 'hglt-max' => 15)),
		'Status' => array('class' => 'timer-cur timer-hglt edit-status chk_single', 'data' => array('hglt-min' => 5, 'hglt-max' => 15)),
		'Duration' => array('class' => 'timer-cur', 'data' => array('hglt-min' => 5, 'hglt-max' => 15)),
		'Room' => array('class' => 'timer-cur timer-hglt edit-room', 'data' => array('hglt-min' => 5, 'hglt-max' => 15)),
		'Checked Out' => array(),
		'Total Time' => array('class' => 'timer-in timer-out', 'data' => array('hglt-min' => 20, 'hglt-max' => 60)),
		'Updated By' => array(),
		'Random Drug Screen' => array('show' => ($GLOBALS['drug_screen']),),
		'Drug Screen Completed' => array('show' => ($GLOBALS['drug_screen']),),
);

// Check user filters, single value columns etc.
$chk_single = array();
$usr_filts = array();
$str_filts = "";
foreach ($trkr_cols as $col_hdr => $col_opts) {
	$filt = getUserSetting($uspfx.'filter-'.xlt($col_hdr));
	if (!is_null($filt) && (strlen($filt)>0)) {
		$usr_filts[$col_hdr] = htmlspecialchars_decode($filt);
		$str_filts .= xlt($col_hdr).' - '.$filt."\n";
	}
	
	if (isset($col_opts['class'])) {
		if (strpos($col_opts['class'], 'chk_single') !== FALSE) {
			$chk_single[$col_hdr] = array();
		}
	}
}

foreach ( $appointments as $ix => $appt ) {
	// Build values for each column - self documenting
	$tracker_id = $appt['id'];
	$newarrive = collect_checkin($tracker_id);
	$newend = collect_checkout($tracker_id);
	$skip_this = false;
	
	foreach ($trkr_cols as $col_hdr => $col_opts) {
		switch ($col_hdr) {
			case 'PID':
				$appt[$col_hdr] = (!empty($appt['pid'])) ? $appt['pid'] : $appt['pc_pid'];
				// skip when $appt_pid = 0, since this means it is not a patient specific appt slot
				$skip_this = $skip_this || ($appt['PID'] == 0);
				break;
			case 'Patient':
				$appt[$col_hdr] = sprintf("%s, %s %s",
				$appt['lname'], $appt['fname'], $appt['mname']);
				break;
			case 'Reason':
				$appt[$col_hdr] = $appt['pc_hometext'];
				break;
			case 'Encounter':
				$appt[$col_hdr] = ($appt['encounter'] != 0 ? $appt['encounter'] : '');
				break;
			case 'Room':
				$appt['edit-room'] = (empty($appt['room'])) ? $appt['pc_room'] : $appt['room'];
				$appt[$col_hdr] = getListItemTitle('patient_flow_board_rooms', $appt['edit-room']);
				// $appt['edit-room-limit'] = '*';  // No room restrictions
				break;
			case 'Appt':
				$appt[$col_hdr] = $appt['pc_startTime']; // ** Recurring appt? *** (!empty($appt['appttime'])) ? $appt['appttime'] : $appt['pc_startTime'];
				$appt[$col_hdr] = oeFormatTime($appt[$col_hdr]);
				break;
			case 'Arrived':
				if ($newarrive) {
					$appt[$col_hdr] = ($newarrive ? oeFormatTime($newarrive) : '');
					$appt['time-in'] = strtotime($newarrive);
				} else {
					$appt[$col_hdr] = "";
				}
				break;
			case 'Status':
				$appt['edit-status'] = (empty($appt['status']) ? $appt['pc_apptstatus'] : $appt['status']);
				$appt[$col_hdr] = $apt_stats[$appt['edit-status']];
				$appt['edit-status-limit'] = $apt_stats_next[$appt['edit-status']];
				break;
			case 'Duration':
				if (!$newarrive) break;
				
				$to_time = strtotime(date("Y-m-d H:i:s"));
				$yestime = '0';
				if (strtotime($newend) != '') {
					$from_time = strtotime($newarrive);
					$to_time = strtotime($newend);
					$yestime = '0';
				}
				else
				{
					$from_time = strtotime($appt['start_datetime']);
					$yestime = '1';
				}
				
				$timecheck = round(abs($to_time - $from_time) / 60,0);
				if (($yestime == '1') && ($timecheck >=1) && (strtotime($newarrive)!= '')) {
					$appt[$col_hdr] = text($timecheck . ' ' .($timecheck >=2 ? xl('minutes'): xl('minute')));
				} else {
					$appt[$col_hdr] = "";
				}
				if (isset($appt['start_datetime'])) {
					$appt['time-cur'] = strtotime($appt['start_datetime']);
				}
				break;
			case 'Visit Type':
				$appt[$col_hdr] = text(xl_appt_category($appt['pc_title']));
				break;
			case 'Provider':
				$appt[$col_hdr] = text(trim(sprintf("%s, %s %s", $appt['ulname'], $appt['ufname'], $appt['umname'])));
				break;
			case 'Total Time':
				if (strtotime($newend) != '') {
					$from_time = strtotime($newarrive);
					$to_time = strtotime($newend);
				}
				else
				{
					$from_time = strtotime($newarrive);
					$to_time = strtotime(date("Y-m-d H:i:s"));
				}
				$timecheck2 = round(abs($to_time - $from_time) / 60,0);
				if (strtotime($newarrive) != '' && ($timecheck2 >=1)) {
					$appt[$col_hdr] = text($timecheck2 . ' ' .($timecheck2 >=2 ? xl('minutes'): xl('minute')));
				} else {
					$appt[$col_hdr] = "";
				}
				break;
			case 'Checked Out':
				if ($newend) {
					$appt['time-out'] = strtotime($newend);
					$appt[$col_hdr] = oeFormatTime($newend);
				} else {
					$appt[$col_hdr] = "";
				}
				break;
			case 'Updated By':
				$appt[$col_hdr] = $appt['user'];
				break;
			case 'Random Drug Screen':
				$appt[$col_hdr] = "";
				break;
			case 'Drug Screen Completed':
				$appt[$col_hdr] = "";
				break;
			case 'Facility':
				$appt[$col_hdr] = $appt['name'];
		};
	}
	
	// Apply user filters
	foreach ($usr_filts as $col_hdr => $filt_value) {
		$skip_this = $skip_this || ($appt[$col_hdr] != $filt_value);
	}
	
	$appt['skip_this'] = $skip_this;
	$appointments[$ix] = $appt;
	if ($skip_this) {
		// unset $appointments[$ix];
		continue;
	}
	
	// Scan for chk_single column values
	foreach ($chk_single as $col_hdr => $col_value) {
		if (empty($col_value[$appt[$col_hdr]])) {
			$col_value[$appt[$col_hdr]] = 1;
		} else {
			$col_value[$appt[$col_hdr]]++;
		}
		$chk_single[$col_hdr] = $col_value;
	}
	
}

$filt_cols = array_keys($chk_single);
$disp_cols = array_keys($trkr_cols);
$filt_flds = '';
foreach ($filt_cols as $filt_col) {
	$ix = array_search($filt_col, $disp_cols);
	$filt_flds .= sprintf('<input id="filt-col-%s" name="filt-col[%s]" type="hidden" value="%s" data-col="%s" />',
			$ix, $ix, (empty($_POST[filt-col[$ix]]) ? '' : $_POST[filt-col[$ix]]), $filt_col);
}
// Build nav control block for chk_singles
$nav_chk_singles = '';
foreach ($chk_single as $col_hdr => $col_values_array) {
	$col_values = array_keys($col_values_array);
	if (count($col_values) == 1) {
		$nav_chk_singles .= sprintf('<li><a>%s:<strong>%s</strong></a></li>',
				xl($col_hdr), $col_values[0]);
		if ($col_hdr != 'Status') { $trkr_cols[$col_hdr]['show'] = false; }
	} else {
		$ix = array_search($col_hdr, $disp_cols);
		$nav_value_sel = '';
		foreach ($col_values_array as $col_value => $col_value_count) {
			$nav_value_sel .= sprintf('<li data-selected="%s"><a href="#">%s <strong>(%s)</strong></a></li>', $col_value, $col_value, $col_value_count);
		}
		$nav_chk_singles .= sprintf('
					<li class="dropdown" data-filt-col="%s">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
							aria-haspopup="true" aria-expanded="false">%s<span class="caret"></span></a>
						<ul class="dropdown-menu">%s</ul>
					</li>', $ix, $col_hdr, $nav_value_sel);
	}
}

// Complex Sort of appointments
$appointments = array_orderby($appointments, 'time-out', SORT_ASC, 'appttime', SORT_DESC, 'pc_startTime', SORT_ASC);

// Helper to display stacked icons
function show_stack ($hlp, $icon, $sym, $key_value) {
	return sprintf('<li><a href="#"">%s(%s) - %s</a></li>',
			$hlp, $sym, text($key_value));
}

// If the screen is set up for auto refresh, this will allow it to be closed by auto logoff
$self_restore = '';
if ($GLOBALS['pat_trkr_timer'] != '0') {
	$self_restore = "?skip_timeout_reset=1";
}
?>
<html>
<head>
<title><?php echo xlt("Flow Board") ?></title>
<?php Header::setupHeader(["bootstrap"]); ?>
<style>
.dropdown:hover .dropdown-menu {
    display: block;
    margin-top: -3px;
    margin-left: 24px;
}
body { 
    padding-top: 50px; 
}
.table-condensed {
  font-size: 100%;
}
</style>
</head>

<body class="body_top" >
<form name='pattrk' id='pattrk' method='post' action='<?php echo $_SERVER["PHP_SELF"].$self_restore; ?>' 
    onsubmit='return top.restoreSession()' enctype='multipart/form-data'>
<?php echo $filt_flds; // Save filters ?>
<nav class="navbar navbar-xs navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header navbar-xs">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand title" href="#" id="navbar-title" <?php echo $home_ref ?> title=<?php echo xlt("Flow Board") ?>>
      	<strong><?php echo count($appointments).' '.xlt("Appointments") ?></strong>
      </a>
    </div>
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse navbar-xs" id="navbar-collapse-div-template-id">
      <ul class="nav navbar-nav" id="navbar-nav-ul-left-template-id">
	  	<?php echo $nav_chk_singles?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a id='refreshme' style="font-size: 150%;"><i class="fa fa-refresh fa-fw"></i></a>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<table id='tbl_ptkr' class='table table-condensed table-hover table-striped'>
	<tr>
     <?php
     $trkr_col_ix = -1;
     foreach ($trkr_cols as $col_hdr => $col_opts) {
     	$trkr_col_ix++;
     	$col_show = (isset($col_opts['show']) ? $col_opts['show'] : true);
     	if (!$col_show) { continue; }

     	$col_cls = sprintf(' class="filt-col-%s%s"', $trkr_col_ix, (isset($col_opts['class']) ? (" ".$col_opts['class']) : ""));
     	$filt = getUserSetting($uspfx.'filter-'.xlt($col_hdr));
     	$fa = '';
     	if (!is_null($filt) && (strlen($filt)>0)) {
     		$usr_filts[$col_hdr] = $filt;
     		$fa = sprintf("<a class='no-filter' data-lbl='filter-%s'><i class='fa fa-filter'></i></a>", xlt($col_hdr));
     	}
     	$col_data = '';
     	if (isset($col_opts['data'])) {
     		foreach ($col_opts['data'] as $dkey => $dval) {
     			$col_data .= sprintf(' data-%s="%s"', $dkey, $dval);
     		}
     	}
     	printf ("<th%s%s>%s%s</th>", $col_cls, $col_data, $fa, xlt($col_hdr));
     }
     ?>
	</tr>

<?php
foreach ( $appointments as $appt ) {
	if ($appt['skip_this']) continue;
	$data_js = '';
	foreach ($appt_tr_data as $js_fld) {
		if (isset($js_fld) && ($appt[$js_fld]>'')) {
			$data_js .= sprintf(' data-%s="%s"', $js_fld, $appt[$js_fld]);
			if (isset($appt[$js_fld.'-limit']) && $appt[$js_fld.'-limit']>'') {
				$data_js .= sprintf(' data-%s="%s"', $js_fld.'-limit', $appt[$js_fld.'-limit']);
			}
		}
	}
	// Print all columns
	printf ('<tr data-tk-id="%s" data-tk-eid="%s" %s>', 
		$appt['id'], ((empty($appt['eid'])) ? $appt['pc_eid'] : $appt['eid']), $data_js);
	foreach ($trkr_cols as $col_hdr => $col_opts) {
		$col_show = (isset($col_opts['show']) ? $col_opts['show'] : true);
		if ($col_show) {
			printf ('<td>%s</td>', text($appt[$col_hdr]) );
		}
	}
	printf ("</tr>");
	
} //end for
?>

<?php
//saving the filter for auto refresh
if(isset($_POST['form_facility']) ){
    echo "<input type='hidden' name='form_facility' value='" . attr($_POST['form_facility']) . "'>";
}
if(isset($_POST['form_apptcat']) ){
    echo "<input type='hidden' name='form_apptcat' value='" . attr($_POST['form_apptcat']) . "'>";
}
?>
</table>
</form>

<script type="text/javascript">
//Refresh self
function refreshme() {
  top.restoreSession();
  document.pattrk.submit();
}
// auto refresh screen pat_trkr_timer is the timer variable
function refreshbegin(first){
  <?php if ($GLOBALS['pat_trkr_timer'] != '0') { ?>
    var reftime="<?php echo attr($GLOBALS['pat_trkr_timer']); ?>";
    var parsetime=reftime.split(":");
    parsetime=(parsetime[0]*60)+(parsetime[1]*1)*1000;
    if (first != '1') {
      refreshme();
    }
    setTimeout("refreshbegin('0')",parsetime);
  <?php } else { ?>
    return;
 <?php } ?>
} 
$(document).ready(function() { 
	$('#settings').css("display","none");
	timedActions();
	var intervalID = setInterval(timedActions, (60*1000));
});	

// toggle of the check box status for drug screen completed and ajax call to update the database
$(".drug_screen_completed").change(function() {
    top.restoreSession();
    testcomplete_toggle=(this.checked);
    $.post( "../../library/ajax/drug_screen_completed.php", {
      trackerid: this.id,
      testcomplete: testcomplete_toggle
    });
  });

$('#refreshme').click(function () {
  refreshme();
});

<?php // *** TBD - Filters - map $trkr_cols index to table hdr, hide rows with matching content and store id for $_POST *** ?>
$("#tbl_ptkr tr th").each( function() {
    var th_col = $("#tbl_ptkr tr th").index($(this))+1;
    var th = $(this);
    $("#tbl_ptkr tr td:nth-child("+th_col+")").each( function(ix) {
        add_class(th, $(this), 'filterable', true);
        add_class(th, $(this), 'edit-status', true);
        add_class(th, $(this), 'edit-room', true);

        var tr = $(this).parent();
        add_class(th, $(this), 'timer-in', (tr.data('time-in') !== undefined));
        add_class(th, $(this), 'timer-cur', (tr.data('time-cur') !== undefined));
        add_class(th, $(this), 'timer-hglt', (tr.data('time-in') !== undefined));
    });
    $(this).removeClass('timer-in');
    $(this).removeClass('timer-cur');
    $(this).removeClass('timer-hglt');
});
function add_class(src, tgt, cls, chk) {
    if (src.hasClass(cls) && chk) {
        tgt.addClass(cls);
    }
}
$(".no-filter").click( function() {
    var lbl = $(this).data("lbl");
    setFilter (lbl, '');
});
// Delegated dropdown selections
$('body').on("click", ".dropdown .dropdown-menu li", function() {
	var dd = $(this).closest(".dropdown");
	var tr = $(dd).closest('tr');
// 	if ($(tr).attr('class').toString().indexOf('edit-') != -1) {
// 		alert ('Editing');
// 	} else {
// 		alert ('Filtering');
// 	}
	if ($(dd).hasClass("edit-status")) {
		updateTrkr({
			'id':{'get':$(tr).data('tk-id')},
			'eid':{'get':$(tr).data('tk-eid')},
			'status':{'get':$(tr).data('edit-status'), 'set':$(this).data('selected')},
			'room':{'get':$(tr).data('edit-room'), 'set':$(tr).data('edit-room')},
		});
	} else if ($(dd).hasClass("edit-room")) {
		updateTrkr({
			'id':{'get':$(tr).data('tk-id')},
			'eid':{'get':$(tr).data('tk-eid')},
			'status':{'get':$(tr).data('edit-status'), 'set':$(tr).data('edit-status')},
			'room':{'get':$(tr).data('edit-room'), 'set':$(this).data('selected')},
		});
	} else {
		alert('TBD : Set filter to '+$(this).data('selected'));
	}
});
$("td.edit-status").hover(
    function() {
      var col = $(this).closest("tr").children().index($(this))+1;
      var cur=$.trim($(this).html());
      $(this).addClass("dropdown");
      $(this).html($("div.edit-status-all label.dropdown").html());
      $(this).find("a.edit-status-text").html(cur);
      var valid_next = $(this).closest("tr").data("edit-status-limit");
      $(this).find("ul.edit-status-menu li").each(function() {
    	  if (valid_next.indexOf($(this).data('selected')) != -1) {
        	  $(this).css("display", "");
          }
      });
   },
   function() {
	  $(this).removeClass("dropdown");
	  $(this).html($(this).find("a.edit-status-text").html());
   }
);
<?php // Allow room selections only when next status is '>' - Checked Out ?>
$("tr[data-edit-status='<'] td.edit-room").hover(
    function() {
      var col = $(this).closest("tr").children().index($(this))+1;
      var cur=$.trim($(this).html());
      $(this).addClass("dropdown");
      $(this).html($("div.edit-rooms-all label.dropdown").html());
      $(this).find("a.edit-room-text").html(cur);
   },
   function() {
	  $(this).removeClass("dropdown");
	  $(this).html($(this).find("a.edit-room-text").html());
   }
);
<?php // Expected updt - field(current value[, updated value]) 
      // Specify only current value to help locate correct record and prevent blind updates ?>
function updateTrkr(updt) {
	$.post("../../library/ajax/pt_trkr_updates.php",
		{ update_spec : JSON.stringify(updt) })
		.done(function(result) {
			// Information block
			var res = JSON.parse(result);
			if (res.status != 'success') {
				var res_display = res.status+'\n';
				for(var det in res.details) {
					res_display += res.details[det] + '\n';
				}
				alert( res_display );
			}
		})
		.fail(function(err) {
			alert( err['status'] + " : " + err['statusText']);
		})
		.always(function() {
			refreshme();
		});
}
function timedHighlight(curr, hglt_min, hglt_max) {
    // alert (curr + '-'+ hglt_min + '-' + hglt_max);
    if (curr > hglt_max) return "#ff0000";
    if (curr < hglt_min) return false;
    var r = parseInt(255*((hglt_max - curr)/(hglt_max - hglt_min))).toString(16);
    return ("#ff"+r+"00");
}
function getSelData(sel, dtxt) {
    if (sel.data(dtxt) === undefined) {
        return 0;
    } else {
        return sel.data(dtxt);
    }
}
function timedActions() {
    $(".timer-cur, .timer-in").each( function(e) {
        var tr = $(this).parent();
        if ($(this).hasClass('timer-in')) {
            var tm_from = getSelData(tr, 'time-in');
        } else {
            var tm_from = getSelData(tr, 'time-cur');
        }
        var tm_chkout = getSelData(tr, 'time-out');
        if (tm_chkout != 0) return;
        var msecs = Date.now() - tm_from*1000;
        var mm = parseInt((msecs/(1000*60))%60)
        , HH = parseInt((msecs/(1000*60*60))%24);
        HH = (HH < 10) ? "0" + HH : HH;
        mm = (mm < 10) ? "0" + mm : mm;
            
        var col = $(this).parent().children().index($(this))+1;
        var th = $("#tbl_ptkr tr th:nth-child("+col+")");
        var bkgrnd = timedHighlight(parseInt(msecs/(60*1000)), getSelData(th, "hglt-min"), getSelData(th, "hglt-max"));
        if (bkgrnd) {        
            $(this).css('background-color', bkgrnd);
        }
        if (!$(this).hasClass('timer-hglt')) {
            $(this).html(HH+':'+mm);
        }
    });
}
</script>
<?php // Status update options ?>
<div class='edit-status-all' style="display: none;">
	<label class="dropdown">
	  <a class="dropdown-toggle edit-status-text" type="button" data-toggle="dropdown">
	    <span class="caret"></span>
	  </a>
	  <ul class="dropdown-menu edit-status-menu" role="menu">
	    <?php echo $li_stats_dropdown ?>
	  </ul>
	</label>
</div>
<?php // Rooms list ?>
<div class='edit-rooms-all' style="display: none;">
	<label class="dropdown">
	  <a class="dropdown-toggle edit-room-text" type="button" data-toggle="dropdown">
	    <span class="caret"></span>
	  </a>
	  <ul class="dropdown-menu edit-rooms-menu" role="menu">
	    <?php echo $li_rooms_dropdown ?>
	  </ul>
	</label>
</div>
</body>
</html>
