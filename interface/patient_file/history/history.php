<?php
/**
 * View history of a patient.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("history.inc.php");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/options.js.php");

?>
<html>
<head>
    <title><?php echo xl("History"); ?></title>
    <?php Header::setupHeader('common'); ?>

<script type="text/javascript">
$(document).ready(function(){
    tabbify();
});
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

</head>
<body class="body_top">

<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <?php
            if (acl_check('patients', 'med')) {
                $tmp = getPatientData($pid, "squad");
                if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
                    echo "<p>(".htmlspecialchars(xl('History not authorized'), ENT_NOQUOTES).")</p>\n";
                    echo "</body>\n</html>\n";
                    exit();
                }
            } else {
                echo "<p>(".htmlspecialchars(xl('History not authorized'), ENT_NOQUOTES).")</p>\n";
                echo "</body>\n</html>\n";
                exit();
            }

            $result = getHistoryData($pid);
            if (!is_array($result)) {
                newHistoryData($pid);
                $result = getHistoryData($pid);
            }
            ?>

            <?php if (acl_check('patients', 'med', '', array('write','addonly'))) { ?>
                <div class="page-header">
                    <h2><?php echo htmlspecialchars(getPatientName($pid), ENT_NOQUOTES);?> <small><?php echo xl("History & Lifestyle");?></small></h2>
                </div>
                <div>
                    <div class="btn-group">
                        <a href="../summary/demographics.php" class="btn btn-default btn-back" onclick="top.restoreSession()">
                            <?php echo htmlspecialchars(xl('Back To Patient'), ENT_NOQUOTES);?>
                        </a>
                        <a href="history_full.php" class="btn btn-default btn-edit" onclick="top.restoreSession()">
                            <?php echo htmlspecialchars(xl("Edit"), ENT_NOQUOTES);?>
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="col-xs-12" style="margin-top: 20px;">
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
</div>


<script type="text/javascript">
    // Array of skip conditions for the checkSkipConditions() function.
    var skipArray = [
        <?php echo $condition_str; ?>
    ];
    checkSkipConditions();
</script>

</body>
</html>
