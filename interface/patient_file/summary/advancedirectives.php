<?php

/**
 * Advance directives gui.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
?>

<html>
<head>
    <title><?php echo xlt('Advance Directives'); ?></title>

    <?php Header::setupHeader(['datetime-picker','opener']); ?>

    <?php
    if ($_POST['form_yesno']) {
        if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }

        $form_yesno = filter_input(INPUT_POST, 'form_yesno');
        $form_adreviewed = DateToYYYYMMDD(filter_input(INPUT_POST, 'form_adreviewed'));
        sqlQuery("UPDATE patient_data SET completed_ad = ?, ad_reviewed = ? where pid = ?", array($form_yesno,$form_adreviewed,$pid));
        // Close this window and refresh the calendar display.
        echo "</head><body>\n<script>\n";
        echo " if (!opener.closed && opener.refreshme) opener.refreshme();\n";
        echo " dlgclose();\n";
        echo "</script>\n</body>\n</html>\n";
        exit();
    }

    $sql = "select completed_ad, ad_reviewed from patient_data where pid = ?";
    $myrow = sqlQuery($sql, array($pid));
    if ($myrow) {
        $form_completedad = $myrow['completed_ad'];
        $form_adreviewed = $myrow['ad_reviewed'];
    }
    ?>

    <script>
        function validate(f) {
            if (f.form_adreviewed.value == "") {
                  alert(<?php echo xlj('Please enter a date for Last Reviewed.'); ?>);
                  f.form_adreviewed.focus();
                  return false;
            }
            return true;
        }

        $(function () {
            $("#cancel").click(function() { dlgclose(); });

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
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3><?php echo xlt('Advance Directives'); ?></h3>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form action='advancedirectives.php' method='post' onsubmit='return validate(this)'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="form-group">
                        <label for="form_yesno"><?php echo xlt('Completed'); ?></label>
                        <?php generate_form_field(array('data_type' => 1,'field_id' => 'yesno','list_id' => 'yesno','empty_title' => 'SKIP'), $form_completedad); ?>
                    </div>
                    <div class="form-group">
                        <label for="form_adreviewed"><?php echo xlt('Last Reviewed'); ?></label>
                        <?php generate_form_field(array('data_type' => 4,'field_id' => 'adreviewed'), oeFormatShortDate($form_adreviewed)); ?>
                    </div>
                    <div class="form-group">
                        <div class="btn-group" role="group">
                            <button type="submit" id="create" class="btn btn-secondary btn-save"><?php echo xla('Save'); ?></button>
                            <button type="button" id="cancel" class="btn btn-link btn-cancel"><?php echo xla('Cancel'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <?php
                $query = "SELECT id FROM categories WHERE name='Advance Directive'";
                $myrow2 = sqlQuery($query);
                if ($myrow2) {
                    $parentId = $myrow2['id'];
                    $query = "SELECT id, name FROM categories WHERE parent = ?";
                    $resNew1 = sqlStatement($query, array($parentId));
                    while ($myrows3 = sqlFetchArray($resNew1)) {
                        $categoryId = $myrows3['id'];
                        $nameDoc = $myrows3['name'];
                        $query = "SELECT documents.date, documents.id " .
                                 "FROM documents " .
                                 "INNER JOIN categories_to_documents " .
                                 "ON categories_to_documents.document_id=documents.id " .
                                   "WHERE categories_to_documents.category_id=? " .
                                   "AND documents.foreign_id=? AND documents.deleted = 0" .
                                "ORDER BY documents.date DESC";
                        $resNew2 = sqlStatement($query, array($categoryId, $pid));
                          $counterFlag = false; //flag used to check for empty categories
                        while ($myrows4 = sqlFetchArray($resNew2)) {
                            $dateTimeDoc = $myrows4['date'];
                            $idDoc = $myrows4['id'];
                            ?>
                            <br />
                            <a href='<?php echo $web_root; ?>/controller.php?document&retrieve&patient_id=<?php echo attr_url($pid); ?>&document_id=<?php echo attr_url($idDoc); ?>&as_file=true'>
                                <?php echo text(xl_document_category($nameDoc)); ?>
                            </a>
                            <?php echo text($dateTimeDoc);
                                $counterFlag = true;
                        }

                          // if no associated docs with category then show it's empty
                        if (!$counterFlag) {
                            ?>
                            <br /><?php echo text($nameDoc); ?><span style='color:red;'>[<?php echo xlt('EMPTY'); ?>]</span>
                            <?php
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
