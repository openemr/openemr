<?php
include_once("../../globals.php");

// allow a custom 'delete' form
$deleteform = $incdir . "/forms/" . $_GET["formname"]."/delete.php";
if (file_exists($deleteform)) {
    include_once($deleteform);
    exit;
}

// if no custom 'delete' form, then use a generic one

// when the Cancel button is pressed, where do we go?
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

if ($_POST['confirm']) {
    // set the deleted flag of the indicated form
    $sql = "update forms set deleted=1 where id=".$_POST['id'];
    if ($_POST['id'] != "*" && $_POST['id'] != '') sqlInsert($sql);
    // log the event   
    newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], "Form ".$_POST['formname']." deleted from Encounter ".$_POST['encounter']);

    // redirect back to the encounter
    $address = "{$GLOBALS['rootdir']}/patient_file/encounter/$returnurl";
    echo "\n<script language='Javascript'>top.restoreSession();window.location='$address';</script>\n";
    exit;
}
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

</head>

<body class="body_top">

<span class="title">Delete Encounter Form</span>

<form method="post" action="<?php echo $rootdir;?>/patient_file/encounter/delete_form.php" name="my_form" id="my_form">
<?php
// output each GET variable as a hidden form input
foreach ($_GET as $key => $value) {
    echo '<input type="hidden" id="'.$key.'" name="'.$key.'" value="'.$value.'"/>'."\n";
}
?>
<input type="hidden" id="confirm" name="confirm" value="1"/>
<p>
You are about to delete the form '<?php echo $_GET['formname']; ?>' from <?php xl('This Encounter','e'); ?>.
</p>
<input type="button" id="confirmbtn" name="confirmbtn" value="Yes, Delete this form">
<input type="button" id="cancel" name="cancel" value="Cancel">
</form>

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#confirmbtn").click(function() { return ConfirmDelete(); });
    $("#cancel").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });
});

function ConfirmDelete() {
    if (confirm("This action cannot be undone. Are you sure you wish to delete this form?")) {
        top.restoreSession();
        $("#my_form").submit();
        return true;
    }
    return false;
}

</script>

</html>
