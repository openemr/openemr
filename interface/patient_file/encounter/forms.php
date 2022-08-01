<?php

/**
 * forms.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/group.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/amc.php");
require_once($GLOBALS['srcdir'] . '/ESign/Api.php');
require_once("$srcdir/../controllers/C_Document.class.php");

use ESign\Api;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\Encounter\EncounterMenuEvent;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcher;

$expand_default = (int)$GLOBALS['expand_form'] ? 'show' : 'hide';
$reviewMode = false;
if (!empty($_REQUEST['review_id'])) {
    $reviewMode = true;
    $encounter = sanitizeNumber($_REQUEST['review_id']);
}

$is_group = ($attendant_type == 'gid') ? true : false;
if ($attendant_type == 'gid') {
    $groupId = $therapy_group;
}
$attendant_id = $attendant_type == 'pid' ? $pid : $therapy_group;
if ($is_group && !AclMain::aclCheckCore("groups", "glog", false, array('view','write'))) {
    echo xlt("access not allowed");
    exit();
}

if ($GLOBALS['kernel']->getEventDispatcher() instanceof EventDispatcher) {
    /**
     * @var EventDispatcher
     */
    $eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
} else {
    throw new Exception("Could not get EventDispatcher from kernel", 1);
}

?>
<!DOCTYPE html>
<html>

<head>

<?php require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; ?>

<?php Header::setupHeader(['common','esign','dygraphs']); ?>

<?php
$esignApi = new Api();
?>

<?php // if the track_anything form exists, then include the styling and js functions (and js variable) for graphing
if (file_exists(dirname(__FILE__) . "/../../forms/track_anything/style.css")) { ?>
 <script>
 var csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
 </script>
 <script src="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/report.js"></script>
 <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/style.css">
<?php } ?>

<?php
// If the user requested attachment of any orphaned procedure orders, do it.
if (!empty($_GET['attachid'])) {
    $attachid = explode(',', $_GET['attachid']);
    foreach ($attachid as $aid) {
        $aid = intval($aid);
        if (!$aid) {
            continue;
        }
        $tmp = sqlQuery(
            "SELECT COUNT(*) AS count FROM procedure_order WHERE " .
            "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
            array($aid, $pid)
        );
        if (!empty($tmp['count'])) {
              sqlStatement(
                  "UPDATE procedure_order SET encounter_id = ? WHERE " .
                  "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
                  array($encounter, $aid, $pid)
              );
              addForm($encounter, "Procedure Order", $aid, "procedure_order", $pid, $userauthorized);
        }
    }
}
?>

