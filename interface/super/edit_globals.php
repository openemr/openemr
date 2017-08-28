<?php
/**
 * Script for the globals editor.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016-2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/globals.inc.php");
require_once("$srcdir/user.inc");
require_once(dirname(__FILE__)."/../../myportal/soap_service/portal_connectivity.php");

use OpenEMR\Admin\Service\AdminMenuBuilder;
use OpenEMR\Admin\Service\Globals;
use OpenEMR\Core\Header;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

$globalsService = new Globals($GLOBALS, new Filesystem());

global $GLOBALS_METADATA;
global $USER_SPECIFIC_TABS;
global $USER_SPECIFIC_GLOBALS;

$r = Request::createFromGlobals();
$userMode = ($r->query->get('mode') === 'user') ? true : false;

if (!$userMode) {
    if (!acl_check('admin', 'super')) {
        die(xlt('Not authorized'));
    }
}

$pageTitle = ($userMode) ? xlt("User Settings") : xlt("Global Settings");
$formAction = ($userMode) ? "?mode=user" : "";
?>
<!DOCTYPE html>
<html>
<head>
<?php
// If we are saving user_specific globals.
if ($userMode && $r->isMethod('post') && $r->request->get('form_save')):
    $globalsService->saveUserSettings($r, $GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_TABS);
?>
<script type='text/javascript'>
if (parent.left_nav.location) {
    parent.left_nav.location.reload();
    parent.Title.location.reload();
    if (self.name == 'RTop') {
        parent.RBot.location.reload();
    } else {
        parent.RTop.location.reload();
    }
}
self.location.href = 'edit_globals.php?mode=user&unique=yes';
</script>
<?php endif;

if (array_key_exists('form_download', $_POST) && $_POST['form_download']) {
    $client = portal_connection();
    try {
        $response = $client->getPortalConnectionFiles($credentials);
    } catch (SoapFault $e) {
        error_log('SoapFault Error');
        error_log(var_dump(get_object_vars($e)));
    } catch (Exception $e) {
        error_log('Exception Error');
        error_log(var_dump(get_object_vars($e)));
    }

    if (array_key_exists('status', $response) && $response['status'] == "1") {//WEBSERVICE RETURNED VALUE SUCCESSFULLY
        $tmpfilename  = realpath(sys_get_temp_dir())."/".date('YmdHis').".zip";
        $fp           = fopen($tmpfilename, "wb");
        fwrite($fp, base64_decode($response['value']));
        fclose($fp);
        $practice_filename    = $response['file_name'];//practicename.zip
        ob_clean();
        // Set headers
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$practice_filename);
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        // Read the file from disk
        readfile($tmpfilename);
        unlink($tmpfilename);
        exit;
    } else {//WEBSERVICE CALL FAILED AND RETURNED AN ERROR MESSAGE
        ob_end_clean();
        ?>
      <script type="text/javascript">
        alert('<?php echo xls('Offsite Portal web Service Failed').":\\n".text($response['value']);?>');
    </script>
        <?php
    }
}

// If we are saving main globals.
if (!$userMode && $r->isMethod('post') && $r->request->get('form_save')):
    $globalsService->saveGlobalSettings($r, $GLOBALS_METADATA);
    ?>
<script type='text/javascript'>
if (parent.left_nav.location) {
    parent.left_nav.location.reload();
    parent.Title.location.reload();
    if (self.name == 'RTop') {
        parent.RBot.location.reload();
    } else {
        parent.RTop.location.reload();
    }
}
self.location.href='edit_globals.php?unique=yes';
</script>
<?php endif; ?>
    <title><?php echo $pageTitle; ?></title>
    <?php Header::setupHeader(['common', 'jscolor', 'bootstrap-sidebar']); ?>
    <style type="text/css">
        button {
            float: none;
        }
    </style>
</head>
<body class="body_top">
<div class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle visible-xs" data-toggle="sidebar" data-target=".sidebar" style="float: left;">
                <span class="sr-only"><?php echo xlt("Toggle Navigation");?></span>
                <i class="fa fa-bars"></i>
            </button>
            <button type="button" class="navbar-toggle visible-xs" data-toggle="sidebar" data-target="main-navbar">
                <span class="sr-only"><?php echo xlt("Toggle Navigation");?></span>
                <i class="fa fa-bars fa-inverted"></i>
            </button>
            <a href="#" class="navbar-brand">
                <?php echo ($userMode) ? xlt("Edit User Settings") : "Edit Global Settings"; ?>
            </a>
        </div>

        <div class="collapse navbar-collapse" id="global-setting-nav">
            <ul class="nav navbar-nav">
                <li><a href="#" id="link-save-changes"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo xlt("Save");?></a></li>
            </ul>
            <form class="navbar-form navbar-right" action="edit_globals.php<?php echo $formAction ?>" id="searchform" name="searchform" onsubmit="return top.restoreSession()">
                <div class="form-group">
                    <input type="text" class="form-control" name='srch_desc' placeholder="<?php echo xla("Search for option");?>..." value='<?php echo (!empty($_POST['srch_desc']) ? attr($_POST['srch_desc']) : '') ?>'>
                </div>
                <button type='submit' class='btn btn-default' name='form_search'>&nbsp;<i class="fa fa-search"></i>&nbsp;</button>
            </form>
        </div>
    </div>
</div>
<?php
/** @var AdminMenuBuilder $menuBuilder */
$menuBuilder = $GLOBALS['kernel']->getContainer()->get('admin.admin_menu_builder');
$base = $menuBuilder->buildMenuFromGlobalsMetadataBridge($userMode);
$menuList = $menuBuilder->generateMainMenu($base);
?>
<style type="text/css">
.vcenter {
    min-height: 100%;
    min-height: 80vh;

    display: flex;
    align-items: center;
}
</style>
<div class="container-fluid" style="margin-top: 50px;">
<form method='post' name='theform' id='theform' class='form-horizontal'
      action='edit_globals.php<?php echo $formAction; ?>'
      onsubmit='return top.restoreSession()'>
    <div class="row">
        <div class="col-xs-7 col-sm-3 col-md-2 sidebar sidebar-left sidebar-sm-show">
            <?php echo $menuBuilder->renderMenu($menuList); ?>
        </div>
    <div class="col-xs-12 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2">
        <div class="tab-content">
            <div class="tab-pane active" role="tabpanel" id="initial">
                <div class="container">
                    <div class="row vcenter">
                        <div class="col-xs-12 text-center">
                            <i class="fa fa-cogs fa-5x"></i>
                            <br>
                            <h3><?php echo xlt("Global Settings"); ?></h3>
                            <br>
                            <button type="submit">Submit</button>
                            <br>
                            <?php
                            var_dump($r->getMethod());
                            var_dump($r->request->all());
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $i = 0;
            foreach ($GLOBALS_METADATA as $grpname => $grparr) {
                if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
                    $id = strtolower(str_replace(' ', '_', $grpname));
                    ?>
                    <div class="tab-pane" role="tabpanel" id="<?php echo $id;?>">
                        <div class="page-header">
                            <h3><?php echo xlt($grpname);?></h3>
                        </div>
                    <?php
                    echo "<div class='container'>";

                    if ($userMode) {
                        echo "<div class='row'>";
                        echo "<div class='col-xs-offset-5 col-xs-4'><b>" . xlt('User Specific Setting') . "</b></div>";
                        echo "<div class='col-xs-2'><b>" . xlt('Default Setting') . "</b></div>";
                        echo "<div class='col-xs-1'><b>" . xlt('Default') . "</b></div>";
                        echo "</div>";
                    }

                    foreach ($grparr as $fldid => $fldarr) {
                        if (!$userMode || in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
                            list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                            $srch_cl = '';
                            if (!empty($_POST['srch_desc']) && (stristr(($fldname.$flddesc), $_POST['srch_desc']) !== false)) {
                                $srch_cl = ' srch';
                            }

                            // Most parameters will have a single value, but some will be arrays.
                            // Here we cater to both possibilities.
                            $glres = sqlStatement("SELECT gl_index, gl_value FROM globals WHERE " .
                                "gl_name = ? ORDER BY gl_index", array($fldid));
                            $glarr = array();
                            while ($glrow = sqlFetchArray($glres)) {
                                $glarr[] = $glrow;
                            }

                            // $fldvalue is meaningful only for the single-value cases.
                            $fldvalue = count($glarr) ? $glarr[0]['gl_value'] : $flddef;

                            // Collect user specific setting if mode set to user
                            $userSetting = "";
                            $settingDefault = "checked='checked'";
                            if ($userMode) {
                                $userSettingArray = sqlQuery("SELECT * FROM user_settings WHERE setting_user=? AND setting_label=?", array($_SESSION['authId'],"global:".$fldid));
                                $userSetting = $userSettingArray['setting_value'];
                                $globalValue = $fldvalue;
                                if (!empty($userSettingArray)) {
                                    $fldvalue = $userSetting;
                                    $settingDefault = "";
                                }
                            } else {
                                $globalValue = "";
                                $globalTitle = "";
                            }

                            if ($userMode) {
                                echo " <div class='row" . $srch_cl . "' title='" . attr($flddesc) . "'><div class='col-xs-5 control-label'><b>" . text($fldname) . "</b></div><div class='col-xs-4'>\n";
                            } else {
                                echo " <div class='row" . $srch_cl . "' title='" . attr($flddesc) . "'><div class='col-sm-5 control-label'><b>" . text($fldname) . "</b></div><div class='col-sm-6'>\n";
                            }

                            if (is_array($fldtype)) {
                                echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
                                foreach ($fldtype as $key => $value) {
                                    if ($userMode) {
                                        if ($globalValue == $key) {
                                            $globalTitle = $value;
                                        }
                                    }

                                    echo "   <option value='" . attr($key) . "'";

                                    // Casting value to string so the comparison will be always the same
                                    // type and the only thing that will check is the value.
                                    // Tried to use === but it will fail in already existing variables
                                    if ((string)$key == (string)$fldvalue) {
                                        echo " selected";
                                    }

                                    echo ">";
                                    echo text($value);
                                    echo "</option>\n";
                                }

                                echo "  </select>\n";
                            } else if ($fldtype == 'bool') {
                                if ($userMode) {
                                    if ($globalValue == 1) {
                                        $globalTitle = xlt('Checked');
                                    } else {
                                        $globalTitle = xlt('Not Checked');
                                    }
                                }

                                echo "  <input type='checkbox' class='checkbox' name='form_$i' id='form_$i' value='1'";
                                if ($fldvalue) {
                                    echo " checked";
                                }

                                echo " />\n";
                            } else if ($fldtype == 'num') {
                                if ($userMode) {
                                    $globalTitle = $globalValue;
                                }

                                echo "  <input type='text' class='form-control' name='form_$i' id='form_$i' " .
                                    "maxlength='15' value='" . attr($fldvalue) . "' />\n";
                            } else if ($fldtype == 'text') {
                                if ($userMode) {
                                    $globalTitle = $globalValue;
                                }

                                echo "  <input type='text' class='form-control' name='form_$i' id='form_$i' " .
                                    "maxlength='255' value='" . attr($fldvalue) . "' />\n";
                            } else if ($fldtype == 'pwd') {
                                if ($userMode) {
                                    $globalTitle = $globalValue;
                                }

                                echo "  <input type='password' class='form-control' name='form_$i' " .
                                    "maxlength='255' value='' />\n";
                            } else if ($fldtype == 'pass') {
                                if ($userMode) {
                                    $globalTitle = $globalValue;
                                }

                                echo "  <input type='password' class='form-control' name='form_$i' " .
                                    "maxlength='255' value='" . attr($fldvalue) . "' />\n";
                            } else if ($fldtype == 'lang') {
                                $res = sqlStatement("SELECT * FROM lang_languages ORDER BY lang_description");
                                echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
                                while ($row = sqlFetchArray($res)) {
                                    echo "   <option value='" . attr($row['lang_description']) . "'";
                                    if ($row['lang_description'] == $fldvalue) {
                                        echo " selected";
                                    }

                                    echo ">";
                                    echo xlt($row['lang_description']);
                                    echo "</option>\n";
                                }

                                echo "  </select>\n";
                            } else if ($fldtype == 'all_code_types') {
                                global $code_types;
                                echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
                                foreach (array_keys($code_types) as $code_key) {
                                    echo "   <option value='" . attr($code_key) . "'";
                                    if ($code_key == $fldvalue) {
                                        echo " selected";
                                    }

                                    echo ">";
                                    echo xlt($code_types[$code_key]['label']);
                                    echo "</option>\n";
                                }

                                echo "  </select>\n";
                            } else if ($fldtype == 'm_lang') {
                                $sql = "SELECT * FROM lang_languages ORDER BY lang_description";
                                $res = sqlStatement($sql);
                                echo "  <select multiple class='form-control' name='form_{$i}[]' id='form_{$i}[]' size='3'>\n";
                                while ($row = sqlFetchArray($res)) {
                                    echo "   <option value='" . attr($row['lang_description']) . "'";
                                    foreach ($glarr as $glrow) {
                                        if ($glrow['gl_value'] == $row['lang_description']) {
                                            echo " selected";
                                            break;
                                        }
                                    }

                                    echo ">";
                                    echo xlt($row['lang_description']);
                                    echo "</option>\n";
                                }

                                echo "  </select>\n";
                            } else if ($fldtype == 'color_code') {
                                if ($userMode) {
                                    $globalTitle = $globalValue;
                                }

                                echo "  <input type='text' class='form-control jscolor {hash:true}' name='form_$i' id='form_$i' " .
                                    "maxlength='15' value='" . attr($fldvalue) . "' />" .
                                    "<input type='button' value='" . xla('Default'). "' onclick=\"document.forms[0].form_$i.color.fromString('" . attr($flddef) . "')\">\n";
                            } else if ($fldtype == 'default_visit_category') {
                                $sql = "SELECT pc_catid, pc_catname, pc_cattype 
                    FROM openemr_postcalendar_categories
                    WHERE pc_active = 1 ORDER BY pc_seq";
                                $result = sqlStatement($sql);
                                echo "<select class='form-control' name='form_{$i}' id='form_{$i}'>\n";
                                echo "<option value='_blank'>" . xlt('None') . "</option>";
                                while ($row = sqlFetchArray($result)) {
                                    $catId = $row['pc_catid'];
                                    $name = $row['pc_catname'];
                                    if ($catId < 9 && $catId != "5") {
                                        continue;
                                    }

                                    if ($row['pc_cattype'] == 3 && !$GLOBALS['enable_group_therapy']) {
                                        continue;
                                    }

                                    $optionStr = '<option value="%pc_catid%" %selected%>%pc_catname%</option>';
                                    $optionStr = str_replace("%pc_catid%", attr($catId), $optionStr);
                                    $optionStr = str_replace("%pc_catname%", text(xl_appt_category($name)), $optionStr);
                                    $selected = ($fldvalue == $catId) ? " selected" : "";
                                    $optionStr = str_replace("%selected%", $selected, $optionStr);
                                    echo $optionStr;
                                }

                                echo "</select>";
                            } else if ($fldtype == 'css') {
                                if ($userMode) {
                                    $globalTitle = $globalValue;
                                }

                                $themedir = "$webserver_root/interface/themes";
                                $dh = opendir($themedir);
                                if ($dh) {
                                    echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
                                    while (false !== ($tfname = readdir($dh))) {
                                        // Only show files that contain style_ as options
                                        //  Skip style_blue.css since this is used for
                                        //  lone scripts such as setup.php
                                        //  Also skip style_pdf.css which is for PDFs and not screen output
                                        if (!preg_match("/^style_.*\.css$/", $tfname) ||
                                            $tfname == 'style_blue.css' || $tfname == 'style_pdf.css') {
                                            continue;
                                        }

                                        echo "<option value='" . attr($tfname) . "'";
                                        // Drop the "style_" part and any replace any underscores with spaces
                                        $styleDisplayName = str_replace("_", " ", substr($tfname, 6));
                                        // Strip the ".css" and uppercase the first character
                                        $styleDisplayName = ucfirst(str_replace(".css", "", $styleDisplayName));
                                        if ($tfname == $fldvalue) {
                                            echo " selected";
                                        }

                                        echo ">";
                                        echo text($styleDisplayName);
                                        echo "</option>\n";
                                    }

                                    closedir($dh);
                                    echo "  </select>\n";
                                }
                            } else if ($fldtype == 'tabs_css') {
                                if ($userMode) {
                                    $globalTitle = $globalValue;
                                }

                                $themedir = "$webserver_root/interface/themes";
                                $dh = opendir($themedir);
                                if ($dh) {
                                    echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
                                    while (false !== ($tfname = readdir($dh))) {
                                        // Only show files that contain tabs_style_ as options
                                        if (!preg_match("/^tabs_style_.*\.css$/", $tfname)) {
                                            continue;
                                        }

                                        echo "<option value='" . attr($tfname) . "'";
                                        // Drop the "tabs_style_" part and any replace any underscores with spaces
                                        $styleDisplayName = str_replace("_", " ", substr($tfname, 11));
                                        // Strip the ".css" and uppercase the first character
                                        $styleDisplayName = ucfirst(str_replace(".css", "", $styleDisplayName));
                                        if ($tfname == $fldvalue) {
                                            echo " selected";
                                        }

                                        echo ">";
                                        echo text($styleDisplayName);
                                        echo "</option>\n";
                                    }

                                    closedir($dh);
                                    echo "  </select>\n";
                                }
                            } else if ($fldtype == 'hour') {
                                if ($userMode) {
                                    $globalTitle = $globalValue;
                                }

                                echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
                                for ($h = 0; $h < 24; ++$h) {
                                    echo "<option value='$h'";
                                    if ($h == $fldvalue) {
                                        echo " selected";
                                    }

                                    echo ">";
                                    if ($h ==  0) {
                                        echo "12 AM";
                                    } else if ($h <  12) {
                                        echo "$h AM";
                                    } else if ($h == 12) {
                                        echo "12 PM";
                                    } else {
                                        echo ($h - 12) . " PM";
                                    }

                                    echo "</option>\n";
                                }

                                echo "  </select>\n";
                            }

                            if ($userMode) {
                                echo " </div>\n";
                                echo "<div class='col-xs-2' style='color:red;'>" . attr($globalTitle) . "</div>\n";
                                echo "<div class='col-xs-1'><input type='checkbox' value='YES' name='toggle_" . $i . "' id='toggle_" . $i . "' " . attr($settingDefault) . "/></div>\n";
                                echo "<input type='hidden' id='globaldefault_" . $i . "' value='" . attr($globalValue) . "'>\n";
                                echo "</div>\n";
                            } else {
                                echo " </div></div>\n";
                            }

                            ++$i;
                        }

                        if (trim(strtolower($fldid)) == 'portal_offsite_address_patient_link' && !empty($GLOBALS['portal_offsite_enable']) && !empty($GLOBALS['portal_offsite_providerid'])) {
                            echo "<div class='row'>";
                            echo "<div class='col-xs-12'>";
                            echo "<input type='hidden' name='form_download' id='form_download'>";
                            echo "<button onclick=\"return validate_file()\" type='button'>" . xla('Download Offsite Portal Connection Files') . "</button>";
                            echo "<div id='file_error_message' class='alert alert-error'></div>";
                            echo "</div>";
                            echo "</div>";
                        }
                    }

                    echo " </div>\n";
                    echo " </div>\n";
                }
                $i++;
            }
            ?>
            <div class="tab-pane" role="tabpanel" id="hookedDetail">
                <div class="page-header"></div>
                <div class="detailPlaceholder"></div>
            </div>
        </div>
    </div>
    </div>
