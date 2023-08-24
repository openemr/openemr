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
     * @param $pid
     * @return array
     */
    public static function isValidPortalPatient($pid): array
    {
        // ensure both portal and patient data match using portal account id.
        $patient = sqlQuery(
            "Select `pid`, `email`, `email_direct` From `patient_data` Where `pid` = ?",
            array($pid)
        );
        $portal = sqlQuery(
            "Select `pid`, `portal_username` From `patient_access_onsite` Where `pid` = ?",
            array($patient['pid'])
        );

        $patient['valid'] = !empty($portal['portal_username']) && ((int)$pid === (int)$portal['pid']);

        return $patient;
    }

    /**
     * @param $pid
     * @param $details
     * @param $content
     * @return bool|string
     */
    public function dispatchPortalOneTimeDocumentRequest($pid, $details, $content = ''): bool|string
    {
        $pid = $pid ?: $details['pid'] ?? 0;
        $document_id = $details['document_id'] ?? 0; // if 0 will allow a portal onetime login
        $audit_id = $details['audit_id'];
        $name = $details['document_name'];
        $period = $details['onetime_period'];
        $method = $details['notification_method'] ?? 'both';

        $message = '';
        if (!empty($content)) {
            $message = xl("Comment from provider") . ": " . $content . "\n";
        }
        $message = $message . xl("Please follow below to edit document.") . ': "' . $name . "\".\n";

        $statusMsg = xl("Notification requests in process!") . "<br />";
        $event_data = [
            'notification_method' => $method,
            'text_message' => $message,
            'html_message' => null,
            'recipient_phone' => $this->getRequest('phone', $details['phone'] ?? ''),
            'document_id' => $document_id,
            'audit_id' => $audit_id,
            'expiry_interval' => $period
        ];
        $eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
        $eventDispatcher->dispatch(new SendNotificationEvent($pid, $event_data), SendNotificationEvent::SEND_NOTIFICATION_SERVICE_ONETIME);
        return js_escape($statusMsg);
    }

    /**
     * @param $param
     * @param $default
     * @return mixed
     */
    public function getRequest($param = null, $default = null): mixed
    {
        if ($param) {
            return $_REQUEST[$param] ?? $default;
        }

        return $_REQUEST;
    }

    /**
     * @param $param
     * @param $default
     * @return mixed
     */
    public function getPost($param = null, $default = null): mixed
    {
        if ($param) {
            return $_POST[$param] ?? $default;
        }

        return $_POST;
    }


    /**
     * @param $param
     * @param $default
     * @return mixed
     */
    public function getGET($param = null, $default = null): mixed
    {
        if ($param) {
            return $_GET[$param] ?? $default;
        }

        return $_GET;
    }
}
