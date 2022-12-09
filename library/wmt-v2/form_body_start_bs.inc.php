<body class="bgcolor2" onLoad="<?php echo $load; ?>">
<div id="save-notification" class="notification" style="left: 45%; top: 40%; <?php echo $save_notification_display; ?>"><?php xl('Processing','e'); ?>....</div>

<div id="pageLoader" class="loaderContainer fixedContainer backWhite" style="display:none;">
	<div class="spinner-border"></div>
</div>

<div id="overDiv" style="position:absolute; visibility: hidden; z-index:3000;"></div>
<form action="<?php echo $GLOBALS['rootdir'].$save_style; ?>" method="post" enctype="multipart/form-data" name="<?php echo $frmdir; ?>" id="<?php echo $frmdir; ?>" class="m-0" >
<input name="tmp_scroll_top" id="tmp_scroll_top" type="hidden" value="<?php echo $dt['tmp_scroll_top']; ?>" />
<div class='small-form'><!-- THIS IS THE OVERALL BODY START -->

<?php include($GLOBALS['srcdir'].'/wmt-v2/floating_menu_bs.inc.php'); ?>

<div class="mx-3 my-0"><!-- THIS IS THE OVERALL INNER CONTAINER START -->

<?php include($GLOBALS['srcdir'].'/wmt-v2/form_top_title_bs.inc.php'); ?>


