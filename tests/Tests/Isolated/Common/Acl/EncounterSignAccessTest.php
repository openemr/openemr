<?php

/**
 * Isolated tests for the rule that decides who may sign an encounter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Acl;

use OpenEMR\Common\Acl\EncounterSignAccess;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EncounterSignAccessTest extends TestCase
{
    /**
     * @return array<string, array{bool, bool, mixed, mixed, mixed, mixed, mixed, bool}>
     */
    public static function cases(): array
    {
        // authAny, authOwn, encounterId, creator, providerId, authUser, authUserId, expected
        return [
            'authorize any signs any encounter' => [true, false, '12', 'other', 99, 'alice', 5, true],
            'authorize any needs no encounter lookup' => [true, true, 'junk', null, null, 'alice', 5, true],
            'no authorization at all is refused' => [false, false, '12', 'alice', 5, 'alice', 5, false],
            'own encounter created by the user' => [false, true, '12', 'alice', 99, 'alice', 5, true],
            'own encounter where the user is the provider' => [false, true, '12', 'bob', 5, 'alice', 5, true],
            'provider id arrives as a string' => [false, true, '12', 'bob', '5', 'alice', '5', true],
            'someone else\'s encounter is refused' => [false, true, '12', 'bob', 99, 'alice', 5, false],
            'encounter number that is not an integer is refused' => [false, true, 'abc', 'alice', 5, 'alice', 5, false],
            'empty encounter number is refused' => [false, true, '', 'alice', 5, 'alice', 5, false],
            'an encounter with no creator or provider is refused' => [false, true, '12', null, null, 'alice', 5, false],
            'an empty creator never matches an empty user name' => [false, true, '12', '', 0, '', 0, false],
            'provider id 0 never matches user id 0' => [false, true, '12', 'bob', 0, 'alice', 0, false],
            'a non-string creator is not trusted' => [false, true, '12', 5, null, 5, 7, false],
        ];
    }

    #[DataProvider('cases')]
    public function testDecision(
        bool $authAny,
        bool $authOwn,
        mixed $encounterId,
        mixed $creator,
        mixed $providerId,
        mixed $authUser,
        mixed $authUserId,
        bool $expected
    ): void {
        $this->assertSame(
            $expected,
            EncounterSignAccess::allows($authAny, $authOwn, $encounterId, $creator, $providerId, $authUser, $authUserId)
        );
    }
}
