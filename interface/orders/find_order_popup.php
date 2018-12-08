<?php
/**
 * Script to pick a procedure order type from the compendium.
 *
 * Copyright (C) 2013 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 */


require_once("../globals.php");

use OpenEMR\Core\Header;

$order = 0 + $_GET['order'];
$labid = 0 + $_GET['labid'];

//////////////////////////////////////////////////////////////////////
// The form was submitted with the selected code type.
if (isset($_GET['typeid'])) {
    $grporders = array();
    $typeid = $_GET['typeid'] + 0;
    $name = '';
    if ($typeid) {
        $ptrow = sqlQuery("SELECT * FROM procedure_type WHERE procedure_type_id = ?", array($typeid));
        $name = addslashes($ptrow['name']);
        $codes = addslashes($ptrow['related_code']);
        if ($ptrow['procedure_type'] == 'fgp') {
            $res = sqlStatement("SELECT * FROM procedure_type WHERE parent = ? && procedure_type = 'for' ORDER BY seq, name, procedure_type_id", array($typeid));
            while ($row = sqlFetchArray($res)) {
                $grporders[] = $row;
            }
        }
    }
    ?>
    <script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
    <script language="JavaScript">
        if (opener.closed) {
            alert('<?php xl('The destination form was closed; I cannot act on your selection.', 'e'); ?>');
        }
        else {
            <?php
            if (isset($_GET['addfav'])) {
                $order = json_encode($ptrow);
                echo "opener.set_new_fav($order);\nwindow.close();";
            }
            $i = 0;
            $t = 0;
            do {
                if (!isset($grporders[$i]['procedure_type_id'])) {
                    echo "opener.set_proc_type($typeid, '$name', '$codes');\n";
                } else {
                    $t = count($grporders) - $i;
                    $typeid = $grporders[$i]['procedure_type_id'] + 0;
                    $name = addslashes($grporders[$i]['name']);
                    $codes = addslashes($grporders[$i]['related_code']);
                    echo "opener.set_proc_type($typeid, '$name', '$codes', $t);\n";
                }
                // This is to generate the "Questions at Order Entry" for the Procedure Order form.
                // GET parms needed for this are: formid, formseq.
                if (isset($_GET['formid'])) {
                    if ($typeid) {
                        require_once("qoe.inc.php");
                        $qoe_init_javascript = '';
                        echo ' opener.set_proc_html("';
                        echo generate_qoe_html($typeid, intval($_GET['formid']), 0, intval($_GET['formseq']));
                        echo '", "' . $qoe_init_javascript . '");' . "\n";
                    } else {
                        echo ' opener.set_proc_html("", "");' . "\n";
                    }
                }
                $i++;
            } while ($grporders[$i]['procedure_type_id']);
            ?>
        }
        window.close();
    </script>
    <?php
    exit();
}

// End Submission.
//////////////////////////////////////////////////////////////////////

?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['opener']); ?>
    <title><?php echo xlt('Procedure Picker'); ?></title>

    <script language="JavaScript">
        // Reload the script with the select procedure type ID.
        function selcode(typeid) {
            location.href = 'find_order_popup.php<?php echo "?order=$order&labid=$labid";
            if (isset($_GET['addfav'])) {
                echo '&addfav=' . $_GET['addfav'];
            }
            if (isset($_GET['formid'])) {
                echo '&formid=' . $_GET['formid'];
            }
            if (isset($_GET['formseq'])) {
                echo '&formseq=' . $_GET['formseq'];
            }
                ?>&typeid=' + typeid;
            return false;
        }
    </script>
</head>
<body>
<div class="container">
    <form class="form-inline" method='post' name='theform' action='find_order_popup.php<?php echo "?order=$order&labid=$labid";
    if (isset($_GET['formid'])) {
        echo '&formid=' . $_GET['formid'];
    }

    if (isset($_GET['formseq'])) {
        echo '&formseq=' . $_GET['formseq'];
    }
    if (isset($_GET['addfav'])) {
        echo '&addfav=' . $_GET['addfav'];
    }
    ?>'>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <div class="input-group">
                    <input type="hidden" name='isfav' value='<?php echo attr($_REQUEST['ordLookup']); ?>'>
                    <input class="form-control" id='search_term' name='search_term' value='<?php echo attr($_REQUEST['search_term']); ?>'
                        title='<?php echo xla('Any part of the desired code or its description'); ?>' placeholder="<?php echo xla('Search for') ?>&hellip;"/>
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default btn-search" name='bn_search' value="true"><?php echo xla('Search'); ?></button>
                        <?php if (!isset($_REQUEST['addfav'])) { ?>
                            <button type="submit" class="btn btn-default btn-search" name='bn_grpsearch' value="true"><?php echo xla('Favorites'); ?></button>
                        <?php } ?>
                        <button type="button" class="btn btn-default btn-delete" onclick="selcode(0)"><?php echo xla('Erase'); ?></button>
                    </span>
                </div>
            </div>
        </div>
        <?php if ($_REQUEST['bn_search'] || $_REQUEST['bn_grpsearch']) { ?>
            <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                    <th><?php echo xlt('Code'); ?></th>
                    <th><?php echo xlt('Description'); ?></th>
                    </thead>
                    <?php
                    $ord = isset($_REQUEST['bn_search']) ? 'ord' : 'fgp';
                    $search_term = '%' . $_REQUEST['search_term'] . '%';
                    $query = "SELECT procedure_type_id, procedure_code, name " .
                        "FROM procedure_type WHERE " .
                        "lab_id = ? AND " .
                        "procedure_type LIKE ? AND " .
                        "activity = 1 AND " .
                        "(procedure_code LIKE ? OR name LIKE ?) " .
                        "ORDER BY seq, procedure_code";
                    $res = sqlStatement($query, array($labid, $ord, $search_term, $search_term));

                    while ($row = sqlFetchArray($res)) {
                        $itertypeid = $row['procedure_type_id'];
                        $itercode = $row['procedure_code'];
                        $itertext = trim($row['name']);
                        $anchor = "<a href='' onclick='return selcode(" .
                            "\"" . $itertypeid . "\")'>";
                        echo " <tr>";
                        echo "  <td>$anchor" . text($itercode) . "</a></td>\n";
                        echo "  <td>$anchor" . text($itertext) . "</a></td>\n";
                        echo " </tr>";
                    } ?>
                </table>
            </div>
        <?php } ?>

    </form>
</div>
</body>
</html>
