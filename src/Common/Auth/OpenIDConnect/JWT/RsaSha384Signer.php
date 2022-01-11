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
        if (!class_exists('\phpseclib\Crypt\RSA') && !class_exists('Crypt_RSA')) {
            throw new JWKValidatorException('Crypt_RSA support unavailable.');
        }

        if ($key instanceof JsonWebKeySet) {
            $kid = $this->headers['kid'] ?? null;
            $this->logger->debug("RsaSha384Signer->verify() attempting to retrieve jwk");
            $jwk = $key->getJSONWebKey($kid, $this->algorithmId());
        } else {
            $key = $key instanceof Key ? $key->contents() : $key;
            try {
                $jwk = json_decode($key);
            } catch (\Exception $exception) {
                throw new JWKValidatorException("failed to decode contents of JWKS from key");
            }
        }

        if (
            empty($jwk)
            || !(property_exists($jwk, 'n') && property_exists($jwk, 'e'))
        ) {
            throw new JWKValidatorException('Malformed key object');
        }

        /* We already have base64url-encoded data, so re-encode it as
           regular base64 and use the XML key format for simplicity.
        */
        $public_key_xml = "<RSAKeyValue>\r\n" .
            '  <Modulus>' . $this->b64url2b64($jwk->n) . "</Modulus>\r\n" .
            '  <Exponent>' . $this->b64url2b64($jwk->e) . "</Exponent>\r\n" .
            '</RSAKeyValue>';
        if (class_exists('Crypt_RSA', false)) {
            $rsa = new Crypt_RSA();
            $rsa->setHash(self::CRYPT_ALGORITHM);
            $rsa->loadKey($public_key_xml, Crypt_RSA::PUBLIC_FORMAT_XML);
            $rsa->signatureMode = Crypt_RSA::SIGNATURE_PKCS1;
        } else {
            $rsa = new \phpseclib\Crypt\RSA();
            $rsa->setHash(self::CRYPT_ALGORITHM);
            $rsa->loadKey($public_key_xml, \phpseclib\Crypt\RSA::PUBLIC_FORMAT_XML);
            $rsa->signatureMode = \phpseclib\Crypt\RSA::SIGNATURE_PKCS1;
        }
        return $rsa->verify($payload, $expected);
    }

    /**
     * Per RFC4648, "base64 encoding with URL-safe and filename-safe
     * alphabet".  This just replaces characters 62 and 63.  None of the
     * reference implementations seem to restore the padding if necessary,
     * but we'll do it anyway.
     * @param string $base64url
     * @return string
     */
    private function b64url2b64($base64url)
    {
        // "Shouldn't" be necessary, but why not
        $padding = strlen($base64url) % 4;
        if ($padding > 0) {
            $base64url .= str_repeat('=', 4 - $padding);
        }
        return strtr($base64url, '-_', '+/');
    }
}
