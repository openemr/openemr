<?php

/**
 * interface/super/rules/controllers/edit/view/age.php
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
<!-- age -->
<p class="form-row">
    <span class="left_col colhead req" data-fld="fld_value"><?php echo xlt('Age'); ?> <?php echo xlt($criteria->getType()); ?></span>
    <span class="end_col"><input id="fld_value" type="text" name="fld_value" class="form-control field short" value="<?php echo attr($criteria->getRequirements()); ?>"></span>
</p>

<!-- age unit -->
<p class="form-row">
    <span class="left_col colhead req" data-fld="fld_timeunit"><?php echo xlt('Unit');?></span>
    <span class="end_col">
    <?php echo timeunit_select(array( "context" => "rule_age_intervals", "target" => "fld_target_interval_type", "name" => "fld_target_interval_type", "value" => $criteria->timeUnit )); ?>
    </span>
</p>

<input type="hidden" name="fld_type" value="<?php echo attr($criteria->type); ?>"/>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields(array( "criteria" => $criteria)); ?>
