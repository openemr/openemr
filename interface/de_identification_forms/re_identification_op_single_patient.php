<?php
/********************************************************************************\
 * Copyright (C) ViCarePlus, Visolve (vicareplus_engg@visolve.com)              *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 \********************************************************************************/
require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

if (!acl_check('admin', 'super')) {
    die(xlt('Not authorized'));
}

if (!verifyCsrfToken($_POST["csrf_token_form"])) {
    csrfNotVerified();
}

$query = "SELECT status FROM re_identification_status";
$res = sqlStatement($query);
if ($row = sqlFetchArray($res)) {
    $status = $row['status'];
    /* $Status:
	*  0 - There is no Re Identification in progress. (start new Re Identification process)
	*  1 - A Re Identification process is currently in progress.
	*  2 - The Re Identification process completed and xls file is ready to download
	*/
}

if ($status == 0) {
 //0 - There is no Re Identification in progress. (start new Re Identification process)
?>
<html>
<head>
<title><?php echo xlt('Re Identification'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>

<style type="text/css">
.style1 {
    text-align: center;
}
</style>
</head>
<body class="body_top">
<strong><?php echo xlt('Re Identification');  ?></strong>
<div id="overDiv"
    style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<form enctype="Re_identification_output" method="POST"><?php
if ($_POST["re_id_code"]) {
    $reIdCode = isset($_POST['re_id_code']) ? trim($_POST['re_id_code']) : '';
}

//to store input for re-idenitification
$query = "DROP TABLE IF EXISTS temp_re_identification_code_table";
$res = sqlStatement($query);

$query = "create table temp_re_identification_code_table (re_identification_code varchar(50))";
$res = sqlStatement($query);

$query = "insert into temp_re_identification_code_table values (?)";
$res = sqlStatement($query, array($reIdCode));

$query = "update re_identification_status set status = 1;";
$res = sqlStatement($query);

//call procedure - execute in background
$sh_cmd='./re_identification_procedure.sh ' . escapeshellarg($sqlconf["host"]) . ' ' . escapeshellarg($sqlconf["login"]) . ' ' . escapeshellarg($sqlconf["pass"]) . ' ' . escapeshellarg($sqlconf["dbase"]) . ' &';
system($sh_cmd);

?>
    <table>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <table class="de_identification_status_message" align="center">
    <tr valign="top">

        <td>&nbsp;</td>
        <td rowspan="3"><br>
        <?php echo xlt('Re Identification Process is ongoing');
        echo "</br></br>";
        echo xlt('Please visit Re Identification screen after some time');
        echo "</br>";   ?> </br>
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <table align="center">
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
        <?php
} else if ($status == 2) {
 //2 - The Re Identification process completed and xls file is ready to download
    $query = "update re_identification_status set status = 0";
    $res = sqlStatement($query);
    $query = "SELECT count(*) as count FROM re_identified_data";
    $res = sqlStatement($query);

    if ($row = sqlFetchArray($res)) {
        $no_of_items = $row['count'];
    }

    if ($no_of_items <= 1) {
        ?>
    <table>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <table class="de_identification_status_message" align="center">
    <tr valign="top">
        <td>&nbsp;</td>
        <td rowspan="3"><br>
        <?php echo xlt('No match Patient record found for the given Re Idenitification code');
        echo "</br></br>";
        echo xlt('Please enter correct Re Identification code');
        echo "</br>";   ?> </br>
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <table align="center">
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
        <?php
    } else {
        //delete old re_identified_data.xls file
        $timestamp=0;
        $query = "select now() as timestamp";
        $res = sqlStatement($query);
        if ($row = sqlFetchArray($res)) {
            $timestamp = $row['timestamp'];
        }

        $timestamp = str_replace(" ", "_", $timestamp);
        $filename = $GLOBALS['temporary_files_dir']."/re_identified_data".$timestamp.".xls";
        $query = "select * from re_identified_data into outfile '" . add_escape_custom($filename) . "' ";
        $res = sqlStatement($query);
        ob_end_clean();
        //download Re Identification .xls file
        if (file_exists($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($filename));
            header('Content-Transfer-Encoding: none');
            header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
            header("Content-type: application/x-msexcel");                    // This should work for the rest
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            ob_clean();
            flush();
            readfile($filename);

            //xls file downloaded complete
        }
    }
}
?></form>
</body>
</html>

