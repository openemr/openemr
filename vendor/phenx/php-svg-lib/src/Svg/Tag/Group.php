<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

use Svg\Style;

class Group extends AbstractTag
{
    protected function before($attribs)
    {
        $surface = $this->document->getSurface();

        $surface->save();

        $style = new Style();
        $style->inherit($this);
        $style->fromAttributes($attribs);

        $this->setStyle($style);

        $surface->setStyle($style);

        $this->applyTransform($attribs);
    }

    protected function after()
    {
        $this->document->getSurface()->restore();
    }
} 