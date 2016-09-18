<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

class Text extends Shape
{
    protected $x = 0;
    protected $y = 0;
    protected $text = "";

    public function start($attribs)
    {
        $document = $this->document;
        $height = $this->document->getHeight();
        $this->y = $height;

        if (isset($attribs['x'])) {
            $this->x = $attribs['x'];
        }
        if (isset($attribs['y'])) {
            $this->y = $height - $attribs['y'];
        }

        $document->getSurface()->transform(1, 0, 0, -1, 0, $height);
    }

    public function end()
    {
        $surface = $this->document->getSurface();
        $x = $this->x;
        $y = $this->y;

        if ($surface->getStyle()->textAnchor == "middle") {
            $width = $surface->measureText($this->text);
            $x -= $width / 2;
        }

        $surface->fillText($this->text, $x, $y);
    }

    protected function after()
    {
        $this->document->getSurface()->restore();
    }

    public function appendText($text)
    {
        $this->text .= $text;
    }
} 