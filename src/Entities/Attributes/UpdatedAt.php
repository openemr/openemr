<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Entities\Attributes;

use Attribute;

/**
 * Marks a DateTimeImmutable property to be automatically set on entity
 * creation and update.
 *
 * Usage:
 *     #[Mapping\Column(name: 'last_updated')]
 *     #[UpdatedAt]
 *     public DateTimeImmutable $updatedAt;
 *
 * The property MUST NOT be marked `readonly`; however, once OpenEMR's minimum
 * PHP version supports asymmetric visibility (8.4+), it should be marked
 * `public private(set)`.
 *
 * @see \OpenEMR\Entities\EventSubscriber\TimestampSubscriber
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class UpdatedAt
{
}
