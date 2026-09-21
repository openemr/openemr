<?php

/**
 * Exercises exact OIDC identity binding against the installed schema.
 *
 * @package OpenEMR
 * @author Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use DateTimeImmutable;
use OpenEMR\Common\Auth\OidcRp\OidcIdentityRepository;
use OpenEMR\Common\Auth\OidcRp\OidcIdTokenClaims;
use OpenEMR\Common\Auth\OidcRp\OidcRpException;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class OidcIdentityRepositoryTest extends TestCase
{
    private OidcIdentityRepository $repository;
    private string $issuer;

    protected function setUp(): void
    {
        // @phpstan-ignore openemr.deprecatedSqlFunction (Always roll back fixtures in tearDown.)
        QueryUtils::startTransaction();
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn(new DateTimeImmutable('2026-01-15 12:00:00'));
        $this->repository = new OidcIdentityRepository($clock);
        $this->issuer = 'https://idp.example/' . bin2hex(random_bytes(8));
    }

    protected function tearDown(): void
    {
        // @phpstan-ignore openemr.deprecatedSqlFunction (A test transaction must never commit.)
        QueryUtils::rollbackTransaction();
    }

    public function testSubjectsAndIssuersAreDistinctIncludingCaseAndTrailingSpaces(): void
    {
        foreach ([[$this->issuer, 'User'], [$this->issuer, 'user'], [$this->issuer, 'user '], [$this->issuer . '/Realm', 'user'], [$this->issuer . '/realm', 'user']] as $index => [$issuer, $subject]) {
            $this->repository->bind(900000 + $index, $this->claims($issuer, $subject));
        }
        foreach ([[$this->issuer, 'User'], [$this->issuer, 'user'], [$this->issuer, 'user '], [$this->issuer . '/Realm', 'user'], [$this->issuer . '/realm', 'user']] as $index => [$issuer, $subject]) {
            self::assertSame(900000 + $index, $this->repository->findUserId($issuer, $subject));
        }
        self::assertNull($this->repository->findUserId($this->issuer, 'USER'));
    }

    public function testAnExistingIdentityCannotBeReboundToAnotherUser(): void
    {
        $claims = $this->claims($this->issuer, 'bound-user');
        $this->repository->bind(900000, $claims);
        $this->expectException(OidcRpException::class);
        $this->repository->bind(900001, $claims);
    }

    public function testRepeatedLoginUpdatesOnlyTheExactIdentity(): void
    {
        $this->repository->bind(900000, $this->claims($this->issuer, 'User'));
        $this->repository->bind(900001, $this->claims($this->issuer, 'user'));
        $this->repository->bind(900000, $this->claims($this->issuer, 'User', 'changed@example.com'));
        $rows = QueryUtils::fetchRecords(
            'SELECT subject, email FROM oidc_external_identity WHERE issuer = ? ORDER BY user_id',
            [$this->issuer]
        );
        self::assertSame('changed@example.com', $rows[0]['email']);
        self::assertSame('original@example.com', $rows[1]['email']);
    }

    private function claims(string $issuer, string $subject, string $email = 'original@example.com'): OidcIdTokenClaims
    {
        return new OidcIdTokenClaims($issuer, $subject, $email, true, 'test-user', '', '', '', []);
    }
}
