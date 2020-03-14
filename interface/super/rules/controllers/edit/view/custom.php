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
    <script language="javascript" src="<?php js_src('custom.js') ?>"></script>

    <script type="text/javascript">
        var custom = new custom( { selectedColumn: <?php echo js_escape($criteria->column); ?> } );
        custom.init();
    </script>
</head>

    <div class="col-12">
        <span class="title2"><?php echo xlt('Custom Selection'); ?></span>
    </div>

    <div class="col-12">
        <table class="table table-sm table-condensed table-hover">
            <!-- category -->
            <tr>
                <td class="text-right">
                    <span data-field="fld_table"><?php echo xlt('Table'); ?></span>
                </td>
                <td>
                    <?php echo render_select(array( "id"       =>  "fld_table",
                        "target"   =>  "fld_table",
                        "name"     =>  "fld_table",
                        "options"  =>  $criteria->getTableNameOptions(),
                        "value"    =>  $criteria->table)); ?>
                </td>
            </tr>
            <tr>
                <td class="text-right">
                    <span data-field="fld_table"><?php echo xlt('Column'); ?></span>
                </td>
                <td>
                    <?php echo render_select(array( "id"       =>  "fld_column",
                        "target"   =>  "fld_column",
                        "name"     =>  "fld_column",
                        "options"  =>  array(),
                        "value"    =>  null )); ?>
                </td>
            </tr>
            <tr>
                <td class="text-right"><span data-field="fld_value"><?php echo xlt('This DB field should hold this value'); ?></span></td>
                <td class="loose">
                    <select data-grp-tgt="" type="dropdown" name="fld_value_comparator" id="">
                        <option id="" value=""></option>
                        <option id="le" value="le" <?php echo $criteria->valueComparator == "le" ? "SELECTED" : "" ?>><?php echo "<=" ;?></option>
                        <option id="lt" value="lt" <?php echo $criteria->valueComparator == "lt" ? "SELECTED" : "" ?>><?php echo "<" ;?></option>
                        <option id="eq" value="eq" <?php echo $criteria->valueComparator == "eq" ? "SELECTED" : "" ?>><?php echo "=" ;?></option>
                        <option id="gt" value="gt" <?php echo $criteria->valueComparator == "gt" ? "SELECTED" : "" ?>><?php echo ">" ;?></option>
                        <option id="ge" value="ge" <?php echo $criteria->valueComparator == "ge" ? "SELECTED" : "" ?>><?php echo ">=" ;?></option>
                        <option id="ne" value="ne" <?php echo $criteria->valueComparator == "ne" ? "SELECTED" : "" ?>><?php echo "!=" ;?></option>
                    </select>

                    <input data-grp-tgt="fld_value" class="field short"
                           type="text"
                           name="fld_value"
                           value="<?php echo attr($criteria->value); ?>" />
                </td>
            </tr>
            <tr>
                <td class="text-right"><!-- frequency -->
                    <span data-field="fld_frequency"><?php echo xlt('How many times should we find this in the DB'); ?></span>
                </td>
                <td class="nowrap"><select data-grp-tgt="" type="dropdown" name="fld_frequency_comparator" id="">
                        <option id="" value=""></option>
                        <option id="le" value="le" <?php echo $criteria->frequencyComparator == "le" ? "SELECTED" : "" ?>><?php echo "<=" ;?></option>
                        <option id="lt" value="lt" <?php echo $criteria->frequencyComparator == "lt" ? "SELECTED" : "" ?>><?php echo "<" ;?></option>
                        <option id="eq" value="eq" <?php echo $criteria->frequencyComparator == "eq" ? "SELECTED" : "" ?>><?php echo "=" ;?></option>
                        <option id="gt" value="gt" <?php echo $criteria->frequencyComparator == "gt" ? "SELECTED" : "" ?>><?php echo ">" ;?></option>
                        <option id="ge" value="ge" <?php echo $criteria->frequencyComparator == "ge" ? "SELECTED" : "" ?>><?php echo ">=" ;?></option>
                        <option id="ne" value="ne" <?php echo $criteria->frequencyComparator == "ne" ? "SELECTED" : "" ?>><?php echo "!=" ;?></option>
                    </select>

                    <input data-grp-tgt="fld_frequency" class="field short"
                           type="text"
                           name="fld_frequency"
                           value="<?php echo attr($criteria->frequency); ?>" />
                </td>
            </tr>
            <?php echo common_fields(array(
                "criteria" => $criteria,
                "type" => $viewBean->type )); ?>

        </table>

    </div>
