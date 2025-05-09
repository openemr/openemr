<?php

 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$faxstats = array(
'B' => xl('Blocked'),
'D' => xl('Sent successfully'),
'F' => xl('Failed'),
'P' => xl('Pending'),
'R' => xl('Send in progress'),
'S' => xl('Sleeping'),
'T' => xl('Suspended'),
'W' => xl('Waiting')
);

$mlines = array();
$dlines = array();
$slines = array();

if ($GLOBALS['enable_hylafax']) {
// Get the recvq entries, parse and sort by filename.
    $statlines = array();
    exec("faxstat -r -l -h " . escapeshellarg($GLOBALS['hylafax_server']), $statlines);
    foreach ($statlines as $line) {
        // This gets pagecount, sender, time, filename.  We are expecting the
        // string to start with "-rw-rw-" so as to exclude faxes not yet fully
        // received, for which permissions are "-rw----".
        if (preg_match('/^-r\S\Sr\S\S\s+(\d+)\s+\S+\s+(.+)\s+(\S+)\s+(\S+)\s*$/', $line, $matches)) {
            $mlines[$matches[4]] = $matches;
        }
    }

    ksort($mlines);

    // Get the doneq entries, parse and sort by job ID
    /* for example:
    JID  Pri S  Owner Number       Pages Dials     TTS Status
    155  123 D nobody 6158898622    1:1   5:12
    153  124 D nobody 6158896439    1:1   4:12
    154  124 F nobody 6153551807    0:1   4:12         No carrier detected
    */
    $donelines = array();
    exec("faxstat -s -d -l -h " . escapeshellarg($GLOBALS['hylafax_server']), $donelines);
    foreach ($donelines as $line) {
            // This gets jobid, priority, statchar, owner, phone, pages, dials and tts/status.
        if (preg_match('/^(\d+)\s+(\d+)\s+(\S)\s+(\S+)\s+(\S+)\s+(\d+:\d+)\s+(\d+:\d+)(.*)$/', $line, $matches)) {
            $dlines[$matches[1]] = $matches;
        }
    }

    ksort($dlines);
}

$scandir = $GLOBALS['scanner_output_directory'];
if ($scandir && $GLOBALS['enable_scanner']) {
    // Get the directory entries, parse and sort by date and time.
    $dh = opendir($scandir);
    if (! $dh) {
        die("Cannot read " . text($scandir));
    }

    while (false !== ($sfname = readdir($dh))) {
        if (substr($sfname, 0, 1) == '.') {
            continue;
        }

        $tmp = stat("$scandir/$sfname");
        $tmp[0] = $sfname; // put filename in slot 0 which we don't otherwise need
        $slines[$tmp[9] . $tmp[1]] = $tmp; // key is file mod time and inode number
    }

    closedir($dh);
    ksort($slines);
}

?>
<html>

<head>

    <?php Header::setupHeader(['opener']);?>
    <title><?php echo xlt('Received Faxes'); ?></title>

<style>
    td {
        font-family: "Arial", "Helvetica", sans-serif;
        padding-left: 4px;
        padding-right: 4px;
    }
    tr.head {
        font-size: 0.8125rem;
        background-color: var(--light);
        font-weight: bold;
    }
    tr.detail {
        font-size: 0.8125rem;
    }
    td.tabhead {
        font-size: 0.9375rem;
        font-weight: bold;
        height: 1.6875rem;
        text-align: center;
    }
</style>

<script>

// Process click on a tab.
function tabclick(tabname) {
 var tabs = new Array('faxin', 'faxout', 'scanin');
 var visdisp = document.getElementById('bigtable').style.display;
 for (var i in tabs) {
  var thistd    = document.getElementById('td_tab_' + tabs[i]);
  var thistable = document.getElementById('table_' + tabs[i]);
  if (tabs[i] == tabname) {
   // thistd.style.borderBottom = '0px solid var(--black)';
   thistd.style.borderBottom = '2px solid transparent';
   thistd.style.color = 'var(--danger)';
   thistd.style.cursor = 'default';
   thistable.style.display = visdisp;
  } else {
   thistd.style.borderBottom = '2px solid var(--black)';
   thistd.style.color = 'var(--gray)';
   thistd.style.cursor = 'pointer';
   thistable.style.display = 'none';
  }
 }
}

