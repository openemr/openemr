<?php

/**
 * FlowBoardFilters
 *
 * The choices in the Flow Board's filter menus (visit categories, statuses, facilities,
 * providers), which one is picked in each, and how wide the Patient ID box needs to be.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\FlowBoard;

use OpenEMR\Common\Database\QueryUtils;
use Symfony\Component\HttpFoundation\InputBag;

final readonly class FlowBoardFilters
{
    /** The Patient ID box fits at least this many characters. */
    private const MIN_PATIENT_ID_LENGTH = 4;

    /**
     * @param list<array{id: string, label: string}> $categories labels untranslated
     * @param list<array{id: string, label: string}> $statuses   labels untranslated
     * @param list<array{id: string, label: string}> $facilities
     * @param list<array{id: string, label: string}> $providers  "Last, First"
     */
    public function __construct(
        public array $categories,
        public array $statuses,
        public array $facilities,
        public array $providers,
        public string $category,
        public string $status,
        public string $facility,
        public string $provider,
        public int $patientIdLength,
    ) {
    }

    /**
     * The menus as the database has them, with the choices posted by the last filter.
     * Providers start at All Providers.
     *
     * @param InputBag<string> $post
     * @param bool $groupTherapy the enable_group_therapy setting: group categories are listed too
     */
    public static function load(InputBag $post, bool $groupTherapy): self
    {
        // As fetchAppointmentCategories() in library/appointments.inc.php.
        $categories = self::options(
            'SELECT `pc_catid` AS `id`, `pc_catname` AS `label` FROM `openemr_postcalendar_categories`'
            . ' WHERE (`pc_active` = 1 AND `pc_recurrtype` = 0 AND `pc_cattype` = 0)'
            . ($groupTherapy ? ' OR `pc_cattype` = 3' : '')
            . ' ORDER BY `label`'
        );
        $statuses = self::options(
            "SELECT `option_id` AS `id`, `title` AS `label` FROM `list_options`"
            . " WHERE `list_id` = 'apptstat' AND `activity` = 1 ORDER BY `seq`"
        );
        $facilities = self::options('SELECT `id`, `name` AS `label` FROM `facility` ORDER BY `id`');
        $providers = self::options(
            "SELECT `id`, CONCAT(`lname`, ', ', `fname`) AS `label` FROM `users`"
            . " WHERE `authorized` = 1 AND `active` = 1 AND `username` > '' ORDER BY `lname`, `fname`"
        );
        $maxPid = QueryUtils::fetchSingleValue('SELECT MAX(`pid`) AS `pid` FROM `patient_data`', 'pid');

        return new self(
            $categories,
            $statuses,
            $facilities,
            $providers,
            $post->getString('form_apptcat'),
            $post->getString('form_apptstatus'),
            $post->getString('form_facility'),
            $post->getString('form_provider'),
            self::patientIdLength(is_numeric($maxPid) ? (string) $maxPid : ''),
        );
    }

    /**
     * Characters the Patient ID box needs for the longest patient ID.
     */
    public static function patientIdLength(string $longestId): int
    {
        return max(self::MIN_PATIENT_ID_LENGTH, strlen($longestId));
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    private static function options(string $sql): array
    {
        $options = [];
        foreach (QueryUtils::fetchRecords($sql) as $row) {
            $id = $row['id'] ?? null;
            $label = $row['label'] ?? null;
            if ((is_string($id) || is_int($id)) && is_string($label)) {
                $options[] = ['id' => (string) $id, 'label' => $label];
            }
        }
        return $options;
    }
}
