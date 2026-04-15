<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions;

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

use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;

class VersionConfig implements VersionConfigInterface
{
    /** @var \OpenEMR\FHIR\Encoding\UnserializeConfig */
    private UnserializeConfig $_unserializeConfig;

    /** @var \OpenEMR\FHIR\Encoding\SerializeConfig */
    private SerializeConfig $_serializeConfig;

    /**
     * VersionConfig constructor.
     * @param null|array|\OpenEMR\FHIR\Encoding\SerializeConfig $serializeConfig
     * @param null|array|\OpenEMR\FHIR\Encoding\UnserializeConfig $unserializeConfig
     */
    public function __construct(null|array|UnserializeConfig $unserializeConfig = null,
                                null|array|SerializeConfig $serializeConfig = null)
    {
        if (null === $unserializeConfig) {
            $unserializeConfig = new UnserializeConfig();
        }
        $this->setUnserializeConfig($unserializeConfig);
        if (null === $serializeConfig) {
            $serializeConfig = new SerializeConfig();
        }
        $this->setSerializeConfig($serializeConfig);
    }

    /**
     * @param array|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @return self
     */
    public function setUnserializeConfig(array|UnserializeConfig $config): self
    {
        if (is_array($config)) {
            $config = new UnserializeConfig(
                libxmlOpts: $config['libxmlOpts'] ?? null,
                jsonDecodeMaxDepth: $config['jsonDecodeMaxDepth'] ?? null,
                jsonDecodeOpts: $config['jsonDecodeOpts'] ?? null,
            );
        }
        $this->_unserializeConfig = $config;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\Encoding\UnserializeConfig
     */
    public function getUnserializeConfig(): UnserializeConfig
    {
        return $this->_unserializeConfig;
    }

    /**
     * @param array|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return self
     */
    public function setSerializeConfig(array|SerializeConfig $config): self
    {
        if (is_array($config)) {
            $config = new SerializeConfig(
                overrideSourceXMLNS: $config['overrideSourceXMLNS'] ?? null,
                rootXMLNS: $config['rootXMLNS'] ?? null,
                xhtmlLibxmlOpts: $config['xhtmlLibxmlOpts'] ?? null,
            );
        }
        $this->_serializeConfig = $config;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\Encoding\SerializeConfig
     */
    public function getSerializeConfig(): SerializeConfig
    {
        return $this->_serializeConfig;
    }
}
