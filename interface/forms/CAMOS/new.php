<?php
include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("../../../library/sql.inc");
formHeader("Form: CAMOS");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
function myauth() {
  return 1;
}
?>


<?
$out_of_encounter = false;
if (($_SESSION['encounter'] == '') || ($_SESSION['pid'] == '')) {
  $out_of_encounter = true;
}
$select_size = 20;
$textarea_rows = 25;
$textarea_cols = 55;
$multibox_rows = 25;
$multibox_cols = 55;
$debug = '';
$error = '';
$previous_encounter_data = '';
if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
  $previous_encounter_data = '<hr><p>'.xl('Previous Encounter CAMOS entries').'</p><hr>';
  //get data from previous encounter to show at bottom of form for reference
  $tmp = sqlQuery("SELECT max(encounter) AS max FROM forms WHERE " .
    "form_name LIKE 'CAMOS%' AND encounter < '" . $_SESSION['encounter'] .
    "' AND pid = '" . $_SESSION['pid'] . "'");
  $last_encounter_id = $tmp['max'] ? $tmp['max'] : 0;
  $query = "SELECT t1.category, t1.subcategory, t1.item, t1.content " .
    "FROM form_CAMOS as t1 JOIN forms as t2 on (t1.id = t2.form_id) where " .
    "t2.encounter = '$last_encounter_id' and t1.pid = " . $_SESSION['pid'];
  $statement = sqlStatement($query);
  while ($result = sqlFetchArray($statement)) { 
    $previous_encounter_data .= $result['category']." | ".$result['subcategory'].
    " | ".$result['item']."<p><pre>".$result['content']."</pre></p><hr>";
  }
}

//end of get data from previous encounter
//variables for preselect section below (after handle database changes):
$preselect_category = '';
$preselect_subcategory = '';
$preselect_item= '';
$preselect_category_override = '';
$preselect_subcategory_override = '';
$preselect_item_override = '';

$quote_search = array("\r","\n");
$quote_replace = array("\\r","\\n");
$quote_search_content = array("\r","\n");
$quote_replace_content = array("\\r","\\n");
$category = str_replace($quote_search,$quote_replace,$_POST['change_category']); 
$subcategory = str_replace($quote_search,$quote_replace,$_POST['change_subcategory']); 
$item = str_replace($quote_search,$quote_replace,$_POST['change_item']); 
$content = str_replace($quote_search_content,$quote_replace_content,$_POST['textarea_content']); 
$category = fixquotes($category); 
$subcategory = fixquotes($subcategory); 
$item = fixquotes($item); 
$content = fixquotes($content); 