// Callback from popups to refresh this display.
function refreshme() {
 location.reload();
}

// Process click on filename to view.
function dodclick(ffname) {
 cascwin('fax_view.php?file=' + encodeURIComponent(ffname) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 600, 475,
  "resizable=1,scrollbars=1");
 return false;
}

// Process click on Job ID to view.
function dojclick(jobid) {
 cascwin('fax_view.php?jid=' + encodeURIComponent(jobid) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 600, 475,
  "resizable=1,scrollbars=1");
 return false;
}

// Process scanned document filename to view.
function dosvclick(sfname) {
 cascwin('fax_view.php?scan=' + encodeURIComponent(sfname) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 600, 475,
  "resizable=1,scrollbars=1");
 return false;
}

// Process click to pop up the fax dispatch window.
function domclick(ffname) {
    dlgopen('fax_dispatch.php?file=' + encodeURIComponent(ffname) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 850, 550, '', 'Fax Dispatch');
}

// Process click to pop up the scanned document dispatch window.
function dosdclick(sfname) {
    dlgopen('fax_dispatch.php?scan=' + encodeURIComponent(sfname) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 850, 550, '', 'Scanned Dispatch');
}

</script>

</head>

<body class="body_top">
<table class='w-100 h-100' cellspacing='0' cellpadding='0' style='margin: 0; border: 2px solid var(--black);' id='bigtable'>
 <tr style='height: 20px;'>
  <td width='33%' id='td_tab_faxin'  class='tabhead'
    <?php if ($GLOBALS['enable_hylafax']) { ?>
   style='color: var(--danger); border-right: 2px solid var(--black); border-bottom: 2px solid transparent;'
    <?php } else { ?>
   style='color: var(--gray); border-right: 2px solid var(--black); border-bottom: 2px solid var(--black); cursor: pointer; display:none;'
    <?php } ?>
   onclick='tabclick("faxin")'><?php echo xlt('Faxes In'); ?></td>
  <td width='33%' id='td_tab_faxout' class='tabhead'
    <?php if ($GLOBALS['enable_hylafax']) { ?>
   style='color: var(--gray); border-right: 2px solid var(--black); border-bottom: 2px solid var(--black); cursor: pointer;'
    <?php } else { ?>
   style='color: var(--gray); border-right: 2px solid var(--black); border-bottom: 2px solid var(--black); cursor: pointer; display:none;'
    <?php } ?>
   onclick='tabclick("faxout")'><?php echo xlt('Faxes Out'); ?></td>
  <td width='34%' id='td_tab_scanin' class='tabhead'
    <?php if ($GLOBALS['enable_scanner']) { ?>
   style='color: var(--gray); border-bottom: 2px solid var(--black); cursor: pointer;'
    <?php } else { ?>
   style='color: var(--danger); border-bottom: 2px solid transparent; display:none;'
    <?php } ?>
   onclick='tabclick("scanin")'><?php echo xlt('Scanner In'); ?></td>
 </tr>
 <tr>
  <td colspan='3' style='padding: 5px;' valign='top'>

   <form method='post' action='faxq.php'>

   <table class='w-100' cellpadding='1' cellspacing='2' id='table_faxin'
    <?php if (!$GLOBALS['enable_hylafax']) {
        echo "style='display:none;'";
    } ?>>
    <tr class='head'>
     <td colspan='2' title='Click to view'><?php echo xlt('Document'); ?></td>
     <td><?php echo xlt('Received'); ?></td>
     <td><?php echo xlt('From'); ?></td>
     <td align='right'><?php echo xlt('Pages'); ?></td>
    </tr>
<?php

 $encount = 0;
