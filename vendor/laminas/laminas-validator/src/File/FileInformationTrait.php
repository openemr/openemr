<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\File;

use Laminas\Validator\Exception;
use Psr\Http\Message\UploadedFileInterface;

trait FileInformationTrait
{
    /**
     * Returns array if the procedure is identified
     *
     * @param  string|array|object $value    Filename to check
     * @param  null|array          $file     File data (when using legacy Laminas_File_Transfer API)
     * @param  bool                $hasType  Return with filetype (optional)
     * @param  bool                $basename Return with basename - is calculated from location path (optional)
     * @return array
     */
    protected function getFileInfo(
        $value,
        array $file = null,
        $hasType = false,
        $hasBasename = false
    ) {
        if (is_string($value) && is_array($file)) {
            return $this->getLegacyFileInfo($file, $hasType, $hasBasename);
        }

        if (is_array($value)) {
            return $this->getSapiFileInfo($value, $hasType, $hasBasename);
        }

        if ($value instanceof UploadedFileInterface) {
            return $this->getPsr7FileInfo($value, $hasType, $hasBasename);
        }

        return $this->getFileBasedFileInfo($value, $hasType, $hasBasename);
    }

    /**
     * Generate file information array with legacy Laminas_File_Transfer API
     *
     * @param array  $file        File data
     * @param bool   $hasType     Return with filetype
     * @param bool   $hasBasename Basename is calculated from location path
     * @return array
     */
    private function getLegacyFileInfo(
        array $file,
        $hasType = false,
        $hasBasename = false
    ) {
        $fileInfo = [];

        $fileInfo['filename'] = $file['name'];
        $fileInfo['file']     = $file['tmp_name'];

        if ($hasBasename) {
            $fileInfo['basename'] = basename($fileInfo['file']);
        }

        if ($hasType) {
            $fileInfo['filetype'] = $file['type'];
        }

        return $fileInfo;
    }

    /**
     * Generate file information array with SAPI
     *
     * @param array $file        File data from SAPI
     * @param bool  $hasType     Return with filetype
     * @param bool  $hasBasename Filename is calculated from location path
     * @return array
     */
    private function getSapiFileInfo(
        array $file,
        $hasType = false,
        $hasBasename = false
    ) {
        if (! isset($file['tmp_name']) || ! isset($file['name'])) {
            throw new Exception\InvalidArgumentException(
                'Value array must be in $_FILES format'
            );
        }

        $fileInfo = [];

        $fileInfo['file']     = $file['tmp_name'];
        $fileInfo['filename'] = $file['name'];

        if ($hasBasename) {
            $fileInfo['basename'] = basename($fileInfo['file']);
        }

        if ($hasType) {
            $fileInfo['filetype'] = $file['type'];
        }

        return $fileInfo;
    }

    /**
     * Generate file information array with PSR-7 UploadedFileInterface
     *
     * @param UploadedFileInterface $file
     * @param bool                  $hasType     Return with filetype
     * @param bool                  $hasBasename Filename is calculated from location path
     * @return array
     */
    private function getPsr7FileInfo(
        UploadedFileInterface $file,
        $hasType = false,
        $hasBasename = false
    ) {
        $fileInfo = [];

        $fileInfo['file']     = $file->getStream()->getMetadata('uri');
        $fileInfo['filename'] = $file->getClientFilename();

        if ($hasBasename) {
            $fileInfo['basename'] = basename($fileInfo['file']);
        }

        if ($hasType) {
            $fileInfo['filetype'] = $file->getClientMediaType();
        }

        return $fileInfo;
    }

    /**
     * Generate file information array with base method
     *
     * @param string $file        File path
     * @param bool   $hasType     Return with filetype
     * @param bool   $hasBasename Filename is calculated from location path
     * @return array
     */
    private function getFileBasedFileInfo(
        $file,
        $hasType = false,
        $hasBasename = false
    ) {
        $fileInfo = [];

        $fileInfo['file']     = $file;
        $fileInfo['filename'] = basename($fileInfo['file']);

        if ($hasBasename) {
            $fileInfo['basename'] = basename($fileInfo['file']);
        }

        if ($hasType) {
            $fileInfo['filetype'] = null;
        }

        return $fileInfo;
    }
}
