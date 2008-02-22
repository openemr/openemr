<?php
 include_once("../../globals.php");
 include_once("$srcdir/acl.inc");
 include_once("$srcdir/options.inc.php");

 // Session pid must be right or bad things can happen when demographics are saved!
 //
 include_once("$srcdir/pid.inc");
 $set_pid = $_GET["set_pid"] ? $_GET["set_pid"] : $_GET["pid"];
 if ($set_pid && $set_pid != $_SESSION["pid"]) {
  setpid($set_pid);
 }

 include_once("$srcdir/patient.inc");

 $result = getPatientData($pid);
 $result2 = getEmployerData($pid);

 // Check authorization.
 $thisauth = acl_check('patients', 'demo');
 if ($pid) {
  if ($thisauth != 'write')
   die("Updating demographics is not authorized.");
  if ($result['squad'] && ! acl_check('squads', $result['squad']))
   die("You are not authorized to access this squad.");
 } else {
  if ($thisauth != 'write' && $thisauth != 'addonly')
   die("Adding demographics is not authorized.");
 }

$CPR = 4; // cells per row

// Might want to use the list_options table and then trash this.
$relats = array('','self','spouse','child','other');

// $statii = array('married','single','divorced','widowed','separated','domestic partner');
// $langi = getLanguages();
// $ethnoraciali = getEthnoRacials();
// $provideri = getProviderInfo();

$insurancei = getInsuranceProviders();

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 " .
  "ORDER BY group_name, seq");
?>
<html>
<head>
<? html_header_show();?>

<link rel=stylesheet href="<?php echo $css_header; ?>" type="text/css">

<style>
body, td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

