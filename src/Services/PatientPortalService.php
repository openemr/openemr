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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Events\Messaging\SendNotificationEvent;

class PatientPortalService
{
    public static bool $isSmsEnabled;
    public static bool $isEmailEnabled;
    public static bool $isFaxEnabled;

    public function __construct()
    {
        self::setIsEnabledServices($GLOBALS['oefax_enable_fax'], $GLOBALS['oefax_enable_sms'], $GLOBALS['oe_enable_email']);
    }

    /**
     * @param bool $isSmsEnabled
     */
    public static function setIsEnabledServices(bool $fax, $sms, $email): void
    {
        self::$isFaxEnabled = $fax ?? false;
        self::$isSmsEnabled = $sms ?? false;
        self::$isEmailEnabled = $email ?? false;
    }

    /**
     * @param bool $isEmailEnabled
     */
    public static function setIsEmailEnabled(bool $isEmailEnabled): void
    {
        self::$isEmailEnabled = $isEmailEnabled;
    }

    /**
     * @param bool $isFaxEnabled
     */
    public static function setIsFaxEnabled(bool $isFaxEnabled): void
    {
        self::$isFaxEnabled = $isFaxEnabled;
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
     * @param        $pid
     * @param        $details
     * @param string $content
     * @return bool|string
     */
    public function dispatchPortalOneTimeDocumentRequest($pid, $details, string $content = ''): bool|string
    {
        $pid = $pid ?: $details['pid'] ?? 0;
        $document_id = $details['document_id'] ?? 0; // if 0 will allow a portal onetime login
        $audit_id = $details['audit_id'];
        $name = $details['document_name'];
        $period = $details['onetime_period'];
        $method = $details['notification_method'] ?? 'both';

        if (empty($pid)) {
            throw new Exception(xlt("Error! Missing patient id."));
        }
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
            'document_id' => $document_id,
            'audit_id' => $audit_id,
            'expiry_interval' => $period
        ];
        $eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
        $eventDispatcher->dispatch(new SendNotificationEvent($pid, $event_data), SendNotificationEvent::SEND_NOTIFICATION_SERVICE_ONETIME);
        return $statusMsg;
    }

    /**
     * @param $id
     * @return bool|array
     */
    public function getPatientDetails($id): bool|array
    {
        $query = "SELECT fname, lname, phone_cell as phone, email, hipaa_allowsms, hipaa_allowemail FROM patient_data WHERE pid = ?";
        $result = sqlQuery($query, array($id));
        return $result ?? false;
    }

    /**
     * @param $sect
     * @param $v
     * @param $u
     * @return bool
     */
    public static function verifyAcl($sect = 'admin', $v = 'docs', $u = ''): bool
    {
        return AclMain::aclCheckCore($sect, $v, $u);
    }

    /**
     * @param $param
     * @param $default
     * If param not valid then entire super is returned.
     * @return mixed
     */
    public function getSession($param = null, $default = null): mixed
    {
        if ($param) {
            return $_SESSION[$param] ?? $default;
        }

        return $_SESSION;
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
