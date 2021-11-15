<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Writer;

use Laminas\Config\Exception;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function file_put_contents;
use function is_array;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;

use const E_WARNING;
use const LOCK_EX;

abstract class AbstractWriter implements WriterInterface
{
    /**
     * toFile(): defined by Writer interface.
     *
     * @see    WriterInterface::toFile()
     * @param  string  $filename
     * @param  mixed   $config
     * @param  bool $exclusiveLock
     * @return void
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function toFile($filename, $config, $exclusiveLock = true)
    {
        if (empty($filename)) {
            throw new Exception\InvalidArgumentException('No file name specified');
        }

        $flags = 0;
        if ($exclusiveLock) {
            $flags |= LOCK_EX;
        }

        set_error_handler(
            function ($error, $message = '') use ($filename) {
                throw new Exception\RuntimeException(
                    sprintf('Error writing to "%s": %s', $filename, $message),
                    $error
                );
            },
            E_WARNING
        );

        try {
            file_put_contents($filename, $this->toString($config), $flags);
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }

        restore_error_handler();
    }

    /**
     * toString(): defined by Writer interface.
     *
     * @see    WriterInterface::toString()
     * @param  mixed   $config
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function toString($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        } elseif (! is_array($config)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable config');
        }

        return $this->processConfig($config);
    }

    /**
     * @param array $config
     * @return string
     */
    abstract protected function processConfig(array $config);
}
