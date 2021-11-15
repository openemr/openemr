<?php

declare(strict_types=1);

namespace Brick\VarExporter;

use Brick\VarExporter\Internal\GenericExporter;

final class VarExporter
{
    /**
     * Prepends the output with `return ` and append a semicolon and a newline.
     * This makes the code ready to be executed in a PHP file―or `eval()`, for that matter.
     */
    public const ADD_RETURN = 1 << 0;

    /**
     * Adds type hints to objects created through reflection, and to `$this` inside closures bound to an object.
     * This allows the resulting code to be statically analyzed by external tools and IDEs.
     */
    public const ADD_TYPE_HINTS = 1 << 1;

    /**
     * Skips dynamic properties on custom classes in the output. By default, any dynamic property set on a custom class
     * is exported; if this flag is set, dynamic properties are only allowed on stdClass objects, and ignored on other
     * objects.
     */
    public const SKIP_DYNAMIC_PROPERTIES = 1 << 2;

    /**
     * Disallows exporting objects through `__set_state()`.
     */
    public const NO_SET_STATE = 1 << 3;

    /**
     * Disallows exporting objects through `__serialize()` and `__unserialize()`.
     */
    public const NO_SERIALIZE = 1 << 4;

    /**
     * Disallows exporting plain objects using direct property access.
     */
    public const NOT_ANY_OBJECT = 1 << 5;

    /**
     * Disallows exporting closures.
     */
    public const NO_CLOSURES = 1 << 6;

    /**
     * Formats numeric arrays containing only scalar values on a single line.
     * Types considered scalar here are int, bool, float, string and null.
     */
    public const INLINE_NUMERIC_SCALAR_ARRAY = 1 << 7;

    /**
     * Export static vars defined via `use` as variables.
     */
    public const CLOSURE_SNAPSHOT_USES = 1 << 8;

    /**
     * Add a trailing comma after the last item of non-inline arrays.
     */
    public const TRAILING_COMMA_IN_ARRAY = 1 << 9;

    /**
     * @param mixed $var       The variable to export.
     * @param int   $options   A bitmask of options. Possible values are `VarExporter::*` constants.
     *                         Combine multiple options with a bitwise OR `|` operator.
     * @param int $indentLevel The base output indentation level.
     *
     * @return string
     *
     * @throws ExportException
     */
    public static function export($var, int $options = 0, int $indentLevel = 0) : string
    {
        $exporter = new GenericExporter($options, $indentLevel);
        $lines = $exporter->export($var, [], []);

        if ($indentLevel < 1 || count($lines) < 2) {
            $export = implode(PHP_EOL, $lines);
        } else {
            $firstLine = array_shift($lines);
            $lines = array_map(function ($line) use ($indentLevel) {
                return str_repeat('    ', $indentLevel) . $line;
            }, $lines);

            $export = $firstLine . PHP_EOL . implode(PHP_EOL, $lines);
        }

        if ($options & self::ADD_RETURN) {
            return 'return ' . $export . ';' . PHP_EOL;
        }

        return $export;
    }
}
