<?php

/**
 * Bootstrap for custom Fax SMS module.
 * Since this was our original example module,
 * I've left it basically intact from original.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/*
 This module uses an abstract class to arbitrate and dispatch
 API calls to different vendor services for both the fax and sms type on a per-call basis.
 To add new vendors, just follow and use the existing dispatching flow
 for an existing service type and vendor service.
 */

use OpenEMR\Events\Messaging\SendSmsEvent;
use OpenEMR\Events\PatientDocuments\PatientDocumentEvent;
use OpenEMR\Events\PatientReport\PatientReportEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Modules\FaxSMS\Events\NotificationEventListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

// some flags
$allowFax = ($GLOBALS['oefax_enable_fax'] ?? null);
$allowSMS = ($GLOBALS['oefax_enable_sms'] ?? null);
$allowSMSButtons = ($GLOBALS['oesms_send'] ?? null);
$allowEmail = ($GLOBALS['oe_enable_email'] ?? null);

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\FaxSMS\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

require __DIR__ . '/vendor/autoload.php';

/**
 * @var EventDispatcherInterface $eventDispatcher
 * @var array                    $module
 * @global                       $eventDispatcher @see ModulesApplication::loadCustomModule
 * @global                       $module          @see ModulesApplication::loadCustomModule
 */

/**
 * @global EventDispatcher $dispatcher Injected by the OpenEMR module loader;
 */
$dispatcher = $GLOBALS['kernel']->getEventDispatcher();

// Add menu items
function oe_module_faxsms_add_menu_item(MenuEvent $event): MenuEvent
{
    $allowFax = ($GLOBALS['oefax_enable_fax'] ?? null);
    $allowSMS = ($GLOBALS['oefax_enable_sms'] ?? null);
    $allowEmail = ($GLOBALS['oe_enable_email'] ?? null);
    $menu = $event->getMenu();
    // Our SMS menu
    $menuItem = new stdClass();
    $menuItem->requirement = 0;
    $menuItem->target = 'sms';
    $menuItem->menu_id = 'mod0';
    $menuItem->label = $allowSMS == '2' ? xlt("Twilio Messaging") : xlt("RingCentral SMS");
    $menuItem->url = "/interface/modules/custom_modules/oe-module-faxsms/messageUI.php?type=sms";
    $menuItem->children = [];
    $menuItem->acl_req = ["patients", "docs"];
    $menuItem->global_req = ["oefax_enable_sms"];
    // Our FAX menu
    $menuItem2 = new stdClass();
    $menuItem2->requirement = 0;
    $menuItem2->target = 'fax';
    $menuItem2->menu_id = 'mod1';
    $menuItem2->label = $allowFax == '3' ? xlt("Manage etherFAX") : xlt("RingCentral FAX");
    $menuItem2->url = "/interface/modules/custom_modules/oe-module-faxsms/messageUI.php?type=fax";
    $menuItem2->children = [];
    $menuItem2->acl_req = ["patients", "docs"];
    $menuItem2->global_req = ["oefax_enable_fax"];

    // email reminders
    $menuItem3 = new stdClass();
    $menuItem3->requirement = 0;
    $menuItem3->target = 'fax';
    $menuItem3->menu_id = 'mod1';
    $menuItem3->label = xlt("Test Email Reminders");
    $menuItem3->url = "/interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php?dryrun=1&alert=0&type=email&site=" . $_SESSION['site_id'];
    $menuItem3->children = [];
    $menuItem3->acl_req = ["patients", "docs"];
    $menuItem3->global_req = ["oe_enable_email"];

    $menuItem4 = new stdClass();
    $menuItem4->requirement = 0;
    $menuItem4->target = 'fax';
    $menuItem4->menu_id = 'mod1';
    $menuItem4->label = xlt("Send Email Reminders");
    $menuItem4->url = "/interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php?alert=1&type=email&site=" . $_SESSION['site_id'];
    $menuItem4->children = [];
    $menuItem4->acl_req = ["patients", "docs"];
    $menuItem4->global_req = ["oe_enable_email"];

    $menuItemSetup = new stdClass();
    $menuItemSetup->requirement = 0;
    $menuItemSetup->target = 'setup';
    $menuItemSetup->menu_id = 'mod2';
    $menuItemSetup->label = xlt("Setup Services");
    $menuItemSetup->url = "/interface/modules/custom_modules/oe-module-faxsms/library/setup_services.php?module_config=1";
    $menuItemSetup->children = [];
    $menuItemSetup->acl_req = ["admin", "docs"];

    $subMenu = new stdClass();
    $subMenu->requirement = 0;
    $subMenu->target = 'subsrv';
    $subMenu->menu_id = 'reminders';
    $subMenu->label = xlt("Notifications");
    $subMenu->children = [$menuItem3, $menuItem4];
    $subMenu->acl_req = [
        "admin",
        "demo"
    ];

    // Top level menu
    $topMenu = new stdClass();
    $topMenu->requirement = 0;
    $topMenu->target = 'serv';
    $topMenu->menu_id = 'service';
    $topMenu->label = xlt("Services");
    $topMenu->children = [$subMenu];
    $topMenu->acl_req = [
        "patients",
        "demo"
    ];

    $i = 0;
    foreach ($menu as $item) {
        if ($item->menu_id == 'modimg') {
            $menu[++$i] = $topMenu;
            $i++;
            continue;
        }
        $menu[$i] = $item;
        $i++;
    }
        // Child of Services top menu.
    foreach ($menu as $item) {
        if ($item->menu_id == 'service') {
            // ensure service is on in globals.
            if (!empty($allowSMS) || !empty($allowFax) || !empty($allowEmail)) {
                $item->children[] = $menuItemSetup;
            }
            if (!empty($allowFax)) {
                $item->children[] = $menuItem2;
            }
            if (!empty($allowSMS)) {
                $item->children[] = $menuItem;
            }
            break;
        }
    }
    $event->setMenu($menu);

    return $event;
}

