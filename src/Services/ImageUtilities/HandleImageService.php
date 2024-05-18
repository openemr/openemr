<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

namespace OpenEMR\Services\ImageUtilities;

use Exception;
use Imagick;
use ImagickException;
use OpenEMR\Pdf\MpdfGenericPdfCreator;

class HandleImageService
{
    /**
     * @param $imageData
     * @param $pdfPath
     * @return false|string
     */

    public function convertImageToPdfUseGD($imageData, $pdfPath): false|string
    {
        try {
            $imageRaw = base64_decode($imageData); // Decode base64 image data (if needed)
            $image = imagecreatefromstring($imageRaw); // Load image using GD
            if ($image === false) {
                throw new Exception('Failed to create image from data');
            }
            ob_start();
            imagepng($image);
            $imagePngData = ob_get_clean();
            imagedestroy($image);

            $pdf = new MpdfGenericPdfCreator();
            $pdf->addImageToPDF($imagePngData); // Add image to PDF

            return $pdf->outputPDF($pdfPath, 'S'); // Output PDF as a string
        } catch (Exception $e) {
            // Handle exceptions
            error_log('Error: ' . text($e->getMessage()));
            return false;
        } finally {
            // Clean up GD resources
            if (is_resource($image)) {
                imagedestroy($image);
            }
        }
    }

    /**
     * @param $imageContent
     * @param $pdfOutPath
     * @return false|string
     */
    public function convertImageToPdfUseImagick($imageContent, $pdfOutPath = ''): false|string
    {
        try {
            $imagick = new Imagick();
            $imageRaw = base64_decode($imageContent); // Decode base64 image data (if needed)
            $imagick->readImageBlob($imageRaw); // Load image using Imagick from binary data
            $imagick->setFirstIterator(); // Set iterator to first page
            $imagick->setImageFormat('pdf');
            $pdfContent = $imagick->getImagesBlob();
        } catch (ImagickException $e) {
            // Handle Imagick-related exceptions
            error_log('Imagick error: ' . text($e->getMessage()));
            return false;
        } catch (Exception $e) {
            // Handle other exceptions
            error_log('Error: ' . text($e->getMessage()));
            return false;
        } finally {
            // Clean up Imagick resources
            if (isset($imagick)) {
                $imagick->clear();
                $imagick->destroy();
            }
        }

        if ($pdfOutPath) {
            // Write the PDF content to a file if $pdfOutPath is provided
            file_put_contents($pdfOutPath, $pdfContent);
            return true; // Return true if PDF is successfully written to file
        }
        // Return the PDF content as a string if $pdfOutPath is empty
        return $pdfContent;
    }

    /**
     * Resize Example:
     * $control = new HandleImageService();
     * $sourceImage = 'C:\xampp\htdocs\openemr\public\images\balloons-154949_960_720.png';
     * $resizedImage = $control->resizeImage($sourceImage, 200, 200);
     *
     * @param $sourceImage
     * @param $targetWidth
     * @param $targetHeight
     * @return string
     * @throws Exception
     */

    public function isImagickAvailable(): bool
    {
        return extension_loaded('imagick');
    }

    /**
     * @return bool
     */
    public function isGdAvailable(): bool
    {
        return extension_loaded('gd');
    }

    /**
     * @param $sourceImage
     * @param $targetWidth
     * @param $targetHeight
     * @param $doSavePath
     * @return string
     * @throws Exception
     */
    public function resizeImage($sourceImage, $targetWidth, $targetHeight, $doSavePath = false): string
    {
        // Get image type and size from the source image
        $imageInfo = getimagesize($sourceImage);
        $imageType = $imageInfo[2];

        // Load the source image based on its type
        $sourceImageResource = match ($imageType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourceImage),
            IMAGETYPE_PNG => imagecreatefrompng($sourceImage),
            default => throw new Exception('Unsupported image type'),
        };

        // Calculate the aspect ratio of image
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $aspectRatio = $originalWidth / $originalHeight;

