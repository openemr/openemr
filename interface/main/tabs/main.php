<?php

/**
 * main.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once(__DIR__ . '/../../globals.php');
require_once $GLOBALS['srcdir'] . '/ESign/Api.php';

use Esign\Api;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Events\Main\Tabs\RenderEvent;

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

        // Since this should be the parent window, this is to prevent calls to the
        // window that opened this window. For example when a new window is opened
        // from the Patient Flow Board or the Patient Finder.
        window.opener = null;
        window.name = "main";

        // This flag indicates if another window or frame is trying to reload the login
        // page to this top-level window.  It is set by javascript returned by auth.inc
        // and is checked by handlers of beforeunload events.
        var timed_out = false;
        // some globals to access using top.variable
        // note that 'let' or 'const' does not allow global scope here.
        // only use var
        var isPortalEnabled = "<?php echo $GLOBALS['portal_onsite_two_enable'] ?>";
        // Set the csrf_token_js token that is used in the below js/tabs_view_model.js script
        var csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
        var userDebug = <?php echo js_escape($GLOBALS['user_debug']); ?>;
        var webroot_url = <?php echo js_escape($web_root); ?>;
        var jsLanguageDirection = <?php echo js_escape($_SESSION['language_direction']); ?>;
        var jsGlobals = {};
        // used in tabs_view_model.js.
        jsGlobals.enable_group_therapy = <?php echo js_escape($GLOBALS['enable_group_therapy']); ?>

        var WindowTitleAddPatient = <?php echo ($GLOBALS['window_title_add_patient_name'] ? 'true' : 'false' ); ?>;
        var WindowTitleBase = <?php echo js_escape($openemr_name); ?>;

        function goRepeaterServices() {
            // Ensure send the skip_timeout_reset parameter to not count this as a manual entry in the
            // timing out mechanism in OpenEMR.

            // Send the skip_timeout_reset parameter to not count this as a manual entry in the
            // timing out mechanism in OpenEMR. Notify App for various portal and reminder alerts.
            // Combined portal and reminders ajax to fetch sjp 06-07-2020.
            // Incorporated timeout mechanism in 2021
            restoreSession();
            let request = new FormData;
            request.append("skip_timeout_reset", "1");
            request.append("isPortal", isPortalEnabled);
            request.append("csrf_token_form", csrf_token_js);
            fetch(webroot_url + "/library/ajax/dated_reminders_counter.php", {
                method: 'POST',
                credentials: 'same-origin',
                body: request
            }).then((response) => {
                if (response.status !== 200) {
                    console.log('Reminders start failed. Status Code: ' + response.status);
                    return;
                }
                return response.json();
            }).then((data) => {
                if (data.timeoutMessage && (data.timeoutMessage == 'timeout')) {
                    // timeout has happened, so logout
                    timeoutLogout();
                }
                if (isPortalEnabled) {
                    let mail = data.mailCnt;
                    let chats = data.chatCnt;
                    let audits = data.auditCnt;
                    let payments = data.paymentCnt;
                    let total = data.total;
                    let enable = ((1 * mail) + (1 * audits)); // payments are among audits.
                    // Send portal counts to notification button model
                    // Will turn off button display if no notification!
                    app_view_model.application_data.user().portal(enable);
                    if (enable > 0) {
                        app_view_model.application_data.user().portalAlerts(total);
                        app_view_model.application_data.user().portalAudits(audits);
                        app_view_model.application_data.user().portalMail(mail);
                        app_view_model.application_data.user().portalChats(chats);
                        app_view_model.application_data.user().portalPayments(payments);
                    }
                }
                // Always send reminder count text to model
                app_view_model.application_data.user().messages(data.reminderText);
            }).catch(function(error) {
                console.log('Request failed', error);
            });

            // run background-services
            // delay 10 seconds to prevent both utility trigger at close to same time.
            // Both call globals so that is my concern.
            setTimeout(function() {
                restoreSession();
                request = new FormData;
                request.append("skip_timeout_reset", "1");
                request.append("ajax", "1");
                request.append("csrf_token_form", csrf_token_js);
                fetch(webroot_url + "/library/ajax/execute_background_services.php", {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: request
                }).then((response) => {
                    if (response.status !== 200) {
                        console.log('Background Service start failed. Status Code: ' + response.status);
                    }
                }).catch(function(error) {
                    console.log('HTML Background Service start Request failed: ', error);
                });
            }, 10000);

            // auto run this function every 60 seconds
            var repeater = setTimeout("goRepeaterServices()", 60000);
        }

        function isEncounterLocked(encounterId) {
            <?php if ($esignApi->lockEncounters()) { ?>
                // If encounter locking is enabled, make a syncronous call (async=false) to check the
                // DB to see if the encounter is locked.
                // Call restore session, just in case
                // @TODO next clean up pass, turn into await promise then modify tabs_view_model.js L-309
                restoreSession();
                let url = webroot_url + "/interface/esign/index.php?module=encounter&method=esign_is_encounter_locked";
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        encounterId: encounterId
                    },
                    success: function(data) {
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
    </script>

    <?php Header::setupHeader(['knockout', 'tabs-theme', 'i18next', 'hotkeys']); ?>
    <script>
        // set up global translations for js
        function setupI18n(lang_id) {
            restoreSession();
            return fetch(<?php echo js_escape($GLOBALS['webroot']) ?> + "/library/ajax/i18n_generator.php?lang_id=" + encodeURIComponent(lang_id) + "&csrf_token_form=" + encodeURIComponent(csrf_token_js), {
                credentials: 'same-origin',
                method: 'GET'
            }).then((response) => {
                if (response.status !== 200) {
                    console.log('I18n setup failed. Status Code: ' + response.status);
                    return [];
                }
                return response.json();
            })
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

        /**
         * Assign and persist documents to portal patients
         * @var int patientId pid
         */
        function assignPatientDocuments(patientId) {
            let url = top.webroot_url + '/portal/import_template_ui.php?from_demo_pid=' + encodeURIComponent(patientId);
            dlgopen(url, 'pop-assignments', 'modal-lg', 850, '', '', {
                allowDrag: true,
                allowResize: true,
                sizeHeight: 'full',
            });
        }
    </script>

    <script src="js/custom_bindings.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/user_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/patient_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/therapy_group_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/tabs_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/application_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/frame_proxies.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/dialog_utils.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="js/shortcuts.js?v=<?php echo $v_js_includes; ?>"></script>

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
    $GLOBALS['track_anything_state'] = ($track_anything_sql['state'] ?? 0);
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

    <script>
        <?php if (!empty($_SESSION['frame1url']) && !empty($_SESSION['frame1target'])) { ?>
            // Use session variables and tabStatus object to set up initial/default first tab
            app_view_model.application_data.tabs.tabsList.push(new tabStatus(<?php echo xlj("Loading"); ?> + "...", <?php echo json_encode("../" . $_SESSION['frame1url']); ?>, <?php echo json_encode($_SESSION['frame1target']); ?>, <?php echo xlj("Loading"); ?> + " " + <?php echo json_encode($_SESSION['frame1label']); ?>, true, true, false));
        <?php } ?>

        <?php if (!empty($_SESSION['frame2url']) && !empty($_SESSION['frame2target'])) { ?>
            // Use session variables and tabStatus object to set up initial/default second tab, if none is set in globals, this tab will not be displayed initially
            app_view_model.application_data.tabs.tabsList.push(new tabStatus(<?php echo xlj("Loading"); ?> + "...", <?php echo json_encode("../" . $_SESSION['frame2url']); ?>, <?php echo json_encode($_SESSION['frame2target']); ?>, <?php echo xlj("Loading"); ?> + " " + <?php echo json_encode($_SESSION['frame2label']); ?>, true, false, false));
        <?php } ?>

        app_view_model.application_data.user(new user_data_view_model(<?php echo json_encode($_SESSION["authUser"])
                                                                            . ',' . json_encode($userQuery['fname'])
                                                                            . ',' . json_encode($userQuery['lname'])
                                                                            . ',' . json_encode($_SESSION['authProvider']); ?>));
    </script>
    <style>
        html,
        body {
            width: max-content;
            min-height: 100% !important;
            height: 100% !important;
        }
    </style>
