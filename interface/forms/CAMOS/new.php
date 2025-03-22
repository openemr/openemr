<?php

/**
 * CAMOS new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("../../../library/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$out_of_encounter = false;
if ((($_SESSION['encounter'] == '') || ($_SESSION['pid'] == '')) || ($_GET['mode'] == 'external')) {
    $out_of_encounter = true;
}

//  formHeader("Form: CAMOS");
function myauth()
{
    return 1;
}
?>


<?php

$break = "/* ---------------------------------- */"; //break between clone items
$delete_subdata = true; //true means allowing the deletion of subdata. If you delete a category, all subcategories and items go too.
$limit = 100;
$select_size = 20;
$textarea_rows = 20;
$textarea_cols = 80;
$debug = '';
$error = '';

$preselect_category = '';
$preselect_subcategory = '';
$preselect_item = '';
$preselect_category_override = '';
$preselect_subcategory_override = '';
$preselect_item_override = '';

$quote_search = array("\r","\n");
$quote_replace = array("\\r","\\n");
$quote_search_content = array("\r","\n");
$quote_replace_content = array("\\r","\\n");
$category = str_replace($quote_search, $quote_replace, $_POST['change_category'] ?? '');
$subcategory = str_replace($quote_search, $quote_replace, $_POST['change_subcategory'] ?? '');
$item = str_replace($quote_search, $quote_replace, $_POST['change_item'] ?? '');
$content = str_replace($quote_search_content, $quote_replace_content, $_POST['textarea_content'] ?? '');
if ($_POST['hidden_category']) {
    $preselect_category = $_POST['hidden_category'];
}

if ($_POST['hidden_subcategory']) {
    $preselect_subcategory = $_POST['hidden_subcategory'];
}

if ($_POST['hidden_item']) {
    $preselect_item = $_POST['hidden_item'];
}

//handle changes to database
if (substr($_POST['hidden_mode'] ?? '', 0, 3) == 'add') {
    if ($_POST['hidden_selection'] == 'change_category') {
        $preselect_category_override = $_POST['change_category'];
        $query = "INSERT INTO " . mitigateSqlTableUpperCase("form_CAMOS_category") . " (user, category) values (?, ?)";
        sqlStatement($query, array($_SESSION['authUser'], $category));
    } elseif ($_POST['hidden_selection'] == 'change_subcategory') {
        $preselect_subcategory_override = $_POST['change_subcategory'];
        $category_id = $_POST['hidden_category'];
        if ($category_id >= 0) {
            $query = "INSERT INTO " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " (user, subcategory, category_id) values (?, ?, ?)";
            sqlStatement($query, array($_SESSION['authUser'], $subcategory, $category_id));
        }
    } elseif ($_POST['hidden_selection'] == 'change_item') {
        $preselect_item_override = $_POST['change_item'];
        $category_id = $_POST['hidden_category'];
        $subcategory_id = $_POST['hidden_subcategory'];
        if (($category_id >= 0 ) && ($subcategory_id >= 0)) {
            $query = "INSERT INTO " . mitigateSqlTableUpperCase("form_CAMOS_item") . " (user, item, content, subcategory_id) values (?, ?, ?, ?)";
            sqlStatement($query, array($_SESSION['authUser'], $item, $content, $subcategory_id));
        }
    } elseif ($_POST['hidden_selection'] == 'change_content') {
        $item_id = $_POST['hidden_item'];
        if ($item_id >= 0) {
            if ($_POST['hidden_mode'] == 'add to') {
                $tmp = sqlQuery("SELECT content from " . mitigateSqlTableUpperCase("form_CAMOS_item") . " where id = ?", array($item_id));
                if (isset($tmp)) {
                    $content .= "\n" . $tmp['content'];
                }
            }

            $query = "UPDATE " . mitigateSqlTableUpperCase("form_CAMOS_item") . " set content = ? where id = ?";
            sqlStatement($query, array($content, $item_id));
        }
    }
} elseif ($_POST['hidden_mode'] == 'delete') {
    if ($delete_subdata) {
        if ($_POST['hidden_selection'] == 'change_category') {
            $to_delete_id = $_POST['hidden_category'];
            $statement1 = sqlStatement("select id from " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " where category_id = ?", array($to_delete_id));
            while ($result1 = sqlFetchArray($statement1)) {
                $query = "DELETE FROM " . mitigateSqlTableUpperCase("form_CAMOS_item") . " WHERE subcategory_id = ?";
                sqlStatement($query, array($result1['id']));
            }

            $query = "DELETE FROM " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " WHERE category_id = ?";
            sqlStatement($query, array($to_delete_id));
            $query = "DELETE FROM " . mitigateSqlTableUpperCase("form_CAMOS_category") . " WHERE id = ?";
            sqlStatement($query, array($to_delete_id));
        } elseif ($_POST['hidden_selection'] == 'change_subcategory') {
            $to_delete_id = $_POST['hidden_subcategory'];
            $query = "DELETE FROM " . mitigateSqlTableUpperCase("form_CAMOS_item") . " WHERE subcategory_id = ?";
            sqlStatement($query, array($to_delete_id));
            $query = "DELETE FROM " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " WHERE id = ?";
            sqlStatement($query, array($to_delete_id));
        } elseif ($_POST['hidden_selection'] == 'change_item') {
            if ((isset($_POST['select_item'])) && (count($_POST['select_item']) > 1)) {
                foreach ($_POST['select_item'] as $v) {
                    $to_delete_id = $v;
                    $query = "DELETE FROM " . mitigateSqlTableUpperCase("form_CAMOS_item") . " WHERE id = ?";
                    sqlStatement($query, array($to_delete_id));
                }
            } else {
                $to_delete_id = $_POST['hidden_item'];
                $query = "DELETE FROM " . mitigateSqlTableUpperCase("form_CAMOS_item") . " WHERE id = ?";
                sqlStatement($query, array($to_delete_id));
            }
        }
    } else {
        if ($_POST['hidden_selection'] == 'change_category') {
            $to_delete_id = $_POST['hidden_category'];
            $to_delete_from_table = 'form_CAMOS_category';
            $to_delete_from_subtable = 'form_CAMOS_subcategory';
            $to_delete_from_subsubtable = 'form_CAMOS_item';
            $tablename = 'category';
            $subtablename = 'subcategory';
            $subsubtablename = 'item';
        } elseif ($_POST['hidden_selection'] == 'change_subcategory') {
            $to_delete_id = $_POST['hidden_subcategory'];
            $to_delete_from_table = 'form_CAMOS_subcategory';
            $to_delete_from_subtable = 'form_CAMOS_item';
            $tablename = 'subcategory';
            $subtablename = 'item';
        } elseif ($_POST['hidden_selection'] == 'change_item') {
            $to_delete_id = $_POST['hidden_item'];
            $to_delete_from_table = 'form_CAMOS_item';
            $to_delete_from_subtable = '';
            $tablename = 'item';
            $subtablename = '';
        }

        if ($subtablename == '') {
            $query = "DELETE FROM " . escape_table_name($to_delete_from_table) . " WHERE id like ?";
            sqlStatement($query, array($to_delete_id));
        } else {
            $query = "SELECT count(id) FROM " . escape_table_name($to_delete_from_subtable) . " WHERE " . escape_sql_column_name($tablename . '_id', [$to_delete_from_subtable]) . " like ?";
            $statement = sqlStatement($query, array($to_delete_id));
            if ($result = sqlFetchArray($statement)) {
                if ($result['count(id)'] == 0) {
                    $query = "DELETE FROM " . escape_table_name($to_delete_from_table) . " WHERE id like ?";
                    sqlStatement($query, array($to_delete_id));
                } else {
                    $error = $subtablename . " not empty!";
                }
            }
        }
    }
} elseif ($_POST['hidden_mode'] == 'alter') {
    $newval = $_POST[$_POST['hidden_selection']];
    if ($_POST['hidden_selection'] == 'change_category') {
        $to_alter_id = $_POST['hidden_category'];
        $to_alter_table = 'form_CAMOS_category';
        $to_alter_column = 'category';
    } elseif ($_POST['hidden_selection'] == 'change_subcategory') {
        $to_alter_id = $_POST['hidden_subcategory'];
        $to_alter_table = 'form_CAMOS_subcategory';
        $to_alter_column = 'subcategory';
    } elseif ($_POST['hidden_selection'] == 'change_item') {
        $to_alter_id = $_POST['hidden_item'];
        $to_alter_table = 'form_CAMOS_item';
        $to_alter_column = 'item';
    }

    sqlStatement("UPDATE " . escape_table_name($to_alter_table) . " set " . escape_sql_column_name($to_alter_column, [$to_alter_table]) . " = ? where id =  ?", array($newval, $to_alter_id));
}

  //preselect column items
  //either a database change has been made, so the user should be made to feel that they never left the same CAMOS screen
  //or, CAMOS has been started freshly, therefore the last entry of the current patient should be selected.
  $preselect_mode = '';
