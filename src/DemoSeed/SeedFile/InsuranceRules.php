<?php

/**
 * The `rules.insurance` section: plan sets (the first that fits a patient
 * wins), earlier ended policies, and how policy and group numbers look.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class InsuranceRules
{
    public const DEFAULT_POLICY_FORMAT = '{CMS}{8 digits}';

    /**
     * @param list<InsurancePlanSet> $plans
     * @param int $endedPercent chance a patient's primary policy replaced an
     *        earlier one
     */
    public function __construct(
        public array $plans,
        public int $endedPercent,
        public DayRange $endedDaysAgo,
        public NumberFormat $policyNumbers,
        public int $groupPercent,
        public ?NumberFormat $groupNumbers,
    ) {
    }

    public static function none(): self
    {
        return new self([], 0, new DayRange(0, 0), self::defaultPolicyFormat(), 0, null);
    }

    public static function defaultPolicyFormat(): NumberFormat
    {
        return NumberFormat::parse(self::DEFAULT_POLICY_FORMAT, 'default policy number format');
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['plans', 'ended', 'policy_numbers', 'group_numbers']);

        $ended = $map->optionalMap('ended');
        $ended?->rejectUnknownKeys(['percent', 'ended_days_ago']);
        $policyNumbers = $map->optionalMap('policy_numbers');
        $policyNumbers?->rejectUnknownKeys(['format']);
        $groupNumbers = $map->optionalMap('group_numbers');
        $groupNumbers?->rejectUnknownKeys(['percent', 'format']);

        return new self(
            array_map(InsurancePlanSet::parse(...), $map->optionalMapList('plans')),
            $ended?->percent('percent') ?? 0,
            $ended === null ? new DayRange(0, 0) : DayRange::fromValue($ended->raw('ended_days_ago'), $ended->pathOf('ended_days_ago')),
            $policyNumbers === null
                ? self::defaultPolicyFormat()
                : NumberFormat::parse($policyNumbers->string('format'), $policyNumbers->pathOf('format')),
            $groupNumbers === null ? 0 : ($groupNumbers->has('percent') ? $groupNumbers->percent('percent') : 100),
            $groupNumbers === null ? null : NumberFormat::parse($groupNumbers->string('format'), $groupNumbers->pathOf('format')),
        );
    }

    /**
     * @return list<string> every company key the rules name
     */
    public function companyKeys(): array
    {
        $keys = [];
        foreach ($this->plans as $plan) {
            foreach ($plan->options as $option) {
                foreach ($option->policies as $policy) {
                    $keys = [...$keys, ...$policy->companyKeys];
                }
            }
        }
        return array_values(array_unique($keys));
    }
}
