<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license GNU LGPLv3+ http://www.gnu.org/copyleft/lesser.html
 */

namespace Svg\Tag;

class Rect extends Shape
{
    protected $x = 0;
    protected $y = 0;
    protected $width = 0;
    protected $height = 0;
    protected $rx = 0;
    protected $ry = 0;

    public function start($attributes)
    {
        if (isset($attributes['x'])) {
            $this->x = $attributes['x'];
        }
        if (isset($attributes['y'])) {
            $this->y = $attributes['y'];
        }

        if (isset($attributes['width'])) {
            $this->width = $attributes['width'];
        }
        if (isset($attributes['height'])) {
            $this->height = $attributes['height'];
        }

        if (isset($attributes['rx'])) {
            $this->rx = $attributes['rx'];
        }
        if (isset($attributes['ry'])) {
            $this->ry = $attributes['ry'];
        }

        $this->document->getSurface()->rect($this->x, $this->y, $this->width, $this->height, $this->rx, $this->ry);
    }
} 