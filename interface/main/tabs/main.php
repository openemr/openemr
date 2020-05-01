<?php

/**
 * main.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once $GLOBALS['srcdir'] . '/ESign/Api.php';

use Esign\Api;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Ensure token_main matches so this script can not be run by itself
//  If do not match, then destroy the session and go back to login screen
if (
    (empty($_SESSION['token_main_php'])) ||
    (empty($_GET['token_main'])) ||
    ($_GET['token_main'] != $_SESSION['token_main_php'])
) {
    // Below functions are from auth.inc, which is included in globals.php
    authCloseSession();
    authLoginScreen(false);
}
// this will not allow copy/paste of the link to this main.php page or a refresh of this main.php page
//  (default behavior, however, this behavior can be turned off in the prevent_browser_refresh global)
if ($GLOBALS['prevent_browser_refresh'] > 1) {
    unset($_SESSION['token_main_php']);
}

$esignApi = new Api();

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo text($openemr_name); ?></title>

    <script>

        // This is to prevent users from losing data by refreshing or backing out of OpenEMR.
        //  (default behavior, however, this behavior can be turned off in the prevent_browser_refresh global)
        <?php if ($GLOBALS['prevent_browser_refresh'] > 0) { ?>
        window.addEventListener('beforeunload', (event) => {
            if (!timed_out) {
                event.returnValue = <?php echo xlj('Recommend not leaving or refreshing or you may lose data.'); ?>;
            }
        });
        <?php } ?>

        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        var isPortalEnabled = "<?php echo $GLOBALS['portal_onsite_two_enable'] == 1; ?>";

        // Since this should be the parent window, this is to prevent calls to the
        // window that opened this window. For example when a new window is opened
        // from the Patient Flow Board or the Patient Finder.
        window.opener = null;

        // This flag indicates if another window or frame is trying to reload the login
        // page to this top-level window.  It is set by javascript returned by auth.inc
        // and is checked by handlers of beforeunload events.
        var timed_out = false;

        function goRepeaterServices() {
            // Ensure send the skip_timeout_reset parameter to not count this as a manual entry in the
            //  timing out mechanism in OpenEMR.

            // Send the skip_timeout_reset parameter to not count this as a manual entry in the
            //  timing out mechanism in OpenEMR.
            top.restoreSession();
            $.post("<?php echo $GLOBALS['webroot']; ?>/library/ajax/dated_reminders_counter.php",
                {
                    skip_timeout_reset: "1",
                    csrf_token_form: "<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"
                },
                function (data) {
                    // Go knockout.js
                    app_view_model.application_data.user().messages(data);
                }
            );
            // Notify App for various portal alerts
            if (isPortalEnabled) {
                top.restoreSession();
                $.post("<?php echo $GLOBALS['webroot']; ?>/library/ajax/dated_reminders_counter.php",
                    {
                        skip_timeout_reset: "1",
                        isPortal: "1",
                        csrf_token_form: "<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"
                    },
                    function (counts) {
                        data = JSON.parse(counts);
                        let mail = data.mailCnt;
                        let chats = data.chatCnt;
                        let audits = data.auditCnt;
                        let payments = data.paymentCnt;
                        let total = data.total;
                        let enable = (1 * mail) + (1 * audits); // payments are among audits.

                        app_view_model.application_data.user().portal(enable);
                        if (enable) {
                            app_view_model.application_data.user().portalAlerts(total);
                            app_view_model.application_data.user().portalAudits(audits);
                            app_view_model.application_data.user().portalMail(mail);
                            app_view_model.application_data.user().portalChats(chats);
                            app_view_model.application_data.user().portalPayments(payments);
                        }
                    }
                );
            }

            top.restoreSession();
            // run background-services
            $.post("<?php echo $GLOBALS['webroot']; ?>/library/ajax/execute_background_services.php",
                {
                    skip_timeout_reset: "1",
                    ajax: "1",
                    csrf_token_form: "<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"
                }
            );

            // auto run this function every 60 seconds
            var repeater = setTimeout("goRepeaterServices()", 60000);
        }

        function isEncounterLocked(encounterId) {
            <?php if ($esignApi->lockEncounters()) { ?>
            // If encounter locking is enabled, make a syncronous call (async=false) to check the
            // DB to see if the encounter is locked.
            // Call restore session, just in case
            top.restoreSession();
            $.ajax({
                type: 'POST',
                url: '<?php echo $GLOBALS['webroot']?>/interface/esign/index.php?module=encounter&method=esign_is_encounter_locked',
                data: {encounterId: encounterId},
                success: function (data) {
                    encounter_locked = data;
                },
                dataType: 'json',
                async: false
            });
            return encounter_locked;
            <?php } else { ?>
            // If encounter locking isn't enabled then always return false
            return false;
            <?php } ?>
        }

        // some globals to access using top.variable
        window.name = "main";
        // note that 'let' or 'const' does not allow global scope here.
        // only use var
        var userDebug = <?php echo js_escape($GLOBALS['user_debug']); ?>;
        var webroot_url = <?php echo js_escape($web_root); ?>;
        var jsLanguageDirection = <?php echo js_escape($_SESSION['language_direction']); ?>;
        var jsGlobals = {};
    </script>

    <?php Header::setupHeader(['knockout', 'tabs-theme', 'i18next']); ?>

    <link rel="shortcut icon" href="<?php echo $GLOBALS['images_static_relative']; ?>/favicon.ico" />

    <script type="text/javascript" src="js/custom_bindings.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="js/user_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="js/patient_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="js/therapy_group_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>

    <script type="text/javascript">
        // Set the csrf_token_js token that is used in the below js/tabs_view_model.js script
        var csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
        // will fullfill json and return promise if needed
        // to call elsewhere for a local scope ie
        // let localJson = top.jsFetchGlobals().then(data => {do something with parsed json data});
        // will use post here due to content type and length.
        function jsFetchGlobals(scope) {
            let url = webroot_url + "/library/ajax/phpvars_to_js.php";
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        scope: scope,
                        csrf_token_form: csrf_token_js,
                    },
                    beforeSend: function () {
                        top.restoreSession;
                    },
                    success: function (data) {
                        // I.E Edge auto parses response json thus this!
                        data = typeof data === 'object' ? data : JSON.parse(data);
                        resolve(data);
                    },
                    error: function (error) {
                        reject(error);
                    },
                })
            })
        }

        jsFetchGlobals('top').then(globalJson => {
            jsGlobals = globalJson;
        }).catch(error => {
            console.log(error.message);
        });

        // set up global translations for js
        function setupI18n(lang_id) {
            top.restoreSession();
            return fetch(<?php echo js_escape($GLOBALS['webroot'])?> +"/library/ajax/i18n_generator.php?lang_id=" + encodeURIComponent(lang_id) + "&csrf_token_form=" + encodeURIComponent(csrf_token_js), {
                credentials: 'same-origin',
                method: 'GET'
            }).then(response => response.json())
        }

        setupI18n(<?php echo js_escape($_SESSION['language_choice']); ?>).then(translationsJson => {
            i18next.init({
                lng: 'selected',
                debug: false,
                nsSeparator: false,
                keySeparator: false,
                resources: {
                    selected: {
                        translation: translationsJson
                    }
                }
            });
        }).catch(error => {
            console.log(error.message);
        });

    </script>
    <script type="text/javascript" src="js/tabs_view_model.js?v=<?php echo $v_js_includes; ?>"></script>

    <script type="text/javascript" src="js/application_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="js/frame_proxies.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="js/dialog_utils.js?v=<?php echo $v_js_includes; ?>"></script>

    <?php
    // Below code block is to prepare certain elements for deciding what links to show on the menu
    //
    // prepare newcrop globals that are used in creating the menu
    if ($GLOBALS['erx_enable']) {
        $newcrop_user_role_sql = sqlQuery("SELECT `newcrop_user_role` FROM `users` WHERE `username` = ?", array($_SESSION['authUser']));
        $GLOBALS['newcrop_user_role'] = $newcrop_user_role_sql['newcrop_user_role'];
        if ($GLOBALS['newcrop_user_role'] === 'erxadmin') {
            $GLOBALS['newcrop_user_role_erxadmin'] = 1;
        }
    }

    // prepare track anything to be used in creating the menu
    $track_anything_sql = sqlQuery("SELECT `state` FROM `registry` WHERE `directory` = 'track_anything'");
    $GLOBALS['track_anything_state'] = $track_anything_sql['state'];
    // prepare Issues popup link global that is used in creating the menu
    $GLOBALS['allow_issue_menu_link'] = ((AclMain::aclCheckCore('encounters', 'notes', '', 'write') || AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) &&
        AclMain::aclCheckCore('patients', 'med', '', 'write'));
    ?>

    <?php require_once("templates/tabs_template.php"); ?>
    <?php require_once("templates/menu_template.php"); ?>
    <?php require_once("templates/patient_data_template.php"); ?>
    <?php require_once("templates/therapy_group_template.php"); ?>
    <?php require_once("templates/user_data_template.php"); ?>
    <?php require_once("menu/menu_json.php"); ?>
    <?php $userQuery = sqlQuery("select * from users where username = ?", array($_SESSION['authUser'])); ?>

    <script type="text/javascript">
        <?php if (!empty($_SESSION['frame1url']) && !empty($_SESSION['frame1target'])) { ?>
        // Use session variables and tabStatus object to set up initial/default first tab
        app_view_model.application_data.tabs.tabsList.push(new tabStatus(<?php echo xlj("Loading"); ?> +"...",<?php echo json_encode("../" . $_SESSION['frame1url']); ?>,<?php echo json_encode($_SESSION['frame1target']); ?>,<?php echo xlj("Loading"); ?> +" " + <?php echo json_encode($_SESSION['frame1label']); ?>, true, true, false));
        <?php } ?>

        <?php if (!empty($_SESSION['frame2url']) && !empty($_SESSION['frame2target'])) { ?>
        // Use session variables and tabStatus object to set up initial/default second tab, if none is set in globals, this tab will not be displayed initially
        app_view_model.application_data.tabs.tabsList.push(new tabStatus(<?php echo xlj("Loading"); ?> +"...",<?php echo json_encode("../" . $_SESSION['frame2url']); ?>,<?php echo json_encode($_SESSION['frame2target']); ?>,<?php echo xlj("Loading"); ?> +" " + <?php echo json_encode($_SESSION['frame2label']); ?>, true, false, false));
        <?php } ?>

        app_view_model.application_data.user(new user_data_view_model(<?php echo json_encode($_SESSION["authUser"])
            . ',' . json_encode($userQuery['fname'])
            . ',' . json_encode($userQuery['lname'])
            . ',' . json_encode($_SESSION['authProvider']); ?>));

    </script>

<style>
    html, body {
        width: max-content;
        min-height: 100% !important;
        height: 100% !important;
    }
</style>

</head>
<body class="min-vw-100">
    <!-- Below iframe is to support auto logout when timeout is reached -->
    <iframe name="timeout" class="position-absolute border-0" style="visibility:hidden; left:0; top:0; height:0; width:0;" src="timeout_iframe.php"></iframe>
    <!-- Below iframe is to support logout, which needs to be run in an inner iframe to work as intended -->
    <iframe name="logoutinnerframe" class="position-absolute border-0" id="logoutinnerframe" style="visibility:hidden; left:0; top:0; height:0; width:0;" src="about:blank"></iframe>
    <?php // mdsupport - app settings
    $disp_mainBox = '';
    if (isset($_SESSION['app1'])) {
        $rs = sqlquery(
            "SELECT title app_url FROM list_options WHERE activity=1 AND list_id=? AND option_id=?",
            array('apps', $_SESSION['app1'])
        );
        if ($rs['app_url'] != "main/main_screen.php") {
            echo '<iframe name="app1" class="position-absolute w-100 h-100 border-0" src="../../' . attr($rs['app_url']) . '"
    			style="left: 0; top: 0;" />';
            $disp_mainBox = 'style="display: none;"';
        }
    }
    ?>
    <div id="mainBox" <?php echo $disp_mainBox; ?>>
        <nav class="navbar navbar-expand-xl navbar-light bg-light py-0 oemr-navbar">
            <a class="navbar-brand" href="https://www.open-emr.org" title="OpenEMR <?php echo xla("Website"); ?>" rel="noopener" target="_blank">
                <?php echo file_get_contents($GLOBALS['images_static_absolute'] . "/menu-logo.svg"); ?>
            </a>
            <!--<a href="../../logout.php" target="logoutinnerframe" class="d-lg-none" id="logout_link" onclick="top.restoreSession()" title="<?php /*echo xla("Logout"); */ ?>"><i class="fa fa-2x fa-sign-out oe-pull-toward" aria-hidden="true" id="logout_icon"></i>
                    </a>-->
            <button class="navbar-toggler mr-auto" type="button" data-toggle="collapse" data-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainMenu" data-bind="template: {name: 'menu-template', data: application_data}"></div>
            <span id="userData" data-bind="template: {name: 'user-data-template', data: application_data}"></span>
        </nav>
            <div id="attendantData" class="body_title acck" data-bind="template: {name: app_view_model.attendant_template_type, data: application_data}">
            </div>

        <div class="body_title" id="tabs_div" data-bind="template: {name: 'tabs-controls', data: application_data}"></div>

        <div class="mainFrames d-flex flex-row" id="mainFrames_div">
            <div id="framesDisplay" data-bind="template: {name: 'tabs-frames', data: application_data}"></div>
        </div>
    </div>
    <script>
        ko.applyBindings(app_view_model);

        $(function () {
            goRepeaterServices();
            $('#patient_caret').click(function () {
                $('#attendantData').slideToggle();
                $('#patient_caret').toggleClass('fa-caret-down').toggleClass('fa-caret-up');
            });
        });
        $(function () {
            $('#logo_menu').focus();
        });
         $('#anySearchBox').keypress(function (event) {
             if (event.which === 13 || event.keyCode === 13) {
                 event.preventDefault();
                 $('#search_globals').mousedown();
             }
           });
        document.addEventListener('touchstart', {}); //specifically added for iOS devices, especially in iframes
    </script>
</body>
</html>
