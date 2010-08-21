<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

$special_timeout = 3600;
include_once("../../globals.php");

//Bring in the style sheet
?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
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
// Also customize the 'graph' class to look like links.
?>
<style>
  .currentvalues { display: none;}
  .valuesunfocus { display: none;}
  .editonly      { display: none;}

  .graph {color:#0000cc;}

  #chart {
    border-style:solid;
    border-width:2px;
    margin:0em 1em 2em 2em;
  }
</style>

<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/openflashchart/js/swfobject.js"></script>
<script type="text/javascript">

// Show the selected chart in the 'chart' div element
function show_graph(name,table)
{
  top.restoreSession();
  //function only appears to allow passing of one parameter to data-file, so combine name____table for passing
  parameters=name + "___" + table;
  a=encodeURIComponent(parameters);
  swfobject.embedSWF('../../../library/openflashchart/open-flash-chart.swf', "chart", "650", "200", "9.0.0", "", {"data-file":"../../../library/openflashchart/graphs.php?params=" + a} );
}

$(document).ready(function(){

  // Use jquery to show the 'readonly' class entries
  $('.readonly').show();

  // Place click callback for graphing
  $(".graph").click(function(e){show_graph(this.id,'form_vitals'); });

  // Show hovering effects for the .graph links
  $(".graph").hover(
    function(){
         $(this).css({color:'#ff5555'}); //mouseover
    },
    function(){
         $(this).css({color:'#0000cc'}); // mouseout
    }
  );

  // show blood pressure graph by default
  show_graph('bps','form_vitals');

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
