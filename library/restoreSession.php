<?php

/**
 * restoreSession.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    ophthal <magauran@ophthal.org>
 * @author    JP-DEV\sjpad <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2007-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 ophthal <magauran@ophthal.org>
 * @copyright Copyright (c) 2017 JP-DEV\sjpad <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;

$scparams = session_get_cookie_params();
?>
// login.php makes sure the session ID captured here is different for each
// new login.  We maintain it here because most browsers do not have separate
// cookie storage for different top-level windows.  This function should be
// called just prior to invoking any server script that requires correct
// session data.  onclick="top.restoreSession()" usually does the job.
//
var oemr_session_name = <?php echo json_encode(urlencode(session_name())); ?>;
var oemr_session_id   = <?php echo json_encode(urlencode(session_id())); ?>;
var oemr_dialog_close_msg = <?php echo (function_exists('xlj')) ? xlj("OK to close this other popup window?") : json_encode("OK to close this other popup window?"); ?>;

var oemr_scp_lifetime = <?php echo js_escape($scparams['lifetime']); ?>;
var oemr_scp_path = <?php echo js_escape($scparams['path']); ?>;
var oemr_scp_domain = <?php echo js_escape($scparams['domain']); ?>;
var oemr_scp_secure = <?php echo js_escape($scparams['secure']); ?>;
var oemr_scp_samesite = <?php echo empty($scparams['samesite']) ? '' : js_escape($scparams['samesite']); ?>;
var oemr_cookie = '';
var oemr_change_count = 0; // debugging

function restoreSession() {
    <?php if (!empty($GLOBALS['restore_sessions'])) { ?>
        var ca = document.cookie.split('; ');
        for (var i = 0; i < ca.length; ++i) {
            var c = ca[i].split('=');
            if (c[0] == oemr_session_name && c[1] != oemr_session_id) {
                <?php if ($GLOBALS['restore_sessions'] == 2) { ?>
                    alert('Changing session ID from\n"' + c[1] + '" to\n"' + oemr_session_id + '"');
                <?php } ?>
                // It's important that the cookie parameters duplicate what PHP assigned.
                oemr_cookie = oemr_session_name + '=' + oemr_session_id +
                    '; path=' + oemr_scp_path +
                    '; domain=' + oemr_scp_domain;
                if (oemr_scp_lifetime) {
                    var d = new Date();
                    d.setTime(d.getTime() + (oemr_scp_lifetime * 1000));
                    oemr_cookie += '; expires=' + d.toUTCString();
                }
                if (oemr_scp_samesite) {
                    oemr_cookie += '; SameSite=' + oemr_scp_samesite;
                }
                document.cookie = oemr_cookie;
                ++oemr_change_count; // debugging
            }
        }
    <?php } ?>
    return true;
}

// Debugging support. Call this from an onclick handler somewhere for some
// insight into the state of the PHP session cookie.
//
function restoreSessionInfo() {
    alert(
        'session_id = ' + oemr_session_id   + '\n' +
        'cookie = '     + document.cookie   + '\n' +
        'lifetime = '   + oemr_scp_lifetime + '\n' +
        'path = '       + oemr_scp_path     + '\n' +
        'domain = '     + oemr_scp_domain   + '\n' +
        'secure = '     + oemr_scp_secure   + '\n' +
        'samesite = '   + oemr_scp_samesite + '\n' +
        'count = '      + oemr_change_count
    );
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
  {
    comments: comments,
    csrf_token_form: <?php echo json_encode(CsrfUtils::collectCsrfToken()); ?>
  }
 );
<?php } ?>
 return true;
}
