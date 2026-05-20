<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\JWT\Validation;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;
use Lcobucci\JWT\Validation\ConstraintViolation;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\Validation\UniqueID;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use PHPUnit\Framework\TestCase;

final class UniqueIDTest extends TestCase
{
    public function testAssertLooksUpUsingClockNowNotTokenExp(): void
    {
        // Frozen "now" is well before the token's exp. The constraint must
        // ask the repository whether the jti has been seen using the clock
        // value (so the strict `jti_exp > ?` filter returns any still-valid
        // record), NOT the token's exp (which would equal the stored value
        // on a replay and miss the row).
        $now = new DateTimeImmutable('2026-05-05T10:00:00+00:00');
        $tokenExp = $now->modify('+1 hour');

        $token = $this->buildToken('jti-replay-1', $tokenExp, 'client-1');

        $repo = $this->createMock(JWTRepository::class);
        $repo->expects($this->once())
            ->method('getJwtGrantHistoryForJTI')
            ->with('jti-replay-1', $now->getTimestamp())
            ->willReturn([]);

        $constraint = new UniqueID($repo, new FrozenClock($now));
        $constraint->assert($token); // must not throw on first use
    }

    public function testAssertThrowsWhenRepositoryReturnsExistingRow(): void
    {
        // Same shape as the first test, but the repository simulates a
        // previously-stored row — i.e. a replay. Constraint must flag it.
        $now = new DateTimeImmutable('2026-05-05T10:00:00+00:00');
        $tokenExp = $now->modify('+1 hour');

        $token = $this->buildToken('jti-replay-2', $tokenExp, 'client-1');

        $repo = $this->createMock(JWTRepository::class);
        $repo->method('getJwtGrantHistoryForJTI')
            ->willReturn([
                ['jti' => 'jti-replay-2', 'jti_exp' => $tokenExp->format('Y-m-d H:i:s')],
            ]);

        $constraint = new UniqueID($repo, new FrozenClock($now));

        $this->expectException(ConstraintViolation::class);
        $this->expectExceptionMessage('jti claim has already been used');
        $constraint->assert($token);
    }

    public function testAssertThrowsWhenJtiClaimMissing(): void
    {
        $now = new DateTimeImmutable('2026-05-05T10:00:00+00:00');
        $tokenExp = $now->modify('+1 hour');

        // Build a token with no `jti` claim at all. The early guard short-
        // circuits before the repository is ever consulted.
        $token = $this->buildToken(null, $tokenExp, 'client-1');

        $repo = $this->createMock(JWTRepository::class);
        $repo->expects($this->never())->method('getJwtGrantHistoryForJTI');

        $constraint = new UniqueID($repo, new FrozenClock($now));

        $this->expectException(ConstraintViolation::class);
        $this->expectExceptionMessage('jti claim is required for JWT');
        $constraint->assert($token);
    }

    /**
     * Construct a Lcobucci Plain token with hand-built claim DataSets. We
     * skip the Configuration/Builder pipeline because UniqueID only reads
     * `jti`, `exp`, `iss` from $token->claims() — no signing, no header
     * inspection — so a synthetic Plain is the lightest fixture possible.
     *
     * @param non-empty-string|null $jti
     */
    private function buildToken(
        ?string $jti,
        DateTimeImmutable $exp,
        string $iss,
    ): Plain {
        $claims = ['exp' => $exp, 'iss' => $iss];
        if ($jti !== null) {
            $claims['jti'] = $jti;
        }

        return new Plain(
            new DataSet(['typ' => 'JWT', 'alg' => 'HS256'], 'encoded-headers'),
            new DataSet($claims, 'encoded-claims'),
            new Signature('hash-bytes', 'encoded-signature'),
        );
    }
}
