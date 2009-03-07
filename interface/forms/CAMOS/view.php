<!-- view.php -->
<?php
include_once("../../globals.php");
include_once("../../../library/api.inc");
formHeader("Form: CAMOS");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
$textarea_rows = 20;
$textarea_cols = 40;
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script type="text/javascript">
function checkall(){
  var f = document.my_form;
  var x = f.elements.length;
  var i;
  for(i=0;i<x;i++) {
    if (f.elements[i].type == 'checkbox') {
      f.elements[i].checked = true;
    }
  }
}
function uncheckall(){
  var f = document.my_form;
  var x = f.elements.length;
  var i;
  for(i=0;i<x;i++) {
    if (f.elements[i].type == 'checkbox') {
      f.elements[i].checked = false;
    }
  }
}
function content_focus() {
}
function content_blur() {
}
function show_edit(t) {
  var e = document.getElementById(t);
  if (e.style.display == 'none') {
    e.style.display = 'inline';
    return;
  }
  else {
    e.style.display = 'none';
  }
}
</script>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir?>/forms/CAMOS/save.php?mode=delete&id=<?php echo $_GET["id"];?>" name="my_form">
<h1> CAMOS </h1>
<input type="submit" name="delete" value="Delete Checked Items" />
<input type="submit" name="update" value="Update Checked Items" />
<?php
echo "<a href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl'>[do nothing]</a>";
?>
<br/><br/>
<input type='button' value='check all'
  onClick='checkall()'>
<input type='button' value='uncheck all'
  onClick='uncheckall()'>
<br/><br/>
<?php
//experimental code start

$pid = $GLOBALS['pid'];
$encounter = $GLOBALS['encounter'];

$query = "select t1.id, t1.content from form_CAMOS as t1 join forms as t2 " .
  "on (t1.id = t2.form_id) where t2.form_name like 'CAMOS%' " .
  "and t2.encounter like $encounter and t2.pid = $pid";

$statement = sqlStatement($query);
while ($result = sqlFetchArray($statement)) { 
    print "<input type=button value=edit onClick='show_edit(\"id_textarea_".$result['id']."\")'>";
    print "<input type=checkbox name='ch_".$result['id']."'> ".$result['content']."<br/>\n";
    print "<div id=id_textarea_".$result['id']." style='display:none'>\n";
    print "<textarea name=textarea_".$result['id']." cols=$textarea_cols rows= $textarea_rows onFocus='content_focus()' onBlur='content_blur()' >".$result['content']."</textarea><br/>\n";
    print "</div>\n";
  }


//experimental code end
?>
</form>
<?
formFooter();
?>
