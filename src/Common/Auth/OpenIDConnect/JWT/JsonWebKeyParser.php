<?php

/**
 * JsonWebKeyParser.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\JWT;

use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use League\OAuth2\Server\CryptTrait;
use LogicException;

class JsonWebKeyParser
{
    use CryptTrait;

    private $publicKeyLocation;

    public function __construct($oaEncryptionKey, $publicKeyLocation)
    {
        $this->setEncryptionKey($oaEncryptionKey);
        $this->publicKeyLocation = $publicKeyLocation;
    }

    public function parseRefreshToken($rawToken)
    {
        if (empty($rawToken)) {
            throw new \InvalidArgumentException("Token cannot be empty");
        }
        $refreshTokenData = null;
        $refreshToken = $this->decrypt($rawToken);
        $refreshTokenData = \json_decode($refreshToken, true);
        $result = array(
            'active' => true,
            'status' => 'active',
            'scope' => $refreshTokenData['scopes'],
            'exp' => $refreshTokenData['expire_time'],
            'sub' => $refreshTokenData['user_id'],
            'jti' => $refreshTokenData['refresh_token_id'],
            'client_id' => $refreshTokenData['client_id'] ?? '' // should always be there since we use it in the renewal
        );
        if ($refreshTokenData['expire_time'] < \time()) {
            $result['active'] = false;
            $result['status'] = 'expired';
        }
        return $result;
    }

    public function parseAccessToken($rawToken)
    {
        if (empty($rawToken)) {
            throw new \InvalidArgumentException("Token cannot be empty");
        }

        // Create the jwtConfiguration object
        //  Just using object for parsing, so keys not needed
        //   (ie. not using forAsymmetricSigner and just using forUnsecuredSigner)
        $configuration = Configuration::forUnsecuredSigner();

        // Attempt to parse the JWT
        $token = $configuration->parser()->parse($rawToken);
        // defaults
        $result = array(
            'active' => true,
            'status' => 'active',
            'scope' => implode(" ", $token->claims()->get('scopes')),
            'exp' => $token->claims()->get('exp'),
            'sub' => $token->claims()->get('sub'), // user_id
            'jti' => $token->claims()->get('jti'),
            'aud' => $token->claims()->get('aud')
        );

        // Attempt to validate the JWT
        $validator = $configuration->validator();
        try {
            if ($validator->validate($token, (new SignedWith(new Sha256(), InMemory::file($this->publicKeyLocation)))) === false) {
                $result['active'] = false;
                $result['status'] = 'failed_verification';
            }
        } catch (Exception $exception) {
            $result['active'] = false;
            $result['status'] = 'invalid_signature';
        }

        // Ensure access token hasn't expired
        $now   = new \DateTimeImmutable();
        if ($token->isExpired($now) === true) {
            $result['active'] = false;
            $result['status'] = 'expired';
        }

        return $result;
    }

    public function getTokenHintFromToken($rawToken)
    {
        if (empty($rawToken)) {
            throw new \InvalidArgumentException("Token cannot be empty");
        }
        // determine if access or refresh.
        $access_parts = explode(".", $rawToken);
        if (count($access_parts) === 3) {
            $token_hint = 'access_token';
        } else {
            $token_hint = 'refresh_token';
        }
        return $token_hint;
    }
}
