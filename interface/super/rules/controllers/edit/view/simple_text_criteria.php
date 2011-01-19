<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<p class="row">
    <span class="left_col colhead req" data-fld="fld_value"><?php echo out( $criteria->getTitle() ); ?></span>
    <span class="end_col"><input id="fld_value" type="text" name="fld_value" class="field" value="<?php echo out( $criteria->getRequirements() ); ?>"></span>
</p>

<?php //echo textfield_row(array("name" => "fld_value",
      //                         "title" => $criteria->getTitle(),
      //                         "value" =>$criteria->getRequirements() ) ); ?>


<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields( array( "criteria" => $criteria) ); ?>