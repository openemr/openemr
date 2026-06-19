<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Entities;

use Doctrine\ORM\Mapping;

/**
 * Partial mapping - additional columns exist in the database.
 */
#[Mapping\Entity]
#[Mapping\Table(name: 'list_options')]
class ListOption
{
    #[Mapping\Id]
    #[Mapping\Column(length: 100)]
    public readonly string $listId; // @phpstan-ignore property.uninitializedReadonly (Doctrine hydrates via reflection)

    #[Mapping\Id]
    #[Mapping\Column(length: 100)]
    public readonly string $optionId; // @phpstan-ignore property.uninitializedReadonly

    #[Mapping\Column(length: 255)]
    public string $codes;
}
