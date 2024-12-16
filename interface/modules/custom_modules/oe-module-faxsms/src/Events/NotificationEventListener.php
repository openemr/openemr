<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Events;

use MyMailer;
use OpenEMR\Common\Auth\OneTimeAuth;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\Messaging\SendNotificationEvent;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationEventListener implements EventSubscriberInterface
{
    /**
     * @var int|mixed
     */
    private bool $isSmsEnabled;
    private mixed $isEmailEnabled;
    private bool $isFaxEnabled;

    public function __construct()
    {
        $this->isSmsEnabled = !empty($GLOBALS['oefax_enable_sms'] ?? 0);
        $this->isFaxEnabled = !empty($GLOBALS['oefax_enable_fax'] ?? 0);
        $this->isEmailEnabled = !empty($GLOBALS['oe_enable_email'] ?? 0);
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SendNotificationEvent::SEND_NOTIFICATION_BY_SERVICE => 'onNotifyEvent',
            SendNotificationEvent::SEND_NOTIFICATION_SERVICE_ONETIME => 'onNotifyDocumentRenderOneTime',
        ];
    }

    /**
     * @param EventDispatcher $eventDispatcher
     * @return void
     */
    public function subscribeToEvents(EventDispatcher $eventDispatcher)
    {
        $eventDispatcher->addListener('sendNotification.send', [$this, 'onNotifySendEvent']);
        $eventDispatcher->addListener('sendNotification.service.onetime', [$this, 'onNotifyDocumentRenderOneTime']);
        $eventDispatcher->addListener(SendNotificationEvent::ACTIONS_RENDER_NOTIFICATION_POST, [$this, 'notificationButton']);
        $eventDispatcher->addListener(SendNotificationEvent::JAVASCRIPT_READY_NOTIFICATION_POST, [$this, 'notificationDialogFunction']);
    }

    /**
     * Send a onetime link to SMS and/or email.
     *
     * @param SendNotificationEvent $event
     * @return string
     * @throws \Exception
     */
    public function onNotifyDocumentRenderOneTime(SendNotificationEvent $event)
    {
        $status = 'Starting request.' . ' ';
        $site_id = ($_SESSION['site_id'] ?? null) ?: 'default';
        $pid = $event->getPid();
        $data = $event->getEventData() ?? [];
        $patient = $event->fetchPatientDetails($pid);
        $text_message = $data['text_message'] ?? xl("Click link to complete document.");
        $html_message = $data['html_message'] ?? '';
        $recipientEmail = $patient['email'];
        $recipientPhone = $patient['phone'];
        $document_id = $data['document_id'] ?? 0;
        $document_name = $data['document_name'] ?? '';
        $audit_id = $data['audit_id'];
        $sendMethod = $data['notification_method'] ?? 'sms';
        $includeSMS = ($sendMethod == 'sms' || $sendMethod == 'both') && $this->isSmsEnabled;
        $includeEmail = $sendMethod == 'email' || $sendMethod == 'both';
        $parameters = [
            'pid' => $pid,
            'redirect_link' => $GLOBALS['web_root'] . "/portal/patient/onsitedocuments?pid=" . urlencode($pid) .
                "&auto_render_id=" . urlencode($document_id) . "&auto_render_name=" . urlencode($document_name) .
                "&audit_render_id=" . urlencode($audit_id) . "&site=" . urlencode($site_id),
            'email' => '',
            'expiry_interval' => $data['expiry_interval'] ?? 'PT60M',
        ];
        $service = new OneTimeAuth();
        $oneTime = $service->createPortalOneTime($parameters);
        if (!isset($oneTime['encoded_link'])) {
            (new SystemLogger())->errorLogCaller("Failed to generate encoded_link with onetime service");
            return 'Failed! Redirect link.';
        }

        $status .= "Send Method: $sendMethod\n";
        $text_message = $text_message . "\n" . $oneTime['encoded_link'];
        if (empty($html_message)) {
            $html_message = "<html><body><div class='wrapper'>" . nl2br($text_message) . "</div></body></html>";
        }

        if ($patient['hipaa_allowsms'] == 'YES' && $includeSMS) {
            $status .= "Sending SMS to " .  text($recipientPhone) . ': ';
            $clientApp = AppDispatch::getApiService('sms');
            $status_api = $clientApp->sendSMS(
                $recipientPhone,
                "",
                $text_message,
                $recipientPhone
            );
            if ($status_api !== true) {
                $status .= text($status_api);
            } else {
                $status .= xlt("Message sent.");
            }
        }
        $status .= "\n";
        if (
            !empty($recipientEmail)
            && ($includeEmail)
            && ($patient['hipaa_allowemail'] == 'YES')
        ) {
            $status .= "Sending email to " . text($recipientEmail) . ': ';
            $status .= text($this->emailNotification($recipientEmail, $html_message));
        }
        $status .= "\n";
        echo (nl2br($status)); //preserve html for alert status
        return 'okay';
    }

    /**
     * Use for SMS send and email
     *
     * @param SendNotificationEvent $event
     * @return string
     */
    public function onNotifySendEvent(SendNotificationEvent $event): string
    {
        $id = $event->getPid();
        $data = $event->getEventData() ?? [];
        $patient = $event->fetchPatientDetails($id);
        $data['recipient_phone'] = $data['recipient_phone'] ?? null;
        $recipientPhone = $data['recipient_phone'] ?: $patient['phone'];
        $status = '';

        if (empty($data['alt_content'] ?? '')) {
            xl("Please follow below link to complete the requested document.");
        } else {
            $message = $data['alt_content'];
        }

        if ($patient['hipaa_allowsms'] == 'YES') {
            $clientApp = AppDispatch::getApiService('sms');
            $status_api = $clientApp->sendSMS(
                $recipientPhone,
                "",
                $message,
                null // will get the "from" phone # from credentials
            );
            if ($status_api !== true) {
                $status .= text($status_api);
            } else {
                $status .= xlt("Message sent.");
            }
        }

        if (
            !empty($patient['email'])
            && ($data['include_email'] ?? false)
            && ($patient['hipaa_allowemail'] == 'YES')
        ) {
            $status .= $this->emailNotification($patient['email'], $message);
        }
        return $status;
    }

    /**
     * @param $email
     * @param $body
     * @param $file
     * @return string
     */
    public function emailNotification($email, $content, $file = null): string
    {
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        $mail = new MyMailer(true);
        $smtpEnabled = $mail::isConfigured();
        if (!$smtpEnabled) {
            return 'Error: ' . xlt("Mail was not sent. A SMTP client is not set up in Config Notifications!.");
        }
        $isHtml = (stripos($content, '<html') !== false) || (stripos($content, '<body') !== false);
        if (!$isHtml) {
            $html = "<html><body><div class='wrapper'>" . nl2br($content) . "</div></body></html>";
        } else {
            $html = $content;
        }
        $from_name = text($from_name);
        $from = $GLOBALS["practice_return_email_path"];
        $mail->AddReplyTo($from, $from_name);
        $mail->SetFrom($from, $from);
        $to = $email;
        $to_name = $email;
        $mail->AddAddress($to, $to_name);
        $subject = xl("Your clinic asks for your attention.");
        $mail->Subject = $subject;
        $mail->MsgHTML($html);
        $mail->IsHTML(true);
        if (!empty($file)) {
            $mail->AddAttachment($file);
        }
        return $mail->Send();
    }

    /**
     * @param SendNotificationEvent $notificationEvent
     * @return void
     */
    public function notificationButton(SendNotificationEvent $notificationEvent): void
    {
        $p_data = $notificationEvent->fetchPatientDetails($notificationEvent->getPid());
        $template = $notificationEvent->getEventData()['id'] ?? '';
        $name = $notificationEvent->getEventData()['template_name'] ?? '';
        $p_data = $p_data ?: [];
        $details = array_merge($p_data, $notificationEvent->getEventData());
        $buttonName = $notificationEvent->getEventData()['is_universal'] == 1 ? xlt('Compose New Email') : xlt('Notify');
        ?>
        <button type="button" class="sendsms float-right btn btn-link btn-send-msg"
            onclick="sendNotification(
            <?php echo attr_js($notificationEvent->getPid()); ?>,
            <?php echo attr_js($name); ?>,
            <?php echo attr_js($template); ?>,
            <?php echo attr_js(json_encode($details)); ?>
                );" value="true"><?php echo $buttonName; ?></button>
    <?php }

    /**
     * @param SendNotificationEvent $event
     * @return void
     */
    public function notificationDialogFunction(SendNotificationEvent $event): void
    {
        $e = $event->getEventData();
        $baseUrl = "/interface/modules/custom_modules/oe-module-faxsms/contact.php?";
        $type = $_REQUEST['type'] ?? 'sms';
        $queryParams = [];
        $queryParams['xmitMode'] = (($this->isSmsEnabled ?? false) && ($this->isEmailEnabled ?? false)) ? 'both' : 'sms';
        $queryParams['isSMS'] = $this->isSmsEnabled;
        $queryParams['isEmail'] = $this->isEmailEnabled;
        $queryParams['isFax'] = $this->isFaxEnabled;
        if ($e['is_onetime'] ?? false) {
            $queryParams['type'] = $this->isSmsEnabled ? 'sms' : 'email';
            $queryParams[$this->isSmsEnabled ? 'isSMS' : 'isEmail'] = 1;
            $queryParams['isOnetime'] = 1;
        } elseif ($e['is_universal'] ?? false) {
            $queryParams['type'] = $this->isEmailEnabled ? 'email' : 'sms';
            $queryParams['isUniversal'] = 1;
        } else {
            $queryParams['type'] = 'sms';
            $queryParams['isSMS'] = 1;
        }
        $queryParams['pid'] = '';
        $url_part = $baseUrl . http_build_query($queryParams);
        $modal = $e['modal_size'] ?? 'modal-md';
        $modal_height = $e['modal_height'] ?? '775';
        $modal_size_height = $e['modal_size_height'] ?? '';
        ?>
        function sendNotification(pid, docName, docId, details) {
            let btnClose = <?php echo xlj("Cancel"); ?>;
            let title = <?php echo xlj("Send Message"); ?>;
            let url = top.webroot_url + '<?php echo $url_part; ?>' + encodeURIComponent(pid) +
                '&title=' + encodeURIComponent(docName) + '&template_id=' + encodeURIComponent(docId) +
                '&details=' + encodeURIComponent(details);
            dlgopen(url, '', '<?php echo attr($modal); ?>', '<?php echo attr($modal_height); ?>', '', title, {
                buttons: [{text: btnClose, close: true, style: 'secondary'}],
                sizeHeight: '<?php echo attr($modal_size_height); ?>',
                allowDrag: true,
                allowResize: true,
            });
        }
    <?php }
}