if ($preselect_category == '' && !$out_of_encounter) {
    $preselect_mode = 'by name';
    //at this point, if this variable has not been set, CAMOS must have been start over
    //so let's get the most recent values from form_CAMOS for this patient's pid
    $tmp = sqlQuery("SELECT max(id) AS max FROM " . mitigateSqlTableUpperCase("form_CAMOS") . " WHERE " .
    "pid = ?", array($_SESSION['pid']));
    $maxid = $tmp['max'] ? $tmp['max'] : 0;

    $query = "SELECT category, subcategory, item FROM " . mitigateSqlTableUpperCase("form_CAMOS") . " WHERE id = ?";
    $statement = sqlStatement($query, array($maxid));
    if ($result = sqlFetchArray($statement)) {
        $preselect_category = $result['category'];
        $preselect_subcategory = $result['subcategory'];
        $preselect_item = $result['item'];
    } else {
        $preselect_mode = '';
    }
} else {
    $preselect_mode = 'by number';
}
?>

<html><head>
<?php Header::setupHeader(); ?>

<script>

var array1 = new Array();
var array2 = new Array();
var array3 = new Array();
var buffer = new Array();
var icd10_list = '';
var preselect_off = false;
var content_change_flag = false;
var lock_override_flag = false;
var columns_status = 'show';
var hs_status = false;
var vs_status = false;
var hide_tc02_status = false;
var clone_mode = false;

var crop_buffer = '';
var special_select_start = 0;
var special_select_end = 0;

<?php

if (substr($_POST['hidden_mode'] ?? '', 0, 5) == 'clone') {
    echo "clone_mode = true;\n";
}
?>

function clear_box(obj) {
  var hold = obj.value;
  obj.value = buffer[obj] ? buffer[obj] : '';
  buffer[obj] = hold;
}

function showit() {
  var log = document.getElementById('log');
  var content = document.testform.testarea;
  specialSelect(content,'/*','*/');
}

function specialSelect(t_area, delim_1, delim_2) {
  if (crop_buffer != '') {
    t_area.value = crop_buffer;
    crop_buffer = '';
    return;
  }
  var cv = t_area.value;
  var start =  t_area.selectionStart;
  var end = t_area.selectionEnd;
  var newstart = cv.lastIndexOf(delim_1, start);
  var newend = cv.indexOf(delim_2, end);
  if ((newstart == -1) || (newend == -1)) {return;}
  if ((newstart == special_select_start) && (newend == special_select_end)) {
    cropToSelect(t_area, delim_2);
  }
  if (newstart >= 0 && newend >= 0) {
    t_area.selectionStart = newstart;
    t_area.selectionEnd = newend+delim_2.length;
  }
  special_select_start = newstart;
  special_select_end = newend;
}

function cropToSelect(t_area, delim_2) {
  var cv = t_area.value;
  crop_buffer = cv;
  var start = special_select_start;
  var end = special_select_end+delim_2.length;
  var length = end-start;
  t_area.value = cv.substr(start,length);
 }

