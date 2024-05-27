<?php

/*
 * Work/School Note Form new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
 * @copyright Copyright (c) Open Source Medical Software
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * sherwingaddis@gmail.com added bootstrap 2023
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once dirname(__FILE__, 3) . "/globals.php";
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: note");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION["authUser"]));
/* name of this form */
$form_name = "note";
?>
<html>
<head>
    <?php Header::setupHeader('datetime-picker'); ?>
    <script>
        // required for textbox date verification
        const mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

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
        <div class="col-sm-12 mt-5">
            <h1><?php echo xlt('Work/School Note'); ?></h1>
            <?php echo text(date("F d, Y", time())); ?>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <form class="form" method=post action="<?php echo $rootdir . "/forms/" . $form_name . "/save.php?mode=new";?>" name="my_form" id="my_form">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                    <div style="margin: 10px;">
                        <input type="button" class="btn btn-primary save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
                        <input type="button" class="btn btn-warning dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
                    </div>

                    <select class="form-control" name="note_type">
                        <option value="WORK NOTE"><?php echo xlt('WORK NOTE'); ?></option>
                        <option value="SCHOOL NOTE"><?php echo xlt('SCHOOL NOTE'); ?></option>
                    </select>
                    <br />
                    <b><?php echo xlt('MESSAGE:'); ?></b>
                    <br />
                    <textarea class="form-control" name="message" id="message" rows="7" cols="47"></textarea>
                    <br />

                    <br />
                    <b><?php echo xlt('Signature:'); ?></b>
                    <br />
                    <table class="table">
                        <tr><td>
                                <?php echo xlt('Doctor:'); ?>
                                <input class="form-control" type="text" name="doctor" id="doctor" value="<?php echo attr($provider_results["fname"]) . ' ' . attr($provider_results["lname"]); ?>">
                            </td>

                            <td>
                                <span class="text"><?php echo xlt('Date'); ?></span>
                                <input class='datepicker form-control' type='text' size='16' name='date_of_signature' id='date_of_signature' autocomplete='off'
                                       value='<?php echo attr(oeFormatShortDate(date('Y-m-d'))); ?>'
                                       title='<?php echo xla('Date of Signature'); ?>' />
                            </td>
                        </tr>
                    </table>
                    <div style="margin: 10px;">
                        <input type="button" class="btn btn-primary save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
                        <input type="button" class="btn btn-warning dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    // jQuery stuff to make the page a little easier to use
    $(function () {
        $(".save").click(function() { top.restoreSession(); $('#my_form').submit(); });
        $(".dontsave").click(function() { parent.closeTab(window.name, false); });
    });
</script>
</html>
