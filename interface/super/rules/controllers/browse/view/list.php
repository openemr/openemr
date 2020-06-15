<?php

/**
 * interface/super/rules/controllers/browse/view/list.php
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

<script src="<?php js_src('list.js') ?>"></script>
<script src="<?php js_src('jQuery.fn.sortElements.js') ?>"></script>

<script>
    var list = new list_rules();
    list.init();
</script>

<table class="table header">
    <tr>
        <td class="title"><?php echo xlt('Plans Configuration'); ?></td>
        <td>
            <a href="index.php?action=browse!plans_config" class="iframe_medium btn btn-primary">
                <span><?php echo xlt('Go'); ?></span>
            </a>
        </td>
    </tr>
    <tr>
        <td class="title"><?php echo xlt('Rules Configuration'); ?></td>
        <td>
            <a href="index.php?action=edit!summary" class="iframe_medium btn btn-primary" onclick="top.restoreSession()">
                <span><?php echo xlt('Add new{{Rule}}'); ?></span>
            </a>
        </td>
    </tr>
</table>

<div class="rule_container text">
    <div class="rule_row header">
        <div class="rule_type header_type"><?php echo xlt('Type'); ?></div>
        <div class="rule_title header_title"><?php echo xlt('Name'); ?></div>
    </div>
</div>

<!-- template -->
<div class="rule_row data template">
    <div class="rule_type"><a href="index.php?action=detail!view" onclick="top.restoreSession()"></a></div>
    <div class="rule_title"><a href="index.php?action=detail!view" onclick="top.restoreSession()"></a></div>
</div>
