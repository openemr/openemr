<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter\FileInput;

use Laminas\InputFilter\FileInput;
use Laminas\Validator\File\UploadFile as UploadValidator;
use Laminas\Validator\ValidatorChain;

use function count;
use function is_array;

/**
 * Decorator for filtering standard SAPI file uploads.
 *
 * It differs from Input in a few ways:
 *
 * 1. It expects the raw value to be in the $_FILES array format.
 *
 * 2. The validators are run **before** the filters (the opposite behavior of Input).
 *    This is so is_uploaded_file() validation can be run prior to any filters that
 *    may rename/move/modify the file.
 *
 * 3. Instead of adding a NotEmpty validator, it will (by default) automatically add
 *    a Laminas\Validator\File\Upload validator.
 */
class HttpServerFileInputDecorator extends FileInput implements FileInputDecoratorInterface
{
    /** @var FileInput */
    private $subject;

    /**
     * Checks if the raw input value is an empty file input eg: no file was uploaded
     *
     * @param $rawValue
     * @return bool
     */
    public static function isEmptyFileDecorator($rawValue)
    {
        if (! is_array($rawValue)) {
            return true;
        }

        if (isset($rawValue['error']) && $rawValue['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (count($rawValue) === 1 && isset($rawValue[0])) {
            return self::isEmptyFileDecorator($rawValue[0]);
        }

        return false;
    }

    public function __construct(FileInput $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->subject->value;

        if (! $this->subject->isValid || ! is_array($value)) {
            return $value;
        }

        // Run filters ~after~ validation, so that is_uploaded_file()
        // validation is not affected by filters.
        $filter = $this->subject->getFilterChain();
        if (isset($value['tmp_name'])) {
            // Single file input
            $value = $filter->filter($value);
            return $value;
        }

        // Multi file input (multiple attribute set)
        $newValue = [];
        foreach ($value as $fileData) {
            if (is_array($fileData) && isset($fileData['tmp_name'])) {
                $newValue[] = $filter->filter($fileData);
            }
        }

        return $newValue;
    }

    /**
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null)
    {
        $rawValue  = $this->subject->getRawValue();
        $validator = $this->injectUploadValidator($this->subject->getValidatorChain());

        if (! is_array($rawValue)) {
            // This can happen in an AJAX POST, where the input comes across as a string
            $rawValue = [
                'tmp_name' => $rawValue,
                'name'     => $rawValue,
                'size'     => 0,
                'type'     => '',
                'error'    => UPLOAD_ERR_NO_FILE,
            ];
        } elseif (! isset($rawValue['tmp_name']) && ! isset($rawValue[0]['tmp_name'])) {
            // This can happen when sent not file and just array
            $rawValue = [
                'tmp_name' => '',
                'name'     => '',
                'size'     => 0,
                'type'     => '',
                'error'    => UPLOAD_ERR_NO_FILE,
            ];
        }

        if (is_array($rawValue) && isset($rawValue['tmp_name'])) {
            // Single file input
            $this->subject->isValid = $validator->isValid($rawValue, $context);
            return $this->subject->isValid;
        }

        if (is_array($rawValue) && isset($rawValue[0]['tmp_name'])) {
            // Multi file input (multiple attribute set)
            $this->subject->isValid = true;

            foreach ($rawValue as $value) {
                if (! $validator->isValid($value, $context)) {
                    $this->subject->isValid = false;
                    return false; // Do not continue processing files if validation fails
                }
            }

            return true; // We return early from the loop if validation fails
        }

        return $this->subject->isValid;
    }

    /**
     * @return ValidatorChain
     */
    protected function injectUploadValidator(ValidatorChain $chain)
    {
        if (! $this->subject->autoPrependUploadValidator) {
            return $chain;
        }

        // Check if Upload validator is already first in chain
        $validators = $chain->getValidators();
        if (isset($validators[0]['instance'])
            && $validators[0]['instance'] instanceof UploadValidator
        ) {
            $this->subject->autoPrependUploadValidator = false;
            return $chain;
        }

        $chain->prependByName('fileuploadfile', [], true);
        $this->subject->autoPrependUploadValidator = false;

        return $chain;
    }
}
