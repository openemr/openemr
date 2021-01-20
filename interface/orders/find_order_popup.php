<?php

/**
 * Script to pick a procedure order type from the compendium.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Core\Header;

$order = 0 + $_GET['order'];
$labid = 0 + ($_GET['labid'] ?? null);

//////////////////////////////////////////////////////////////////////
// The form was submitted with the selected code type.
if (isset($_GET['typeid'])) {
    $grporders = array();
    $typeid = $_GET['typeid'] + 0;
    $name = '';
    if ($typeid) {
        $ptrow = sqlQuery("SELECT * FROM procedure_type WHERE procedure_type_id = ?", array($typeid));
        $name = $ptrow['name'];
        $codes = $ptrow['related_code'];
        if ($ptrow['procedure_type'] == 'fgp') {
            $res = sqlStatement("SELECT * FROM procedure_type WHERE parent = ? && procedure_type = 'for' ORDER BY seq, name, procedure_type_id", array($typeid));
            while ($row = sqlFetchArray($res)) {
                $grporders[] = $row;
            }
        }
    }
    ?>
    <script src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
    <script>
        if (opener.closed) {
            alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
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
                    echo "opener.set_proc_type(" . js_escape($typeid) . ", " . js_escape($name) . ", " . js_escape($codes) . ");\n";
                } else {
                    $t = count($grporders) - $i;
                    $typeid = $grporders[$i]['procedure_type_id'] + 0;
                    $name = $grporders[$i]['name'];
                    $codes = $grporders[$i]['related_code'];
                    echo "opener.set_proc_type(" . js_escape($typeid) . ", " . js_escape($name) . ", " . js_escape($codes) . ", " . js_escape($t) . ");\n";
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

    <script>
        // Reload the script with the select procedure type ID.
        function selcode(typeid) {
            location.href = 'find_order_popup.php?order=' + <?php echo js_url($order); ?> + '&labid=' + <?php echo js_url($labid);
            if (isset($_GET['addfav'])) {
                echo " + '&addfav=' + " . js_url($_GET['addfav']);
            }
            if (isset($_GET['formid'])) {
                echo " + '&formid=' + " . js_url($_GET['formid']);
            }
            if (isset($_GET['formseq'])) {
                echo " + '&formseq=' + " . js_url($_GET['formseq']);
            }
            ?> + '&typeid=' + encodeURIComponent(typeid);
            return false;
        }
    </script>
</head>
<body>
<div class="container">
    <div class="row">
        <form class="form-inline" method='post' name='theform' action='find_order_popup.php<?php echo "?order=" . attr_url($order) . "&labid=" . attr_url($labid);
        if (isset($_GET['formid'])) {
            echo '&formid=' . attr_url($_GET['formid']);
        }

        if (isset($_GET['formseq'])) {
            echo '&formseq=' . attr_url($_GET['formseq']);
        }
        if (isset($_GET['addfav'])) {
            echo '&addfav=' . attr_url($_GET['addfav']);
        }
        ?>'>
        <div class="col-sm-12">
            <div class="input-group">
                <input type="hidden" name='isfav' value='<?php echo attr($_REQUEST['ordLookup'] ?? ''); ?>' />
                <input class="form-control" id='search_term' name='search_term' value='<?php echo attr($_REQUEST['search_term'] ?? ''); ?>' title='<?php echo xla('Any part of the desired code or its description'); ?>' placeholder="<?php echo xla('Search for') ?>&hellip;"/>
                <span class="input-group-append">
                    <button type="submit" class="btn btn-primary btn-search" name='bn_search' value="true"><?php echo xlt('Search'); ?></button>
                    <?php if (!isset($_REQUEST['addfav'])) { ?>
                        <button type="submit" class="btn btn-primary btn-search" name='bn_grpsearch' value="true"><?php echo xlt('Favorites'); ?></button>
                    <?php } ?>
                    <button type="button" class="btn btn-danger btn-delete" onclick="selcode(0)"><?php echo xlt('Erase'); ?></button>
                </span>
            </div>
        </div>
        <?php if (!empty($_REQUEST['bn_search']) || !empty($_REQUEST['bn_grpsearch'])) { ?>
            <div class="table-responsive mt-3">
                <table class="table table-striped table-sm">
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
                        $anchor = "<a href='' onclick='return selcode(" . attr_js($itertypeid) . ")'>";
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
</div>
</body>
</html>
