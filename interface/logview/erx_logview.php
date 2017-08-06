<?php
/**
 * interface/logview/erx_logview.php Display NewCrop errors.
 *
 * @package    OpenEMR
 * @subpackage NewCrop
 * @link       http://www.open-emr.org
 * @author     Eldho Chacko <eldho@zhservices.com>
 * @author     Vinish K <vinish@zhservices.com>
 * @author     Sam Likins <sam.likins@wsi-services.com>
 * @author     Brady Miller <brady.g.miller@gmail.com>
 * @copyright  Copyright (c) 2011 ZMG LLC <sam@zhservices.com>
 * @copyright  Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__.'/../globals.php');
require_once($srcdir.'/log.inc');

$error_log_path = $GLOBALS['OE_SITE_DIR'].'/documents/erx_error';

if (array_key_exists('filename', $_REQUEST)) {
    $filename = $_REQUEST['filename'];
} else {
    $filename = '';
}

if (array_key_exists('start_date', $_REQUEST)) {
    $start_date = $_REQUEST['start_date'];
} else {
    $start_date = '';
}

if ($filename) {
    $bat_content = '';

    preg_match('/erx_error-\d{4}-\d{1,2}-\d{1,2}\.log/', $filename, $matches);

    if ($matches) {
        if ($fd = fopen($error_log_path.'/'.$filename, 'r')) {
            $bat_content = fread($fd, filesize($error_log_path.'/'.$filename));
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename='.$filename);
        header('Content-Description: File Transfer');
        header('Content-Length: '.strlen($bat_content));

        echo $bat_content;

        die;
    }
}

?>
<html>
    <head>
        <?php html_header_show(); ?>
        <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
        <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

        <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-7-2/index.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

        <script language="JavaScript">
            $(document).ready(function(){
                $('.datepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = false; ?>
                    <?php $datetimepicker_formatInput = false; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });
        </script>

    </head>
    <body class="body_top">
        <form method="post">
        <font class="title"><?php echo xlt('eRx Logs'); ?></font><br><br>
        <table>
            <tr>
                <td>
                    <span class="text"><?php echo xlt('Date'); ?>: </span>
                </td>
                <td>
                    <input type="text" size="10" class='datepicker' name="start_date" id="start_date" value="<?php echo $start_date ? substr($start_date, 0, 10) : date('Y-m-d'); ?>" title="<?php echo xlt('yyyy-mm-dd Date of service'); ?>" />
                </td>
                <td>
                    <input type="submit" name="search_logs" value="<?php echo xlt('Search'); ?>">
                </td>
            </tr>
        </table>
        </form>
<?php

    $check_for_file = 0;
if (array_key_exists('search_logs', $_REQUEST)) {
    if ($handle = opendir($error_log_path)) {
        while (false !== ($file = readdir($handle))) {
            $file_as_in_folder = 'erx_error-'.$start_date.'.log';

            if ($file != '.' && $file != '..' && $file_as_in_folder == $file) {
                $check_for_file = 1;
                $fd = fopen($error_log_path.'/'.$file, 'r');
                $bat_content = fread($fd, filesize($error_log_path.'/'.$file));
?>
                <p><?php echo xlt('Download'); ?>: <a href="erx_logview.php?filename=<?php echo htmlspecialchars($file, ENT_QUOTES); ?>"><?php echo htmlspecialchars($file, ENT_NOQUOTES); ?></a></p>
                <textarea rows="35" cols="132"><?php echo htmlspecialchars($bat_content, ENT_QUOTES); ?></textarea>
<?php
            }
        }
    }

    if ($check_for_file == 0) {
        echo xlt('No log file exist for the selected date').': '.$start_date;
    }
}

?>
    </body>
</html>
