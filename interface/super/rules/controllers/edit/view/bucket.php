<?php

/**
 * interface/super/rules/controllers/edit/view/bucket.php
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
    <script src="<?php js_src('bucket.js') ?>"></script>

    <script>
        var bucket = new bucket({});
        bucket.init();
    </script>
</head>

<!-- category -->
<?php
$change_link = '<a href="javascript:;" id="change_category" onclick="top.restoreSession();">(' . xlt('Change') . ')</a>';
echo textfield_row(array("id" => "fld_category_lbl",
    "name" => "fld_category_lbl",
    "title" => xl("Category"),
    "value" => $criteria->getCategoryLabel(),
    "render_link" => $change_link)); ?>
<input type="hidden" id="fld_category" name="fld_category" value="<?php echo attr($criteria->category); ?>" />

<!-- item -->
<?php
$change_link = '<a href="javascript:;" id="change_item" onclick="top.restoreSession();">(' . xlt('Change') . ')</a>';
echo textfield_row(array("id" => "fld_item_lbl",
    "name" => "fld_item_lbl",
    "title" => xl("Item"),
    "value" => $criteria->getItemLabel(),
    "render_link" => $change_link)); ?>
<input type="hidden" id="fld_item" name="fld_item" value="<?php echo attr($criteria->item); ?>" />

<!-- completed -->
<p class="form-row">
    <span class="left_col colhead req" data-field="fld_completed"><?php echo xlt('Completed?'); ?></span>
    <span class="end_col">
        <select class="form-control" data-grp-tgt="" type="dropdown" name="fld_completed" id="">
            <option id="" value="">--<?php echo xlt('Select'); ?>--</option>
            <option id="Yes" value="yes" <?php echo $criteria->completed ? "SELECTED" : "" ?>><?php echo xlt('Yes'); ?></option>
            <option id="No" value="no" <?php echo !$criteria->completed ? "SELECTED" : "" ?>><?php echo xlt('No'); ?></option>
        </select>
    </span>
</p>

<!-- frequency -->
<p class="form-row">
    <span class="left_col colhead req" data-field="fld_frequency"><?php echo xlt('Frequency'); ?></span>
    <span class="end_col">
        <select class="form-control" data-grp-tgt="" type="dropdown" name="fld_frequency_comparator" id="">
            <option id="" value="">--<?php echo xlt("Select"); ?>--</option>
            <option id="le" value="le" <?php echo $criteria->frequencyComparator == "le" ? "SELECTED" : "" ?>><?php echo "<="; ?></option>
            <option id="lt" value="lt" <?php echo $criteria->frequencyComparator == "lt" ? "SELECTED" : "" ?>><?php echo "<"; ?></option>
            <option id="eq" value="eq" <?php echo $criteria->frequencyComparator == "eq" ? "SELECTED" : "" ?>><?php echo "="; ?></option>
            <option id="gt" value="gt" <?php echo $criteria->frequencyComparator == "gt" ? "SELECTED" : "" ?>><?php echo ">"; ?></option>
            <option id="ge" value="ge" <?php echo $criteria->frequencyComparator == "ge" ? "SELECTED" : "" ?>><?php echo ">="; ?></option>
            <option id="ne" value="ne" <?php echo $criteria->frequencyComparator == "ne" ? "SELECTED" : "" ?>><?php echo "!="; ?></option>
        </select>

        <input data-grp-tgt="fld_frequency" class="form-control field short"
            type="text"
            name="fld_frequency"
            value="<?php echo attr($criteria->frequency); ?>" />
    </span>

    <br />

    <!-- optional/required and inclusion/exclusion fields -->
    <?php echo common_fields(array("criteria" => $criteria)); ?>
