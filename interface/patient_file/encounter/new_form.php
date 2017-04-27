<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once $GLOBALS['srcdir'].'/ESign/Api.php';

use OpenEMR\Encounter\Services\ViewHelper;

$esignApi = new Esign\Api();

$assets_dir = $GLOBALS['assets_static_relative'];
?>
<?php if (empty($hide)) { // if not included by forms.php ?>
<html>
<head>
<?php } ?>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $assets_dir;?>/bootstrap-3-3-4/dist/css/bootstrap.css" type="text/css">
<link rel="stylesheet" href="<?php echo $assets_dir;?>/font-awesome-4-6-3/css/font-awesome.css" type="text/css">

<script type="text/javascript" src="<?php echo $assets_dir;?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $assets_dir;?>/bootstrap-3-3-4/dist/js/bootstrap.js"></script>
<script language="JavaScript">

function openNewForm(sel) {
  top.restoreSession();
  FormNameValueArray = sel.split('formname=');
  if(FormNameValueArray[1] == 'newpatient' || FormNameValueArray[1] == 'newGroupEncounter')
  {
    parent.location.href = sel;
  }
  else if (!parent.Forms)
  {
    location.href = sel;
  }
  else
  {
    parent.Forms.location.href = sel;
  }
}

function toggleFrame1(fnum) {
  top.frames['left_nav'].document.forms[0].cb_top.checked=false;
  top.window.parent.left_nav.toggleFrame(fnum);
 }
</script>
<script type="text/javascript" language="javascript">
// @todo This whole thing can probably be deleted as it seems to only relate to the old menu. RD 2017-04-21
var timeout	= 500;
var closetimer	= 0;
var ddmenuitem	= 0;
var oldddmenuitem = 0;
var flag = 0;

// open hidden layer
function mopen(id)
{
	// cancel close timer
	//mcancelclosetime();

	flag=10;

	// close old layer
	//if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
	//if(ddmenuitem) ddmenuitem.style.display = 'none';

	// get new layer and show it
        oldddmenuitem = ddmenuitem;
	ddmenuitem = document.getElementById(id);
        if((ddmenuitem.style.visibility == '')||(ddmenuitem.style.visibility == 'hidden')){
            if(oldddmenuitem) oldddmenuitem.style.visibility = 'hidden';
            if(oldddmenuitem) oldddmenuitem.style.display = 'none';
            ddmenuitem.style.visibility = 'visible';
            ddmenuitem.style.display = 'block';
        }else{
            ddmenuitem.style.visibility = 'hidden';
            ddmenuitem.style.display = 'none';
        }
}
// close showed layer
function mclose()
{
	if(flag==10)
	 {
	  flag=11;
	  return;
	 }
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
	if(ddmenuitem) ddmenuitem.style.display = 'none';
}

// close layer when click-out
document.onclick = mclose;
//=================================================
function findPosX(id)
  {
    obj=document.getElementById(id);
	var curleft = 0;
    if(obj.offsetParent)
        while(1)
        {
          curleft += obj.offsetLeft;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.x)
        curleft += obj.x;
   PropertyWidth=document.getElementById(id).offsetWidth;
   if(PropertyWidth>curleft)
    {
	 document.getElementById(id).style.left=0;
	}
  }

  function findPosY(obj)
  {
    var curtop = 0;
    if(obj.offsetParent)
        while(1)
        {
          curtop += obj.offsetTop;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.y)
        curtop += obj.y;
    return curtop;
  }
</script>

</head>
<body class="bgcolor2" style="padding-top:45px;">
<dl>
<?php //DYNAMIC FORM RETREIVAL
include_once("$srcdir/registry.inc");

$old_category = '';

$menuItems = ViewHelper::parseRegistry(ViewHelper::getRegistry());

// Push this static element to the menu list
$encounterSummary = array(
    'name' => 'Encounter Summary',
    'href' => '#',
);
if (isset($hide)) {
    $encounterSummary['href'] = 'enc2';
} else {
    if ($GLOBALS['new_tabs_layout']) {
        $encounterSummaryLoadFrame = 'loadFrame';
        $framePosition = 'enc';
    } else {
        $encounterSummaryLoadFrame = 'loadFrame2';
        $framePosition = 'RBot';
    }
}
array_unshift($menuItems, $encounterSummary);

