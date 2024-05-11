<?php

/**
 * PdfCreator class
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\ImageUtilities;

use Exception;
use OpenEMR\Services\ImageUtilities\HandleImageService;

class HandleImageController
{
    private HandleImageService $imageService;
    private $extension;

    public function __construct($extension = 'gd')
    {
        $this->imageService = new HandleImageService();
        $this->extension = $extension;
    }

    public function isImagickAvailable(): bool
    {
        return extension_loaded('imagick');
    }

    public function isGdAvailable(): bool
    {
        return extension_loaded('gd');
    }

    public function convertImageToPdf($imageData, $pdfPath = '', $useExt = 'imagick'): false|string
    {
        $content = '';
        if (is_file($imageData)) {
            $imageContent = file_get_contents($imageData);
        } else {
            $imageContent = $imageData;
        }

        $usingImagick = $useExt === 'imagick' && $this->isImagickAvailable();
        $usingGd = $useExt === 'gd' && $this->isGdAvailable() && !$usingImagick;

        if ($usingImagick || $usingGd) {
            try {
                if ($usingImagick) {
                    $content = $this->imageService->convertImageToPdfUseImagick($imageContent, $pdfPath);
                } elseif ($usingGd) {
                    $content = $this->imageService->convertImageToPdfUseGD($imageContent, $pdfPath);
                }
            } catch (Exception $e) {
                error_log('Error converting image to PDF: ' . $e->getMessage());
                return false;
            }
        } else {
            error_log('No suitable image processing library available.');
            return false;
        }

        return $content;
    }
}
