<?php
include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("../../../library/sql.inc");
formHeader("Form: CAMOS");
?>


<?
$select_size = 20;
$textarea_rows = 25;
$textarea_cols = 55;
$debug = '';
$error = '';
$previous_encounter_data = '<hr><p>Previous Encounter CAMOS entries</p><hr>';
//get data from previous encounter to show at bottom of form for reference
$query = "SELECT t1.category, t1.subcategory, t1.item, t1.content FROM form_CAMOS as t1 JOIN forms as t2 on (t1.id = t2.form_id) where t2.encounter=(select max(encounter) from forms where form_name like 'CAMOS%' and encounter < ".$_SESSION['encounter']." and pid=".$_SESSION['pid'].") and t1.pid=".$_SESSION['pid'];
$statement = sqlStatement($query);
while ($result = sqlFetchArray($statement)) { 
$previous_encounter_data .= $result['category']." | ".$result['subcategory']." | ".$result['item']."<p><pre>".$result['content']."</pre></p><hr>";
}

//end of get data from previous encounter
//variables for preselect section below (after handle database changes):
$preselect_category = '';
$preselect_subcategory = '';
$preselect_item= '';
if ($_POST['hidden_category']) {$preselect_category = $_POST['hidden_category'];}
if ($_POST['hidden_subcategory']) {$preselect_subcategory = $_POST['hidden_subcategory'];}
if ($_POST['hidden_item']) {$preselect_item = $_POST['hidden_item'];}
//handle changes to database
if ($_POST['hidden_mode'] == 'add') {
  if ($_POST['hidden_selection'] == 'change_category') {
    $query = "INSERT INTO form_CAMOS_category (category) values ('";
    $query .= $_POST['change_category']."')"; 
    sqlInsert($query);
  }
  else if ($_POST['hidden_selection'] == 'change_subcategory') {
    $category_id = $_POST['hidden_category']; 
    if ($category_id >= 0 ) {
      $query = "INSERT INTO form_CAMOS_subcategory (subcategory, category_id) values ('";
      $query .= $_POST['change_subcategory']."', '".$category_id."')";
      sqlInsert($query);
    }
  }
  else if ($_POST['hidden_selection'] == 'change_item') {
    $category_id = $_POST['hidden_category']; 
    $subcategory_id = $_POST['hidden_subcategory']; 
    if (($category_id >= 0 ) && ($subcategory_id >=0)) {
      $query = "INSERT INTO form_CAMOS_item (item, content, subcategory_id) values ('";
      $query .= $_POST['change_item']."', '".$_POST['textarea_content']."', '".$subcategory_id."')";
      sqlInsert($query);
    }
    
  }
  else if ($_POST['hidden_selection'] == 'change_content') {
    $item_id = $_POST['hidden_item'];
    $content = $_POST['textarea_content'];
    if ($item_id >= 0) {
      $query = "UPDATE form_CAMOS_item set content = '".$content."' where id = ".$item_id;
      sqlInsert($query);
    }
  }
}
else if ($_POST['hidden_mode'] == 'delete') {
  if ($_POST['hidden_selection'] == 'change_category') {
    $to_delete_id = $_POST['hidden_category'];
    $to_delete_from_table = 'form_CAMOS_category';
    $to_delete_from_subtable = 'form_CAMOS_subcategory';
    $tablename = 'category';
    $subtablename = 'subcategory';
  }
  else if ($_POST['hidden_selection'] == 'change_subcategory') {
    $to_delete_id = $_POST['hidden_subcategory'];
    $to_delete_from_table = 'form_CAMOS_subcategory';
    $to_delete_from_subtable = 'form_CAMOS_item';
    $tablename = 'subcategory';
    $subtablename = 'item';
  }
  else if ($_POST['hidden_selection'] == 'change_item') {
    $to_delete_id = $_POST['hidden_item'];
    $to_delete_from_table = 'form_CAMOS_item';
    $to_delete_from_subtable = '';
    $tablename = 'item';
    $subtablename = '';
  }

  if ($subtablename == '') {
    $query = "DELETE FROM ".$to_delete_from_table." WHERE id like '".$to_delete_id."'";
    sqlInsert($query);
  }
  else {
    $query = "SELECT count(id) FROM ".$to_delete_from_subtable." WHERE ".$tablename."_id like '".$to_delete_id."'";
    $statement = sqlStatement($query);
    if ($result = sqlFetchArray($statement)) {
      if ($result['count(id)'] == 0) {
        $query = "DELETE FROM ".$to_delete_from_table." WHERE id like '".$to_delete_id."'";
        sqlInsert($query);
      }
      else {
        $error = $subtablename." not empty!";
      }
    }
  }
}
else if ($_POST['hidden_mode'] == 'alter') {
  $newval = $_POST[$_POST['hidden_selection']];
  if ($_POST['hidden_selection'] == 'change_category') {
    $to_alter_id = $_POST['hidden_category'];
    $to_alter_table = 'form_CAMOS_category';
    $to_alter_column = 'category';
  }
  else if ($_POST['hidden_selection'] == 'change_subcategory') {
    $to_alter_id = $_POST['hidden_subcategory'];
    $to_alter_table = 'form_CAMOS_subcategory';
    $to_alter_column = 'subcategory';
  }
  else if ($_POST['hidden_selection'] == 'change_item') {
    $to_alter_id = $_POST['hidden_item'];
    $to_alter_table = 'form_CAMOS_item';
    $to_alter_column = 'item';
  }
  $query = "UPDATE ".$to_alter_table." set ".$to_alter_column." = '".$newval."' where id = ".$to_alter_id; 
  sqlInsert($query);
}
// end handle changes to database