$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'oe_module_faxsms_add_menu_item');

/* Moved globals Module setup section to module config panel. That's why it's there. */

// patient report send fax button
function oe_module_faxsms_patient_report_render_action_buttons(Event $event): void
{
    ?>
<button type="button" class="genfax btn btn-success btn-sm btn-send-msg" value="<?php echo xla('Send Fax'); ?>"><?php echo xlt('Send Fax'); ?></button><span id="waitplace"></span>
<input type='hidden' name='fax' value='0'>
<?php }

function oe_module_faxsms_patient_report_render_javascript_post_load(Event $event): void
{
    ?>
function getFaxContent() {
    top.restoreSession();
    document.report_form.fax.value = 1;
    let url = 'custom_report.php';
    let wait = '<span id="wait"><?php echo '  ' . xlt("Building Document") . ' ... '; ?><i class="fa fa-cog fa-spin fa-2x"></i></span>';
    $("#waitplace").append(wait);
    $.ajax({
    type: "POST",
    url: url,
    data: $("#report_form").serialize(),
    success: function (content) {
    document.report_form.fax.value = 0;
    let btnClose = <?php echo xlj("Cancel"); ?>;
    let title = <?php echo xlj("Send To Contact"); ?>;
    let url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/contact.php?isContent=0&type=fax&file=' + encodeURIComponent(content);
    dlgopen(url, '', 'modal-sm', 700, '', title, {buttons: [{text: btnClose, close: true, style: 'secondary'}]});
    return false;
    }
    }).always(function () {
    $("#wait").remove();
    });
    return false;
}
$(".genfax").click(function() {getFaxContent();});
<?php }

// patient documents fax anchor
function oe_module_faxsms_document_render_action_anchors(Event $event): void
{
    ?>
<a class="btn btn-success btn-sm btn-send-msg" href="" onclick="return doFax(event,file,mime)">
    <span><?php echo xlt('Send Fax'); ?></span>
</a>
<?php }

function oe_module_faxsms_document_render_javascript_fax_dialog(Event $event): void
{
    ?>
function doFax(e, filePath, mime='') {
    e.preventDefault();
    let btnClose = <?php echo xlj("Cancel"); ?>;
    let title = <?php echo xlj("Send To Contact"); ?>;
    let url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/contact.php?isDocuments=1&type=fax&file=' +
    encodeURIComponent(filePath) + '&mime=' + encodeURIComponent(mime) + '&docid=' + encodeURIComponent(docid);
    dlgopen(url, 'faxto', 'modal-md', 'full', '', title, {
    buttons: [],
    sizeHeight: 'full',
    resolvePromiseOn: 'close'
    }).then(function () {
        top.restoreSession();
    });
    return false;
}
<?php }

function oe_module_faxsms_sms_render_action_buttons(Event $event): void
{
    ?>
<button type="button" class="sendsms btn btn-success btn-send-msg" onclick="sendSMS(<?php echo attr_js($event->getPid()); ?>, <?php echo attr_js($event->getRecipientPhone()); ?>);" value="true"><?php echo xlt('Send SMS'); ?></button>
<?php }

function oe_module_faxsms_sms_render_javascript_post_load(Event $event): void
{
    ?>
function sendSMS(pid, phone) {
    let btnClose = <?php echo xlj("Cancel"); ?>;
    let title = <?php echo xlj("Send SMS Message"); ?>;
    let url = top.webroot_url +
    '/interface/modules/custom_modules/oe-module-faxsms/contact.php?type=sms&isSMS=1&pid=' + encodeURIComponent(pid) +
    '&recipient=' + encodeURIComponent(phone);
    dlgopen(url, '', 'modal-md', 700, '', title, {
    buttons: [{text: btnClose, close: true, style: 'secondary'}]
    });
}
<?php }

// Add our listeners.
if ($allowFax) {
    // patient report
    $eventDispatcher->addListener(PatientReportEvent::ACTIONS_RENDER_POST, 'oe_module_faxsms_patient_report_render_action_buttons');
    $eventDispatcher->addListener(PatientReportEvent::JAVASCRIPT_READY_POST, 'oe_module_faxsms_patient_report_render_javascript_post_load');
    // documents
    $eventDispatcher->addListener(PatientDocumentEvent::ACTIONS_RENDER_FAX_ANCHOR, 'oe_module_faxsms_document_render_action_anchors');
    $eventDispatcher->addListener(PatientDocumentEvent::JAVASCRIPT_READY_FAX_DIALOG, 'oe_module_faxsms_document_render_javascript_fax_dialog');
}

if ($allowSMSButtons) {
    $eventDispatcher->addListener(SendSmsEvent::ACTIONS_RENDER_SMS_POST, 'oe_module_faxsms_sms_render_action_buttons');
    $eventDispatcher->addListener(SendSmsEvent::JAVASCRIPT_READY_SMS_POST, 'oe_module_faxsms_sms_render_javascript_post_load');
}

if (!(empty($_SESSION['authUserID'] ?? null) && ($_SESSION['pid'] ?? null)) && $allowSMS) {
    (new NotificationEventListener())->subscribeToEvents($eventDispatcher);
}
