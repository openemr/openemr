<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter;

use Psr\Http\Message\UploadedFileInterface;

use function is_array;

/**
 * FileInput is a special Input type for handling uploaded files.
 *
 * It differs from Input in a few ways:
 *
 * 1. It expects the raw value to either be in the $_FILES array format, or an
 *    array of PSR-7 UploadedFileInterface instances.
 *
 * 2. The validators are run **before** the filters (the opposite behavior of Input).
 *    This is so validation can be run prior to any filters that may
 *    rename/move/modify the file.
 *
 * 3. Instead of adding a NotEmpty validator, it will (by default) automatically add
 *    a Laminas\Validator\File\Upload validator.
 */
class FileInput extends Input
{
    /**
     * @var bool
     */
    protected $isValid = false;

    /**
     * @var bool
     */
    protected $autoPrependUploadValidator = true;

    /** @var FileInput\FileInputDecoratorInterface */
    private $implementation;

    /**
     * @param array|UploadedFile $value
     *
     * @return Input
     */
    public function setValue($value)
    {
        $this->implementation = $this->createDecoratorImplementation($value);
        parent::setValue($value);
        return $this;
    }

    public function resetValue()
    {
        $this->implementation = null;
        return parent::resetValue();
    }

    /**
     * @param  bool $value Enable/Disable automatically prepending an Upload validator
     *
     * @return FileInput
     */
    public function setAutoPrependUploadValidator($value)
    {
        $this->autoPrependUploadValidator = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAutoPrependUploadValidator()
    {
        return $this->autoPrependUploadValidator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->implementation === null) {
            return $this->value;
        }
        return $this->implementation->getValue();
    }

    /**
     * Checks if the raw input value is an empty file input eg: no file was uploaded
     *
     * @param $rawValue
     * @return bool
     */
    public function isEmptyFile($rawValue)
    {
        if ($rawValue instanceof UploadedFileInterface) {
            return FileInput\PsrFileInputDecorator::isEmptyFileDecorator($rawValue);
        }

        if (is_array($rawValue)) {
            if (isset($rawValue[0]) && $rawValue[0] instanceof UploadedFileInterface) {
                return FileInput\PsrFileInputDecorator::isEmptyFileDecorator($rawValue);
            }

            return FileInput\HttpServerFileInputDecorator::isEmptyFileDecorator($rawValue);
        }

        return true;
    }

    /**
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null)
    {
        $rawValue        = $this->getRawValue();
        $hasValue        = $this->hasValue();
        $empty           = $this->isEmptyFile($rawValue);
        $required        = $this->isRequired();
        $allowEmpty      = $this->allowEmpty();
        $continueIfEmpty = $this->continueIfEmpty();

        if (! $hasValue && ! $required) {
            return true;
        }

        if (! $hasValue && $required && ! $this->hasFallback()) {
            if ($this->errorMessage === null) {
                $this->errorMessage = $this->prepareRequiredValidationFailureMessage();
            }
            return false;
        }

        if ($empty && ! $required && ! $continueIfEmpty) {
            return true;
        }

        if ($empty && $allowEmpty && ! $continueIfEmpty) {
            return true;
        }

        return $this->implementation->isValid($context);
    }

    /**
     * @param  InputInterface $input
     *
     * @return FileInput
     */
    public function merge(InputInterface $input)
    {
        parent::merge($input);
        if ($input instanceof FileInput) {
            $this->setAutoPrependUploadValidator($input->getAutoPrependUploadValidator());
        }
        return $this;
    }

    /**
     * @deprecated 2.4.8 See note on parent class. Removal does not affect this class.
     *
     * No-op, NotEmpty validator does not apply for FileInputs.
     * See also: BaseInputFilter::isValid()
     *
     * @return void
     */
    protected function injectNotEmptyValidator()
    {
        $this->notEmptyValidator = true;
    }

    /**
     * @param mixed $value
     * @return FileInput\FileInputDecoratorInterface
     */
    private function createDecoratorImplementation($value)
    {
        // Single PSR-7 instance
        if ($value instanceof UploadedFileInterface) {
            return new FileInput\PsrFileInputDecorator($this);
        }

        if (is_array($value)) {
            if (isset($value[0]) && $value[0] instanceof UploadedFileInterface) {
                // Array of PSR-7 instances
                return new FileInput\PsrFileInputDecorator($this);
            }

            // Single or multiple SAPI file upload arrays
            return new FileInput\HttpServerFileInputDecorator($this);
        }

        // AJAX/XHR/Fetch case
        return new FileInput\HttpServerFileInputDecorator($this);
    }
}
