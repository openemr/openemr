<?php

/**
 *
 * @package        OpenEMR
 * @link           https://www.open-emr.org
 * @author         Jerry Padgett <sjpadgett@gmail.com>
 * @author         Michael A. Smith <michael@opencoreemr.com>
 * @copyright      Copyright (c) 2025 <sjpadgett@gmail.com>
 * @copyright      Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use OpenEMR\Services\Background\BackgroundServiceDefinition;
use OpenEMR\Services\Background\BackgroundServiceRegistry;

/**
 * Manages background task operations for Notifications.
 */
class NotificationTaskManager
{
    public function getTaskHours($type): int
    {
        if ($type == 'email') {
            $name = 'Notification_Email_Task';
        } elseif ($type == 'sms') {
            $name = 'Notification_SMS_Task';
        } else {
            return 24;
        }

        // Get the interval in minutes
        $total_minutes = sqlQueryNoLog("SELECT `execute_interval` as hours FROM `background_services` WHERE `name` = ?", [$name])['hours'] ?? 0;
        return $total_minutes > 0 ? $total_minutes / 60 : 24;
    }

    /**
     * Check whether a reminder falls within the cron send window.
     *
     * Both parameters use whole-hour granularity. $remainHour is the
     * difference between hours-until-appointment and the configured
     * notification lead time. A value of 0 means "exactly time to send";
     * negative means the ideal send time has passed.
     *
     * $cronIntervalHours is the background-service execution interval
     * converted to hours (via getTaskHours()). Values below 1 are clamped
     * to 1 so the window is never zero-width.
     */
    public static function isWithinCronWindow(int $remainHour, int $cronIntervalHours): bool
    {
        // Enforce a minimum 1-hour window. getTaskHours() returns int, so
        // sub-hour intervals (e.g. 30 minutes) truncate to 0 — which would
        // make the window zero-width and silently suppress all sends.
        $cronIntervalHours = max(1, $cronIntervalHours);
        return $remainHour >= -$cronIntervalHours && $remainHour <= $cronIntervalHours;
    }

    /**
     * Creates or updates the background Notification task.
     *
     * $hours is the execution interval in whole hours. When $hours is 0
     * (the default), the existing DB interval is read via getTaskHours()
     * and used instead. The value stored in `background_services.execute_interval`
     * is always in minutes, so both branches convert hours -> minutes.
     *
     * Returns true when a new service row was created, false when an
     * existing row was updated (or when $type is invalid). Uses
     * BackgroundServiceRegistry so the admin's enable/disable toggle is
     * preserved on re-registration.
     */
    public function manageService($type, $hours = 0): bool
    {
        if ($type == 'sms') {
            $name = 'Notification_SMS_Task';
            $fn = 'doSmsNotificationTask';
        } elseif ($type == 'email') {
            $name = 'Notification_Email_Task';
            $fn = 'doEmailNotificationTask';
        } else {
            return false;
        }

        // Callers treat $hours as an integer number of hours, but legacy
        // code paths pass strings from $_POST. Normalize to int and fall
        // back to the stored DB interval when no explicit value is given.
        $hoursInt = is_numeric($hours) ? (int) $hours : 0;
        $intervalHours = $hoursInt > 0 ? $hoursInt : $this->getTaskHours($type);
        $total_minutes = $intervalHours * 60;

        $registry = new BackgroundServiceRegistry();
        $isNew = !$registry->exists($name);
        $registry->register(new BackgroundServiceDefinition(
            name: $name,
            title: 'Scheduled Automated Notifications',
            function: $fn,
            requireOnce: '/interface/modules/custom_modules/oe-module-faxsms/library/run_notifications.php',
            executeInterval: $total_minutes,
            sortOrder: 100,
            active: false,
        ));

        return $isNew;
    }

    /**
     * Deletes the background Notification task.
     */
    public function deleteService($type)
    {
        if ($type == 'sms') {
            $name = 'Notification_SMS_Task';
        } elseif ($type == 'email') {
            $name = 'Notification_Email_Task';
        } else {
            return false;
        }

        $sql = "DELETE FROM `background_services` WHERE `name` = ?";
        sqlStatementNoLog($sql, [$name]);
    }

    /**
     * Enables the background Notification task.
     */
    public function enableService($type, $period = 24)
    {
        if ($type == 'sms') {
            $name = 'Notification_SMS_Task';
        } elseif ($type == 'email') {
            $name = 'Notification_Email_Task';
        } else {
            return false;
        }
        $this->manageService($type, $period);

        $sql = "UPDATE `background_services` SET `active` = '1' WHERE `name` = ?";
        sqlStatementNoLog($sql, [$name]);
    }

    /**
     * Disables the background Notification task.
     */
    public function disableService($type)
    {
        if ($type == 'sms') {
            $name = 'Notification_SMS_Task';
        } elseif ($type == 'email') {
            $name = 'Notification_Email_Task';
        } else {
            return false;
        }

        $sql = "UPDATE `background_services` SET `active` = '0' WHERE `name` = ?";
        sqlStatementNoLog($sql, [$name]);
    }

    public function getServiceStatus($type): false|array|string
    {
        if ($type == 'sms') {
            $name = 'Notification_SMS_Task';
        } elseif ($type == 'email') {
            $name = 'Notification_Email_Task';
        } else {
            return false;
        }

        $sql = "SELECT * FROM `background_services` WHERE `name` = ?";
        $result[$type] = sqlQueryNoLog($sql, [$name]);

        return $result;
    }
}