function hide_columns() {
  var column01 = document.getElementById('id_category_column');
  var column02 = document.getElementById('id_subcategory_column');
  var column03 = document.getElementById('id_item_column');
  var columnheader01 = document.getElementById('id_category_column_header');
  var columnheader02 = document.getElementById('id_subcategory_column_header');
  var columnheader03 = document.getElementById('id_item_column_header');

  if (columns_status == 'show') {
    columns_status = 'hide';
    column01.style.display = 'none';
    column02.style.display = 'none';
    column03.style.display = 'none';
    columnheader01.style.display = 'none';
    columnheader02.style.display = 'none';
    columnheader03.style.display = 'none';
  }
  else {
    columns_status = 'show';
    column01.style.display = 'inline';
    column02.style.display = 'inline';
    column03.style.display = 'inline';
    columnheader01.style.display = 'inline';
    columnheader02.style.display = 'inline';
    columnheader03.style.display = 'inline';
  }
  resize_content();
}
function resize_content() {
  f2 = document.CAMOS;
  f4 = f2.textarea_content
  if (f4.cols == <?php echo js_escape($textarea_cols); ?>) {
    f4.cols = <?php echo js_escape($textarea_cols); ?>*2;
    f4.rows = <?php echo js_escape($textarea_rows); ?>;
  } else {
    f4.cols = <?php echo js_escape($textarea_cols); ?>;
    f4.rows = <?php echo js_escape($textarea_rows); ?>;
  }
}
//function hs_button() {
//  f2 = document.CAMOS;
//  if (hs_status) {
//    hide_columns();
//    f2.textarea_content.cols /= 3;
//    f2.textarea_content02.cols /= 3;
//    hs_status = false;
//  } else {
//    hide_columns();
////    f2.textarea_content.cols *= 3;
//    f2.textarea_content02.cols *= 3;
//    hs_status = true;
//  }
//}



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
  if ( (f2.select_category.selectedIndex < 0) || (f2.select_subcategory.selectedIndex < 0) || (f2["select_item[]"].selectedIndex < 0) ) {
    return false; //one of the columns is not selected
  }
  else {
    return true; //all columns have a selection
  }
}

