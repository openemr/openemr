<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
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

    public function start($attribs)
    {
        if (isset($attribs['x'])) {
            $this->x = $attribs['x'];
        }
        if (isset($attribs['y'])) {
            $this->y = $attribs['y'];
        }

        if (isset($attribs['width'])) {
            $this->width = $attribs['width'];
        }
        if (isset($attribs['height'])) {
            $this->height = $attribs['height'];
        }

        if (isset($attribs['rx'])) {
            $this->rx = $attribs['rx'];
        }
        if (isset($attribs['ry'])) {
            $this->ry = $attribs['ry'];
        }

        $this->document->getSurface()->rect($this->x, $this->y, $this->width, $this->height, $this->rx, $this->ry);
    }
} 