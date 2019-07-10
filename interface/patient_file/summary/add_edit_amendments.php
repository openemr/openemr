<?php
/**
 * Add/Edit Amendments
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Hema Bandaru <hemab@drcloudemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Ensoftek
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (isset($_POST['mode'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $currentUser = $_SESSION['authUserID'];
    $created_time = date('Y-m-d H:i');
    if ($_POST["amendment_id"] == "") {
        // New. Insert
        $query = "INSERT INTO amendments SET
			amendment_date = ?,
			amendment_by = ?,
			amendment_status = ?,
			pid = ?,
			amendment_desc = ?,
			created_by = ?,
			created_time = ?";
        $sqlBindArray = array(
            DateToYYYYMMDD($_POST['amendment_date']),
            $_POST['form_amendment_by'],
            $_POST['form_amendment_status'],
            $pid,
            $_POST['desc'],
            $currentUser,
            $created_time
        );

        $amendment_id = sqlInsert($query, $sqlBindArray);
    } else {
        $amendment_id = $_POST['amendment_id'];
        // Existing. Update
        $query = "UPDATE amendments SET
			amendment_date = ?,
			amendment_by = ?,
			amendment_status = ?,
			amendment_desc = ?,
			modified_by = ?,
			modified_time = ?
			WHERE amendment_id = ?";
        $sqlBindArray = array(
            DateToYYYYMMDD($_POST['amendment_date']),
            $_POST['form_amendment_by'],
            $_POST['form_amendment_status'],
            $_POST['desc'],
            $currentUser,
            $created_time,
            $_POST['amendment_id']
        );
        sqlStatement($query, $sqlBindArray);
    }

    // Insert into amendments_history
    $query = "INSERT INTO amendments_history SET
		amendment_id = ? ,
		amendment_note = ?,
		amendment_status = ?,
		created_by = ?,
		created_time = ?";
    $sqlBindArray = array(
        $amendment_id,
        $_POST['note'],
        $_POST["form_amendment_status"],
        $currentUser,
        $created_time
    );
    sqlStatement($query, $sqlBindArray);
    header("Location:add_edit_amendments.php?id=" . urlencode($amendment_id));
    exit;
}

$amendment_id = ( $amendment_id ) ? $amendment_id : $_REQUEST['id'];
if ($amendment_id) {
    $query = "SELECT * FROM amendments WHERE amendment_id = ? ";
    $resultSet = sqlQuery($query, array($amendment_id));
    $amendment_date = $resultSet['amendment_date'];
    $amendment_status = $resultSet['amendment_status'];
    $amendment_by = $resultSet['amendment_by'];
    $amendment_desc = $resultSet['amendment_desc'];

    $query = "SELECT * FROM amendments_history ah INNER JOIN users u ON ah.created_by = u.id WHERE amendment_id = ? ";
    $resultSet = sqlStatement($query, array($amendment_id));
}

// Check the ACL
$haveAccess = acl_check('patients', 'trans');
$onlyRead = ( $haveAccess ) ? 0 : 1;
$onlyRead = ( $onlyRead || $amendment_status ) ? 1 : 0;
$customAttributes = ( $onlyRead ) ? array("disabled" => "true") : null;

?>

<html>
<head>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js"></script>

<!-- page styles -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.min.css">

<style>
.highlight {
  color: green;
}
tr.selected {
  background-color: white;
}
.historytbl {
 border-collapse: collapse;
}
.historytbl td th{
  border: 1px solid #000;
}
</style>

<script type="text/javascript">

function formValidation() {
    if ( $("#amendment_date").val() == "" ) {
        alert(<?php echo xlj('Select Amendment Date'); ?>);
        return;
    } else if ( $("#form_amendment_by").val() == "" ) {
        alert(<?php echo xlj('Select Requested By'); ?>);
        return;
    }

    var statusText = $("#form_amendment_status option:selected").text();
    $("#note").val($("#note").val() + ' ' + statusText);

    $("#add_edit_amendments").submit();
}

$(function() {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>

</head>

<body class="body_top">

<form action="add_edit_amendments.php" name="add_edit_amendments" id="add_edit_amendments" method="post" onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

    <table>
    <tr>
        <td>
            <span class="title"><?php echo xlt('Amendments'); ?></span>&nbsp;
        </td>
        <?php if (! $onlyRead) { ?>
        <td>
            <a href=# onclick="formValidation()" class="css_button_small"><span><?php echo xlt('Save');?></span></a>
        </td>
        <?php } ?>
        <td>
            <a href="list_amendments.php" class="css_button_small"><span><?php echo xlt('Back');?></span></a>
        </td>
    </tr>
    </table>

    <br>
    <table border=0 cellpadding=1 cellspacing=1>
        <tr>
            <td><span class=text ><?php echo xlt('Requested Date'); ?></span></td>
            <td>
            <?php if (! $onlyRead) { ?>
                <input type='text' size='10' class='datepicker' name="amendment_date" id="amendment_date"
                    value='<?php echo $amendment_date ? attr(oeFormatShortDate($amendment_date)) : attr(oeFormatShortDate()); ?>'
                />
            <?php } else { ?>
                <input type='text' size='10' name="amendment_date" id="amendment_date" readonly
                    value='<?php echo $amendment_date ? attr(oeFormatShortDate($amendment_date)) : attr(oeFormatShortDate()); ?>'
                />
            <?php } ?>
            </td>
        </tr>

        <tr>
            <td><span class=text ><?php echo xlt('Requested By'); ?></span></td>
            <td>
                <?php echo generate_select_list("form_amendment_by", "amendment_from", $amendment_by, 'Amendment Request By', ' ', '', '', '', $customAttributes); ?>
            </td>
        </tr>

        <tr>
            <td><span class=text ><?php echo xlt('Request Description'); ?></span></td>
            <td><textarea <?php echo ( $onlyRead ) ? "readonly" : "";  ?> id="desc" name="desc" rows="4" cols="30"><?php
            if ($amendment_id) {
                echo text($amendment_desc);
            } else {
                echo "";
            } ?></textarea></td>
        </tr>

        <tr>
            <td><span class=text ><?php echo xlt('Request Status'); ?></span></td>
            <td>
                <?php echo generate_select_list("form_amendment_status", "amendment_status", $amendment_status, 'Amendment Status', ' ', '', '', '', $customAttributes); ?>
            </td>
        </tr>

        <tr>
            <td><span class=text ><?php echo xlt('Comments'); ?></span></td>
            <td><textarea <?php echo ( $onlyRead ) ? "readonly" : "";  ?> id="note" name="note" rows="4" cols="30"><?php
            if ($amendment_id) {
                echo "";
            } else {
                echo xlt('New amendment request');
            } ?></textarea></td>
        </tr>
    </table>

    <?php if ($amendment_id) { ?>
    <hr>

    <span class="title"><?php echo xlt("History") ; ?></span>

    <table border="1" cellpadding=3 cellspacing=0 class="historytbl">

    <!-- some columns are sortable -->
    <tr class='text bold'>
        <th align="left" style="width:15%"><?php echo xlt('Date'); ?></th>
        <th align="left" style="width:25%"><?php echo xlt('By'); ?></th>
        <th align="left" style="width:15%"><?php echo xlt('Status'); ?></th>
        <th align="left"><?php echo xlt('Comments'); ?></th>
    </tr>

        <?php
        if (sqlNumRows($resultSet)) {
            while ($row = sqlFetchArray($resultSet)) {
                $created_date = date('Y-m-d', strtotime($row['created_time']));
                echo "<tr>";
                $userName = $row['lname'] . ", " . $row['fname'];
                echo "<td align=left class=text>" . text(oeFormatShortDate($created_date)) . "</td>";
                echo "<td align=left class=text>" . text($userName) . "</td>";
                echo "<td align=left class=text>" . ( ( $row['amendment_status'] ) ? generate_display_field(array('data_type'=>'1','list_id'=>'amendment_status'), $row['amendment_status']) : '') . "</td>";
                echo "<td align=left class=text>" . text($row['amendment_note']) . "</td>";
                echo "<tr>";
            }
        }
        ?>
    </table>
    <?php } ?>

    <input type="hidden" id="mode" name="mode" value=""/>
    <input type="hidden" id="amendment_id" name="amendment_id" value="<?php echo attr($amendment_id); ?>"/>
</form>
</body>
</html>
