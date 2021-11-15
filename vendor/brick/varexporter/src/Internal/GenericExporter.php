<?php

declare(strict_types=1);

namespace Brick\VarExporter\Internal;

use Brick\VarExporter\ExportException;
use Brick\VarExporter\VarExporter;

/**
 * The main exporter implementation, that handles variables of any type.
 *
 * A GenericExporter is only intended to be used once per array/object graph (i.e. once per `VarExport::export()` call),
 * as it keeps an internal cache of visited objects; if it is ever going to be reused, just implement a reset method to
 * reset the visited objects.
 *
 * @internal This class is for internal use, and not part of the public API. It may change at any time without warning.
 */
final class GenericExporter
{
    /**
     * @var ObjectExporter[]
     */
    private $objectExporters = [];

    /**
     * The visited objects, to detect circular references.
     *
     * This is a two-level map of parent object id => child object id => path where the object first appeared.
     *
     * @var array<int, array<int, string[]>>
     */
    private $visitedObjects = [];

    /**
     * @psalm-readonly
     *
     * @var bool
     */
    public $addTypeHints;

    /**
     * @psalm-readonly
     *
     * @var bool
     */
    public $skipDynamicProperties;

    /**
     * @psalm-readonly
     *
     * @var bool
     */
    public $inlineNumericScalarArray;

    /**
     * @psalm-readonly
     *
     * @var bool
     */
    public $closureSnapshotUses;

    /**
     * @psalm-readonly
     *
     * @var bool
     */
    public $trailingCommaInArray;

    /**
     * @psalm-readonly
     *
     * @var int
     */
    public $indentLevel;

    /**
     * @param int $options
     * @param int Indentation level
     */
    public function __construct(int $options, int $indentLevel = 0)
    {
        $this->objectExporters[] = new ObjectExporter\StdClassExporter($this);

        if (! ($options & VarExporter::NO_CLOSURES)) {
            $this->objectExporters[] = new ObjectExporter\ClosureExporter($this);
        }

        if (! ($options & VarExporter::NO_SET_STATE)) {
            $this->objectExporters[] = new ObjectExporter\SetStateExporter($this);
        }

        $this->objectExporters[] = new ObjectExporter\InternalClassExporter($this);

        if (! ($options & VarExporter::NO_SERIALIZE)) {
            $this->objectExporters[] = new ObjectExporter\SerializeExporter($this);
        }

        if (! ($options & VarExporter::NOT_ANY_OBJECT)) {
            $this->objectExporters[] = new ObjectExporter\AnyObjectExporter($this);
        }

        $this->addTypeHints             = (bool) ($options & VarExporter::ADD_TYPE_HINTS);
        $this->skipDynamicProperties    = (bool) ($options & VarExporter::SKIP_DYNAMIC_PROPERTIES);
        $this->inlineNumericScalarArray = (bool) ($options & VarExporter::INLINE_NUMERIC_SCALAR_ARRAY);
        $this->closureSnapshotUses      = (bool) ($options & VarExporter::CLOSURE_SNAPSHOT_USES);
        $this->trailingCommaInArray     = (bool) ($options & VarExporter::TRAILING_COMMA_IN_ARRAY);

        $this->indentLevel = $indentLevel;
    }

