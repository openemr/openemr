<?php

/**
 * interface/super/rules/controllers/edit/template/criteria.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['opener']); ?>
    <?php if ($_SESSION['language_direction'] == "rtl") { ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rtl_rules.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } else { ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rules.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } ?>
</head>

<body class='body_top'>
    <div class="container-xl">
        <?php $rule = $viewBean->rule ?>
        <?php $criteria = $viewBean->criteria ?>

        <script src="<?php js_src('edit.js') ?>"></script>
        <script>
            var edit = new rule_edit({});
            edit.init();
        </script>
        <div class="table-responsive">
            <table class="table header">
                <tr>
                    <td class="title"><?php echo xlt('Rule Edit'); ?></td>
                    <td>
                        <a href="index.php?action=detail!view&id=<?php echo attr_url($rule->id); ?>" class="iframe_medium btn btn-secondary" onclick="top.restoreSession()"><?php echo xlt('Cancel'); ?></a>
                        <a href="javascript:;" class="iframe_medium btn btn-primary" id="btn_save" onclick="top.restoreSession()"><span><?php echo xlt('Save'); ?></span></a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="rule_detail edit">
            <form action="index.php?action=edit!submit_criteria" method="post" id="frm_submit" onsubmit="return top.restoreSession()">
                <input type="hidden" name="id" value="<?php echo attr($rule->id); ?>" />
                <input type="hidden" name="group_id" value="<?php echo attr($criteria->groupId); ?>" />
                <input type="hidden" name="guid" value="<?php echo attr($criteria->guid); ?>" />
                <input type="hidden" name="type" value="<?php echo attr($viewBean->type); ?>" />
                <input type="hidden" name="criteriaTypeCode" value="<?php echo attr($criteria->criteriaType->code); ?>" />

                <!-- ----------------- -->
                <?php
                if (file_exists($viewBean->_view_body)) {
                    require_once($viewBean->_view_body);
                }
                ?>
                <!-- ----------------- -->

            </form>
        </div>
        <div id="required_msg" class="small">
            <span class="required">*</span><?php echo xlt('Required fields'); ?>
        </div>
    </div>
</body>
</html>