function content_focus() {
  if (content_change_flag == false) {
    if (!allSelected()) {
//      alert("If you add text to the 'content' box without a selection in each column (category, subcategory, item), you will likely lose your work.")
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
<?php

if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
  //ICD10
    $code_list = '';
    $query = "SELECT code_text, code FROM billing WHERE encounter=? AND pid=? AND code_type like 'ICD10' AND activity=1";
    $statement = sqlStatement($query, array($_SESSION['encounter'], $_SESSION['pid']));
    if ($result = sqlFetchArray($statement)) {
        $code_list = "\n\n" . trim(preg_replace('/\r\n|\r|\n/', '', text($result['code'] . " " . $result['code_text'])));
    }

    while ($result = sqlFetchArray($statement)) {
        $code_list .= "\n\n" . trim(preg_replace('/\r\n|\r|\n/', '', text($result['code'] . " " . $result['code_text'])));
    }

    $code_list = "icd10_list=" . js_escape($code_list . "\n") . ";\n";
    if (!empty($code_list)) {
        echo $code_list;
    }
}

$query = "SELECT id, category FROM " . mitigateSqlTableUpperCase("form_CAMOS_category") . " ORDER BY category";
$statement = sqlStatement($query);
$i = 0;
while ($result = sqlFetchArray($statement)) {
    echo "array1[" . attr(addslashes($i)) . "] = new Array(" . js_escape($result['category']) . ", " . js_escape($result['id']) . ", new Array());\n";
    $i++;
}

$i = 0;
$query = "SELECT id, subcategory, category_id FROM " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " ORDER BY subcategory";
$statement = sqlStatement($query);
while ($result = sqlFetchArray($statement)) {
    echo "array2[" . attr(addslashes($i)) . "] = new Array(" . js_escape($result['subcategory']) . ", " . js_escape($result['category_id']) . ", " . js_escape($result['id']) . ", new Array());\n";
    $i++;
}

$i = 0;
$query = "SELECT id, item, content, subcategory_id FROM " . mitigateSqlTableUpperCase("form_CAMOS_item") . " ORDER BY item";
$statement = sqlStatement($query);
while ($result = sqlFetchArray($statement)) {
    echo "array3[" . attr(addslashes($i)) . "] = new Array(" . js_escape($result['item']) . ", " . js_escape_protected(strip_tags($result['content'], "<b>,<i>"), '\r\n') . ", " . js_escape($result['subcategory_id']) .
    "," . js_escape($result['id']) . ");\n";
    $i++;
}
?>

function append_icd10() {
  var f2 = document.CAMOS;
  f2.textarea_content.value = f2.textarea_content.value + icd10_list;
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
<?php

if (1) { //we are hiding the clone buttons and still need 'search others' so this is not to be removed if out of encounter anymore.
//if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
  //cloning - similar process to preselect set to first time starting CAMOS
  //as above
    $clone_category = '';
    $clone_subcategory = '';
    $clone_item = '';
    $clone_content = '';
    $clone_data1 = '';
    $clone_data2 = '';
    $clone_data_array = array();
    if (substr($_POST['hidden_mode'] ?? '', 0, 5) == 'clone') {
        $clone_category = $_POST['category'] ? $_POST['category'] : '';
        $clone_category_term = '';
        if ($clone_category != '') {
            $clone_category_term = " where category like '" . add_escape_custom($clone_category) . "'";
        }

        $clone_subcategory = $_POST['subcategory'] ? $_POST['subcategory'] : '';
        $clone_subcategory_term = '';
        if ($clone_subcategory != '') {
            $clone_subcategory_term = " and subcategory like '" . add_escape_custom($_POST['subcategory']) . "'";
        }

        $clone_item = $_POST['item'] ? $_POST['item'] : '';
        $clone_item_term = '';
        if ($clone_item != '') {
            $clone_item_term = " and item like '" . add_escape_custom($_POST['item']) . "'";
        }

        $clone_search = trim($_POST['clone_others_search']);

        $name_data_flag = false; //flag to see if we are going to use patient names in search result of clone others.
        $show_phone_flag = false; //if we do show patient names, flag to see if we show phone numbers too
        $pid_clause = ''; //if name search, will return a limited list of names to search for.
        if (strpos($clone_search, "::") !== false) {
            $name_data_flag = true;
            $show_phone_flag = true;
            $split = preg_split('/\s*::\s*/', $clone_search);
            $clone_search = $split[1];
            $pid_clause = searchName($split[0]);
        } elseif (strpos($clone_search, ":") !== false) {
            $name_data_flag = true;
            $split = preg_split('/\s*:\s*/', $clone_search);
            $clone_search = $split[1];
            $pid_clause = searchName($split[0]);
        }

        $clone_search_term = '';
        if (!empty($clone_search)) {
            $clone_search =  preg_replace('/\s+/', '%', $clone_search);
            if (substr($clone_search, 0, 1) == "`") {
                $clone_subcategory_term = '';
                $clone_item_term = '';
                $clone_search = substr($clone_search, 1);
            }

            $clone_search_term = " and content like '%" . add_escape_custom($clone_search) . "%'";
        }

        if (substr($_POST['hidden_mode'] ?? '', 0, 12) == 'clone others') {
            if (preg_match('/^(export)(.*)/', $clone_search, $matches)) {
                $query1 = "select id, category from " . mitigateSqlTableUpperCase("form_CAMOS_category");
                $statement1 = sqlStatement($query1);
                while ($result1 = sqlFetchArray($statement1)) {
                    $tmp = $result1['category'];
                    $tmp = "/*import::category::$tmp*/" . "\n";
                    $clone_data_array[$tmp] = $tmp;
                    $query2 = "select id,subcategory from " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " where category_id=?";
                    $statement2 = sqlStatement($query2, array($result1['id']));
                    while ($result2 = sqlFetchArray($statement2)) {
                        $tmp = $result2['subcategory'];
                        $tmp = "/*import::subcategory::$tmp*/" . "\n";
                        $clone_data_array[$tmp] = $tmp;
                        $query3 = "select item, content from " . mitigateSqlTableUpperCase("form_CAMOS_item") . " where subcategory_id=?";
                        $statement3 = sqlStatement($query3, array($result2['id']));
                        while ($result3 = sqlFetchArray($statement3)) {
                            $tmp = $result3['item'];
                            $tmp = "/*import::item::$tmp*/" . "\n";
                            $clone_data_array[$tmp] = $tmp;
                            $tmp = $result3['content'];
                            $tmp = "/*import::content::$tmp*/" . "\n";
                            $clone_data_array[$tmp] = $tmp;
                        }
                    }
                }

                $clone_data_array = array();
            } elseif (
                (preg_match('/^(billing)(.*)/', $clone_search, $matches)) ||
                (preg_match('/^(codes)(.*)/', $clone_search, $matches))
            ) {
                  $table = $matches[1];
                  $line = $matches[2];
                  $line = '%' . trim($line) . '%';
                  $search_term = preg_replace('/\s+/', '%', $line);
                  $query = "select code, code_type,code_text,modifier,units,fee from " . escape_table_name($table) . " where code_text like ? limit " . escape_limit($limit);
                  $statement = sqlStatement($query, array($search_term));
                while ($result = sqlFetchArray($statement)) {
                    $code_type = $result['code_type'];
                    if ($code_type == 1) {
                        $code_type = 'CPT4';
                    }

                    if ($code_type == 2) {
                        $code_type = 'ICD10';
                    }

                    if ($code_type == 3) {
                        $code_type = 'OTHER';
                    }

                    $code = $result['code'];
                    $code_text = $result['code_text'];
                    $modifier = $result['modifier'];
                    $units = $result['units'];
                    $fee = $result['fee'];
                    $tmp = "/*billing::$code_type::$code::$code_text::$modifier::$units::$fee*/";
                    $clone_data_array[$tmp] = $tmp;
                }
            } else {
                //$clone_data_array['others'] = '/*'.$clone_category.'::'.$clone_subcategory.'::'.
                //  $clone_item.'*/';
                //See the two lines commented out just below:
                //I am trying out searching all content regardless of category, subcategory, item...
                //because of this, we have to limit results more.  There may be a few lines
                //above that should be deleted if this becomes the normal way of doing these searches.
                //Consider making the two queries below by encounter instead of camos id.
                //This may be a little tricky.
                if ($_POST['hidden_mode'] == 'clone others selected') { //clone from search box
                    $query = "select id, category, subcategory, item, content from " . mitigateSqlTableUpperCase("form_CAMOS") . " " .
                        $clone_category_term . $clone_subcategory_term . $clone_item_term .
                    $clone_search_term . $pid_clause . " order by id desc limit " . escape_limit($limit);
                } else {
                    $query = "select id, category, subcategory, item, content from " . mitigateSqlTableUpperCase("form_CAMOS") . " " .
                    " where " .
                    //"category like '%$clone_search%' or" .
                  //" subcategory like '%$clone_search%' or" .
                  //" item like '%$clone_search%' or" .
                    " content like '%" . add_escape_custom($clone_search) . "%'" . $pid_clause . " order by id desc limit " . escape_limit($limit);
                }

                  $statement = sqlStatement($query);
                while ($result = sqlFetchArray($statement)) {
                    $tmp = '/*camos::' . $result['category'] . '::' . $result['subcategory'] .
                    '::' . $result['item'] . '::' . $result['content'] . '*/';
                    if ($name_data_flag === true) {
                            $tmp = getMyPatientData($result['id'], $show_phone_flag) . "\n$break\n" . $tmp;
                    }

                    $key_tmp = preg_replace('/\W+/', '', $tmp);
                    $key_tmp = preg_replace('/\W+/', '', $tmp);
                    $clone_data_array[$key_tmp] = $tmp;
                }
            }
        } else {//end of clone others
            if ($_POST['hidden_mode'] == 'clone last visit') {
                //go back $stepback # of encounters...
            //This has been changed to clone last visit based on actual last encounter rather than as it was
            //only looking at most recent BILLED encounters.  To go back to billed encounters, change the following
            //two queries to the 'billing' table rather than form_encounter and make sure to add in 'and activity=1'
            //OK, now I have tried tracking last encounter from billing, then form_encounter.  Now, we are going to
            //try from forms where form_name like 'CAMOS%' so we will not bother with encounters that have no CAMOS entries...
                $stepback = $_POST['stepback'] ? $_POST['stepback'] : 1;
                $tmp = sqlQuery("SELECT max(encounter) as max FROM forms where encounter < ?" .
                    " and form_name like 'CAMOS%' and pid= ?", array($_SESSION['encounter'], $_SESSION['pid']));
                $last_encounter_id = $tmp['max'] ? $tmp['max'] : 0;
                for ($i = 0; $i < $stepback - 1; $i++) {
                        $tmp = sqlQuery("SELECT max(encounter) as max FROM forms where encounter < ?" .
                            " and form_name like 'CAMOS%' and pid= ?", array($last_encounter_id, $_SESSION['pid']));
                        $last_encounter_id = $tmp['max'] ? $tmp['max'] : 0;
                }

                $query = "SELECT category, subcategory, item, content FROM " . mitigateSqlTableUpperCase("form_CAMOS") . " " .
                "join forms on (" . mitigateSqlTableUpperCase("form_CAMOS") . ".id = forms.form_id) where " .
                "forms.encounter = ? and " . mitigateSqlTableUpperCase("form_CAMOS") . ".pid=? " .
                " order by " . mitigateSqlTableUpperCase("form_CAMOS") . ".id";
                $statement = sqlStatement($query, array($last_encounter_id, $_SESSION['pid']));
            } else {
                $query = "SELECT date(date) as date, subcategory, item, content FROM " . mitigateSqlTableUpperCase("form_CAMOS") . " WHERE category like ? " .
                    " and pid=? order by id desc";
                $statement = sqlStatement($query, array($clone_category, $_SESSION['pid']));
            }

            while ($result = sqlFetchArray($statement)) {
                if (preg_match('/^[\s\r\n]*$/', $result['content']) == 0) {
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

                    if (!$clone_data_array[$clone_data1]) { //if does not exist, don't overwrite.
                          $clone_data_array[$clone_data1] = "";
                        if ($_POST['hidden_mode'] == 'clone') {
                            $clone_data_array[$clone_data1] = "/* ------  " . $result['date'] . "  --------- */\n"; //break between clone items
                        }

                          $clone_data_array[$clone_data1] .= $clone_data3;
                    }
                }
            }

            if ($_POST['hidden_mode'] == 'clone last visit') {
                $query = "SELECT t1.* FROM form_vitals as t1 join forms as t2 on (t1.id = t2.form_id) WHERE t2.encounter = ? and t1.pid=? and t2.form_name like 'Vitals'";
                $statement = sqlStatement($query, array($last_encounter_id, $_SESSION['pid']));
                if ($result = sqlFetchArray($statement)) {
                    $weight = $result['weight'];
                    $height = $result['height'];
                    $bps = $result['bps'];
                    $bpd = $result['bpd'];
                    $pulse = $result['pulse'];
                    $temperature = $result['temperature'];
          //              $clone_vitals = "/* vitals_key:: weight :: height :: systolic :: diastolic :: pulse :: temperature */\n";
                    $clone_vitals = "";
                    $clone_vitals .= "/* vitals\n :: $weight\n :: $height\n :: $bps\n :: $bpd\n :: $pulse\n :: $temperature\n */";
                    $clone_data_array[$clone_vitals] = $clone_vitals;
                }

                $query = "SELECT code_type, code, code_text, modifier, units, fee, justify FROM billing WHERE encounter = ? and pid=? and activity=1 order by id";
                $statement = sqlStatement($query, array($last_encounter_id, $_SESSION['pid']));
                while ($result = sqlFetchArray($statement)) {
                    $clone_code_type = $result['code_type'];
                    $clone_code = $result['code'];
                    $clone_code_text = $result['code_text'];
                    $clone_modifier = $result['modifier'];
                    $clone_units = $result['units'];
                    $clone_fee = $result['fee'];

                //added ability to grab justifications also - bm
                    $clone_justify = "";
                    $clone_justify_raw = $result['justify'];
                    $clone_justify_array = explode(":", $clone_justify_raw);
                    foreach ($clone_justify_array as $temp_justify) {
                        trim($temp_justify);
                        if ($temp_justify != "") {
                            $clone_justify .= ":: " . $temp_justify . " ";
                        }
                    }

                    $clone_billing_data = "/* billing :: $clone_code_type :: $clone_code :: $clone_code_text :: $clone_modifier :: $clone_units :: $clone_fee $clone_justify*/";
                    $clone_data_array[$clone_billing_data] = $clone_billing_data;
                }
            }
        } //end else (not clone others)
    }//end of clone stuff
  //end preselect column items
}
?>
function init() {
  var f2 = document.CAMOS;
  if (clone_mode) {
    clone_mode = false;
  }
  for (i1=0;i1<array1.length;i1++) {
    f2.select_category.options[f2.select_category.length] = new Option(array1[i1][0], array1[i1][1]);
  }
<?php

  $temp_preselect_mode = $preselect_mode;
if ($preselect_category_override != '') {
    $temp_preselect_mode = "by name";
    $preselect_category = $preselect_category_override;
}
?>
  if (select_word(<?php echo js_escape($temp_preselect_mode) . ", " . js_escape($preselect_category); ?> ,f2.select_category)) {
    click_category();
  }
<?php

if (substr($_POST['hidden_mode'] ?? '', 0, 5) == 'clone') {
    echo "f2.textarea_content.value = '';\n";
//  echo "f2.textarea_content.value += '/* count = ".count($clone_data_array)."*/\\n$break\\n';";
    echo "f2.textarea_content.value += '/* count = " . count($clone_data_array) . "*/\\n$break\\n';";
    foreach ($clone_data_array as $key => $val) {
        echo "f2.textarea_content.value = f2.textarea_content.value + " . js_escape($val) . " + \"\\n" . $break . "\\n\"" . ";\n";
    }
}

?>
}

function click_category() {
  var f2 = document.CAMOS;
  var category_index = f2.select_category.selectedIndex;
  if ((category_index < 0) || (category_index > f2.select_category.length-1)) {return 0;}
  var sel = f2.select_category.options[f2.select_category.selectedIndex].value;
  for (var i1=0;i1<array1.length;i1++) {
    if (array1[i1][1] == sel) {
    f2.select_subcategory.length = 0;
    f2["select_item[]"].length = 0;
    f2.textarea_content.value = '';
      for (var i2=0;i2<array2.length;i2++) {
        if (array1[i1][1] == array2[i2][1]) {
          f2.select_subcategory.options[f2.select_subcategory.length] = new Option(array2[i2][0], array2[i2][2]);
        }
      }
    }
  }
<?php

  $temp_preselect_mode = $preselect_mode;
if ($preselect_subcategory_override != '') {
    $temp_preselect_mode = "by name";
    $preselect_subcategory = $preselect_subcategory_override;
}
?>
  if (select_word(<?php echo js_escape($temp_preselect_mode) . ", " . js_escape($preselect_subcategory); ?> ,f2.select_subcategory)) {
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
    f2["select_item[]"].length = 0;
    f2.textarea_content.value = '';
      for (var i2=0;i2<array3.length;i2++) {
        if (array2[i1][2] == array3[i2][2]) {
          f2["select_item[]"].options[f2["select_item[]"].length] = new Option(array3[i2][0], array3[i2][3]);
        }
      }
    }
  }
<?php

  $temp_preselect_mode = $preselect_mode;
if ($preselect_item_override != '') {
    $temp_preselect_mode = "by name";
    $preselect_item = $preselect_item_override;
}
?>
  if (select_word(<?php echo js_escape($temp_preselect_mode) . ", " . js_escape($preselect_item); ?> ,f2["select_item[]"])) {
    click_item();
    preselect_off = true;
  }
}
function click_item() {
  var f2 = document.CAMOS;
  var item_index = f2["select_item[]"].selectedIndex;
  if ((item_index < 0) || (item_index > f2["select_item[]"].length-1)) {return 0;}
  var sel = f2["select_item[]"].options[item_index].value;
  for (var i1=0;i1<array3.length;i1++) {
    if (array3[i1][3] == sel) {
      //diplay text in content box
      f2.textarea_content.value= array3[i1][1].replace(/\\/g,'');
    }
  }
}

function selectContains(myselect, str) {
  for (var i=0;i<myselect.length;i++) {
    if (myselect.options[i].text == trimString(str)) {return true;}
  }
}

function insert_content(direction) {
  var f2 = document.CAMOS;
  var source_box = f2.textarea_content;
  var target_box = f2.textarea_content02;
  if (direction == 'up') {
    source_box = f2.textarea_content02;
    target_box = f2.textarea_content;
  }
  var sba = source_box.selectionStart;
  var sbb = source_box.selectionEnd;
  var tba = target_box.selectionStart;
  var tbb = target_box.selectionEnd;
  if (sbb-sba == 0) {
    sba = 0;
    sbb = source_box.value.length;
  }
  var insert_text = (source_box.value).
    substring(sba, sbb);
  target_box.value = (target_box.value).
    substring(0,tba) + insert_text +
    (target_box.value).substring(tba,target_box.value.length);
}

//AJAX FUNCTIONS
//Function to create an XMLHttp Object.
function getxmlhttp (){
  //Create a boolean variable to check for a valid microsoft active X instance.
  var xmlhttp = false;

  //Check if we are using internet explorer.
  try {
    //If the javascript version is greater than 5.
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
    //If not, then use the older active x object.
    try {
      //If we are using internet explorer.
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
      //Else we must be using a non-internet explorer browser.
      xmlhttp = false;
    }
  }

  //If we are using a non-internet explorer browser, create a javascript instance of the object.
  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
  xmlhttp = new XMLHttpRequest();
  }

  return xmlhttp;
}

//Function to process an XMLHttpRequest.
function processajax (serverPage, obj, getOrPost, str){
  //Get an XMLHttpRequest object for use.
  xmlhttp = getxmlhttp ();
  if (getOrPost == "get"){
    xmlhttp.open("GET", serverPage);
    xmlhttp.onreadystatechange = function() {
      if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        obj.innerHTML = xmlhttp.responseText;
      }
    }
    xmlhttp.send(null);
  } else {
    xmlhttp.open("POST", serverPage, true);
    xmlhttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");
    xmlhttp.onreadystatechange = function() {
      if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        obj.innerHTML = xmlhttp.responseText;
      }
    }
    xmlhttp.send(str);
  }
}