        // Preserve aspect ratio
        $newDimensions = $this->calculateNewDimensions($targetWidth, $targetHeight, $aspectRatio);
        $newWidth = $newDimensions['width'];
        $newHeight = $newDimensions['height'];

        // Create a new empty image with the target dimensions
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG images
        $this->preserveTransparency($targetImage, $imageType);

        // Resize the source image to the target dimensions
        imagecopyresampled($targetImage, $sourceImageResource, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Output processing
        if ($doSavePath) {
            $outputImage = $this->saveImageToFile($sourceImage, $targetImage, $imageType);
        } else {
            $outputImage = $this->getImageAsBase64($targetImage, $imageType);
        }

        // Free up memory
        imagedestroy($targetImage);
        imagedestroy($sourceImageResource);

        return $outputImage;
    }

    /**
     * @param $targetWidth
     * @param $targetHeight
     * @param $aspectRatio
     * @return array|float[]|int[]
     */
    private function calculateNewDimensions($targetWidth, $targetHeight, $aspectRatio): array
    {
        if ($targetWidth / $targetHeight > $aspectRatio) {
            $newWidth = $targetHeight * $aspectRatio;
            $newHeight = $targetHeight;
        } else {
            $newWidth = $targetWidth;
            $newHeight = $targetWidth / $aspectRatio;
        }
        return ['width' => $newWidth, 'height' => $newHeight];
    }

    /**
     * @param $targetImage
     * @param $imageType
     * @return void
     */
    private function preserveTransparency($targetImage, $imageType)
    {
        if ($imageType == IMAGETYPE_PNG) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, imagesx($targetImage), imagesy($targetImage), $transparent);
        }
    }

    /**
     * @param $sourceImage
     * @param $targetImage
     * @param $imageType
     * @return string
     */
    private function saveImageToFile($sourceImage, $targetImage, $imageType): string
    {
        $base = pathinfo($sourceImage, PATHINFO_FILENAME);
        $outputImage = $base . '_resized' . ($imageType == IMAGETYPE_PNG ? '.png' : '.jpg');

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($targetImage, $outputImage, 90); // 90 is the quality level
                break;
            case IMAGETYPE_PNG:
                imagepng($targetImage, $outputImage);
                break;
        }
        return $outputImage;
    }

    /**
     * @param $targetImage
     * @param $imageType
     * @return string
     */
    private function getImageAsBase64($targetImage, $imageType): string
    {
        ob_start();
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($targetImage, null, 90); // 90 is the quality level
                break;
            case IMAGETYPE_PNG:
                imagepng($targetImage);
                break;
        }
        $imageData = ob_get_contents();
        ob_end_clean();
        return 'data:image/' . ($imageType == IMAGETYPE_PNG ? 'png' : 'jpeg') . ';base64,' . base64_encode($imageData);
    }


    /**
     * @param                    $imageData
     * @param string             $pdfPath
     * @param string             $useExt
     * @return false|string
     * @throws Exception
     */
    public function convertImageToPdf($imageData, $pdfPath = '', $useExt = 'imagick'): false|string
    {
        $content = '';

        if (is_file($imageData)) {
            $imageContent = file_get_contents($imageData);
        } else {
            $imageContent = $imageData;
        }
        // Check for extension availability
        $usingImagick =  $this->isImagickAvailable() && $useExt === 'imagick';
        $usingGd =  $this->isGdAvailable() && $useExt === 'gd' && !$usingImagick;

        if (!$usingImagick && !$usingGd) {
            return false; // todo Could provide an alternative method but JS will pick this up
        }

        try {
            if ($usingImagick) {
                $content = $this->convertImageToPdfUseImagick($imageContent, $pdfPath);
            } elseif ($usingGd) {
                // Implement GD conversion or provide a message if not yet implemented
                $content = false; // Placeholder for actual GD implementation
                error_log('GD based conversion not implemented.');
            }
        } catch (Exception $e) {
            error_log('Error converting image to PDF using ' . ($usingImagick ? 'Imagick' : 'GD') . ': ' . text($e->getMessage()));
            return false;
        }

        return $content;
    }
}
