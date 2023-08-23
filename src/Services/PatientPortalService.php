<?php

/**
 * Portal Base Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Events\Messaging\SendNotificationEvent;

class PatientPortalService
{
    private bool $isSmsEnabled;
    private bool $isEmailEnabled;

    public function __construct()
    {
        $this->isSmsEnabled = !empty($GLOBALS['oefax_enable_sms'] ?? 0);
        $this->isEmailEnabled = !empty($GLOBALS['oe_enable_email'] ?? 0);
    }

    /**
     * @throws \Exception
     */
    public function dispatchPortalOneTimeDocumentRequest($pid, $details, $content = '')
    {
        $pid = $pid ?: $details['pid'] ?? 0;
        $document_id = $details['document_id'] ?? 0; // if 0 will allow a portal onetime login
        $audit_id = $details['audit_id'];
        $name = $details['document_name'];
        $period = $details['onetime_period'];
        $method = $details['notification_method'] ?? 'both';
        $message = '';

        if (!empty($content)) {
            $message = xl("Provider comment") . ": " . $content . "\n";
        }
        $message = $message .  xl("Click link to complete document") . " " . $name . ".\n";

        $eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
        $statusMsg = xlt("SMS and or Email notify requests in process.") . "<br />";
        $event_data = [
            'notification_method' => $method,
            'text_message' => $message,
            'html_message' => null,
            'recipient_phone' => $this->getRequest('phone', $details['phone'] ?? ''),
            'document_id' => $document_id,
            'audit_id' => $audit_id,
            'expiry_interval' => $period
        ];
        $eventDispatcher->dispatch(new SendNotificationEvent($pid, $event_data), SendNotificationEvent::SEND_NOTIFICATION_SERVICE_ONETIME);
        return js_escape($statusMsg);
    }

    public function getRequest($param = null, $default = null): mixed
    {
        if ($param) {
            return $_REQUEST[$param] ?? $default;
        }

        return $_REQUEST;
    }

    public function getPost($param = null, $default = null): mixed
    {
        if ($param) {
            return $_POST[$param] ?? $default;
        }

        return $_POST;
    }


    public function getGET($param = null, $default = null): mixed
    {
        if ($param) {
            return $_GET[$param] ?? $default;
        }

        return $_GET;
    }
}
