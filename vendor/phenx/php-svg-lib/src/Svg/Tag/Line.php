<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

class Line extends Shape
{
    protected $x1 = 0;
    protected $y1 = 0;

    protected $x2 = 0;
    protected $y2 = 0;

    public function start($attribs)
    {
        if (isset($attribs['x1'])) {
            $this->x1 = $attribs['x1'];
        }
        if (isset($attribs['y1'])) {
            $this->y1 = $attribs['y1'];
        }
        if (isset($attribs['x2'])) {
            $this->x2 = $attribs['x2'];
        }
        if (isset($attribs['y2'])) {
            $this->y2 = $attribs['y2'];
        }

        $surface = $this->document->getSurface();
        $surface->moveTo($this->x1, $this->y1);
        $surface->lineTo($this->x2, $this->y2);
    }
} 