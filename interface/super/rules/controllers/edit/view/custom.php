<?php

/**
 * interface/super/rules/controllers/edit/view/custom.php
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
    <script src="<?php js_src('custom.js') ?>"></script>

    <script>
        var custom = new custom( { selectedColumn: <?php echo js_escape($criteria->column); ?> } );
        custom.init();
    </script>
</head>

<!-- table -->
<p class="form-row">
    <span class="left_col colhead req" data-field="fld_table"><?php echo xlt('Table'); ?></span>
    <span class="end_col">
        <?php echo render_select(array( "id"       =>  "fld_table",
                                         "target"   =>  "fld_table",
                                         "name"     =>  "fld_table",
                                         "options"  =>  $criteria->getTableNameOptions(),
                                         "value"    =>  $criteria->table)); ?>
    </span>
</p>

<!-- column -->
<p class="form-row">
    <span class="left_col colhead" data-field="fld_table"><?php echo xlt('Column'); ?></span>
    <span class="end_col">
        <?php echo render_select(array( "id"       =>  "fld_column",
                                         "target"   =>  "fld_column",
                                         "name"     =>  "fld_column",
                                         "options"  =>  array(),
                                         "value"    =>  null )); ?>
    </span>
</p>

<!-- value -->
<p class="form-row">
    <span class="left_col colhead req" data-field="fld_value"><?php echo xlt('Value'); ?></span>
    <span class="end_col">
        <select class="form-control" data-grp-tgt="" type="dropdown" name="fld_value_comparator" id="">
            <option id="" value="">--<?php echo xlt("Select"); ?>--</option>
            <option id="le" value="le" <?php echo $criteria->valueComparator == "le" ? "SELECTED" : "" ?>><?php echo "<=" ;?></option>
            <option id="lt" value="lt" <?php echo $criteria->valueComparator == "lt" ? "SELECTED" : "" ?>><?php echo "<" ;?></option>
            <option id="eq" value="eq" <?php echo $criteria->valueComparator == "eq" ? "SELECTED" : "" ?>><?php echo "=" ;?></option>
            <option id="gt" value="gt" <?php echo $criteria->valueComparator == "gt" ? "SELECTED" : "" ?>><?php echo ">" ;?></option>
            <option id="ge" value="ge" <?php echo $criteria->valueComparator == "ge" ? "SELECTED" : "" ?>><?php echo ">=" ;?></option>
            <option id="ne" value="ne" <?php echo $criteria->valueComparator == "ne" ? "SELECTED" : "" ?>><?php echo "!=" ;?></option>
        </select>

        <input data-grp-tgt="fld_value" class="form-control field short" type="text" name="fld_value" value="<?php echo attr($criteria->value); ?>" />
    </span>
</p>

<!-- frequency -->
<p class="form-row">
    <span class="left_col colhead req" data-field="fld_frequency"><?php echo xlt('Frequency'); ?></span>
    <span class="end_col">
        <select class="form-control" data-grp-tgt="" type="dropdown" name="fld_frequency_comparator" id="">
            <option id="" value="">--<?php echo xlt("Select"); ?>--</option>
            <option id="le" value="le" <?php echo $criteria->frequencyComparator == "le" ? "SELECTED" : "" ?>><?php echo "<=" ;?></option>
            <option id="lt" value="lt" <?php echo $criteria->frequencyComparator == "lt" ? "SELECTED" : "" ?>><?php echo "<" ;?></option>
            <option id="eq" value="eq" <?php echo $criteria->frequencyComparator == "eq" ? "SELECTED" : "" ?>><?php echo "=" ;?></option>
            <option id="gt" value="gt" <?php echo $criteria->frequencyComparator == "gt" ? "SELECTED" : "" ?>><?php echo ">" ;?></option>
            <option id="ge" value="ge" <?php echo $criteria->frequencyComparator == "ge" ? "SELECTED" : "" ?>><?php echo ">=" ;?></option>
            <option id="ne" value="ne" <?php echo $criteria->frequencyComparator == "ne" ? "SELECTED" : "" ?>><?php echo "!=" ;?></option>
        </select>

        <input data-grp-tgt="fld_frequency" class="form-control field short" type="text" name="fld_frequency" value="<?php echo attr($criteria->frequency); ?>" />
    </span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields(array( "criteria" => $criteria)); ?>