body {
 padding: 5pt 5pt 5pt 5pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

</style>

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>

<SCRIPT LANGUAGE="JavaScript"><!--

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

//code used from http://tech.irt.org/articles/js037/
function replace(string,text,by) {
 // Replaces text with by in string
 var strLength = string.length, txtLength = text.length;
 if ((strLength == 0) || (txtLength == 0)) return string;

 var i = string.indexOf(text);
 if ((!i) && (text != string.substring(0,txtLength))) return string;
 if (i == -1) return string;

 var newstr = string.substring(0,i) + by;

 if (i+txtLength < strLength)
  newstr += replace(string.substring(i+txtLength,strLength),text,by);

 return newstr;
}

function upperFirst(string,text) {
 return replace(string,text,text.charAt(0).toUpperCase() + text.substring(1,text.length));
}

<?php for ($i=1;$i<=3;$i++) { ?>
function auto_populate_employer_address<?php echo $i ?>(){
 if (document.demographics_form.i<?php echo $i?>subscriber_relationship.options[document.demographics_form.i<?php echo $i?>subscriber_relationship.selectedIndex].value == "self") {
  document.demographics_form.i<?php echo $i?>subscriber_fname.value=document.demographics_form.form_fname.value;
  document.demographics_form.i<?php echo $i?>subscriber_mname.value=document.demographics_form.form_mname.value;
  document.demographics_form.i<?php echo $i?>subscriber_lname.value=document.demographics_form.form_lname.value;
  document.demographics_form.i<?php echo $i?>subscriber_street.value=document.demographics_form.form_street.value;
  document.demographics_form.i<?php echo $i?>subscriber_city.value=document.demographics_form.form_city.value;
  document.demographics_form.i<?php echo $i?>subscriber_state.value=document.demographics_form.form_state.value;
  document.demographics_form.i<?php echo $i?>subscriber_postal_code.value=document.demographics_form.form_postal_code.value;
  document.demographics_form.i<?php echo $i?>subscriber_country.value=document.demographics_form.form_country_code.value;
  document.demographics_form.i<?php echo $i?>subscriber_phone.value=document.demographics_form.form_phone_home.value;
  document.demographics_form.i<?php echo $i?>subscriber_DOB.value=document.demographics_form.form_DOB.value;
  document.demographics_form.i<?php echo $i?>subscriber_ss.value=document.demographics_form.form_ss.value;
  document.demographics_form.i<?php echo $i?>subscriber_sex.selectedIndex = document.demographics_form.form_sex.selectedIndex;
  document.demographics_form.i<?php echo $i?>subscriber_employer.value=document.demographics_form.form_em_name.value;
  document.demographics_form.i<?php echo $i?>subscriber_employer_street.value=document.demographics_form.form_em_street.value;
  document.demographics_form.i<?php echo $i?>subscriber_employer_city.value=document.demographics_form.form_em_city.value;
  document.demographics_form.i<?php echo $i?>subscriber_employer_state.value=document.demographics_form.form_em_state.value;
  document.demographics_form.i<?php echo $i?>subscriber_employer_postal_code.value=document.demographics_form.form_em_postal_code.value;
  document.demographics_form.i<?php echo $i?>subscriber_employer_country.value=document.demographics_form.form_em_country.value;
 }
}

<?php } ?>

function popUp(URL) {
 day = new Date();
 id = day.getTime();
 top.restoreSession();
 eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=300,left = 440,top = 362');");
}

function checkNum () {
 var re= new RegExp();
 re = /^\d*\.?\d*$/;
 str=document.demographics_form.monthly_income.value;
 if(re.exec(str))
 {
 }else{
  alert("Please enter a dollar amount using only numbers and a decimal point.");
 }
}

// Indicates which insurance slot is being updated.
var insurance_index = 0;

// The OnClick handler for searching/adding the insurance company.
function ins_search(ins) {
 insurance_index = ins;
 dlgopen('../../practice/ins_search.php', '_blank', 550, 400);
 return false;
}

// The ins_search.php window calls this to set the selected insurance.
function set_insurance(ins_id, ins_name) {
 var thesel = document.forms[0]['i' + insurance_index + 'provider'];
 var theopts = thesel.options; // the array of Option objects
 var i = 0;
 for (; i < theopts.length; ++i) {
  if (theopts[i].value == ins_id) {
   theopts[i].selected = true;
   return;
  }
 }
 // no matching option was found so create one, append it to the
 // end of the list, and select it.
 theopts[i] = new Option(ins_name, ins_id, false, true);
}

// This capitalizes the first letter of each word in the passed input
// element.  It also strips out extraneous spaces.
function capitalizeMe(elem) {
 var a = elem.value.split(' ');
 var s = '';
 for(var i = 0; i < a.length; ++i) {
  if (a[i].length > 0) {
   if (s.length > 0) s += ' ';
   s += a[i].charAt(0).toUpperCase() + a[i].substring(1);
  }
 }
 elem.value = s;
}

function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

// Compute the length of a string without leading and trailing spaces.
function trimlen(s) {
 var i = 0;
 var j = s.length - 1;
 for (; i <= j && s.charAt(i) == ' '; ++i);
 for (; i <= j && s.charAt(j) == ' '; --j);
 if (i > j) return 0;
 return j + 1 - i;
}

function validate(f) {
<?php
// Generate JavaScript validation logic for the required fields.
while ($frow = sqlFetchArray($fres)) {
  if ($frow['uor'] < 2) continue;
  $data_type = $frow['data_type'];
  $field_id  = $frow['field_id'];
  $fldtitle  = $frow['title'];
  if (!$fldtitle) $fldtitle  = $frow['description'];
  $fldname   = "form_$field_id";
  switch($data_type) {
    case  1:
    case 11:
    case 12:
    case 13:
    case 14:
      echo
      " if (f.$fldname.selectedIndex <= 0) {\n" .
      "  alert('Please choose a value for $fldtitle');\n" .
      "  if (f.$fldname.focus) f.$fldname.focus();\n" .
      "  return false;\n" .
      " }\n";
      break;
    case 2:
    case 3:
    case 4:
      echo
      " if (trimlen(f.$fldname.value) == 0) {\n" .
      "  alert('Please enter a value for $fldtitle');\n" .
      "  if (f.$fldname.focus) f.$fldname.focus();\n" .
      "  return false;\n" .
      " }\n";
      break;
  }
}
?>
 return true;
}

function submitme() {
 var f = document.forms[0];
 if (validate(f)) {
  top.restoreSession();
  f.submit();
 }
}

//-->

</script>
</head>

<body <?php echo $top_bg_line; ?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<form action='demographics_save.php' name='demographics_form' method='post'
 onsubmit='return validate(this)'>
<input type='hidden' name='mode' value='save' />
<input type='hidden' name='db_id' value="<?php echo $result['id']?>" />

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="demographics.php" onclick="top.restoreSession()">
<?php } else { ?>
<a href="patient_summary.php" target="Main" onclick="top.restoreSession()">
<?php } ?>
<font class=title><?php xl('Demographics','e'); ?></font>
<font class=back><?php echo $tback;?></font></a>

<?php

function end_cell() {
  global $item_count, $cell_count;
  if ($item_count > 0) {
    echo "</td>";
    $item_count = 0;
  }
}

