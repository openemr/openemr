<?php

/**
 * Tests the consistency of the standard API scope registry.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use PHPUnit\Framework\TestCase;

class ServerScopeListEntityTest extends TestCase
{
    /**
     * Every standard API resource offered in the v1 scope syntax must also be offered in the v2
     * syntax, otherwise a client that speaks only v2 has no way to request that resource at all.
     * Scope strings are only ever validated against this registry, so a resource missing from one
     * side fails silently at authorization time rather than anywhere a test would normally look.
     */
    public function testEveryV1ApiResourceHasAV2Counterpart(): void
    {
        $entity = new ServerScopeListEntity();

        $v1Resources = $this->extractResources($entity->apiScopes());
        $v2Resources = $this->extractResources($entity->getV2ApiScopes());

        $missing = array_values(array_diff($v1Resources, $v2Resources));

        $this->assertSame(
            [],
            $missing,
            'Standard API resources present in apiScopes() but absent from getV2ApiScopes(): '
                . implode(', ', $missing)
        );
    }

    /**
     * Reduces a list of scope strings such as "user/users.read" to their "<context>/<resource>"
     * key, dropping operation scopes (those whose permission segment starts with a $).
     *
     * The scope list accessors are untyped legacy signatures, so entries are narrowed here rather
     * than cast.
     *
     * @param array<array-key, mixed> $scopes
     * @return list<string>
     */
    private function extractResources(array $scopes): array
    {
        $resources = [];
        foreach ($scopes as $scope) {
            if (!is_string($scope)) {
                continue;
            }
            $separator = strrpos($scope, '.');
            if ($separator === false) {
                continue;
            }
            if (str_starts_with(substr($scope, $separator + 1), '$')) {
                continue; // operation scope, not a resource permission scope
            }
            $resources[] = substr($scope, 0, $separator);
        }

        return array_values(array_unique($resources));
    }
}
