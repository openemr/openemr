<?php

/**
 * Loads the CQL libraries of every installed 2025 measure and checks that
 * the repository resolves them completely: every include, and every
 * reference to an expression, function, value set, code system, code,
 * concept or parameter. It also pins the ELM node types the measures use,
 * which is the set the interpreter must implement; a measure update that
 * brings a new one fails here first.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Cql;

use OpenEMR\Cqm\Cql\Elm\ElmLibrary;
use OpenEMR\Cqm\Cql\Elm\ElmRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ElmRepositoryTest extends TestCase
{
    /** The ELM node types the 2025 measures use. */
    private const NODE_TYPES = [
        'Add', 'After', 'AliasRef', 'And', 'AnyInValueSet', 'As', 'Before', 'ByColumn', 'ByDirection',
        'ByExpression', 'CalculateAgeAt', 'Case', 'ChoiceTypeSpecifier', 'Coalesce', 'CodeRef', 'Collapse',
        'Contains', 'Count', 'Date', 'DateFrom', 'DateTime', 'DateTimeComponentFrom', 'DifferenceBetween',
        'Distinct', 'Divide', 'DurationBetween', 'End', 'Equal', 'Equivalent', 'Except', 'Exists', 'Expand',
        'ExpressionRef', 'First', 'Flatten', 'FunctionRef', 'Greater', 'GreaterOrEqual', 'IdentifierRef', 'If',
        'In', 'InValueSet', 'IncludedIn', 'Includes', 'Indexer', 'Instance', 'Intersect', 'Interval',
        'IntervalTypeSpecifier', 'Is', 'IsFalse', 'IsNull', 'Last', 'Less', 'LessOrEqual', 'List',
        'ListTypeSpecifier', 'Literal', 'Max', 'MaxValue', 'Min', 'MinValue', 'Multiply', 'NamedTypeSpecifier',
        'Negate', 'Not', 'Null', 'OperandRef', 'Or', 'Overlaps', 'OverlapsAfter', 'OverlapsBefore',
        'ParameterRef', 'Power', 'Property', 'Quantity', 'Query', 'QueryLetRef', 'Retrieve', 'SameAs',
        'SameOrAfter', 'SameOrBefore', 'SingletonFrom', 'Start', 'Subtract', 'Sum', 'ToDate', 'ToDateTime',
        'ToDecimal', 'ToList', 'ToQuantity', 'TruncatedDivide', 'Tuple', 'Union', 'ValueSetRef', 'With',
        'Without',
    ];

    /** Reference node type => the library section its name must exist in. */
    private const REFERENCES = [
        'ExpressionRef' => 'expressions',
        'FunctionRef' => 'functions',
        'ValueSetRef' => 'valueSets',
        'CodeSystemRef' => 'codeSystems',
        'CodeRef' => 'codes',
        'ConceptRef' => 'concepts',
        'ParameterRef' => 'parameters',
    ];

    #[DataProvider('measureProvider')]
    public function testEveryLibraryAndReferenceResolves(string $measureName): void
    {
        $repository = ElmRepository::fromMeasure(self::measure($measureName));
        $this->assertSame($repository->main, $repository->resolve($repository->main->name, $repository->main->version));

        $unresolved = [];
        foreach ($repository->libraries as $library) {
            foreach (array_keys($library->includes) as $alias) {
                if ($repository->included($library, $alias) === null) {
                    $unresolved[] = "$library->name includes $alias";
                }
            }
            foreach (self::definitions($library) as $definition) {
                self::walk($definition, static function (string $type, array $node) use ($repository, $library, &$unresolved): void {
                    $section = self::REFERENCES[$type] ?? null;
                    $name = $node['name'] ?? null;
                    if ($section === null || !is_string($name)) {
                        return;
                    }
                    $alias = $node['libraryName'] ?? null;
                    $target = is_string($alias) ? $repository->included($library, $alias) : $library;
                    if ($target === null || !array_key_exists($name, $target->{$section})) {
                        $unresolved[] = "$library->name: $type " . (is_string($alias) ? "$alias." : '') . $name;
                    }
                });
            }
        }
        $this->assertSame([], array_values(array_unique($unresolved)));
    }

    public function testMeasuresUseOnlyTheKnownNodeTypes(): void
    {
        $types = [];
        foreach (array_keys(self::measureProvider()) as $measureName) {
            foreach (ElmRepository::fromMeasure(self::measure($measureName))->libraries as $library) {
                foreach (self::definitions($library) as $definition) {
                    self::walk($definition, static function (string $type) use (&$types): void {
                        $types[$type] = true;
                    });
                }
            }
        }
        $used = array_keys($types);
        sort($used);
        $this->assertSame(self::NODE_TYPES, $used, 'The installed measures use a different set of ELM node types; the CQL interpreter must cover any new one.');
    }

    public function testIncludeResolutionFollowsVersionAndSystem(): void
    {
        $library = static fn (string $name, ?string $version): array => [
            'library' => ['identifier' => ['id' => $name, 'system' => 'https://example.org', 'version' => $version]],
        ];
        $repository = ElmRepository::fromMeasure([
            'main_cql_library' => 'Main',
            'cql_libraries' => [['elm' => $library('Main', '1.0.0')], ['elm' => $library('Shared', '2.0.000')]],
        ]);
        $this->assertSame('Shared', $repository->resolve('Shared', '2.0.000')?->name);
        $this->assertSame('Shared', $repository->resolve('https://example.org/Shared', null)?->name);
        $this->assertNull($repository->resolve('Shared', '1.0.000'));
        $this->assertNull($repository->resolve('Missing', null));
    }

    public function testOverloadedFunctionsAreKeptTogether(): void
    {
        $library = ElmLibrary::fromElm(['library' => [
            'identifier' => ['id' => 'Lib'],
            'statements' => ['def' => [
                ['name' => 'F', 'type' => 'FunctionDef', 'operand' => [['name' => 'a']]],
                ['name' => 'F', 'type' => 'FunctionDef', 'operand' => [['name' => 'a'], ['name' => 'b']]],
                ['name' => 'Expr', 'type' => 'ExpressionDef'],
            ]],
        ]]);
        $this->assertCount(2, $library->functions['F']);
        $this->assertArrayHasKey('Expr', $library->expressions);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function measureProvider(): array
    {
        $cases = [];
        foreach (glob(self::measuresDir() . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $cases[basename($dir)] = [basename($dir)];
        }
        return $cases;
    }

    /**
     * Every ELM tree of a library: expression bodies, function operands and
     * parameter defaults.
     *
     * @return list<mixed>
     */
    private static function definitions(ElmLibrary $library): array
    {
        $trees = [];
        foreach ($library->expressions as $definition) {
            $trees[] = $definition['expression'] ?? null;
        }
        foreach ($library->functions as $overloads) {
            foreach ($overloads as $definition) {
                $trees[] = $definition['expression'] ?? null;
                $trees[] = $definition['operand'] ?? null;
            }
        }
        foreach ($library->parameters as $parameter) {
            $trees[] = $parameter['default'] ?? null;
        }
        return $trees;
    }

    /**
     * Calls $visit for every typed node, skipping annotations (narrative,
     * not logic).
     *
     * @param \Closure(string, array<mixed>): void $visit called with the node type and the node
     */
    private static function walk(mixed $node, \Closure $visit): void
    {
        if (!is_array($node)) {
            return;
        }
        $type = $node['type'] ?? null;
        if (is_string($type)) {
            $visit($type, $node);
        }
        foreach ($node as $key => $child) {
            if ($key !== 'annotation') {
                self::walk($child, $visit);
            }
        }
    }

    /**
     * @return array<mixed>
     */
    private static function measure(string $name): array
    {
        $json = file_get_contents(self::measuresDir() . "/$name/$name.json");
        $measure = $json === false ? null : json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($measure)) {
            throw new \RuntimeException("Measure $name is not installed");
        }
        return $measure;
    }

    private static function measuresDir(): string
    {
        return dirname(__DIR__, 5) . '/vendor/openemr/oe-cqm-parsers/2025_reporting_period/json_measures';
    }
}