if ($_POST['hidden_category']) {$preselect_category = $_POST['hidden_category'];}
if ($_POST['hidden_subcategory']) {$preselect_subcategory = $_POST['hidden_subcategory'];}
if ($_POST['hidden_item']) {$preselect_item = $_POST['hidden_item'];}
//handle changes to database
if ($_POST['hidden_mode'] == 'add') {
  if ($_POST['hidden_selection'] == 'change_category') {
    $preselect_category_override = $_POST['change_category'];
    $query = "INSERT INTO form_CAMOS_category (category) values ('";
    $query .= $category."')"; 
    sqlInsert($query);
  }
  else if ($_POST['hidden_selection'] == 'change_subcategory') {
    $preselect_subcategory_override = $_POST['change_subcategory'];
    $category_id = $_POST['hidden_category']; 
    if ($category_id >= 0 ) {
      $query = "INSERT INTO form_CAMOS_subcategory (subcategory, category_id) values ('";
      $query .= $subcategory."', '".$category_id."')";
      sqlInsert($query);
    }
  }
  else if ($_POST['hidden_selection'] == 'change_item') {
    $preselect_item_override = $_POST['change_item'];
    $category_id = $_POST['hidden_category']; 
    $subcategory_id = $_POST['hidden_subcategory']; 
    if (($category_id >= 0 ) && ($subcategory_id >=0)) {
      $query = "INSERT INTO form_CAMOS_item (item, content, subcategory_id) values ('";
      $query .= $item."', '".$content."', '".$subcategory_id."')";
      sqlInsert($query);
    }
    
  }
  else if ($_POST['hidden_selection'] == 'change_content') {
    $item_id = $_POST['hidden_item'];
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
  if ($preselect_category == '' && !$out_of_encounter) {
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
?>

<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<script language="javascript" type="text/javascript">

var array1 = new Array();
var array2 = new Array();
var array3 = new Array();
var icd9_list = '';
var preselect_off = false;
var content_change_flag = false;
var lock_override_flag = false;
var switchbox_status = 'main';
var clone_mode = false;

<?
if (substr($_POST['hidden_mode'],0,5) == 'clone') {
  echo "clone_mode = true;\n";
}
?>

function addbox() {
  f2 = document.CAMOS;
  f3 = document.CAMOS.textarea_content.value;
  document.CAMOS.textarea_multibox.value =  
    document.CAMOS.textarea_multibox.value + "/*camos::" + 
    f2.select_category.options[f2.select_category.selectedIndex].text +
    "::" +
    f2.select_subcategory.options[f2.select_subcategory.selectedIndex].text +
    "::" +
    f2.select_item.options[f2.select_item.selectedIndex].text +
    "::" +
    f3 + "*/\n\n";
}
function switchbox() {
  var mainbox = document.getElementById('mainbox');
  var multibox = document.getElementById('multibox');
  if (switchbox_status == 'main') {
    switchbox_status = 'multi';
    mainbox.style.display = 'none';
    multibox.style.display = 'block';
    document.CAMOS.switch_box.value = 'hide multibox';
  }
  else {
    switchbox_status = 'main';
    multibox.style.display = 'none';
    mainbox.style.display = 'block';
    document.CAMOS.switch_box.value = 'show multibox';
  }
}
//deal with locking of content = prevent accidental overwrite

function trimString (str) {
  str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function isLocked() {
  f2 = document.CAMOS;
  if (lock_override_flag) {
    lock_override_flag = false;
    return false;
  }
  return /\/\*\s*lock\s*\:\:\s*\*\//.test(f2.textarea_content.value);
}
function lock_content() {
  f2 = document.CAMOS;
  if ((trimString(f2.textarea_content.value) != "") && (!isLocked())) {
    f2.textarea_content.value = f2.textarea_content.value + "\n\n" + "/*lock::*/";
    lock_override_flag = true;
    js_button('add','change_content');
  }
}
function allSelected() {
  var f2 = document.CAMOS;
  if ( (f2.select_category.selectedIndex < 0) || (f2.select_subcategory.selectedIndex < 0) || (f2.select_item.selectedIndex < 0) ) {
    return false; //one of the columns is not selected
  }
  else {
    return true; //all columns have a selection
  }
}

function content_focus() {
  if (content_change_flag == false) {
    if (!allSelected()) {
      alert("If you add text to the 'content' box without a selection in each column (category, subcategory, item), you will likely lose your work.")
    }
  }
  else {return;}
  content_change_flag = true;
}
function content_blur() {
  if (content_change_flag == true) {
    content_change_flag = false;
  }
}
<?
if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
  //ICD9
  $icd9_flag = false;
  $query = "SELECT code_text, code FROM billing WHERE encounter=".$_SESSION['encounter'].
    " AND pid=".$_SESSION['pid']." AND code_type like 'ICD9' AND activity=1";
  $statement = sqlStatement($query);
  if ($result = sqlFetchArray($statement)) {
    $icd9_flag = true;
    echo "icd9_list = \"\\n\\n\\\n";
    echo $result['code']." ".$result['code_text']."\\n\\\n";
  }
  while ($result = sqlFetchArray($statement)) {
    echo $result['code']." ".$result['code_text']."\\n\\\n";
  }
  if ($icd9_flag) {echo "\";\n";}
}

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
  echo "array3[".$i."] = new Array('".$result['item']."', '".fixquotes(str_replace($quote_search_content,$quote_replace_content,strip_tags($result['content'],"<b>,<i>")))."', '".$result['subcategory_id'].
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
<?
if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
  //cloning - similar process to preselect set to first time starting CAMOS 
  //as above
  $clone_category = '';
  $clone_subcategory = '';
  $clone_item = '';
  $clone_content = '';
  $clone_data1 = '';
  $clone_data2 = '';
  $clone_data_array = array();
  if (substr($_POST['hidden_mode'],0,5) == 'clone') {
    $clone_category = $_POST['category'];
    $query = "SELECT subcategory, item, content FROM form_CAMOS WHERE category like '".$clone_category."' and pid=".$_SESSION['pid']." order by id"; 
//    if ($_POST['hidden_mode'] == 'clone last visit') {
//      $query = "SELECT category, subcategory, item, content FROM form_CAMOS WHERE date(date) like (SELECT 
//    date(MAX(date)) FROM form_CAMOS where date(date) < date(now()) and pid=".$_SESSION['pid'].") and pid=".$_SESSION['pid']." order by id"; 
//    }

    if ($_POST['hidden_mode'] == 'clone last visit') {
      $tmp = sqlQuery("SELECT max(encounter) AS max FROM forms WHERE " .
        "form_name LIKE 'CAMOS%' AND encounter < '" . $_SESSION['encounter'] .
        "' AND pid = '" . $_SESSION['pid'] . "'");
      $last_encounter_id = $tmp['max'] ? $tmp['max'] : 0;
      $query = "SELECT category, subcategory, item, content FROM form_CAMOS " .
        "left join forms on (form_CAMOS.id = forms.form_id) where " . 
        "forms.encounter = '$last_encounter_id' and form_CAMOS.pid=" .
        $_SESSION['pid']." order by form_CAMOS.id"; 
    }
    $statement = sqlStatement($query);
    while ($result = sqlFetchArray($statement)) {
      if (preg_match('/^[\s\r\n]*$/',$result['content']) == 0) {
        if ($_POST['hidden_mode'] == 'clone last visit') {
          $clone_category = $result['category'];
        }
        $clone_subcategory = $result['subcategory'];
        $clone_item = $result['item'];
        $clone_content = $result['content'];
        $clone_data1 = "/* camos :: $clone_category :: $clone_subcategory :: $clone_item :: ";
        $clone_data2 = "$clone_content */";
        $clone_data3 = $clone_data1 . $clone_data2;
        if ($_POST['hidden_mode'] == 'clone last visit') {
          $clone_data1 = $clone_data3; //make key include whole entry so all 'last visit' data gets recorded and shown
        }
        $clone_data_array[$clone_data1] = $clone_data3;
    }
    if ($_POST['hidden_mode'] == 'clone last visit') {
//      $tmp = sqlQuery("SELECT max(encounter) as max FROM billing where encounter < " .
//        $_SESSION['encounter'] . " and pid= " . $_SESSION['pid']);
//      $last_encounter_id = $tmp['max'] ? $tmp['max'] : 0;
      $query = "SELECT code_type, code, code_text, modifier, units, fee FROM billing WHERE encounter = '$last_encounter_id' and pid=".$_SESSION['pid']." and activity=1 order by id";} 
      $statement = sqlStatement($query);
      while ($result = sqlFetchArray($statement)) {
        $clone_code_type = $result['code_type'];
        $clone_code = $result['code'];
        $clone_code_text = $result['code_text'];
        $clone_modifier = $result['modifier'];
        $clone_units = $result['units'];
        $clone_fee = $result['fee'];
        $clone_billing_data = "/* billing :: $clone_code_type :: $clone_code :: $clone_code_text :: $clone_modifier :: $clone_units :: $clone_fee */"; 
        $clone_data_array[$clone_billing_data] = $clone_billing_data;
      }
    }
  }
  //end preselect column items
} //end of clone stuff
?>
function init() {
  var f2 = document.CAMOS;
  if (clone_mode) {
    clone_mode = false;
<?
if (substr($_POST['hidden_mode'],0,5) == 'clone') {
  foreach($clone_data_array as $key => $val) {
  echo "f2.textarea_multibox.value += \"".fixquotes(str_replace($quote_search,$quote_replace,$val))."\\n\"\n";
  }
  echo "switchbox();\n";
}

?>
  }
  for (i1=0;i1<array1.length;i1++) {
    f2.select_category.options[f2.select_category.length] = new Option(array1[i1][0], array1[i1][1]);
  }
<?
  $temp_preselect_mode = $preselect_mode;
  if ($preselect_category_override != '') {
    $temp_preselect_mode = "by name";
    $preselect_category = $preselect_category_override;
  }
?>
  if (select_word('<? echo $temp_preselect_mode."', '".$preselect_category; ?>' ,f2.select_category)) {
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
<?
  $temp_preselect_mode = $preselect_mode;
  if ($preselect_subcategory_override != '') {
    $temp_preselect_mode = "by name";
    $preselect_subcategory = $preselect_subcategory_override;
  }
?>
  if (select_word('<? echo $temp_preselect_mode."', '".$preselect_subcategory; ?>' ,f2.select_subcategory)) {
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
<?
  $temp_preselect_mode = $preselect_mode;
  if ($preselect_item_override != '') {
    $temp_preselect_mode = "by name";
    $preselect_item = $preselect_item_override;
  }
?>
  if (select_word('<? echo $temp_preselect_mode."', '".$preselect_item; ?>' ,f2.select_item)) {
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
      f2.textarea_content.value= array3[i1][1].replace(/\\/g,'');
    }
  }
}

function selectContains(myselect, str) {
  for (var i=0;i<myselect.length;i++) {
    if (myselect.options[i].text == trimString(str)) {return true;}
  }
}

function js_button(mode,selection) {
  var f2 = document.CAMOS;
//check lock next 
if ( (mode == 'add') && (selection == 'change_content') && (isLocked()) ) {
  alert("You have attempted to alter content which is locked.\nRemove the lock if you want to do this.\nTo unlock, remove the line, '/*lock::*/'");
  return;
}
//end check lock

//check for blank or duplicate submissions
if ( (mode == 'add') || (mode == 'alter') ) {
  if (selection == 'change_category') {
    if (trimString(f2.change_category.value) == "") {
      alert("You cannot add a blank value for a category!"); 
      return;
    }
    if (selectContains(f2.select_category, trimString(f2.change_category.value))) {
      alert("There is already a category named "+f2.change_category.value+".");
      return;
    }
  }
  if (selection == 'change_subcategory') {
    if (trimString(f2.change_subcategory.value) == "") {
      alert("You cannot add a blank value for a subcategory!"); 
      return;
    }
    if (selectContains(f2.select_subcategory, trimString(f2.change_subcategory.value))) {
      alert("There is already a subcategory named "+f2.change_subcategory.value+".");
      return;
    }
  }
  if (selection == 'change_item') {
    if (trimString(f2.change_item.value) == "") {
      alert("You cannot add a blank value for an item!"); 
      return;
    }
    if (selectContains(f2.select_item, trimString(f2.change_item.value))) {
      alert("There is already an item named "+f2.change_item.value+".");
      return;
    }
  }
}
//end of check for blank or duplicate submissions

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
//deal with clone buttons 
  if (mode.substr(0,5) == 'clone') {
    f2.category.value = f2.select_category.options[f2.select_category.selectedIndex].text;
    f2.hidden_mode.value = mode;
    f2.hidden_selection.value = selection;
    f2.hidden_category.value = category;
    f2.hidden_subcategory.value = subcategory;
    f2.hidden_item.value = item;
    f2.action = '<? print $GLOBALS['webroot'] ?>/interface/patient_file/encounter/load_form.php?formname=CAMOS';
    f2.target = '_self';
    f2.submit();
  }
  if (mode == 'submit') {
    f2.category.value = f2.select_category.options[f2.select_category.selectedIndex].text;
    f2.subcategory.value = f2.select_subcategory.options[f2.select_subcategory.selectedIndex].text;
    f2.item.value = f2.select_item.options[f2.select_item.selectedIndex].text;
    if (selection == 'submit_selection') {
      f2.content.value = (f2.textarea_content.value).substring(f2.textarea_content.selectionStart, f2.textarea_content.selectionEnd);
    }
    else if (selection == 'multibox') {f2.content.value = f2.textarea_multibox.value;}
    else {f2.content.value = f2.textarea_content.value;}
    f2.action = '<?echo $rootdir;?>/forms/CAMOS/save.php?mode=new';
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
<input type=button name='switch_box' value='show multibox' onClick="switchbox()">
<input type=button name='add_multibox' value='add to multibox' onClick="addbox()">
<input type=button name='submit_multibox' value='submit multibox' onClick="js_button('submit','multibox')">
<input type=button name=clone value=clone onClick="js_button('clone', 'clone')">
<input type=button name=clone_visit value='clone last visit' onClick="js_button('clone last visit', 'clone last visit')">
<?  
echo "<a href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl' onclick='top.restoreSession()'>[".xl('do not save')."]</a>";
?>
<div id=mainbox style="display:block">
<?  
if ($error != '') {
  echo "<h1> error: ".$error."</h1>\n"; 
}
?>
<table border=1>
<tr>
  <td>
    <?php xl('Category',e)?>
  </td>
  <td>
    <?php xl('Subcategory',e)?>
  </td>
  <td>
    <?php xl('Item',e)?>
  </td>
  <td>
    <?php xl('Content',e)?>
  </td>
</tr>

<tr>
  <td>
    <select name=select_category size=<? echo $select_size ?> onchange="click_category()"></select><br>
<?
if (myAuth() == 1) {//root user only can see administration option 
?>
    <input type=text name=change_category><br>
    <input type=button name=add1 value=add onClick="js_button('add','change_category')">
    <input type=button name=alter1 value=alter onClick="js_button('alter','change_category')">
    <input type=button name=del1 value=del onClick="js_button('delete','change_category')"><br>
<?
}
?>
  </td>
  <td>
    <select name=select_subcategory size=<? echo $select_size ?> onchange="click_subcategory()"></select><br>
<?
if (myAuth() == 1) {//root user only can see administration option 
?>
    <input type=text name=change_subcategory><br>
    <input type=button name=add2 value=add onClick="js_button('add','change_subcategory')">
    <input type=button name=alter1 value=alter onClick="js_button('alter','change_subcategory')">
    <input type=button name=del2 value=del onClick="js_button('delete','change_subcategory')"><br>
<?
}
?>
  </td>
  <td>
    <select name=select_item size=<? echo $select_size ?> onchange="click_item()"></select><br>
<?
if (myAuth() == 1) {//root user only can see administration option 
?>
    <input type=text name=change_item><br>
    <input type=button name=add3 value=add onClick="js_button('add','change_item')">
    <input type=button name=alter1 value=alter onClick="js_button('alter','change_item')">
    <input type=button name=del3 value=del onClick="js_button('delete','change_item')"><br>
<?
}
?>
  </td>
  <td>
    <textarea name=textarea_content cols=<? echo $textarea_cols ?> rows=<? echo $textarea_rows ?> onFocus="content_focus()" onBlur="content_blur()"></textarea><br>
<?
if (myAuth() == 1) {//root user only can see administration option 
?>
    <input type=button name=add4 value=add onClick="js_button('add','change_content')">
    <input type=button name=lock value=lock onClick="lock_content()">
<?
if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
?>
    <input type=button name=icd9 value=icd9 onClick="append_icd9()">
<?
}
?>
<?
}
?>
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
<?
if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
?>
<input type=button name='submit form' value='submit all content' onClick="js_button('submit','submit')">
<input type=button name='submit form' value='submit selected content' onClick="js_button('submit','submit_selection')">
<?
}
?>
<?
if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
  echo "<a href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl' onclick='top.restoreSession()'>[".xl('do not save')."]</a>";
  echo "<a href='".$GLOBALS['webroot'] . "/interface/forms/CAMOS/help.html' target='new'> | [".xl('help')."]</a>";
//  echo $previous_encounter_data; //probably don't need anymore now that we have clone last visit
}
?>
</div>
<div id=multibox style="display:none">
<textarea name=textarea_multibox cols=<? echo $multibox_cols ?> rows=<? echo $multibox_rows ?> onFocus="content_focus()" onBlur="content_blur()"></textarea>
</div>
</form>
<?php
formFooter();

//PHP FUNCTIONS

function fixquotes ($string) { 
  $string =  preg_replace('/([\\\])*\'/', "\\\'", $string);
  $string =  preg_replace('/([\\\])*\"/', "\\\"", $string);
  return $string;
}
?>
