<?php
/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  ophthal <magauran@ophthal.org>
 * @author  sunsetsystems <sunsetsystems>
 * @author  JP-DEV\sjpad <sjpadgett@gmail.com>
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 ophthal <magauran@ophthal.org>
 * @copyright Copyright (c) 2007 sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2017 JP-DEV\sjpad <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2015 Rod Roark <rod@sunsetsystems.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
// login.php makes sure the session ID captured here is different for each
// new login.  We maintain it here because most browsers do not have separate
// cookie storage for different top-level windows.  This function should be
// called just prior to invoking any server script that requires correct
// session data.  onclick="top.restoreSession()" usually does the job.
//
var oemr_session_name = '<?php echo session_name(); ?>';
var oemr_session_id   = '<?php echo session_id(); ?>';
var oemr_dialog_close_msg = '<?php echo (function_exists('xla')) ? xla("OK to close this other popup window?") : "OK to close this other popup window?"; ?>';
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
   document.cookie = oemr_session_name + '=' + oemr_session_id + '; path=<?php echo($web_root ? $web_root : '/');?>';
  }
 }
<?php } ?>
 return true;
}

// Pages that have a Print button or link should call this to initialize it for logging.
// This is done at page load time in case we want to hide or disable the element.
// The second argument, if present, specifies a log message to be used instead of logging
// the entire document and will always prevent hiding of the button or link.
//
function printLogSetup(elem, logdata) {
 if (elem == null) return;
 var doc = elem.ownerDocument;
 var win = doc.defaultView || doc.parentWindow;
 if (typeof(logdata) == 'undefined') logdata = null;
<?php if ($GLOBALS['gbl_print_log_option'] == 1) { ?>
 if (logdata == null) {
  elem.style.display = 'none';
  return;
 }
<?php } ?>
 win.printlogdata = logdata;
 elem.onclick = function () {
  // This is a function definition and variables here will be evaluated when the function executes.
  top.printLogPrint(this);
 }
}

// Pages that would otherwise call window.print() at load time should call this instead
// to support print logging. In this case the passed argument is normally the window,
// and data to log, if specified, should be in the caller's window.printlogdata.
// If no log data is specified and the global option to hide the print feature is set,
// then no printing is done and the function returns false.
//
function printLogPrint(elem) {
 var win = elem;
 if (elem.ownerDocument) {
  var doc = elem.ownerDocument;
  win = doc.defaultView || doc.parentWindow;
 }
<?php if ($GLOBALS['gbl_print_log_option'] == 1) { ?>
 // Returning false means we didn't print.
 if (!win.printlogdata) return false;
<?php } ?>
 if (win.printlog_before_print) win.printlog_before_print();
 win.print();
<?php if (!empty($GLOBALS['gbl_print_log_option'])) { ?>
 comments = win.printlogdata || win.document.body.innerHTML;
 top.restoreSession();
 $.post("<?php echo $GLOBALS['webroot']; ?>/library/ajax/log_print_action_ajax.php",
  { comments: comments }
 );
<?php } ?>
 return true;
}
