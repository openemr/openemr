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
    <?php if ($_SESSION['language_direction'] == "rtl") { ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rtl_rules.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } else { ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rules.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } ?>
    
    <script src="../../../library/dialog.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    
    <script>
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
<p class="form-row">
    <span class="left_col colhead req" data-fld="fld_diagnosis"><?php echo text($criteria->getTitle()); ?></span>
    <span class="end_col"><input id="fld_value" type="text" name="fld_value" class="form-control field" onclick="sel_diagnosis()" value="<?php echo attr($criteria->getRequirements()); ?>"></span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields(array( "criteria" => $criteria)); ?>
