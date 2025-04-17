<?php

/**
 *
 * Enter description here ...
 * @link http://www.electrictoolbox.com/php-generate-thumbnails/
 * @author Chris Hope
 *
 */

/**
 * thumbnail generator for creating smaller sized images and either saving them to disk
 * or streaming them directly to the browser
 *
 * @example <pre>
 *          // Create a thumbnail from /tmp/big.jpg and stream it to the browser with maximum dimensions of 100x100
 *          // this will default to jpg format and will send the necessary image type header:
 *          $tg = new thumbnailGenerator;
 *          $tg->generate('/tmp/big.jpg', 100, 100);
 *
 *          // Create a thumbnail from /tmp/big.png and save it to /tmp/small.png with maximum dimensions of 100x100:
 *          $tg = new thumbnailGenerator;
 *          $tg->generate('/tmp/big.jpg', 100, 100, '/tmp/small.jpg');
 *          </pre>
 */
class thumbnail
{
    var $allowableTypes = array (
            IMAGETYPE_GIF,
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG
    );
    public function imageCreateFromFile($filename, $imageType)
    {
        switch ($imageType) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filename);
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filename);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filename);
            default:
                return false;
        }
    }

    /**
     * Generates a thumbnail image using the file at $sourceFilename and either writing it
     * out to a new file or directly to the browser.
     *
     * @param string $sourceFilename
     *          Filename for the image to have thumbnail made from
     * @param integer $maxWidth
     *          The maxium width for the resulting thumbnail
     * @param integer $maxHeight
     *          The maxium height for the resulting thumbnail
     * @param string $targetFormatOrFilename
     *          Either a filename extension (gif|jpg|png) or the
     * @param
     *          bool set to true to set the image to the exact size given (stretching if necessary)
     *          filename the resulting file should be written to. This is optional and if not specified
     *          will send a jpg to the browser.
     * @return boolean true if the image could be created, false if not
     */
    public function generate($sourceFilename, $maxWidth, $maxHeight, $targetFormatOrFilename = 'jpg', $useExactSize = false)
    {
        $size = getimagesize($sourceFilename); // 0 = width, 1 = height, 2 = type

        // check to make sure source image is in allowable format
        if (! in_array($size [2], $this->allowableTypes)) {
            return false;
        }

        // work out the extension, what target filename should be and output function to call
        $pathinfo = pathinfo($targetFormatOrFilename);
        if ($pathinfo ['basename'] == $pathinfo ['filename']) {
            $extension = strtolower($targetFormatOrFilename);
            // set target to null so writes out to browser
            $targetFormatOrFilename = null;
        } else {
            $extension = strtolower($pathinfo ['extension']);
        }

        switch ($extension) {
            case 'gif':
                $function = 'imagegif';
                break;
            case 'png':
                $function = 'imagepng';
                break;
            default:
                $function = 'imagejpeg';
                break;
        }

        // load the image and return false if didn't work
        $source = $this->imageCreateFromFile($sourceFilename, $size [2]);
        if (! $source) {
            return false;
        }

        // write out the appropriate HTTP headers if going to browser
        if ($targetFormatOrFilename == null) {
            if ($extension == 'jpg') {
                header("Content-Type: image/jpeg");
            } else {
                header("Content-Type: image/$extension");
            }
        }

        // if the source fits within the maximum then no need to resize
        if ($useExactSize == false && $size [0] <= $maxWidth && $size [1] <= $maxHeight) {
            $function($source, $targetFormatOrFilename);
        } else {
            $newWidth = 0;
            $newHeight = 0;

            if ($useExactSize) {
                $newWidth = $maxWidth;
                $newHeight = $maxHeight;
            } else {
                $ratioWidth = $maxWidth / $size [0];
                $ratioHeight = $maxHeight / $size [1];

                // use smallest ratio
                if ($ratioWidth < $ratioHeight) {
                    $newWidth = $maxWidth;
                    $newHeight = round($size [1] * $ratioWidth);
                } else {
                    $newWidth = round($size [0] * $ratioHeight);
                    $newHeight = $maxHeight;
                }
            }

            $target = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $size [0], $size [1]);
            $function($target, $targetFormatOrFilename);
        }

        return true;
    }
}
