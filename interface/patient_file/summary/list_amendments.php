<?php

/**
 * List Amendments
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Hema Bandaru <hemab@drcloudemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Ensoftek
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

?>

<html>
<head>

<?php Header::setupHeader(); ?>

<script>
    function checkForAmendments() {
        var amendments = "";
        $("#list_amendments input:checkbox:checked").each(function() {
                amendments += $(this).val() + ",";
        });

        if ( amendments == '' ) {
            alert(<?php echo xlj('Select amendments to print'); ?>);
            return;
        }

        // Call the function to print
        var url = "print_amendments.php?ids=" + encodeURIComponent(amendments);
        window.open(url);
    }

    function checkUncheck(option) {
        $("input[name='check_list[]']").each( function () {
            var optionFlag = ( option ) ? true : false;
            $(this).prop('checked',optionFlag);
        });
    }
</script>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('List'); ?></h2>
            </div>
            <div class="col-12">
                <form action="list_amendments.php" name="list_amendments" id="list_amendments" method="post" onsubmit='return top.restoreSession()'>
                <?php
                $query = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
                $resultSet = sqlStatement($query, array($pid));
                if (sqlNumRows($resultSet)) { ?>
                    <table class="table w-100">
                        <tr>
                            <td>
                                <a href="javascript:checkForAmendments();" class="btn btn-primary btn-print"><?php echo xlt("Print Amendments"); ?></a>
                            </td>
                            <td class="text-right">
                                <a href="#" class="small" onClick="checkUncheck(1);"><span><?php echo xlt('Check All');?></span></a> |
                                <a href="#" class="small" onClick="checkUncheck(0);"><span><?php echo xlt('Clear All');?></span></a>
                            </td>
                        </tr>
                    </table>
                    <div id="patient_stats">
                        <br />
                        <table class="table w-100 table-borderless mb-3">
                            <thead class="table-primary">
                                <tr>
                                    <th></th>
                                    <th><?php echo xlt('Requested Date'); ?></th>
                                    <th><?php echo xlt('Request Description'); ?></th>
                                    <th><?php echo xlt('Requested By'); ?></th>
                                    <th><?php echo xlt('Request Status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($row = sqlFetchArray($resultSet)) {
                                $amendmentLink = "<a href='add_edit_amendments.php?id=" . attr_url($row['amendment_id']) . "'>" . text(oeFormatShortDate($row['amendment_date'])) . "</a>";
                                ?>
                                <tr class="amendmentrow" id="<?php echo attr($row['amendment_id']); ?>">
                                    <td><input id="check_list[]" name="check_list[]" type="checkbox" value="<?php echo attr($row['amendment_id']); ?>"></td>
                                    <td><?php echo $amendmentLink; ?> </td>
                                    <td><?php echo text($row['amendment_desc']); ?> </td>
                                    <td><?php echo generate_display_field(array('data_type' => '1','list_id' => 'amendment_from'), $row['amendment_by']); ?> </td>
                                    <td><?php echo generate_display_field(array('data_type' => '1','list_id' => 'amendment_status'), $row['amendment_status']); ?> </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p class="text-danger">
                        <?php echo xlt("No amendment requests available"); ?>
                    </p>
                <?php } ?>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
