<?php
/** 
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
 
/**
 *  @param string|array $json
 *  @param array $options OPTIONAL; the options for image creation
 *    imageSize => array(width, height)
 *    bgColour => array(red, green, blue) | transparent
 *    penWidth => int
 *    penColour => array(red, green, blue)
 *    drawMultiplier => int
 *
 *  @return object
 */

function sigJsonToImage ($json, $options = array()) {
    $defaultOptions = array(
            'imageSize' => array(240,70)
            ,'bgColour' => 'transparent'
            ,'penWidth' => 6
            ,'penColour' => array(0x14, 0x53, 0x94)
            ,'drawMultiplier'=> 4
    );

    $options = array_merge($defaultOptions, $options);

    $img = imagecreatetruecolor($options['imageSize'][0] * $options['drawMultiplier'], $options['imageSize'][1] * $options['drawMultiplier']);

    if ($options['bgColour'] == 'transparent') {
        imagesavealpha($img, true);
        $bg = imagecolorallocatealpha($img, 0, 0, 0, 127);
    } else {
        $bg = imagecolorallocate($img, $options['bgColour'][0], $options['bgColour'][1], $options['bgColour'][2]);
    }

    $pen = imagecolorallocate($img, $options['penColour'][0], $options['penColour'][1], $options['penColour'][2]);
    imagefill($img, 0, 0, $bg);

    if (is_string($json))
        $json = json_decode(stripslashes($json));

        foreach ($json as $v)
            drawThickLine($img, $v->lx * $options['drawMultiplier'], $v->ly * $options['drawMultiplier'], $v->mx * $options['drawMultiplier'], $v->my * $options['drawMultiplier'], $pen, $options['penWidth'] * ($options['drawMultiplier'] / 2));

            $imgDest = imagecreatetruecolor($options['imageSize'][0], $options['imageSize'][1]);

            if ($options['bgColour'] == 'transparent') {
                imagealphablending($imgDest, false);
                imagesavealpha($imgDest, true);
            }

            imagecopyresampled($imgDest, $img, 0, 0, 0, 0, $options['imageSize'][0], $options['imageSize'][0], $options['imageSize'][0] * $options['drawMultiplier'], $options['imageSize'][0] * $options['drawMultiplier']);
            imagedestroy($img);

            return $imgDest;
}
/**
 * image resize function
 * @param  $file - file name to resize
 * @param  $string - The image data, as a string
 * @param  $width - new image width
 * @param  $height - new image height
 * @param  $proportional - keep image proportional, default is no
 * @param  $output - name of the new file (include path if needed)
 * @param  $delete_original - if true the original image will be deleted
 * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
 * @param  $quality - enter 1-100 (100 is best quality) default is 100
 * @param  $cropFromTop - if false crop will be from center, if true crop will be from top
 * @return boolean|resource
 */
function smart_resize_image($file,
        $string             = null,
        $width              = 0,
        $height             = 0,
        $proportional       = false,
        $output             = 'file',
        $delete_original    = true,
        $use_linux_commands = false,
        $quality            = 100,
        $cropFromTop        = false
        ) {
            if ( $height <= 0 && $width <= 0 ) return false;
            if ( $file === null && $string === null ) return false;
            # Setting defaults and meta
            $info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
            $image                        = '';
            $final_width                  = 0;
            $final_height                 = 0;
            list($width_old, $height_old) = $info;
            $cropHeight = $cropWidth = 0;
            # Calculating proportionality
            if ($proportional) {
                if      ($width  == 0)  $factor = $height/$height_old;
                elseif  ($height == 0)  $factor = $width/$width_old;
                else                    $factor = min( $width / $width_old, $height / $height_old );
                $final_width  = round( $width_old * $factor );
                $final_height = round( $height_old * $factor );
            }
            else {
                $final_width = ( $width <= 0 ) ? $width_old : $width;
                $final_height = ( $height <= 0 ) ? $height_old : $height;
                $widthX = $width_old / $width;
                $heightX = $height_old / $height;
                $x = min($widthX, $heightX);
                $cropWidth = ($width_old - $width * $x) / 2;
                $cropHeight = ($height_old - $height * $x) / 2;
            }
            # Loading image to memory according to type
            switch ( $info[2] ) {
                case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;
                case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;
                case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;
                default: return false;
            }
            # This is the resizing/resampling/transparency-preserving magic
            $image_resized = imagecreatetruecolor( $final_width, $final_height );
            if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
                $transparency = imagecolortransparent($image);
                $palletsize = imagecolorstotal($image);
                if ($transparency >= 0 && $transparency < $palletsize) {
                    $transparent_color  = imagecolorsforindex($image, $transparency);
                    $transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($image_resized, 0, 0, $transparency);
                    imagecolortransparent($image_resized, $transparency);
                }
                elseif ($info[2] == IMAGETYPE_PNG) {
                    imagealphablending($image_resized, false);
                    $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
                    imagefill($image_resized, 0, 0, $color);
                    imagesavealpha($image_resized, true);
                }
            }
            if ($cropFromTop){
                $cropHeightFinal = 0;
            }else{
                $cropHeightFinal = $cropHeight;
            }
            imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeightFinal, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
            # Taking care of original, if needed
            if ( $delete_original ) {
                if ( $use_linux_commands ) exec('rm '.$file);
                else @unlink($file);
            }
            # Preparing a method of providing result
            switch ( strtolower($output) ) {
                case 'browser':
                    $mime = image_type_to_mime_type($info[2]);
                    header("Content-type: $mime");
                    $output = NULL;
                    break;
                case 'file':
                    $output = $file;
                    break;
                case 'return':
                    imagedestroy($image);
                    return $image_resized;
                    break;
                default:
                    break;
            }
            # Writing image according to type to the output destination and image quality
            switch ( $info[2] ) {
                case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
                case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
                case IMAGETYPE_PNG:
                    $quality = 9 - (int)((0.9*$quality)/10.0);
                    imagepng($image_resized, $output, $quality);
                    break;
                default: return false;
            }
            return true;
}
/**
 *  Draws a thick line
 *  Changing the thickness of a line using imagesetthickness doesn't produce as nice of result
 *
 *  @param object $img
 *  @param int $startX
 *  @param int $startY
 *  @param int $endX
 *  @param int $endY
 *  @param object $colour
 *  @param int $thickness
 *
 *  @return void
 */
