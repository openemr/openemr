<?php

declare(strict_types=1);

namespace Brick\VarExporter\Internal\ObjectExporter;

use Brick\VarExporter\ExportException;
use Brick\VarExporter\Internal\ObjectExporter;

/**
 * Handles instances of classes with a __set_state() method.
 *
 * @internal This class is for internal use, and not part of the public API. It may change at any time without warning.
 */
class SetStateExporter extends ObjectExporter
{
    /**
     * {@inheritDoc}
     */
    public function supports(\ReflectionObject $reflectionObject) : bool
    {
        if ($reflectionObject->hasMethod('__set_state')) {
            $method = $reflectionObject->getMethod('__set_state');

            return $method->isPublic() && $method->isStatic();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function export($object, \ReflectionObject $reflectionObject, array $path, array $parentIds) : array
    {
        $className = $reflectionObject->getName();

        $vars = $this->getObjectVars($object, $path);

        $exportedVars = $this->exporter->exportArray($vars, $path, $parentIds);
        $exportedVars = $this->exporter->wrap($exportedVars, '\\' . $className . '::__set_state(',  ')');

        return $exportedVars;
    }

    /**
     * Returns public and private object properties, as an associative array.
     *
     * This is unlike get_object_vars(), which only returns properties accessible from the current scope.
     *
     * The returned values are in line with those returned by var_export() in the array passed to __set_state(); unlike
     * var_export() however, this method throws an exception if the object has overridden private properties, as this
     * would result in a conflict in array keys. In this case, var_export() would return multiple values in the output,
     * which once executed would yield an array containing only the last value for this key in the output.
     *
     * This way we offer a better safety guarantee, while staying compatible with var_export() in the output.
     *
     * @psalm-suppress MixedAssignment
     *
     * @param object   $object The object to dump.
     * @param string[] $path   The path to the object, in the array/object graph.
     *
     * @return array<string, mixed> An associative array of property name to value.
     *
     * @throws ExportException
     */
    private function getObjectVars(object $object, array $path) : array
    {
        $result = [];

        foreach ((array) $object as $name => $value) {
            $name = (string) $name;
            $pos = strrpos($name, "\0");

            if ($pos !== false) {
                $name = substr($name, $pos + 1);
            }

            assert($name !== false);

            if (array_key_exists($name, $result)) {
                $className = get_class($object);

                throw new ExportException(
                    'Class "' . $className . '" has overridden private property "' . $name . '". ' .
                    'This is not supported for exporting objects with __set_state().',
                    $path
                );
            }

            if ($this->exporter->skipDynamicProperties && $this->isDynamicProperty($object, $name)) {
                continue;
            }

            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * @param object $object
     * @param string $name
     *
     * @return bool
     */
    private function isDynamicProperty(object $object, string $name) : bool
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionObject = new \ReflectionObject($object);

        return $reflectionObject->hasProperty($name) && ! $reflectionClass->hasProperty($name);
    }
}
