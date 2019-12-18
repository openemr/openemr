<?php
/**
 * interface/super/rules/controllers/edit/view/diagnosis.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>

<head>
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>" type="text/css">
    <script language="javascript" src="../../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript">
        // This invokes the find-code popup.
        function sel_diagnosis() {
            dlgopen('../../patient_file/encounter/find_code_popup.php', '_blank', 500, 400);
        }
        // This is for callback by the find-code popup.
        // Only allows one entry.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            var s = '';
            if (code) {
                s = codetype + ':' + code;
            }
            f.fld_value.value = s;
        }
    </script>
</head>

<!-- diagnosis -->
<div class="col-12">
    <span class="title2">Add a  <?php echo text($criteria->getTitle()); ?></span>
</div>
<div class="col-12 indent10 text">
    <span class="bold" data-fld="fld_diagnosis">
        <?php //echo text($criteria->getTitle()); ?></span>
    <span class="indent10"><input id="fld_value" type="text"
                                  name="fld_value" class="field"
                                  placeholder="click to search for codes"
                                  onclick="sel_diagnosis()" value="<?php echo attr($criteria->getRequirements()); ?>"></span>
</div>

<div class="col-10 ">
    <table class="table table-100" >
        <!-- optional/required and inclusion/exclusion fields -->
        <?php echo common_fields(array( "criteria" => $criteria)); ?>
    </table>
</div>