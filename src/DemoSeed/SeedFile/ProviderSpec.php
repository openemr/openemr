<?php

/**
 * A provider to create (`providers[]`): a user who can have appointments.
 * Created without a password, so it can't log in until one is set.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class ProviderSpec
{
    public function __construct(
        public string $key,
        public string $username,
        public ?string $title,
        public string $firstName,
        public string $lastName,
        public ?string $credentials,
        public ?string $specialty,
        public string $npi,
        public string $facilityKey,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys([
            'key', 'username', 'title', 'first_name', 'last_name', 'credentials', 'specialty', 'npi', 'facility',
        ]);
        $username = $map->string('username');
        if (preg_match('/^[a-z][a-z0-9_.-]{1,31}$/i', $username) !== 1) {
            throw SeedFileException::at($map->pathOf('username'), 'must be 2-32 letters, digits, ".", "_" or "-", starting with a letter');
        }
        return new self(
            key: $map->string('key'),
            username: $username,
            title: $map->optionalString('title'),
            firstName: $map->string('first_name'),
            lastName: $map->string('last_name'),
            credentials: $map->optionalString('credentials'),
            specialty: $map->optionalString('specialty'),
            npi: Npi::parse($map, 'npi'),
            facilityKey: $map->string('facility'),
        );
    }
}