    /**
     * @param mixed    $var       The variable to export.
     * @param string[] $path      The path to the current variable in the array/object graph.
     * @param int[]    $parentIds The ids of all objects higher in the graph.
     *
     * @return string[] The lines of code.
     *
     * @throws ExportException
     */
    public function export($var, array $path, array $parentIds) : array
    {
        switch ($type = gettype($var)) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                return [var_export($var, true)];

            case 'NULL':
                // lowercase null
                return ['null'];

            case 'array':
                /** @var array $var */
                return $this->exportArray($var, $path, $parentIds);

            case 'object':
                /** @var object $var */
                return $this->exportObject($var, $path, $parentIds);

            default:
                // resources
                throw new ExportException(sprintf('Type "%s" is not supported.', $type), $path);
        }
    }

    /**
     * @psalm-suppress MixedAssignment
     *
     * @param array    $array     The array to export.
     * @param string[] $path      The path to the current array in the array/object graph.
     * @param int[]    $parentIds The ids of all objects higher in the graph.
     *
     * @return string[] The lines of code.
     *
     * @throws ExportException
     */
    public function exportArray(array $array, array $path, array $parentIds) : array
    {
        if (! $array) {
            return ['[]'];
        }

        $result = [];

        $count = count($array);
        $isNumeric = array_keys($array) === range(0, $count - 1);

        $current = 0;

        $inline = ($this->inlineNumericScalarArray && $isNumeric && $this->isScalarArray($array));

        foreach ($array as $key => $value) {
            $isLast = (++$current === $count);

            $newPath = $path;
            $newPath[] = (string) $key;

            $exported = $this->export($value, $newPath, $parentIds);

            if ($inline) {
                $result[] = $exported[0];
            } else {
                $prepend = '';
                $append = '';

                if (! $isNumeric) {
                    $prepend = var_export($key, true) . ' => ';
                }

                if (! $isLast || $this->trailingCommaInArray) {
                    $append = ',';
                }

                $exported = $this->wrap($exported, $prepend, $append);
                $exported = $this->indent($exported);

                $result = array_merge($result, $exported);
            }
        }

        if ($inline) {
            return ['[' . implode(', ', $result) . ']'];
        }

        array_unshift($result, '[');
        $result[] = ']';

        return $result;
    }

    /**
     * Returns whether the given array only contains scalar values.
     *
     * Types considered scalar here are int, bool, float, string and null.
     * If the array is empty, this method returns true.
     *
     * @param array $array
     *
     * @return bool
     */
    private function isScalarArray(array $array) : bool
    {
        foreach ($array as $value) {
            if ($value !== null && ! is_scalar($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param object   $object    The object to export.
     * @param string[] $path      The path to the current object in the array/object graph.
     * @param int[]    $parentIds The ids of all objects higher in the graph.
     *
     * @return string[] The lines of code.
     *
     * @throws ExportException
     */
    public function exportObject(object $object, array $path, array $parentIds) : array
    {
        $id = spl_object_id($object);

        foreach ($parentIds as $parentId) {
            if (isset($this->visitedObjects[$parentId][$id])) {
                throw new ExportException(sprintf(
                    'Object of class "%s" has a circular reference at %s. ' .
                    'Circular references are currently not supported.',
                    get_class($object),
                    ExportException::pathToString($this->visitedObjects[$parentId][$id])
                ), $path);
            }

            $this->visitedObjects[$parentId][$id] = $path;
        }

        $reflectionObject = new \ReflectionObject($object);

        foreach ($this->objectExporters as $objectExporter) {
            if ($objectExporter->supports($reflectionObject)) {
                return $objectExporter->export($object, $reflectionObject, $path, $parentIds);
            }
        }

        // This may only happen when an option is given to disallow specific export methods.

        $className = $reflectionObject->getName();

        throw new ExportException('Class "' . $className . '" cannot be exported using the current options.', $path);
    }

    /**
     * Indents every non-empty line.
     *
     * @param string[] $lines The lines of code.
     *
     * @return string[] The indented lines of code.
     */
    public function indent(array $lines) : array
    {
        foreach ($lines as & $value) {
            if ($value !== '') {
                $value = '    ' . $value;
            }
        }

        return $lines;
    }

    /**
     * @param string[] $lines   The lines of code.
     * @param string   $prepend The string to prepend to the first line.
     * @param string   $append  The string to append to the last line.
     *
     * @return string[]
     */
    public function wrap(array $lines, string $prepend, string $append) : array
    {
        $lines[0] = $prepend . $lines[0];
        $lines[count($lines) - 1] .= $append;

        return $lines;
    }
}
