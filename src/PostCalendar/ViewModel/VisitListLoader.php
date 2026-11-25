<?php

/**
 * VisitListLoader
 *
 * Reads what the calendar views need to show Visit Lists: the Visit Lists of the providers
 * shown on the dates shown, and the appointment statuses their rows are colored and labeled by.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar\ViewModel;

use DateTimeImmutable;
use OpenEMR\Appointment\VisitList\VisitListSchedule;
use OpenEMR\Appointment\VisitList\VisitListService;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\PostCalendar\LegacyInputNarrowing;

/**
 * @phpstan-import-type AppointmentStatus from VisitListDayColumn
 */
final class VisitListLoader
{
    /** @var callable(string): list<array<mixed>> */
    private $fetchRecords;

    /** @var callable(string): string */
    private $translate;

    /**
     * @param callable(string): list<array<mixed>>|null $fetchRecords runs a query that has no binds
     * @param callable(string): string|null             $translate    turns a list title into the user's language
     */
    public function __construct(
        private readonly VisitListService $service = new VisitListService(),
        ?callable $fetchRecords = null,
        ?callable $translate = null,
    ) {
        $this->fetchRecords = $fetchRecords ?? static fn(string $sql): array => QueryUtils::fetchRecords($sql);
        $this->translate = $translate ?? xl_list_label(...);
    }

    /**
     * The Visit Lists of the providers shown, from the first to the last of the dates shown
     * (Y-m-d, as the keys of the calendar's events by date); null when no date can be read.
     *
     * @param array<mixed> $dates     the dates the view shows, in any order
     * @param array<mixed> $providers rows with each provider's id
     */
    public function schedule(array $dates, array $providers): ?VisitListSchedule
    {
        $from = null;
        $to = null;
        foreach ($dates as $dateYmd) {
            $date = is_string($dateYmd) ? DateTimeImmutable::createFromFormat('!Y-m-d', $dateYmd) : false;
            if ($date === false || $date->format('Y-m-d') !== $dateYmd) {
                continue;
            }
            if ($from === null || $date < $from) {
                $from = $date;
            }
            if ($to === null || $date > $to) {
                $to = $date;
            }
        }
        if ($from === null || $to === null) {
            return null;
        }

        $providerIds = [];
        foreach ($providers as $provider) {
            $providerId = LegacyInputNarrowing::intValue(is_array($provider) ? ($provider['id'] ?? null) : null);
            if ($providerId > 0) {
                $providerIds[] = $providerId;
            }
        }
        return $this->service->forDates($from, $to, $providerIds);
    }

    /**
     * The active appointment statuses by option_id.
     *
     * @return array<string, AppointmentStatus>
     */
    public function statuses(): array
    {
        return VisitListDayColumn::appointmentStatuses(
            ($this->fetchRecords)(
                "SELECT `option_id`, `title`, `notes` FROM `list_options` WHERE `list_id` = 'apptstat' AND `activity` = 1"
            ),
            $this->translate
        );
    }
}
