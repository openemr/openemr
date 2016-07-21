<?php
/*
 *  $Id: c7ddadefd28858687239537bc43337787c1e704e $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */
include_once 'phing/system/io/FileParserInterface.php';

/**
 * Implements a YamlFileParser to parse yaml-files as array.
 *
 * @author Mike Lohmann <mike.lohmann@deck36.de>
 * @package phing.system.io
 */
class YamlFileParser implements FileParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function parseFile(PhingFile $file)
    {
        if (!$file->canRead()) {
            throw new IOException("Unable to read file: " . $file);
        }

        try {
            // We load the Yaml class without the use of namespaces to prevent
            // parse errors in PHP 5.2.
            $parserClass = '\Symfony\Component\Yaml\Parser';
            $parser = new $parserClass;
            $properties = $parser->parse(file_get_contents($file->getAbsolutePath()));
        } catch (Exception $e) {
            if (is_a($e, '\Symfony\Component\Yaml\Exception\ParseException')) {
                throw new IOException("Unable to parse contents of " . $file . ": " . $e->getMessage());
            }
            throw $e;
        }

        $flattenedProperties = $this->flattenArray($properties);
        foreach ($flattenedProperties as $key => $flattenedProperty) {
            if (is_array($flattenedProperty)) {
                $flattenedProperties[$key] = implode(',', $flattenedProperty);
            }
        }

        return $flattenedProperties;
    }

    /**
     * Flattens an array to key => value.
     * @todo: milo - 20142901 - If you plan to extend phing and add a new fileparser, please move this to an abstract
     * class.
     *
     * @param array $arrayToFlatten
     */
    private function flattenArray(array $arrayToFlatten, $separator = '.', $flattenedKey = '')
    {
        $flattenedArray = array();
        foreach ($arrayToFlatten as $key => $value) {
            $tmpFlattendKey = (!empty($flattenedKey) ? $flattenedKey.$separator : '') . $key;
            // only append next value if is array and is an associative array
            if (is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
                $flattenedArray = array_merge(
                    $flattenedArray,
                    $this->flattenArray(
                        $value,
                        $separator,
                        $tmpFlattendKey
                    )
                );
            } else {
                $flattenedArray[$tmpFlattendKey] = $value;
            }
        }
        return $flattenedArray;
    }
}
