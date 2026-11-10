<?php

/**
 * Recognise a ZIP archive of DICOM images and give its entries the .dcm extension the viewer needs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Zip;

use ZipArchive;

final class DicomZip
{
    /**
     * Entries that are not images and do not decide whether an archive is DICOM: folders, the
     * __MACOSX/ resource forks and dot files (".DS_Store", "._1.dcm") that macOS adds when it zips a folder.
     */
    public static function isIgnoredEntry(string $name): bool
    {
        if (str_ends_with($name, '/')) {
            return true;
        }
        if (preg_match('#(^|/)__MACOSX/#', $name) === 1) {
            return true;
        }
        return str_starts_with(basename($name), '.');
    }

    /**
     * True when every entry other than the ignored ones is a DICOM file (the "DICM" marker at offset 128),
     * and there is at least one. Entries without the .dcm extension are renamed, keeping their folder, so
     * the viewer accepts them.
     */
    public static function normalize(ZipArchive $zip): bool
    {
        $images = 0;
        $renames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                return false;
            }
            $name = $stat['name'];
            if (self::isIgnoredEntry($name)) {
                continue;
            }
            $stream = $zip->getStream($name);
            if ($stream === false) {
                return false;
            }
            $head = fread($stream, 256);
            fclose($stream);
            // The marker is fixed at offset 128. One non-DICOM file makes the archive not a DICOM archive.
            if ($head === false || substr($head, 128, 4) !== 'DICM') {
                return false;
            }
            $images++;
            $info = pathinfo($name);
            if (strtolower($info['extension'] ?? '') !== 'dcm') {
                $folder = ($info['dirname'] ?? '.') === '.' ? '' : $info['dirname'] . '/';
                $renames[$i] = $folder . $info['filename'] . '.dcm';
            }
        }
        if ($images === 0) {
            return false;
        }
        foreach ($renames as $index => $newName) {
            $zip->renameIndex($index, $newName);
        }
        return true;
    }
}
