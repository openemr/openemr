<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Reader;

use Laminas\Config\Exception;

use function array_replace_recursive;
use function dirname;
use function file_get_contents;
use function is_array;
use function is_file;
use function is_readable;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;
use function trim;

use const JSON_ERROR_NONE;

/**
 * JSON config reader.
 */
class Json implements ReaderInterface
{
    /**
     * Directory of the JSON file
     *
     * @var string
     */
    protected $directory;

    /**
     * fromFile(): defined by Reader interface.
     *
     * @see    ReaderInterface::fromFile()
     * @param  string $filename
     * @return array
     * @throws Exception\RuntimeException
     */
    public function fromFile($filename)
    {
        if (! is_file($filename) || ! is_readable($filename)) {
            throw new Exception\RuntimeException(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }

        $this->directory = dirname($filename);

        $config = $this->decode(file_get_contents($filename));

        return $this->process($config);
    }

    /**
     * fromString(): defined by Reader interface.
     *
     * @see    ReaderInterface::fromString()
     * @param  string $string
     * @return array|bool
     * @throws Exception\RuntimeException
     */
    public function fromString($string)
    {
        if (empty($string)) {
            return [];
        }

        $this->directory = null;

        $config = $this->decode($string);

        return $this->process($config);
    }

    /**
     * Process the array for @include
     *
     * @param  array $data
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function process(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->process($value);
            }
            if (trim($key) === '@include') {
                if ($this->directory === null) {
                    throw new Exception\RuntimeException('Cannot process @include statement for a JSON string');
                }
                $reader = clone $this;
                unset($data[$key]);
                $data = array_replace_recursive($data, $reader->fromFile($this->directory . '/' . $value));
            }
        }
        return $data;
    }

    /**
     * Decode JSON configuration.
     *
     * Determines if ext/json is present, and, if so, uses that to decode the
     * configuration. Otherwise, it uses laminas-json, and, if that is missing,
     * raises an exception indicating inability to decode.
     *
     * @param string $data
     * @return array
     * @throws Exception\RuntimeException for any decoding errors.
     */
    private function decode($data)
    {
        $config = json_decode($data, true);

        if (null !== $config && ! is_array($config)) {
            throw new Exception\RuntimeException(
                'Invalid JSON configuration; did not return an array or object'
            );
        }

        if (null !== $config) {
            return $config;
        }

        if (JSON_ERROR_NONE === json_last_error()) {
            return $config;
        }

        throw new Exception\RuntimeException(json_last_error_msg());
    }
}