foreach ($mlines as $matches) {
    ++$encount;
    $ffname = $matches[4];
    $ffbase = basename("/$ffname", '.tif');
    $bgcolor = (($encount & 1) ? "#ddddff" : "#ffdddd");
    echo "    <tr class='detail' bgcolor='" . attr($bgcolor) . "'>\n";
    echo "     <td onclick='dodclick(\"" . attr(addslashes($ffname)) . "\")'>";
    echo "<a href='fax_view.php?file=" . attr_url($ffname) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='return false'>" . text($ffbase) . "</a></td>\n";
    echo "     <td onclick='domclick(\"" . attr(addslashes($ffname)) . "\")'>";
    echo "<a href='fax_dispatch.php?file=" . attr_url($ffname) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='return false'>" . xlt('Dispatch') . "</a></td>\n";
    echo "     <td>" . text($matches[3]) . "</td>\n";
    echo "     <td>" . text($matches[2]) . "</td>\n";
    echo "     <td align='right'>" . text($matches[1]) . "</td>\n";
    echo "    </tr>\n";
}
?>
   </table>

   <table class='w-100' cellpadding='1' cellspacing='2' id='table_faxout'
    style='display:none;'>
    <tr class='head'>
     <td title='Click to view'><?php echo xlt('Job ID'); ?></td>
     <td><?php echo xlt('To{{Destination}}'); ?></td>
     <td><?php echo xlt('Pages'); ?></td>
     <td><?php echo xlt('Dials'); ?></td>
     <td><?php echo xlt('TTS'); ?></td>
     <td><?php echo xlt('Status'); ?></td>
    </tr>
<?php
 $encount = 0;
foreach ($dlines as $matches) {
    ++$encount;
    $jobid = $matches[1];
    $ffstatus = $faxstats[$matches[3]];
    $fftts = '';
    $ffstatend = trim($matches[8]);
    if (preg_match('/^(\d+:\d+)\s*(.*)$/', $ffstatend, $tmp)) {
        $fftts = $tmp[1];
        $ffstatend = $tmp[2];
    }

    if ($ffstatend) {
        $ffstatus .= ': ' . $ffstatend;
    }

    $bgcolor = (($encount & 1) ? "#ddddff" : "#ffdddd");
    echo "    <tr class='detail' bgcolor='" . attr($bgcolor) . "'>\n";
    echo "     <td onclick='dojclick(\"" . attr(addslashes($jobid)) . "\")'>" .
     "<a href='fax_view.php?jid=" . attr_url($jobid) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='return false'>" .
     "$jobid</a></td>\n";
    echo "     <td>" . text($matches[5]) . "</td>\n";
    echo "     <td>" . text($matches[6]) . "</td>\n";
    echo "     <td>" . text($matches[7]) . "</td>\n";
    echo "     <td>" . text($fftts)      . "</td>\n";
    echo "     <td>" . text($ffstatus)   . "</td>\n";
    echo "    </tr>\n";
}
?>
   </table>

   <table class='w-100' cellpadding='1' cellspacing='2' id='table_scanin'
    <?php if ($GLOBALS['enable_hylafax']) {
        echo "style='display:none;'";
    } ?>>
    <tr class='head'>
     <td colspan='2' title='Click to view'><?php echo xlt('Filename'); ?></td>
     <td><?php echo xlt('Scanned'); ?></td>
     <td align='right'><?php echo xlt('Length'); ?></td>
    </tr>
<?php
 $encount = 0;
foreach ($slines as $sline) {
    ++$encount;
    $bgcolor = (($encount & 1) ? "#ddddff" : "#ffdddd");
    $sfname = $sline[0]; // filename
    $sfdate = date('Y-m-d H:i', $sline[9]);
    echo "    <tr class='detail' bgcolor='" . attr($bgcolor) . "'>\n";
    echo "     <td onclick='dosvclick(\"" . attr(addslashes($sfname)) . "\")'>" .
     "<a href='fax_view.php?scan=" . attr_url($sfname) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='return false'>" .
     "$sfname</a></td>\n";
    echo "     <td onclick='dosdclick(\"" . attr(addslashes($sfname)) . "\")'>";
    echo "<a href='fax_dispatch.php?scan=" . attr_url($sfname) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='return false'>" . xlt('Dispatch') . "</a></td>\n";
    echo "     <td>" . text($sfdate) . "</td>\n";
    echo "     <td align='right'>" . text($sline[7]) . "</td>\n";
    echo "    </tr>\n";
}
?>
   </table>

   </form>

  </td>
 </tr>
</table>
</body>
</html>
