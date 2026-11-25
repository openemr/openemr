<?php

/**
 * One current policy of a case patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class CasePolicy
{
    /** Policy and group number lengths CoverageValidator accepts. */
    public const NUMBER_MIN_LENGTH = 2;
    public const NUMBER_MAX_LENGTH = 255;

    /**
     * @param ?string $policyNumber null = made up with the rules' format
     */
    public function __construct(
        public PolicyType $type,
        public string $companyKey,
        public ?string $policyNumber,
        public ?string $groupNumber,
        public Subscriber $subscriber,
        public ?int $copay,
    ) {
    }

    public static function parse(MapReader $map, PolicyType $type): self
    {
        $map->rejectUnknownKeys(['company', 'policy', 'group', 'subscriber', 'copay']);
        return new self(
            $type,
            $map->string('company'),
            self::number($map, 'policy'),
            self::number($map, 'group'),
            Subscriber::parseCase($map, 'subscriber'),
            Copay::optional($map, 'copay'),
        );
    }

    public static function number(MapReader $map, string $key): ?string
    {
        $number = $map->optionalString($key);
        if ($number !== null && (strlen($number) < self::NUMBER_MIN_LENGTH || strlen($number) > self::NUMBER_MAX_LENGTH)) {
            throw SeedFileException::at(
                $map->pathOf($key),
                sprintf('must be %d to %d characters', self::NUMBER_MIN_LENGTH, self::NUMBER_MAX_LENGTH),
            );
        }
        return $number;
    }
}
