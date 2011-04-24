<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<head>
    <script language="javascript" src="<?php js_src('bucket.js') ?>"></script>

    <script type="text/javascript">
        var bucket = new bucket( {} );
        bucket.init();
    </script>
</head>

<!-- category -->
<?php echo textfield_row(array("id" => "fld_category_lbl",
                               "name" => "fld_category_lbl",
                               "title" => xl("Category"),
                               "value" => $criteria->getCategoryLabel() ) ); ?>
<br/><a href="javascript:;" id="change_category" onclick="top.restoreSession()">(change)</a>
<input type="hidden" id="fld_category" name="fld_category" value="<?php echo $criteria->category?>" />

<!-- item -->
<?php echo textfield_row(array("id" => "fld_item_lbl",
                               "name" => "fld_item_lbl",
                               "title" => xl("Item"),
                               "value" => $criteria->getItemLabel() ) ); ?>
<br/><a href="javascript:;" id="change_item" onclick="top.restoreSession()">(change)</a>
<input type="hidden" id="fld_item" name="fld_item" value="<?php echo out( $criteria->item ); ?>" />

<!-- completed -->
<p class="row">
    <span class="left_col colhead req" data-field="fld_completed"><?php echo out( xl( 'Completed?' ) ); ?></span>
    <span class="end_col">
        <select data-grp-tgt="" type="dropdown" name="fld_completed" id="">
            <option id="" value="">--<?php echo out( xl( 'Select' ) ); ?>--</option>
            <option id="Yes" value="yes" <?php echo $criteria->completed ? "SELECTED" : "" ?>><?php echo out( xl( 'Yes' ) ); ?></option>
            <option id="No" value="no" <?php echo !$criteria->completed ? "SELECTED" : "" ?>><?php echo out( xl( 'No' ) ); ?></option>
        </select>
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

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields( array( "criteria" => $criteria) ); ?>
