<?php
/**
 * Make thumbnail file by PHP GD functions.
 * version 1 - just for images.
 *
 * Another: Amiel Elboim
 * Date: 29/05/16
 */
class Thumbnail
{

    //max size of thumbnail
    public $max_size = 100;
    public $thumbnail_type = 'jpg';
    /**
     * Enable to set max size of thumbnail
     * @param (int) $max_size
     */
    public function __construct($max_size = null)
    {
        if(!is_null($max_size))$this->max_size = $max_size;

    }

    /**
     * Check if system could make thumbnail for current file.
     * @param (string) path to file
     * @return (boolean)
     */
    public function file_support_thumbnail($file){

        $info = getimagesize($file);

        $type = isset($info['type']) ? $info['type'] : $info[2];

        // Check support of file type
        if ( !(imagetypes() & $type) )
        {
            // Server does not support file type
            return false;
        }

        return true;
    }

    /**
     * Create thumbnail (calculate by the size at $this->max_size)
     * @param (string) path to file
     * @param (optional) (string) content of file (prevent to get content again)
     * @return (resource) resource of new file or false if failed.
     */
    public function create_thumbnail($file = null, $content_file = null)
    {
        if (is_null($file)) {
            $info = getimagesizefromstring($content_file);
        } else {
            $info =  getimagesize($file);
        }

        if(!$info) {
            error_log("Can't open file $file for generate thumbnail");
            return false;
        }

        $width  = isset($info['width'])  ? $info['width']  : $info[0];
        $height = isset($info['height']) ? $info['height'] : $info[1];

        // Calculate aspect ratio
        $wRatio = $this->max_size / $width;
        $hRatio = $this->max_size / $height;

        // Using imagecreatefromstring will automatically detect the file type
        $content_file = is_null($content_file) ? file_get_contents($file) : $content_file;
        $sourceImage = imagecreatefromstring($content_file);

        // Calculate a proportional width and height no larger than the max size.
        if ( ($width <= $this->max_size) && ($height <= $this->max_size) )
        {
            // Input is smaller than thumbnail, do nothing
            return $sourceImage;
        }
        elseif ( ($wRatio * $height) < $this->max_size )
        {
            // Image is horizontal
            $tHeight = ceil($wRatio * $height);
            $tWidth  = $this->max_size;
        }
        else
        {
            // Image is vertical
            $tWidth  = ceil($hRatio * $width);
            $tHeight = $this->max_size;
        }

        $thumb = imagecreatetruecolor($tWidth, $tHeight);

        if ( $sourceImage === false )
        {
            // Could not load image
            return false;
        }

        // Copy resampled makes a smooth thumbnail
        imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $tWidth, $tHeight, $width, $height);
        imagedestroy($sourceImage);

        return $thumb;

    }

    /**
     * Save the image to a file. Type is determined from the extension.
     * @param (resource) file resource from create_thumbnail()
     * @param (string) file name (pull path with wanted name)
     * @param (optional) (int) quality for 'jpeg' type
     * @return boolean
     */
    public function image_to_file($resource_file, $fileName, $quality = 80)
    {
        if ( !$resource_file || file_exists($fileName) )
        {
            return false;
        }

        $new_file = $this->create_file($resource_file, $fileName, $quality);

        return $new_file;
    }

    /**
     * Return content file. Type is determined from the extension.
     * @param (resource) file resource from create_thumbnail()
     * @param (string) file name (pull path with wanted name)
     * @param (optional) (int) quality for 'jpeg' type
     * @return (string) content file
     */
    public function get_string_file($resource_file)
    {
        ob_start();
        $this->create_file($resource_file);
        $image_string = ob_get_contents();
        ob_end_clean();
        return $image_string;
    }

    /**
     *  Create new file from resource file with GD functions.
     *  @param (string) extension of file
     *  @param (resource) file resource from create_thumbnail()
     *  @param (optional)(string) file name for saving (pull path with wanted name)
     *  @param (optional) (int) quality for 'jpeg' type
     *  @return false if failed
     */
    private function create_file($image_resource, $file_name = null ,$quality = 80)
    {
        switch ( $this->thumbnail_type )
        {
            case 'gif':
                $file = imagegif($image_resource, $file_name);
                break;
            case 'jpg':
            case 'jpeg':
                $file =  imagejpeg($image_resource, $file_name, $quality);
                break;
            case 'png':
                $file =  imagepng($image_resource, $file_name);
                break;
            case 'bmp':
                $file =  imagewbmp($image_resource, $file_name);
                break;
            default:
                return false;
        }
        return $file;
    }


}