function end_row() {
  global $cell_count, $CPR;
  end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $CPR; ++$cell_count) echo "<td></td>";
    echo "</tr>\n";
    $cell_count = 0;
  }
}

function end_group() {
  global $last_group;
  if (strlen($last_group) > 0) {
    end_row();
    echo " </table>\n";
    echo "</div>\n";
  }
}

$last_group = '';
$cell_count = 0;
$item_count = 0;
$display_style = 'block';

mysql_data_seek($fres, 0); // TBD: Move this to sql.inc.

while ($frow = sqlFetchArray($fres)) {
  $this_group = $frow['group_name'];
  $titlecols  = $frow['titlecols'];
  $datacols   = $frow['datacols'];
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $currvalue  = '';
  if (strpos($field_id, 'em_') === 0) {
    $tmp = substr($field_id, 3);
    if (isset($result2[$tmp])) $currvalue = $result2[$tmp];
  }
  else {
    if (isset($result[$field_id])) $currvalue = $result[$field_id];
  }

  // Handle a data category (group) change.
  if (strcmp($this_group, $last_group) != 0) {
    end_group();
    $group_seq  = substr($this_group, 0, 1);
    $group_name = substr($this_group, 1);
    $last_group = $this_group;
    echo "<br /><span class='bold'><input type='checkbox' name='form_cb_$group_seq' value='1' " .
      "onclick='return divclick(this,\"div_$group_seq\");'";
    if ($display_style == 'block') echo " checked";
    echo " /><b>$group_name</b></span>\n";
    echo "<div id='div_$group_seq' class='section' style='display:$display_style;'>\n";
    echo " <table border='0' cellpadding='0'>\n";
    $display_style = 'none';
  }

  // Handle starting of a new row.
  if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
    end_row();
    echo "  <tr>";
  }

  if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

  // Handle starting of a new label cell.
  if ($titlecols > 0) {
    end_cell();
    echo "<td colspan='$titlecols'";
    echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
    if ($cell_count == 2) echo " style='padding-left:10pt'";
    echo ">";
    $cell_count += $titlecols;
  }
  ++$item_count;

  echo "<b>";
  if ($frow['title']) echo $frow['title'] . ":"; else echo "&nbsp;";
  echo "</b>";

  // Handle starting of a new data cell.
  if ($datacols > 0) {
    end_cell();
    echo "<td colspan='$datacols' class='text'";
    if ($cell_count > 0) echo " style='padding-left:5pt'";
    echo ">";
    $cell_count += $datacols;
  }

  ++$item_count;
  generate_form_field($frow, $currvalue);
}

