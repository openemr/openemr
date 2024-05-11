<?php

namespace OpenEMR\Services\ImageUtilities;

use Exception;
use Imagick;
use ImagickException;
use OpenEMR\Pdf\MpdfGenericPdfCreator;

class HandleImageService
{
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
            error_log('Error: ' . $e->getMessage());
            return false;
        } finally {
            // Clean up GD resources
            if (is_resource($image)) {
                imagedestroy($image);
            }
        }
    }

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
            error_log('Imagick error: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Handle other exceptions
            error_log('Error: ' . $e->getMessage());
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
}