</div>
</form>
</body>
</body>

<script type="text/javascript">
$(function() {
    $('.srch div').wrapInner("<mark></mark>");
    $('.tab-pane .row.srch :first-child').find('div.srch:first').each(function () {
        var srch_div = $(this).closest('div').prevAll().length + 1;
        $('.nav-pills > li:nth-child(' + srch_div + ') a').wrapInner("<mark></mark>");
    });
    $(".sidebar ul li a").on('click', function(e) {
        e.preventDefault();
        var link = $(e.target);
        var href = link.attr('href');
        var $this = $(this);
        if (href[0] === "#") {
            $(this).tab('show');
        } else {
            $.get(href, function(res) {
                $("#hookedDetail").empty().append(res);
                $this.tab('show');
            });
        }
    });
    $("#link-save-changes").on('click', function(e) {
        $("#theform").append('<input type="hidden" name="form_save" value="1">');
        $("#theform").submit();
    });
    // Use the counter ($i) to make the form user friendly for user-specific globals use
    <?php if ($userMode): ?>
        <?php for ($j = 0; $j <= $i; $j++): ?>
            $("#form_<?php echo $j ?>").change(function () {
                $("#toggle_<?php echo $j ?>").prop('checked', false);
            });
            $("#toggle_<?php echo $j ?>").change(function () {
                if ($('#toggle_<?php echo $j ?>').prop('checked')) {
                    var defaultGlobal = $("#globaldefault_<?php echo $j ?>").val();
                    $("#form_<?php echo $j ?>").val(defaultGlobal);
                }
            });
        <?php
        endfor;
    endif;
    ?>
});
function validate_file(){
    $.ajax({
        type: "POST",
        url: "<?php echo $GLOBALS['webroot']?>/library/ajax/offsite_portal_ajax.php",
        data: {
            action: 'check_file'
        },
        cache: false,
        success: function( message )
        {
            if(message == 'OK'){
                document.getElementById('form_download').value = 1;
                document.getElementById('file_error_message').innerHTML = '';
                document.forms[0].submit();
            }
            else{
                document.getElementById('form_download').value = 0;
                document.getElementById('file_error_message').innerHTML = message;
                return false;
            }
        }
    });
}
</script>
</html>