function drawThickLine ($img, $startX, $startY, $endX, $endY, $colour, $thickness) {
    $angle = (atan2(($startY - $endY), ($endX - $startX)));

    $dist_x = $thickness * (sin($angle));
    $dist_y = $thickness * (cos($angle));

    $p1x = ceil(($startX + $dist_x));
    $p1y = ceil(($startY + $dist_y));
    $p2x = ceil(($endX + $dist_x));
    $p2y = ceil(($endY + $dist_y));
    $p3x = ceil(($endX - $dist_x));
    $p3y = ceil(($endY - $dist_y));
    $p4x = ceil(($startX - $dist_x));
    $p4y = ceil(($startY - $dist_y));

    $array = array(0=>$p1x, $p1y, $p2x, $p2y, $p3x, $p3y, $p4x, $p4y);
    imagefilledpolygon($img, $array, (count($array)/2), $colour);
}

Class sigToSvg {
    /**
     * Associative array of options.
     * @var array|null
     */
    private $options;
    /**
     * An array of indexed coordinates [lx, ly, mx, my]
     * @var array|null
     */
    private $coords;
    /**
     * Maximum image width and height.
     * @var array
     */
    public $max = array(0,0);
    /**
     * @param    string|array    $json Can accept a JSON string or an array of SigPad coord objects.
     * @param    array            $options
     *            title            : @var string ['Signature'] Text description of the image
     *             penWidth        : @var int [2] width of the line
     *             penColour        : @var string ['#145394'] hexidecimal color of the signature
     * @throws    Exception If failure on JSON parsing.
     */
    public function __construct($json, $options = array()) {
        $this->options = array_merge($this->getDefaultOptions(), $options);
        if (is_string($json)) {
            $this->coords = json_decode($json, true); // force to assoc array
            if (is_null($this->coords)) {
                $jErr = '';
                if (function_exists('json_last_error')) { // allow for php 5.2
                    switch(json_last_error()) {
                        case JSON_ERROR_DEPTH:
                                $jErr = ' - Maximum stack depth exceeded';
                        break;
                        case JSON_ERROR_CTRL_CHAR:
                                $jErr = ' - Unexpected control character found';
                        break;
                        case JSON_ERROR_SYNTAX:
                                $jErr = ' - Syntax error, malformed JSON';
                        break;
                        case JSON_ERROR_NONE:
                                $jErr = ' - Unknown error';
                        break;
                    }
                }
                throw new Exception("Cannot decode the JSON string.$jErr", 1000);
            }
            $this->coords = array_map('array_values', $this->coords); // flatten the array
        } elseif (is_array($json)) {
            $this->coords = array();
            foreach ($json as $obj) $this->coords[] = array_values((array)$obj);
        } else {
            throw new Exception('Data passed to constructor is invalid.', 1001);
        }
    }
    /**
     * Svg Mime Type
     * @return string
     */
    static public function getMimeType() {
        return 'image/svg+xml';
    }
    /**
     * @return array Name value pairs
     */
    private function getDefaultOptions() {
        return array(
            'title'                 => 'Signature',
            'penWidth'              => 2,
            'penColour'             => '#145394'
        );
    }
    /**
     * Determine the maximum height and width of the image.
     * @param array $coord
     * @return null
     */
    private function setMax($coord) {
        foreach ($coord as $i => $pt) {
            if ($pt > $this->max[$i%2]) $this->max[$i%2] = $pt;
        }       
    }
    /**
     * Get the SVG line elements.
     * @return string
     */
    private function getLineElements() {
        $lines = '';
        foreach ($this->coords as $coord) {
            $lines .= vsprintf('<line x1="%d" y1="%d" x2="%d" y2="%d"/>', $coord);
            $this->setMax($coord);
        }
        return $lines;
    }
    /**
     * Get the image boundaries.
     * @param bool $axis False is x-axis, True is y-axis
     * @return int
     */
    private function getBound($axis=0) {
        return round($this->max[(int)$axis] + ($this->options['penWidth'] / 2));
    }
    /**
     * Get the full XML SVG image.
     * @return string
     */
    public function getImage() {
        $max[0] = $this->getBound(0); $max[1] = $this->getBound(1);
        $lines = $this->getLineElements();
        return '<?xml version="1.0"?><svg baseProfile="tiny" width="' . $this->getBound(0) . '" height="' . $this->getBound(1) . '" version="1.2" xmlns="http://www.w3.org/2000/svg"><g fill="red" stroke="' . $this->options['penColour'] . '" stroke-width="' . (int)$this->options['penWidth'] . '" stroke-linecap="round" stroke-lingjoin="round"><title>' . htmlspecialchars($this->options['title']) . '</title>' . $lines . '</g></svg>';
    }
    /**
     * Compress the SVG using gzip.
     * @return binary
     */
    public function getImageGz() {
        if (!function_exists('gzencode')) throw new Exception('Cannot get gzip image. Check that Zlib is installed.', 2000);
        return gzencode($this->getImage(), 9);
    }
}
?>