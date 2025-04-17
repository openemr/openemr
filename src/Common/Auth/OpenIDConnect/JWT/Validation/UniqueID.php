<?php

/**
 * UniqueID.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\JWT\Validation;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Common\Logging\SystemLogger;

class UniqueID implements Constraint
{
    /**
     * @var JWTRepository
     */
    private $jwtRepository;

    public function __construct(JWTRepository $jwtRepository)
    {
        $this->jwtRepository = $jwtRepository;
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
        $expCheck = null;
        if ($exp instanceof \DateTimeInterface) {
            $expCheck = $exp->getTimestamp();
        }
        $existingJWT = $this->jwtRepository->getJwtGrantHistoryForJTI($jti, $expCheck);
        if (!empty($existingJWT)) {
            (new SystemLogger())->emergency(
                get_class($this) . "->assert() Attempted duplicate usage of JWT token.  This could be a replay attack",
                ['clientId' => $iss, 'exp' => $exp, 'jti' => $jti]
            );
            throw new ConstraintViolation("jti claim has already been used");
        }
    }
}
