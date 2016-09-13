<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

class Ellipse extends Shape
{
    protected $cx = 0;
    protected $cy = 0;
    protected $rx = 0;
    protected $ry = 0;

    public function start($attribs)
    {
        parent::start($attribs);

        if (isset($attribs['cx'])) {
            $this->cx = $attribs['cx'];
        }
        if (isset($attribs['cy'])) {
            $this->cy = $attribs['cy'];
        }
        if (isset($attribs['rx'])) {
            $this->rx = $attribs['rx'];
        }
        if (isset($attribs['ry'])) {
            $this->ry = $attribs['ry'];
        }

        $this->document->getSurface()->ellipse($this->cx, $this->cy, $this->rx, $this->ry, 0, 0, 360, false);
    }
} 