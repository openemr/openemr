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

    <div class="col-12">
        <span class="title2"><?php echo xlt($criteria->getTitle()); ?>: Social History</span>
    </div>

    <div class="col-12">
        <table class="table table-sm table-condensed table-hover" style="display: table;">
            <!-- category -->
            <tr>
                <td class="text-center" colspan="2"><?php echo render_select(array( "target"   =>  "fld_lifestyle",
                    "name"     =>  "fld_lifestyle",
                    "value"    =>  $criteria->type,
                    "label"    =>  '',
                    "options"  =>  $criteria->getOptions() )); ?>
                </td>
            </tr>
            <tr>
                <td rowspan="2" class="text-right">
                    <span class="req"><?php echo xlt('Given the Social History category selected above, we are looking at this value:'); ?></span>
                </td>
                <td class="text-nowrap">
                    <input type="radio" name="fld_value_type" class="" value="match"
                        <?php echo !is_null($criteria->matchValue) ? "CHECKED" : ""?>> <?php echo xlt('Match'); ?>
                    <input type="text" name="fld_value" class="form-control" value="<?php echo attr($criteria->matchValue); ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" name="fld_value_type" class="" value="any"
                        <?php echo is_null($criteria->matchValue) ? "CHECKED" : ""?>> <?php echo xlt('Any'); ?>

                </td>
            </tr>
            <!-- optional/required and inclusion/exclusion fields -->
            <?php echo common_fields(array( "criteria" => $criteria)); ?>
        </table>

    </div>
