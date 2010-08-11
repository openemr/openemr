<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

$special_timeout = 3600;
include_once("../../globals.php");

//Bring in the style sheet
?>
<link rel="stylesheet" href="/openemr/interface/themes/style_sky_blue.css" type="text/css">
<?php  
// Hide the current value css entries. This is currently specific
//  for the vitals form but could use this mechanism for other
//  forms.
// Hiding classes:
//  currentvalues - input boxes
//  valuesunfocus - input boxes that are auto-calculated
//  editonly      - the edit and cancel buttons
// Showing class:
//  readonly      - the link back to summary screen
?>
<style>
  .currentvalues { display: none;}
  .valuesunfocus { display: none;}
  .editonly      { display: none;}
</style>
<?php
// Use jquery to show the 'readonly' class entries
?>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  $('.readonly').show();      	
});
</script>
	
<?php
if (substr($_GET["formname"], 0, 3) === 'LBF') {
  // Use the List Based Forms engine for all LBFxxxxx forms.
  include_once("$incdir/forms/LBF/new.php");
}
else {
  include_once("$incdir/forms/" . $_GET["formname"] . "/new.php");
}
?>