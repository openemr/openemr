<?php

/**
 * The `rules` section: what every patient not claimed by a case gets.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class Rules
{
    /**
     * @param list<string> $careTeamProviders provider keys, assigned in turn
     * @param list<RelatedPersonRule> $relatedPeople
     * @param list<TelecomRule> $telecom
     * @param list<AddressRule> $addresses
     */
    public function __construct(
        public array $careTeamProviders,
        public array $relatedPeople,
        public int $relatedPersonMobilePercent,
        public int $relatedPersonEmailPercent,
        public array $telecom,
        public array $addresses,
    ) {
    }

    public static function none(): self
    {
        return new self([], [], 0, 0, [], []);
    }

    /**
     * `insurance` is accepted but not read yet (step 5).
     */
    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['care_team', 'related_people', 'telecom', 'addresses', 'insurance']);

        $careTeam = $map->optionalMap('care_team');
        $careTeam?->rejectUnknownKeys(['providers']);

        $relatedPeople = $map->optionalMap('related_people');
        $relatedPeople?->rejectUnknownKeys(['people', 'contact']);
        $contact = $relatedPeople?->optionalMap('contact');
        $contact?->rejectUnknownKeys(['mobile', 'email']);

        return new self(
            $careTeam?->stringOrList('providers') ?? [],
            array_map(
                static fn (MapReader $rule): RelatedPersonRule => RelatedPersonRule::parse($rule, $lists),
                $relatedPeople?->optionalMapList('people') ?? [],
            ),
            $contact?->has('mobile') === true ? $contact->percent('mobile') : 0,
            $contact?->has('email') === true ? $contact->percent('email') : 0,
            array_map(
                static fn (MapReader $rule): TelecomRule => TelecomRule::parse($rule, $lists),
                $map->optionalMapList('telecom'),
            ),
            array_map(
                static fn (MapReader $rule): AddressRule => AddressRule::parse($rule, $lists),
                $map->optionalMapList('addresses'),
            ),
        );
    }
}
