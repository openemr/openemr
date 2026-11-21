<?php

/**
 * A value in the seed file that must be an option id of an OpenEMR list.
 * Collected while parsing and checked against list_options before the
 * loader writes anything.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class ListReference
{
    public const RELATIONSHIP = 'related_person_relationship';
    public const ROLE = 'related_person_role';
    public const TELECOM_SYSTEM = 'telecom_systems';
    public const TELECOM_USE = 'telecom_uses';
    public const ADDRESS_TYPE = 'address-types';
    public const ADDRESS_USE = 'address-uses';
    public const STATE = 'state';
    public const MARITAL = 'marital';

    public function __construct(
        public string $listId,
        public string $value,
        public string $path,
    ) {
    }
}