<script>
$(function () {
    var formConfig = <?php echo $esignApi->formConfigToJson(); ?>;
    $(".esign-button-form").esign(
        formConfig,
        {
            afterFormSuccess : function( response ) {
                if ( response.locked ) {
                    var editButtonId = "form-edit-button-"+response.formDir+"-"+response.formId;
                    $("#"+editButtonId).replaceWith( response.editButtonHtml );
                }

                var logId = "esign-signature-log-"+response.formDir+"-"+response.formId;
                $.post( formConfig.logViewAction, response, function( html ) {
                    $("#"+logId).replaceWith( html );
                });
            }
        }
    );

    var encounterConfig = <?php echo $esignApi->encounterConfigToJson(); ?>;
    $(".esign-button-encounter").esign(
        encounterConfig,
        {
            afterFormSuccess : function( response ) {
                // If the response indicates a locked encounter, replace all
                // form edit buttons with a "disabled" button, and "disable" left
                // nav visit form links
                if ( response.locked ) {
                    // Lock the form edit buttons
                    $(".form-edit-button").replaceWith( response.editButtonHtml );
                    // Disable the new-form capabilities in left nav
                    top.window.parent.left_nav.syncRadios();
                    // Disable the new-form capabilities in top nav of the encounter
                    $(".encounter-form-category-li").remove();
                }

                var logId = "esign-signature-log-encounter-"+response.encounterId;
                $.post( encounterConfig.logViewAction, response, function( html ) {
                    $("#"+logId).replaceWith( html );
                });
            }
        }
    );

    $("#prov_edu_res").click(function() {
        if ( $('#prov_edu_res').prop('checked') ) {
            var mode = "add";
        }
        else {
            var mode = "remove";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "patient_edu_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo js_escape($pid); ?>,
              object_category: "form_encounter",
              object_id: <?php echo js_escape($encounter); ?>,
              csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            }
        );
    });

    $("#provide_sum_pat_flag").click(function() {
        if ( $('#provide_sum_pat_flag').prop('checked') ) {
            var mode = "add";
        }
        else {
            var mode = "remove";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "provide_sum_pat_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo js_escape($pid); ?>,
              object_category: "form_encounter",
              object_id: <?php echo js_escape($encounter); ?>,
              csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            }
        );
    });

    $("#trans_trand_care").click(function() {
        if ( $('#trans_trand_care').prop('checked') ) {
            var mode = "add";
            // Enable the reconciliation checkbox
            $("#med_reconc_perf").removeAttr("disabled");
        $("#soc_provided").removeAttr("disabled");
        }
        else {
            var mode = "remove";
            //Disable the reconciliation checkbox (also uncheck it if applicable)
            $("#med_reconc_perf").attr("disabled", true);
            $("#med_reconc_perf").prop("checked",false);
        $("#soc_provided").attr("disabled",true);
        $("#soc_provided").prop("checked",false);
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "med_reconc_amc",
              complete: false,
              mode: mode,
              patient_id: <?php echo js_escape($pid); ?>,
              object_category: "form_encounter",
              object_id: <?php echo js_escape($encounter); ?>,
              csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            }
        );
    });

    $("#med_reconc_perf").click(function() {
        if ( $('#med_reconc_perf').prop('checked') ) {
            var mode = "complete";
        }
        else {
            var mode = "uncomplete";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "med_reconc_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo js_escape($pid); ?>,
              object_category: "form_encounter",
              object_id: <?php echo js_escape($encounter); ?>,
              csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            }
        );
    });
    $("#soc_provided").click(function(){
        if($('#soc_provided').prop('checked')){
                var mode = "soc_provided";
        }
        else{
                var mode = "no_soc_provided";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
                { amc_id: "med_reconc_amc",
                complete: true,
                mode: mode,
                patient_id: <?php echo js_escape($pid); ?>,
                object_category: "form_encounter",
                object_id: <?php echo js_escape($encounter); ?>,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                }
        );
    });

     $(".deleteme").click(function(evt) { deleteme(); evt.stopPropogation(); });

<?php
  // If the user was not just asked about orphaned orders, build javascript for that.
if (!isset($_GET['attachid'])) {
    $ares = sqlStatement(
        "SELECT procedure_order_id, date_ordered " .
        "FROM procedure_order WHERE " .
        "patient_id = ? AND encounter_id = 0 AND activity = 1 " .
        "ORDER BY procedure_order_id",
        array($pid)
    );
    echo "  // Ask about attaching orphaned orders to this encounter.\n";
    echo "  var attachid = '';\n";
    while ($arow = sqlFetchArray($ares)) {
        $orderid   = $arow['procedure_order_id'];
        $orderdate = $arow['date_ordered'];
        echo "  if (confirm(" . xlj('There is a lab order') . " + ' ' + " . js_escape($orderid) . " + ' ' + " .
        xlj('dated') . " + ' ' + " . js_escape($orderdate) .  " + ' ' + " .
        xlj('for this patient not yet assigned to any encounter.') . " + ' ' + " .
        xlj('Assign it to this one?') . ")) attachid += " . js_escape($orderid . ",") . ";\n";
    }
    echo "  if (attachid) location.href = 'forms.php?attachid=' + encodeURIComponent(attachid);\n";
}
?>

    <?php if ($reviewMode) { ?>
        $("body table:first").hide();
        $(".encounter-summary-column").hide();
        $(".btn").hide();
        $(".encounter-summary-column:first").show();
        $(".title:first").text(<?php echo xlj("Review"); ?> + " " + $(".title:first").text() + " ( " + <?php echo js_escape($encounter); ?> + " )");
    <?php } ?>
});

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?encounterid=' + <?php echo js_url($encounter); ?> + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 200, '', '', {
      allowResize: false,
      allowDrag: true,
  });
  return false;
 }


