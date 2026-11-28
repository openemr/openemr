<?php

/**
 * Typed, path-aware access to one map (YAML mapping) of a seed file.
 *
 * Every failure is a SeedFileException naming the full path, e.g.
 * `rules.telecom[2].percent: required, must be a whole number`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class MapReader
{
    /**
     * @param array<string, mixed> $values
     */
    private function __construct(
        private array $values,
        public string $path,
    ) {
    }

    public static function of(mixed $value, string $path): self
    {
        if (!is_array($value) || ($value !== [] && array_is_list($value))) {
            throw SeedFileException::at($path === '' ? '(file)' : $path, 'expected a map of keys and values');
        }
        $map = [];
        foreach ($value as $key => $item) {
            if (!is_string($key)) {
                throw SeedFileException::at($path, 'keys must be names, found ' . $key);
            }
            $map[$key] = $item;
        }
        return new self($map, $path);
    }

    public function pathOf(string $key): string
    {
        return $this->path === '' ? $key : $this->path . '.' . $key;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values) && $this->values[$key] !== null;
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->values);
    }

    /**
     * @param list<string> $allowed
     */
    public function rejectUnknownKeys(array $allowed): void
    {
        $unknown = array_diff(array_keys($this->values), $allowed);
        if ($unknown !== []) {
            throw SeedFileException::at($this->path === '' ? '(file)' : $this->path, 'unknown key(s): ' . implode(', ', $unknown));
        }
    }

    public function raw(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function map(string $key): self
    {
        return self::of($this->values[$key] ?? null, $this->pathOf($key));
    }

    public function optionalMap(string $key): ?self
    {
        return $this->has($key) ? $this->map($key) : null;
    }

    /**
     * Each item of a list of maps.
     *
     * @return list<self>
     */
    public function mapList(string $key): array
    {
        return array_map(
            fn (int $index, mixed $item): self => self::of($item, $this->pathOf($key) . '[' . $index . ']'),
            array_keys($this->list($key)),
            $this->list($key),
        );
    }

    /**
     * @return list<self>
     */
    public function optionalMapList(string $key): array
    {
        return $this->has($key) ? $this->mapList($key) : [];
    }

    /**
     * @return list<mixed>
     */
    public function list(string $key): array
    {
        $value = $this->values[$key] ?? null;
        if (!is_array($value) || !array_is_list($value)) {
            throw SeedFileException::at($this->pathOf($key), 'required, must be a list');
        }
        return $value;
    }

    public function string(string $key): string
    {
        $value = $this->values[$key] ?? null;
        if (is_int($value)) {
            // YAML reads 413 or 01060 without quotes as a number.
            $value = (string) $value;
        }
        if (!is_string($value) || trim($value) === '') {
            throw SeedFileException::at($this->pathOf($key), 'required, must be text');
        }
        return trim($value);
    }

    public function optionalString(string $key): ?string
    {
        return $this->has($key) ? $this->string($key) : null;
    }

    /**
     * A single text value, or a list of them (one is picked per use).
     *
     * @return non-empty-list<string>
     */
    public function stringOrList(string $key): array
    {
        $value = $this->values[$key] ?? null;
        if (is_string($value) && trim($value) !== '') {
            return [trim($value)];
        }
        if (is_array($value) && array_is_list($value) && $value !== []) {
            $strings = [];
            foreach ($value as $index => $item) {
                if (!is_string($item) || trim($item) === '') {
                    throw SeedFileException::at($this->pathOf($key) . '[' . $index . ']', 'must be text');
                }
                $strings[] = trim($item);
            }
            return $strings;
        }
        throw SeedFileException::at($this->pathOf($key), 'required, must be text or a list of text');
    }

    /**
     * @return list<string>
     */
    public function stringList(string $key): array
    {
        return $this->has($key) ? $this->stringOrList($key) : [];
    }

    public function int(string $key): int
    {
        $value = $this->values[$key] ?? null;
        if (!is_int($value)) {
            throw SeedFileException::at($this->pathOf($key), 'required, must be a whole number');
        }
        return $value;
    }

    public function optionalInt(string $key): ?int
    {
        return $this->has($key) ? $this->int($key) : null;
    }

    public function bool(string $key): bool
    {
        $value = $this->values[$key] ?? null;
        if (!is_bool($value)) {
            throw SeedFileException::at($this->pathOf($key), 'required, must be true or false');
        }
        return $value;
    }

    public function optionalBool(string $key, bool $default = false): bool
    {
        return $this->has($key) ? $this->bool($key) : $default;
    }

    /**
     * A chance from 0 to 100.
     */
    public function percent(string $key): int
    {
        $value = $this->int($key);
        if ($value < 0 || $value > 100) {
            throw SeedFileException::at($this->pathOf($key), 'must be from 0 to 100');
        }
        return $value;
    }

    /**
     * Accepts a YAML date (2026-11-19) or the same date in quotes.
     */
    public function date(string $key): \DateTimeImmutable
    {
        $value = $this->values[$key] ?? null;
        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value)->setTime(0, 0);
        }
        if (is_string($value)) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('UTC'));
            if ($date !== false && $date->format('Y-m-d') === $value) {
                return $date;
            }
        }
        throw SeedFileException::at($this->pathOf($key), 'required, must be a date like 2026-11-19');
    }
}
