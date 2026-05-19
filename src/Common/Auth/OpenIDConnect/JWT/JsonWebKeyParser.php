<?php

/**
 * JsonWebKeyParser.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\JWT;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use League\OAuth2\Server\CryptTrait;

class JsonWebKeyParser
{
    use CryptTrait;

    /**
     * @param non-empty-string $publicKeyLocation
     */
    public function __construct($oaEncryptionKey, private string $publicKeyLocation)
    {
        $this->setEncryptionKey($oaEncryptionKey);
    }

    public function parseRefreshToken($rawToken)
    {
        if (empty($rawToken)) {
            throw new \InvalidArgumentException("Token cannot be empty");
        }
        $refreshTokenData = null;
        $refreshToken = $this->decrypt($rawToken);
        $refreshTokenData = \json_decode($refreshToken, true);
        $result = [
            'active' => true,
            'status' => 'active',
            'scope' => $refreshTokenData['scopes'],
            'exp' => $refreshTokenData['expire_time'],
            'sub' => $refreshTokenData['user_id'],
            'jti' => $refreshTokenData['refresh_token_id'],
            'client_id' => $refreshTokenData['client_id'] ?? '' // should always be there since we use it in the renewal
        ];
        if ($refreshTokenData['expire_time'] < \time()) {
            $result['active'] = false;
            $result['status'] = 'expired';
        }
        return $result;
    }

    public function parseAccessToken($rawToken)
    {
        if (!is_string($rawToken) || $rawToken === '') {
            throw new \InvalidArgumentException("Token cannot be empty");
        }

        // Parse the JWT. No keys are needed for parsing; signature
        // validation is performed separately below.
        $token = (new Parser(new JoseEncoder()))->parse($rawToken);
        // defaults
        $result = [
            'active' => true,
            'status' => 'active',
            'scope' => implode(" ", $token->claims()->get('scopes')),
            'exp' => $token->claims()->get('exp'),
            'sub' => $token->claims()->get('sub'), // user_id
            'jti' => $token->claims()->get('jti'),
            'aud' => $token->claims()->get('aud'),
            'iss' => $token->claims()->get('iss'),
        ];

        // Attempt to validate the JWT
        $validator = new Validator();
        try {
            if ($validator->validate($token, (new SignedWith(new Sha256(), InMemory::file($this->publicKeyLocation)))) === false) {
                $result['active'] = false;
                $result['status'] = 'failed_verification';
            }
        } catch (\Throwable) {
            $result['active'] = false;
            $result['status'] = 'invalid_signature';
        }

        // Round-6 #3 (CWE-287). Enforce the full validity window —
        // not just exp. The pre-fix check used `isExpired()`, which
        // gates on `exp` only; a JWT with valid signature and a
        // future `nbf` (not-before) or implausible-future `iat`
        // (issued-at) would still report active:true here.
        // LooseValidAt enforces nbf, iat, and exp together, with a
        // 1-minute drift tolerance to match the
        // JWTClientAuthenticationService grant validator. On
        // failure, distinguish "not yet valid" (future nbf) from
        // "expired" (past exp) for the status field — RFC 7662
        // active=false in both cases but operators can tell which
        // gate fired.
        $now = new \DateTimeImmutable();
        $clock = new SystemClock(new \DateTimeZone(\date_default_timezone_get()));
        try {
            if (!$validator->validate($token, new LooseValidAt($clock, new \DateInterval('PT1M')))) {
                $result['active'] = false;
                $result['status'] = $token->isExpired($now) ? 'expired' : 'not_yet_valid';
            }
        } catch (\RuntimeException) {
            // RequiredConstraintsViolated / NoConstraintsGiven both
            // extend \RuntimeException — the narrowest catch that
            // covers lcobucci's validation throws without tripping
            // the project's forbiddenCatchType rule (\Throwable and
            // \Exception both include ErrorException). PHP-level
            // errors propagate to TokenIntrospectionRestController's
            // outer Throwable handler, which rewrites them to
            // active:false per RFC 7662.
            $result['active'] = false;
            $result['status'] = 'invalid';
        }

        return $result;
    }

    public function getTokenHintFromToken($rawToken)
    {
        if (empty($rawToken)) {
            throw new \InvalidArgumentException("Token cannot be empty");
        }
        // determine if access or refresh.
        $access_parts = explode(".", (string) $rawToken);
        $token_hint = count($access_parts) === 3 ? 'access_token' : 'refresh_token';
        return $token_hint;
    }
}
