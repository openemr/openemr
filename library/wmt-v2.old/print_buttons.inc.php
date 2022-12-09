<?php
if(!isset($print_href)) $print_href = '';
if(!isset($print_action)) $print_action = '';
?>
<br>
<br>
<div class="wmtNoPrint" style="float: left; padding-left: 10px;"><a href="<?php echo ($print_href) ? $print_href : 'javascript:;'; ?>" class="css_button wmtNoPrint" tabindex="-1" <?php echo ($print_href) ? '' : 'onclick="window.print(); '.$print_action .'"'; ?>><span class="wmtNoPrint">Print Form</span></a></div>
<div class="wmtNoPrint" style="float: right; padding-right: 10px;"><a href="javascript:;" class="css_button wmtNoPrint" tabindex="-1" onclick="window.close();"><span class="wmtNoPrint">Cancel</span></a></div>
<br>
