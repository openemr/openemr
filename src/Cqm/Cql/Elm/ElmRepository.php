<?php

/**
 * The CQL libraries of one measure, with include resolution as in
 * cql-execution's Repository: an include path matches a library's id or
 * "system/id", and a version, when given, must match exactly.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Elm;

final readonly class ElmRepository
{
    /**
     * @param list<ElmLibrary> $libraries
     */
    public function __construct(
        public array $libraries,
        public ElmLibrary $main,
    ) {
    }

    /**
     * The libraries of a measure document (as oe-cqm-parsers ships it):
     * every entry of cql_libraries, with main_cql_library as the main one.
     *
     * @param array<mixed> $measure
     */
    public static function fromMeasure(array $measure): self
    {
        $entries = $measure['cql_libraries'] ?? null;
        $mainName = $measure['main_cql_library'] ?? null;
        if (!is_array($entries) || !is_string($mainName)) {
            throw new \UnexpectedValueException('The measure has no CQL libraries');
        }
        $libraries = [];
        $main = null;
        foreach ($entries as $entry) {
            $elm = is_array($entry) ? ($entry['elm'] ?? null) : null;
            if (!is_array($elm)) {
                throw new \UnexpectedValueException('A measure library has no ELM');
            }
            $library = ElmLibrary::fromElm($elm);
            $libraries[] = $library;
            if ($library->name === $mainName) {
                $main = $library;
            }
        }
        if ($main === null) {
            throw new \UnexpectedValueException("The main library $mainName is not among the measure's libraries");
        }
        return new self($libraries, $main);
    }

    public function resolve(string $path, ?string $version): ?ElmLibrary
    {
        foreach ($this->libraries as $library) {
            if ($path !== $library->name && $path !== $library->system . '/' . $library->name) {
                continue;
            }
            if ($version === null || $version === $library->version) {
                return $library;
            }
        }
        return null;
    }

    /** The library an include alias of a library refers to. */
    public function included(ElmLibrary $from, string $alias): ?ElmLibrary
    {
        $include = $from->includes[$alias] ?? null;
        return $include === null ? null : $this->resolve($include['path'], $include['version']);
    }
}
