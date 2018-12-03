<?php
/**
 * View history of a patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("history.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.js.php");

use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;

?>
<html>
<head>
    <title><?php echo xlt("History"); ?></title>
    <?php Header::setupHeader('common'); ?>

<script type="text/javascript">
$(document).ready(function(){
    tabbify();
});
<?php require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal ?>
</script>

<style type="text/css">
<?php
// This is for layout font size override.
$grparr = array();
getLayoutProperties('HIS', $grparr, 'grp_size');
if (!empty($grparr['']['grp_size'])) {
    $FONTSIZE = $grparr['']['grp_size'];
?>
/* Override font sizes in the theme. */
#HIS .groupname {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#HIS .label {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#HIS .data {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#HIS .data td {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
<?php } ?>
</style>
<?php
//BEGIN - edit as needed - variables needed to construct the array $arrHeading - needed to output the Heading text with icons and Help modal code
$name = " - " . getPatientNameFirstLast($pid); //un-comment to include fname lname, use ONLY on relevant pages :))
$heading_title = xlt('History and Lifestyle') . $name; // Minimum needed is the heading text
//3 optional icons - for ease of use and troubleshooting first create the variables and then use them to populate the arrays:)
$arrExpandable = array();//2 elements - int|bool $current_state, int|bool $expandable . $current_state = collectAndOrganizeExpandSetting($arr_files_php).
                        //$arr_files_php is also an indexed array, current file name first, linked file names thereafter, all need _xpd suffix, names to be unique
$arrAction = array();//3 elements - string $action (conceal, reveal, search, reset, link and back), string $action_title - leave blank for actions
$show_help_icon = 1;
$help_file_name = 'history_dashboard_help.php';
$arrHelp = array($show_help_icon, $help_file_name );// 2 elements - int|bool $show_help_icon, string $help_file_name - file needs to exist in Documentation/help_files directory
//END - edit as needed
//DO NOT EDIT BELOW
$arrHeading = array($heading_title, $arrExpandable, $arrAction, $arrHelp); // minimum $heading_title, others can be an empty arrays - displays only heading
$oemr_ui = new OemrUI($arrHeading);
$arr_display_heading = $oemr_ui->pageHeading(); // returns an indexed array containing heading string with selected icons and container string value
$heading = $arr_display_heading[0];
$container = $arr_display_heading[1];// if you want page to always open as full-width override the default returned value with $container = 'container-fluid'
echo "<script>\r\n";
require_once("$srcdir/js/oeUI/universalTooltip.js");
echo "\r\n</script>\r\n";
?>
</head>
<body class="body_top">

<div id="container_div" class="<?php echo $container;?>">
    <div class="row">
        <div class="col-sm-12">
            <?php
            if (acl_check('patients', 'med')) {
                $tmp = getPatientData($pid, "squad");
                if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
                    echo "<p>(" . xlt('History not authorized') . ")</p>\n";
                    echo "</body>\n</html>\n";
                    exit();
                }
            } else {
                echo "<p>(" . xlt('History not authorized') . ")</p>\n";
                echo "</body>\n</html>\n";
                exit();
            }

            $result = getHistoryData($pid);
            if (!is_array($result)) {
                newHistoryData($pid);
                $result = getHistoryData($pid);
            }
            ?>
        </div>
    </div>
    <?php
    if (acl_check('patients', 'med', '', array('write','addonly'))) {?>
        <div class="row">
            <div class="col-sm-12">
                <?php require_once("$include_root/patient_file/summary/dashboard_header.php");?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php
                $list_id = "history"; // to indicate nav item is active, count and give correct id
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="btn-group">
                    <a href="history_full.php" class="btn btn-default btn-edit" onclick="top.restoreSession()">
                        <?php echo xlt("Edit");?>
                    </a>
                </div>
            </div>
        </div>
    <?php
    } ?>
    <div class="row">
        <div class="col-sm-12" style="margin-top: 20px;">
            <!-- Demographics -->
            <div id="HIS">
                <ul class="tabNav">
                    <?php display_layout_tabs('HIS', $result, $result2); ?>
                </ul>
                <div class="tabContainer">
                    <?php display_layout_tabs_data('HIS', $result, $result2); ?>
                </div>
            </div>
        </div>
    </div>

</div><!--end of container div -->
<?php $oemr_ui->helpFileModal(); // help file name passed in $arrHeading [3][1] ?>
<script>
    var listId = '#' + <?php echo js_escape($list_id); ?>;
    $(document).ready(function(){
        $(listId).addClass("active");
    });
</script>
<script type="text/javascript">
    // Array of skip conditions for the checkSkipConditions() function.
    var skipArray = [
        <?php echo js_escape($condition_str); ?>
    ];
    checkSkipConditions();
</script>

</body>
</html>