// create new follow-up Encounter.
function createFollowUpEncounter() {

    <?php
    $result = sqlQuery("SELECT * FROM form_encounter WHERE pid = ? AND encounter = ?", array(
        $_SESSION['pid'],
        $encounter
    ));
    $encounterId = (!empty($result['parent_encounter_id'])) ? $result['parent_encounter_id'] : $result['id'];
    ?>
    var data = {encounterId: '<?php echo attr($encounterId); ?>', mode: 'follow_up_encounter'};
    top.window.parent.newEncounter(data);
}


 // Called by the deleter.php window on a successful delete.
function imdeleted(EncounterId) {
    top.window.parent.left_nav.removeOptionSelected(EncounterId);
    top.window.parent.left_nav.clearEncounter();
    top.encounterList();
}

// Called to open the data entry form a specified encounter form instance.
function openEncounterForm(formdir, formname, formid) {
  var url = <?php echo js_escape($rootdir); ?> + '/patient_file/encounter/view_form.php?formname=' +
      encodeURIComponent(formdir) + '&id=' + encodeURIComponent(formid);
  if (formdir == 'newpatient' || !parent.twAddFrameTab) {
    top.restoreSession();
    location.href = url;
  }
  else {
    parent.twAddFrameTab('enctabs', formname, url);
  }
  return false;
}

// Called when an encounter form may changed something that requires a refresh here.
function refreshVisitDisplay() {
  location.href = <?php echo js_escape($rootdir); ?> + '/patient_file/encounter/forms.php';
}

</script>

<style>
    div.tab {
        min-height: 50px;
        padding: 8px;
    }

    div.form_header {
        float: left;
        min-width: 400px;
    }

    div.form_header_controls {
        float: left;
        margin-bottom: 2px;
        margin-left: 6px;
    }

    div.formname {
        float: left;
        min-width: 160px;
        padding: 0;
        margin: 0;
    }

    .encounter-summary-container {
        float: left;
        width: 100%;
    }

    .encounter-summary-column {
        width: 33.3%;
        float: left;
        display: inline;
        margin-top: 10px;
    }

    #sddm {
        margin: 0;
        padding: 0;
        z-index: 30;
    }

    button:focus {
        outline: none;
    }

    button::-moz-focus-inner {
        border: 0;
    }
</style>

<!-- *************** -->
<!-- Form menu start -->
<script>

function openNewForm(sel, label) {
  top.restoreSession();
  var FormNameValueArray = sel.split('formname=');
  if (FormNameValueArray[1] == 'newpatient') {
    // TBD: Make this work when it's not the first frame.
    parent.frames[0].location.href = sel;
  }
  else {
    parent.twAddFrameTab('enctabs', label, sel);
  }
}

function toggleFrame1(fnum) {
  top.frames['left_nav'].document.forms[0].cb_top.checked=false;
  top.window.parent.left_nav.toggleFrame(fnum);
}
</script>
<script>

var timeout = 500;
var closetimer  = 0;
var ddmenuitem  = 0;
var oldddmenuitem = 0;
var flag = 0;

// open hidden layer
function mopen(id) {
    // cancel close timer
    //mcancelclosetime();

    flag = 10;

    // close old layer
    //if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
    //if(ddmenuitem) ddmenuitem.style.display = 'none';

    // get new layer and show it
    oldddmenuitem = ddmenuitem;
    ddmenuitem = document.getElementById(id);
    if ((ddmenuitem.style.visibility == '')||(ddmenuitem.style.visibility == 'hidden')){
        if (oldddmenuitem) {
            oldddmenuitem.style.visibility = 'hidden';
            oldddmenuitem.style.display = 'none';
        }
        ddmenuitem.style.visibility = 'visible';
        ddmenuitem.style.display = 'block';
    } else {
        ddmenuitem.style.visibility = 'hidden';
        ddmenuitem.style.display = 'none';
    }
}

// close showed layer
function mclose() {
    if (flag === 10) {
        flag = 11;
        return;
    }
    if (ddmenuitem) {
        ddmenuitem.style.visibility = 'hidden';
        ddmenuitem.style.display = 'none';
    }
}

