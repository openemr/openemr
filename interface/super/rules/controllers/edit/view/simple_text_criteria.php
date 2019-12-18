<?php
/**
 * interface/super/rules/controllers/edit/view/simple_text_criteria.php
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
        <span class="title2"><?php echo text($criteria->getTitle()); ?></span>
    </div>

    <div class="col-11 offfset-1">
        <table class="table table-sm table-condensed table-hover">
            <tr>
                <td colspan="2" data-fld="fld_value">
                    <input id="fld_value" type="text" name="fld_value" class="field" value="<?php echo attr($criteria->getRequirements()); ?>">
                </td>
            </tr>
            <!-- optional/required and inclusion/exclusion fields -->
            <?php echo common_fields(array( "criteria" => $criteria)); ?>
        </table>

    </div>
</div>
