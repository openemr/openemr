<?php

/**
 * One library's evaluation for one patient, cql-execution's PatientContext:
 * its statement results (each evaluated once), the results recorded by
 * statement localId, and the contexts of the libraries it includes, by
 * alias.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Engine;

use OpenEMR\Cqm\Cql\Elm\ElmLibrary;
use OpenEMR\Cqm\Cql\Elm\ElmRepository;
use OpenEMR\Cqm\Cql\Qdm\QdmPatient;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;

final class LibraryContext
{
    /** @var array<string, mixed> statement results by name */
    private array $results = [];

    /** @var array<string, mixed> results by localId */
    private array $localIds = [];

    /** @var array<string, LibraryContext> */
    private array $included = [];

    /**
     * @param array<string, mixed> $parameters parameter values by name
     */
    public function __construct(
        public readonly ElmLibrary $library,
        public readonly ElmRepository $repository,
        public readonly QdmPatient $patient,
        public readonly CodeService $codeService,
        public readonly array $parameters,
        public readonly CqlDateTime $executionDateTime,
    ) {
    }

    public function scope(): Scope
    {
        return new Scope($this);
    }

    /**
     * The context of an included library, created on first use.
     */
    public function includedContext(string $alias): self
    {
        if (!isset($this->included[$alias])) {
            $library = $this->repository->included($this->library, $alias)
                ?? throw new \UnexpectedValueException("Library {$this->library->name} has no include $alias");
            $this->included[$alias] = new self(
                $library,
                $this->repository,
                $this->patient,
                $this->codeService,
                $this->parameters,
                $this->executionDateTime,
            );
        }
        return $this->included[$alias];
    }

    /**
     * @return array{bool, mixed} whether the statement has been evaluated, and its result
     */
    public function result(string $name): array
    {
        return array_key_exists($name, $this->results) ? [true, $this->results[$name]] : [false, null];
    }

    public function setResult(string $name, mixed $value): void
    {
        $this->results[$name] = $value;
    }

    /**
     * Records a result by localId unless one is already recorded that
     * counts as a result (cql-execution keeps the first non-empty one).
     */
    public function recordLocalId(string $localId, mixed $value): void
    {
        $existing = $this->localIds[$localId] ?? null;
        if ($existing === null || $existing === false || $existing === [] || $existing === '') {
            $this->localIds[$localId] = $value;
        }
    }

    /**
     * Results by localId of this library and of every library reached
     * through its includes, by library name: cql-execution's getAllLocalIds.
     *
     * @return array<string, array<string, mixed>>
     */
    public function allLocalIds(): array
    {
        $all = [$this->library->name => $this->localIds];
        foreach ($this->included as $context) {
            $context->mergeLocalIdsInto($all);
        }
        return $all;
    }

    /**
     * @param array<string, array<string, mixed>> $all
     */
    private function mergeLocalIdsInto(array &$all): void
    {
        $name = $this->library->name;
        if (isset($all[$name])) {
            foreach ($this->localIds as $localId => $value) {
                $existing = $all[$name][$localId] ?? null;
                if ($existing === null || $existing === false || $existing === [] || $existing === '') {
                    $all[$name][$localId] = $value;
                }
            }
        } else {
            $all[$name] = $this->localIds;
        }
        foreach ($this->included as $context) {
            $context->mergeLocalIdsInto($all);
        }
    }
}
