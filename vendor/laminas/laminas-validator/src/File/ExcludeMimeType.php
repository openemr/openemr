<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

/**
 * Validator for the mime type of a file
 */
class ExcludeMimeType extends MimeType
{
    use FileInformationTrait;

    const FALSE_TYPE   = 'fileExcludeMimeTypeFalse';
    const NOT_DETECTED = 'fileExcludeMimeTypeNotDetected';
    const NOT_READABLE = 'fileExcludeMimeTypeNotReadable';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::FALSE_TYPE   => "File has an incorrect mimetype of '%type%'",
        self::NOT_DETECTED => 'The mimetype could not be detected from the file',
        self::NOT_READABLE => 'File is not readable or does not exist',
    ];

    /**
     * Returns true if the mimetype of the file does not matche the given ones. Also parts
     * of mimetypes can be checked. If you give for example "image" all image
     * mime types will not be accepted like "image/gif", "image/jpeg" and so on.
     *
     * @param  string|array $value Real file to check for mimetype
     * @param  array        $file  File data from \Laminas\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $fileInfo = $this->getFileInfo($value, $file, true);

        $this->setValue($fileInfo['filename']);

        // Is file readable ?
        if (empty($fileInfo['file']) || false === is_readable($fileInfo['file'])) {
            $this->error(self::NOT_READABLE);
            return false;
        }

        $mimefile = $this->getMagicFile();
        if (class_exists('finfo', false)) {
            if (! $this->isMagicFileDisabled() && (! empty($mimefile) && empty($this->finfo))) {
                $this->finfo = finfo_open(FILEINFO_MIME_TYPE, $mimefile);
            }

            if (empty($this->finfo)) {
                $this->finfo = finfo_open(FILEINFO_MIME_TYPE);
            }

            $this->type = null;
            if (! empty($this->finfo)) {
                $this->type = finfo_file($this->finfo, $fileInfo['file']);
            }
        }

        if (empty($this->type) && $this->getHeaderCheck()) {
            $this->type = $fileInfo['filetype'];
        }

        if (empty($this->type)) {
            $this->error(self::NOT_DETECTED);
            return false;
        }

        $mimetype = $this->getMimeType(true);
        if (in_array($this->type, $mimetype)) {
            $this->error(self::FALSE_TYPE);
            return false;
        }

        $types = explode('/', $this->type);
        $types = array_merge($types, explode('-', $this->type));
        $types = array_merge($types, explode(';', $this->type));
        foreach ($mimetype as $mime) {
            if (in_array($mime, $types)) {
                $this->error(self::FALSE_TYPE);
                return false;
            }
        }

        return true;
    }
}
