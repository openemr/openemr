<?php
/**
 * Interactive code finder.
 * For DataTables documentation see: http://legacy.datatables.net/
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015-2017 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/patient.inc');
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

$info_msg = "";

// What we are picking from: codes, fields, lists or groups
$what = empty($_GET['what']) ? 'codes' : $_GET['what'];

// For what == codes
$codetype = empty($_GET['codetype']) ? '' : $_GET['codetype'];
if (!empty($codetype)) {
    $allowed_codes = split_csv_line($codetype);
}
// This is the html element of the target script where the selected code will be stored.
$target_element = empty($_GET['target_element']) ? '' : $_GET['target_element'];

// For what == fields
$source = empty($_GET['source']) ? 'D' : $_GET['source'];

// For what == groups
$layout_id = empty($_GET['layout_id']) ? '' : $_GET['layout_id'];
?>
<!DOCTYPE html>
<html>
<head>
<?php html_header_show(); ?>
<title><?php echo xlt('Code Finder'); ?></title>
<link rel="stylesheet" href='<?php echo attr($css_header) ?>' type='text/css'>

<style type="text/css">
</style>

<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-dt-1-10-13/css/jquery.dataTables.min.css" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-dt-1-3-2/css/colReorder.dataTables.min.css" type="text/css">

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-1-10-13/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>

<script language="JavaScript">

var oTable;

// Keeps track of which items have been selected during this session.
var oChosenIDs = {};

$(document).ready(function() {

 // Initializing the DataTable.
 oTable = $('#my_data_table').dataTable({
  "bProcessing": true,
  // Next 2 lines invoke server side processing
  "bServerSide": true,
  "sAjaxSource": "find_code_dynamic_ajax.php",
  // Vertical length options and their default
  "aLengthMenu": [ 15, 25, 50, 100 ],
  "iDisplayLength": 15,
  // Specify a width for the first column.
  "aoColumns": [{"sWidth":"10%"}, null],
  // This callback function passes some form data on each call to the ajax handler.
  "fnServerParams": function (aoData) {
    aoData.push({"name": "what", "value": "<?php echo attr($what); ?>"});
<?php if ($what == 'codes') { ?>
    aoData.push({"name": "codetype", "value": document.forms[0].form_code_type.value});
    aoData.push({"name": "inactive", "value": (document.forms[0].form_include_inactive.checked ? 1 : 0)});
<?php } else if ($what == 'fields') { ?>
    aoData.push({"name": "source", "value": "<?php echo attr($source); ?>"});
<?php } else if ($what == 'groups') { ?>
    aoData.push({"name": "layout_id", "value": "<?php echo attr($layout_id); ?>"});
<?php } ?>
  },
  // Drawing a row, apply styling if it is previously selected.
  "fnCreatedRow": function (nRow, aData, iDataIndex) {
    if (oChosenIDs[nRow.id]) {
      nRow.style.fontWeight = 'bold';
    }
  },
  // Language strings are included so we can translate them
  "oLanguage": {
   "sSearch"      : "<?php echo xla('Search for'); ?>:",
   "sLengthMenu"  : "<?php echo xla('Show') . ' _MENU_ ' . xla('entries'); ?>",
   "sZeroRecords" : "<?php echo xla('No matching records found'); ?>",
   "sInfo"        : "<?php echo xla('Showing') . ' _START_ ' . xla('to{{range}}') . ' _END_ ' . xla('of') . ' _TOTAL_ ' . xla('entries'); ?>",
   "sInfoEmpty"   : "<?php echo xla('Nothing to show'); ?>",
   "sInfoFiltered": "(<?php echo xla('filtered from') . ' _MAX_ ' . xla('total entries'); ?>)",
   "oPaginate"    : {
    "sFirst"      : "<?php echo xla('First'); ?>",
    "sPrevious"   : "<?php echo xla('Previous'); ?>",
    "sNext"       : "<?php echo xla('Next'); ?>",
    "sLast"       : "<?php echo xla('Last'); ?>"
   }
  }
 });

 // OnClick handler for the rows
 $('#my_data_table').on('click', 'tbody tr', function () {
  var jobj = JSON.parse(this.id.substring(4));

  this.style.fontWeight = 'bold';
  oChosenIDs[this.id] = 1;

<?php if ($what == 'codes') { ?>
  // this.id is of the form "CID|jsonstring".
  var codesel = jobj['code'].split('|');
  selcode(jobj['codetype'], codesel[0], codesel[1], jobj['description']);
<?php } else if ($what == 'fields') { ?>
  selectField(jobj);
<?php } else if ($what == 'lists') { ?>
  SelectList(jobj);
<?php } else if ($what == 'groups') { ?>
  SelectItem(jobj);
<?php } ?>

 } );

<?php if ($what == 'codes') { ?>
 // Initialize the selector of codes that can be deleted.
 if (opener.get_related) {
  var acodes = opener.get_related();
  var sel = document.forms[0].form_delcodes;
  if (acodes.length > 1) {
   for (var i = 0; i < acodes.length; ++i) {
    sel.options[sel.options.length] = new Option(acodes[i], acodes[i]);
   }
  }
  else {
   sel.style.display = 'none';
  }
 }
<?php } ?>

});

<?php if ($what == 'codes') { ?>

// Pass info back to the opener and close this window. Specific to billing/product codes.
function selcode(codetype, code, selector, codedesc) {
 if (opener.closed || ! opener.set_related) {
  alert('<?php echo xls('The destination form was closed; I cannot act on your selection.'); ?>');
 }
 else {
  var msg = opener.set_related(codetype, code, selector, codedesc);
  if (msg) alert(msg);
  // window.close();
  return false;
 }
}
// Function to call the opener to delete all or one related code. Specific to billing/product codes.
function delcode() {
 if (opener.closed || ! opener.del_related) {
  alert('<?php echo xls('The destination form was closed; I cannot act on your selection.'); ?>');
 }
 else {
  var sel = document.forms[0].form_delcodes;
  opener.del_related(sel.value);
  oChosenIDs = {};
  oTable.fnDraw();
  // window.close();
  return false;
 }
}

<?php } else if ($what == 'fields') { ?>

function selectField(jobj) {
  if (opener.closed || ! opener.SetField) {
    alert('The destination form was closed; I cannot act on your selection.');
  }
  else {
    opener.SetField(jobj['field_id'], jobj['title'], jobj['data_type'], jobj['uor'], jobj['fld_length'],
      jobj['max_length'], jobj['list_id'], jobj['titlecols'], jobj['datacols'], jobj['edit_options'],
      jobj['description'], jobj['fld_rows']);
  }
  dlgclose();
  return false;
}
function newField() {
  return selectField({
    "field_id"    : document.forms[0].new_field_id.value,
    "title"       : '',
    "data_type"   : 2,
    "uor"         : 1,
    "fld_length"  : 10,
    "max_length"  : 255,
    "list_id"     : '',
    "titlecols"   : 1,
    "datacols"    : 3,
    "edit_options": '',
    "description" : '',
    "fld_rows"    : 0
  });
}

<?php } else if ($what == 'lists') { ?>

function SelectList(jobj) {
  if (opener.closed || ! opener.SetList)
    alert('The destination form was closed; I cannot act on your selection.');
  else
    opener.SetList(jobj['code']);
  dlgclose();
  return false;
};

<?php } else if ($what == 'groups') { ?>

var SelectItem = function(jobj) {
  if (opener.closed)
    alert('The destination form was closed; I cannot act on your selection.');
  else
    opener.MoveFields(jobj['code']);
  dlgclose();
  return false;
};

<?php } ?>

</script>

</head>

<body class="body_top">

<?php
$string_target_element = empty($target_element) ? '?' : "?target_element=" . rawurlencode($target_element) . "&";
?>

<form method='post' name='theform'>

<?php
echo "<p>\n";
if ($what == 'codes') {
    if (isset($allowed_codes)) {
        if (count($allowed_codes) == 1) {
            echo "<input type='text' name='form_code_type' value='" . attr($codetype) . "' size='5' readonly>\n";
        } else {
            echo "<select name='form_code_type' onchange='oTable.fnDraw()'>\n";
            foreach ($allowed_codes as $code) {
                echo " <option value='" . attr($code) . "'>" . xlt($code_types[$code]['label']) . "</option>\n";
            }
            echo "</select>\n";
        }
    } else {
        echo "<select name='form_code_type' onchange='oTable.fnDraw()'>\n";
        foreach ($code_types as $key => $value) {
            echo " <option value='" . attr($key) . "'";
            echo ">" . xlt($value['label']) . "</option>\n";
        }
        echo " <option value='PROD'";
        echo ">" . xlt("Product") . "</option>\n";
        echo "   </select>\n";
    }
    echo "&nbsp;&nbsp;\n";
    echo "<input type='checkbox' name='form_include_inactive' value='1' onclick='oTable.fnDraw()' />" .
    xlt('Include Inactive') . "\n";
    echo "&nbsp;&nbsp;\n";
    echo "<input type='button' value='" . xla('Delete') . "' onclick='delcode()' />\n";
    echo "<select name='form_delcodes'>\n";
    echo " <option value=''>" . xlt('All') . "</option>\n";
    echo "</select>\n";
    echo "&nbsp;&nbsp;\n";
    echo "<input type='button' value='" . xla('Close') . "' onclick='dlgclose()' />\n";
}
if ($what == 'lists') {
    echo "<input type='button' value='" . xla('Delete') . "' onclick='SelectList({\"code\":\"\"})' />\n";
}
echo "</p>\n";
?>

<!-- Class "display" is defined in demo_table.css -->
<table cellpadding="0" cellspacing="0" border="0" class="display" id="my_data_table">
 <thead>
  <tr>
   <th><?php echo xlt('Code'); ?></th>
   <th><?php echo xlt('Description'); ?></th>
  </tr>
 </thead>
 <tbody>
  <tr>
   <!-- Class "dataTables_empty" is defined in jquery.dataTables.css -->
   <td colspan="2" class="dataTables_empty">...</td>
  </tr>
 </tbody>
</table>

<?php if ($what == 'fields' && $source == 'E') { ?>
<center>
<p>
<input type='text' name='new_field_id' size='20' />&nbsp;
<input type='button' value='<?php echo xla('Or create this new field ID') ?>' onclick='newField()' />
</p>
</center>
<?php } ?>

</form>
</body>
</html>
