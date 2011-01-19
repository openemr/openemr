<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<head>
    <script language="javascript" src="<?php js_src('custom.js') ?>"></script>

    <script type="text/javascript">
        var custom = new custom( { selectedColumn: '<?php echo out( $criteria->column ); ?>' } );
        custom.init();
    </script>
</head>

<!-- table -->
<p class="row">
    <span class="left_col colhead req" data-field="fld_table"><?php echo xl('Table'); ?></span>
    <span class="end_col">
        <?php echo render_select( array( "id"       =>  "fld_table",
                                         "target"   =>  "fld_table",
                                         "name"     =>  "fld_table",
                                         "options"  =>  $criteria->getTableNameOptions(),
                                         "value"    =>  out( $criteria->table ) ) ); ?>
    </span>
</p>

<!-- column -->
<p class="row">
    <span class="left_col colhead" data-field="fld_table"><?php echo out( xl( 'Column' ) ); ?></span>
    <span class="end_col">
        <?php echo render_select( array( "id"       =>  "fld_column",
                                         "target"   =>  "fld_column",
                                         "name"     =>  "fld_column",
                                         "options"  =>  array(),
                                         "value"    =>  null ) ); ?>
    </span>
</p>

<!-- value -->
<p class="row">
    <span class="left_col colhead req" data-field="fld_value"><?php echo xl('Value'); ?></span>
    <span class="end_col">
        <select data-grp-tgt="" type="dropdown" name="fld_value_comparator" id="">
            <option id="" value="">--<?php echo xl("Select"); ?>--</option>
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
           value="<?php echo $criteria->value ?>" />
    </span>
</p>

<!-- frequency -->
<p class="row">
    <span class="left_col colhead req" data-field="fld_frequency"><?php echo out( xl( 'Frequency' ) ); ?></span>
    <span class="end_col">
        <select data-grp-tgt="" type="dropdown" name="fld_frequency_comparator" id="">
            <option id="" value="">--<?php echo xl("Select"); ?>--</option>
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
           value="<?php echo out( $criteria->frequency ); ?>" />
    </span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields( array( "criteria" => $criteria) ); ?>

