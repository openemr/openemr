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
 * Important note: this table has a composite PK (see #12541) and must use the
 * rarely-used array-as-id syntax for `find()` operations, e.g.
 * `$em->find(ListOption::class, ['listId' => 'asdf', 'optionId' => 'sdfg'])`
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
