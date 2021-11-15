<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter\FileInput;

/**
 * FileInputInterface defines expected methods for filtering uploaded files.
 *
 * FileInput will consume instances of this interface when filtering files,
 * allowing it to switch between SAPI uploads and PSR-7 UploadedFileInterface
 * instances.
 */
interface FileInputDecoratorInterface
{
    /**
     * Checks if the raw input value is an empty file input eg: no file was uploaded
     *
     * @param $rawValue
     * @return bool
     */
    public static function isEmptyFileDecorator($rawValue);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null);
}
