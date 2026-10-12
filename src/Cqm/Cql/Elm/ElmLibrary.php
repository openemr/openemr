<?php

/**
 * One compiled CQL library (ELM JSON), indexed by definition name the way
 * cql-execution's Library is. Expression trees stay as raw ELM arrays here;
 * the interpreter builds evaluable nodes from them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Elm;

final readonly class ElmLibrary
{
    /**
     * @param array<string, array{path: string, version: ?string}> $includes local identifier => included library
     * @param array<string, array<mixed>> $parameters
     * @param array<string, array<mixed>> $codeSystems
     * @param array<string, array<mixed>> $valueSets
     * @param array<string, array<mixed>> $codes
     * @param array<string, array<mixed>> $concepts
     * @param array<string, array<mixed>> $expressions expression definitions by name
     * @param array<string, list<array<mixed>>> $functions function definitions by name, one per overload
     */
    public function __construct(
        public string $name,
        public ?string $version,
        public ?string $system,
        public array $includes,
        public array $parameters,
        public array $codeSystems,
        public array $valueSets,
        public array $codes,
        public array $concepts,
        public array $expressions,
        public array $functions,
    ) {
    }

    /**
     * @param array<mixed> $elm a library's ELM JSON, decoded
     */
    public static function fromElm(array $elm): self
    {
        $library = $elm['library'] ?? null;
        if (!is_array($library)) {
            throw new \UnexpectedValueException('ELM has no library');
        }
        $identifier = is_array($library['identifier'] ?? null) ? $library['identifier'] : [];
        $name = $identifier['id'] ?? null;
        if (!is_string($name)) {
            throw new \UnexpectedValueException('ELM library has no identifier');
        }

        $includes = [];
        foreach (self::defs($library, 'includes') as $include) {
            $alias = $include['localIdentifier'] ?? null;
            $path = $include['path'] ?? null;
            if (!is_string($alias) || !is_string($path)) {
                throw new \UnexpectedValueException("$name has a malformed include");
            }
            $includes[$alias] = ['path' => $path, 'version' => self::optionalString($include['version'] ?? null)];
        }

        $expressions = [];
        $functions = [];
        foreach (self::defs($library, 'statements') as $statement) {
            $statementName = self::name($statement, $name);
            if (($statement['type'] ?? null) === 'FunctionDef') {
                $functions[$statementName][] = $statement;
            } else {
                $expressions[$statementName] = $statement;
            }
        }

        return new self(
            $name,
            self::optionalString($identifier['version'] ?? null),
            self::optionalString($identifier['system'] ?? null),
            $includes,
            self::byName($library, 'parameters', $name),
            self::byName($library, 'codeSystems', $name),
            self::byName($library, 'valueSets', $name),
            self::byName($library, 'codes', $name),
            self::byName($library, 'concepts', $name),
            $expressions,
            $functions,
        );
    }

    /**
     * @param array<mixed> $library
     * @return list<array<mixed>>
     */
    private static function defs(array $library, string $section): array
    {
        $block = $library[$section] ?? null;
        $defs = is_array($block) ? ($block['def'] ?? []) : [];
        if (!is_array($defs)) {
            throw new \UnexpectedValueException("ELM $section is malformed");
        }
        $result = [];
        foreach ($defs as $def) {
            if (!is_array($def)) {
                throw new \UnexpectedValueException("ELM $section has a malformed definition");
            }
            $result[] = $def;
        }
        return $result;
    }

    /**
     * @param array<mixed> $library
     * @return array<string, array<mixed>>
     */
    private static function byName(array $library, string $section, string $libraryName): array
    {
        $result = [];
        foreach (self::defs($library, $section) as $def) {
            $result[self::name($def, $libraryName)] = $def;
        }
        return $result;
    }

    /**
     * @param array<mixed> $def
     */
    private static function name(array $def, string $libraryName): string
    {
        $name = $def['name'] ?? null;
        if (!is_string($name)) {
            throw new \UnexpectedValueException("$libraryName has a definition without a name");
        }
        return $name;
    }

    private static function optionalString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
