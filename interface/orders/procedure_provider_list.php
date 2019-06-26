<?php
/**
 * Maintenance for the list of procedure providers.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

$popup = empty($_GET['popup']) ? 0 : 1;

$form_name = trim($_POST['form_name']);

$query = "SELECT pp.* FROM procedure_providers AS pp";
$query .= " ORDER BY pp.name";
$res = sqlStatement($query);
?>
<html>

<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Procedure Providers'); ?></title>

<?php if ($popup) { ?>
<script type="text/javascript" src="../../library/topdialog.js"></script>
<?php } ?>

<script language="JavaScript">

<?php if ($popup) {
    require($GLOBALS['srcdir'] . "/restoreSession.php");
} ?>

// Callback from popups to refresh this display.
function refreshme() {
 // location.reload();
 document.forms[0].submit();
}

// Process click to pop up the add window.
function doedclick_add() {
 top.restoreSession();
 var addTitle = '<i class="fa fa-plus" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Add Mode"); ?>;
 dlgopen('procedure_provider_edit.php?ppid=0', '_blank', 800, 750, false, addTitle);
}

// Process click to pop up the edit window.
function doedclick_edit(ppid) {
 top.restoreSession();
 var editTitle = '<i class="fa fa-pencil" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Edit Mode"); ?> + ' ';
 dlgopen('procedure_provider_edit.php?ppid=' + ppid, '_blank', 800, 750, false, editTitle);
}

</script>

</head>

<body class="body_top">
    <?php
    if ($GLOBALS['enable_help'] == 1) {
        $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 2) {
        $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 0) {
        $help_icon = '';
    }
    ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header clearfix">
                    <h2 id="header_title" class="clearfix"><span id='header_text'><?php echo xlt('Procedure Providers');?></span><?php echo $help_icon; ?></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
               <form method='post' action='procedure_provider_list.php'>
                    <div class="btn-group">
                        <button type="button" name="form_search" class="btn btn-default btn-refresh" onclick="refreshme()"><?php echo xlt('Refresh');?></button>
                        <button type="button" class="btn btn-default btn-add" onclick="doedclick_add()"><?php echo xlt('Add New');?></button>
                    </div>
                    <br>
                    <br>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th title='<?php echo xla('Click to view or edit'); ?>'><?php echo xlt('Name'); ?></th>
                                <th><?php echo xlt('NPI'); ?></th>
                                <th><?php echo xlt('Protocol'); ?></th>
                                <th class="text-center"><?php echo xlt('Edit'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = sqlFetchArray($res)) {
                                echo " <tr class='detail'>";
                                echo "  <td>" . text($row['name']) . "</td>\n";
                                echo "  <td>" . text($row['npi']) . "</td>\n";
                                echo "  <td>" . text($row['protocol']) . "</td>\n";
                                if (acl_check('admin', 'practice')) {
                                        $trTitle = xl('Edit') . ' ' . $row['name'];
                                        echo "  <td class=\"text-center\"><span style=\"color:#000000; cursor: pointer;\"  onclick='doedclick_edit(" . attr_js($row['ppid']) . ")' class=\"haskids fa fa-pencil\" title='" . attr($trTitle) . "'></span></td>\n";
                                } else {
                                        $trTitle = xl("Not Allowed to Edit") . ' ' . $row['name'];
                                        echo "  <td class=\"text-center\"><span style=\"color:#CACFD2;cursor: no-drop;\"  class=\"haskids fa fa-pencil\" title='" . attr($trTitle) . "'></span></td>\n";
                                }
                                echo " </tr>\n";
                            }
                            ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div><!-- End of container div -->
    <br>
    <?php
    //home of the help modal ;)
    //$GLOBALS['enable_help'] = 0; // Please comment out line if you want help modal to function on this page
    if ($GLOBALS['enable_help'] == 1) {
        echo "<script>var helpFile = 'procedure_provider_help.php'</script>";
        //help_modal.php lives in interface, set path accordingly
        require "../help_modal.php";
    }
    ?>
</body>
</html>