function setformvalues(form_array){

  //Run through a list of all objects
  var str = '';
  for(key in form_array) {
    str += key + "=" + encodeURIComponent(form_array[key]) + "&";
  }
  //Then return the string values.
  return str;
}

//END OF AJAX RELATED FUNCTIONS

function js_button(mode,selection) {
  var f2 = document.CAMOS;
//check lock next
if ( (mode == 'add') && (selection == 'change_content') && (isLocked()) ) {
  alert(<?php echo xlj("You have attempted to alter content which is locked. Remove the lock if you want to do this. To unlock, remove the line, '/*lock::*/'"); ?>);
  return;
}
//end check lock

//check for blank or duplicate submissions
if ( (mode == 'add') || (mode == 'alter') ) {
  if (selection == 'change_category') {
    if (trimString(f2.change_category.value) == "") {
      alert(<?php echo xlj("You cannot add a blank value for a category!"); ?>);
      return;
    }
    if (selectContains(f2.select_category, trimString(f2.change_category.value))) {
      alert(<?php echo xlj("There is already a category named"); ?> + " " + f2.change_category.value + ".");
      return;
    }
  }
  if (selection == 'change_subcategory') {
    if (trimString(f2.change_subcategory.value) == "") {
      alert(<?php echo xlj("You cannot add a blank value for a subcategory!"); ?>);
      return;
    }
    if (selectContains(f2.select_subcategory, trimString(f2.change_subcategory.value))) {
      alert(<?php echo xlj("There is already a subcategory named"); ?> + " " + f2.change_subcategory.value + ".");
      return;
    }
  }
  if (selection == 'change_item') {
    if (trimString(f2.change_item.value) == "") {
      alert(<?php echo xlj("You cannot add a blank value for an item!"); ?>);
      return;
    }
    if (selectContains(f2["select_item[]"], trimString(f2.change_item.value))) {
      alert(<?php echo xlj("There is already an item named"); ?> + " " + f2.change_item.value + ".");
      return;
    }
  }
}
//end of check for blank or duplicate submissions

  if (mode == 'delete') {
    if (!confirm(<?php echo xlj("Are you sure you want to delete this item from the database?"); ?>)) {
      return;
    }
  }
  //check selections and assign variable values
  var f2 = document.CAMOS;
  var category_index = f2.select_category.selectedIndex;
  var category_value;
  var category_text;
  if (category_index < 0) {
    if ((selection == 'change_subcategory') || (selection == 'change_item') ||
      (selection == 'change_content')) {
//      alert ('You have not selected a category!');
      return;
    }
    category_value = -1;
    category_text = '';
  }
  else {
    category_value = f2.select_category.options[category_index].value;
    category_text = f2.select_category.options[category_index].text;
  }
  var subcategory_index = f2.select_subcategory.selectedIndex;
  var subcategory_value;
  var subcategory_text;
  if (subcategory_index < 0) {
    if ((selection == 'change_item') || (selection == 'change_content')) {
//      alert ('You have not selected a subcategory!');
     return;
    }
    subcategory_value = -1;
    subcategory_text = '';
  }
  else {
    subcategory_value = f2.select_subcategory.options[subcategory_index].value;
    subcategory_text = f2.select_subcategory.options[subcategory_index].text;
  }
  var item_index = f2["select_item[]"].selectedIndex;
  var item_value;
  var item_text;
  if (item_index < 0) {
    if (selection == 'change_content') {
//      alert ('You have not selected an item!');
      return;
    }
    item_value = -1;
    item_text = '';
  }
  else {
    item_value = f2["select_item[]"].options[item_index].value;
    item_text = f2["select_item[]"].options[item_index].text;
  }
  f2.category.value = category_text;
  f2.subcategory.value = subcategory_text;
  f2.item.value = item_text;
  //end of setting values relating to selections

//deal with clone buttons or add, alter, delete.
  if ( (mode.substr(0,5) == 'clone') || (mode == 'add') || (mode == 'add to') ||
    (mode == 'alter') || (mode =='delete') ) {
    f2.hidden_mode.value = mode;
    f2.hidden_selection.value = selection;
    f2.hidden_category.value = category_value;
    f2.hidden_subcategory.value = subcategory_value;
    f2.hidden_item.value = item_value;
<?php
if (!$out_of_encounter) {
    ?>
    f2.action = '<?php print $GLOBALS['webroot'] ?>/interface/patient_file/encounter/load_form.php?formname=CAMOS';
    <?php
} else {
    ?>
    f2.action = '<?php print $GLOBALS['webroot'] ?>/interface/forms/CAMOS/new.php?mode=external';
    <?php
}
?>
    f2.target = '_self';
    f2.submit();
    return;
  }
//ajax code
    var myobj = document.getElementById('id_info');
    myarray = new Array();
    myarray['category'] = category_text;
    myarray['subcategory'] = subcategory_text;
    myarray['item'] = item_text;
    myarray['content']
    if (selection == 'submit_selection') {
      myarray['content'] = (f2.textarea_content.value).substring(f2.textarea_content.selectionStart, f2.textarea_content.selectionEnd);
    }
    else {myarray['content'] = f2.textarea_content.value;}
    myarray['csrf_token_form'] = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
    var str = setformvalues(myarray);
//    alert(str);
    processajax ('<?php print $GLOBALS['webroot'] ?>/interface/forms/CAMOS/ajax_save.php', myobj, "post", str);
//    alert("submitted!");
//ajax code
}

