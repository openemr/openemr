<?php

/**
 * The `appointments` section: which days get appointments, the providers'
 * clinic hours, the categories and statuses drawn for them, and the Visit
 * Lists (made each day by a rule, or written out for one day).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class AppointmentsSpec
{
    public const DEFAULT_VISIT_LIST_CATEGORY = 'office_visit';

    /**
     * @param non-empty-list<Weekday> $weekdays
     * @param list<ClinicSpec> $clinics
     * @param ?WeightedChoices $categories calendar categories (pc_constant_id)
     *        of clinic appointments; needed when there are clinics
     * @param list<VisitListRule> $visitListRules
     * @param list<FixedVisitList> $fixedVisitLists
     */
    public function __construct(
        public DayOffsets $days,
        public array $weekdays,
        public array $clinics,
        public ?WeightedChoices $categories,
        public StatusWeights $status,
        public array $visitListRules,
        public array $fixedVisitLists,
    ) {
    }

    public static function none(): self
    {
        return new self(new DayOffsets(0, 0), [Weekday::Mon], [], null, StatusWeights::none(), [], []);
    }

    public function isEmpty(): bool
    {
        return $this->clinics === [] && $this->visitListRules === [] && $this->fixedVisitLists === [];
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['days', 'weekdays', 'clinics', 'categories', 'status', 'visit_lists']);
        $clinics = array_map(ClinicSpec::parse(...), $map->optionalMapList('clinics'));
        if ($clinics !== [] && !$map->has('categories')) {
            throw SeedFileException::at($map->pathOf('categories'), 'required when there are clinics');
        }
        $rules = [];
        $fixed = [];
        foreach ($map->optionalMapList('visit_lists') as $listMap) {
            if ($listMap->has('day')) {
                $fixed[] = FixedVisitList::parse($listMap);
            } else {
                $rules[] = VisitListRule::parse($listMap);
            }
        }
        $status = $map->optionalMap('status');
        return new self(
            DayOffsets::parse($map, 'days'),
            Weekday::optionalList($map, 'weekdays') ?? [Weekday::Mon, Weekday::Tue, Weekday::Wed, Weekday::Thu, Weekday::Fri],
            $clinics,
            $map->has('categories') ? WeightedChoices::parse($map, 'categories') : null,
            $status === null ? StatusWeights::none() : StatusWeights::parse($status, $lists),
            $rules,
            $fixed,
        );
    }

    /**
     * Every calendar category the section names, by where it is named.
     *
     * @return array<string, string> pc_constant_id by seed-file path
     */
    public function categoryReferences(): array
    {
        $references = [];
        foreach ($this->categories->values ?? [] as $category) {
            $references['appointments.categories.' . $category] = $category;
        }
        foreach ($this->visitListRules as $rule) {
            $references[$rule->path . '.patient_category'] = $rule->patientCategory;
        }
        foreach ($this->fixedVisitLists as $list) {
            $references[$list->path . '.patient_category'] = $list->patientCategory;
        }
        return $references;
    }

    public function hasVisitLists(): bool
    {
        return $this->visitListRules !== [] || $this->fixedVisitLists !== [];
    }
}
