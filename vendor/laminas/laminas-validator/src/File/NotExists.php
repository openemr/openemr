<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

/**
 * Validator which checks if the destination file does not exist
 */
class NotExists extends Exists
{
    use FileInformationTrait;

    /**
     * @const string Error constants
     */
    const DOES_EXIST = 'fileNotExistsDoesExist';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::DOES_EXIST => 'File exists',
    ];

    /**
     * Returns true if and only if the file does not exist in the set destinations
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
            if (file_exists($fileInfo['file'])) {
                $this->error(self::DOES_EXIST);
                return false;
            }
        } else {
            foreach ($directories as $directory) {
                if (! isset($directory) || '' === $directory) {
                    continue;
                }

                $check = true;
                if (file_exists($directory . DIRECTORY_SEPARATOR . $fileInfo['basename'])) {
                    $this->error(self::DOES_EXIST);
                    return false;
                }
            }
        }

        if (! $check) {
            $this->error(self::DOES_EXIST);
            return false;
        }

        return true;
    }
}