// close layer when click-out
document.onclick = mclose;
//=================================================
function findPosX(id) {
    obj = document.getElementById(id);
    var curleft = 0;
    if(obj.offsetParent) {
        while(1) {
          curleft += obj.offsetLeft;
          if (!obj.offsetParent) {
            break;
          }
          obj = obj.offsetParent;
        }
    } else if(obj.x) {
        curleft += obj.x;
    }

    PropertyWidth = document.getElementById(id).offsetWidth;
    if (PropertyWidth > curleft) {
        document.getElementById(id).style.left = 0;
    }
}

function findPosY(obj) {
    var curtop = 0;
    if(obj.offsetParent) {
        while(1) {
            curtop += obj.offsetTop;
            if(!obj.offsetParent) {
                break;
            }
            obj = obj.offsetParent;
        }
    } else if (obj.y) {
        curtop += obj.y;
    }
    return curtop;
  }
</script>

</head>
<body>
<nav>
<?php //DYNAMIC FORM RETREIVAL
include_once("$srcdir/registry.inc");

$reg = getFormsByCategory();
$old_category = '';
$DivId = 1;

// To see if the encounter is locked. If it is, no new forms can be created
$encounterLocked = false;
if (
    $esignApi->lockEncounters() &&
    isset($GLOBALS['encounter']) &&
    !empty($GLOBALS['encounter'])
) {
    $esign = $esignApi->createEncounterESign($GLOBALS['encounter']);
    if ($esign->isLocked()) {
        $encounterLocked = true;
    }
}


// Convert the flat list of menu items into a multi-dimensional array based on the category
// decide what will be displayed (name or nickname)
$eventDispatcher->addListener(EncounterMenuEvent::MENU_RENDER, function (EncounterMenuEvent $menuEvent) {
    $menuArray = $menuEvent->getMenuData();
    $reg = getFormsByCategory();
    foreach ($reg as $item) {
        $tmp = explode('|', $item['aco_spec']);
        if (!empty($tmp[1])) {
            if (!AclMain::aclCheckCore($tmp[0], $tmp[1], '', 'write') && !AclMain::aclCheckCore($tmp[0], $tmp[1], '', 'addonly')) {
                continue;
            }
        }

        $_cat = trim($item['category']);
        $_cat = ($_cat == '') ? xl("Miscellaneous") : xl($_cat);
        $item['displayText'] = (trim($item['nickname'] ?? '') != '') ? trim($item['nickname'] ?? '') : trim($item['name'] ?? '');
        unset($item['category']);
        unset($item['name']);
        unset($item['nickname']);
        $menuArray[$_cat]['children'][] = $item;
    }
    $menuEvent->setMenuData($menuArray);
    return $menuEvent;
});