function selectItem () {
  f2 = document.CAMOS;
  f2.item.value=f2["select_item[]"].options[f2["select_item[]"].selectedIndex].text;
  f2.content.value = f2.textarea_content.value;
}
function getKey(e) { //process keypresses with getKeyPress
  var keynum;
  if(window.event) { //IE
    keynum = e.keyCode;
  } else if(e.which) { // Netscape/Firefox/Opera
    keynum = e.which;
  }
  return keynum;
}
function gotoOne(e) {
  if (getKey(e) == 96) {
    document.CAMOS.clone_others_search.focus();
  }
}
function processEnter(e,message) {
  if (getKey(e) == 13) {
    if (message == "clone_others_search") {
      js_button('clone others', 'clone others');
    }
  }
}
$(function (body) {
    init();
});
</script>
</head>
<body class="body_top">
<div name="form_container" onKeyPress="gotoOne(event)">
<form method='post' action="<?php echo $rootdir;?>/forms/CAMOS/save.php?mode=new" name="CAMOS">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<?php
if (!$out_of_encounter) {
//  echo "<h1>$out_of_encounter</h1>\n";
    ?>
<input type='button' name='clone' value='<?php echo xla('Clone'); ?>' onClick="js_button('clone', 'clone')">
<input type='button' name='clone_visit' value='<?php echo xla('Clone Past Visit'); ?>' onClick="js_button('clone last visit', 'clone last visit')">
<select name='stepback'>
  <option value='1'><?php echo xlt('Back one visit'); ?></option>
  <option value='2'><?php echo xlt('Back two visits'); ?></option>
  <option value='3'><?php echo xlt('Back three visits'); ?></option>
  <option value='4'><?php echo xlt('Back four visits'); ?></option>
  <option value='5'><?php echo xlt('Back five visits'); ?></option>
  <option value='6'><?php echo xlt('Back six visits'); ?></option>
  <option value='7'><?php echo xlt('Back seven visits'); ?></option>
  <option value='8'><?php echo xlt('Back eight visits'); ?></option>
  <option value='9'><?php echo xlt('Back nine visits'); ?></option>
  <option value='10'><?php echo xlt('Back ten visits'); ?></option>
  <option value='11'><?php echo xlt('Back eleven visits'); ?></option>
  <option value='12'><?php echo xlt('Back twelve visits'); ?></option>
</select>
    <?php
    echo "<a href='{$GLOBALS['form_exit_url']}' onclick='top.restoreSession()'>[" . xlt('Leave The Form') . "]</a>";
    ?>
<input type='button' name='hide columns' value='<?php echo xla('Hide/Show Columns'); ?>' onClick="hide_columns()">
<input type='button' name='submit form' value='<?php echo xla('Submit Selected Content'); ?>' onClick="js_button('submit','submit_selection')">
    <?php
} //end of if !$out_of_encounter
?>
<div id=id_info style="display:inline">
<!-- supposedly where ajax induced php pages can print their output to... -->
</div>
<div id=id_mainbox style="display:inline">
<?php
if ($error != '') {
    echo "<h1> error: " . text($error) . "</h1>\n";
}
?>
<table border='1'>
<tr>
  <td>
  <div id='id_category_column_header' style="display:inline">
    <?php echo xlt('Category'); ?>
  </div> <!-- end of id_category_column_header -->
  </td>
  <td>
  <div id='id_subcategory_column_header' style="display:inline">
    <?php echo xlt('Subcategory'); ?>
  </div> <!-- end of id_subcategory_column_header -->
  </td>
  <td>
  <div id='id_item_column_header' style="display:inline">
    <?php echo xlt('Item'); ?>
  </div> <!-- end of id_item_column_header -->
  </td>
  <td>
    <?php echo xlt('Content'); ?>
  </td>
