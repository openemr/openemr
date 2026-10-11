<?php

/**
 * A name scope of the CQL evaluation, cql-execution's child Context: query
 * aliases, let and function operands, and, for a sort or aggregate, a
 * value whose own properties are in scope. Lookups walk out to the
 * library context.
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
use OpenEMR\Cqm\Cql\Types\Tuple;

final class Scope
{
    /** @var array<string, mixed> names set in this scope */
    private array $names = [];

    /**
     * @param mixed $value a value whose properties are in scope (cql-execution's context_values object)
     */
    public function __construct(
        public readonly LibraryContext $library,
        private readonly ?Scope $parent = null,
        private readonly mixed $value = null,
    ) {
    }

    public function child(mixed $value = null): self
    {
        return new self($this->library, $this, $value);
    }

    public function set(string $name, mixed $value): void
    {
        $this->names[$name] = $value;
    }

    /**
     * The names set in this scope, in the order they were set: a query
     * row of several sources, which cql-execution returns as an object.
     */
    public function namesAsTuple(): Tuple
    {
        return new Tuple($this->names);
    }

    /**
     * A name in scope; found is false when no scope out to the library
     * has it.
     *
     * @return array{bool, mixed} found, value
     */
    public function lookup(string $name): array
    {
        if (array_key_exists($name, $this->names)) {
            return [true, $this->names[$name]];
        }
        if ($this->value !== null) {
            if ($name === '$this') {
                return [true, $this->value];
            }
            $own = self::ownProperty($this->value, $name);
            if ($own[0]) {
                return $own;
            }
        }
        return $this->parent?->lookup($name) ?? [false, null];
    }

    /**
     * A property the value itself has (JavaScript's obj[name] !== undefined).
     *
     * @return array{bool, mixed}
     */
    public static function ownProperty(mixed $value, string $name): array
    {
        if ($value instanceof QdmObject) {
            return $value->has($name) ? [true, $value->get($name)] : [false, null];
        }
        if ($value instanceof Tuple) {
            return array_key_exists($name, $value->elements) ? [true, $value->elements[$name]] : [false, null];
        }
        if (is_object($value)) {
            $properties = get_object_vars($value);
            return array_key_exists($name, $properties) ? [true, $properties[$name]] : [false, null];
        }
        return [false, null];
    }
}
