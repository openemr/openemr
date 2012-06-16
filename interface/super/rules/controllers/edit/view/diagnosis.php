<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<head>
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>" type="text/css">
    <script language="javascript" src="../../../library/dialog.js"></script>
    <script type="text/javascript">
        // This invokes the find-code popup.
        function sel_diagnosis() {
            dlgopen('../../patient_file/encounter/find_code_popup.php', '_blank', 500, 400);
        }
        // This is for callback by the find-code popup.
        // Only allows one entry.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            var s = '';
            if (code) {
                s = codetype + ':' + code;
            }
            f.fld_value.value = s;
        }
    </script>
</head>

<!-- diagnosis -->
<p class="row">
    <span class="left_col colhead req" data-fld="fld_diagnosis"><?php echo out( $criteria->getTitle() ); ?></span>
    <span class="end_col"><input id="fld_value" type="text" name="fld_value" class="field" onclick="sel_diagnosis()" value="<?php echo out( $criteria->getRequirements() ); ?>"></span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields( array( "criteria" => $criteria) ); ?>
