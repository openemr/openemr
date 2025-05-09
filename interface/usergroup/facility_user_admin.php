<?php

/**
 * edit per-facility user information.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Scott Wakefield <scott@npclinics.com.au>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 NP Clinics <info@npclinics.com.au>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Ensure authorized
if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit Facility Specific User Information")]);
    exit;
}

// Ensure variables exist
if (!isset($_GET["user_id"]) || !isset($_GET["fac_id"])) {
    die(xlt("Error"));
}

?>

<html>

<head>

    <title><?php echo xlt("Edit Facility Specific User Information"); ?></title>

    <?php Header::setupHeader(['common', 'datetime-picker', 'opener', 'select2']); ?>

    <script>
        $(function() {
            $(".select-dropdown").select2({
                theme: "bootstrap4",
                <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
            });
            if (typeof error !== 'undefined') {
                if (error) {
                    alertMsg(error);
                }
            }

            $("#form_facility_user").submit(function(event) {
                top.restoreSession();
                event.preventDefault();
                var post_url = $(this).attr("action");
                var request_method = $(this).attr("method");
                var form_data = $(this).serialize();
                $.ajax({
                    url: post_url,
                    type: request_method,
                    data: form_data
                }).done(function(r) {
                    dlgclose('refreshme', false);
                });
            });

            $("#cancel").click(function() {
                dlgclose();
            });

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = false; ?>
                <?php $datetimepicker_maxDate = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma
                ?>
            });
            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = false; ?>
                <?php $datetimepicker_maxDate = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma
                ?>
            });
            $('.datepicker-past').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = false; ?>
                <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma
                ?>
            });
            $('.datetimepicker-past').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = false; ?>
                <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma
                ?>
            });
            $('.datepicker-future').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = '-1970/01/01'; ?>
                <?php $datetimepicker_maxDate = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma
                ?>
            });
            $('.datetimepicker-future').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = '-1970/01/01'; ?>
                <?php $datetimepicker_maxDate = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma
                ?>
            });
        });
    </script>

</head>

<body>
    <?php
    // Collect user information
    $user_info = sqlQuery("select * from `users` WHERE `id` = ?", array($_GET["user_id"]));

    // Collect facility information
    $fac_info = sqlQuery("select * from `facility` where `id` = ?", array($_GET["fac_id"]));

    // Collect layout information and store them in an array
    $l_res = sqlStatement("SELECT * FROM layout_options " .
        "WHERE form_id = 'FACUSR' AND uor > 0 AND field_id != '' " .
        "ORDER BY group_id, seq");
    $l_arr = array();
    for ($i = 0; $row = sqlFetchArray($l_res); $i++) {
        $l_arr[$i] = $row;
    }
    ?>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title">
                    <h3><?php echo xlt('Edit Facility Specific User Information'); ?></h3>
                </div>
            </div>
        </div>
        <div class="row">
            <form name='form_facility_user' id='form_facility_user' method='post' action="facility_user.php">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <input type=hidden name=mode value="facility_user_id">
                <input type=hidden name=user_id value="<?php echo attr($_GET["user_id"]); ?>">
                <input type=hidden name=fac_id value="<?php echo attr($_GET["fac_id"]); ?>">

                <table class="table table-borderless ">
                    <tr>
                        <td>
                            <?php echo xlt('User'); ?>:
                        </td>
                        <td>
                            <?php echo text($user_info['username']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo xlt('Facility'); ?>:
                        </td>
                        <td>
                            <?php echo text($fac_info['name']); ?>
                        </td>
                    </tr>
                    <?php foreach ($l_arr as $layout_entry) { ?>
                        <tr>
                            <td style="width:180px;">
                                <?php echo text(xl_layout_label($layout_entry['title'])) ?>:
                            </td>
                            <td style="width:270px;">
                                <?php
                                $entry_data = sqlQuery("SELECT `field_value` FROM `facility_user_ids` " .
                                    "WHERE `uid` = ? AND `facility_id` = ? AND `field_id` = ?", array($user_info['id'], $fac_info['id'], $layout_entry['field_id']));
                                echo generate_form_field($layout_entry, ($entry_data['field_value'] ?? ''));
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <button type="submit" class="btn btn-secondary btn-save" name='form_save' id='form_save' href='#'>
                                <?php echo xlt('Save'); ?>
                            </button>
                            <a class="btn btn-link btn-cancel" id='cancel' href='#'>
                                <?php echo xlt('Cancel'); ?>
                            </a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    <!-- include support for the list-add selectbox feature -->
    <?php require $GLOBALS['fileroot'] . "/library/options_listadd.inc.php"; ?>

    <script>
        <?php echo $date_init; ?>
    </script>
</body>

</html>