</head>

<body class="min-vw-100">
<?php
    // fire off an event here
if (!empty($GLOBALS['kernel']->getEventDispatcher())) {
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    $dispatcher = $GLOBALS['kernel']->getEventDispatcher();
    $dispatcher->dispatch(new RenderEvent(), RenderEvent::EVENT_BODY_RENDER_PRE);
}
?>
    <!-- Below iframe is to support logout, which needs to be run in an inner iframe to work as intended -->
    <iframe name="logoutinnerframe" id="logoutinnerframe" style="visibility:hidden; position:absolute; left:0; top:0; height:0; width:0; border:none;" src="about:blank"></iframe>
    <?php // mdsupport - app settings
    $disp_mainBox = '';
    if (isset($_SESSION['app1'])) {
        $rs = sqlquery(
            "SELECT title app_url FROM list_options WHERE activity=1 AND list_id=? AND option_id=?",
            array('apps', $_SESSION['app1'])
        );
        if ($rs['app_url'] != "main/main_screen.php") {
            echo '<iframe name="app1" src="../../' . attr($rs['app_url']) . '"
    			style="position: absolute; left: 0; top: 0; height: 100%; width: 100%; border: none;" />';
            $disp_mainBox = 'style="display: none;"';
        }
    }
    ?>
    <div id="mainBox" <?php echo $disp_mainBox ?>>
        <nav class="navbar navbar-expand-xl navbar-light bg-light py-0">
            <?php if ($GLOBALS['display_main_menu_logo'] === '1') : ?>
            <a class="navbar-brand mt-2 mt-xl-0 mr-3 mr-xl-2" href="https://www.open-emr.org" title="OpenEMR <?php echo xla("Website"); ?>" rel="noopener" target="_blank">
                <?php echo file_get_contents($GLOBALS['images_static_absolute'] . "/menu-logo.svg"); ?>
            </a>
            <?php endif; ?>
            <button class="navbar-toggler mr-auto" type="button" data-toggle="collapse" data-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainMenu" data-bind="template: {name: 'menu-template', data: application_data}"></div>
            <form name="frm_search_globals" class="form-inline">
                <div class="input-group">
                    <input type="text" id="anySearchBox" class="form-control-sm <?php echo $any_search_class ?> form-control" name="anySearchBox" placeholder="<?php echo xla("Search by any demographics") ?>" autocomplete="off">
                    <div class="input-group-append">
                        <button type="button" id="search_globals" class="btn btn-sm btn-secondary <?php echo $search_globals_class ?>" title='<?php echo xla("Search for patient by entering whole or part of any demographics field information"); ?>' data-bind="event: {mousedown: viewPtFinder.bind( $data, '<?php echo xla("The search field cannot be empty. Please enter a search term") ?>', '<?php echo attr($search_any_type); ?>')}"><i class="fa fa-search">&nbsp;</i></button>
                    </div>
                </div>
            </form>
            <span id="userData" data-bind="template: {name: 'user-data-template', data: application_data}"></span>
        </nav>
        <div id="attendantData" class="body_title acck" data-bind="template: {name: app_view_model.attendant_template_type, data: application_data}"></div>
        <div class="body_title" id="tabs_div" data-bind="template: {name: 'tabs-controls', data: application_data}"></div>
        <div class="mainFrames d-flex flex-row" id="mainFrames_div">
            <div id="framesDisplay" data-bind="template: {name: 'tabs-frames', data: application_data}"></div>
        </div>
    </div>
    <script>
        ko.applyBindings(app_view_model);

        $(function() {
            $('.dropdown-toggle').dropdown();
            $('#patient_caret').click(function() {
                $('#attendantData').slideToggle();
                $('#patient_caret').toggleClass('fa-caret-down').toggleClass('fa-caret-up');
            });
            if ($('body').css('direction') == "rtl") {
                $('.dropdown-menu-right').each(function() {
                    $(this).removeClass('dropdown-menu-right');
                });
            }
        });
        $(function() {
            $('#logo_menu').focus();
        });
        $('#anySearchBox').keypress(function(event) {
            if (event.which === 13 || event.keyCode === 13) {
                event.preventDefault();
                $('#search_globals').mousedown();
            }
        });
        document.addEventListener('touchstart', {}); //specifically added for iOS devices, especially in iframes
        $(function() {
            goRepeaterServices();
        });
    </script>
    <?php
    // fire off an event here
    if (!empty($GLOBALS['kernel']->getEventDispatcher())) {
        /**
         * @var \Symfony\Component\EventDispatcher\EventDispatcher
         */
        $dispatcher = $GLOBALS['kernel']->getEventDispatcher();
        $dispatcher->dispatch(new RenderEvent(), RenderEvent::EVENT_BODY_RENDER_POST);
    }
    ?>
</body>

</html>