</tr>

<tr>
  <td>
  <div id='id_category_column' style="display:inline">
    <select name='select_category' size='<?php echo attr($select_size); ?>' onchange="click_category()"></select><br />
<?php

if (myAuth() == 1) {//root user only can see administration option
    ?>
    <input type='text' name='change_category'><br />
    <input type='button' name='add1' value='<?php echo xla('add'); ?>' onClick="js_button('add','change_category')">
    <input type='button' name='alter1' value='<?php echo xla('alter'); ?>' onClick="js_button('alter','change_category')">
    <input type='button' name='del1' value='<?php echo xla('del'); ?>' onClick="js_button('delete','change_category')"><br />
    <?php
}
?>
  </div> <!-- end of id_category_column -->
  </td>
  <td>
  <div id='id_subcategory_column' style="display:inline">
    <select name='select_subcategory' size='<?php echo attr($select_size); ?>' onchange="click_subcategory()"></select><br />
<?php

if (myAuth() == 1) {//root user only can see administration option
    ?>
    <input type='text' name='change_subcategory'><br />
    <input type='button' name='add2' value='<?php echo xla('add'); ?>' onClick="js_button('add','change_subcategory')">
    <input type='button' name='alter1' value='<?php echo xla('alter'); ?>' onClick="js_button('alter','change_subcategory')">
    <input type='button' name='del2' value='<?php echo xla('del'); ?>' onClick="js_button('delete','change_subcategory')"><br />
    <?php
}
?>
  </div> <!-- end of id_subcategory_column -->
  </td>
  <td>
  <div id='id_item_column' style="display:inline">
    <select name='select_item[]' size='<?php echo attr($select_size); ?>' onchange="click_item()" multiple="multiple"></select><br />
