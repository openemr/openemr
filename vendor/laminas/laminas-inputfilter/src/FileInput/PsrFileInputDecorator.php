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
use Psr\Http\Message\UploadedFileInterface;

use function is_array;

/**
 * PsrFileInput is a special Input type for handling uploaded files through  PSR-7 middlware.
 *
 * It differs from Input in a few ways:
 *
 * 1. It expects the raw value to be an instance of UploadedFileInterface.
 *
 * 2. The validators are run **before** the filters (the opposite behavior of Input).
 *    This is so validation can be run prior to any filters that may
 *    rename/move/modify the file.
 *
 * 3. Instead of adding a NotEmpty validator, it will (by default) automatically add
 *    a Laminas\Validator\File\Upload validator.
 */
class PsrFileInputDecorator extends FileInput implements FileInputDecoratorInterface
{
    /** @var FileInput */
    private $subject;

    /**
     * Checks if the raw input value is an empty file input eg: no file was uploaded
     *
     * @param UploadedFileInterface|array $rawValue
     * @return bool
     */
    public static function isEmptyFileDecorator($rawValue)
    {
        if (is_array($rawValue)) {
            return self::isEmptyFileDecorator($rawValue[0]);
        }

        return $rawValue->getError() === UPLOAD_ERR_NO_FILE;
    }

    public function __construct(FileInput $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return UploadedFileInterface|UploadedFileInterface[]
     */
    public function getValue()
    {
        $value = $this->subject->value;

        // Run filters ~after~ validation, so that is_uploaded_file()
        // validation is not affected by filters.
        if (! $this->subject->isValid) {
            return $value;
        }

        $filter = $this->subject->getFilterChain();

        if (is_array($value)) {
            // Multi file input (multiple attribute set)
            $newValue = [];
            foreach ($value as $fileData) {
                $newValue[] = $filter->filter($fileData);
            }
            return $newValue;
        }

        // Single file input
        return $filter->filter($value);
    }

    /**
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null)
    {
        $rawValue  = $this->subject->getRawValue();
        $validator = $this->injectUploadValidator($this->subject->getValidatorChain());

        if (is_array($rawValue)) {
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

        // Single file input
        $this->subject->isValid = $validator->isValid($rawValue, $context);
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

        $chain->prependByName(UploadValidator::class, [], true);
        $this->subject->autoPrependUploadValidator = false;

        return $chain;
    }
}
