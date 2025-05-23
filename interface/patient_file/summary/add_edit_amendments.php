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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;


//ensure user has proper access
if (!AclMain::aclCheckCore('patients', 'amendment')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Amendments")]);
    exit;
}
$editAccess = AclMain::aclCheckCore('patients', 'amendment', '', 'write');
$addAccess = ($editAccess || AclMain::aclCheckCore('patients', 'amendment', '', 'addonly'));

if (isset($_POST['mode'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $currentUser = $_SESSION['authUserID'];
    $created_time = date('Y-m-d H:i');
    if ($_POST["amendment_id"] == "") {
        // New. Insert
        if (!$addAccess) {
            echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Amendment Add")]);
            exit;
        }
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
        if (!$editAccess) {
            echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Amendment Edit")]);
            exit;
        }
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

$amendment_id = $amendment_id ?? ($_REQUEST['id'] ?? '');
if (!empty($amendment_id)) {
    $query = "SELECT * FROM amendments WHERE amendment_id = ? ";
    $resultSet = sqlQuery($query, array($amendment_id));
    $amendment_date = $resultSet['amendment_date'];
    $amendment_status = $resultSet['amendment_status'];
    $amendment_by = $resultSet['amendment_by'];
    $amendment_desc = $resultSet['amendment_desc'];

    $query = "SELECT * FROM amendments_history ah INNER JOIN users u ON ah.created_by = u.id WHERE amendment_id = ? ";
    $resultSet = sqlStatement($query, array($amendment_id));
}

$onlyRead = ( $editAccess || ($addAccess && empty($amendment_id)) ) ? 0 : 1;
$onlyRead = ( $onlyRead || (!empty($amendment_status)) ) ? 1 : 0;
$customAttributes = ( $onlyRead ) ? array("disabled" => "true") : null;
?>

<html>
<head>

<?php Header::setupHeader('datetime-picker');
echo "<title>" . xlt('Amendments') . "</title>";
?>

<script>

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

$(function () {
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

<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Amendments'); ?></h2>
            </div>
            <div class="col-12">
                <div class="btn-group">
                    <?php if (! $onlyRead) { ?>
                        <a href=# onclick="formValidation()" class="btn btn-primary btn-sm btn-save"><span><?php echo xlt('Save');?></span></a>
                    <?php } ?>
                    <a href="list_amendments.php" class="btn btn-secondary btn-sm btn-back"><span><?php echo xlt('Back');?></span></a>
                </div>
            </div>
            <div class="col-12">
                <form action="add_edit_amendments.php" name="add_edit_amendments" id="add_edit_amendments" method="post" onsubmit='return top.restoreSession()'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Requested Date'); ?></label>
                        <?php if (! $onlyRead) { ?>
                            <input type='text' size='10' class='form-control datepicker' name="amendment_date" id="amendment_date"
                                value='<?php echo (!empty($amendment_date)) ? attr(oeFormatShortDate($amendment_date)) : attr(oeFormatShortDate()); ?>'
                            />
                        <?php } else { ?>
                            <input type='text' size='10' class='form-control' name="amendment_date" id="amendment_date" readonly
                                value='<?php echo (!empty($amendment_date)) ? attr(oeFormatShortDate($amendment_date)) : attr(oeFormatShortDate()); ?>'
                            />
                        <?php } ?>
                    </div>

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Requested By'); ?></label>
                        <?php echo generate_select_list("form_amendment_by", "amendment_from", ($amendment_by ?? ''), 'Amendment Request By', ' ', '', '', '', $customAttributes); ?>
                    </div>

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Request Description'); ?></label>
                        <textarea <?php echo ( $onlyRead ) ? "readonly" : "";  ?> id="desc" class="form-control" name="desc" rows="4" cols="30"><?php
                        if (!empty($amendment_id)) {
                            echo text($amendment_desc);
                        } else {
                            echo "";
                        }
                        ?></textarea>
                    </div>

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Request Status'); ?></label>
                        <?php echo generate_select_list("form_amendment_status", "amendment_status", ($amendment_status ?? ''), 'Amendment Status', ' ', '', '', '', $customAttributes); ?>
                    </div>

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Comments'); ?></label>
                        <textarea <?php echo ( $onlyRead ) ? "readonly" : "";  ?> id="note" class="form-control" name="note" rows="4" cols="30"><?php
                        if (!empty($amendment_id)) {
                            echo "";
                        } else {
                            echo xlt('New amendment request');
                        }
                        ?></textarea>
                    </div>

                    <input type="hidden" id="mode" name="mode" value=""/>
                    <input type="hidden" id="amendment_id" name="amendment_id" value="<?php echo attr($amendment_id); ?>"/>
                </form>
            </div>
            <?php if (!empty($amendment_id)) { ?>
            <hr />
            <div class="col-12">
                <h2><?php echo xlt("History") ; ?></h2>
            </div>

            <table class="table table-bordered table-hover">
                <!-- some columns are sortable -->
                <thead class="table-primary font-weight-bold">
                    <tr>
                        <th><?php echo xlt('Date'); ?></th>
                        <th><?php echo xlt('By'); ?></th>
                        <th><?php echo xlt('Status'); ?></th>
                        <th><?php echo xlt('Comments'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (sqlNumRows($resultSet)) {
                    while ($row = sqlFetchArray($resultSet)) {
                        $created_date = date('Y-m-d', strtotime($row['created_time']));
                        echo "<tr>";
                        $userName = $row['lname'] . ", " . $row['fname'];
                        echo "<td>" . text(oeFormatShortDate($created_date)) . "</td>";
                        echo "<td>" . text($userName) . "</td>";
                        echo "<td>" . ( ( $row['amendment_status'] ) ? generate_display_field(array('data_type' => '1','list_id' => 'amendment_status'), $row['amendment_status']) : '') . "</td>";
                        echo "<td>" . text($row['amendment_note']) . "</td>";
                        echo "<tr>";
                    }
                }
                ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
    </div>

</body>
</html>
