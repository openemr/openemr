<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 ZMG LLC <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+
//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
include_once("../globals.php");
include_once("$srcdir/log.inc");
include_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");
$error_log_path=$GLOBALS['OE_SITE_DIR']."/documents/erx_error";
if($_REQUEST['filename'])
{
    $bat_content='';
    preg_match('/erx_error-\d{4}-\d{1,2}-\d{1,2}\.log/',$_REQUEST['filename'],$matches);
    if($matches){
    if ($fd = fopen ($error_log_path."/".$_REQUEST['filename'], "r")) {
        $bat_content = fread($fd, filesize($error_log_path."/".$_REQUEST['filename']));
    }
    $filename=$_REQUEST['filename'];
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Description: File Transfer");
    header("Content-Length: " . strlen($bat_content));
    echo $bat_content;
    die;
    }
}
$start_date=$_REQUEST['start_date'];
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href='<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css' type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
    <form method="post">
    <font class="title"><?php  echo htmlspecialchars(xl('eRx Logs'), ENT_QUOTES) ?></font><br><br>
    <table>
        <tr>
            <td>
                <span class="text"><?php  echo htmlspecialchars(xl('Date'), ENT_QUOTES) ?>: </span>
            </td>
            <td>
                <input type="text" size="10" name="start_date" id="start_date" value="<?php echo $start_date ? substr($start_date, 0, 10) : date('Y-m-d'); ?>" title="<?php echo htmlspecialchars(xl('yyyy-mm-dd Date of service'), ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" />
                <img src="../pic/show_calendar.gif" align="absbottom" width="24" height="22" id="img_begin_date" border="0" alt="[?]" style="cursor: pointer; cursor: hand" title="<?php echo htmlspecialchars(xl('Click here to choose a date'), ENT_QUOTES); ?>">&nbsp;
            </td>
            <td>
                <input type="submit" name="search_logs" value="<?php echo htmlspecialchars(xl('Search'), ENT_QUOTES);?>">
            </td>
        </tr>
    </table>
    </form>
    <?php
    $check_for_file=0;
    if($_REQUEST['search_logs'])
    {
        if ($handle = opendir($error_log_path)) {
            while (false !== ($file = readdir($handle))) {
                $file_as_in_folder="erx_error-$start_date.log";
                if($file!='.' && $file!='..' && $file_as_in_folder==$file){
                    $check_for_file=1;
                    $fd = fopen ($error_log_path."/".$file, "r");
                    $bat_content = fread($fd, filesize($error_log_path."/".$file));
                ?>
                    <a href="erx_logview.php?filename=<?php echo htmlspecialchars($file,ENT_QUOTES);?>"><?php echo htmlspecialchars($file,ENT_NOQUOTES)?></a><br>
                    <textarea rows="35" cols="132"><?php echo htmlspecialchars($bat_content, ENT_QUOTES);?></textarea>
                <?php
                }
            }
        }
        if($check_for_file==0)
            echo htmlspecialchars( xl("No log file exist for the selected date"),ENT_QUOTES);
    }    
    ?>
</body>
<script language="javascript">
Calendar.setup({inputField:"start_date", ifFormat:"%Y-%m-%d", button:"img_begin_date"});
</script>
</html>