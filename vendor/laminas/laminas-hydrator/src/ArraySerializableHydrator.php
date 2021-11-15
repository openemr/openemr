<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use function array_merge;
use function is_callable;
use function method_exists;
use function sprintf;

class ArraySerializableHydrator extends AbstractHydrator
{
    /**
     * Extract values from the provided object
     *
     * Extracts values via the object's getArrayCopy() method.
     *
     * {@inheritDoc}
     * @throws Exception\BadMethodCallException for an $object not implementing getArrayCopy()
     */
    public function extract(object $object) : array
    {
        if (! method_exists($object, 'getArrayCopy') || ! is_callable([$object, 'getArrayCopy'])) {
            throw new Exception\BadMethodCallException(
                sprintf('%s expects the provided object to implement getArrayCopy()', __METHOD__)
            );
        }

        $data   = $object->getArrayCopy();
        $filter = $this->getFilter();

        foreach ($data as $name => $value) {
            $name = (string) $name;

            if (! $filter->filter($name)) {
                unset($data[$name]);
                continue;
            }

            $extractedName = $this->extractName($name, $object);

            // replace the original key with extracted, if differ
            if ($extractedName !== $name) {
                unset($data[$name]);
                $name = $extractedName;
            }

            $data[$name] = $this->extractValue($name, $value, $object);
        }

        return $data;
    }

    /**
     * Hydrate an object
     *
     * Hydrates an object by passing $data to either its exchangeArray() or
     * populate() method.
     *
     * {@inheritDoc}
     * @throws Exception\BadMethodCallException for an $object not implementing exchangeArray() or populate()
     */
    public function hydrate(array $data, object $object)
    {
        $replacement = [];
        foreach ($data as $key => $value) {
            $name = $this->hydrateName($key, $data);
            $replacement[$name] = $this->hydrateValue($name, $value, $data);
        }

        if (method_exists($object, 'exchangeArray') && is_callable([$object, 'exchangeArray'])) {
            // Ensure any previously populated values not in the replacement
            // remain following population.
            if (method_exists($object, 'getArrayCopy') && is_callable([$object, 'getArrayCopy'])) {
                $original = $object->getArrayCopy();
                $replacement = array_merge($original, $replacement);
            }
            $object->exchangeArray($replacement);
            return $object;
        }

        if (method_exists($object, 'populate') && is_callable([$object, 'populate'])) {
            $object->populate($replacement);
            return $object;
        }

        throw new Exception\BadMethodCallException(
            sprintf('%s expects the provided object to implement exchangeArray() or populate()', __METHOD__)
        );
    }
}