// Zend Module hooks
$eventDispatcher->addListener(EncounterMenuEvent::MENU_RENDER, function (EncounterMenuEvent $menuEvent) {
    $module_query = sqlStatement("SELECT msh.*, ms.menu_name, ms.path, m.mod_ui_name, m.type
        FROM modules_hooks_settings AS msh
        LEFT OUTER JOIN modules_settings AS ms ON obj_name=enabled_hooks AND ms.mod_id=msh.mod_id
        LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
        WHERE fld_type=3
            AND mod_active=1
            AND sql_run=1
            AND attached_to='encounter'
        ORDER BY mod_id");

    $menuData = $menuEvent->getMenuData();

    while ($row = sqlFetchArray($module_query)) {
        $_cat = $row['mod_ui_name'];
        if ($row['type'] == 0) {
            $modulePath = $GLOBALS['customModDir'];
            $added = "";
        } else {
            $modulePath = $GLOBALS['zendModDir'];
            $added = "index";
        }

        $relativeLink = "../../modules/{$modulePath}/public/{$row['path']}";
        $row['menu_name'] = $row['menu_name'] ?: "No_Name";
        $row['href'] = $relativeLink;
        $menuData[$_cat]['children'][] = $row;
    }

    $menuEvent->setMenuData($menuData);
    return $menuEvent;
});

$dateres = getEncounterDateByEncounter($encounter);
$encounter_date = date("Y-m-d", strtotime($dateres["date"]));
$providerIDres = getProviderIdOfEncounter($encounter);
$providerNameRes = getProviderName($providerIDres, false);

// Check for group encounter
if ($attendant_type == 'pid' && is_numeric($pid)) {
    $groupEncounter = false;
    $result = getPatientData($pid, "fname,lname,squad");
    $patientName = getPatientFullNameAsString($pid);
} else {
    $groupEncounter = true;
    $result = getGroup($groupId);
    $patientName = $result['group_name'];
}

// Check for no access to the patient's squad.
if ($result['squad'] && !AclMain::aclCheckCore('squads', $result['squad'])) {
    $pass_sens_squad = false;
}

$encounterMenuEvent = new EncounterMenuEvent();
$menu = $eventDispatcher->dispatch($encounterMenuEvent, EncounterMenuEvent::MENU_RENDER);

$twig = new TwigContainer(null, $GLOBALS['kernel']);
$t = $twig->getTwig();
echo $t->render('encounter/forms/navbar.html.twig', [
    'encounterDate' => oeFormatShortDate($encounter_date),
    'patientName' => $patientName,
    'isAdminSuper' => AclMain::aclCheckCore("admin", "super"),
    'enableFollowUpEncounters' => $GLOBALS['enable_follow_up_encounters'],
    'menuArray' => $menu->getMenuData(),
]);
?>

<div id="encounter_forms" class="mx-1">
<div class='encounter-summary-container'>
    <div class='encounter-summary-column'>
        <div>
            <?php
            $pass_sens_squad = true;
            //fetch acl for category of given encounter
            $pc_catid = fetchCategoryIdByEncounter($encounter);
            $postCalendarCategoryACO = AclMain::fetchPostCalendarCategoryACO($pc_catid);
            if ($postCalendarCategoryACO) {
                $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
                $authPostCalendarCategory = AclMain::aclCheckCore($postCalendarCategoryACO[0], $postCalendarCategoryACO[1]);
                $authPostCalendarCategoryWrite = AclMain::aclCheckCore($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write');
            } else { // if no aco is set for category
                $authPostCalendarCategory = true;
                $authPostCalendarCategoryWrite = true;
            }

            // Check for no access to the encounter's sensitivity level.
            $sensitivity = (new EncounterService())->getSensitivity($pid, $encounter);
            if (($sensitivity && !AclMain::aclCheckCore('sensitivities', $sensitivity)) || (!$authPostCalendarCategory ?? '')) {
                $pass_sens_squad = false;
            }
            ?>
        </div>
        <div style='margin-top: 8px;'>
            <?php
            // ESign for entire encounter
            $esign = $esignApi->createEncounterESign($encounter);
            if ($esign->isButtonViewable()) {
                echo $esign->buttonHtml();
            }
            ?>

        </div>
    </div>
<div class='encounter-summary-column'>
<?php if ($esign->isLogViewable()) {
    $esign->renderLog();
} ?>
</div>

<div class='encounter-summary-column'>
<?php if ($GLOBALS['enable_amc_prompting']) { ?>
    <div class="float-right border border-dark mr-2">
        <div class="float-left m-2">
          <table>
            <tr>
              <td>
              <?php // Display the education resource checkbox (AMC prompting)
                  $itemAMC = amcCollect("patient_edu_amc", $pid, 'form_encounter', $encounter);
                ?>
              <?php if (!(empty($itemAMC))) { ?>
                  <input type="checkbox" id="prov_edu_res" checked />
              <?php } else { ?>
                  <input type="checkbox" id="prov_edu_res" />
              <?php } ?>
            </td>
            <td>
                <span class="text"><?php echo xlt('Provided Education Resource(s)?') ?></span>
            </td>
            </tr>
            <tr>
            <td>
            <?php // Display the Provided Clinical Summary checkbox (AMC prompting)
                $itemAMC = amcCollect("provide_sum_pat_amc", $pid, 'form_encounter', $encounter);
            ?>
            <?php if (!(empty($itemAMC))) { ?>
                <input type="checkbox" id="provide_sum_pat_flag" checked />
            <?php } else { ?>
                <input type="checkbox" id="provide_sum_pat_flag" />
            <?php } ?>
            </td>
            <td>
                <span class="text"><?php echo xlt('Provided Clinical Summary?') ?></span>
            </td>
            </tr>
            <?php // Display the medication reconciliation checkboxes (AMC prompting)
                $itemAMC = amcCollect("med_reconc_amc", $pid, 'form_encounter', $encounter);
            ?>
            <?php if (!(empty($itemAMC))) { ?>
                <tr>
                    <td>
                        <input type="checkbox" id="trans_trand_care" checked />
                    </td>
                    <td>
                        <span class="text"><?php echo xlt('Transition/Transfer of Care?') ?></span>
                    </td>
                </tr>
                </table>
                <table class="ml-4">
                    <tr>
                        <td>
                            <?php if (!(empty($itemAMC['date_completed']))) { ?>
                                <input type="checkbox" id="med_reconc_perf" checked />
                            <?php } else { ?>
                                <input type="checkbox" id="med_reconc_perf" />
                            <?php } ?>
                        </td>
                        <td>
                            <span class="text"><?php echo xlt('Medication Reconciliation Performed?') ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <?php if (!(empty($itemAMC['soc_provided']))) { ?>
                            <input type="checkbox" id="soc_provided" checked />
                        <?php } else { ?>
                            <input type="checkbox" id="soc_provided" />
                        <?php } ?>
                        </td>
                        <td>
                            <span class="text"><?php echo xlt('Summary Of Care Provided?') ?></span>
                        </td>
                    </tr>
            </table>
            <?php } else { ?>
                <tr>
                    <td>
                        <input type="checkbox" id="trans_trand_care" />
                    </td>
                    <td>
                        <span class="text"><?php echo xlt('Transition/Transfer of Care?') ?></span>
                    </td>
                </tr>
                </table>
                <table class="ml-4">
                    <tr>
                        <td>
                            <input type="checkbox" id="med_reconc_perf" disabled />
                        </td>
                        <td>
                            <span class="text"><?php echo xlt('Medication Reconciliation Performed?') ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" id="soc_provided" disabled />
                        </td>
                        <td>
                            <span class="text"><?php echo xlt('Summary of Care Provided?') ?></span>
                        </td>
                    </tr>
                </table>
            <?php } ?>
        </div>
    </div>
<?php } ?>
</div>

</div>

<!-- Get the documents tagged to this encounter and display the links and notes as the tooltip -->
<?php
if ($attendant_type == 'pid') {
    $docs_list = getDocumentsByEncounter($pid, $_SESSION['encounter']);
} else {
    // already doesn't exist document for therapy groups
    $docs_list = array();
}
if (!empty($docs_list) && count($docs_list) > 0) {
    ?>
<div class='enc_docs'>
<span class="font-weight-bold"><?php echo xlt("Document(s)"); ?>:</span>
    <?php
    $doc = new C_Document();
    foreach ($docs_list as $doc_iter) {
        $doc_url = $doc->_tpl_vars['CURRENT_ACTION'] . "&view&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_iter['id']) . "&";
        // Get notes for this document.
        $queryString = "SELECT GROUP_CONCAT(note ORDER BY date DESC SEPARATOR '|') AS docNotes, GROUP_CONCAT(date ORDER BY date DESC SEPARATOR '|') AS docDates
			FROM notes WHERE foreign_id = ? GROUP BY foreign_id";
        $noteData = sqlQuery($queryString, array($doc_iter['id']));
        $note = '';
        if ($noteData) {
            $notes = array();
            $notes = explode("|", $noteData['docNotes']);
            $dates = explode("|", $noteData['docDates']);
            for ($i = 0, $iMax = count($notes); $i < $iMax; $i++) {
                $note .= oeFormatShortDate(date('Y-m-d', strtotime($dates[$i]))) . " : " . $notes[$i] . "\n";
            }
        }
        ?>
<br />
<a href="<?php echo $doc_url;?>" style="font-size: small;" onsubmit="return top.restoreSession()"><?php echo text($doc_iter['document_name']) . ": " . text(basename($doc_iter['name']));?></a>
        <?php if ($note != '') {?>
            <a href="javascript:void(0);" title="<?php echo attr($note);?>"><img src="<?php echo $GLOBALS['images_static_relative']; ?>/info.png"/></a>
    <?php }?>
<?php } ?>
</div>
<?php } ?>
<br/>

<?php
if (
    $pass_sens_squad &&
    ($result = getFormByEncounter(
        $attendant_id,
        $encounter,
        "id, date, form_id, form_name, formdir, user, deleted",
        "",
        "FIND_IN_SET(formdir,'newpatient') DESC, form_name, date DESC"
    ))
) {
    echo "<table class='w-100' id='partable'>";
    $divnos = 1;
    foreach ($result as $iter) {
        $formdir = $iter['formdir'];

        // skip forms whose 'deleted' flag is set to 1
        if ($iter['deleted'] == 1) {
            continue;
        }

        $aco_spec = false;

        if (substr($formdir, 0, 3) == 'LBF') {
            // Skip LBF forms that we are not authorized to see.
            $lrow = sqlQuery(
                "SELECT grp_aco_spec " .
                "FROM layout_group_properties WHERE " .
                "grp_form_id = ? AND grp_group_id = '' AND grp_activity = 1",
                array($formdir)
            );
            if (!empty($lrow)) {
                if (!empty($lrow['grp_aco_spec'])) {
                    $aco_spec = explode('|', $lrow['grp_aco_spec']);
                    if (!AclMain::aclCheckCore($aco_spec[0], $aco_spec[1])) {
                        continue;
                    }
                }
            }
        } else {
          // Skip non-LBF forms that we are not authorized to see.
            $tmp = getRegistryEntryByDirectory($formdir, 'aco_spec');
            if (!empty($tmp['aco_spec'])) {
                $aco_spec = explode('|', $tmp['aco_spec']);
                if (!AclMain::aclCheckCore($aco_spec[0], $aco_spec[1])) {
                    continue;
                }
            }
        }

        // $form_info = getFormInfoById($iter['id']);
        if (strtolower(substr($iter['form_name'], 0, 5)) == 'camos') {
            //CAMOS generates links from report.php and these links should
            //be clickable without causing view.php to come up unexpectedly.
            //I feel that the JQuery code in this file leading to a click
            //on the report.php content to bring up view.php steps on a
            //form's autonomy to generate it's own html content in it's report
            //but until any other form has a problem with this, I will just
            //make an exception here for CAMOS and allow it to carry out this
            //functionality for all other forms.  --Mark
            echo '<tr title="' . xla('Edit form') . '" ' .
                  'id="' . attr($formdir) . '~' . attr($iter['form_id']) . '">';
        } else {
            echo '<tr id="' . attr($formdir) . '~' . attr($iter['form_id']) . '" class="text onerow">';
        }

        $acl_groups = AclMain::aclCheckCore("groups", "glog", false, 'write') ? true : false;
        $user = (new UserService())->getUserByUsername($iter['user']);

        $form_name = ($formdir == 'newpatient') ? xl('Visit Summary') : xl_form_title($iter['form_name']);

        // Create the ESign instance for this form
        $esign = $esignApi->createFormESign($iter['id'], $formdir, $encounter);

        // echo "<tr>"; // Removed as bug fix.

        echo "<td class='border-bottom border-dark'>";

        // Figure out the correct author (encounter authors are the '$providerNameRes', while other
        // form authors are the '$user['fname'] . "  " . $user['lname']').
        if ($formdir == 'newpatient') {
            $form_author = $providerNameRes;
        } else {
            $form_author = ($user['fname'] ?? '') . "  " . ($user['lname'] ?? '');
        }
        $div_nums_attr = attr($divnos);
        $title = xla("Expand/Collapse this form");
        $display = text($form_name) . " " . xlt("by") . " " . text($form_author);
        $author_text = text($form_author);
        $by_text = xlt("by");
        $form_text = text($form_name);
        echo <<<HTML
        <div class="form_header">
            <a href="#" data-toggle="collapse" data-target="#divid_{$div_nums_attr}" class="" id="aid_{$div_nums_attr}">
                <h5>{$form_text}</h5>
                {$by_text} {$author_text}
            </a>
        </div>
        <div class='form_header_controls btn-group' role='group'>
        HTML;

        // If the form is locked, it is no longer editable
        if ($esign->isLocked()) {
                 echo "<a href='#' class='btn btn-secondary btn-sm form-edit-button-locked' id='form-edit-button-" . attr($formdir) . "-" . attr($iter['id']) . "'><i class='fa fa-lock fa-fw'></i>&nbsp;" . xlt('Locked') . "</a>";
        } else {
            if (
                (!$aco_spec || AclMain::aclCheckCore($aco_spec[0], $aco_spec[1], '', 'write') and $is_group == 0 and $authPostCalendarCategoryWrite)
                or (((!$aco_spec || AclMain::aclCheckCore($aco_spec[0], $aco_spec[1], '', 'write')) and $is_group and AclMain::aclCheckCore("groups", "glog", false, 'write')) and $authPostCalendarCategoryWrite)
            ) {
                echo "<a class='btn btn-secondary btn-sm form-edit-button btn-edit' " .
                    "id='form-edit-button-" . attr($formdir) . "-" . attr($iter['id']) . "' " .
                    "href='#' " .
                    "title='" . xla('Edit this form') . "' " .
                    "onclick=\"return openEncounterForm(" . attr_js($formdir) . ", " .
                    attr_js($form_name) . ", " . attr_js($iter['form_id']) . ")\">";
                echo "" . xlt('Edit') . "</a>";
            }
        }

        if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and AclMain::aclCheckCore("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
            if (!$aco_spec || AclMain::aclCheckCore($aco_spec[0], $aco_spec[1], '', 'write')) {
                echo $esign->buttonHtml();
            }
        }

        if (substr($formdir, 0, 3) == 'LBF') {
          // A link for a nice printout of the LBF
            echo "<a target='_blank' " .
            "href='$rootdir/forms/LBF/printable.php?"   .
            "formname="   . attr_url($formdir)         .
            "&formid="    . attr_url($iter['form_id']) .
            "&visitid="   . attr_url($encounter)       .
            "&patientid=" . attr_url($pid)             .
            "' class='btn btn-secondary btn-sm' title='" . xla('Print this form') .
            "' onclick='top.restoreSession()'>" . xlt('Print') . "</a>";
        }

        if (AclMain::aclCheckCore('admin', 'super')) {
            if ($formdir != 'newpatient' && $formdir != 'newGroupEncounter') {
                // a link to delete the form from the encounter
                echo "<a href='$rootdir/patient_file/encounter/delete_form.php?" .
                    "formname=" . attr_url($formdir) .
                    "&id=" . attr_url($iter['id']) .
                    "&encounter=" . attr_url($encounter) .
                    "&pid=" . attr_url($pid) .
                    "' class='btn btn-danger btn-sm btn-delete' title='" . xla('Delete this form') . "' onclick='top.restoreSession()'>" . xlt('Delete') . "</a>";
            } else {
                // do not show delete button for main encounter here since it is displayed at top
            }
        }

        echo "<a class='btn btn-secondary btn-sm collapse-button-form' title='" . xla('Expand/Collapse this form') . "' data-toggle='collapse' data-target='#divid_" . attr($divnos) . "'>" . xlt('Expand / Collapse') . "</a>";
        echo "</div>\n"; // Added as bug fix.

        echo "</td>\n";
        echo "</tr>";
        echo "<tr>";
        echo "<td class='formrow'><div id='divid_" . attr($divnos) . "' class='mb-5 collapse " . attr($expand_default) . "' ";
        echo "class='tab " . ($divnos == 1 ? 'd-block' : 'd-none') . "'>";

        // Use the form's report.php for display.  Forms with names starting with LBF
        // are list-based forms sharing a single collection of code.
        //
        if (substr($formdir, 0, 3) == 'LBF') {
            include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
            lbf_report($attendant_id, $encounter, 2, $iter['form_id'], $formdir, true);
        } else {
            include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
            call_user_func($formdir . "_report", $attendant_id, $encounter, 2, $iter['form_id']);
        }

        if ($esign->isLogViewable()) {
            $esign->renderLog();
        }

        echo "</div></td></tr>";
        ++$divnos;
    }
    echo "</table>";
}
if (!$pass_sens_squad) {
    echo xlt("Not authorized to view this encounter");
}
?>

</div> <!-- end large encounter_forms DIV -->
</body>
</html>
