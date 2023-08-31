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
use OpenEMR\Events\Messaging\SendNotificationEvent;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use OpenEMR\Modules\FaxSMS\Controller\TwilioSMSClient;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationEventListener implements EventSubscriberInterface
{
    private null|TwilioSMSClient $clientApp;
    private DocumentTemplateService $docClient;

    public function __construct()
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SendNotificationEvent::class => 'onNotifyEvent',
        ];
    }

    public function subscribeToEvents(EventDispatcher $eventDispatcher)
    {
        $eventDispatcher->addListener('sendNotification.send', [$this, 'onNotifyEvent']);
    }

    public function onNotifyEvent(SendNotificationEvent $event)
    {
        $this->clientApp = AppDispatch::getApiService('sms');
        $this->docClient = new DocumentTemplateService();
        $id = $event->getPid();
        $data = $event->getEventData() ?? [];
        $patient = $event->fetchPatientDetails($id);
        $data['recipient_phone'] = $data['recipient_phone'] ?? null;
        $recipientPhone = $data['recipient_phone'] ?: $patient['phone'];
        $message_template = $data['template_name'] ?? 'Default Notification';

        if (empty($data['alt_content'] ?? '')) {
            $message = $this->docClient->getTemplateListByCategory('notification_template', '-1', $message_template)['template_content'] ?? '';
        } else {
            $message = $data['alt_content'];
        }

        if ($patient['hipaa_allowsms'] == 'YES') {
            $this->clientApp->sendSMS(
                $recipientPhone,
                "",
                $message,
                null // will get from phone from credentials
            );
        }

        if (!empty($patient['email']) && ($data['include_email'] ?? false) && ($patient['hipaa_allowemail'] == 'YES')) {
            $this->emailNotification($patient['email'], $message);
        }
    }

    public function emailNotification($email, $body, $file = null): string
    {
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        $mail = new MyMailer();
        $from_name = text($from_name);
        $from = $GLOBALS["practice_return_email_path"];
        $mail->AddReplyTo($from, $from_name);
        $mail->SetFrom($from, $from);
        $to = $email;
        $to_name = $email;
        $mail->AddAddress($to, $to_name);
        $subject = xlt("Your clinic asks for your attention.");
        $mail->Subject = $subject;
        $mail->Body = $body;
        if (!empty($file)) {
            $mail->AddAttachment($file);
        }
        return $mail->Send();
    }
}
