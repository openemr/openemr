<?php

/**
 * A case patient's insurance, written out: self pay, or policies by type
 * plus earlier ended ones.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class CaseInsurance
{
    /**
     * @param list<CasePolicy> $policies primary first
     * @param list<CaseEndedPolicy> $ended
     */
    public function __construct(
        public bool $selfPay,
        public array $policies,
        public array $ended,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['self_pay', 'primary', 'secondary', 'tertiary', 'ended']);
        $selfPay = $map->optionalBool('self_pay');
        $policies = [];
        foreach (PolicyType::cases() as $type) {
            $policyMap = $map->optionalMap($type->value);
            if ($policyMap !== null) {
                $policies[] = CasePolicy::parse($policyMap, $type);
            }
        }
        if ($selfPay === ($policies !== [])) {
            throw SeedFileException::at($map->path, 'give self_pay: true or a primary policy, not both');
        }
        if ($policies !== [] && $policies[0]->type !== PolicyType::Primary) {
            throw SeedFileException::at($map->path, 'a secondary or tertiary policy needs a primary');
        }
        return new self(
            $selfPay,
            $policies,
            array_map(CaseEndedPolicy::parse(...), $map->optionalMapList('ended')),
        );
    }
}
