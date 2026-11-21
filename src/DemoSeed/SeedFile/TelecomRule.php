<?php

/**
 * One `rules.telecom[]` entry: a phone, email or other contact method a
 * patient may get.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class TelecomRule
{
    /**
     * @param bool $requiresMobile only for patients who got a mobile phone
     *        earlier in the rules; uses that same number
     */
    public function __construct(
        public string $system,
        public string $use,
        public int $percent,
        public PatientCondition $when,
        public bool $requiresMobile,
        public ?Inactivation $inactive,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['system', 'use', 'percent', 'when', 'requires', 'inactive']);
        $requires = $map->optionalString('requires');
        if ($requires !== null && $requires !== 'mobile') {
            throw SeedFileException::at($map->pathOf('requires'), 'the only supported value is mobile');
        }
        $inactive = $map->optionalMap('inactive');
        return new self(
            $lists->add(ListReference::TELECOM_SYSTEM, $map->string('system'), $map->pathOf('system')),
            $lists->add(ListReference::TELECOM_USE, $map->string('use'), $map->pathOf('use')),
            $map->percent('percent'),
            PatientCondition::parse($map->optionalMap('when')),
            $requires === 'mobile',
            $inactive === null ? null : Inactivation::parse($inactive),
        );
    }
}
