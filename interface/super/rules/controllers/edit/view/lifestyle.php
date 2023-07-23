<?php

/**
 * interface/super/rules/controllers/edit/view/lifestyle.php
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
<p class="form-row">
    <span class="left_col colhead req" data-fld="fld_lifestyle"><?php echo text($criteria->getTitle()); ?></span>
    <span class="end_col">
    <?php echo render_select(array( "target"   =>  "fld_lifestyle",
                                     "name"     =>  "fld_lifestyle",
                                     "value"    =>  $criteria->type,
                                     "options"  =>  $criteria->getOptions() )); ?>
    </span>
</p>

<br/>

<p class="lifestyle">
    <span class="left_col colhead req"><?php echo xlt('Value'); ?></span>
    <span class="end_col">
        <input type="radio" name="fld_value_type" class="field" value="match"
                <?php echo !is_null($criteria->matchValue) ? "CHECKED" : ""?>> <?php echo xlt('Match'); ?>
        <input type="text" name="fld_value" class="form-control field short" value="<?php echo attr($criteria->matchValue); ?>" />
    </span>
</p>

<p class="row lifestyle">
    <span class="left_col colhead">&nbsp;</span>
    <span class="end_col">
        <input type="radio" name="fld_value_type" class="field" value="any"
                <?php echo is_null($criteria->matchValue) ? "CHECKED" : ""?>> <?php echo xlt('Any'); ?>
    </span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields(array( "criteria" => $criteria)); ?>
