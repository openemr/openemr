<?php

/**
 * interface/super/rules/controllers/edit/view/add_criteria.php
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
<?php $allowed = $viewBean->allowed?>
<?php $ruleId = $viewBean->id;?>
<?php $groupId = $viewBean->groupId;?>

<table class="table header">
  <tr>
        <td class="title"><?php echo xlt('Rule Edit'); ?></td>
        <td>
            <a href="index.php?action=detail!view&id=<?php echo attr_url($ruleId); ?>" class="iframe_medium btn btn-secondary" onclick="top.restoreSession()">
                <span><?php echo xlt('Cancel'); ?></span>
            </a>
        </td>
  </tr>
</table>

<div class="rule_detail edit">
    <p class="header"><?php echo xlt('Add criteria'); ?> </p>
    <ul>
    <?php foreach ($allowed as $type) { ?>
        <li>
        <a href="index.php?action=edit!choose_criteria&id=<?php echo attr_url($ruleId); ?>&group_id=<?php echo attr_url($groupId); ?>&type=<?php echo attr_url($viewBean->type); ?>&criteriaType=<?php echo attr_url($type->code); ?>" onclick="top.restoreSession()">
            <?php echo xlt($type->lbl); ?>
        </a>
        </li>
    <?php } ?>
    </ul>
</div>
