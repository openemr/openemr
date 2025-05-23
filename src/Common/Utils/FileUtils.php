<?php

/**
 * Utility functions for working with files.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin McCormick Longview, Texas
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) Kevin McCormick Longview, Texas
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

class FileUtils
{
    /**
     * Map of file extensions to MIME types.
     */
    private static array $mimeTypes = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];

    /**
     * adapted from http://scratch99.com/web-development/javascript/convert-bytes-to-mb-kb/
     *
     * @param int
     * @author    Kevin McCormick Longview, Texas
     *
     * @return string
     */
    public static function getHumanReadableFileSize($bytes): string
    {
        $sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        if ($bytes == 0) {
            return 'n/a';
        }

        $i = floor(log($bytes) / log(1024));
        //$i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        if ($i == 0) {
            return $bytes . ' ' . $sizes[$i];
        } else {
            return round($bytes / pow(1024, $i), 1) . ' ' . $sizes[$i];
        }
    }

    /**
     * Determines the MIME type of file or content.
     *
     * @param string $filePath
     * @param string $content
     * @return array
     */
    public static function fileGetMimeType($filePath, &$content): false|array
    {
        $f_info = finfo_open(FILEINFO_MIME_TYPE);
        if (!empty($content)) {
            $mimeType = finfo_buffer($f_info, $content);
            finfo_close($f_info);
            // Check if filePath has an extension, if not add it based on MIME type
            $filePath = self::ensureExtension($filePath, $mimeType);
            return ['type' => $mimeType, 'filePath' => $filePath];
        }

        if (!empty($filePath) && !file_exists($filePath)) {
            finfo_close($f_info);
            return false;
        }

        $mimeType = finfo_file($f_info, $filePath);
        finfo_close($f_info);
        // Check if filePath has an extension, if not add it based on MIME type
        $filePath = self::ensureExtension($filePath, $mimeType);

        return ['type' => $mimeType, 'filePath' => $filePath];
    }

    /**
     * Retrieves the MIME type based on the file extension.
     *
     * @param string $extension
     * @return string
     */
    public static function getMimeTypeFromExtension($extension, $default = 'text/plain'): string
    {
        return self::$mimeTypes[strtolower($extension)] ?? $default;
    }

    /**
     * Retrieves the file extension based on the MIME type.
     *
     * @param string $mimeType
     * @return string
     */
    public static function getExtensionFromMimeType($mimeType): string
    {
        $extension = array_search(strtolower($mimeType), self::$mimeTypes);
        return $extension !== false ? $extension : '';
    }

    /**
     * Ensures the file path has an appropriate extension based on the MIME type.
     *
     * @param string $filePath
     * @param string $mimeType
     * @return string
     */
    public static function ensureExtension($filePath, $mimeType): string
    {
        $pathInfo = pathinfo($filePath);
        if (empty($pathInfo['extension'])) {
            $extension = self::getExtensionFromMimeType($mimeType);
            $filePath .= '.' . $extension;
        }
        return $filePath;
    }
}