// Get the layout based forms and push it to the menu
$lbfItems = ViewHelper::getLayoutBasedForms();
if ($lbfItems) {
    $menuItems[] = $lbfItems;
}

$menu = ViewHelper::createEncounterMenu($menuItems);
?>

<nav class="nav navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu-encounter" aria-expanded="false">
                <span class="sr-only"><?php echo xlt("Toggle Navigation");?></span>
                <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand" href="#"><?php echo oeFormatShortDate($encounter_date);?> <?php echo xlt("Encounter");?> <?php echo xlt("for");?> <?php echo $patientName;?></a>
        </div>

        <div class="collapse navbar-collapse" id="menu-encounter">
            <ul class="nav navbar-nav">
                <?php echo $menu;?>
            </ul>
        </div>
    </div>
</nav>
<?php
$reg = ViewHelper::getRegistry();

  $DivId=1;

// To see if the encounter is locked. If it is, no new forms can be created
$encounterLocked = false;
if ($esignApi->lockEncounters() && isset($GLOBALS['encounter']) && !empty($GLOBALS['encounter'])) {
    $esign = $esignApi->createEncounterESign($GLOBALS['encounter']);
    if ($esign->isLocked()) {
        $encounterLocked = true;
    }
}

$old_category = "";
$new_category = "";

if (!empty($reg)) {
  $StringEcho= '<ul id="sddm">';
  if(isset($hide)){
    $StringEcho.= "<li><a id='enc2' >" . htmlspecialchars( xl('Encounter Summary'),ENT_NOQUOTES) . "</a></li>";
  } else {
        if ($GLOBALS['new_tabs_layout']) {
            $encounterSummaryLoadFrame = 'loadFrame';
            $framePosition = 'enc';
        } else {
            $encounterSummaryLoadFrame = 'loadFrame2';
            $framePosition = 'RBot';
        }
  }
  if ($encounterLocked === false) {
      foreach ($reg as $entry) {
        if ($old_category != $new_category) {
          $StringEcho.= "<li class=\"encounter-form-category-li\"><a href='JavaScript:void(0);' onClick=\"mopen('$DivId');\" >$new_category</a><div id='$DivId' ><table border='0' cellspacing='0' cellpadding='0'>";
          $old_category = $new_category;
          $DivId++;
        }
        $StringEcho.= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=\"openNewForm('" . $rootdir .'/patient_file/encounter/load_form.php?formname=' .urlencode($entry['directory']) .
        "')\" href='JavaScript:void(0);'>" . xl_form_title($nickname) . "</a></td></tr>";
      }
  }
  $StringEcho.= '</table></div></li>';
}

if($StringEcho){
  $StringEcho2= '<div style="clear:both"></div>';
}else{
  $StringEcho2="";
}
?>

<!-- DISPLAYING HOOKS STARTS HERE -->
<?php
//  $DivId = 'mod_installer';
//  if (sqlNumRows($module_query)) {
//    $jid = 0;
//    $modid = '';
//    while ($modulerow = sqlFetchArray($module_query)) {
//      $DivId = 'mod_'.$modulerow['mod_id'];
//      if($jid==0 || ($modid!=$modulerow['mod_id'])){
//        if($modid!='')
//        $StringEcho.= '</table></div></li>';
//      $StringEcho.= "<li><a href='JavaScript:void(0);' onClick=\"mopen('$DivId');\" >$new_category</a><div id='$DivId' ><table border='0' cellspacing='0' cellpadding='0'>";
//      }
//      $jid++;
//      $modid = $modulerow['mod_id'];
//      $StringEcho.= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=\"openNewForm('$relative_link')\" href='JavaScript:void(0);'>" . xl_form_title($nickname) . "</a></td></tr>";
//   }
//  }
	?>
<table cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td valign="top"><?php echo $StringEcho; ?></td>
  </tr>
</table>
</dl>
<?php if (empty($hide)) { ?>
</body>
</html>
<?php } ?>
