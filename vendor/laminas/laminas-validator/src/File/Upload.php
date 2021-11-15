<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

use Countable;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Validator for the maximum size of a file up to a max of 2GB
 *
 */
class Upload extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const INI_SIZE       = 'fileUploadErrorIniSize';
    const FORM_SIZE      = 'fileUploadErrorFormSize';
    const PARTIAL        = 'fileUploadErrorPartial';
    const NO_FILE        = 'fileUploadErrorNoFile';
    const NO_TMP_DIR     = 'fileUploadErrorNoTmpDir';
    const CANT_WRITE     = 'fileUploadErrorCantWrite';
    const EXTENSION      = 'fileUploadErrorExtension';
    const ATTACK         = 'fileUploadErrorAttack';
    const FILE_NOT_FOUND = 'fileUploadErrorFileNotFound';
    const UNKNOWN        = 'fileUploadErrorUnknown';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::INI_SIZE       => "File '%value%' exceeds upload_max_filesize directive in php.ini",
        self::FORM_SIZE      => "File '%value%' exceeds the MAX_FILE_SIZE directive that was "
            . 'specified in the HTML form',
        self::PARTIAL        => "File '%value%' was only partially uploaded",
        self::NO_FILE        => "File '%value%' was not uploaded",
        self::NO_TMP_DIR     => "Missing a temporary folder to store '%value%'",
        self::CANT_WRITE     => "Failed to write file '%value%' to disk",
        self::EXTENSION      => "A PHP extension stopped uploading the file '%value%'",
        self::ATTACK         => "File '%value%' was illegally uploaded. This could be a possible attack",
        self::FILE_NOT_FOUND => "File '%value%' was not found",
        self::UNKNOWN        => "Unknown error while uploading file '%value%'",
    ];

    protected $options = [
        'files' => [],
    ];

    /**
     * Sets validator options
     *
     * The array $files must be given in syntax of Laminas\File\Transfer\Transfer to be checked
     * If no files are given the $_FILES array will be used automatically.
     * NOTE: This validator will only work with HTTP POST uploads!
     *
     * @param  array|\Traversable $options Array of files in syntax of \Laminas\File\Transfer\Transfer
     */
    public function __construct($options = [])
    {
        if (is_array($options) && ! array_key_exists('files', $options)) {
            $options = ['files' => $options];
        }

        parent::__construct($options);
    }

    /**
     * Returns the array of set files
     *
     * @param  string $file (Optional) The file to return in detail
     * @return array
     * @throws Exception\InvalidArgumentException If file is not found
     */
    public function getFiles($file = null)
    {
        if ($file !== null) {
            $return = [];
            foreach ($this->options['files'] as $name => $content) {
                if ($name === $file) {
                    $return[$file] = $this->options['files'][$name];
                }

                if ($content instanceof UploadedFileInterface) {
                    if ($content->getClientFilename() === $file) {
                        $return[$name] = $this->options['files'][$name];
                    }
                } elseif ($content['name'] === $file) {
                    $return[$name] = $this->options['files'][$name];
                }
            }

            if (! $return) {
                throw new Exception\InvalidArgumentException("The file '$file' was not found");
            }

            return $return;
        }

        return $this->options['files'];
    }

    /**
     * Sets the files to be checked
     *
     * @param  array $files The files to check in syntax of \Laminas\File\Transfer\Transfer
     * @return $this Provides a fluent interface
     */
    public function setFiles($files = [])
    {
        if (null === $files
            || ((is_array($files) || $files instanceof Countable)
                && count($files) === 0)
        ) {
            $this->options['files'] = $_FILES;
        } else {
            $this->options['files'] = $files;
        }

        if ($this->options['files'] === null) {
            $this->options['files'] = [];
        }

        foreach ($this->options['files'] as $file => $content) {
            if (! $content instanceof UploadedFileInterface
                && ! isset($content['error'])
            ) {
                unset($this->options['files'][$file]);
            }
        }

        return $this;
    }

    /**
     * Returns true if and only if the file was uploaded without errors
     *
     * @param  string $value Single file to check for upload errors, when giving null the $_FILES array
     *                       from initialization will be used
     * @param  mixed  $file
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $files = [];
        $this->setValue($value);
        if (array_key_exists($value, $this->getFiles())) {
            $files = array_merge($files, $this->getFiles($value));
        } else {
            foreach ($this->getFiles() as $file => $content) {
                if ($content instanceof UploadedFileInterface) {
                    if ($content->getClientFilename() === $value) {
                        $files = array_merge($files, $this->getFiles($file));
                    }

                    // PSR cannot search by tmp_name because it does not have
                    // a public interface to get it, only user defined name
                    // from form field.
                    continue;
                }

                if (isset($content['name']) && ($content['name'] === $value)) {
                    $files = array_merge($files, $this->getFiles($file));
                }

                if (isset($content['tmp_name']) && ($content['tmp_name'] === $value)) {
                    $files = array_merge($files, $this->getFiles($file));
                }
            }
        }

        if (empty($files)) {
            return $this->throwError($file, self::FILE_NOT_FOUND);
        }

        foreach ($files as $file => $content) {
            $this->value = $file;
            $error = $content instanceof UploadedFileInterface
                ? $content->getError()
                : $content['error'];

            switch ($error) {
                case 0:
                    if ($content instanceof UploadedFileInterface) {
                        // done!
                        break;
                    }

                    // For standard SAPI environments, check that the upload
                    // was valid
                    if (! is_uploaded_file($content['tmp_name'])) {
                        $this->throwError($content, self::ATTACK);
                    }
                    break;

                case 1:
                    $this->throwError($content, self::INI_SIZE);
                    break;

                case 2:
                    $this->throwError($content, self::FORM_SIZE);
                    break;

                case 3:
                    $this->throwError($content, self::PARTIAL);
                    break;

                case 4:
                    $this->throwError($content, self::NO_FILE);
                    break;

                case 6:
                    $this->throwError($content, self::NO_TMP_DIR);
                    break;

                case 7:
                    $this->throwError($content, self::CANT_WRITE);
                    break;

                case 8:
                    $this->throwError($content, self::EXTENSION);
                    break;

                default:
                    $this->throwError($content, self::UNKNOWN);
                    break;
            }
        }

        if ($this->getMessages()) {
            return false;
        }

        return true;
    }

    /**
     * Throws an error of the given type
     *
     * @param  array|string|UploadedFileInterface $file
     * @param  string $errorType
     * @return false
     */
    protected function throwError($file, $errorType)
    {
        if ($file !== null) {
            if (is_array($file)) {
                if (array_key_exists('name', $file)) {
                    $this->value = $file['name'];
                }
            } elseif (is_string($file)) {
                $this->value = $file;
            } elseif ($file instanceof UploadedFileInterface) {
                $this->value = $file->getClientFilename();
            }
        }

        $this->error($errorType);
        return false;
    }
}
