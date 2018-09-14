<?php
require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");
?>
<html>
<head>
<?php html_header_show();?>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/manual-added-packages/jquery-min-1-2-2/index.js"></script>

<!-- DBC STUFF ================ -->

<script type="text/javascript">
$(document).ready(function(){

$('#closeztn').bind('click', function(){
    if ( confirm("Do you really want to close the ZTN?") ) {
        $.ajax({
            type: 'POST',
            url: 'as.php',
            data: 'cztn=1',
            async: false
        });
    }
    window.location.reload(true);
});

});
</script>

<script language="JavaScript">
<!-- hide from JavaScript-challenged browsers

function selas() {
  popupWin = window.open('dbc_aschoose.php', 'remote', 'width=800,height=700,scrollbars');
};

function selcl() {
  popupWin = window.open('dbc_close.php', 'remote', 'width=960,height=630,left=200,top=100,scrollbars');
};

function selfl() {
  popupWin = window.open('dbc_showfollowup.php', 'remote', 'width=500,height=270,left=200,top=100,scrollbars');
}
// done hiding --></script>


<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_bottom">


<?php
$pres = "prescription";
?>

<dl>
<dt><span href="coding.php" class="title"><?php echo xlt('Coding'); ?></span></dt>

<dd><a class="text" href="superbill_codes.php"
 target="_parent"
 onclick="top.restoreSession()">
<?php echo xlt('Superbill'); ?></a></dd>

<?php foreach ($code_types as $key => $value) { ?>
<dd><a class="text" href="search_code.php?type=<?php echo attr(urlencode($key)); ?>"
 target="Codes" onclick="top.restoreSession()">
<?php echo $key; ?> <?php echo xlt('Search'); ?></a></dd>
<?php } ?>

<dd><a class="text" href="copay.php" target="Codes" onclick="top.restoreSession()"><?php echo xlt('Copay'); ?></a></dd>
<dd><a class="text" href="other.php" target="Codes" onclick="top.restoreSession()"><?php echo xlt('Other'); ?></a></dd><br />

<?php if (!$GLOBALS['disable_prescriptions']) { ?>
<dt><span href="coding.php" class="title"><?php echo xlt('Prescriptions'); ?></span></dt>
<dd><a class="text" href="<?php echo $GLOBALS['webroot']?>/controller.php?<?php echo attr(urlencode($pres)); ?>&list&id=<?php echo attr(urlencode($pid)); ?>"
 target="Codes" onclick="top.restoreSession()"><?php echo xlt('List Prescriptions'); ?></a></dd>
<dd><a class="text" href="<?php echo $GLOBALS['webroot']?>/controller.php?<?php echo attr(urlencode($pres)); ?>&edit&id=&pid=<?php echo attr(urlencode($pid)); ?>"
 target="Codes" onclick="top.restoreSession()"><?php echo xlt('Add Prescription'); ?></a></dd>
<?php }; // if (!$GLOBALS['disable_prescriptions']) ?>
</dl>

</body>
</html>
