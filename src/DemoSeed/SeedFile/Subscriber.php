<?php

/**
 * Who holds a policy. Rules name the patient (self), the spouse or the
 * guardian the related-people rules made; cases name the patient (self),
 * another case patient or one of the case's related people by key.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class Subscriber
{
    private function __construct(
        public SubscriberKind $kind,
        public ?string $key = null,
    ) {
    }

    public static function self(): self
    {
        return new self(SubscriberKind::Self);
    }

    public static function parseRule(MapReader $map, string $key): self
    {
        if (!$map->has($key)) {
            return self::self();
        }
        return match ($map->string($key)) {
            'self' => self::self(),
            'spouse' => new self(SubscriberKind::Spouse),
            'guardian' => new self(SubscriberKind::Guardian),
            default => throw SeedFileException::at($map->pathOf($key), 'must be self, spouse or guardian'),
        };
    }

    public static function parseCase(MapReader $map, string $key): self
    {
        if (!$map->has($key)) {
            return self::self();
        }
        if (!is_array($map->raw($key))) {
            return $map->string($key) === 'self'
                ? self::self()
                : throw SeedFileException::at($map->pathOf($key), 'must be self, {patient: key} or {related: key}');
        }
        $subscriber = $map->map($key);
        $subscriber->rejectUnknownKeys(['patient', 'related']);
        return match (true) {
            $subscriber->has('patient') && !$subscriber->has('related') => new self(SubscriberKind::Patient, $subscriber->string('patient')),
            $subscriber->has('related') && !$subscriber->has('patient') => new self(SubscriberKind::Related, $subscriber->string('related')),
            default => throw SeedFileException::at($map->pathOf($key), 'give one of patient or related'),
        };
    }
}
