<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<p class="row">
    <span class="left_col colhead req" data-fld="fld_sex"><?php echo xl('Sex');?></span>
    <span class="end_col">
    <?php echo render_select( array( "target"   =>  "fld_sex",
                                     "name"     =>  "fld_sex",
                                     "value"    =>  $criteria->value,
                                     "options"  =>  $criteria->getOptions() ) ); ?>
    </span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields( array( "criteria" => $criteria) ); ?>