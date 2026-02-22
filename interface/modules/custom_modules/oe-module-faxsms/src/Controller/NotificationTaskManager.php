<?php

/**
 *
 * @package        OpenEMR
 * @link           https://www.open-emr.org
 * @author         Jerry Padgett <sjpadgett@gmail.com>
 * @copyright      Copyright (c) 2025 <sjpadgett@gmail.com>
 * @license        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

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
     * Creates or updates the background Notification task
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

        $total_minutes = empty($hours) ? $this->getTaskHours($type) : $hours * 60;

        $sql = "SELECT COUNT(*) as count FROM `background_services` WHERE `name` = ?";
        $result = sqlQueryNoLog($sql, [$name]);
        if ($result['count'] > 0) {
            $sql = "UPDATE `background_services` SET `execute_interval` = ? WHERE `name` = ?";
            sqlStatementNoLog($sql, [$total_minutes, $name]);
            return false;
        }

        $sql = "INSERT INTO `background_services`
                (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`)
                VALUES (?, 'Scheduled Automated Notifications', '0', '0', current_timestamp(), ?, ?, '/interface/modules/custom_modules/oe-module-faxsms/library/run_notifications.php', '100')";
        sqlStatementNoLog($sql, [$name, $total_minutes, $fn]);

        return true;
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
