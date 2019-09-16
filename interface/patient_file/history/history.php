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
$arrOeUiSettings = array(
    'heading_title' => xl('History and Lifestyle'),
    'include_patient_name' => true,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "history_dashboard_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>
<body class="body_top">

<div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
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
<?php $oemr_ui->oeBelowContainerDiv();?>
<script>
    var listId = '#' + <?php echo js_escape($list_id); ?>;
    $(document).ready(function(){
        $(listId).addClass("active");
    });
</script>
<script type="text/javascript">
    // Array of skip conditions for the checkSkipConditions() function.
    var skipArray = [<?php echo !empty($condition_str) ? js_escape($condition_str) : ''; ?>];
    checkSkipConditions();
</script>

</body>
</html>
