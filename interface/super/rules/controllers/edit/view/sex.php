<?php
/**
 * interface/super/rules/controllers/edit/view/sex.php
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


<div class="row">
    <div class="col-12">
        <span class="title2"><?php echo xlt('Gender Identity'); ?></span>
    </div>

    <div class="col-11 offfset-1">
        <table class="table table-sm table-condensed table-hover">
            <!-- category -->
            <tr>
                <td class="text-right"data-fld="fld_value">
                    <?php echo render_select(array( "target"   =>  "fld_sex",
                        "name"     =>  "fld_sex",
                        "value"    =>  $criteria->value,
                        "options"  =>  $criteria->getOptions() )); ?>
                </td>
            </tr>
            <!-- optional/required and inclusion/exclusion fields -->
            <?php echo common_fields(array( "criteria" => $criteria)); ?>
        </table>

    </div>
</div>
