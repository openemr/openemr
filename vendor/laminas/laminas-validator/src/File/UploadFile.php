<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Validator for the maximum size of a file up to a max of 2GB
 */
class UploadFile extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const INI_SIZE       = 'fileUploadFileErrorIniSize';
    const FORM_SIZE      = 'fileUploadFileErrorFormSize';
    const PARTIAL        = 'fileUploadFileErrorPartial';
    const NO_FILE        = 'fileUploadFileErrorNoFile';
    const NO_TMP_DIR     = 'fileUploadFileErrorNoTmpDir';
    const CANT_WRITE     = 'fileUploadFileErrorCantWrite';
    const EXTENSION      = 'fileUploadFileErrorExtension';
    const ATTACK         = 'fileUploadFileErrorAttack';
    const FILE_NOT_FOUND = 'fileUploadFileErrorFileNotFound';
    const UNKNOWN        = 'fileUploadFileErrorUnknown';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::INI_SIZE       => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        self::FORM_SIZE      => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was '
            . 'specified in the HTML form',
        self::PARTIAL        => 'The uploaded file was only partially uploaded',
        self::NO_FILE        => 'No file was uploaded',
        self::NO_TMP_DIR     => 'Missing a temporary folder',
        self::CANT_WRITE     => 'Failed to write file to disk',
        self::EXTENSION      => 'A PHP extension stopped the file upload',
        self::ATTACK         => 'File was illegally uploaded. This could be a possible attack',
        self::FILE_NOT_FOUND => 'File was not found',
        self::UNKNOWN        => 'Unknown error while uploading file',
    ];

    /**
     * Returns true if and only if the file was uploaded without errors
     *
     * @param  string|array|UploadedFileInterface $value File to check for upload errors
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isValid($value)
    {
        if (is_array($value)) {
            if (! isset($value['tmp_name']) || ! isset($value['name']) || ! isset($value['error'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }

            return $this->validateUploadedFile(
                $value['error'],
                $value['name'],
                $value['tmp_name']
            );
        }

        if ($value instanceof UploadedFileInterface) {
            return $this->validatePsr7UploadedFile($value);
        }

        if (is_string($value)) {
            return $this->validateUploadedFile(0, basename($value), $value);
        }

        $this->error(self::UNKNOWN);
        return false;
    }

    /**
     * @param int $error UPLOAD_ERR_* constant value
     * @return bool
     */
    private function validateFileFromErrorCode($error)
    {
        switch ($error) {
            case UPLOAD_ERR_OK:
                return true;

            case UPLOAD_ERR_INI_SIZE:
                $this->error(self::INI_SIZE);
                return false;

            case UPLOAD_ERR_FORM_SIZE:
                $this->error(self::FORM_SIZE);
                return false;

            case UPLOAD_ERR_PARTIAL:
                $this->error(self::PARTIAL);
                return false;

            case UPLOAD_ERR_NO_FILE:
                $this->error(self::NO_FILE);
                return false;

            case UPLOAD_ERR_NO_TMP_DIR:
                $this->error(self::NO_TMP_DIR);
                return false;

            case UPLOAD_ERR_CANT_WRITE:
                $this->error(self::CANT_WRITE);
                return false;

            case UPLOAD_ERR_EXTENSION:
                $this->error(self::EXTENSION);
                return false;

            default:
                $this->error(self::UNKNOWN);
                return false;
        }
    }

    /**
     * @param  int $error UPLOAD_ERR_* constant
     * @param  string $filename
     * @param  string $uploadedFile Name of uploaded file (gen tmp_name)
     * @return bool
     */
    private function validateUploadedFile($error, $filename, $uploadedFile)
    {
        $this->setValue($filename);

        // Normal errors can be validated normally
        if ($error !== UPLOAD_ERR_OK) {
            return $this->validateFileFromErrorCode($error);
        }

        // Did we get no name? Is the file missing?
        if (empty($uploadedFile) || false === is_file($uploadedFile)) {
            $this->error(self::FILE_NOT_FOUND);
            return false;
        }

        // Do we have an invalid upload?
        if (! is_uploaded_file($uploadedFile)) {
            $this->error(self::ATTACK);
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function validatePsr7UploadedFile(UploadedFileInterface $uploadedFile)
    {
        $this->setValue($uploadedFile);
        return $this->validateFileFromErrorCode($uploadedFile->getError());
    }
}
