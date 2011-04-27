<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<head>
    <script language="javascript" src="<?php js_src('jQuery.autocomplete.js') ?>"></script>
    <script language="javascript" src="<?php js_src('typeahead.js') ?>"></script>
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>" type="text/css">
    <link rel="stylesheet" href="<?php css_src('jQuery.autocomplete.css') ?>" type="text/css">

    <script type="text/javascript">
        var type_ahead = new type_ahead( {
            url: "index.php?action=edit!codes",
            inputId: "fld_value"
        });
        type_ahead.init();
    </script>
</head>

<!-- diagnosis -->
<p class="row">
    <span class="left_col colhead req" data-fld="fld_diagnosis"><?php echo out( $criteria->getTitle() ); ?></span>
    <span class="end_col"><input id="fld_value" type="text" name="fld_value" class="field" value="<?php echo out( $criteria->getRequirements() ); ?>"></span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields( array( "criteria" => $criteria) ); ?>