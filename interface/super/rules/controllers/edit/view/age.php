<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<!-- age -->
<p class="row">
    <span class="left_col colhead req" data-fld="fld_value"><?php echo out( xl( 'Age' ) );?> <?php echo out( xl( $criteria->getType() ) ); ?></span>
    <span class="end_col"><input id="fld_value" type="text" name="fld_value" class="field short" value="<?php echo out( $criteria->getRequirements() ); ?>"></span>
</p>

<!-- age unit -->
<p class="row">
    <span class="left_col colhead req" data-fld="fld_timeunit"><?php echo out( xl('Unit') );?></span>
    <span class="end_col">
    <?php echo timeunit_select( array( "context" => "rule_age_intervals", "target"=>"fld_target_interval_type", "name" => "fld_target_interval_type", "value" => $criteria->timeUnit ) ); ?>
    </span>
</p>

<input type="hidden" name="fld_type" value="<?php echo out( $criteria->type ); ?>"/>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields( array( "criteria" => $criteria) ); ?>
