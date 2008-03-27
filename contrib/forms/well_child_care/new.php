<?php

// file new.php for well child evaluation

// input designed by Lowell Gordon, MD lgordon@whssf.org



include_once("../../globals.php");

include_once("../../../library/api.inc");

include_once("C_WellChildCare.class.php");

?>

<html><head>
<?php html_header_show();?>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

</head>

<body class="body_top">



<!--REM note that every input method has the same name as a valid column, this will make things easier in save.php -->



<br>

<form method='post' action="<?php echo $rootdir;?>/forms/well_child_care/save.php?mode=new" name='well_child_care' >



<!-- the form goes here -->

<?php

		$form=new C_WellChildCare($pid);

		$a=$form->put_form();

?>

<!-- the form ends here -->



<!--REM note our nifty jscript submit -->

<a href="javascript:top.restoreSession();document.well_child_care.submit();" class="link_submit">[Save]</a>

<br>



<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>

</form>



<?php

formFooter();

?>

