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
use OpenEMR\FHIR\FHIRVersion;
use OpenEMR\FHIR\Types\ResourceTypeInterface;

/**
 * Class Client
 *
 * Basic implementation of the ClientInterface interface.
 */
class Client implements ClientInterface
{
    private const _PARAM_FORMAT = '_format';
    private const _PARAM_SORT = '_sort';
    private const _PARAM_COUNT = '_count';

    private const _BASE_CURL_OPTS = [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'php-fhir client (build: April 15th, 2026 16:02+0000;)',
    ];

    protected Config $_config;

    /**
     * Client Constructor
     *
     * @param string|\OpenEMR\FHIR\Client\Config $config Fully qualified address of FHIR server, or configuration object.
     */
    public function __construct(string|Config $config)
    {
        if (is_string($config)) {
            $config = new Config(address: $config);
        }
        $this->_config = $config;
    }

    public function getConfig(): Config
    {
        return $this->_config;
    }

    /**
     * @param \OpenEMR\FHIR\Client\Request $request
     * @return \OpenEMR\FHIR\Client\Response
     */
    public function exec(Request $request): Response
    {
        $queryParams = array_merge($this->_config->getDefaultQueryParams(), $request->queryParams ?? []);
        $format = $request->format ?? $this->_config->getDefaultFormat();
        $parseResponseHeaders = match(true) {
            isset($request->parseResponseHeaders) => $request->parseResponseHeaders,
            default => $this->_config->getParseResponseHeaders(),
        };

        $queryParams[self::_PARAM_FORMAT] = $format->value;
        if (isset($request->sort)) {
            $queryParams[self::_PARAM_SORT] = $request->sort;
        }
        if (isset($request->count)) {
            $queryParams[self::_PARAM_COUNT] = $request->count;
        }

        $url = "{$this->_config->getAddress()}{$request->path}?" . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

        $rc = new Response($request->method, $url, $format);

        $curlOpts = self::_BASE_CURL_OPTS + array_merge($this->_config->getCurlOpts(), $request->options ?? []);

        if ($parseResponseHeaders) {
            $rc->headers = new ResponseHeaders();
            $curlOpts[CURLOPT_HEADER] = 1;
            $curlOpts[CURLOPT_HEADERFUNCTION] = function($ch, string $line) use (&$rc): int {
                    return $rc->headers->addLine($line);
            };
        }

        if (!isset($curlOpts[CURLOPT_HTTPHEADER])) {
            $curlOpts[CURLOPT_HTTPHEADER] = [];
        }

        if (HTTPMethodEnum::GET !== $request->method) {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = $request->method->value;
            $curlOpts[CURLOPT_HTTPHEADER][] = "X-HTTP-Method-Override: {$request->method->value}";
        }

        $curlOpts[CURLOPT_HTTPHEADER][] = $this->_buildAcceptHeader($request, $format);

        if (isset($request->resource)) {
            $curlOpts[CURLOPT_HTTPHEADER][] = $this->_buildContentTypeHeader($request, $format);
        }

        $ch = curl_init($url);
        if (!curl_setopt_array($ch, $curlOpts)) {
            throw new \DomainException(sprintf(
                'curl_setopt_array returned false for "%s" with options: %s',
                $url,
                var_export($curlOpts, true),
            ));
        }

        $resp = curl_exec($ch);
        $rc->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $rc->err = curl_error($ch);
        $rc->errno = curl_errno($ch);
        curl_close($ch);

        if (0 === $rc->errno) {
            $rc->resp = $parseResponseHeaders ? substr($resp, $rc->headers->getLength()) : $resp;
        }

        return $rc;
    }

    protected function _buildAcceptHeader(Request $request,
                                          SerializeFormatEnum $format): string
    {
        $ver = match(true) {
            isset($request->version) => $request->version,
            isset($request->resource) => $request->resource->_getFHIRVersion(),
            default => null,
        };
        if (null === $ver) {
            return "Accept: application/{$format->value}+json, application/json+{$format->value}";
        } else if ($ver->getFHIRVersionInteger() < FHIRVersion::STU3_MIN_VERSION_INTEGER) {
            return "Accept: application/{$format->value}+fhir; fhirVersion={$ver->getFHIRShortVersion()}";
        } else {
            return "Accept: application/fhir+{$format->value}; fhirVersion={$ver->getFHIRShortVersion()}";
        }
    }

    protected function _buildContentTypeHeader(Request $request,
                                               SerializeFormatEnum $format): string
    {
        $ver = $request->resource->_getFHIRVersion();
        if (HTTPMethodEnum::PATCH === $request->method) {
            return "Content-Type: application/{$format->value}-patch+{$format->value}; fhirVersion={$ver->getFHIRShortVersion()}";
        } else if ($ver->getFHIRVersionInteger() < FHIRVersion::STU3_MIN_VERSION_INTEGER) {
            return "Content-Type: application/{$format->value}+fhir; fhirVersion={$ver->getFHIRShortVersion()}";
        } else {
            return "Content-Type: application/fhir+{$format->value}; fhirVersion={$ver->getFHIRShortVersion()}";
        }
    }

    protected function _buildBody(ResourceTypeInterface $resource,
                                  SerializeFormatEnum $format): string
    {
        return match ($format) {
            SerializeFormatEnum::JSON => json_encode($resource),
            SerializeFormatEnum::XML => $resource->xmlSerialize(config: $this->_version->getConfig()->getSerializeConfig()),
        };
    }
}
