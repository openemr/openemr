<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Entities\EventSubscriber;

use DateTimeImmutable;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use OpenEMR\Entities\Attributes\CreatedAt;
use OpenEMR\Entities\Attributes\UpdatedAt;
use ReflectionObject;

/**
 * Doctrine event subscriber that automatically sets timestamp properties
 * on entities.
 *
 * Properties marked with #[CreatedAt] are set during prePersist.
 * Properties marked with #[UpdatedAt] are set during prePersist and preUpdate.
 *
 * Register with the EntityManager's event manager:
 *
 *     $em->getEventManager()->addEventListener(
 *         [Events::prePersist, Events::preUpdate],
 *         new TimestampSubscriber(),
 *     );
 *
 * @see CreatedAt
 * @see UpdatedAt
 */
class TimestampSubscriber
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $now = new DateTimeImmutable();

        $this->setPropertiesWithAttribute($entity, CreatedAt::class, $now);
        $this->setPropertiesWithAttribute($entity, UpdatedAt::class, $now);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $now = new DateTimeImmutable();

        $this->setPropertiesWithAttribute($entity, UpdatedAt::class, $now);
    }

    /**
     * @param class-string $attributeClass
     */
    private function setPropertiesWithAttribute(object $entity, string $attributeClass, DateTimeImmutable $value): void
    {
        $reflection = new ReflectionObject($entity);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes($attributeClass);
            if ($attributes !== []) {
                $property->setValue($entity, $value);
            }
        }
    }
}
