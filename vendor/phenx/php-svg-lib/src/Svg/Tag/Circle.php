<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

class Circle extends Shape
{
    protected $cx = 0;
    protected $cy = 0;
    protected $r;

    public function start($attribs)
    {
        if (isset($attribs['cx'])) {
            $this->cx = $attribs['cx'];
        }
        if (isset($attribs['cy'])) {
            $this->cy = $attribs['cy'];
        }
        if (isset($attribs['r'])) {
            $this->r = $attribs['r'];
        }

        $this->document->getSurface()->circle($this->cx, $this->cy, $this->r);
    }
} 