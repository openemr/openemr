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

class ResponseHeaders implements \Countable
{
    /** @var array */
    private array $_headerLines = [];
    /** @var int */
    private int $_headerLen = 0;
    /** @var array */
    private array $_headers = [];
    /** @var bool */
    private bool $_parsed = false;

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->_headerLen;
    }

    /**
     * Returns the number of raw header lines seen in the response.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->_headerLines);
    }

    /**
     * @param string $line
     * @return int
     */
    public function addLine(string $line): int
    {
        $this->_parsed = false;
        $len = strlen($line);
        $this->_headerLen += $len;
        $this->_headerLines[] = trim($line);
        return $len;
    }

    /**
     * Return all parsed values for a given response header, if it exists.
     *
     * @param string $name
     * @return null|array
     */
    public function get(string $name): null|array
    {
        $this->_parseResponseHeaders();
        return $this->_headers[strtolower($name)] ?? null;
    }

    /**
     * Return the first value for a given response header, if it exists.
     *
     * @return null|string
     */
    public function getFirst(string $name): null|string
    {
        $v = $this->get($name);
        if (null === $v || [] === $v) {
            return null;
        }
        return $v[0];
    }

    /**
     * Returns an iterator containing the raw header lines extracted from the repsonse.
     *
     * @return iterable
     */
    public function getLinesIterator(): iterable
    {
        return new \ArrayIterator($this->_headerLines);
    }

    /**
     * Returns an iterator containing the parsed header lines from the response.
     *
     * @return iterable
     */
    public function getParsedIterator(): iterable
    {
        $this->_parseResponseHeaders();
        return new \ArrayIterator($this->_headers);
    }

    protected function _parseResponseHeaders(): void
    {
        if ($this->_parsed) {
            return;
        }
        $this->_headers = [];
        $this->_parsed = true;
        foreach($this->_headerLines as $line) {
            if (!str_contains((string) $line, ':')) {
                continue;
            }
            $parts = explode(':', (string) $line, 2);
            if (2 !== count($parts)) {
                continue;
            }
            $name = strtolower(trim($parts[0]));
            $value = trim($parts[1]);
            if (!isset($this->_headers[$name])) {
                $this->_headers[$name] = [$value];
            } else {
                $this->_headers[$name][] = $value;
            }
        }
    }
}
