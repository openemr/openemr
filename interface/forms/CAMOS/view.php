<?php

/**
 * CAMOS view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    fndtn357 <fndtn357@gmail.com>
 * @author    cornfeed <jdough823@gmail.com>
 * @author    cfapress <cfapress>
 * @author    Wakie87 <scott@npclinics.com.au>
 * @author    Robert Down <robertdown@live.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2006-2009 Mark Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2011 cornfeed <jdough823@gmail.com>
 * @copyright Copyright (c) 2012 fndtn357 <fndtn357@gmail.com>
 * @copyright Copyright (c) 2016 Wakie87 <scott@npclinics.com.au>
 * @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("../../../library/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: CAMOS");
$textarea_rows = 22;
$textarea_cols = 90;
?>
<html><head>
    <?php Header::setupHeader(); ?>
<script>
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
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir?>/forms/CAMOS/save.php?mode=delete&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<h1> <?php echo xlt('CAMOS'); ?> </h1>
<input type="submit" name="delete" value="<?php echo xla('Delete Selected Items'); ?>" />
<input type="submit" name="update" value="<?php echo xla('Update Selected Items'); ?>" />
<?php
echo "<a href='{$GLOBALS['form_exit_url']}'>[" . xlt('do nothing') . "]</a>";
?>
<br/><br/>
<input type='button' value='<?php echo xla('Select All'); ?>'
  onClick='checkall()'>
<input type='button' value='<?php echo xla('Unselect All'); ?>'
  onClick='uncheckall()'>
<br/><br/>
<?php
//experimental code start

$pid = $GLOBALS['pid'];
$encounter = $GLOBALS['encounter'];

$query = "select t1.id, t1.content from " . mitigateSqlTableUpperCase("form_CAMOS") . " as t1 join forms as t2 " .
  "on (t1.id = t2.form_id) where t2.form_name like 'CAMOS%' " .
  "and t2.encounter like ? and t2.pid = ?";

$statement = sqlStatement($query, array($encounter, $pid));
while ($result = sqlFetchArray($statement)) {
    print "<input type=button value='" . xla('Edit') . "' onClick='show_edit(" . attr_js('id_textarea_' . $result['id']) . ")'>";
    print "<input type=checkbox name='ch_" . attr($result['id']) . "'> " . text($result['content']) . "<br/>";
    print "<div id=id_textarea_" . attr($result['id']) . " style='display:none'>";
    print "<textarea name=textarea_" . attr($result['id']) . " cols=" . attr($textarea_cols) . " rows=" . attr($textarea_rows) . " onFocus='content_focus()' onBlur='content_blur()' >" . text($result['content']) . "</textarea><br/>";
    print "</div>";
}


//experimental code end
?>
</form>
<?php

formFooter();
