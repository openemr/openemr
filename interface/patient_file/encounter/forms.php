<?php
use ESign\Api;

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/group.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/amc.php");
require_once $GLOBALS['srcdir'] . '/ESign/Api.php';
require_once("$srcdir/../controllers/C_Document.class.php");
require_once("forms_review_header.php");

$is_group = ($attendant_type == 'gid') ? true : false;
if ($attendant_type == 'gid') {
    $groupId = $therapy_group;
}
$attendant_id = $attendant_type == 'pid' ? $pid : $therapy_group;
if ($is_group && !acl_check("groups", "glog", false, array('view', 'write'))) {
    echo xlt("access not allowed");
    exit();
}

?>
<html>

<head>
    <?php
    html_header_show();

    /**
     * Generate a script tag based on array of elements
     *
     * ```php
     * $options = [
     *     [
     *         'src' => 'Source path of the file to include',
     *         'basePath' => 'Defaults to $GLOBALS['assets_static_relative']',
     *         'atts' => ['Array of attributes. Not currently supported, future use'],
     *     ]];
     * ```
     *
     * Options can also be an array of strings in which case each string
     * represents the path (basePath will be prepended), in which case:
     *
     * ```php
     * $options = ['jquery-latest/jquery.js', 'bootstrap-latest/bootstrap.js'];
     * ```
     *
     * @param $options array Array of items
     * @return string
     */
    function generateScriptElements($options)
    {
        $template = '<script src="{src}"></script>';
        $return = [];
        $basePath = $GLOBALS['assets_static_relative'] . '/';
        foreach ($options as $element) {
            if (is_array($element)) {
                if (array_key_exists('basePath', $element) && $element['basePath'] !== false) {
                    $basePath = $element['basePath'];
                } else if (array_key_exists('basePath', $element) && $element['basePath'] === false) {
                    // If basePath gets passed in but is false, ensure it's not appended
                    $basePath = "";
                }
                $str = str_replace("{src}", $basePath . $element['src'], $template);
            } else {
                $str = str_replace("{src}", $element, $template);
            }
            $return[] = $str;
        }
        return implode("\n", $return);
    }

    $libraryDir = $GLOBALS['webroot'] . '/library/';
    $scripts = [
        ['src' => 'jquery-min-3-1-1/index.js'],
        ['basePath' => $libraryDir, 'src' => 'dialog.js?v=' . $v_js_includes],
        ['basePath' => $libraryDir, 'src' => 'textformat.js'],
        ['basePath' => $libraryDir, 'src' => 'dynarch_calendar.js'],
        ['basePath' => $libraryDir, 'src' => 'dynarch_calendar_setup.js'],
        ['basePath' => $libraryDir, 'src' => 'js/common.js'],
//        ['basePath' => $libraryDir, 'src' => 'js/fancybox-1.3.4/jquery.fancybox-1.3.4.js'],
        ['basePath' => $libraryDir, 'src' => 'ESign/js/jquery.esign.js'],
        ['basePath' => $libraryDir, 'src' => 'openflashchart/js/json/json2.js'],
        ['basePath' => $libraryDir, 'src' => 'openflashchart/js/swfobject.js'],
    ];

    require_once "{$GLOBALS['srcdir']}/templates/standard_header_template.php";
    ?>
    <link rel="stylesheet" type="text/css"
          href="../../../library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css"
          media="screen"/>
    <style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>

    <!-- supporting javascript code -->
    <?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
    <link rel="stylesheet" type="text/css"
          href="<?php echo $GLOBALS['webroot'] ?>/library/ESign/css/esign.css"/>

    <?php
    $esignApi = new Api();

    // include generic js support for graphing ?>

    <?php // if the track_anything form exists, then include the styling and js functions for graphing
    if (file_exists(dirname(__FILE__) . "/../../forms/track_anything/style.css")) {
        $scripts[] = ['basePath' => $GLOBALS['web_root'], 'src' => 'interface/forms/track_anything/report.js'];
        ?>
        <link rel="stylesheet"
              href="<?php echo $GLOBALS['web_root'] ?>/interface/forms/track_anything/style.css"
              type="text/css">
    <?php }

    // If the user requested attachment of any orphaned procedure orders, do it.
    if (!empty($_GET['attachid'])) {
        $attachid = explode(',', $_GET['attachid']);
        foreach ($attachid as $aid) {
            $aid = intval($aid);
            if (!$aid) continue;
            $tmp = sqlQuery("SELECT COUNT(*) AS count FROM procedure_order WHERE " .
                "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
                array($aid, $pid));
            if (!empty($tmp['count'])) {
                sqlStatement("UPDATE procedure_order SET encounter_id = ? WHERE " .
                    "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
                    array($encounter, $aid, $pid));
                addForm($encounter, "Procedure Order", $aid, "procedure_order", $pid, $userauthorized);
            }
        }
    }

    echo generateScriptElements($scripts);
    ?>

    <script type="text/javascript">
        $.noConflict();
        jQuery(document).ready(function ($) {
            var formConfig = <?php echo $esignApi->formConfigToJson(); ?>;
            $(".esign-button-form").esign(
                formConfig,
                {
                    afterFormSuccess: function (response) {
                        if (response.locked) {
                            var editButtonId = "form-edit-button-" + response.formDir + "-" + response.formId;
                            $("#" + editButtonId).replaceWith(response.editButtonHtml);
                        }

                        var logId = "esign-signature-log-" + response.formDir + "-" + response.formId;
                        $.post(formConfig.logViewAction, response, function (html) {
                            $("#" + logId).replaceWith(html);
                        });
                    }
                }
            );

            var encounterConfig = <?php echo $esignApi->encounterConfigToJson(); ?>;
            $(".esign-button-encounter").esign(
                encounterConfig,
                {
                    afterFormSuccess: function (response) {
                        // If the response indicates a locked encounter, replace all
                        // form edit buttons with a "disabled" button, and "disable" left
                        // nav visit form links
                        if (response.locked) {
                            // Lock the form edit buttons
                            $(".form-edit-button").replaceWith(response.editButtonHtml);
                            // Disable the new-form capabilities in left nav
                            top.window.parent.left_nav.syncRadios();
                            // Disable the new-form capabilities in top nav of the encounter
                            $(".encounter-form-category-li").remove();
                        }

                        var logId = "esign-signature-log-encounter-" + response.encounterId;
                        $.post(encounterConfig.logViewAction, response, function (html) {
                            $("#" + logId).replaceWith(html);
                        });
                    }
                }
            );

            $(".onerow").mouseover(function () {
                $(this).toggleClass("highlight");
            });
            $(".onerow").mouseout(function () {
                $(this).toggleClass("highlight");
            });
            $(".onerow").click(function () {
                GotoForm(this);
            });

            $("#prov_edu_res").click(function () {
                if ($('#prov_edu_res').attr('checked')) {
                    var mode = "add";
                }
                else {
                    var mode = "remove";
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "patient_edu_amc",
                        complete: true,
                        mode: mode,
                        patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
                    }
                );
            });

            $("#provide_sum_pat_flag").click(function () {
                if ($('#provide_sum_pat_flag').attr('checked')) {
                    var mode = "add";
                }
                else {
                    var mode = "remove";
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "provide_sum_pat_amc",
                        complete: true,
                        mode: mode,
                        patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
                    }
                );
            });

            $("#trans_trand_care").click(function () {
                if ($('#trans_trand_care').attr('checked')) {
                    var mode = "add";
                    // Enable the reconciliation checkbox
                    $("#med_reconc_perf").removeAttr("disabled");
                    $("#soc_provided").removeAttr("disabled");
                }
                else {
                    var mode = "remove";
                    //Disable the reconciliation checkbox (also uncheck it if applicable)
                    $("#med_reconc_perf").attr("disabled", true);
                    $("#med_reconc_perf").removeAttr("checked");
                    $("#soc_provided").attr("disabled", true);
                    $("#soc_provided").removeAttr("checked");
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "med_reconc_amc",
                        complete: false,
                        mode: mode,
                        patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
                    }
                );
            });

            $("#med_reconc_perf").click(function () {
                if ($('#med_reconc_perf').attr('checked')) {
                    var mode = "complete";
                }
                else {
                    var mode = "uncomplete";
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "med_reconc_amc",
                        complete: true,
                        mode: mode,
                        patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
                    }
                );
            });
            $("#soc_provided").click(function () {
                if ($('#soc_provided').attr('checked')) {
                    var mode = "soc_provided";
                }
                else {
                    var mode = "no_soc_provided";
                }
                top.restoreSession();
                $.post("../../../library/ajax/amc_misc_data.php",
                    {
                        amc_id: "med_reconc_amc",
                        complete: true,
                        mode: mode,
                        patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
                        object_category: "form_encounter",
                        object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
                    }
                );
            });

            $(".deleteme").click(function (evt) {
                deleteme();
                evt.stopPropogation();
            });

            var GotoForm = function (obj) {
                var parts = $(obj).attr("id").split("~");
                top.restoreSession();
                parent.location.href = "<?php echo $rootdir; ?>/patient_file/encounter/view_form.php?formname=" + parts[0] + "&id=" + parts[1];
            }

            <?php
            // If the user was not just asked about orphaned orders, build javascript for that.
            if (!isset($_GET['attachid'])) {
                $ares = sqlStatement("SELECT procedure_order_id, date_ordered " .
                    "FROM procedure_order WHERE " .
                    "patient_id = ? AND encounter_id = 0 AND activity = 1 " .
                    "ORDER BY procedure_order_id",
                    array($pid));
                echo "  // Ask about attaching orphaned orders to this encounter.\n";
                echo "  var attachid = '';\n";
                while ($arow = sqlFetchArray($ares)) {
                    $orderid = $arow['procedure_order_id'];
                    $orderdate = $arow['date_ordered'];
                    echo "  if (confirm('" . xls('There is a lab order') . " $orderid " .
                        xls('dated') . " $orderdate " .
                        xls('for this patient not yet assigned to any encounter.') . " " .
                        xls('Assign it to this one?') . "')) attachid += '$orderid,';\n";
                }
                echo "  if (attachid) location.href = 'forms.php?attachid=' + attachid;\n";
            }
            ?>

            $('li.dropdown').on('click', 'a.dropdown-toggle', function(){
                var icon = $(this).children("i");
                var defaultIcon = 'fa-chevron-right';
                var toggledIcon = 'fa-chevron-down';
                if ($(icon).hasClass(defaultIcon)) {
                    $(icon).removeClass(defaultIcon);
                    $(icon).addClass(toggledIcon);
                } else if ($(icon).hasClass(toggledIcon)) {
                    $(icon).addClass(defaultIcon);
                    $(icon).removeClass(toggledIcon);
                }

                $(this).parent().children("ul").toggleClass('hidden');
            });

            $('ul.mainmenu').on('click', 'a[href!="#"]', function(e){
                e.preventDefault();
                var href = $(this).attr('href');
                $("#content iframe").attr('src', href);
            });

        });

        // Process click on Delete link.
        function deleteme() {
            dlgopen('../deleter.php?encounterid=<?php echo $encounter; ?>', '_blank', 500, 450);
            return false;
        }

        // Called by the deleter.php window on a successful delete.
        function imdeleted(EncounterId) {
            top.window.parent.left_nav.removeOptionSelected(EncounterId);
            top.window.parent.left_nav.clearEncounter();
        }

    </script>

    <script language="javascript">
        function expandcollapse(atr) {
            if (atr == "expand") {
                for (i = 1; i < 15; i++) {
                    var mydivid = "divid_" + i;
                    var myspanid = "spanid_" + i;
                    var ele = document.getElementById(mydivid);
                    var text = document.getElementById(myspanid);
                    if (typeof(ele) != 'undefined' && ele != null)
                        ele.style.display = "block";
                    if (typeof(text) != 'undefined' && text != null)
                        text.innerHTML = "<?php xl('Collapse', 'e'); ?>";
                }
            }
            else {
                for (i = 1; i < 15; i++) {
                    var mydivid = "divid_" + i;
                    var myspanid = "spanid_" + i;
                    var ele = document.getElementById(mydivid);
                    var text = document.getElementById(myspanid);
                    if (typeof(ele) != 'undefined' && ele != null)
                        ele.style.display = "none";
                    if (typeof(text) != 'undefined' && text != null)
                        text.innerHTML = "<?php xl('Expand', 'e'); ?>";
                }
            }

        }

        function divtoggle(spanid, divid) {
            var ele = document.getElementById(divid);
            var text = document.getElementById(spanid);
            if (ele.style.display == "block") {
                ele.style.display = "none";
                text.innerHTML = "<?php xl('Expand', 'e'); ?>";
            }
            else {
                ele.style.display = "block";
                text.innerHTML = "<?php xl('Collapse', 'e'); ?>";
            }
        }
    </script>
    <style type="text/css">
        #content iframe {
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
        }
    </style>
</head>
<?php
$hide = 1;
require_once("new_form.php");
?>

<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'];?>/bootstrap-sidebar-0-2-2/dist/css/sidebar.css">
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'];?>/bootstrap-sidebar-0-2-2/dist/js/sidebar.js"></script>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 col-sm-2 sidebar sidebar-left sidebar-md-show">
            <ul class="nav navbar-stacked mainmenu">
                <?php echo $menu; ?>
            </ul>
        </div>
        <div class="col-xs-12 col-sm-10 col-sm-offset-2" id="content" style="margin-top: 25px;">
            <iframe src="<?php echo $GLOBALS['rootdir'];?>/patient_file/encounter/list.php"
                    frameborder="0"
                    style=""></iframe>

        </div>
    </div>
</div> <!-- end large encounter_forms DIV -->
</body>
<?php require_once("forms_review_footer.php"); ?>
</html>
