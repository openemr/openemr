<?php

/**
 * JsonWebKeySet represents a signing key used for JWT signature verification.  It can take a JSON Web Key Set (JWKS)
 * as a string or as a URI.  If a URI is provided it will retrieve the JWKS and store them internally.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\JWT;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Lcobucci\JWT\Signer\Key;
use OpenEMR\Common\Logging\SystemLogger;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class JsonWebKeySet implements Key
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    private $jwks = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ClientInterface $httpClient, $jwks_uri = null, $jwks = null)
    {
        $this->logger = new SystemLogger();
        $this->setHttpClient($httpClient);

        $passphrase = '';
        if (empty($jwks_uri) && empty($jwks)) {
            throw new JWKValidatorException("jwks_uri or jwks string must be provided");
        }
        $content = $jwks;
        if (!empty($jwks_uri)) {
            // TODO: for optimization in the future we can cache these, SPEC says we should do this as a best practice
            // since access tokens can't be longer than 5 minutes, but since it's optional we'll leave it out for now.
            $content = $this->getJWKFromUri($jwks_uri);
            $this->logger->debug("Retrieved jwk content from jwks_uri", ['jwks_uri' => $jwks_uri]);
        }

        // grab the keys array from the content
        $jwks = json_decode($content);
        if (!property_exists($jwks, 'keys')) {
            throw new JWKValidatorException("Malformed jwks missing keys property");
        }
        $this->jwks = $jwks->keys;

        $this->content = $content;
        $this->passphrase = $passphrase;
    }
    /**
     * Returns a JWK that matches the given key id and algorithm in the JWK Set.
     * @param $kid The key id for the JWK we are attempting to retrieve
     * @param $alg The algorithm for the JWK we are attempting to retrieve
     * @return object
     */
    public function getJSONWebKey($kid, $alg)
    {
        $this->logger->debug("JsonWebKeySet::getJSONWebKey() Attempting to find web key for kid & alg", ['kid' => $kid, 'alg' => $alg]);
        foreach ($this->jwks as $key) {
            if ($key->kty === 'RSA') {
                if (!isset($kid) || $key->kid === $kid) {
                    return $key;
                }
            } else {
                if (isset($key->alg) && $key->alg === $alg && $key->kid === $kid) {
                    return $key;
                }
            }
        }
        $this->logger->debug("Failed to find jwk key");
        return null;
    }

    public function setHttpClient(?ClientInterface $client)
    {
        $this->httpClient = $client;
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    protected function getJWKFromUri($jwk_uri)
    {
        try {
            $request = new Request('GET', $jwk_uri);
            $body = $this->httpClient->sendRequest($request)->getBody();
            $json = $body->getContents();
            return $json;
        } catch (RequestException | ConnectException $exception) {
            throw new JWKValidatorException("failed to retrieve jwk contents from jwk_uri", 0, $exception);
        } catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller("Failed to retrieve jwk contents from jwk_uri and unknown error occurred", ['jwk_uri' => $jwk_uri]);
            throw new JWKValidatorException("failed to retrieve jwk contents from jwk_uri", 0, $exception);
        }
    }

    /** @return string */
    public function contents(): string
    {
        return $this->content;
    }

    /** @return string */
    public function passphrase(): string
    {
        return $this->passphrase;
    }
}
