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

/**
 * Class Config
 *
 * Configuration class for built-in FHIR API client.  If you are not using the built-in client,
 * you can ignore this class.
 */
class Config
{
    /**
     * Config Constructor
     *
     * @param string $_address Fully qualified address of FHIR server, including scheme, port, and any path prefix.
     * @param \OpenEMR\FHIR\Encoding\SerializeFormatEnum $_defaultFormat Default serialization format.  Defaults to XML.
     * @param array $_defaultQueryParams Base query parameters array.  These will be added to every request.  May be overridden by an individual request.
     * @param array $_curlOpts Base curl options array.  These will be added to every request.  May be overridden by an individual request.
     * @param bool $_parseResponseHeaders Whether or not to parse headers from response.  This adds overhead to parsing each response, but is also necessary to extract response version information.
     */
    public function __construct(private readonly string $_address, private readonly SerializeFormatEnum $_defaultFormat = SerializeFormatEnum::XML, private readonly array $_defaultQueryParams = [], private readonly array $_curlOpts = [], private readonly bool $_parseResponseHeaders = true)
    {
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->_address;
    }

    /**
     * @return \OpenEMR\FHIR\Encoding\SerializeFormatEnum
     */
    public function getDefaultFormat(): SerializeFormatEnum
    {
        return $this->_defaultFormat;
    }

    /**
     * @return array
     */
    public function getDefaultQueryParams(): array
    {
        return $this->_defaultQueryParams;
    }

    /**
     * @return array
     */
    public function getCurlOpts(): array
    {
        return $this->_curlOpts;
    }

    /**
     * @return bool
     */
    public function getParseResponseHeaders(): bool
    {
        return $this->_parseResponseHeaders;
    }
}
