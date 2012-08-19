<?php


// file new.php

// presents a blank form to upload an image file

// this file made by andres@paglayan.com on 2004-04-23

// custom from for uploading scanned documents into the emr.

include_once("../../globals.php");

include_once("../../../library/api.inc");

formHeader("Scanned Documents Input");



?>

<html><head>
<?php html_header_show();?>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

</head>

<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>



<!--REM note that every input method has the same name as a valid column, this will make things easier in save.php -->



<br>

<form method='post' action="<?php echo $rootdir;?>/forms/documents/save.php?mode=new" name='document_form' enctype="multipart/form-data">

<span class=title>Scanned Documents Input</span>

<br>



<span class=text>Document:</span><br>

<input type="file" name="document_image"><br>



<br>

<span class=text>Description:</span> <input type=text name="document_description">

<br>



<span class=text>Who sent it?:</span> <input type=text name="document_source">

<br>



<!--REM note our nifty jscript submit -->

<input type="hidden" name="action" value="submit">

<a href="javascript:top.restoreSession();document.document_form.submit();" class="link_submit">[Save]</a>

<br>



<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>

</form>



<?php

formFooter();

?>

