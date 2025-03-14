<?php

// file new.php for pediatric FEVER evaluation

// presents a blank form for evaluating pediatric FEVER

// this file made by andres@paglayan.com on 2004-09-23

// input designed by Lowell Gordon, MD lgordon@whssf.org

// to max the billing complexity coding



require_once(__DIR__ . "/../../globals.php");
require_once("../../../library/api.inc.php");

use OpenEMR\Core\Header;

formHeader("Pediatric Fever Evaluation");

?>

<html><head>

<?php Header::setupHeader(); ?>

</head>

<body class="body_top">



<!--REM note that every input method has the same name as a valid column, this will make things easier in save.php -->



<br />

<form method='post' action="<?php echo $rootdir;?>/forms/ped_fever/save.php?mode=new" name='ped_fever' >



<!-- the form goes here -->

<?php

    $obj = array(); // just to avoid undeclared var warning

    require('form.php'); // to use a single file for both, empty and editing

?>

<!-- the form ends here -->



<!--REM note our nifty jscript submit -->

<a href="javascript:top.restoreSession();document.ped_fever.submit();" class="link_submit">[Save]</a>

<br />



<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>

</form>



<?php

formFooter();

?>