end_group();

 if (! $GLOBALS['simplified_demographics']) {
  $insurance_headings = array("Primary Insurance Provider:", "Secondary Insurance Provider", "Tertiary Insurance provider");
  $insurance_info = array();
  $insurance_info[1] = getInsuranceData($pid,"primary");
  $insurance_info[2] = getInsuranceData($pid,"secondary");
  $insurance_info[3] = getInsuranceData($pid,"tertiary");

  echo "<br /><span class='bold'><input type='checkbox' name='form_cb_ins' value='1' " .
    "onclick='return divclick(this,\"div_ins\");'";
  if ($display_style == 'block') echo " checked";
  echo " /><b>Insurance</b></span>\n";
  echo "<div id='div_ins' class='section' style='display:$display_style;'>\n";

  for($i=1;$i<=3;$i++) {
   $result3 = $insurance_info[$i];
?>
<table border="0">
 <tr>
  <td valign='top' colspan='2'>
   <span class='required'><?php echo $insurance_headings[$i -1]?></span>
   <select name="i<?php echo $i?>provider">
    <option value=""><?php xl('Unassigned','e'); ?></option>
<?php
 foreach ($insurancei as $iid => $iname) {
  echo "<option value='" . $iid . "'";
  if (strtolower($iid) == strtolower($result3{"provider"}))
   echo " selected";
  echo ">" . $iname . "</option>\n";
 }
?>
   </select>&nbsp;<a href='' onclick='return ins_search(<?php echo $i?>)'>
   <?php xl('Search/Add Insurer','e'); ?></a>
  </td>
 </tr>

 <tr>
  <td valign=top>
   <table border="0">

    <tr>
     <td>
      <span class='required'><?php xl('Plan Name','e'); ?>: </span>
     </td>
     <td>
      <input type='entry' size='20' name='i<?php echo $i?>plan_name' value="<?php echo $result3{"plan_name"} ?>"
       onchange="capitalizeMe(this);" />&nbsp;&nbsp;
     </td>
    </tr>

    <tr>
     <td>
      <span class='required'><?php xl('Effective Date','e'); ?>: </span>
     </td>
     <td>
      <input type='entry' size='11' name='i<?php echo $i ?>effective_date'
       value='<?php echo $result3['date'] ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
       title='yyyy-mm-dd' />
     </td>
    </tr>

    <tr>
     <td><span class=required><?php xl('Policy Number','e'); ?>: </span></td>
     <td><input type=entry size=16 name=i<?php echo $i?>policy_number value="<?php echo $result3{"policy_number"}?>"></td>
    </tr>

    <tr>
     <td><span class=required><?php xl('Group Number','e'); ?>: </span></td><td><input type=entry size=16 name=i<?php echo $i?>group_number value="<?php echo $result3{"group_number"}?>"></td>
    </tr>

    <tr<?php if ($GLOBALS['omit_employers']) echo " style='display:none'"; ?>>
     <td class='required'><?php xl('Subscriber Employer (SE)','e'); ?><br><span style='font-weight:normal'>
      (<?php xl('if unemployed enter Student','e'); ?>,<br><?php xl('PT Student, or leave blank','e'); ?>): </span></td>
     <td><input type=entry size=25 name=i<?php echo $i?>subscriber_employer
      value="<?php echo $result3{"subscriber_employer"}?>"
       onchange="capitalizeMe(this);" /></td>
    </tr>

    <tr<?php if ($GLOBALS['omit_employers']) echo " style='display:none'"; ?>>
     <td><span class=required><?php xl('SE Address','e'); ?>: </span></td>
     <td><input type=entry size=25 name=i<?php echo $i?>subscriber_employer_street
      value="<?php echo $result3{"subscriber_employer_street"}?>"
       onchange="capitalizeMe(this);" /></td>
    </tr>

    <tr<?php if ($GLOBALS['omit_employers']) echo " style='display:none'"; ?>>
     <td colspan="2">
      <table>
       <tr>
        <td><span class=required><?php xl('SE City','e'); ?>: </span></td>
        <td><input type=entry size=15 name=i<?php echo $i?>subscriber_employer_city
         value="<?php echo $result3{"subscriber_employer_city"}?>"
          onchange="capitalizeMe(this);" /></td>
        <td><span class=required><?php xl('SE','e'); ?> <?php echo ($GLOBALS['phone_country_code'] == '1') ? 'State' : 'Locality' ?>: </span></td>
        <td><input type=entry size=15 name=i<?php echo $i?>subscriber_employer_state
         value="<?php echo $result3{"subscriber_employer_state"}?>"></td>
       </tr>
       <tr>
        <td><span class=required><?php xl('SE','e'); ?> <?php echo ($GLOBALS['phone_country_code'] == '1') ? 'Zip' : 'Postal' ?> <?php xl('Code','e'); ?>: </span></td>
        <td><input type=entry size=10 name=i<?php echo $i?>subscriber_employer_postal_code value="<?php echo $result3{"subscriber_employer_postal_code"}?>"></td>
        <td><span class=required><?php xl('SE Country','e'); ?>: </span></td>
        <td><input type=entry size=25 name=i<?php echo $i?>subscriber_employer_country
         value="<?php echo $result3{"subscriber_employer_country"}?>"
         onchange="capitalizeMe(this);" /></td>
       </tr>
      </table>
     </td>
    </tr>

   </table>
  </td>

  <td valign=top>
   <span class=required><?php xl('Subscriber','e'); ?>: </span>
   <input type=entry size=10 name=i<?php echo $i?>subscriber_fname
    value="<?php echo $result3{"subscriber_fname"}?>"
    onchange="capitalizeMe(this);" />
   <input type=entry size=3 name=i<?php echo $i?>subscriber_mname
    value="<?php echo $result3{"subscriber_mname"}?>"
    onchange="capitalizeMe(this);" />
   <input type=entry size=10 name=i<?php echo $i?>subscriber_lname
    value="<?php echo $result3{"subscriber_lname"}?>"
    onchange="capitalizeMe(this);" />
   <br>
   <span class=required><?php xl('Relationship','e'); ?>: </span>
   <select name=i<?php echo $i?>subscriber_relationship onchange="javascript:auto_populate_employer_address<?php echo $i?>();">
<?php
 foreach ($relats as $s) {
  if ($s == "unassigned") {
   echo "<option value=''";
  } else {
   echo "<option value='".$s."'";
  }
  if ($s == strtolower($result3['subscriber_relationship']))
   echo " selected";
  echo ">".ucfirst($s)."</option>\n";
}
?>
   </select>
   <a href="javascript:popUp('browse.php?browsenum=<?php echo $i?>')" class=text>(<?php xl('Browse','e'); ?>)</a><br />
   <span class=bold><?php xl('D.O.B.','e'); ?>: </span>
   <input type='entry' size='11' name='i<?php echo $i?>subscriber_DOB'
    value='<?php echo $result3['subscriber_DOB'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd' />
   <span class=bold><?php xl('S.S.','e'); ?>: </span><input type=entry size=11 name=i<?php echo $i?>subscriber_ss value="<?php echo $result3{"subscriber_ss"}?> ">&nbsp;
   <span class=bold><?php xl('Sex','e'); ?>: </span>
   <select name=i<?php echo $i?>subscriber_sex>
    <option value="Female" <?php if (strtolower($result3{"subscriber_sex"}) == "female") echo "selected"?>><?php xl('Female','e'); ?></option>
    <option value="Male" <?php if (strtolower($result3{"subscriber_sex"}) == "male") echo "selected"?>><?php xl('Male','e'); ?></option>
   </select>
   <br>
   <span class=required><?php xl('Subscriber Address','e'); ?>: </span>
   <input type=entry size=25 name=i<?php echo $i?>subscriber_street
    value="<?php echo $result3{"subscriber_street"}?>"
    onchange="capitalizeMe(this);" /><br>
   <span class=required><?php xl('City','e'); ?>: </span>
   <input type=entry size=15 name=i<?php echo $i?>subscriber_city
    value="<?php echo $result3{"subscriber_city"}?>"
    onchange="capitalizeMe(this);" />
   <span class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? 'State' : 'Locality' ?>: </span><input type=entry size=15 name=i<?php echo $i?>subscriber_state value="<?php echo $result3{"subscriber_state"}?>"><br>
   <span class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? 'Zip' : 'Postal' ?> <?php xl('Code','e'); ?>: </span><input type=entry size=10 name=i<?php echo $i?>subscriber_postal_code value="<?php echo $result3{"subscriber_postal_code"}?>">
   <span class='required'<?php if ($GLOBALS['omit_employers']) echo " style='display:none'"; ?>>
   <?php xl('Country','e'); ?>:
   <input type=entry size=10 name=i<?php echo $i?>subscriber_country
    value="<?php echo $result3{"subscriber_country"}?>"
    onchange="capitalizeMe(this);" /><br></span>
   <span class=bold><?php xl('Subscriber Phone','e'); ?>: 
   <input type='text' size='20' name='i<?php echo $i?>subscriber_phone' value='<?php echo $result3["subscriber_phone"] ?>' onkeyup='phonekeyup(this,mypcc)' />
   </span><br />
   <span class=bold><?php xl('CoPay','e'); ?>: <input type=text size="6" name=i<?php echo $i?>copay value="<?php echo $result3{"copay"}?>">
  </td>
 </tr>
</table>

<?php
    if ($i < 3) echo "<hr />\n";
  } //end insurer for loop
  echo "</div>\n";
 } // end of "if not simplified_demographics"
