<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<p class="row">
    <span class="left_col colhead req" data-fld="fld_lifestyle"><?php echo out( $criteria->getTitle() ); ?></span>
    <span class="end_col">
    <?php echo render_select( array( "target"   =>  "fld_lifestyle",
                                     "name"     =>  "fld_lifestyle",
                                     "value"    =>  $criteria->type,
                                     "options"  =>  $criteria->getOptions() ) ); ?>
    </span>
</p>

<br/>

<p class="lifestyle">
    <span class="left_col colhead req"><?php echo out( xl( 'Value' ) ); ?></span>
    <span class="end_col">
        <input type="radio" name="fld_value_type" class="field" value="match"
               <?php echo !is_null($criteria->matchValue) ? "CHECKED" : ""?>> <?php echo out( xl( 'Match' ) ); ?>
        <input type="text" name="fld_value" class="field short" value="<?php echo out( $criteria->matchValue ); ?>" />
    </span>
</p>

<p class="row lifestyle">
    <span class="left_col colhead">&nbsp;</span>
    <span class="end_col">
        <input type="radio" name="fld_value_type" class="field" value="any"
               <?php echo is_null($criteria->matchValue) ? "CHECKED" : ""?>> <?php echo out( xl( 'Any' ) ); ?>
    </span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields( array( "criteria" => $criteria) ); ?>