<?php

/**
 * One way a patient can be insured under a `rules.insurance` plan set: its
 * policies by type, or self pay. A plan set without `choose` has one
 * option; with `choose`, one is picked per patient by weight.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class InsuranceOption
{
    /**
     * @param array<value-of<PolicyType>, PolicyRule> $policies by type, primary first
     * @param list<SubscriberKind> $needs related people the patient must have
     *        (from `requires` and from the subscribers)
     */
    public function __construct(
        public int $weight,
        public bool $selfPay,
        public array $policies,
        public array $needs,
    ) {
    }

    /**
     * @param list<string> $extraKeys keys the caller reads itself
     */
    public static function parse(MapReader $map, array $extraKeys = []): self
    {
        $map->rejectUnknownKeys(['weight', 'requires', 'self_pay', 'primary', 'secondary', 'tertiary', ...$extraKeys]);
        $selfPay = $map->optionalBool('self_pay');
        $policies = [];
        foreach (PolicyType::cases() as $type) {
            $policyMap = $map->optionalMap($type->value);
            if ($policyMap !== null) {
                $policies[$type->value] = PolicyRule::parse($policyMap);
            }
        }
        if ($selfPay === ($policies !== [])) {
            throw SeedFileException::at($map->path, 'give self_pay: true or a primary policy, not both');
        }
        if ($policies !== [] && !isset($policies[PolicyType::Primary->value])) {
            throw SeedFileException::at($map->path, 'a secondary or tertiary policy needs a primary');
        }
        $needs = [];
        $requires = $map->optionalString('requires');
        if ($requires !== null) {
            $needs[] = match ($requires) {
                'spouse' => SubscriberKind::Spouse,
                'guardian' => SubscriberKind::Guardian,
                default => throw SeedFileException::at($map->pathOf('requires'), 'must be spouse or guardian'),
            };
        }
        foreach ($policies as $policy) {
            $kind = $policy->subscriber->kind;
            if ($kind !== SubscriberKind::Self && !in_array($kind, $needs, true)) {
                $needs[] = $kind;
            }
        }
        $weight = $map->optionalInt('weight') ?? 1;
        if ($weight < 1) {
            throw SeedFileException::at($map->pathOf('weight'), 'must be at least 1');
        }
        return new self($weight, $selfPay, $policies, $needs);
    }
}
