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

use Exception;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Events\Messaging\SendNotificationEvent;

class PatientPortalService
{
    public static bool $isSmsEnabled;
    public static bool $isEmailEnabled;
    public static bool $isFaxEnabled;

    public function __construct()
    {
        self::setIsEnabledServices($GLOBALS['oefax_enable_fax'] ?? false, $GLOBALS['oefax_enable_sms'] ?? false, $GLOBALS['oe_enable_email'] ?? false);
    }

    /**
     * @param bool $isSmsEnabled
     */
    public static function setIsEnabledServices(bool $fax, $sms, $email): void
    {
        self::$isFaxEnabled = $fax;
        self::$isSmsEnabled = $sms;
        self::$isEmailEnabled = $email;
    }

    /**
     * @param $pid
     * @return array
     */
    public static function isValidPortalPatient($pid): array
    {
        $patient['valid'] = false;
        if (empty($pid)) {
            return $patient;
        }
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
     * @throws Exception
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
        $message = $message . xl("Please click the below link (only valid for 48 hours) to edit document") . ': "' . $name . "\".\n";

        $statusMsg = xl("Notification requests are being sent!");
        $event_data = [
            'notification_method' => $method,
            'text_message' => $message,
            'html_message' => null,
            'document_id' => $document_id,
            'document_name' => $name,
            'audit_id' => $audit_id,
            'expiry_interval' => $period
        ];
        $eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
        $eventDispatcher->dispatch(new SendNotificationEvent($pid, $event_data), SendNotificationEvent::SEND_NOTIFICATION_SERVICE_ONETIME);
        return text($statusMsg);
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
     * @param string $sect
     * @param string $v
     * @param string $u
     * @return bool
     */
    public static function verifyAcl(string $sect = 'admin', string $v = 'docs', string $u = ''): bool
    {
        return AclMain::aclCheckCore($sect, $v, $u);
    }

    /**
     * @param $u
     * @return mixed
     */
    public static function isPortalUser($u = null)
    {
        $user = $u ?: $_SESSION['authUserID'];
        // test for either id or username
        return sqlQuery("SELECT `portal_user` FROM `users` WHERE `id` = ? OR username = ? LIMIT 1", array($user, $user))['portal_user'];
    }

    /**
     * TODO Move this to AclMain class and refactor portal ACLs
     * It's important to rely on portal user and not ACL.
     * @param string $sect
     * @param string $v
     * @param        $u
     * @return bool
     */
    public static function authPortalUser(string $sect = 'admin', string $v = 'docs', $u = null): bool
    {
        if (empty(self::isPortalUser())) {
            // default is admin forms
            if (!self::verifyAcl($sect, $v)) {
                return false;
            } else {
                return true;
            }
        }
        return true;
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

    /**
     * @return bool
     */
    public static function isSmsEnabled(): bool
    {
        return self::$isSmsEnabled;
    }

    /**
     * @return bool
     */
    public static function isEmailEnabled(): bool
    {
        return self::$isEmailEnabled;
    }

    /**
     * @return bool
     */
    public static function isFaxEnabled(): bool
    {
        return self::$isFaxEnabled;
    }

    /**
     * Currently only used in portal theme setting
     * however the patient_settings table is useful anywhere.
     *
     * @param $setting_patient
     * @param $setting_label
     * @param $setting_value
     * @return int
     */
    public static function persistPatientSetting($setting_patient, $setting_label, $setting_value): int
    {
        $sql = "INSERT INTO `patient_settings` (`setting_patient`, `setting_label`, `setting_value`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `setting_patient` = ?,  `setting_label` = ?, `setting_value` = ?";

        return sqlInsert(
            $sql,
            array(
                $setting_patient ?? 0, $setting_label, $setting_value ?? '',
                $setting_patient ?? 0, $setting_label, $setting_value ?? '')
        );
    }
}
