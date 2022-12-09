
function FormPop(pid, id, enc, form)
{
	var warn_msg = '';
  var root  = '<?php echo $GLOBALS['rootdir']; ?>'
  if(arguments.length > 4) root = arguments[4];
	if(pid == '' || pid == 0) warn_msg = 'Patient ID is NOT set - ';
	if(enc == '' || enc == 0) warn_msg = 'Encounter is NOT set - ';
	if(form == '' || form == 0) warn_msg = 'Form Directory is NOT set - ';
	if(warn_msg != '') {
		alert(warn_msg + 'Not Able to Pop Open this Form');
		return false;
	}
	var mode = '&mode=update';
	var script = 'view.php';
	var form_id = '&id='+id;
	if(id == '' || id == 0) {
		script = 'new.php';
		mode = ''	
		form_id = '';
	}
	wmtOpen(root+'/forms/'+form+'/'+script+'?pid='+pid+'&enc='+enc+form_id+mode, '_blank', 'max', 'max');
}

function goParentPid(pid) {
	<?php if($GLOBALS['new_tabs_layout'] == 1) { ?>
  top.restoreSession();
  top.RTop.location = "<?php echo $GLOBALS['rootdir']; ?>/patient_file/summary/demographics.php?set_pid=" + pid;
	<?php } else { ?>

	if( (window.opener) && (window.opener.setPatient) ) {
		window.opener.loadFrame('RTop', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid);
	} else if( (parent.left_nav) && (parent.left_nav.loadFrame) ) {
		parent.left_nav.loadFrame('RTop', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid);
	} else {
		var newWin = window.open('<?php echo $GLOBALS['rootdir']; ?>/main/main_screen.php?patientID=' + pid);
	}
	<?php } ?>
}

function PopRTO(pid, id) {
  var root  = '<?php echo $GLOBALS['rootdir']; ?>';
  if(arguments.length > 4) root = arguments[4];
	wmtOpen(root+'/forms/rto/new.php?pop=yes&pid='+pid+'&id='+id, '_blank', 1200, 400);
}

function toencounter(pid, pubpid, pname, enc, datestr, dobstr) {
	restoreSession();
	// IN A POPUP SO WE LOAD THE OPENER WINDOW IF IT EXISTS - TYPICAL
	if ( (window.opener) && (window.opener.setEncounter) ) {
    window.opener.forceDual();
    window.opener.loadFrame('RBot', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid + '&set_enc=' + enc);
	// THIS HAS YET TO BE THOROUGHLY TESTED - WILL INSTALL TO POPUP
	} else if ( (window.opener) && (window.opener.top) && (window.opener.top.left_nav) && (window.opener.top.left_nav.setEncounter) ) {
		window.opener.top.left_nav.forceDual();
		window.opener.top.left_nav.loadFrame('RBot', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid + '&set_enc=' + enc);
	} else if ( (parent.left_nav) && (parent.left_nav.setEncounter) ) {
    parent.left_nav.forceDual();
    parent.left_nav.loadFrame('RBot', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid + '&set_enc=' + enc);
	// not in a frame and opener no longer exists, create a new window
	} else {
		var newwin = window.open('<?php echo $GLOBALS['rootdir']; ?>/main/main_screen.php?patientID=' + pid + '&encounterID=' + enc);
	}
}

function CheckAll(base) {
	var f = document.forms[0].elements;
	var tmp;
	var cnt = f.length;
	for(tmp=1; tmp<cnt; tmp++) {
		if(f[tmp].name.indexOf(base) == 0) {	
			if(f[tmp].disabled != true) {	
				f[tmp].checked = true;		
			}
		}
	}
}

function UncheckAll(base) {
	var f = document.forms[0].elements;
	var tmp;
	var cnt = f.length;
	for(tmp=1; tmp<=cnt; tmp++) {
		if(f[tmp].name.indexOf(base) == 0) {	
			if(f[tmp].disabled != true) {	
				f[tmp].checked = false;		
			}
		}
	}
}
