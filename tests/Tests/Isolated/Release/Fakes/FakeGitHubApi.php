<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release\Fakes;

use OpenEMR\Release\GitHubApi;

/**
 * In-memory stand-in for the gh CLI wrapper. Records the endpoints it was
 * asked about so tests can assert that a resolver skipped a network call
 * entirely, not merely that it ignored the answer.
 */
final class FakeGitHubApi extends GitHubApi
{
    /** @var list<string> */
    public array $paginated = [];

    /** @var list<string> */
    public array $existenceChecks = [];

    /**
     * @param list<array<string, mixed>> $items       returned by every paginate() call
     * @param list<string>               $existing    endpoints exists() answers true for
     */
    public function __construct(
        private readonly array $items = [],
        private readonly array $existing = [],
    ) {
        parent::__construct('fake/repo');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function paginate(string $endpoint): array
    {
        $this->paginated[] = $endpoint;

        return $this->items;
    }

    public function exists(string $endpoint): bool
    {
        $this->existenceChecks[] = $endpoint;

        return in_array($endpoint, $this->existing, true);
    }
}
