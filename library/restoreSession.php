// login.php makes sure the session ID captured here is different for each
// new login.  We maintain it here because most browsers do not have separate
// cookie storage for different top-level windows.  This function should be
// called just prior to invoking any server script that requires correct
// session data.  onclick="top.restoreSession()" usually does the job.
//
var oemr_session_name = '<?php echo session_name(); ?>';
var oemr_session_id   = '<?php echo session_id(); ?>';
var oemr_dialog_close_msg = '<?php echo xl('OK to close this other popup window?'); ?>';
//
function restoreSession() {
<?php if (!empty($GLOBALS['restore_sessions'])) { ?>
 var ca = document.cookie.split('; ');
 for (var i = 0; i < ca.length; ++i) {
  var c = ca[i].split('=');
  if (c[0] == oemr_session_name && c[1] != oemr_session_id) {
<?php if ($GLOBALS['restore_sessions'] == 2) { ?>
   alert('Changing session ID from\n"' + c[1] + '" to\n"' + oemr_session_id + '"');
<?php } ?>
   document.cookie = oemr_session_name + '=' + oemr_session_id + '; path=/';
  }
 }
<?php } ?>
 return true;
}
