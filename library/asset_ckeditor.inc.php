<?php
/**
 * Drop-in ckeditor control
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This script should be included after </body> tag.
 * Main form(s) should specify fields with class : oemr_editable
 **/

// Performance considerations -
// TBD
//
// Server setup

// -- Bring in components if needed --
//if (class_exists('OpenEMR\Core\Header')) {
// ckeditor won't work unless added to the Header::setupHeader list
//} else {
// This will fail if base script has not brought in jQ
$oemr_cke_dir = $GLOBALS['assets_static_relative']."/ckeditor-4-7-0";
print <<<_CKE_SCRIPTS
<script type="text/javascript" src="$oemr_cke_dir/ckeditor.js"></script>
<script type="text/javascript" src="$oemr_cke_dir/adapters/jquery.js"></script>
_CKE_SCRIPTS;
//}
?>

<script type="text/javascript">
$( 'textarea.md-edit' ).ckeditor();

$(".md-edit.md-edit-inline").each(function() {
    $(this).attr("contenteditable", "true");
    CKEDITOR.disableAutoInline = true;
    CKEDITOR.inline( $(this).attr('id') );
});

</script>
