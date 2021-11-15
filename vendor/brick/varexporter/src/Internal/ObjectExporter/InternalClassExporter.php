<?php

declare(strict_types=1);

namespace Brick\VarExporter\Internal\ObjectExporter;

use Brick\VarExporter\ExportException;
use Brick\VarExporter\Internal\ObjectExporter;

/**
 * Throws on internal classes.
 *
 * @internal This class is for internal use, and not part of the public API. It may change at any time without warning.
 */
class InternalClassExporter extends ObjectExporter
{
    /**
     * {@inheritDoc}
     */
    public function supports(\ReflectionObject $reflectionObject) : bool
    {
        return $reflectionObject->isInternal();
    }

    /**
     * {@inheritDoc}
     */
    public function export($object, \ReflectionObject $reflectionObject, array $path, array $parentIds) : array
    {
        $className = $reflectionObject->getName();

        throw new ExportException('Class "' . $className . '" is internal, and cannot be exported.', $path);
    }
}
