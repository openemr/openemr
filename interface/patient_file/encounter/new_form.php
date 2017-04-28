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
$(document).ready(function(){

    $("ul.nav").on('click', 'a.menu-item-action', function(e) {
        e.preventDefault();
        if (parent.Forms) {
            parent.location.href = $(this).attr('href');
        } else {
            location.href = $(this).attr('href');
        }
    });

});


function openNewForm(sel) {
  top.restoreSession();
  FormNameValueArray = sel.split('formname=');
  if(FormNameValueArray[1] == 'newpatient' || FormNameValueArray[1] == 'newGroupEncounter')
  {
    parent.location.href = sel;
  } else if (!parent.Forms) {
    location.href = sel;
  } else {
    parent.Forms.location.href = sel;
  }
}

function toggleFrame1(fnum) {
  top.frames['left_nav'].document.forms[0].cb_top.checked=false;
  top.window.parent.left_nav.toggleFrame(fnum);
 }
</script>
</head>
<body class="bgcolor2" style="padding-top:45px;">
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
    $encounterSummary['href'] = '#';
    $clickStr = "return top.window.parent.left_nav.loadFrame('enc2', 'enc', 'patient_file/encounter/encounter_top.php')";
    $encounterSummary['atts']['onclick'] = $clickStr;
} else {
    if ($GLOBALS['new_tabs_layout']) {
        $encounterSummaryLoadFrame = 'loadFrame';
        $framePosition = 'enc';
    } else {
        $encounterSummaryLoadFrame = 'loadFrame2';
        $framePosition = 'RBot';
    }
    $clickStr = "return top.window.parent.left_nav.{$encounterSummaryLoadFrame}('enc2', '{$framePosition}', 'patient_file/encounter/encounter_top.php')";
    $encounterSummary['atts']['onclick'] = $clickStr;
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

            <?php if (acl_check('admin', 'super')): ?>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="toggledivs(this.id, this.id)" onclick="return deleteme()"><i class="fa fa-times"></i>&nbsp;<?php echo xl("Delete Encounter");?></a>
                </li>
            </ul>
            <?php endif; ?>
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
?>

<?php if (empty($hide)) { ?>
</body>
</html>
<?php } ?>
