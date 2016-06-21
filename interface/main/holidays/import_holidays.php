<?php
/**
 *
 * @package OpenEMR
 * @author  sharon cohen <sharonco@matrix.co.il>
 * @link    http://www.open-emr.org
 */

set_time_limit(0);

// Disable magic quotes and fake register globals.
$sanitize_all_escapes = true;
$fake_register_globals = false;
require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/acl.inc');
require_once("$srcdir/htmlspecialchars.inc.php");
require_once("$srcdir/sql.inc");
require_once("Holidays_Controller.php");


//require_once($GLOBALS['srcdir'] . '/htmlspecialchars.inc.php');
//require_once($GLOBALS['srcdir'] . '/formdata.inc.php');
//require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

if (!acl_check('admin', 'super')) die(xlt('Not authorized'));

//$form_replace = !empty($_POST['form_replace']);
//$code_type = empty($_POST['form_code_type']) ? '' : $_POST['form_code_type'];

// Handle uploads.
$holidays_controller = new Holidays_Controller();
$csv_file_data = $holidays_controller->get_file_csv_data();

if (!empty($_POST['bn_upload'])) {
    //Upload and save the csv
    $saved = $holidays_controller->upload_csv($_FILES);
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
    <title><?php echo xlt('Holidays management '); ?></title>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

</head>

<body class="body_top">
<?php
if ($saved){
    echo "<p style='color:green'>" .
        xlt('Successfully Completed ');
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
                        <a href='<?php echo $holidays_controller->get_target_file();?>'><?php echo $csv_file_data['date'];?></a>
                    <?php }else{
                        echo htmlspecialchars(xl('File not found'));
                    }?>
                </td>
            </tr>

        <tr bgcolor='#dddddd'>
                <td align='center' class='detail' colspan='2'>
                    <input type='submit' name='bn_upload' value='<?php echo xlt('Upload / Save ') ?>' />
                </td>
            </tr>
        </table>
        </p>
</form>
        <table border='1'>

        <tr >
            <td >
                <form method='post' action='import_holidays.php' onsubmit='return top.restoreSession()'>
                    <input type='submit' name='import_holidays' value='<?php echo xlt('Import holiday events ') ?>'></br>

                </form>
            </td>

            <td>
                    <?php echo xlt('CSV to calendar_external table'); ?></br>
                    If  the csv has been uploaded please click on "parse and insert" button</br>
                    **NOTE: clicking on the button will remove all the existing rows in the calendar external table
                </td>
        </tr>
            <tr >
                <td >
                    <form method='post' action='import_holidays.php' onsubmit='return top.restoreSession()'>
                        <input type='submit' name='sync' value='<?php echo xlt('Syncronize ') ?>' /></br>
                    </form>
                </td>
                <td >
                    <?php echo xlt('calendar_External to events'); ?></br>
                    If you have already filled the calendar external table please click on create events to have the holidays n the calendar view </br>
                    **NOTE: clicking on the button will remove all the existing rows in the events table related to holidays

                </td>
            </tr>
        </table>
</body>
</html>
