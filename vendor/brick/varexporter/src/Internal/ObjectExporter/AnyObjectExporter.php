<?php

declare(strict_types=1);

namespace Brick\VarExporter\Internal\ObjectExporter;

use Brick\VarExporter\Internal\ObjectExporter;

/**
 * Handles any class through direct property access and bound closures.
 *
 * @todo On PHP 7.4, we could remove unset() calls from the output for typed properties having no default value.
 *       This doesn't hurt in the meantime.
 *
 * @internal This class is for internal use, and not part of the public API. It may change at any time without warning.
 */
class AnyObjectExporter extends ObjectExporter
{
    /**
     * {@inheritDoc}
     */
    public function supports(\ReflectionObject $reflectionObject) : bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedAssignment
     */
    public function export($object, \ReflectionObject $reflectionObject, array $path, array $parentIds) : array
    {
        $lines = $this->getCreateObjectCode($reflectionObject);

        $objectAsArray = (array) $object;

        $current = $this->exporter->skipDynamicProperties
            ? new \ReflectionClass($object) // properties from class definition only
            : $reflectionObject;            // properties from class definition + dynamic properties

        $isParentClass = false;

        $returnNewObject = ($reflectionObject->getConstructor() === null);

        while ($current) {
            $publicProperties = [];
            $nonPublicProperties = [];
            $unsetPublicProperties = [];
            $unsetNonPublicProperties = [];

            foreach ($current->getProperties() as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                if ($isParentClass && ! $property->isPrivate()) {
                    // property already handled in the child class.
                    continue;
                }

                $name = $property->getName();

                // getting the property value through the object to array cast, and not through reflection, as this is
                // currently the only way to know whether a declared property has been unset - at least before PHP 7.4,
                // which will bring ReflectionProperty::isInitialized().

                $key = $this->getPropertyKey($property);

                if (array_key_exists($key, $objectAsArray)) {
                    $value = $objectAsArray[$key];

                    if ($property->isPublic()) {
                        $publicProperties[$name] = $value;
                    } else {
                        $nonPublicProperties[$name] = $value;
                    }
                } else {
                    if ($property->isPublic()) {
                        $unsetPublicProperties[] = $name;
                    } else {
                        $unsetNonPublicProperties[] = $name;
                    }
                }

                $returnNewObject = false;
            }

            if ($publicProperties || $unsetPublicProperties) {
                $lines[] = '';

                foreach ($publicProperties as $name => $value) {
                    /** @psalm-suppress RedundantCast See: https://github.com/vimeo/psalm/issues/4891 */
                    $name = (string) $name;

                    $newPath = $path;
                    $newPath[] = $name;

                    $newParentIds = $parentIds;
                    $newParentIds[] = spl_object_id($object);

                    $exportedValue = $this->exporter->export($value, $newPath, $newParentIds);
                    $exportedValue = $this->exporter->wrap($exportedValue, '$object->' . $this->escapePropName($name) . ' = ', ';');
                    $lines = array_merge($lines, $exportedValue);
                }

                foreach ($unsetPublicProperties as $name) {
                    $lines[] = 'unset($object->' . $this->escapePropName($name) . ');';
                }
            }

            if ($nonPublicProperties || $unsetNonPublicProperties) {
                $closureLines = [];

                if ($this->exporter->addTypeHints) {
                    $closureLines[] = '/** @var \\' . $current->getName() . ' $this */';
                }

                foreach ($nonPublicProperties as $name => $value) {
                    $newPath = $path;
                    $newPath[] = $name;

                    $newParentIds = $parentIds;
                    $newParentIds[] = spl_object_id($object);

                    $exportedValue = $this->exporter->export($value, $newPath, $newParentIds);
                    $exportedValue = $this->exporter->wrap($exportedValue, '$this->' . $this->escapePropName($name) . ' = ', ';');
                    $closureLines = array_merge($closureLines, $exportedValue);
                }

                foreach ($unsetNonPublicProperties as $name) {
                    $closureLines[] = 'unset($this->' . $this->escapePropName($name) . ');';
                }

                $lines[] = '';
                $lines[] = '(function() {';
                $lines = array_merge($lines, $this->exporter->indent($closureLines));
                $lines[] = '})->bindTo($object, \\' . $current->getName() . '::class)();';
            }

            $current = $current->getParentClass();
            $isParentClass = true;
        }

        if ($returnNewObject) {
            // no constructor, no properties
            return ['new \\' . $reflectionObject->getName()];
        }

        $lines[] = '';
        $lines[] = 'return $object;';

        return $this->wrapInClosure($lines);
    }

    /**
     * Returns the key of the given property in the object-to-array cast.
     *
     * @param \ReflectionProperty $property
     *
     * @return string
     */
    private function getPropertyKey(\ReflectionProperty $property) : string
    {
        $name = $property->getName();

        if ($property->isPrivate()) {
            return "\0" . $property->getDeclaringClass()->getName() . "\0" . $name;
        }

        if ($property->isProtected()) {
            return "\0*\0" . $name;
        }

        return $name;
    }

    /**
     * @param string $var
     *
     * @return string
     */
    private function escapePropName(string $var) : string
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $var) === 1) {
            return $var;
        }

        return '{' . var_export($var, true) . '}';
    }
}
