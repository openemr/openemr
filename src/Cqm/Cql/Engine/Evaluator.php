<?php

/**
 * Evaluates ELM expressions, ported from cql-execution 3.3.2's elm
 * classes. Each node is evaluated directly from its JSON form.
 *
 * Results match cql-execution's, including where it follows JavaScript
 * rather than the CQL specification: truthiness for conditions, records
 * whose missing attributes are undefined, and the operators' own handling
 * of nulls. Node types the installed measures do not use are not
 * implemented and throw.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Engine;

use OpenEMR\Cqm\Cql\Qdm\QdmObject;
use OpenEMR\Cqm\Cql\Types\Code;
use OpenEMR\Cqm\Cql\Types\CodeSystem;
use OpenEMR\Cqm\Cql\Types\Comparison;
use OpenEMR\Cqm\Cql\Types\Concept;
use OpenEMR\Cqm\Cql\Types\CqlDate;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\CqlTemporal;
use OpenEMR\Cqm\Cql\Types\CqlValueSet;
use OpenEMR\Cqm\Cql\Types\Interval;
use OpenEMR\Cqm\Cql\Types\Precision;
use OpenEMR\Cqm\Cql\Types\Quantity;
use OpenEMR\Cqm\Cql\Types\Ratio;
use OpenEMR\Cqm\Cql\Types\ThreeValuedLogic;
use OpenEMR\Cqm\Cql\Types\Tuple;
use OpenEMR\Cqm\Cql\Types\Uncertainty;
use OpenEMR\Cqm\Cql\Types\ValueSet;
use OpenEMR\Cqm\Cql\Util\CqlMath;
use OpenEMR\Cqm\Cql\Util\CqlOverflowException;
use OpenEMR\Cqm\Cql\Util\Fdlibm;
use OpenEMR\Cqm\Cql\Util\JavaScript;

final class Evaluator
{
    private const SYSTEM = '{urn:hl7-org:elm-types:r1}';

    /**
     * Evaluates a statement of a library: once per library context, with
     * its result kept by name and by localId.
     */
    public static function statement(LibraryContext $library, string $name): mixed
    {
        [$done, $value] = $library->result($name);
        if ($done) {
            return $value;
        }
        $def = $library->library->expressions[$name]
            ?? throw new \UnexpectedValueException("No statement $name in {$library->library->name}");
        $expression = self::node($def['expression'] ?? null);
        $value = $expression === null ? null : self::evaluate($expression, $library->scope());
        $library->setResult($name, $value);
        if (is_string($def['localId'] ?? null)) {
            $library->recordLocalId($def['localId'], $value);
        }
        return $value;
    }

    /**
     * @param array<mixed> $node
     */
    public static function evaluate(array $node, Scope $scope): mixed
    {
        $type = $node['type'] ?? null;
        return match ($type) {
            // Values
            'Null' => null,
            'Literal' => self::literal($node),
            'List' => self::evaluateAll(self::nodes($node['element'] ?? []), $scope),
            'Tuple' => self::tuple($node, $scope),
            'Instance' => self::instance($node, $scope),
            'Quantity' => new Quantity(JavaScript::parseFloat(self::text($node['value'] ?? '')), self::stringOrNull($node['unit'] ?? null)),
            'Ratio' => self::ratio($node),
            'Interval' => self::interval($node, $scope),
            'DateTime' => self::dateTime($node, $scope),
            'Date' => self::date($node, $scope),
            'Code' => self::codeLiteral($node, $scope),
            'Concept' => self::conceptLiteral($node, $scope),
            // References
            'ExpressionRef' => self::expressionRef($node, $scope),
            'FunctionRef' => self::functionRef($node, $scope),
            'ParameterRef' => self::parameterRef($node, $scope),
            'OperandRef', 'AliasRef', 'QueryLetRef' => self::lookup($scope, self::name($node)),
            'IdentifierRef' => self::identifierRef($node, $scope),
            'Property' => self::property($node, $scope),
            'ValueSetRef' => self::valueSetRef($node, $scope),
            'CodeSystemRef' => self::codeSystemRef($node, $scope),
            'CodeRef' => self::codeRef($node, $scope),
            'ConceptRef' => self::conceptRef($node, $scope),
            // Clinical
            'Retrieve' => self::retrieve($node, $scope),
            'InValueSet' => self::inValueSet($node, $scope),
            'AnyInValueSet' => self::anyInValueSet($node, $scope),
            'CalculateAgeAt' => self::calculateAgeAt($node, $scope),
            'CalculateAge' => self::calculateAge($node, $scope),
            // Queries
            'Query' => self::query($node, $scope),
            // Logic and nulls
            'And' => ThreeValuedLogic::and(...self::booleans(self::args($node, $scope))),
            'Or' => ThreeValuedLogic::or(...self::booleans(self::args($node, $scope))),
            'Xor' => ThreeValuedLogic::xor(...self::booleans(self::args($node, $scope))),
            'Not' => ThreeValuedLogic::not(self::boolean(self::arg($node, $scope))),
            'Implies' => ThreeValuedLogic::implies(...self::booleans(self::args($node, $scope))),
            'IsTrue' => self::arg($node, $scope) === true,
            'IsFalse' => self::arg($node, $scope) === false,
            'IsNull' => self::arg($node, $scope) === null,
            'Coalesce' => self::coalesce($node, $scope),
            'If' => self::truthy(self::evaluate(self::requireNode($node['condition'] ?? null), $scope))
                ? self::evaluate(self::requireNode($node['then'] ?? null), $scope)
                : self::evaluate(self::requireNode($node['else'] ?? null), $scope),
            'Case' => self::caseExpression($node, $scope),
            // Comparison
            'Equal' => self::equal($node, $scope),
            'NotEqual' => self::notEqual($node, $scope),
            'Equivalent' => self::equivalent($node, $scope),
            'Less', 'LessOrEqual', 'Greater', 'GreaterOrEqual' => self::compare($type, $node, $scope),
            // Arithmetic
            'Add', 'Subtract', 'Multiply', 'Divide' => self::arithmetic($type, $node, $scope),
            'TruncatedDivide' => self::truncatedDivide($node, $scope),
            'Modulo' => self::modulo($node, $scope),
            'Negate' => self::negate($node, $scope),
            'Abs' => self::abs($node, $scope),
            'Ceiling', 'Floor', 'Truncate' => self::rounding($type, $node, $scope),
            'Round' => self::round($node, $scope),
            'Power' => self::power($node, $scope),
            'MinValue', 'MaxValue' => self::limitValue($type === 'MaxValue', $node, $scope),
            'Successor', 'Predecessor' => self::step($type === 'Successor', $node, $scope),
            // Dates
            'Now' => $scope->library->executionDateTime,
            'Today' => $scope->library->executionDateTime->getDate(),
            'DateFrom' => self::dateFrom($node, $scope),
            'DateTimeComponentFrom' => self::componentFrom($node, $scope),
            'TimezoneOffsetFrom' => self::timezoneOffsetFrom($node, $scope),
            'DifferenceBetween', 'DurationBetween' => self::between($type, $node, $scope),
            'SameAs', 'SameOrAfter', 'SameOrBefore' => self::sameAs($type, $node, $scope),
            'After', 'Before' => self::afterBefore($type, $node, $scope),
            // Intervals
            'Start', 'End' => self::startEnd($type === 'Start', $node, $scope),
            'Width', 'Size' => self::widthSize($type === 'Width', $node, $scope),
            'Overlaps', 'OverlapsAfter', 'OverlapsBefore', 'Meets', 'MeetsAfter', 'MeetsBefore', 'Starts', 'Ends'
                => self::intervalRelation($type, $node, $scope),
            'Collapse' => self::collapse($node, $scope),
            'Expand' => self::expand($node, $scope),
            // Lists and overloads
            'Exists' => self::exists($node, $scope),
            'SingletonFrom' => self::singletonFrom($node, $scope),
            'ToList' => self::toList($node, $scope),
            'First', 'Last' => self::firstLast($type === 'First', $node, $scope),
            'Flatten' => self::flatten($node, $scope),
            'Distinct' => self::distinct($node, $scope),
            'IndexOf' => self::indexOf($node, $scope),
            'Slice' => self::slice($node, $scope),
            'Union' => self::union($node, $scope),
            'Except' => self::except($node, $scope),
            'Intersect' => self::intersect($node, $scope),
            'Indexer' => self::indexer($node, $scope),
            'In', 'Contains' => self::membership($type, $node, $scope),
            'Includes', 'IncludedIn', 'ProperIncludes', 'ProperIncludedIn' => self::inclusion($type, $node, $scope),
            'Length' => self::length($node, $scope),
            // Aggregates
            'Count' => self::count($node, $scope),
            'Sum', 'Avg' => self::sumAvg($type === 'Avg', $node, $scope),
            'Min', 'Max' => self::minMax($type === 'Max', $node, $scope),
            // Types
            'As' => self::asType($node, $scope),
            'Is' => self::isType($node, $scope),
            'ToDate' => self::toDate($node, $scope),
            'ToDateTime' => self::toDateTime($node, $scope),
            'ToDecimal' => self::toDecimal($node, $scope),
            'ToInteger' => self::toInteger($node, $scope),
            'ToString' => self::toCqlString(self::arg($node, $scope)),
            'ToQuantity' => self::toQuantity(self::arg($node, $scope)),
            'ToConcept' => self::toConcept($node, $scope),
            'ToBoolean' => self::toBoolean($node, $scope),
            default => throw new \LogicException('Unimplemented Expression: ' . (is_string($type) ? $type : '?')),
        };
    }

    // ---------------------------------------------------------------
    // Values

    /**
     * @param array<mixed> $node
     */
    private static function literal(array $node): mixed
    {
        $value = $node['value'] ?? null;
        return match ($node['valueType'] ?? null) {
            self::SYSTEM . 'Boolean' => $value === 'true',
            self::SYSTEM . 'Integer' => self::parseInt(self::text($value)),
            self::SYSTEM . 'Decimal' => JavaScript::parseFloat(self::text($value)),
            self::SYSTEM . 'String' => str_replace(["\\'", '\\"'], ["'", '"'], self::text($value)),
            default => $value,
        };
    }

    /**
     * @param array<mixed> $node
     */
    private static function tuple(array $node, Scope $scope): Tuple
    {
        $elements = [];
        foreach (self::nodes($node['element'] ?? []) as $element) {
            $value = self::node($element['value'] ?? null);
            $elements[self::text($element['name'] ?? '')] = $value === null ? null : self::evaluate($value, $scope);
        }
        return new Tuple($elements);
    }

    /**
     * @param array<mixed> $node
     */
    private static function instance(array $node, Scope $scope): mixed
    {
        $values = [];
        foreach (self::nodes($node['element'] ?? []) as $element) {
            $value = self::node($element['value'] ?? null);
            $values[self::text($element['name'] ?? '')] = $value === null ? null : self::evaluate($value, $scope);
        }
        return match ($node['classType'] ?? null) {
            self::SYSTEM . 'Quantity' => new Quantity(self::numberOrNull($values['value'] ?? null), self::stringOrNull($values['unit'] ?? null)),
            self::SYSTEM . 'Code' => new Code(
                self::text($values['code'] ?? ''),
                self::stringOrNull($values['system'] ?? null),
                self::stringOrNull($values['version'] ?? null),
                self::stringOrNull($values['display'] ?? null),
            ),
            self::SYSTEM . 'Concept' => new Concept(self::codeList($values['codes'] ?? []), self::stringOrNull($values['display'] ?? null)),
            default => new Tuple($values),
        };
    }

    /**
     * @param array<mixed> $node
     */
    private static function ratio(array $node): Ratio
    {
        $numerator = $node['numerator'] ?? null;
        $denominator = $node['denominator'] ?? null;
        if (!is_array($numerator) || !is_array($denominator)) {
            throw new \UnexpectedValueException('Cannot create a ratio with an undefined value');
        }
        return new Ratio(
            new Quantity(self::numberOrNull($numerator['value'] ?? null), self::stringOrNull($numerator['unit'] ?? null)),
            new Quantity(self::numberOrNull($denominator['value'] ?? null), self::stringOrNull($denominator['unit'] ?? null)),
        );
    }

    /**
     * @param array<mixed> $node
     */
    private static function interval(array $node, Scope $scope): Interval
    {
        $lowNode = self::requireNode($node['low'] ?? null);
        $highNode = self::requireNode($node['high'] ?? null);
        $low = self::evaluate($lowNode, $scope);
        $high = self::evaluate($highNode, $scope);
        $lowClosed = self::closedFlag($node, 'lowClosed', $scope);
        $highClosed = self::closedFlag($node, 'highClosed', $scope);
        $defaultPointType = null;
        if ($low === null && $high === null) {
            $defaultPointType = self::namedAsType($lowNode) ?? self::namedAsType($highNode);
        }
        return new Interval($low, $high, $lowClosed, $highClosed, $defaultPointType);
    }

    /**
     * @param array<mixed> $node
     */
    private static function closedFlag(array $node, string $key, Scope $scope): ?bool
    {
        if (array_key_exists($key, $node) && $node[$key] !== null) {
            return self::truthy($node[$key]);
        }
        $expression = self::node($node[$key . 'Expression'] ?? null);
        if ($expression === null) {
            return null;
        }
        $value = self::evaluate($expression, $scope);
        return $value === null ? null : self::truthy($value);
    }

    /**
     * The named type of an As node, the only node that keeps its asTypeSpecifier.
     *
     * @param array<mixed> $node
     */
    private static function namedAsType(array $node): ?string
    {
        if (($node['type'] ?? null) !== 'As') {
            return null;
        }
        $spec = self::asSpecifier($node);
        return ($spec['type'] ?? null) === 'NamedTypeSpecifier' && is_string($spec['name'] ?? null) ? $spec['name'] : null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function dateTime(array $node, Scope $scope): CqlDateTime
    {
        $fields = [];
        foreach (['year', 'month', 'day', 'hour', 'minute', 'second', 'millisecond'] as $field) {
            $value = self::node($node[$field] ?? null);
            $fields[] = $value === null ? null : self::intOrNull(self::evaluate($value, $scope));
        }
        $offsetNode = self::node($node['timezoneOffset'] ?? null);
        $offset = $offsetNode === null
            ? $scope->library->executionDateTime->timezoneOffset
            : self::numberOrNull(self::evaluate($offsetNode, $scope));
        return new CqlDateTime(...[...$fields, $offset === null ? null : (float) $offset]);
    }

    /**
     * @param array<mixed> $node
     */
    private static function date(array $node, Scope $scope): CqlDate
    {
        $fields = [];
        foreach (['year', 'month', 'day'] as $field) {
            $value = self::node($node[$field] ?? null);
            $fields[] = $value === null ? null : self::intOrNull(self::evaluate($value, $scope));
        }
        return new CqlDate(...$fields);
    }

    /**
     * @param array<mixed> $node
     */
    private static function codeLiteral(array $node, Scope $scope): Code
    {
        $system = is_array($node['system'] ?? null) ? self::codeSystem($scope->library, self::name($node['system']), null) : null;
        return new Code(
            self::text($node['code'] ?? ''),
            $system?->id,
            self::stringOrNull($node['version'] ?? null),
            self::stringOrNull($node['display'] ?? null),
        );
    }

    /**
     * @param array<mixed> $node
     */
    private static function conceptLiteral(array $node, Scope $scope): Concept
    {
        $codes = [];
        foreach (self::nodes($node['code'] ?? []) as $code) {
            $codes[] = self::codeLiteral($code, $scope);
        }
        return new Concept($codes, self::stringOrNull($node['display'] ?? null));
    }

    // ---------------------------------------------------------------
    // References

    /**
     * @param array<mixed> $node
     */
    private static function expressionRef(array $node, Scope $scope): mixed
    {
        $name = self::name($node);
        $library = is_string($node['libraryName'] ?? null) ? $scope->library->includedContext($node['libraryName']) : $scope->library;
        if (!is_string($node['libraryName'] ?? null)) {
            // An alias or operand of the same name shadows the statement.
            [$found, $value] = $scope->lookup($name);
            if ($found) {
                return $value;
            }
        }
        return self::statement($library, $name);
    }

    /**
     * @param array<mixed> $node
     */
    private static function functionRef(array $node, Scope $scope): mixed
    {
        $name = self::name($node);
        $alias = $node['libraryName'] ?? null;
        $library = is_string($alias) ? $scope->library->includedContext($alias) : $scope->library;
        $args = self::evaluateAll(self::operands($node), $scope);
        $defs = array_values(array_filter(
            $library->library->functions[$name] ?? [],
            static fn (array $def): bool => count(self::nodes($def['operand'] ?? [])) === count($args),
        ));
        if (count($defs) > 1) {
            $defs = array_values(array_filter($defs, static function (array $def) use ($args): bool {
                $operands = self::nodes($def['operand'] ?? []);
                foreach ($args as $i => $arg) {
                    if ($arg === null) {
                        continue;
                    }
                    $spec = TypeMatcher::spec($operands[$i]['operandTypeSpecifier'] ?? null);
                    if ($spec === null && is_string($operands[$i]['operandType'] ?? null)) {
                        $spec = ['name' => $operands[$i]['operandType'], 'type' => 'NamedTypeSpecifier'];
                    }
                    if (!TypeMatcher::matches($arg, $spec)) {
                        return false;
                    }
                }
                return true;
            }));
        }
        if ($defs === []) {
            throw new \UnexpectedValueException('no function with matching signature could be found');
        }
        $def = $defs[count($defs) - 1];
        // A function of this library runs in a child of the caller's scope, as in cql-execution.
        $child = is_string($alias) ? $library->scope()->child() : $scope->child();
        foreach (self::nodes($def['operand'] ?? []) as $i => $operand) {
            $child->set(self::name($operand), $args[$i] ?? null);
        }
        $body = self::node($def['expression'] ?? null);
        return $body === null ? null : self::evaluate($body, $child);
    }

    /**
     * @param array<mixed> $node
     */
    private static function parameterRef(array $node, Scope $scope): mixed
    {
        $library = is_string($node['libraryName'] ?? null) ? $scope->library->includedContext($node['libraryName']) : $scope->library;
        $name = self::name($node);
        if (!isset($library->library->parameters[$name])) {
            return null;
        }
        // cql-execution never returns a parameter's default (its ParameterDef drops it).
        return $library->parameters[$name] ?? null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function identifierRef(array $node, Scope $scope): mixed
    {
        $name = self::name($node);
        [$found, $value] = $scope->lookup($name);
        if ($found && $value !== null) {
            return $value;
        }
        $parts = explode('.', $name);
        [, $current] = $scope->lookup($parts[0]);
        if ($current !== null && count($parts) > 1) {
            foreach (array_slice($parts, 1) as $part) {
                $current = $current === null ? null : self::propertyOf($current, $part);
            }
            return $current;
        }
        return $found ? $value : $current;
    }

    /**
     * @param array<mixed> $node
     */
    private static function property(array $node, Scope $scope): mixed
    {
        $path = self::text($node['path'] ?? '');
        if (is_string($node['scope'] ?? null)) {
            $object = self::lookup($scope, $node['scope']);
        } else {
            $source = self::node($node['source'] ?? null);
            $object = $source === null ? null : self::evaluate($source, $scope);
        }
        $value = self::propertyOf($object, $path);
        if ($value === null && str_contains($path, '.')) {
            $current = $object;
            foreach (explode('.', $path) as $part) {
                $current = self::propertyOf($current, $part);
            }
            $value = $current;
        }
        return $value;
    }

    /**
     * A property of a value, as cql-execution reads obj[path] or obj.get(path).
     */
    public static function propertyOf(mixed $object, string $name): mixed
    {
        if ($object === null) {
            return null;
        }
        if ($object instanceof QdmObject) {
            return $object->get($name);
        }
        if ($object instanceof Tuple) {
            return $object->elements[$name] ?? null;
        }
        if ($object instanceof Interval || $object instanceof Code || $object instanceof Quantity || $object instanceof Concept
            || $object instanceof Ratio || $object instanceof Uncertainty || $object instanceof CqlDateTime || $object instanceof CqlDate) {
            $properties = get_object_vars($object);
            return $properties[$name] ?? null;
        }
        return null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function valueSetRef(array $node, Scope $scope): CqlValueSet
    {
        $name = self::name($node);
        $libraryName = $node['libraryName'] ?? null;
        $library = $scope->library->library;
        if (is_string($libraryName) && isset($library->includes[$libraryName])) {
            $library = $scope->library->repository->included($library, $libraryName) ?? $library;
        } elseif (is_string($libraryName) && $libraryName !== $library->name) {
            throw new \UnexpectedValueException("No value set $name in $libraryName");
        }
        $def = $library->valueSets[$name] ?? throw new \UnexpectedValueException("No value set $name");
        // cqm-execution removes value set versions before calculating.
        return new CqlValueSet(self::text($def['id'] ?? ''), null, $name);
    }

    /**
     * @param array<mixed> $node
     */
    private static function codeSystemRef(array $node, Scope $scope): CodeSystem
    {
        return self::codeSystem($scope->library, self::name($node), self::stringOrNull($node['libraryName'] ?? null))
            ?? throw new \UnexpectedValueException('No code system ' . self::name($node));
    }

    private static function codeSystem(LibraryContext $context, string $name, ?string $libraryName): ?CodeSystem
    {
        $library = $context->library;
        if ($libraryName !== null && isset($library->includes[$libraryName])) {
            $library = $context->repository->included($library, $libraryName) ?? $library;
        }
        $def = $library->codeSystems[$name] ?? null;
        return $def === null ? null : new CodeSystem(self::text($def['id'] ?? ''), self::stringOrNull($def['version'] ?? null), $name);
    }

    /**
     * @param array<mixed> $node
     */
    private static function codeRef(array $node, Scope $scope): ?Code
    {
        $context = is_string($node['libraryName'] ?? null) ? $scope->library->includedContext($node['libraryName']) : $scope->library;
        return self::codeDef($context, self::name($node));
    }

    private static function codeDef(LibraryContext $context, string $name): ?Code
    {
        $def = $context->library->codes[$name] ?? null;
        if ($def === null) {
            return null;
        }
        $systemName = is_array($def['codeSystem'] ?? null) ? self::name($def['codeSystem']) : '';
        $system = self::codeSystem($context, $systemName, null) ?? throw new \UnexpectedValueException("No code system $systemName");
        return new Code(self::text($def['id'] ?? ''), $system->id, $system->version, self::stringOrNull($def['display'] ?? null));
    }

    /**
     * @param array<mixed> $node
     */
    private static function conceptRef(array $node, Scope $scope): ?Concept
    {
        $def = $scope->library->library->concepts[self::name($node)] ?? null;
        if ($def === null) {
            return null;
        }
        $codes = [];
        foreach (self::nodes($def['code'] ?? []) as $code) {
            $codes[] = self::codeDef($scope->library, self::name($code));
        }
        return new Concept(array_values(array_filter($codes)), self::stringOrNull($def['display'] ?? null));
    }

    // ---------------------------------------------------------------
    // Clinical

    /**
     * @param array<mixed> $node
     */
    private static function retrieve(array $node, Scope $scope): mixed
    {
        $codes = null;
        $codesNode = self::node($node['codes'] ?? null);
        if ($codesNode !== null) {
            $executed = self::evaluate($codesNode, $scope);
            if ($executed === null) {
                return [];
            }
            $codes = is_array($executed) ? $executed : self::resolveValueSet($executed, $scope);
        }
        if (isset($node['dateRange'])) {
            throw new \LogicException('Retrieve date ranges are not supported');
        }
        $profile = self::stringOrNull($node['templateId'] ?? null) ?? self::text($node['dataType'] ?? '');
        $records = $scope->library->patient->findRecords($profile);
        if ($codes === null) {
            return $records;
        }
        return array_values(array_filter($records, static function (mixed $record) use ($codes): bool {
            if (!$record instanceof QdmObject || !$record->isDataElement) {
                throw new \UnexpectedValueException('Record has no codes');
            }
            $recordCodes = $record->getCode();
            if ($codes instanceof ValueSet) {
                return $codes->hasMatch($recordCodes);
            }
            foreach ($codes as $code) {
                if (($code instanceof Code || $code instanceof Concept || $code instanceof ValueSet) && $code->hasMatch($recordCodes)) {
                    return true;
                }
            }
            return false;
        }));
    }

    private static function resolveValueSet(mixed $valueSet, Scope $scope): ValueSet
    {
        if (!$valueSet instanceof CqlValueSet) {
            throw new \UnexpectedValueException('ValueSet must be provided');
        }
        return $scope->library->codeService->findValueSet($valueSet->id, $valueSet->version)
            ?? throw new \UnexpectedValueException("Unable to resolve expected valueset with id {$valueSet->id}");
    }

    /**
     * @param array<mixed> $node
     */
    private static function inValueSet(array $node, Scope $scope): bool
    {
        $code = self::evaluate(self::requireNode($node['code'] ?? null), $scope);
        if ($code === null) {
            return false;
        }
        return self::resolveValueSet(self::valueSetOperand($node, $scope), $scope)->hasMatch($code);
    }

    /**
     * @param array<mixed> $node
     */
    private static function anyInValueSet(array $node, Scope $scope): bool
    {
        $codes = self::evaluate(self::requireNode($node['codes'] ?? null), $scope);
        if ($codes === null) {
            return false;
        }
        $expansion = self::resolveValueSet(self::valueSetOperand($node, $scope), $scope);
        foreach (is_array($codes) ? $codes : [$codes] as $code) {
            if ($expansion->hasMatch($code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<mixed> $node
     */
    private static function valueSetOperand(array $node, Scope $scope): mixed
    {
        if (is_array($node['valueset'] ?? null)) {
            return self::valueSetRef($node['valueset'], $scope);
        }
        return self::evaluate(self::requireNode($node['valuesetExpression'] ?? null), $scope);
    }

    /**
     * @param array<mixed> $node
     */
    private static function calculateAgeAt(array $node, Scope $scope): mixed
    {
        [$birthDate, $asOf] = self::args($node, $scope) + [null, null];
        return self::age(self::precision($node), $birthDate, $asOf, $scope->library->executionDateTime->timezoneOffset);
    }

    /**
     * @param array<mixed> $node
     */
    private static function calculateAge(array $node, Scope $scope): mixed
    {
        $birthDate = self::arg($node, $scope);
        $precision = self::precision($node);
        $now = $scope->library->executionDateTime;
        $asOf = $precision === Precision::Year || $precision === Precision::Month ? $now->getDate() : $now;
        return self::age($precision, $birthDate, $asOf, null);
    }

    private static function age(?Precision $precision, mixed $birthDate, mixed $asOf, ?float $offset): mixed
    {
        if ($birthDate === null || $asOf === null || $precision === null) {
            return null;
        }
        if ($asOf instanceof CqlDate && $birthDate instanceof CqlDateTime) {
            $birthDate = $birthDate->getDate();
        } elseif ($asOf instanceof CqlDateTime && $birthDate instanceof CqlDate) {
            $birthDate = $offset === null ? $birthDate->getDateTime() : $birthDate->getDateTime($offset);
        }
        if (!$birthDate instanceof CqlTemporal) {
            throw new \UnexpectedValueException('Birth date is not a date');
        }
        $result = $birthDate->durationBetween($asOf, $precision);
        return $result !== null && $result->isPoint() ? $result->low : $result;
    }

    // ---------------------------------------------------------------
    // Queries

    /**
     * @param array<mixed> $node
     */
    private static function query(array $node, Scope $scope): mixed
    {
        $sources = self::nodes($node['source'] ?? []);
        $aliases = array_map(static fn (array $source): string => self::text($source['alias'] ?? ''), $sources);
        $results = [];
        foreach ($sources as $source) {
            $results[] = self::evaluate(self::requireNode($source['expression'] ?? null), $scope);
        }
        if (array_filter($results, static fn (mixed $r): bool => $r !== null) === []) {
            return null;
        }
        $isList = array_filter($results, is_array(...)) !== [];
        $lists = array_map(static fn (mixed $r): array => $r === null ? [] : (is_array($r) ? $r : [$r]), $results);
        $rows = [[]];
        foreach ($lists as $list) {
            $next = [];
            foreach ($rows as $row) {
                foreach ($list as $item) {
                    $next[] = [...$row, $item];
                }
            }
            $rows = $next;
        }
        $lets = self::nodes($node['let'] ?? []);
        $relationships = self::nodes($node['relationship'] ?? []);
        $where = self::node($node['where'] ?? null);
        $return = self::node($node['return'] ?? null);
        $aggregate = self::node($node['aggregate'] ?? null);
        $returned = [];
        foreach ($rows as $row) {
            $rowScope = $scope->child();
            foreach ($row as $i => $item) {
                $rowScope->set($aliases[$i], $item);
            }
            foreach ($lets as $let) {
                $rowScope->set(self::text($let['identifier'] ?? ''), self::evaluate(self::requireNode($let['expression'] ?? null), $rowScope));
            }
            $passed = true;
            foreach ($relationships as $relationship) {
                if (!self::truthy(self::relationship($relationship, $rowScope->child()))) {
                    $passed = false;
                }
            }
            if ($passed && $where !== null) {
                $passed = self::truthy(self::evaluate($where, $rowScope));
            }
            if (!$passed) {
                continue;
            }
            if ($return !== null) {
                $returned[] = self::evaluate(self::requireNode($return['expression'] ?? null), $rowScope);
            } elseif (count($aliases) === 1 && $aggregate === null) {
                $returned[] = $row[0];
            } else {
                $returned[] = $rowScope->namesAsTuple();
            }
        }
        if (self::queryIsDistinct($return, $aggregate)) {
            $returned = self::distinctList($returned);
        }
        if ($aggregate !== null) {
            $returned = self::aggregate($aggregate, $returned, $scope);
        }
        $sort = self::node($node['sort'] ?? null);
        if ($sort !== null && is_array($returned)) {
            $returned = self::sort($sort, array_values($returned), $scope);
        }
        if ($isList || $aggregate !== null) {
            return $returned;
        }
        return is_array($returned) ? ($returned[0] ?? null) : $returned;
    }

    /**
     * @param array<mixed>|null $return
     * @param array<mixed>|null $aggregate
     */
    private static function queryIsDistinct(?array $return, ?array $aggregate): bool
    {
        if ($aggregate !== null) {
            return self::truthy($aggregate['distinct'] ?? false);
        }
        if ($return !== null) {
            return !array_key_exists('distinct', $return) || $return['distinct'] === null || self::truthy($return['distinct']);
        }
        return true;
    }

    /**
     * With and Without: whether any related record meets the condition.
     *
     * @param array<mixed> $node
     */
    private static function relationship(array $node, Scope $scope): bool
    {
        $records = self::evaluate(self::requireNode($node['expression'] ?? null), $scope);
        if (!is_array($records)) {
            $records = [$records];
        }
        $suchThat = self::requireNode($node['suchThat'] ?? null);
        $alias = self::text($node['alias'] ?? '');
        $any = false;
        foreach ($records as $record) {
            $child = $scope->child();
            $child->set($alias, $record);
            if (self::truthy(self::evaluate($suchThat, $child))) {
                $any = true;
            }
        }
        return ($node['type'] ?? null) === 'Without' ? !$any : $any;
    }

    /**
     * @param array<mixed> $aggregate
     * @param list<mixed> $returned
     */
    private static function aggregate(array $aggregate, array $returned, Scope $scope): mixed
    {
        $starting = self::node($aggregate['starting'] ?? null);
        $value = $starting === null ? null : self::evaluate($starting, $scope);
        $identifier = self::text($aggregate['identifier'] ?? '');
        $expression = self::requireNode($aggregate['expression'] ?? null);
        foreach ($returned as $row) {
            $child = $scope->child($row);
            $child->set($identifier, $value);
            $value = self::evaluate($expression, $child);
        }
        return $value;
    }

    /**
     * A stable merge sort with cql-execution's comparisons.
     *
     * @param array<mixed> $sort
     * @param list<mixed> $values
     * @return list<mixed>
     */
    private static function sort(array $sort, array $values, Scope $scope): array
    {
        $by = self::nodes($sort['by'] ?? []);
        if ($by === []) {
            return $values;
        }
        $compare = static function (mixed $a, mixed $b) use ($by, $scope): int {
            foreach ($by as $item) {
                $order = self::sortOrder($item, $a, $b, $scope);
                if ($order !== 0) {
                    return $order;
                }
            }
            return 0;
        };
        return self::mergeSort($values, $compare);
    }

    /**
     * @param list<mixed> $values
     * @param \Closure(mixed, mixed): int $compare
     * @return list<mixed>
     */
    private static function mergeSort(array $values, \Closure $compare): array
    {
        if (count($values) <= 1) {
            return $values;
        }
        $mid = intdiv(count($values), 2);
        $left = self::mergeSort(array_slice($values, 0, $mid), $compare);
        $right = self::mergeSort(array_slice($values, $mid), $compare);
        $sorted = [];
        while ($left !== [] && $right !== []) {
            if ($compare($left[0], $right[0]) <= 0) {
                $sorted[] = array_shift($left);
            } else {
                $sorted[] = array_shift($right);
            }
        }
        return [...$sorted, ...$left, ...$right];
    }

    /**
     * @param array<mixed> $item
     */
    private static function sortOrder(array $item, mixed $a, mixed $b, Scope $scope): int
    {
        $direction = $item['direction'] ?? 'asc';
        $low = $direction === 'asc' || $direction === 'ascending' ? -1 : 1;
        $high = -$low;
        if (($item['type'] ?? null) === 'ByDirection') {
            if (self::strictEquals($a, $b)) {
                return 0;
            }
            if ($a instanceof Quantity && $b instanceof Quantity) {
                return $a->before($b) === true ? $low : $high;
            }
            return self::jsLess($a, $b) ? $low : $high;
        }
        $expression = ($item['type'] ?? null) === 'ByColumn'
            ? ['type' => 'IdentifierRef', 'name' => $item['path'] ?? '']
            : self::requireNode($item['expression'] ?? null);
        $aValue = self::evaluate($expression, $scope->child($a));
        $bValue = self::evaluate($expression, $scope->child($b));
        if (self::strictEquals($aValue, $bValue) || ($aValue === null && $bValue === null)) {
            return 0;
        }
        if ($aValue === null || $bValue === null) {
            return $aValue === null ? $low : $high;
        }
        if ($aValue instanceof Quantity && $bValue instanceof Quantity) {
            return $aValue->before($bValue) === true ? $low : $high;
        }
        return self::jsLess($aValue, $bValue) ? $low : $high;
    }

    // ---------------------------------------------------------------
    // Logic, nulls and conditions

    /**
     * @param array<mixed> $node
     */
    private static function coalesce(array $node, Scope $scope): mixed
    {
        $operands = self::operands($node);
        foreach ($operands as $operand) {
            $result = self::evaluate($operand, $scope);
            if (count($operands) === 1 && is_array($result)) {
                foreach ($result as $item) {
                    if ($item !== null) {
                        return $item;
                    }
                }
            } elseif ($result !== null) {
                return $result;
            }
        }
        return null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function caseExpression(array $node, Scope $scope): mixed
    {
        $comparand = self::node($node['comparand'] ?? null);
        $value = $comparand === null ? null : self::evaluate($comparand, $scope);
        foreach (self::nodes($node['caseItem'] ?? []) as $item) {
            $when = self::evaluate(self::requireNode($item['when'] ?? null), $scope);
            $matched = $comparand === null ? self::truthy($when) : Comparison::equals($when, $value) === true;
            if ($matched) {
                return self::evaluate(self::requireNode($item['then'] ?? null), $scope);
            }
        }
        return self::evaluate(self::requireNode($node['else'] ?? null), $scope);
    }

    // ---------------------------------------------------------------
    // Comparison

    /**
     * @param array<mixed> $node
     */
    private static function equal(array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        return $a === null || $b === null ? null : Comparison::equals($a, $b);
    }

    /**
     * @param array<mixed> $node
     */
    private static function notEqual(array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        return $a === null || $b === null ? null : ThreeValuedLogic::not(Comparison::equals($a, $b));
    }

    /**
     * @param array<mixed> $node
     */
    private static function equivalent(array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        if ($a === null && $b === null) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }
        if ($a instanceof CqlValueSet && $b instanceof CqlValueSet && $a->id === $b->id && $a->version === $b->version) {
            return true;
        }
        if ($a instanceof CqlValueSet) {
            $a = self::resolveValueSet($a, $scope);
        }
        if ($b instanceof CqlValueSet) {
            $b = self::resolveValueSet($b, $scope);
        }
        return Comparison::equivalent($a, $b);
    }

    /**
     * @param array<mixed> $node
     */
    private static function compare(string $type, array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        $a = Uncertainty::from($a);
        $b = Uncertainty::from($b);
        return match ($type) {
            'Less' => $a->lessThan($b),
            'LessOrEqual' => $a->lessThanOrEquals($b),
            'Greater' => $a->greaterThan($b),
            default => $a->greaterThanOrEquals($b),
        };
    }

    // ---------------------------------------------------------------
    // Arithmetic

    /**
     * @param array<mixed> $node
     */
    private static function arithmetic(string $type, array $node, Scope $scope): mixed
    {
        $args = self::args($node, $scope);
        if (in_array(null, $args, true)) {
            return null;
        }
        $result = array_shift($args);
        foreach ($args as $y) {
            $result = self::arithmeticStep($type, $result, $y);
        }
        return CqlMath::overflowsOrUnderflows($result) ? null : $result;
    }

    private static function arithmeticStep(string $type, mixed $x, mixed $y): mixed
    {
        if ($x instanceof Uncertainty && !$y instanceof Uncertainty) {
            $y = new Uncertainty($y, $y);
        } elseif ($y instanceof Uncertainty && !$x instanceof Uncertainty) {
            $x = new Uncertainty($x, $x);
        }
        switch ($type) {
            case 'Add':
            case 'Subtract':
                if ($x instanceof Quantity || $x instanceof CqlTemporal) {
                    return $type === 'Add' ? Quantity::doAddition($x, $y) : Quantity::doSubtraction($x, $y);
                }
                if ($x instanceof Uncertainty && $y instanceof Uncertainty) {
                    $isObject = $x->low instanceof Quantity || $x->low instanceof CqlTemporal;
                    if ($type === 'Add') {
                        return $isObject
                            ? new Uncertainty(Quantity::doAddition(self::addend($x->low), $y->low), Quantity::doAddition(self::addend($x->high), $y->high))
                            : new Uncertainty(self::numeric($x->low) + self::numeric($y->low), self::numeric($x->high) + self::numeric($y->high));
                    }
                    return $isObject
                        ? new Uncertainty(Quantity::doSubtraction(self::addend($x->low), $y->high), Quantity::doSubtraction(self::addend($x->high), $y->low))
                        : new Uncertainty(self::numeric($x->low) - self::numeric($y->high), self::numeric($x->high) - self::numeric($y->low));
                }
                return $type === 'Add' ? self::numeric($x) + self::numeric($y) : self::numeric($x) - self::numeric($y);
            case 'Multiply':
                if ($x instanceof Quantity) {
                    return $x->multiplyBy(self::quantityOperand($y));
                }
                if ($y instanceof Quantity) {
                    return $y->multiplyBy(self::quantityOperand($x));
                }
                if ($x instanceof Uncertainty && $y instanceof Uncertainty) {
                    return new Uncertainty(self::numeric($x->low) * self::numeric($y->low), self::numeric($x->high) * self::numeric($y->high));
                }
                return self::numeric($x) * self::numeric($y);
            default:
                if ($x instanceof Quantity) {
                    return $x->dividedBy(self::quantityOperand($y));
                }
                if ($x instanceof Uncertainty && $y instanceof Uncertainty) {
                    return new Uncertainty(fdiv(self::numeric($x->low), self::numeric($y->high)), fdiv(self::numeric($x->high), self::numeric($y->low)));
                }
                return fdiv(self::numeric($x), self::numeric($y));
        }
    }

    private static function addend(mixed $value): Quantity|CqlTemporal
    {
        return $value instanceof Quantity || $value instanceof CqlTemporal ? $value : throw new \UnexpectedValueException('Unsupported argument types.');
    }

    private static function quantityOperand(mixed $value): Quantity|int|float|null
    {
        return $value === null || is_int($value) || is_float($value) || $value instanceof Quantity
            ? $value
            : throw new \UnexpectedValueException('Unsupported argument types.');
    }

    /**
     * @param array<mixed> $node
     */
    private static function truncatedDivide(array $node, Scope $scope): mixed
    {
        $args = self::args($node, $scope);
        if (in_array(null, $args, true)) {
            return null;
        }
        $quotient = self::numeric(array_shift($args));
        foreach ($args as $y) {
            $quotient = fdiv($quotient, self::numeric($y));
        }
        $truncated = $quotient >= 0 ? floor($quotient) : ceil($quotient);
        return CqlMath::overflowsOrUnderflows($truncated) ? null : $truncated;
    }

    /**
     * @param array<mixed> $node
     */
    private static function modulo(array $node, Scope $scope): mixed
    {
        $args = self::args($node, $scope);
        if (in_array(null, $args, true)) {
            return null;
        }
        $result = self::numeric(array_shift($args));
        foreach ($args as $y) {
            $result = fmod((float) $result, (float) self::numeric($y));
        }
        return CqlMath::isValidDecimal($result) ? $result : null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function negate(array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        return $arg instanceof Quantity ? new Quantity($arg->value * -1, $arg->unit) : self::numeric($arg) * -1;
    }

    /**
     * @param array<mixed> $node
     */
    private static function abs(array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        return $arg instanceof Quantity ? new Quantity(abs($arg->value), $arg->unit) : abs(self::numeric($arg));
    }

    /**
     * @param array<mixed> $node
     */
    private static function rounding(string $type, array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        $value = self::numeric($arg);
        return match ($type) {
            'Ceiling' => ceil($value),
            'Floor' => floor($value),
            default => $value >= 0 ? floor($value) : ceil($value),
        };
    }

    /**
     * @param array<mixed> $node
     */
    private static function round(array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        $precisionNode = self::node($node['precision'] ?? null);
        $places = $precisionNode === null ? 0 : self::evaluate($precisionNode, $scope);
        $scale = Fdlibm::pow(10.0, (float) self::numeric($places ?? 0));
        return fdiv(JavaScript::round(self::numeric($arg) * $scale), $scale);
    }

    /**
     * @param array<mixed> $node
     */
    private static function power(array $node, Scope $scope): mixed
    {
        $args = self::args($node, $scope);
        if (in_array(null, $args, true)) {
            return null;
        }
        $result = self::numeric(array_shift($args));
        foreach ($args as $y) {
            $result = Fdlibm::pow((float) $result, (float) self::numeric($y));
        }
        return CqlMath::overflowsOrUnderflows($result) ? null : $result;
    }

    /**
     * @param array<mixed> $node
     */
    private static function limitValue(bool $max, array $node, Scope $scope): mixed
    {
        $type = self::text($node['valueType'] ?? '');
        if ($type === self::SYSTEM . 'Time') {
            throw new \LogicException('CQL Time values are not supported');
        }
        $value = $max ? CqlMath::maxValueForType($type) : CqlMath::minValueForType($type);
        if ($value === null) {
            throw new \UnexpectedValueException(($max ? 'Maximum' : 'Minimum') . " not supported for $type");
        }
        if ($value instanceof CqlDateTime) {
            return $value->withTimezoneOffset($scope->library->executionDateTime->timezoneOffset);
        }
        return $value;
    }

    /**
     * @param array<mixed> $node
     */
    private static function step(bool $successor, array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        try {
            $value = $successor ? CqlMath::successor($arg) : CqlMath::predecessor($arg);
        } catch (CqlOverflowException | \RuntimeException) {
            // cql-execution returns null for any error here.
            return null;
        }
        return CqlMath::overflowsOrUnderflows($value) ? null : $value;
    }

    // ---------------------------------------------------------------
    // Dates

    /**
     * @param array<mixed> $node
     */
    private static function dateFrom(array $node, Scope $scope): ?CqlDate
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        return $arg instanceof CqlDateTime ? $arg->getDate() : throw new \UnexpectedValueException('DateFrom needs a DateTime');
    }

    /**
     * @param array<mixed> $node
     */
    private static function componentFrom(array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        $precision = self::precision($node);
        if ($arg === null || $precision === null) {
            return null;
        }
        return $arg instanceof CqlTemporal ? $arg->field($precision) : null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function timezoneOffsetFrom(array $node, Scope $scope): ?float
    {
        $arg = self::arg($node, $scope);
        return $arg instanceof CqlDateTime ? $arg->timezoneOffset : null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function between(string $type, array $node, Scope $scope): mixed
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        $precision = self::precision($node);
        if (!$a instanceof CqlTemporal || !$b instanceof CqlTemporal || $precision === null) {
            return null;
        }
        $result = $type === 'DifferenceBetween' ? $a->differenceBetween($b, $precision) : $a->durationBetween($b, $precision);
        return $result !== null && $result->isPoint() ? $result->low : $result;
    }

    /**
     * @param array<mixed> $node
     */
    private static function sameAs(string $type, array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        if ($a === null || $b === null) {
            return null;
        }
        $method = match ($type) {
            'SameAs' => 'sameAs',
            'SameOrAfter' => 'sameOrAfter',
            default => 'sameOrBefore',
        };
        if ($a instanceof Quantity && $method !== 'sameAs') {
            return $a->{$method}($b);
        }
        if (!$a instanceof CqlTemporal && !$a instanceof Interval) {
            throw new \UnexpectedValueException("$type needs a date or interval");
        }
        return $a->{$method}($b, self::precision($node));
    }

    /**
     * @param array<mixed> $node
     */
    private static function afterBefore(string $type, array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        if ($a === null || $b === null) {
            return null;
        }
        $method = $type === 'After' ? 'after' : 'before';
        if ($a instanceof Quantity) {
            return $a->{$method}($b);
        }
        if (!$a instanceof CqlTemporal && !$a instanceof Interval) {
            throw new \UnexpectedValueException("$type needs a date or interval");
        }
        return $a->{$method}($b, self::precision($node));
    }

    // ---------------------------------------------------------------
    // Intervals

    /**
     * @param array<mixed> $node
     */
    private static function startEnd(bool $start, array $node, Scope $scope): mixed
    {
        $interval = self::arg($node, $scope);
        if ($interval === null) {
            return null;
        }
        if (!$interval instanceof Interval) {
            throw new \UnexpectedValueException('Start and End need an interval');
        }
        $point = $start ? $interval->start() : $interval->end();
        $limit = $start ? CqlDateTime::minimum() : CqlDateTime::maximum();
        if ($point instanceof CqlDateTime && $point->equals($limit) === true) {
            return $point->withTimezoneOffset($scope->library->executionDateTime->timezoneOffset);
        }
        return $point;
    }

    /**
     * @param array<mixed> $node
     */
    private static function widthSize(bool $width, array $node, Scope $scope): mixed
    {
        $interval = self::arg($node, $scope);
        if ($interval === null) {
            return null;
        }
        if (!$interval instanceof Interval) {
            throw new \UnexpectedValueException('Width and Size need an interval');
        }
        return $width ? $interval->width() : $interval->size();
    }

    /**
     * @param array<mixed> $node
     */
    private static function intervalRelation(string $type, array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        if ($a === null || $b === null) {
            return null;
        }
        if (!$a instanceof Interval) {
            throw new \UnexpectedValueException("$type needs an interval");
        }
        $precision = self::precision($node);
        return match ($type) {
            'Overlaps' => $a->overlaps($b, $precision),
            'OverlapsAfter' => $a->overlapsAfter($b, $precision),
            'OverlapsBefore' => $a->overlapsBefore($b, $precision),
            'Meets' => $a->meets($b, $precision),
            'MeetsAfter' => $a->meetsAfter($b, $precision),
            'MeetsBefore' => $a->meetsBefore($b, $precision),
            'Starts' => $a->starts($b, $precision),
            default => $a->ends($b, $precision),
        };
    }

    /**
     * @param array<mixed> $node
     */
    private static function collapse(array $node, Scope $scope): mixed
    {
        [$intervals, $per] = self::args($node, $scope) + [null, null];
        return IntervalLists::collapse($intervals, $per);
    }

    /**
     * @param array<mixed> $node
     */
    private static function expand(array $node, Scope $scope): mixed
    {
        [$intervals, $per] = self::args($node, $scope) + [null, null];
        return IntervalLists::expand($intervals, $per);
    }

    // ---------------------------------------------------------------
    // Lists

    /**
     * @param array<mixed> $node
     */
    private static function exists(array $node, Scope $scope): bool
    {
        $list = self::arg($node, $scope);
        if (!is_array($list)) {
            return $list !== null && self::truthy($list) ? throw new \UnexpectedValueException('Exists needs a list') : false;
        }
        foreach ($list as $item) {
            if ($item !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<mixed> $node
     */
    private static function singletonFrom(array $node, Scope $scope): mixed
    {
        $list = self::arg($node, $scope);
        if (is_array($list) && count($list) > 1) {
            throw new \UnexpectedValueException("IllegalArgument: 'SingletonFrom' requires a 0 or 1 arg array");
        }
        return is_array($list) ? ($list[0] ?? null) : null;
    }

    /**
     * @param array<mixed> $node
     * @return list<mixed>
     */
    private static function toList(array $node, Scope $scope): array
    {
        $arg = self::arg($node, $scope);
        return $arg === null ? [] : [$arg];
    }

    /**
     * @param array<mixed> $node
     */
    private static function firstLast(bool $first, array $node, Scope $scope): mixed
    {
        $source = self::evaluate(self::requireNode($node['source'] ?? null), $scope);
        if (!is_array($source) || $source === []) {
            return null;
        }
        return $first ? $source[0] : $source[count($source) - 1];
    }

    /**
     * @param array<mixed> $node
     */
    private static function flatten(array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        if (!is_array($arg)) {
            return $arg;
        }
        $flat = [];
        foreach ($arg as $inner) {
            if (!is_array($inner)) {
                return $arg;
            }
            array_push($flat, ...array_values($inner));
        }
        return $flat;
    }

    /**
     * @param array<mixed> $node
     * @return ?list<mixed>
     */
    private static function distinct(array $node, Scope $scope): ?array
    {
        $arg = self::arg($node, $scope);
        return is_array($arg) ? self::distinctList($arg) : null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function indexOf(array $node, Scope $scope): ?int
    {
        $source = self::evaluate(self::requireNode($node['source'] ?? null), $scope);
        $element = self::evaluate(self::requireNode($node['element'] ?? null), $scope);
        if (!is_array($source) || $element === null) {
            return null;
        }
        foreach ($source as $i => $item) {
            if (Comparison::equals($item, $element) === true) {
                return $i;
            }
        }
        return -1;
    }

    /**
     * @param array<mixed> $node
     * @return ?list<mixed>
     */
    private static function slice(array $node, Scope $scope): ?array
    {
        $source = self::evaluate(self::requireNode($node['source'] ?? null), $scope);
        if (!is_array($source)) {
            return null;
        }
        $start = self::evaluate(self::requireNode($node['startIndex'] ?? null), $scope) ?? 0;
        $end = self::evaluate(self::requireNode($node['endIndex'] ?? null), $scope) ?? count($source);
        $start = (int) self::numeric($start);
        $end = (int) self::numeric($end);
        if ($source === [] || $start < 0 || $end < 0 || $end < $start) {
            return [];
        }
        return array_values(array_slice($source, $start, $end - $start));
    }

    /**
     * @param array<mixed> $node
     */
    private static function union(array $node, Scope $scope): mixed
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        if ($a === null && $b === null) {
            foreach (self::operands($node) as $operand) {
                if (($operand['type'] ?? null) === 'As' && (self::asSpecifier($operand)['type'] ?? null) === 'ListTypeSpecifier') {
                    return [];
                }
            }
            return null;
        }
        if ($a === null || $b === null) {
            $notNull = $a ?? $b;
            return is_array($notNull) ? $notNull : null;
        }
        if (is_array($a)) {
            return self::removeDuplicateNulls(self::distinctList([...$a, ...(is_array($b) ? $b : [$b])]));
        }
        if (!$a instanceof Interval) {
            throw new \UnexpectedValueException('Union needs lists or intervals');
        }
        return $a->union($b);
    }

    /**
     * @param array<mixed> $node
     */
    private static function except(array $node, Scope $scope): mixed
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        if ($a === null) {
            return null;
        }
        if ($b === null) {
            return is_array($a) ? $a : null;
        }
        if (is_array($a)) {
            $list = is_array($b) ? $b : [$b];
            return array_values(array_filter(
                self::removeDuplicateNulls(self::distinctList($a)),
                static fn (mixed $item): bool => !self::listContains($list, $item),
            ));
        }
        if (!$a instanceof Interval) {
            throw new \UnexpectedValueException('Except needs lists or intervals');
        }
        return $a->except($b);
    }

    /**
     * @param array<mixed> $node
     */
    private static function intersect(array $node, Scope $scope): mixed
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        if ($a === null || $b === null) {
            return null;
        }
        if (is_array($a)) {
            $list = is_array($b) ? $b : [$b];
            return array_values(array_filter(
                self::removeDuplicateNulls(self::distinctList($a)),
                static fn (mixed $item): bool => self::listContains($list, $item),
            ));
        }
        if (!$a instanceof Interval) {
            throw new \UnexpectedValueException('Intersect needs lists or intervals');
        }
        return $a->intersect($b);
    }

    /**
     * @param array<mixed> $node
     */
    private static function indexer(array $node, Scope $scope): mixed
    {
        [$operand, $index] = self::args($node, $scope) + [null, null];
        if ($operand === null || $index === null) {
            return null;
        }
        $i = self::numeric($index);
        if (is_string($operand)) {
            $characters = mb_str_split($operand);
            return $i < 0 || $i >= count($characters) || floor($i) != $i ? null : $characters[(int) $i];
        }
        if (!is_array($operand) || $i < 0 || $i >= count($operand) || floor($i) != $i) {
            return null;
        }
        return $operand[(int) $i];
    }

    /**
     * @param array<mixed> $node
     */
    private static function membership(string $type, array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        [$item, $container] = $type === 'In' ? [$a, $b] : [$b, $a];
        if ($container === null) {
            return false;
        }
        if (is_array($container)) {
            return self::listContains($container, $item);
        }
        if ($item === null) {
            return null;
        }
        if (!$container instanceof Interval) {
            throw new \UnexpectedValueException("$type needs a list or interval");
        }
        return $container->contains($item, self::precision($node));
    }

    /**
     * @param array<mixed> $node
     */
    private static function inclusion(string $type, array $node, Scope $scope): ?bool
    {
        [$a, $b] = self::args($node, $scope) + [null, null];
        [$container, $contained] = $type === 'Includes' || $type === 'ProperIncludes' ? [$a, $b] : [$b, $a];
        if ($container === null || $contained === null) {
            return null;
        }
        $proper = $type === 'ProperIncludes' || $type === 'ProperIncludedIn';
        if (is_array($container)) {
            $sublist = is_array($contained) ? $contained : throw new \UnexpectedValueException("$type needs a list to include");
            $includes = true;
            foreach ($sublist as $item) {
                if (!self::listContains($container, $item)) {
                    $includes = false;
                }
            }
            return $proper ? count($container) > count($sublist) && $includes : $includes;
        }
        if (!$container instanceof Interval) {
            throw new \UnexpectedValueException("$type needs a list or interval");
        }
        $precision = self::precision($node);
        return $proper ? $container->properlyIncludes($contained, $precision) : $container->includes($contained, $precision);
    }

    /**
     * @param array<mixed> $node
     */
    private static function length(array $node, Scope $scope): ?int
    {
        $arg = self::arg($node, $scope);
        if (is_array($arg)) {
            return count($arg);
        }
        if (is_string($arg)) {
            return strlen(mb_convert_encoding($arg, 'UTF-16LE', 'UTF-8')) >> 1;
        }
        $operand = self::node($node['operand'] ?? null);
        if ($operand !== null && ($operand['type'] ?? null) === 'As' && (self::asSpecifier($operand)['type'] ?? null) === 'ListTypeSpecifier') {
            return 0;
        }
        return null;
    }

    // ---------------------------------------------------------------
    // Aggregates

    /**
     * @param array<mixed> $node
     */
    private static function count(array $node, Scope $scope): int
    {
        $items = self::evaluate(self::requireNode($node['source'] ?? null), $scope);
        return is_array($items) ? count(array_filter($items, static fn (mixed $x): bool => $x !== null)) : 0;
    }

    /**
     * @param array<mixed> $node
     */
    private static function sumAvg(bool $average, array $node, Scope $scope): mixed
    {
        $items = self::evaluate(self::requireNode($node['source'] ?? null), $scope);
        if (!is_array($items)) {
            return null;
        }
        try {
            $items = self::processQuantities($items);
        } catch (\UnexpectedValueException | \InvalidArgumentException) {
            return null;
        }
        if ($items === []) {
            return null;
        }
        if (self::allQuantities($items)) {
            $sum = 0;
            foreach ($items as $item) {
                $sum += $item instanceof Quantity ? $item->value : 0;
            }
            $first = $items[0] instanceof Quantity ? $items[0] : null;
            return new Quantity($average ? $sum / count($items) : $sum, $first?->unit);
        }
        $sum = self::numeric(array_shift($items));
        foreach ($items as $item) {
            $sum += self::numeric($item);
        }
        return $average ? fdiv($sum, count($items) + 1) : $sum;
    }

    /**
     * @param array<mixed> $node
     */
    private static function minMax(bool $max, array $node, Scope $scope): mixed
    {
        $list = self::evaluate(self::requireNode($node['source'] ?? null), $scope);
        if ($list === null) {
            return null;
        }
        if (!is_array($list)) {
            throw new \UnexpectedValueException('Min and Max need a list');
        }
        $items = array_values(array_filter($list, static fn (mixed $x): bool => $x !== null));
        try {
            self::processQuantities($list);
        } catch (\UnexpectedValueException | \InvalidArgumentException) {
            return null;
        }
        if ($items === []) {
            return null;
        }
        $best = $items[0];
        foreach ($items as $item) {
            $better = $max ? Comparison::greaterThan($item, $best) : Comparison::lessThan($item, $best);
            if ($better === true) {
                $best = $item;
            }
        }
        return $best;
    }

    /**
     * Quantities all converted to the first one's unit; an error for a
     * mix of quantities and other values.
     *
     * @param array<mixed> $values
     * @return list<mixed>
     */
    private static function processQuantities(array $values): array
    {
        $items = array_values(array_filter($values, static fn (mixed $x): bool => $x !== null));
        if ($items !== [] && self::allQuantities($items)) {
            $first = $items[0];
            return array_map(static fn (mixed $q): Quantity => $q instanceof Quantity && $first instanceof Quantity ? $q->convertUnit($first->unit) : throw new \UnexpectedValueException('Not a quantity'), $items);
        }
        foreach ($items as $item) {
            if ($item instanceof Quantity) {
                throw new \UnexpectedValueException('Cannot perform aggregate operations on mixed values of Quantities and non Quantities');
            }
        }
        return $items;
    }

    /**
     * @param list<mixed> $items
     */
    private static function allQuantities(array $items): bool
    {
        foreach ($items as $item) {
            if (!$item instanceof Quantity) {
                return false;
            }
        }
        return true;
    }

    // ---------------------------------------------------------------
    // Types

    /**
     * @param array<mixed> $node
     */
    private static function asType(array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        if (TypeMatcher::matches($arg, self::asSpecifier($node))) {
            return $arg;
        }
        if (self::truthy($node['strict'] ?? false)) {
            throw new \UnexpectedValueException('Cannot cast value');
        }
        return null;
    }

    /**
     * @param array<mixed> $node
     * @return array<mixed>|null
     */
    private static function asSpecifier(array $node): ?array
    {
        if (is_array($node['asTypeSpecifier'] ?? null)) {
            return $node['asTypeSpecifier'];
        }
        return is_string($node['asType'] ?? null) ? ['name' => $node['asType'], 'type' => 'NamedTypeSpecifier'] : null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function isType(array $node, Scope $scope): bool
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return false;
        }
        $spec = is_array($node['isTypeSpecifier'] ?? null)
            ? $node['isTypeSpecifier']
            : (is_string($node['isType'] ?? null) ? ['name' => $node['isType'], 'type' => 'NamedTypeSpecifier'] : null);
        if (!($arg instanceof QdmObject && $arg->isDataElement) && !TypeMatcher::isSystemType($spec)) {
            throw new \UnexpectedValueException('Patient Source does not support Is operation');
        }
        return TypeMatcher::matches($arg, $spec);
    }

    /**
     * @param array<mixed> $node
     */
    private static function toDate(array $node, Scope $scope): ?CqlDate
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        if ($arg instanceof CqlDateTime) {
            return $arg->getDate();
        }
        return CqlDate::parse(self::toCqlString($arg) ?? '');
    }

    /**
     * @param array<mixed> $node
     */
    private static function toDateTime(array $node, Scope $scope): ?CqlDateTime
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        if ($arg instanceof CqlDate) {
            $offset = $scope->library->executionDateTime->timezoneOffset;
            return $offset === null ? $arg->getDateTime() : $arg->getDateTime($offset);
        }
        return CqlDateTime::parse(self::toCqlString($arg) ?? '');
    }

    /**
     * @param array<mixed> $node
     */
    private static function toDecimal(array $node, Scope $scope): mixed
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        if ($arg instanceof Uncertainty) {
            return new Uncertainty(
                CqlMath::limitDecimalPrecision(JavaScript::parseFloat(self::toCqlString($arg->low) ?? '')),
                CqlMath::limitDecimalPrecision(JavaScript::parseFloat(self::toCqlString($arg->high) ?? '')),
            );
        }
        $decimal = CqlMath::limitDecimalPrecision(JavaScript::parseFloat(self::toCqlString($arg) ?? ''));
        return CqlMath::isValidDecimal($decimal) ? $decimal : null;
    }

    /**
     * @param array<mixed> $node
     */
    private static function toInteger(array $node, Scope $scope): ?int
    {
        $arg = self::arg($node, $scope);
        if (is_string($arg)) {
            $integer = self::parseInt($arg);
            return is_int($integer) && CqlMath::isValidInteger($integer) ? $integer : null;
        }
        if (is_bool($arg)) {
            return $arg ? 1 : 0;
        }
        return null;
    }

    private static function toQuantity(mixed $value): mixed
    {
        return match (true) {
            $value === null => null,
            is_int($value), is_float($value) => new Quantity($value, '1'),
            $value instanceof Ratio => $value->numerator->dividedBy($value->denominator),
            $value instanceof Uncertainty => new Uncertainty(self::toQuantity($value->low), self::toQuantity($value->high)),
            default => Quantity::parse(self::toCqlString($value) ?? ''),
        };
    }

    /**
     * @param array<mixed> $node
     */
    private static function toConcept(array $node, Scope $scope): ?Concept
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        return new Concept($arg instanceof Code ? [$arg] : [], $arg instanceof Code ? $arg->display : null);
    }

    /**
     * @param array<mixed> $node
     */
    private static function toBoolean(array $node, Scope $scope): ?bool
    {
        $arg = self::arg($node, $scope);
        if ($arg === null) {
            return null;
        }
        $text = strtolower(self::toCqlString($arg) ?? '');
        if (in_array($text, ['true', 't', 'yes', 'y', '1'], true)) {
            return true;
        }
        return in_array($text, ['false', 'f', 'no', 'n', '0'], true) ? false : null;
    }

    /** JavaScript's toString of a CQL value. */
    private static function toCqlString(mixed $value): ?string
    {
        return match (true) {
            $value === null => null,
            is_string($value) => $value,
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value), is_float($value) => JavaScript::numberToString($value),
            is_array($value) => implode(',', array_map(static fn (mixed $v): string => self::toCqlString($v) ?? '', $value)),
            $value instanceof \Stringable => (string) $value,
            default => '[object Object]',
        };
    }

    // ---------------------------------------------------------------
    // List helpers

    /**
     * @param array<mixed> $list
     * @return list<mixed>
     */
    public static function distinctList(array $list): array
    {
        $seen = [];
        $distinct = [];
        foreach ($list as $item) {
            $key = DistinctKey::of($item);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $distinct[] = $item;
            }
        }
        return $distinct;
    }

    /**
     * @param list<mixed> $list
     * @return list<mixed>
     */
    private static function removeDuplicateNulls(array $list): array
    {
        $result = [];
        $nullFound = false;
        foreach ($list as $item) {
            if ($item !== null) {
                $result[] = $item;
            } elseif (!$nullFound) {
                $result[] = null;
                $nullFound = true;
            }
        }
        return $result;
    }

    /**
     * @param array<mixed> $list
     */
    private static function listContains(array $list, mixed $item): bool
    {
        foreach ($list as $element) {
            if (Comparison::equals($element, $item) === true || ($element === null && $item === null)) {
                return true;
            }
        }
        return false;
    }

    // ---------------------------------------------------------------
    // Node and value helpers

    /**
     * @param array<mixed> $node
     * @return list<mixed>
     */
    private static function args(array $node, Scope $scope): array
    {
        return self::evaluateAll(self::operands($node), $scope);
    }

    /**
     * The single operand's value (cql-execution's arg), or the list of
     * operand values when there are several.
     *
     * @param array<mixed> $node
     */
    private static function arg(array $node, Scope $scope): mixed
    {
        $operand = $node['operand'] ?? null;
        if (!is_array($operand)) {
            return null;
        }
        if (array_is_list($operand)) {
            return self::evaluateAll(self::nodes($operand), $scope);
        }
        return self::evaluate($operand, $scope);
    }

    /**
     * @param array<mixed> $node
     * @return list<array<mixed>>
     */
    private static function operands(array $node): array
    {
        $operand = $node['operand'] ?? null;
        if (!is_array($operand)) {
            return [];
        }
        return array_is_list($operand) ? self::nodes($operand) : [$operand];
    }

    /**
     * @param list<array<mixed>> $nodes
     * @return list<mixed>
     */
    private static function evaluateAll(array $nodes, Scope $scope): array
    {
        $values = [];
        foreach ($nodes as $node) {
            $values[] = self::evaluate($node, $scope);
        }
        return $values;
    }

    /**
     * @return list<array<mixed>>
     */
    private static function nodes(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $nodes = [];
        foreach (array_is_list($value) ? $value : [$value] as $item) {
            if (is_array($item)) {
                $nodes[] = $item;
            }
        }
        return $nodes;
    }

    /**
     * @return array<mixed>|null
     */
    private static function node(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }

    /**
     * @return array<mixed>
     */
    private static function requireNode(mixed $value): array
    {
        return is_array($value) ? $value : throw new \UnexpectedValueException('Missing ELM expression');
    }

    private static function lookup(Scope $scope, string $name): mixed
    {
        return $scope->lookup($name)[1];
    }

    /**
     * @param array<mixed> $node
     */
    private static function name(array $node): string
    {
        return self::text($node['name'] ?? '');
    }

    /**
     * @param array<mixed> $node
     */
    private static function precision(array $node): ?Precision
    {
        $precision = $node['precision'] ?? null;
        return is_string($precision) ? Precision::fromElm($precision) : null;
    }

    /** JavaScript truthiness. */
    public static function truthy(mixed $value): bool
    {
        return !($value === null || $value === false || $value === 0 || $value === 0.0 || $value === '' || (is_float($value) && is_nan($value)));
    }

    private static function boolean(mixed $value): ?bool
    {
        return $value === null ? null : (is_bool($value) ? $value : self::truthy($value));
    }

    /**
     * @param list<mixed> $values
     * @return list<?bool>
     */
    private static function booleans(array $values): array
    {
        return array_map(static fn (mixed $v): ?bool => $v === null || is_bool($v) ? $v : self::truthy($v), $values);
    }

    private static function numeric(mixed $value): int|float
    {
        return match (true) {
            is_int($value), is_float($value) => $value,
            $value === null, $value === false => 0,
            $value === true => 1,
            is_string($value) => JavaScript::toNumber($value),
            default => NAN,
        };
    }

    /** JavaScript's `===`: numbers by value, anything else by identity. */
    private static function strictEquals(mixed $a, mixed $b): bool
    {
        if ((is_int($a) || is_float($a)) && (is_int($b) || is_float($b))) {
            return (float) $a === (float) $b;
        }
        return $a === $b;
    }

    /** JavaScript's `<`: numbers numerically, anything else by its string form. */
    private static function jsLess(mixed $a, mixed $b): bool
    {
        $aPrimitive = is_object($a) || is_array($a) ? self::toCqlString($a) : $a;
        $bPrimitive = is_object($b) || is_array($b) ? self::toCqlString($b) : $b;
        if (is_string($aPrimitive) && is_string($bPrimitive)) {
            return strcmp($aPrimitive, $bPrimitive) < 0;
        }
        return self::numeric($aPrimitive) < self::numeric($bPrimitive);
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : (is_int($value) || is_float($value) ? JavaScript::numberToString($value) : '');
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private static function numberOrNull(mixed $value): int|float|null
    {
        if (is_string($value)) {
            return JavaScript::parseFloat($value);
        }
        return is_int($value) || is_float($value) ? $value : null;
    }

    private static function intOrNull(mixed $value): ?int
    {
        if (is_float($value)) {
            return (int) $value;
        }
        return is_int($value) ? $value : null;
    }

    /** parseInt(s, 10): the leading integer, or NaN. */
    private static function parseInt(string $value): int|float
    {
        if (preg_match('/^\s*([+-]?\d+)/', $value, $m) !== 1) {
            return NAN;
        }
        $number = (float) $m[1];
        return abs($number) <= PHP_INT_MAX ? (int) $m[1] : $number;
    }

    /**
     * @param mixed $codes
     * @return list<Code>
     */
    private static function codeList(mixed $codes): array
    {
        return is_array($codes) ? array_values(array_filter($codes, static fn (mixed $c): bool => $c instanceof Code)) : [];
    }
}
