<?php

/**
 * UniqueID.php
 *
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect\JWT\Validation;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use Psr\Clock\ClockInterface;

class UniqueID implements Constraint
{
    public function __construct(
        private readonly JWTRepository $jwtRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    /** @throws ConstraintViolation */
    public function assert(Token $token): void
    {
        $jti = $token->claims()->get('jti');
        $exp = $token->claims()->get('exp');
        $iss = $token->claims()->get('iss');

        if (empty($jti)) {
            throw new ConstraintViolation("jti claim is required for JWT");
        }

        // Look up by current clock time, NOT the token's own exp. The repo
        // filter is `jti_exp > FROM_UNIXTIME(?)` and the stored value equals
        // the token's exp, so passing the token's exp here makes the strict
        // `>` comparison false on a replay (stored == supplied) and the
        // existing row is missed. Passing now() instead asks "is there any
        // record for this jti whose validity window is still open" — the
        // semantics OidcTokenValidator already uses for the same table.
        $nowTimestamp = $this->clock->now()->getTimestamp();
        $existingJWT = $this->jwtRepository->getJwtGrantHistoryForJTI($jti, $nowTimestamp);
        if (!empty($existingJWT)) {
            ServiceContainer::getLogger()->emergency(
                static::class . "->assert() Attempted duplicate usage of JWT token.  This could be a replay attack",
                ['clientId' => $iss, 'exp' => $exp, 'jti' => $jti]
            );
            throw new ConstraintViolation("jti claim has already been used");
        }
    }
}
