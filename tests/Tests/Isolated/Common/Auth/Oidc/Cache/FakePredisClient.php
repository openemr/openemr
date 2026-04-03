<?php

/**
 * In-memory fake Predis client for testing RedisCache without a Redis server.
 *
 * Implements the subset of Redis commands used by RedisCache via __call().
 * Type annotations are intentionally loose to match Predis\ClientInterface's
 * untyped __call() dispatch.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Cache;

use Predis\ClientInterface;
use Predis\Command\CommandInterface;
use Predis\Configuration\OptionsInterface;
use Predis\Connection\ConnectionInterface;

/**
 * @phpstan-type StoreArray array<string, string>
 * @phpstan-type TtlArray array<string, int>
 */
final class FakePredisClient implements ClientInterface
{
    /** @var array<string, string> */
    private array $store = [];

    /** @var array<string, int> */
    private array $ttls = [];

    public function getCommandFactory(): never
    {
        throw new \BadMethodCallException('Not implemented in fake');
    }

    public function getOptions(): OptionsInterface
    {
        throw new \BadMethodCallException('Not implemented in fake');
    }

    public function connect(): void
    {
    }

    public function disconnect(): void
    {
    }

    public function getConnection(): ConnectionInterface
    {
        throw new \BadMethodCallException('Not implemented in fake');
    }

    /** @phpstan-ignore missingType.iterableValue (Predis interface is untyped) */
    public function createCommand($method, $arguments = []): CommandInterface
    {
        throw new \BadMethodCallException('Not implemented in fake');
    }

    public function executeCommand(CommandInterface $command): mixed
    {
        throw new \BadMethodCallException('Not implemented in fake');
    }

    /** @phpstan-ignore missingType.iterableValue (Predis interface uses bare array) */
    public function __call($commandID, $arguments): mixed
    {
        $cmd = strtolower((string) $commandID);
        /** @var list<mixed> $args */
        $args = array_values((array) $arguments);
        $a0 = $args[0] ?? null;
        $a1 = $args[1] ?? null;
        $a2 = $args[2] ?? null;

        return match ($cmd) {
            'get' => is_string($a0) ? ($this->store[$a0] ?? null) : null,
            'set' => $this->doSet(is_string($a0) ? $a0 : '', is_string($a1) ? $a1 : ''),
            'setex' => $this->doSetex(is_string($a0) ? $a0 : '', is_int($a1) ? $a1 : 0, is_string($a2) ? $a2 : ''),
            'del' => $this->doDel(is_array($a0) ? array_values(array_filter($a0, 'is_string')) : []),
            'exists' => is_string($a0) && isset($this->store[$a0]) ? 1 : 0,
            'scan' => $this->doScan(is_string($a0) ? $a0 : '0', is_array($a1) ? $a1 : []),
            default => throw new \BadMethodCallException("Unsupported command: {$cmd}"),
        };
    }

    // -- Public typed accessors for test assertions --

    public function get(string $key): ?string
    {
        return $this->store[$key] ?? null;
    }

    public function set(string $key, string $value): void
    {
        $this->store[$key] = $value;
        unset($this->ttls[$key]);
    }

    public function setex(string $key, int $seconds, string $value): void
    {
        $this->store[$key] = $value;
        $this->ttls[$key] = $seconds;
    }

    public function getTtl(string $key): ?int
    {
        return $this->ttls[$key] ?? null;
    }

    // -- Private dispatch targets (called from __call with cast values) --

    private function doSet(string $key, string $value): void
    {
        $this->store[$key] = $value;
        unset($this->ttls[$key]);
    }

    private function doSetex(string $key, int $seconds, string $value): void
    {
        $this->store[$key] = $value;
        $this->ttls[$key] = $seconds;
    }

    /**
     * @param array<int|string, string> $keys
     */
    private function doDel(array $keys): int
    {
        $count = 0;
        foreach ($keys as $key) {
            if (isset($this->store[$key])) {
                unset($this->store[$key], $this->ttls[$key]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param array<mixed> $options
     * @return array{0: string, 1: list<string>}
     */
    private function doScan(string $cursor, array $options): array
    {
        $pattern = isset($options['MATCH']) && is_string($options['MATCH']) ? $options['MATCH'] : '*';
        $regex = '/^' . str_replace('\*', '.*', str_replace('\?', '.', preg_quote($pattern, '/'))) . '$/';

        $matched = [];
        foreach (array_keys($this->store) as $key) {
            if (preg_match($regex, $key) === 1) {
                $matched[] = $key;
            }
        }

        return ['0', $matched];
    }
}
