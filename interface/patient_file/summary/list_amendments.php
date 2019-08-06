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

?>

<html>
<head>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>


<!-- page styles -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<style>
.highlight {
  color: green;
}
tr.selected {
  background-color: white;
}
</style>

<script type="text/javascript">
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

<body class="body_top">

<form action="list_amendments.php" name="list_amendments" id="list_amendments" method="post" onsubmit='return top.restoreSession()'>

<span class="title"><?php echo xlt('List'); ?></span>&nbsp;
<?php
    $query = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
    $resultSet = sqlStatement($query, array($pid));
if (sqlNumRows($resultSet)) { ?>
            <table cellspacing="0" cellpadding="0" style="width:100%">
                <tr>
                    <td><a href="javascript:checkForAmendments();" class="css_button"><span><?php echo xlt("Print Amendments"); ?></span></a></td>
                    <td align="right">
                        <a href="#" class="small" onClick="checkUncheck(1);"><span><?php echo xlt('Check All');?></span></a> |
                        <a href="#" class="small" onClick="checkUncheck(0);"><span><?php echo xlt('Clear All');?></span></a>
                    </td>
                </tr>
            </table>
        <div id="patient_stats">
            <br>
        <table border=0 cellpadding=0 cellspacing=0 style="margin-bottom:1em;">

        <tr class='head'>
            <th style="width:5%"></th>
            <th style="width:15%" align="left"><?php echo  xlt('Requested Date'); ?></th>
            <th style="width:40%" align="left"><?php echo  xlt('Request Description'); ?></th>
            <th style="width:25%" align="left"><?php echo  xlt('Requested By'); ?></th>
            <th style="width:15%" align="left"><?php echo  xlt('Request Status'); ?></th>
        </tr>

        <?php while ($row = sqlFetchArray($resultSet)) {
            $amendmentLink = "<a href='add_edit_amendments.php?id=" . attr_url($row['amendment_id']) . "'>" . text(oeFormatShortDate($row['amendment_date'])) . "</a>";
            ?>
            <tr class="amendmentrow" id="<?php echo attr($row['amendment_id']); ?>">
                <td><input id="check_list[]" name="check_list[]" type="checkbox" value="<?php echo attr($row['amendment_id']); ?>"></td>
                <td class=text><?php echo $amendmentLink; ?> </td>
                <td class=text><?php echo text($row['amendment_desc']); ?> </td>
                <td class=text><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'amendment_from'), $row['amendment_by']); ?> </td>
                <td class=text><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'amendment_status'), $row['amendment_status']); ?> </td>
            </tr>
        <?php } ?>
        </table>
        </div>
<?php } else { ?>
        <span style="color:red">
            <br>
            <?php echo xlt("No amendment requests available"); ?>
        </span>
<?php } ?>
</form>
</body>

</html>