//preselect column items
//either a database change has been made, so the user should be made to feel that they never left the same CAMOS screen
//or, CAMOS has been started freshly, therefore the last entry of the current patient should be selected. 
$preselect_mode = '';
if ($preselect_category == '') {
  $preselect_mode = 'by name';
  //at this point, if this variable has not been set, CAMOS must have been start over
  //so let's get the most recent values from form_CAMOS for this patient's pid 
  $query = "SELECT category, subcategory, item FROM form_CAMOS WHERE id =(SELECT max(id) from form_CAMOS WHERE pid=".$_SESSION['pid'].")";
  $statement = sqlStatement($query);
  if ($result = sqlFetchArray($statement)) {
    $preselect_category = $result['category'];
    $preselect_subcategory = $result['subcategory'];
    $preselect_item = $result['item'];
  }
  else {$preselect_mode = '';}
}
else {
  $preselect_mode = 'by number';
}

//end preselect column items
?>

<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<script language="javascript" type="text/javascript">

var array1 = new Array();
var array2 = new Array();
var array3 = new Array();
var icd9_list = '';
var preselect_off = false;

<?
//ICD9
$query = "SELECT code_text, code FROM billing WHERE encounter=".$_SESSION['encounter'].
  " AND pid=".$_SESSION['pid']." AND code_type like 'ICD9'";
$statement = sqlStatement($query);
echo "icd9_list = \"\\n\\n\\\n";
while ($result = sqlFetchArray($statement)) {
  echo $result['code']." ".$result['code_text']."\\n\\\n";
}
echo "\";\n";

$query = "SELECT id, category FROM form_CAMOS_category ORDER BY category";
$statement = sqlStatement($query);
$i = 0;
while ($result = sqlFetchArray($statement)) {
  echo "array1[".$i."] = new Array('".$result['category']."','".$result['id']."', new Array());\n";
  $i++;
}
$i=0;
$query = "SELECT id, subcategory, category_id FROM form_CAMOS_subcategory ORDER BY subcategory";
$statement = sqlStatement($query);
while ($result = sqlFetchArray($statement)) {
  echo "array2[".$i."] = new Array('".$result['subcategory']."', '".$result['category_id']."', '".$result['id']."', new Array());\n";
  $i++;
}
$i=0;
$query = "SELECT id, item, content, subcategory_id FROM form_CAMOS_item ORDER BY item";
$statement = sqlStatement($query);
while ($result = sqlFetchArray($statement)) {
  echo "array3[".$i."] = new Array('".$result['item']."', '".str_replace(array("\r","\n","'","\""),array("\\r","\\n","\\'","\\\""),strip_tags($result['content'],"<b>,<i>"))."', '".$result['subcategory_id'].
    "','".$result['id']."');\n";
  $i++;
}
?>

function append_icd9() {
  var f2 = document.CAMOS;
  f2.textarea_content.value = f2.textarea_content.value + icd9_list;
}

