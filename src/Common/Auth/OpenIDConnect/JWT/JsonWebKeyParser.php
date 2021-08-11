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
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
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
        if (empty($rawToken))
        {
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
            'jti' => $refreshTokenData['refresh_token_id']
        );
        if ($refreshTokenData['expire_time'] < \time()) {
            $result['active'] = false;
            $result['status'] = 'expired';
        }
        return $result;
    }

    public function parseAccessToken($rawToken)
    {
        if (empty($rawToken))
        {
            throw new \InvalidArgumentException("Token cannot be empty");
        }
        // Attempt to parse and validate the JWT
        $token = (new Parser())->parse($rawToken);
        // defaults
        $result = array(
            'active' => true,
            'status' => 'active',
            'scope' => implode(" ", $token->getClaim('scopes')),
            'exp' => $token->claims()->get('exp'),
            'sub' => $token->claims()->get('sub'), // user_id
            'jti' => $token->claims()->get('jti'),
            'aud' => $token->claims()->get('aud')
        );
        try {
            if ($token->verify(new Sha256(), 'file://' . $this->publicKeyLocation) === false) {
                $result['active'] = false;
                $result['status'] = 'failed_verification';
            }
        } catch (Exception $exception) {
            $result['active'] = false;
            $result['status'] = 'invalid_signature';
        }
        // Ensure access token hasn't expired
        $data = new ValidationData();
        $data->setCurrentTime(\time());
        if ($token->validate($data) === false) {
            $result['active'] = false;
            $result['status'] = 'expired';
        }
        return $result;
    }

    public function getTokenHintFromToken($rawToken)
    {
        if (empty($rawToken))
        {
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