<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Writer;

use Laminas\Config\Exception;

use function get_class;
use function gettype;
use function is_object;
use function is_scalar;
use function is_string;
use function sprintf;

class JavaProperties extends AbstractWriter
{
    const DELIMITER_DEFAULT = ':';

    /**
     * Delimiter for key/value pairs.
     */
    private $delimiter;

    /**
     * @param string $delimiter Delimiter to use for key/value pairs; defaults
     *     to self::DELIMITER_DEFAULT (':')
     * @throws Exception\InvalidArgumentException for invalid $delimiter values.
     */
    public function __construct($delimiter = self::DELIMITER_DEFAULT)
    {
        if (! is_string($delimiter) || '' === $delimiter) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid delimiter of type "%s"; must be a non-empty string',
                is_object($delimiter) ? get_class($delimiter) : gettype($delimiter)
            ));
        }

        $this->delimiter = $delimiter;
    }

    /**
     * processConfig(): defined by AbstractWriter.
     *
     * @param  array $config
     * @return string
     * @throws Exception\UnprocessableConfigException for non-scalar values in
     *     the $config array.
     */
    public function processConfig(array $config)
    {
        $string = '';

        foreach ($config as $key => $value) {
            if (! is_scalar($value)) {
                throw new Exception\UnprocessableConfigException(sprintf(
                    '%s configuration writer can only process scalar values; received "%s" for key "%s"',
                    __CLASS__,
                    is_object($value) ? get_class($value) : gettype($value),
                    $key
                ));
            }

            $value = (null === $value) ? '' : $value;

            $string .= sprintf(
                "%s%s%s\n",
                $key,
                $this->delimiter,
                $value
            );
        }

        return $string;
    }
}
