<?php

/**
 * RsaSha384Signer handles the JWT signature verification for SMART Bulk FHIR OpenID-Connect
 * validation.  Signing algorithm was modified from the jumbojett/OpenID-Connect-PHP project and is licensed under the original
 * Apache2 license of the originating code.
 * @see https://github.com/jumbojett/OpenID-Connect-PHP
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author  Michael Jett <mjett@mitre.org>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright  MITRE 2020
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\JWT;

use InvalidArgumentException;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Utils\HttpUtils;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use Psr\Log\LoggerInterface;

class RsaSha384Signer implements Signer
{
    const ALGORITHM_ID = 'RS384';

    const CRYPT_ALGORITHM = 'sha384';

    private $headers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->headers = [];
    }

    /**
     * Returns the algorithm id
     *
     * @return string
     */
    public function algorithmId(): string
    {
        return self::ALGORITHM_ID;
    }

    /**
     * Apply changes on headers according with algorithm
     *
     * @param array $headers
     */
    public function modifyHeader(array &$headers)
    {
        $headers['alg'] = $this->algorithmId();
        $this->headers = $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function sign($payload, $key): string
    {
        // we only handle signature verification not signature creation.
        throw new \BadMethodCallException("This class can only be used for signature verification not signing");
    }

    /**
     * Returns if the expected hash matches with the data and key
     *
     * @param string $expected
     * @param string $payload
     * @param Key|string $key
     *
     * @return boolean
     *
     * @throws InvalidArgumentException When given key is invalid
     */
    public function verify($expected, $payload, $key): bool
    {

        $this->logger->debug("RsaSha384Signer->verify() beginning jwt verification");

        if ($key instanceof JsonWebKeySet) {
            $kid = $this->headers['kid'] ?? null;
            $this->logger->debug("RsaSha384Signer->verify() attempting to retrieve jwk", ['kid' => $kid]);
            $jwk = $key->getJSONWebKey($kid, $this->algorithmId());
        } else {
            $key = $key instanceof Key ? $key->contents() : $key;
            try {
                $jwk = json_decode($key);
            } catch (\Throwable) {
                throw new JWKValidatorException("failed to decode contents of JWKS from key");
            }
        }

        if (
            empty($jwk)
            || !(property_exists($jwk, 'n') && property_exists($jwk, 'e'))
        ) {
            throw new JWKValidatorException('Malformed key object');
        }

        // Re-encode from base64url to standard base64 for the XML key format.
        $modulus = base64_encode(HttpUtils::base64url_decode($jwk->n));
        $exponent = base64_encode(HttpUtils::base64url_decode($jwk->e));
        $public_key_xml = <<<XML
        <RSAKeyValue>
          <Modulus>{$modulus}</Modulus>
          <Exponent>{$exponent}</Exponent>
        </RSAKeyValue>
        XML;
        $rsa = PublicKeyLoader::load($public_key_xml)->withPadding(RSA::SIGNATURE_PKCS1)->withHash(self::CRYPT_ALGORITHM);

        return $rsa->verify($payload, $expected);
    }
}
