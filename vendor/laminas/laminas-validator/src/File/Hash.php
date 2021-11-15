<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

/**
 * Validator for the hash of given files
 */
class Hash extends AbstractValidator
{
    use FileInformationTrait;

    /**
     * @const string Error constants
     */
    const DOES_NOT_MATCH = 'fileHashDoesNotMatch';
    const NOT_DETECTED   = 'fileHashHashNotDetected';
    const NOT_FOUND      = 'fileHashNotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::DOES_NOT_MATCH => 'File does not match the given hashes',
        self::NOT_DETECTED   => 'A hash could not be evaluated for the given file',
        self::NOT_FOUND      => 'File is not readable or does not exist',
    ];

    /**
     * Options for this validator
     *
     * @var string
     */
    protected $options = [
        'algorithm' => 'crc32',
        'hash'      => null,
    ];

    /**
     * Sets validator options
     *
     * @param string|array $options
     */
    public function __construct($options = null)
    {
        if (is_scalar($options) ||
            (is_array($options) && ! array_key_exists('hash', $options))) {
            $options = ['hash' => $options];
        }

        if (1 < func_num_args()) {
            $options['algorithm'] = func_get_arg(1);
        }

        parent::__construct($options);
    }

    /**
     * Returns the set hash values as array, the hash as key and the algorithm the value
     *
     * @return array
     */
    public function getHash()
    {
        return $this->options['hash'];
    }

    /**
     * Sets the hash for one or multiple files
     *
     * @param  string|array $options
     * @return $this Provides a fluent interface
     */
    public function setHash($options)
    {
        $this->options['hash'] = null;
        $this->addHash($options);

        return $this;
    }

    /**
     * Adds the hash for one or multiple files
     *
     * @param  string|array $options
     * @throws Exception\InvalidArgumentException
     * @return $this Provides a fluent interface
     */
    public function addHash($options)
    {
        if (is_string($options)) {
            $options = [$options];
        } elseif (! is_array($options)) {
            throw new Exception\InvalidArgumentException('False parameter given');
        }

        $known = hash_algos();
        if (! isset($options['algorithm'])) {
            $algorithm = $this->options['algorithm'];
        } else {
            $algorithm = $options['algorithm'];
            unset($options['algorithm']);
        }

        if (! in_array($algorithm, $known)) {
            throw new Exception\InvalidArgumentException("Unknown algorithm '{$algorithm}'");
        }

        foreach ($options as $value) {
            if (! is_string($value)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Hash must be a string, %s received',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }
            $this->options['hash'][$value] = $algorithm;
        }

        return $this;
    }

    /**
     * Returns true if and only if the given file confirms the set hash
     *
     * @param  string|array $value File to check for hash
     * @param  array        $file  File data from \Laminas\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $fileInfo = $this->getFileInfo($value, $file);

        $this->setValue($fileInfo['filename']);

        // Is file readable ?
        if (empty($fileInfo['file']) || false === is_readable($fileInfo['file'])) {
            $this->error(self::NOT_FOUND);
            return false;
        }

        $algos = array_unique(array_values($this->getHash()));
        foreach ($algos as $algorithm) {
            $filehash = hash_file($algorithm, $fileInfo['file']);

            if ($filehash === false) {
                $this->error(self::NOT_DETECTED);
                return false;
            }

            if (isset($this->getHash()[$filehash]) && $this->getHash()[$filehash] === $algorithm) {
                return true;
            }
        }

        $this->error(self::DOES_NOT_MATCH);
        return false;
    }
}
