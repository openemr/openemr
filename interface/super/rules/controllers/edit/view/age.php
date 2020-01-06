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
    
    if ($criteria->getType()=='Min') {
        $title = "Minimum Age";
    } elseif ($criteria->getType()=='Max') {
        $title = "Maximum Age";
    }
?>
<!-- age -->

<div class="row">
    <div class="col-12">
        <span class="title2"><?php echo xlt($title); ?></span>
    </div>

    <div class="col-8 offset-2">
        <table class="table table-sm table-condensed table-hover">
            <!-- category -->
            <tr>
                <td class="text-right"data-fld="fld_value">
                    <?php echo xlt('Age'); ?>
                </td>
                <td class="tight">
                    <input id="fld_value" type="text" name="fld_value" class="field short" value="<?php echo attr($criteria->getRequirements()); ?>">
                    <?php echo timeunit_select(array( "context" => "rule_age_intervals", "target"=>"fld_target_interval_type", "name" => "fld_target_interval_type", "value" => $criteria->timeUnit )); ?>
                    <input type="hidden" name="fld_type" value="<?php echo attr($criteria->type); ?>"/>
                </td>
            </tr>
            <!-- optional/required and inclusion/exclusion fields -->
            <?php //echo common_fields(array( "criteria" => $criteria)); ?>
        </table>

    </div>
</div>
