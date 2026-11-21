<?php

/**
 * Works out the appointments and Visit Lists of the `appointments` section
 * over its days, counted from the day the loader runs.
 *
 * Written-out Visit Lists come first, so their patients are free that day;
 * then the Visit List rules, then the clinics. A patient has at most one
 * appointment a day, and of the patients free that day the ones with the
 * fewest appointments so far are drawn first: clinic slots from the
 * provider's own panel (care team) before anyone else, Visit List rules
 * from the preferred patients first. Each slot and list rolls its own dice
 * (seed, provider, day offset, time); statuses roll separate dice, since
 * they also depend on the time the loader runs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

use OpenEMR\DemoSeed\Import\ImportedPatient;
use OpenEMR\DemoSeed\PostalAddress;
use OpenEMR\DemoSeed\SeedFile\AppointmentsSpec;
use OpenEMR\DemoSeed\SeedFile\ClinicSpec;
use OpenEMR\DemoSeed\SeedFile\FixedVisitList;
use OpenEMR\DemoSeed\SeedFile\FixedVisitListEntry;
use OpenEMR\DemoSeed\SeedFile\PatientCondition;
use OpenEMR\DemoSeed\SeedFile\TimeWindow;
use OpenEMR\DemoSeed\SeedFile\VisitListRule;
use OpenEMR\DemoSeed\SeedFile\Weekday;
use OpenEMR\DemoSeed\SeedFile\WeightedChoices;

final class AppointmentPlanner
{
    public const SLOT_MINUTES = 15;

    private readonly \DateTimeImmutable $today;

    /** @var list<ImportedPatient> in import order */
    private array $patients = [];
    /** @var array<int, string> provider key by pid */
    private array $panels = [];
    /** @var array<int, PostalAddress> home by pid */
    private array $homes = [];
    /** @var array<string, array<int, true>> pids with an appointment, by Y-m-d */
    private array $booked = [];
    /** @var array<int, int> appointments so far, by pid */
    private array $counts = [];
    /** @var array<string, true> provider key|Y-m-d of written-out Visit Lists */
    private array $fixedListDays = [];
    /** @var list<PlannedAppointment> */
    private array $appointments = [];
    /** @var list<PlannedVisitList> */
    private array $visitLists = [];

    /**
     * @param \DateTimeImmutable $now when the loader runs; day offsets count
     *        from its day, and today's statuses depend on its time
     * @param array<string, int> $categoryMinutes appointment length by
     *        calendar category (pc_constant_id); missing or 0 = one slot
     */
    public function __construct(
        private readonly AppointmentsSpec $spec,
        private readonly int $seed,
        private readonly \DateTimeImmutable $now,
        private readonly \DateTimeImmutable $referenceDate,
        private readonly array $categoryMinutes,
    ) {
        $this->today = $now->setTime(0, 0);
    }

    /**
     * @param list<ImportedPatient> $patients in import order
     * @param array<int, string> $panels provider key by pid (care team)
     * @param array<string, int> $casePatients pid by case patient key
     * @param array<int, PostalAddress> $homes home address by pid
     * @return array{list<PlannedAppointment>, list<PlannedVisitList>}
     * @throws PlanException
     */
    public function plan(array $patients, array $panels, array $casePatients, array $homes): array
    {
        $this->patients = $patients;
        $this->panels = $panels;
        $this->homes = $homes;
        foreach ($this->spec->fixedVisitLists as $list) {
            $this->planFixedVisitList($list, $casePatients);
        }
        foreach ($this->spec->visitListRules as $rule) {
            foreach ($this->days($rule->weekdays) as $offset => $date) {
                $this->planVisitListFromRule($rule, $offset, $date);
            }
        }
        foreach ($this->spec->clinics as $clinic) {
            foreach ($this->days($clinic->weekdays) as $offset => $date) {
                $this->planClinicDay($clinic, $offset, $date);
            }
        }
        usort(
            $this->appointments,
            static fn (PlannedAppointment $a, PlannedAppointment $b): int => [$a->start, $a->providerKey] <=> [$b->start, $b->providerKey],
        );
        usort(
            $this->visitLists,
            static fn (PlannedVisitList $a, PlannedVisitList $b): int => [$a->start, $a->providerKey] <=> [$b->start, $b->providerKey],
        );
        return [$this->appointments, $this->visitLists];
    }

    /**
     * @param ?list<Weekday> $weekdays null = the section's days
     * @return array<int, \DateTimeImmutable> by day offset
     */
    private function days(?array $weekdays): array
    {
        $weekdays ??= $this->spec->weekdays;
        $days = [];
        for ($offset = $this->spec->days->from; $offset <= $this->spec->days->to; $offset++) {
            $date = $this->today->modify(sprintf('%+d days', $offset));
            if (in_array(Weekday::of($date), $weekdays, true)) {
                $days[$offset] = $date;
            }
        }
        return $days;
    }

    /**
     * @param array<string, int> $casePatients
     * @throws PlanException
     */
    private function planFixedVisitList(FixedVisitList $list, array $casePatients): void
    {
        $date = $this->today->modify(sprintf('%+d days', $list->day));
        while (!in_array(Weekday::of($date), $this->spec->weekdays, true)) {
            $date = $date->modify('+1 day');
        }
        $dateKey = $date->format('Y-m-d');
        $this->fixedListDays[$list->providerKey . '|' . $dateKey] = true;
        $dice = Dice::for($this->seed, 'fixed-visit-list', $list->path);

        $entries = $list->patients;
        usort($entries, static fn (FixedVisitListEntry $a, FixedVisitListEntry $b): int => $a->order <=> $b->order);
        $pids = [];
        foreach ($entries as $entry) {
            if ($entry->patientKey === null) {
                continue;
            }
            $pids[$entry->order] = $casePatients[$entry->patientKey]
                ?? throw new PlanException(sprintf('%s: case patient "%s" was not claimed', $list->path, $entry->patientKey));
            $this->book($pids[$entry->order], $dateKey);
        }
        $planned = [];
        foreach ($entries as $entry) {
            $pid = $pids[$entry->order] ?? null;
            if ($pid === null) {
                $pid = $this->pickPatient($dice, $dateKey, [null]);
                if ($pid === null) {
                    continue;
                }
                $this->book($pid, $dateKey);
            }
            $planned[] = [$pid, $entry->order, $entry->stop];
        }
        $this->addVisitList($list->providerKey, $list->facilityKey, $date, $list->time, $list->patientCategory, $planned);
    }

    private function planVisitListFromRule(VisitListRule $rule, int $offset, \DateTimeImmutable $date): void
    {
        $dateKey = $date->format('Y-m-d');
        if (isset($this->fixedListDays[$rule->providerKey . '|' . $dateKey])) {
            return;
        }
        $dice = Dice::for($this->seed, 'visit-list', $rule->providerKey, (string) $offset, (string) $rule->time->start);
        $count = $dice->between($rule->minPatients, $rule->maxPatients);
        $pids = [];
        for ($i = 0; $i < $count; $i++) {
            $pid = $this->pickPatient($dice, $dateKey, [$rule->prefer, null]);
            if ($pid === null) {
                break;
            }
            $this->book($pid, $dateKey);
            $pids[] = $pid;
        }

        // patients at one address are visited together and share a Stop #
        $groups = [];
        foreach ($pids as $pid) {
            $groups[$this->addressKey($pid)][] = $pid;
        }
        $planned = [];
        $stop = 0;
        foreach ($groups as $group) {
            $stop++;
            foreach ($group as $pid) {
                $planned[] = [$pid, count($planned) + 1, $stop];
            }
        }
        $this->addVisitList($rule->providerKey, $rule->facilityKey, $date, $rule->time, $rule->patientCategory, $planned);
    }

    /**
     * @param list<array{int, int, ?int}> $planned pid, Order #, Stop #
     */
    private function addVisitList(
        string $providerKey,
        string $facilityKey,
        \DateTimeImmutable $date,
        TimeWindow $time,
        string $category,
        array $planned,
    ): void {
        if ($planned === []) {
            return;
        }
        $start = $date->modify(sprintf('+%d minutes', $time->start));
        $entries = [];
        foreach ($planned as $index => [$pid, $sequence, $stop]) {
            // for today's statuses, assume the visits are spread evenly over the block
            $visitAt = $start->modify(sprintf('+%d minutes', intdiv($time->minutes() * $index, count($planned))));
            $entries[] = new PlannedVisitListEntry(
                $pid,
                $category,
                $sequence,
                $stop,
                $this->status($providerKey, $visitAt),
            );
        }
        $this->visitLists[] = new PlannedVisitList(
            $providerKey,
            $facilityKey,
            $start,
            $date->modify(sprintf('+%d minutes', $time->end)),
            $entries,
        );
    }

    private function planClinicDay(ClinicSpec $clinic, int $offset, \DateTimeImmutable $date): void
    {
        $categories = $this->spec->categories;
        if ($categories === null) {
            return;
        }
        $dateKey = $date->format('Y-m-d');
        foreach ($clinic->hours as $window) {
            $minute = $window->start;
            while ($minute + self::SLOT_MINUTES <= $window->end) {
                $dice = Dice::for($this->seed, 'clinic', $clinic->providerKey, (string) $offset, (string) $minute);
                if (!$dice->percent($clinic->fill)) {
                    $minute += self::SLOT_MINUTES;
                    continue;
                }
                $category = self::pick($dice, $categories);
                $length = $this->length($category);
                if ($minute + $length > $window->end) {
                    $minute += self::SLOT_MINUTES;
                    continue;
                }
                $pid = $this->pickPatient($dice, $dateKey, [$clinic->providerKey, null]);
                if ($pid === null) {
                    return;
                }
                $this->book($pid, $dateKey);
                $start = $date->modify(sprintf('+%d minutes', $minute));
                $this->appointments[] = new PlannedAppointment(
                    $pid,
                    $clinic->providerKey,
                    $clinic->facilityKey,
                    $category,
                    $start,
                    $length,
                    $this->status($clinic->providerKey, $start),
                );
                $minute += $length;
            }
        }
    }

    /**
     * A patient free that day: from the first group that has any, one of
     * those with the fewest appointments so far.
     *
     * @param non-empty-list<PatientCondition|string|null> $groups a
     *        condition, a provider key (that provider's panel) or null
     *        (anyone), in the order to try
     */
    private function pickPatient(Dice $dice, string $dateKey, array $groups): ?int
    {
        foreach ($groups as $group) {
            $candidates = [];
            foreach ($this->patients as $patient) {
                if (isset($this->booked[$dateKey][$patient->pid]) || !$this->inGroup($patient, $group)) {
                    continue;
                }
                $candidates[$this->counts[$patient->pid] ?? 0][] = $patient->pid;
            }
            if ($candidates !== []) {
                ksort($candidates);
                return $dice->pick($candidates[array_key_first($candidates)]);
            }
        }
        return null;
    }

    private function inGroup(ImportedPatient $patient, PatientCondition|string|null $group): bool
    {
        if ($group === null) {
            return true;
        }
        if (is_string($group)) {
            return ($this->panels[$patient->pid] ?? null) === $group;
        }
        return $group->matches($patient->synthea, $this->referenceDate);
    }

    private function book(int $pid, string $dateKey): void
    {
        $this->booked[$dateKey][$pid] = true;
        $this->counts[$pid] = ($this->counts[$pid] ?? 0) + 1;
    }

    private function addressKey(int $pid): string
    {
        $home = $this->homes[$pid] ?? null;
        if ($home === null) {
            return 'pid:' . $pid;
        }
        return strtolower(implode('|', [$home->street, $home->city, $home->postalCode]));
    }

    /**
     * Minutes an appointment of the category takes, in whole slots.
     */
    private function length(string $category): int
    {
        $minutes = $this->categoryMinutes[$category] ?? 0;
        $slots = max(1, intdiv($minutes + self::SLOT_MINUTES - 1, self::SLOT_MINUTES));
        return $slots * self::SLOT_MINUTES;
    }

    /**
     * Past days use the past weights; today, appointments that have started
     * by now use today's; later ones and future days use the future weights.
     */
    private function status(string $providerKey, \DateTimeImmutable $start): string
    {
        $weights = match (true) {
            $start < $this->today => $this->spec->status->past,
            $start <= $this->now => $this->spec->status->today,
            default => $this->spec->status->future,
        };
        $dice = Dice::for($this->seed, 'appointment-status', $providerKey, $start->format('Y-m-d H:i'));
        return self::pick($dice, $weights);
    }

    private static function pick(Dice $dice, WeightedChoices $choices): string
    {
        return $choices->values[$dice->weighted($choices->weights)] ?? $choices->values[0];
    }
}
