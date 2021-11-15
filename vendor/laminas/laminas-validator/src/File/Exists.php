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
 * Validator which checks if the file already exists in the directory
 */
class Exists extends AbstractValidator
{
    use FileInformationTrait;

    /**
     * @const string Error constants
     */
    const DOES_NOT_EXIST = 'fileExistsDoesNotExist';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::DOES_NOT_EXIST => 'File does not exist',
    ];

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = [
        'directory' => null,  // internal list of directories
    ];

    /**
     * @var array Error message template variables
     */
    protected $messageVariables = [
        'directory' => ['options' => 'directory'],
    ];

    /**
     * Sets validator options
     *
     * @param  string|array|\Traversable $options
     */
    public function __construct($options = null)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        if (is_array($options) && ! array_key_exists('directory', $options)) {
            $options = ['directory' => $options];
        }

        parent::__construct($options);
    }

    /**
     * Returns the set file directories which are checked
     *
     * @param  bool $asArray Returns the values as array; when false, a concatenated string is returned
     * @return string|null
     */
    public function getDirectory($asArray = false)
    {
        $asArray   = (bool) $asArray;
        $directory = $this->options['directory'];
        if ($asArray && isset($directory)) {
            $directory = explode(',', (string) $directory);
        }

        return $directory;
    }

    /**
     * Sets the file directory which will be checked
     *
     * @param string|array $directory The directories to validate
     * @return self Provides a fluent interface
     */
    public function setDirectory($directory)
    {
        $this->options['directory'] = null;
        $this->addDirectory($directory);
        return $this;
    }

    /**
     * Adds the file directory which will be checked
     *
     * @param string|array $directory The directory to add for validation
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function addDirectory($directory)
    {
        $directories = $this->getDirectory(true);
        if (! isset($directories)) {
            $directories = [];
        }

        if (is_string($directory)) {
            $directory = explode(',', $directory);
        } elseif (! is_array($directory)) {
            throw new Exception\InvalidArgumentException('Invalid options to validator provided');
        }

        foreach ($directory as $content) {
            if (empty($content) || ! is_string($content)) {
                continue;
            }

            $directories[] = trim($content);
        }
        $directories = array_unique($directories);

        // Sanity check to ensure no empty values
        foreach ($directories as $key => $dir) {
            if (empty($dir)) {
                unset($directories[$key]);
            }
        }

        $this->options['directory'] = ! empty($directory)
            ? implode(',', $directories) : null;

        return $this;
    }

    /**
     * Returns true if and only if the file already exists in the set directories
     *
     * @param  string|array $value Real file to check for existence
     * @param  array        $file  File data from \Laminas\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $fileInfo = $this->getFileInfo($value, $file, false, true);

        $this->setValue($fileInfo['filename']);

        $check = false;
        $directories = $this->getDirectory(true);
        if (! isset($directories)) {
            $check = true;
            if (! file_exists($fileInfo['file'])) {
                $this->error(self::DOES_NOT_EXIST);
                return false;
            }
        } else {
            foreach ($directories as $directory) {
                if (! isset($directory) || '' === $directory) {
                    continue;
                }

                $check = true;
                if (! file_exists($directory . DIRECTORY_SEPARATOR . $fileInfo['basename'])) {
                    $this->error(self::DOES_NOT_EXIST);
                    return false;
                }
            }
        }

        if (! $check) {
            $this->error(self::DOES_NOT_EXIST);
            return false;
        }

        return true;
    }
}
