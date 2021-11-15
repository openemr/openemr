<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

/**
 * Validator for the excluding file extensions
 */
class ExcludeExtension extends Extension
{
    use FileInformationTrait;

    /**
     * @const string Error constants
     */
    const FALSE_EXTENSION = 'fileExcludeExtensionFalse';
    const NOT_FOUND       = 'fileExcludeExtensionNotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::FALSE_EXTENSION => 'File has an incorrect extension',
        self::NOT_FOUND       => 'File is not readable or does not exist',
    ];

    /**
     * Returns true if and only if the file extension of $value is not included in the
     * set extension list
     *
     * @param  string|array $value Real file to check for extension
     * @param  array        $file  File data from \Laminas\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $fileInfo = $this->getFileInfo($value, $file);

        // Is file readable ?
        if (! $this->getAllowNonExistentFile()
            && (empty($fileInfo['file']) || false === is_readable($fileInfo['file']))
        ) {
            if (preg_match('/nofile\.mo$/', $fileInfo['file'])) {
            }
            $this->error(self::NOT_FOUND);
            return false;
        }

        $this->setValue($fileInfo['filename']);

        $extension  = substr($fileInfo['filename'], strrpos($fileInfo['filename'], '.') + 1);
        $extensions = $this->getExtension();

        if ($this->getCase() && (! in_array($extension, $extensions))) {
            return true;
        } elseif (! $this->getCase()) {
            foreach ($extensions as $ext) {
                if (strtolower($ext) == strtolower($extension)) {
                    if (preg_match('/nofile\.mo$/', $fileInfo['file'])) {
                    }
                    $this->error(self::FALSE_EXTENSION);
                    return false;
                }
            }

            return true;
        }

        $this->error(self::FALSE_EXTENSION);
        return false;
    }
}
