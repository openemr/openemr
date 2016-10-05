<?php
/**
 * interface/main/holidays/import_holidays.php holidays/clinic handle import/download holidays files
 *
 * Copyright (C) 2016 Sharon Cohen <sharonco@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  sharonco <sharonco@matrix.co.il>
 * @link    http://www.open-emr.org
 */

set_time_limit(0);

// Disable magic quotes and fake register globals.
$sanitize_all_escapes = true;
$fake_register_globals = false;
require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/acl.inc');
require_once("Holidays_Controller.php");

if (!acl_check('admin', 'super')) die(xlt('Not authorized'));

$holidays_controller = new Holidays_Controller();
$csv_file_data = $holidays_controller->get_file_csv_data();

//this part download the CSV file after the click on the href link
if($_GET['download_file']==1) {
    $target_file=$holidays_controller->get_target_file();
    if ( ! file_exists($target_file))
    {
        echo xlt('file missing');
    }
    else
    {
        header('HTTP/1.1 200 OK');
        header('Cache-Control: no-cache, must-revalidate');
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=holiday.csv");
        readfile($target_file);
        exit;
    }
    die();
}
// end download section

// Handle uploads.

if (!empty($_POST['bn_upload'])) {
    //Upload and save the csv
    $saved = $holidays_controller->upload_csv($_FILES);
    if($saved) {
        $csv_file_data = $holidays_controller->get_file_csv_data();
    }

}
if (!empty($_POST['import_holidays'])) {
    //Import from the csv file to the calendar external table
    $saved = $holidays_controller->import_holidays_from_csv();
}
if (!empty($_POST['sync'])) {
    //Upload and save the csv
    $saved = $holidays_controller->create_holiday_event();
}


?>

<html>
<head>
    <title><?php echo xlt('Holidays management'); ?></title>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

</head>

<body class="body_top">
<?php
if ($saved){
    echo "<p style='color:green'>" .
        xlt('Successfully Completed');
        "</p>\n";
}elseif(!empty($_POST['bn_upload'])             &&
        !empty($_POST['import_holidays'])       &&
        !empty($_POST['sync'])
        ){
    echo "<p style='color:red'>" .
        xlt('Operation Failed' );
    "</p>\n";
}
?>
<form method='post' action='import_holidays.php' enctype='multipart/form-data'
      onsubmit='return top.restoreSession()'>

        <p class='text'>
        <table border='1' cellpadding='4'>
            <tr bgcolor='#dddddd' class='dehead'>
                <td align='center' colspan='2'>
                    <?php echo xlt('CSV'); ?>
                </td>
            </tr>
            <tr>
                <td class='detail' nowrap>
                    <?php echo htmlspecialchars(xl('CSV File')); ?>
                    <input type="hidden" name="MAX_FILE_SIZE" value="350000000" />
                </td>
                <td class='detail' nowrap>
                    <input type="file" name="form_file" size="40" />
                </td>
            </tr>
            <tr>
                <td class='detail' nowrap>
                    <?php echo xlt('File on server (modification date)'); ?>
                </td>
                <td class='detail' nowrap>
                    <?php
                    if(!empty($csv_file_data)){?>

                        <?php $path=explode("/",$holidays_controller->get_target_file());?>
                        <?php $filename=$path[count($path)-1];?>
                        <?php unset($path[count($path)-1]);?>

                        <a href="#" onclick='window.open("import_holidays.php?download_file=1")'><?php echo text($csv_file_data['date']);?></a>
                    <?php }else{
                        echo htmlspecialchars(xl('File not found'));
                    }?>
                </td>
            </tr>

        <tr bgcolor='#dddddd'>
                <td align='center' class='detail' colspan='2'>
                    <input type='submit' name='bn_upload' value='<?php echo xla('Upload / Save') ?>' />
                </td>
            </tr>
        </table>
        </p>
</form>
        <table border='1'>

        <tr >
            <td >
                <form method='post' action='import_holidays.php' onsubmit='return top.restoreSession()'>
                    <input type='submit' name='import_holidays' value='<?php echo xla('Import holiday events') ?>'></br>

                </form>
            </td>

            <td>
                    <?php echo xlt('CSV to calendar_external table'); ?></br>
                <?php echo xlt('If the csv file has been uploaded, then click on the "Import holiday events" button. NOTE that clicking on the button will remove all the existing rows in the calendar_external table')?>
                </td>
        </tr>
            <tr >
                <td >
                    <form method='post' action='import_holidays.php' onsubmit='return top.restoreSession()'>
                        <input type='submit' name='sync' value='<?php echo xla('Synchronize') ?>' /></br>
                    </form>
                </td>
                <td >
                    <?php echo xlt('calendar_external to events'); ?></br>
                    <?php echo xlt('If you have already filled the calendar_external table, then click on "Synchronize" button to have the holidays in the calendar view. NOTE that clicking on the button will remove all the existing items in the calendar view related to holidays')?>
                </td>
            </tr>
        </table>
</body>
</html>