function select_word(mode, mystring, myselect) { //take a string and select it in a select box if present
  if (preselect_off) return 0;
  for (var i=0;i<myselect.length;i++) {
    var match = '';
    if (mode == 'by name') {
      match = myselect.options[i].text;
    }
    else if (mode == 'by number') {
      match = myselect.options[i].value;
    } 
    else {return 0;}
    if (match == mystring) {
      myselect.selectedIndex = i;
    }
  }
  return 1;
}

function init() {
  var f2 = document.CAMOS;
  for (i1=0;i1<array1.length;i1++) {
    f2.select_category.options[f2.select_category.length] = new Option(array1[i1][0], array1[i1][1]);
  }
  if (select_word('<? echo $preselect_mode."', '".$preselect_category; ?>' ,f2.select_category)) {
    click_category();
  }
}

function click_category() {
  var f2 = document.CAMOS;
  var category_index = f2.select_category.selectedIndex;
  if ((category_index < 0) || (category_index > f2.select_category.length-1)) {return 0;}
  var sel = f2.select_category.options[f2.select_category.selectedIndex].value;
  for (var i1=0;i1<array1.length;i1++) {
    if (array1[i1][1] == sel) {
    f2.select_subcategory.length = 0;
    f2.select_item.length = 0;
    f2.textarea_content.value = '';
      for (var i2=0;i2<array2.length;i2++) {
        if (array1[i1][1] == array2[i2][1]) {
          f2.select_subcategory.options[f2.select_subcategory.length] = new Option(array2[i2][0], array2[i2][2]);
        }
      }
    }
  }
  if (select_word('<? echo $preselect_mode."', '".$preselect_subcategory; ?>' ,f2.select_subcategory)) {
    click_subcategory();
  }
}
function click_subcategory() {
  var f2 = document.CAMOS;
  var subcategory_index = f2.select_subcategory.selectedIndex;
  if ((subcategory_index < 0) || (subcategory_index > f2.select_subcategory.length-1)) {return 0;}
  var sel = f2.select_subcategory.options[f2.select_subcategory.selectedIndex].value;
  for (var i1=0;i1<array2.length;i1++) {
    if (array2[i1][2] == sel) {
    f2.select_item.length = 0;
    f2.textarea_content.value = '';
      for (var i2=0;i2<array3.length;i2++) {
        if (array2[i1][2] == array3[i2][2]) {
          f2.select_item.options[f2.select_item.length] = new Option(array3[i2][0], array3[i2][3]);
        }
      }
    }
  }
  if (select_word('<? echo $preselect_mode."', '".$preselect_item; ?>' ,f2.select_item)) {
    click_item();
    preselect_off = true;
  }
}
function click_item() {
  var f2 = document.CAMOS;
  var item_index = f2.select_item.selectedIndex;
  if ((item_index < 0) || (item_index > f2.select_item.length-1)) {return 0;}
  var sel = f2.select_item.options[item_index].value;
  for (var i1=0;i1<array3.length;i1++) {
    if (array3[i1][3] == sel) {
      f2.textarea_content.value= array3[i1][1];
    }
  }
}
function js_button(mode,selection) {
  if (mode == 'delete') {
    if (!confirm("Are you sure you want to delete this item from the database?")) {
      return;
    }
  }
  var f2 = document.CAMOS;
  var category_index = f2.select_category.selectedIndex;
  var category;
  if (category_index < 0) {
    if ((selection == 'change_subcategory') || (selection == 'change_item') ||
      (selection == 'change_content')) {
      alert ('You have not selected a category!');
      return;
    }
    category = -1;
  }
  else {
    category = f2.select_category.options[category_index].value;
  }
  var subcategory_index = f2.select_subcategory.selectedIndex;
  var subcategory;
  if (subcategory_index < 0) {
    if ((selection == 'change_item') || (selection == 'change_content')) {
      alert ('You have not selected a subcategory!');
      return;
    }
    subcategory = -1;
  }
  else {
    subcategory = f2.select_subcategory.options[subcategory_index].value;
  }
  var item_index = f2.select_item.selectedIndex;
  var item;
  if (item_index < 0) {
    if (selection == 'change_content') {
      alert ('You have not selected an item!');
      return;
    }
    item= -1;
  }
  else {
    item = f2.select_item.options[item_index].value;
  }
  if (mode == 'submit') {
    f2.category.value = f2.select_category.options[f2.select_category.selectedIndex].text;
    f2.subcategory.value = f2.select_subcategory.options[f2.select_subcategory.selectedIndex].text;
    f2.item.value = f2.select_item.options[f2.select_item.selectedIndex].text;
    if (selection == 'submit_selection') {
      f2.content.value = (f2.textarea_content.value).substring(f2.textarea_content.selectionStart, f2.textarea_content.selectionEnd);
    }
    else {f2.content.value = f2.textarea_content.value;}
    f2.action = '<?echo $rootdir;?>/forms/CAMOS/save.php?mode=new';
    f2.submit();
  }
  else {
    f2.hidden_mode.value = mode;
    f2.hidden_selection.value = selection;
    f2.hidden_category.value = category;
    f2.hidden_subcategory.value = subcategory;
    f2.hidden_item.value = item;
    f2.action = '<? print $GLOBALS['webroot'] ?>/interface/patient_file/encounter/load_form.php?formname=CAMOS';
    f2.target = 'Main';
    f2.submit();
  }
}