?>

<center><br />
<a href="javascript:submitme();"
 class='link_submit'>[<?php xl('Save Patient Demographics','e'); ?>]</a>
</center>

</form>

<br>

<script language="JavaScript">

 // fix inconsistently formatted phone numbers from the database
 var f = document.forms[0];
 if (f.form_phone_contact) phonekeyup(f.form_phone_contact,mypcc);
 if (f.form_phone_home   ) phonekeyup(f.form_phone_home   ,mypcc);
 if (f.form_phone_biz    ) phonekeyup(f.form_phone_biz    ,mypcc);
 if (f.form_phone_cell   ) phonekeyup(f.form_phone_cell   ,mypcc);

<?php if (! $GLOBALS['simplified_demographics']) { ?>
 phonekeyup(f.i1subscriber_phone,mypcc);
 phonekeyup(f.i2subscriber_phone,mypcc);
 phonekeyup(f.i3subscriber_phone,mypcc);
<?php } ?>

<?php if ($GLOBALS['concurrent_layout'] && $set_pid) { ?>
 parent.left_nav.setPatient(<?php echo "'" . $result['fname'] . " " . $result['lname'] . "',$pid,''"; ?>);
 parent.left_nav.setRadio(window.name, 'dem');
<?php } ?>

</script>

</body>
</html>
