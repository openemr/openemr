<?php

/**
 * VisitListDayLoader
 *
 * Reads what the calendar day view needs to show Visit Lists: the day's Visit Lists for the
 * providers shown, and the appointment statuses their rows are colored and labeled by.
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
final class VisitListDayLoader
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
     * The Visit Lists on one day (Ymd) of the providers shown, as the day view's provider
     * rows list them; null when the date cannot be read.
     *
     * @param array<mixed> $providers rows with each provider's id
     */
    public function schedule(string $dateYmd, array $providers): ?VisitListSchedule
    {
        $date = DateTimeImmutable::createFromFormat('!Ymd', $dateYmd);
        if ($date === false) {
            return null;
        }

        $providerIds = [];
        foreach ($providers as $provider) {
            $providerId = LegacyInputNarrowing::intValue(is_array($provider) ? ($provider['id'] ?? null) : null);
            if ($providerId > 0) {
                $providerIds[] = $providerId;
            }
        }
        return $this->service->forDates($date, $date, $providerIds);
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