<?php

if (myAuth() == 1) {//root user only can see administration option
    ?>
    <input type='text' name='change_item'><br />
    <input type='button' name='add3' value='<?php echo xla('add'); ?>' onClick="js_button('add','change_item')">
    <input type='button' name='alter1' value='<?php echo xla('alter'); ?>' onClick="js_button('alter','change_item')">
    <input type='button' name='del3' value='<?php echo xla('del'); ?>' onClick="js_button('delete','change_item')"><br />
    <?php
}
?>
  </div> <!-- end of id_item_column -->
  </td>
  <td>
<div id='id_textarea_content' style="display:inline">
    <textarea name='textarea_content' cols='<?php echo attr($textarea_cols); ?>' rows='<?php echo attr($textarea_rows); ?>' onFocus="content_focus()" onBlur="content_blur()" onDblClick="specialSelect(this,'/*','*/')" tabindex='2'></textarea>
    <br/>
<input type='text' size='35' name='clone_others_search' value='<?php echo attr($_POST['clone_others_search']); ?>' tabindex='1' onKeyPress="processEnter(event,'clone_others_search')"/>
<input type='button' name='clone_others_search_button' value='<?php echo xla('Search'); ?>' onClick="js_button('clone others', 'clone others')"/>
<input type='button' name='clone_others_selected_search_button' value='<?php echo xla('Search Selected'); ?>' onClick="js_button('clone others selected', 'clone others selected')"/>
<?php

if (myAuth() == 1) {//root user only can see administration option
    ?>
<div id='id_main_content_buttons' style="display:block">
    <input type='button' name='add4' value='<?php echo xla('Add'); ?>' onClick="js_button('add','change_content')">
    <input type='button' name='add4' value='<?php echo xla('Add to'); ?>' onClick="js_button('add to','change_content')">
    <input type='button' name='lock' value='<?php echo xla('Lock'); ?>' onClick="lock_content()">
    <?php

    if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
        ?>
    <input type='button' name='icd10' value='<?php echo xla('ICD10'); ?>' onClick="append_icd10()">
</div> <!-- end of id_main_content_buttons-->
        <?php
    }
    ?>
    <?php
}
?>
  </td>
</td>
</tr>
</table>

<input type='hidden' name='hidden_mode' />
<input type='hidden' name='hidden_selection' />
<input type='hidden' name='hidden_category' />
<input type='hidden' name='hidden_subcategory' />
<input type='hidden' name='hidden_item' />

<input type='hidden' name='category' />
<input type='hidden' name='subcategory' />
<input type='hidden' name='item' />
<input type='hidden' name='content' />
<?php

if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
    ?>
<input type='button' name='submit form' value='<?php echo xla('Submit All Content'); ?>' onClick="js_button('submit','submit')">
<input type='button' name='submit form' value='<?php echo xla('Submit Selected Content'); ?>' onClick="js_button('submit','submit_selection')">
    <?php
}
?>
<?php

if (!$out_of_encounter) { //do not do stuff that is encounter specific if not in an encounter
    echo "<a href='{$GLOBALS['form_exit_url']}' onclick='top.restoreSession()'>[" . xlt('Leave The Form') . "]</a>";
    echo "<a href='" . $GLOBALS['webroot'] . "/interface/forms/CAMOS/help.html' target='new'> | [" . xlt('Help') . "]</a>";
//  echo $previous_encounter_data; //probably don't need anymore now that we have clone last visit
}
?>
</div>
</form>
</div>
<?php
formFooter();

//PHP FUNCTIONS

function searchName($string)
{
 //match one or more names and return clause for query of pids
    $string = trim($string);
    if ($string == 'this') {
        return " and (pid = '" . add_escape_custom($_SESSION['pid']) . "') ";
    }

    global $limit;
    $ret = '';
    $data = array();
    $fname = '';
    $lname = '';
    if ($string == '') {
        return $ret;
    }

    $split = preg_split('/\s+/', $string);
    $name1 = $split[1];
    $name2 = $split[0];
    if ($name1 != '') {
        $name1 = "%" . $name1 . "%";
    }

    if ($name2 != '') {
        $name1 = "%" . $name2 . "%";
    }

    $query = sqlStatement("select pid from patient_data where fname like ? or fname like ? or " .
    "lname like ? or lname like ? limit " . escape_limit($limit), array($name1, $name2, $name1, $name2));
    while ($results = sqlFetchArray($query)) {
        array_push($data, "'" . add_escape_custom($results['pid'])) . "'";
    }

    if (count($data) > 0) {
        $ret = join(" or pid = ", $data);
        $ret = " and (pid = " . $ret . ") ";
    }

    return $ret;
}
function getMyPatientData($form_id, $show_phone_flag)
{
//return a string of patient data and encounter data based on the form_CAMOS id
    $ret = '';
    $name = '';
    $dob = '';
    $enc_date = '';
    $phone_list = '';
    $pid = '';
    $query = sqlStatement("select t1.pid, t1.fname, t1.mname, t1.lname, " .
    "t1.phone_home, t1.phone_biz, t1.phone_contact, t1.phone_cell, " .
    "date_format(t1.DOB,'%m-%d-%y') as DOB, date_format(t2.date,'%m-%d-%y') as date, " .
    "datediff(current_date(),t2.date) as days " .
    "from patient_data as t1 join forms as t2 on (t1.pid = t2.pid) where t2.form_id=? " .
    "and form_name like 'CAMOS%'", array($form_id));
    if ($results = sqlFetchArray($query)) {
        $pid = $results['pid'];
        $fname = $results['fname'];
        $mname = $results['mname'];
        $lname = $results['lname'];
        if ($mname) {
            $name = $fname . ' ' . $mname . ' ' . $lname;
        } else {
            $name = $fname . ' ' . $lname;
        }

            $dob = $results['DOB'];
            $enc_date = $results['date'];
            $days_ago = $results['days'];
            $phone_list =
            "/* Home: " . $results['phone_home'] . " | " .
            "Cell: " . $results['phone_cell'] . " | " .
            "Bus: " . $results['phone_biz'] . " | " .
            "Contact: " . $results['phone_contact'] . " */";
    }

    $ret = "/*$pid, $name, DOB: $dob, Enc: $enc_date, $days_ago days ago. */";
    if ($show_phone_flag === true) {
        $ret .= "\n" . $phone_list;
    }

    return $ret;
}
?>