function selectItem () {
  f2 = document.CAMOS;
  f2.item.value=f2.select_item.options[f2.select_item.selectedIndex].text;
  f2.content.value = f2.textarea_content.value;
}

</script>
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0 onload="init()">
<form method=post action="<?echo $rootdir;?>/forms/CAMOS/save.php?mode=new" name="CAMOS">

<?  
if ($error != '') {
  echo "<h1> error: ".$error."</h1>\n"; 
}
?>
<table border=1>
<tr>
  <td>
    Category
  </td>
  <td>
    Subcategory
  </td>
  <td>
    Item
  </td>
  <td>
    Content 
  </td>
</tr>

<tr>
  <td>
    <select name=select_category size=<? echo $select_size ?> onchange="click_category()"></select><br>
    <input type=text name=change_category><br>
    <input type=button name=add1 value=add onClick="js_button('add','change_category')">
    <input type=button name=alter1 value=alter onClick="js_button('alter','change_category')">
    <input type=button name=del1 value=del onClick="js_button('delete','change_category')"><br>
  </td>
  <td>
    <select name=select_subcategory size=<? echo $select_size ?> onchange="click_subcategory()"></select><br>
    <input type=text name=change_subcategory><br>
    <input type=button name=add2 value=add onClick="js_button('add','change_subcategory')">
    <input type=button name=alter1 value=alter onClick="js_button('alter','change_subcategory')">
    <input type=button name=del2 value=del onClick="js_button('delete','change_subcategory')"><br>
  </td>
  <td>
    <select name=select_item size=<? echo $select_size ?> onchange="click_item()"></select><br>
    <input type=text name=change_item><br>
    <input type=button name=add3 value=add onClick="js_button('add','change_item')">
    <input type=button name=alter1 value=alter onClick="js_button('alter','change_item')">
    <input type=button name=del3 value=del onClick="js_button('delete','change_item')"><br>
  </td>
  <td>
    <textarea name=textarea_content cols=<? echo $textarea_cols ?> rows=<? echo $textarea_rows ?>></textarea><br>
    <input type=button name=add4 value=add onClick="js_button('add','change_content')">
    <input type=button name=icd9 value=icd9 onClick="append_icd9()">
  </td>
</tr>
</table>

<input type=hidden name=hidden_mode>
<input type=hidden name=hidden_selection>
<input type=hidden name=hidden_category>
<input type=hidden name=hidden_subcategory>
<input type=hidden name=hidden_item>

<input type=hidden name=category>
<input type=hidden name=subcategory>
<input type=hidden name=item>
<input type=hidden name=content>
<input type=button name='submit form' value='submit all content' onClick="js_button('submit','submit')">
<input type=button name='submit form' value='submit selected content' onClick="js_button('submit','submit_selection')">
<?
echo "<a href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/patient_encounter.php'>[do not save]</a>";
echo "<a href='".$GLOBALS['webroot'] . "/interface/forms/CAMOS/help.html' target='new'> | [help]</a>";
echo $previous_encounter_data;
?>


</form>
<?php
formFooter();
?>
