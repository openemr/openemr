<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Client;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

use OpenEMR\FHIR\Encoding\SerializeFormatEnum;

class Response
{
    /**
     * HTTP request method.
     *
     * @var \OpenEMR\FHIR\Client\HTTPMethodEnum
     */
    public HTTPMethodEnum $method;

    /**
     * The serialized format used to encode the request, if applicable.
     *
     * @var \OpenEMR\FHIR\Encoding\SerializeFormatEnum
     */
    public SerializeFormatEnum $requestFormat;

    /**
     * HTTP response status code.
     *
     * @var int
     */
    public int $code;

    /**
     * HTTP response headers.
     *
     * @var \OpenEMR\FHIR\Client\ResponseHeaders
     */
    public ResponseHeaders $headers;

    /**
     * HTTP response body.
     *
     * @var string
     */
    public string $resp;

    /**
     * Client error.
     *
     * @var string
     */
    public string $err;

    /**
     * Client error number.
     *
     * @var int
     */
    public int $errno;

    public function __construct(HTTPMethodEnum $method,
                                /**
                                 * Request URL.
                                 */
                                public string $url,
                                SerializeFormatEnum $requestFormat)
    {
        $this->method = $method;
    }

    /**
     * Return the HTTP request method used.
     *
     * @return null|\OpenEMR\FHIR\Client\HTTPMethodEnum
     */
    public function getMethod(): null|HTTPMethodEnum
    {
        return $this->method ?? null;
    }

    /**
     * Return the full URL used.
     *
     * @return null|string
     */
    public function getURL(): null|string
    {
        return $this->url ?? null;
    }

    /**
     * Return the HTTP response code seen.
     *
     * @return null|int
     */
    public function getCode(): null|int
    {
        return $this->code ?? null;
    }

    /**
     * Return the HTTP response headers seen.
     *
     * @return null|\OpenEMR\FHIR\Client\ResponseHeaders
     */
    public function getHeaders(): null|ResponseHeaders
    {
        return $this->headers ?? null;
    }

    /**
     * Return the full response seen, if there was one.
     *
     * @return null|string
     */
    public function getResp(): null|string
    {
        return $this->resp ?? null;
    }

    /**
     * Client error message, if there was one.
     *
     * @return null|string
     */
    public function getErr(): null|string
    {
        return $this->err ?? null;
    }

    /**
     * Client error code, if there was one.
     *
     * @return null|int
     */
    public function getErrno(): null|int
    {
        return $this->errno ?? null;
    }

    /**
     * Attempts to extract the serialization format from the response Content-Type header.  Returns null if response
     * headers were not parsed, if the Content-Type header is not present or parseable.
     *
     * @return null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum
     */
    public function getResponseFormat(): null|SerializeFormatEnum
    {
        if (!isset($this->headers)) {
            return $this->requestFormat ?? null;
        }
        $ctHeaders = $this->headers->get('content-type');
        if ([] === $ctHeaders) {
            return $this->requestFormat ?? null;
        }
        foreach ($ctHeaders as $header) {
            $lower = strtolower($header);
            switch (true) {
                case str_contains($lower, 'application/json'):
                case str_contains($lower, 'application/fhir+json'):
                case str_contains($lower, 'application/json+fhir'):
                    return SerializeFormatEnum::JSON;

                case str_contains($lower, 'application/xml'):
                case str_contains($lower, 'application/fhir+xml'):
                case str_contains($lower, 'application/xml+fhir'):
                    return SerializeFormatEnum::XML;
            }
        }
        return $this->requestFormat ?? null;
    }
}
