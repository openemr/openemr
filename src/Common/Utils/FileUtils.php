<?php

/**
 * Utility functions for working with files.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin McCormick Longview, Texas
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) Kevin McCormick Longview, Texas
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

class FileUtils
{
    /**
     * adapted from http://scratch99.com/web-development/javascript/convert-bytes-to-mb-kb/
     *
     * @param int
     * @author    Kevin McCormick Longview, Texas
     *
     * @return string
     */
    public static function getHumanReadableFileSize($bytes)
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
}
