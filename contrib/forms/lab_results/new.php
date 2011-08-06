<?php

// file new.php

include_once("../../globals.php");

include_once("../../../library/api.inc");

formHeader("Lab Results");



?>

<html><head>
<?php html_header_show();?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>

<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>



<br>

<form method='post' action="<?echo $rootdir;?>/forms/lab_results/save.php?mode=new" name='lab_results_form' enctype="multipart/form-data">

<span class=title>Lab Results</span>

<br>



<span class=text>Notes:</span><br>

<textarea name="notes" wrap="virtual" cols="45" rows="10"></textarea><br>



<!--REM note our nifty jscript submit -->

<input type="hidden" name="action" value="submit">

<a href="javascript:top.restoreSession();document.lab_results_form.submit();" class="link_submit">[Save]</a>

<br>



<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>

</form>



<?php

formFooter();

?>

