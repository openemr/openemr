<?php

/**
 * Pure helpers for the Fax/SMS module's disable/enable lifecycle. Kept free
 * of database access so they can be tested in isolation; callers do the I/O
 * and pass the results in.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS;

final class ModuleLifecycleState
{
    /**
     * Background services owned by this module.
     */
    public const REMINDER_TASKS = ['Notification_SMS_Task', 'Notification_Email_Task'];

    /**
     * Decode settings saved as JSON when the module was disabled.
     *
     * @return array<mixed> The saved settings, or an empty array when nothing usable was stored.
     */
    public static function decodeSettings(mixed $stored): array
    {
        if (!is_string($stored)) {
            return [];
        }
        $settings = json_decode($stored, true);
        return is_array($settings) ? $settings : [];
    }

    /**
     * Names of this module's reminder tasks found in background_services rows.
     *
     * @param array<mixed> $rows Rows that each carry a `name` column.
     * @return list<string>
     */
    public static function taskNames(array $rows): array
    {
        $names = [];
        foreach ($rows as $row) {
            $name = is_array($row) ? ($row['name'] ?? null) : null;
            if (is_string($name) && in_array($name, self::REMINDER_TASKS, true)) {
                $names[] = $name;
            }
        }
        return $names;
    }

    /**
     * Reminder tasks to restart on enable, from the JSON saved on disable.
     * Anything that is not one of this module's tasks is ignored.
     *
     * @return list<string>
     */
    public static function tasksToRestore(mixed $stored): array
    {
        $names = [];
        foreach (self::decodeSettings($stored) as $name) {
            if (is_string($name) && in_array($name, self::REMINDER_TASKS, true)) {
                $names[] = $name;
            }
        }
        return $names;
    }
}
